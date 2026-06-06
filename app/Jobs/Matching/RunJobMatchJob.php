<?php

namespace App\Jobs\Matching;

use App\Actions\Matching\BuildMatchCacheKeyAction;
use App\Actions\Matching\FindReusableJobApplicationMatchAction;
use App\Actions\Matching\PersistJobApplicationMatchAction;
use App\Actions\Matching\ReuseJobApplicationMatchAction;
use App\Actions\Matching\ScoreApplicationAgainstJobAction;
use App\Events\JobApplicationMatchScored;
use App\Events\JobMatchRunCompleted;
use App\Events\JobMatchRunFailed;
use App\Events\JobMatchRunStarted;
use App\Models\Application;
use App\Models\JobApplicationMatch;
use App\Models\JobMatchRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RunJobMatchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public readonly int $runId)
    {
        $this->onQueue('ai');
    }

    public function handle(
        BuildMatchCacheKeyAction $cacheKeyBuilder,
        FindReusableJobApplicationMatchAction $findReusable,
        ReuseJobApplicationMatchAction $reuseMatch,
        ScoreApplicationAgainstJobAction $scoreApplication,
        PersistJobApplicationMatchAction $persistMatch,
    ): void {
        $run = JobMatchRun::query()->with('job')->findOrFail($this->runId);

        $run->update([
            'status' => JobMatchRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        event(new JobMatchRunStarted($run));

        try {
            $applications = Application::query()
                ->where('job_id', $run->job_id)
                ->with('job:id,company_id,title,description,requirements,location,job_type,experience_level,remote_work,salary,salary_range')
                ->orderBy('id')
                ->get();

            $run->update(['applications_total' => $applications->count()]);

            foreach ($applications as $application) {
                $cacheKey = null;

                try {
                    $cacheKey = $cacheKeyBuilder->execute(
                        $application,
                        $run->job_snapshot_hash,
                        $run->matching_version,
                    );

                    $reusable = $findReusable->execute($cacheKey);

                    if ($reusable) {
                        $match = DB::transaction(fn () => $reuseMatch->execute($run, $application, $reusable));
                        $run->increment('applications_reused');
                    } else {
                        $result = $scoreApplication->execute($run->job, $application, $run->company_id);
                        $match = DB::transaction(fn () => $persistMatch->execute(
                            run: $run,
                            application: $application,
                            jobSnapshotHash: $run->job_snapshot_hash,
                            matchingVersion: $run->matching_version,
                            cacheKey: $cacheKey,
                            score: $result['score'],
                            aiExplanation: $result['ai_explanation'],
                        ));
                    }

                    $run->increment('applications_processed');
                    event(new JobApplicationMatchScored($match));
                } catch (\Throwable $e) {
                    JobApplicationMatch::query()->create([
                        'job_match_run_id' => $run->id,
                        'application_id' => $application->id,
                        'job_id' => $application->job_id,
                        'company_id' => $run->company_id,
                        'resume_snapshot_hash' => $application->resume_snapshot_hash,
                        'resume_snapshot_version' => $application->resume_snapshot_version ?: 1,
                        'job_snapshot_hash' => $run->job_snapshot_hash,
                        'matching_version' => $run->matching_version,
                        'match_cache_key' => $cacheKey ?? 'failed-' . $application->id . '-' . $run->id,
                        'status' => JobApplicationMatch::STATUS_FAILED,
                        'evaluated_at' => now(),
                        'error_message' => $e->getMessage(),
                    ]);

                    $run->increment('applications_failed');
                    $run->increment('applications_processed');

                    Log::warning('Application matching failed inside run.', [
                        'run_id' => $run->id,
                        'application_id' => $application->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $run->update([
                'status' => JobMatchRun::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            event(new JobMatchRunCompleted($run->fresh()));
        } catch (\Throwable $e) {
            $run->update([
                'status' => JobMatchRun::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Job matching run failed.', [
                'run_id' => $run->id,
                'error' => $e->getMessage(),
            ]);

            event(new JobMatchRunFailed($run->fresh()));

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        JobMatchRun::query()
            ->whereKey($this->runId)
            ->update([
                'status' => JobMatchRun::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);
    }

    public function tags(): array
    {
        return ['ai', 'job-matching', 'match-run:' . $this->runId];
    }
}
