<?php

namespace App\Services\ATS;

use App\Models\Application;

class ApplicationStatusWorkflowService
{
    private const TRANSITIONS = [
        Application::STATUS_PENDING => [
            Application::STATUS_VIEWED,
            Application::STATUS_SHORTLISTED,
            Application::STATUS_INTERVIEW,
            Application::STATUS_REJECTED,
        ],
        Application::STATUS_VIEWED => [
            Application::STATUS_SHORTLISTED,
            Application::STATUS_INTERVIEW,
            Application::STATUS_REJECTED,
        ],
        Application::STATUS_SHORTLISTED => [
            Application::STATUS_INTERVIEW,
            Application::STATUS_ACCEPTED,
            Application::STATUS_REJECTED,
        ],
        Application::STATUS_INTERVIEW => [
            Application::STATUS_ACCEPTED,
            Application::STATUS_REJECTED,
        ],
        Application::STATUS_ACCEPTED => [],
        Application::STATUS_REJECTED => [],
    ];

    public function canTransition(Application $application, string $toStatus): bool
    {
        return in_array($toStatus, $this->allowedTargetStatuses($application), true);
    }

    public function allowedTargetStatuses(Application $application): array
    {
        return self::TRANSITIONS[$application->status] ?? [];
    }

    public function availableTransitions(Application $application): array
    {
        return collect($this->allowedTargetStatuses($application))
            ->mapWithKeys(fn (string $status) => [
                $status => [
                    'status' => $status,
                    'key' => $this->transitionKey($application->status, $status),
                    'label' => $this->statusLabel($status),
                    'color' => $this->statusColor($status),
                ],
            ])
            ->all();
    }

    public function transitionKey(?string $fromStatus, string $toStatus): string
    {
        return match ($toStatus) {
            Application::STATUS_VIEWED => 'mark_viewed',
            Application::STATUS_SHORTLISTED => 'shortlist',
            Application::STATUS_INTERVIEW => 'schedule_interview',
            Application::STATUS_ACCEPTED => 'accept',
            Application::STATUS_REJECTED => 'reject',
            default => trim(($fromStatus ?: 'unknown') . '_to_' . $toStatus, '_'),
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            Application::STATUS_PENDING => 'قيد المراجعة',
            Application::STATUS_VIEWED => 'تمت المشاهدة',
            Application::STATUS_SHORTLISTED => 'في القائمة المختصرة',
            Application::STATUS_INTERVIEW => 'دُعي للمقابلة',
            Application::STATUS_ACCEPTED => 'مقبول',
            Application::STATUS_REJECTED => 'مرفوض',
            default => $status,
        };
    }

    public function statusColor(string $status): string
    {
        return match ($status) {
            Application::STATUS_PENDING => 'warning',
            Application::STATUS_VIEWED => 'info',
            Application::STATUS_SHORTLISTED => 'primary',
            Application::STATUS_INTERVIEW => 'purple',
            Application::STATUS_ACCEPTED => 'success',
            Application::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }
}
