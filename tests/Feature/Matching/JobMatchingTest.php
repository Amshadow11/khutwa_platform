<?php

namespace Tests\Feature\Matching;

use App\Actions\Matching\BuildJobSnapshotHashAction;
use App\Actions\Matching\RunJobApplicationMatchingAction;
use App\Models\Application;
use App\Models\ApplicationReview;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobApplicationMatch;
use App\Models\JobMatchRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class JobMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('ai.matching.enable_explanations', false);
        config()->set('ai.matching.version', 1);
    }

    public function test_matching_reuses_completed_result_for_same_cache_key(): void
    {
        $company = $this->createCompany();
        $job = $this->createJob($company);
        $application = $this->createJobApplication($job, ['Laravel', 'PHP', 'Redis']);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);
        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        $matches = JobApplicationMatch::query()->where('application_id', $application->id)->oldest()->get();

        $this->assertCount(2, $matches);
        $this->assertFalse($matches[0]->is_reused);
        $this->assertTrue($matches[1]->is_reused);
        $this->assertSame($matches[0]->id, $matches[1]->reused_from_match_id);

        $secondRun = JobMatchRun::query()->latest('id')->firstOrFail();
        $this->assertSame(1, $secondRun->applications_reused);
        $this->assertSame(1, $secondRun->applications_processed);
    }

    public function test_matching_version_change_forces_new_calculation(): void
    {
        $company = $this->createCompany();
        $job = $this->createJob($company);
        $this->createJobApplication($job, ['Laravel', 'PHP']);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        config()->set('ai.matching.version', 2);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        $latest = JobApplicationMatch::query()->latest('id')->firstOrFail();

        $this->assertFalse($latest->is_reused);
        $this->assertSame(2, $latest->matching_version);
    }

    public function test_job_requirement_change_forces_new_job_snapshot_hash(): void
    {
        $company = $this->createCompany();
        $job = $this->createJob($company);
        $this->createJobApplication($job, ['Laravel', 'PHP']);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        $oldHash = JobMatchRun::query()->firstOrFail()->job_snapshot_hash;
        $job->update(['requirements' => 'Laravel, PHP, Redis, Docker, Kubernetes']);

        app(RunJobApplicationMatchingAction::class)->execute($job->fresh(), $company, $company);

        $latestRun = JobMatchRun::query()->latest('id')->firstOrFail();
        $latestMatch = JobApplicationMatch::query()->latest('id')->firstOrFail();

        $this->assertNotSame($oldHash, $latestRun->job_snapshot_hash);
        $this->assertFalse($latestMatch->is_reused);
    }

    public function test_failed_matches_are_not_reused(): void
    {
        $company = $this->createCompany();
        $job = $this->createJob($company);
        $application = $this->createJobApplication($job, ['Laravel']);
        $jobSnapshotHash = app(BuildJobSnapshotHashAction::class)->execute($job);
        $cacheKey = hash('sha256', implode('|', [
            $application->id,
            $application->job_id,
            $application->resume_snapshot_hash,
            $application->resume_snapshot_version,
            $jobSnapshotHash,
            1,
        ]));

        $run = JobMatchRun::query()->create([
            'job_id' => $job->id,
            'company_id' => $company->id,
            'status' => JobMatchRun::STATUS_COMPLETED,
            'matching_version' => 1,
            'job_snapshot_hash' => $jobSnapshotHash,
        ]);

        JobApplicationMatch::query()->create([
            'job_match_run_id' => $run->id,
            'application_id' => $application->id,
            'job_id' => $job->id,
            'company_id' => $company->id,
            'resume_snapshot_hash' => $application->resume_snapshot_hash,
            'resume_snapshot_version' => $application->resume_snapshot_version,
            'job_snapshot_hash' => $jobSnapshotHash,
            'matching_version' => 1,
            'match_cache_key' => $cacheKey,
            'status' => JobApplicationMatch::STATUS_FAILED,
            'evaluated_at' => now(),
        ]);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        $latest = JobApplicationMatch::query()->latest('id')->firstOrFail();

        $this->assertSame(JobApplicationMatch::STATUS_COMPLETED, $latest->status);
        $this->assertFalse($latest->is_reused);
    }

    public function test_matching_does_not_mutate_manual_application_reviews(): void
    {
        $company = $this->createCompany();
        $job = $this->createJob($company);
        $application = $this->createJobApplication($job, ['Laravel', 'PHP']);

        $review = ApplicationReview::query()->create([
            'application_id' => $application->id,
            'company_id' => $company->id,
            'rating' => 4,
            'recommendation' => 'yes',
            'overall_score' => 80,
            'match_signals' => ['source' => 'manual_review'],
        ]);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);

        $review->refresh();

        $this->assertSame('manual_review', $review->match_signals['source']);
        $this->assertSame('80.00', (string) $review->overall_score);
    }

    public function test_company_matching_route_dispatches_queue_job_only(): void
    {
        Bus::fake();

        $company = $this->createCompany();
        $job = $this->createJob($company);

        $this->actingAs($company, 'company')
            ->post(route('company.jobs.matches.run', $job))
            ->assertRedirect(route('company.jobs.show', $job));

        $this->assertDatabaseHas('job_match_runs', [
            'job_id' => $job->id,
            'company_id' => $company->id,
            'status' => JobMatchRun::STATUS_QUEUED,
        ]);
    }

    public function test_match_search_is_scoped_to_current_company(): void
    {
        $company = $this->createCompany();
        $otherCompany = $this->createCompany();
        $job = $this->createJob($company);
        $otherJob = $this->createJob($otherCompany);
        $this->createJobApplication($job, ['Laravel', 'PHP']);
        $this->createJobApplication($otherJob, ['Laravel', 'PHP']);

        app(RunJobApplicationMatchingAction::class)->execute($job, $company, $company);
        app(RunJobApplicationMatchingAction::class)->execute($otherJob, $otherCompany, $otherCompany);

        $this->actingAs($company, 'company')
            ->get(route('company.matches.index', ['q' => 'Laravel']))
            ->assertOk()
            ->assertSee('Snapshot Candidate')
            ->assertDontSee($otherCompany->company_name);
    }

    private function createCompany(): Company
    {
        return Company::query()->create([
            'company_name' => 'Company ' . Str::random(6),
            'email' => Str::random(8) . '@company.test',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_verified' => true,
        ]);
    }

    private function createJob(Company $company): Job
    {
        return Job::query()->create([
            'company_id' => $company->id,
            'title' => 'Senior Laravel Engineer',
            'description' => 'Build APIs with Laravel and PHP.',
            'requirements' => 'Laravel, PHP, MySQL, Redis',
            'job_type' => 'full_time',
            'experience_level' => 'senior',
            'location' => 'Riyadh',
            'status' => 'active',
            'is_active' => true,
            'deadline' => now()->addMonth()->toDateString(),
        ]);
    }

    private function createJobApplication(Job $job, array $skills): Application
    {
        $user = User::query()->create([
            'username' => 'candidate_' . Str::random(8),
            'full_name' => 'Snapshot Candidate',
            'email' => Str::random(8) . '@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_active' => true,
            'email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $snapshot = [
            'snapshot_schema' => 1,
            'identity' => [
                'name' => $user->full_name,
                'email' => $user->email,
                'headline' => 'Senior Laravel Engineer',
                'city' => 'Riyadh',
                'country' => 'Saudi Arabia',
            ],
            'skills' => collect($skills)->map(fn (string $skill) => ['name' => $skill])->all(),
            'experiences' => [
                ['title' => 'Senior Laravel Engineer', 'company_name' => 'Tech Co'],
            ],
            'educations' => [
                ['degree' => 'Bachelor of Computer Science', 'institution_name' => 'University'],
            ],
            'projects' => [],
            'certifications' => [],
            'languages' => [],
        ];

        return Application::query()->create([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'cover_letter' => 'I am interested.',
            'resume_snapshot' => $snapshot,
            'resume_snapshot_hash' => hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'resume_snapshot_version' => 1,
            'resume_snapshot_created_at' => now(),
            'applicant_name' => $user->full_name,
            'applicant_email' => $user->email,
            'status' => Application::STATUS_PENDING,
            'applied_at' => now(),
        ]);
    }
}
