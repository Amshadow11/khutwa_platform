<?php

namespace App\Services\ATS;

use App\Models\Application;
use App\Models\Company;
use Illuminate\Support\Collection;

class ApplicationPipelineService
{
    private function stages(): array
    {
        return [
            Application::STATUS_PENDING => ['label' => 'جديد', 'color' => 'warning'],
            Application::STATUS_VIEWED => ['label' => 'تمت المشاهدة', 'color' => 'info'],
            Application::STATUS_SHORTLISTED => ['label' => 'القائمة المختصرة', 'color' => 'primary'],
            Application::STATUS_INTERVIEW => ['label' => 'مقابلة', 'color' => 'purple'],
            Application::STATUS_ACCEPTED => ['label' => 'مقبول', 'color' => 'success'],
            Application::STATUS_REJECTED => ['label' => 'مرفوض', 'color' => 'danger'],
        ];
    }

    public function build(Company $company, array $filters = []): Collection
    {
        $jobIds = $company->jobs()
            ->when($filters['job_id'] ?? null, fn ($query, $jobId) => $query->whereKey($jobId))
            ->pluck('id');

        $applications = Application::query()
            ->with([
                'user:id,username,full_name,email,phone,profile_picture',
                'job:id,title,location,company_id',
                'resume:id,title,version_number',
                'reviews',
                'latestAiMatch',
                'interviews',
            ])
            ->whereIn('job_id', $jobIds)
            ->latest('applied_at')
            ->get()
            ->groupBy('status');

        return collect($this->stages())->map(function (array $stage, string $status) use ($applications) {
            return [
                'status' => $status,
                'label' => $stage['label'],
                'color' => $stage['color'],
                'applications' => $applications->get($status, collect())->values(),
            ];
        });
    }
}
