<?php

namespace App\Providers;

use App\Events\ApplicationStatusTransitioned;
use App\Events\UserAppliedToJob;
use App\Listeners\LogApplicationStatusTransitionActivity;
use App\Listeners\SendApplicationReceivedNotification;
use App\Listeners\SendApplicationStatusChangedNotification;
use App\Models\Application;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\Message;
use App\Models\ProfessionalProfile;
use App\Models\Resume;
use App\Models\UserCertification;
use App\Models\UserEducation;
use App\Models\UserExperience;
use App\Models\UserLanguage;
use App\Models\UserProject;
use App\Models\UserSkill;
use App\Policies\ApplicationPolicy;
use App\Policies\ConversationPolicy;
use App\Policies\JobPolicy;
use App\Policies\MessagePolicy;
use App\Policies\ProfessionalProfilePolicy;
use App\Policies\ResumePolicy;
use App\Policies\UserCertificationPolicy;
use App\Policies\UserEducationPolicy;
use App\Policies\UserExperiencePolicy;
use App\Policies\UserLanguagePolicy;
use App\Policies\UserProjectPolicy;
use App\Policies\UserSkillPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Job::class, JobPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(Conversation::class, ConversationPolicy::class);
        Gate::policy(Message::class, MessagePolicy::class);
        Gate::policy(Resume::class, ResumePolicy::class);
        Gate::policy(ProfessionalProfile::class, ProfessionalProfilePolicy::class);
        Gate::policy(UserExperience::class, UserExperiencePolicy::class);
        Gate::policy(UserEducation::class, UserEducationPolicy::class);
        Gate::policy(UserProject::class, UserProjectPolicy::class);
        Gate::policy(UserCertification::class, UserCertificationPolicy::class);
        Gate::policy(UserSkill::class, UserSkillPolicy::class);
        Gate::policy(UserLanguage::class, UserLanguagePolicy::class);

        Event::listen(UserAppliedToJob::class, SendApplicationReceivedNotification::class);
        Event::listen(ApplicationStatusTransitioned::class, SendApplicationStatusChangedNotification::class);
        Event::listen(ApplicationStatusTransitioned::class, LogApplicationStatusTransitionActivity::class);
    }
}
