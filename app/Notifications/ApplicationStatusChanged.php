<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للمستخدم عند تغيير حالة طلبه.
 */
class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application,
        private readonly string $oldStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $jobTitle  = $this->application->job->title;
        $company   = $this->application->job->company->company_name;
        $newStatus = $this->application->status_label;

        $message = match($this->application->status) {
            'viewed'      => "شاهدت {$company} طلبك على وظيفة \"{$jobTitle}\"",
            'shortlisted' => "🌟 أنت في القائمة المختصرة لوظيفة \"{$jobTitle}\" في {$company}",
            'interview'   => "🎤 دُعيت لمقابلة وظيفة \"{$jobTitle}\" في {$company}",
            'accepted'    => "✅ تهانينا! تم قبولك في وظيفة \"{$jobTitle}\" في {$company}",
            'rejected'    => "تم مراجعة طلبك على وظيفة \"{$jobTitle}\" في {$company}",
            default       => "تم تحديث حالة طلبك على وظيفة \"{$jobTitle}\"",
        };

        return [
            'type'           => 'status_changed',
            'message'        => $message,
            'application_id' => $this->application->id,
            'new_status'     => $this->application->status,
            'old_status'     => $this->oldStatus,
            'job_title'      => $jobTitle,
            'company_name'   => $company,
            'url'            => route('user.applications.show', $this->application->id),
        ];
    }
}
