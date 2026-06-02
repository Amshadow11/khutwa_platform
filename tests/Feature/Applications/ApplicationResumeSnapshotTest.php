<?php

namespace Tests\Feature\Applications;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApplicationResumeSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_resume_snapshot_is_frozen_when_profile_and_resume_change_after_applying(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $job = $this->createJob();
        $user->professionalProfile()->create([
            'headline' => 'Original profile headline',
            'current_title' => 'Backend Engineer',
            'location_city' => 'Riyadh',
            'profile_visibility' => 'public',
        ]);

        $snapshot = $this->resumeSnapshot($user, 'Original resume headline');
        $resume = $this->createResume($user, $snapshot);

        $this->actingAs($user, 'web')
            ->post(route('jobs.apply', $job), [
                'resume_id' => $resume->id,
                'cover_letter' => 'I am interested.',
            ])
            ->assertRedirect(route('user.applications.index'));

        $application = Application::query()->firstOrFail();

        $this->assertSame($resume->id, $application->resume_id);
        $this->assertSame('Original resume headline', $application->resume_snapshot['identity']['headline']);
        $this->assertSame($resume->snapshot_hash, $application->resume_snapshot_hash);
        $this->assertSame($resume->generated_pdf_path, $application->submitted_resume_pdf_path);

        $user->professionalProfile()->update(['headline' => 'Changed profile headline']);
        $resume->update([
            'profile_snapshot' => $this->resumeSnapshot($user, 'Changed resume headline'),
            'snapshot_hash' => 'changed-hash',
            'version_number' => 2,
        ]);

        $application->refresh();

        $this->assertSame('Original resume headline', $application->resume_snapshot['identity']['headline']);
        $this->assertSame(1, $application->resume_snapshot_version);
        $this->assertNotSame($resume->fresh()->snapshot_hash, $application->resume_snapshot_hash);

        $this->actingAs($job->company, 'company')
            ->get(route('company.applications.show', $application))
            ->assertOk()
            ->assertSee('Original resume headline')
            ->assertDontSee('Changed resume headline');
    }

    public function test_current_professional_profile_snapshot_is_frozen_when_no_resume_is_selected(): void
    {
        Notification::fake();

        $user = $this->createUser();
        $job = $this->createJob();
        $profile = $user->professionalProfile()->create([
            'headline' => 'Snapshot headline',
            'current_title' => 'Product Designer',
            'location_city' => 'Jeddah',
            'profile_visibility' => 'public',
        ]);

        $this->actingAs($user, 'web')
            ->post(route('jobs.apply', $job), [
                'cover_letter' => 'Please consider my application.',
            ])
            ->assertRedirect(route('user.applications.index'));

        $application = Application::query()->firstOrFail();

        $this->assertNull($application->resume_id);
        $this->assertSame('Snapshot headline', $application->resume_snapshot['identity']['headline']);
        $this->assertNotNull($application->resume_snapshot_hash);
        $this->assertNotNull($application->resume_snapshot_created_at);

        $profile->update(['headline' => 'Changed after apply']);

        $application->refresh();

        $this->assertSame('Snapshot headline', $application->resume_snapshot['identity']['headline']);
    }

    private function createUser(): User
    {
        $user = User::query()->create([
            'username' => 'candidate_' . Str::random(8),
            'full_name' => 'Snapshot Candidate',
            'email' => Str::random(8) . '@example.com',
            'password' => Hash::make('password'),
            'phone' => '0500000000',
            'bio' => 'Original summary',
            'status' => 'active',
            'is_active' => true,
            'email_verified' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function createJob(): Job
    {
        $company = Company::query()->create([
            'company_name' => 'Snapshot Company',
            'email' => Str::random(8) . '@company.test',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_verified' => true,
        ]);

        return Job::query()->create([
            'company_id' => $company->id,
            'title' => 'Senior Laravel Engineer',
            'description' => 'Build production Laravel systems.',
            'job_type' => 'full_time',
            'location' => 'Riyadh',
            'status' => 'active',
            'is_active' => true,
            'deadline' => now()->addMonth()->toDateString(),
        ]);
    }

    private function createResume(User $user, array $snapshot): Resume
    {
        $template = ResumeTemplate::query()->create([
            'name' => 'Modern',
            'slug' => 'modern-test',
            'view_path' => 'resumes.templates.modern',
            'supports_rtl' => true,
            'is_active' => true,
        ]);

        return Resume::query()->create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'template_id' => $template->id,
            'title' => 'Primary Resume',
            'slug' => 'primary-resume',
            'visibility' => 'private',
            'version_number' => 1,
            'locale' => 'ar',
            'direction' => 'rtl',
            'profile_snapshot' => $snapshot,
            'snapshot_version' => 1,
            'snapshot_hash' => hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
            'snapshot_created_at' => now()->subHour(),
            'generated_pdf_path' => 'resumes/generated/primary.pdf',
        ]);
    }

    private function resumeSnapshot(User $user, string $headline): array
    {
        return [
            'snapshot_schema' => 1,
            'captured_at' => now()->toISOString(),
            'identity' => [
                'name' => $user->display_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'headline' => $headline,
                'summary' => $user->bio,
                'links' => [],
            ],
            'skills' => [],
            'experiences' => [],
            'educations' => [],
            'projects' => [],
            'certifications' => [],
            'languages' => [],
        ];
    }
}
