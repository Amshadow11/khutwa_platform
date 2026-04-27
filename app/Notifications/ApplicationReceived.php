<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * إشعار للشركة عند استلام طلب تقديم جديد.
 *
 * تُرسَل عبر قناة database فقط (في المرحلة الثانية).
 * في المرحلة الثالثة: يمكن إضافة قناة mail أو broadcast.
 */
class ApplicationReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Application $application
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $applicantName = $this->application->user->display_name;
        $jobTitle      = $this->application->job->title;

        return [
            'type'           => 'application_received',
            'message'        => "تقدّم {$applicantName} على وظيفة \"{$jobTitle}\"",
            'application_id' => $this->application->id,
            'job_id'         => $this->application->job_id,
            'user_name'      => $applicantName,
            'job_title'      => $jobTitle,
            'url'            => route('company.applications.show', $this->application->id),
        ];
    }
}
