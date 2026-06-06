<?php

namespace Tests\Feature\Applications;

use App\Actions\ATS\TransitionApplicationStatusAction;
use App\Events\ApplicationStatusTransitioned;
use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApplicationStatusWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_transition_action_records_workflow_metadata_and_dispatches_event(): void
    {
        Event::fake([ApplicationStatusTransitioned::class]);

        [$company, $application] = $this->createWorkflowApplication();

        app(TransitionApplicationStatusAction::class)->execute(
            application: $application,
            toStatus: Application::STATUS_SHORTLISTED,
            actor: $company,
            note: 'Good fit.',
        );

        $application->refresh();
        $history = $application->statusHistory()->firstOrFail();

        $this->assertSame(Application::STATUS_SHORTLISTED, $application->status);
        $this->assertSame(Application::STATUS_PENDING, $history->from_status);
        $this->assertSame(Application::STATUS_SHORTLISTED, $history->status);
        $this->assertSame('shortlist', $history->transition_key);
        $this->assertSame($company->id, $history->actor_id);
        $this->assertSame($company::class, $history->actor_type);

        Event::assertDispatched(ApplicationStatusTransitioned::class);
    }

    public function test_terminal_application_status_cannot_transition(): void
    {
        [$company, $application] = $this->createWorkflowApplication([
            'status' => Application::STATUS_ACCEPTED,
        ]);

        $this->expectException(ValidationException::class);

        app(TransitionApplicationStatusAction::class)->execute(
            application: $application,
            toStatus: Application::STATUS_REJECTED,
            actor: $company,
        );
    }

    public function test_company_show_marks_pending_application_as_viewed_once_without_candidate_notification(): void
    {
        Notification::fake();

        [$company, $application, $user] = $this->createWorkflowApplication();

        $this->actingAs($company, 'company')
            ->get(route('company.applications.show', $application))
            ->assertOk();

        $this->actingAs($company, 'company')
            ->get(route('company.applications.show', $application))
            ->assertOk();

        $application->refresh();

        $this->assertSame(Application::STATUS_VIEWED, $application->status);
        $this->assertSame(1, $application->statusHistory()->where('transition_key', 'mark_viewed')->count());

        Notification::assertNotSentTo($user, ApplicationStatusChanged::class);
    }

    public function test_scheduling_interview_transitions_application_through_workflow(): void
    {
        Notification::fake();

        [$company, $application] = $this->createWorkflowApplication([
            'status' => Application::STATUS_SHORTLISTED,
        ]);

        $this->actingAs($company, 'company')
            ->post(route('company.applications.interviews.store', $application), [
                'scheduled_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'duration_minutes' => 45,
                'location_type' => 'online',
                'meeting_url' => 'https://example.com/interview',
            ])
            ->assertRedirect();

        $application->refresh();

        $this->assertSame(Application::STATUS_INTERVIEW, $application->status);
        $this->assertDatabaseHas('application_status_history', [
            'application_id' => $application->id,
            'from_status' => Application::STATUS_SHORTLISTED,
            'status' => Application::STATUS_INTERVIEW,
            'transition_key' => 'schedule_interview',
            'actor_type' => $company::class,
            'actor_id' => $company->id,
        ]);
    }

    public function test_non_owner_company_cannot_transition_application(): void
    {
        [$company, $application] = $this->createWorkflowApplication();
        $otherCompany = $this->createCompany();

        $this->actingAs($otherCompany, 'company')
            ->patch(route('company.applications.transitionStatus', $application), [
                'status' => Application::STATUS_SHORTLISTED,
            ])
            ->assertForbidden();

        $this->assertSame(Application::STATUS_PENDING, $application->fresh()->status);
        $this->assertSame(0, $application->statusHistory()->count());
    }

    public function test_status_transitions_do_not_mutate_application_resume_snapshot(): void
    {
        [$company, $application] = $this->createWorkflowApplication([
            'resume_snapshot' => [
                'identity' => ['headline' => 'Frozen snapshot'],
                'skills' => [['name' => 'Laravel']],
            ],
            'resume_snapshot_hash' => 'snapshot-hash',
            'resume_snapshot_version' => 3,
            'resume_snapshot_created_at' => now(),
        ]);

        app(TransitionApplicationStatusAction::class)->execute(
            application: $application,
            toStatus: Application::STATUS_SHORTLISTED,
            actor: $company,
        );

        $application->refresh();

        $this->assertSame('Frozen snapshot', $application->resume_snapshot['identity']['headline']);
        $this->assertSame('snapshot-hash', $application->resume_snapshot_hash);
        $this->assertSame(3, $application->resume_snapshot_version);
    }

    private function createWorkflowApplication(array $applicationOverrides = []): array
    {
        $company = $this->createCompany();
        $user = $this->createUser();

        $job = Job::query()->create([
            'company_id' => $company->id,
            'title' => 'Backend Engineer',
            'description' => 'Build Laravel products.',
            'job_type' => 'full_time',
            'location' => 'Riyadh',
            'status' => 'active',
            'is_active' => true,
            'deadline' => now()->addMonth()->toDateString(),
        ]);

        $application = Application::query()->create(array_merge([
            'job_id' => $job->id,
            'user_id' => $user->id,
            'cover_letter' => 'Interested.',
            'applicant_name' => $user->display_name,
            'applicant_email' => $user->email,
            'applicant_phone' => $user->phone,
            'status' => Application::STATUS_PENDING,
            'applied_at' => now(),
        ], $applicationOverrides));

        return [$company, $application, $user, $job];
    }

    private function createCompany(): Company
    {
        return Company::query()->create([
            'company_name' => 'Workflow Company ' . Str::random(6),
            'email' => Str::random(8) . '@company.test',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_verified' => true,
        ]);
    }

    private function createUser(): User
    {
        $user = User::query()->create([
            'username' => 'candidate_' . Str::random(8),
            'full_name' => 'Workflow Candidate',
            'email' => Str::random(8) . '@example.com',
            'password' => Hash::make('password'),
            'phone' => '0500000000',
            'status' => 'active',
            'is_active' => true,
            'email_verified' => true,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
