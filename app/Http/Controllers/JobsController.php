<?php

namespace App\Http\Controllers;

use App\Data\Search\JobSearchFilters;
use App\Models\Job;
use App\Services\Search\JobSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobsController extends Controller
{
    public function index(Request $request, JobSearchService $search): View
    {
        $filters = JobSearchFilters::fromRequest($request);
        $jobs = $search->search($filters);
        $filters = $filters->toViewArray();

        $cities = ['صنعاء', 'عدن', 'تعز', 'الحديدة', 'إب', 'مأرب', 'حضرموت', 'لحج', 'أبين', 'ذمار', 'شبوة'];

        return view('jobs.index', compact('jobs', 'filters', 'cities'));
    }

    public function show(Job $job): View
    {
        abort_if(! $job->is_active || $job->status !== 'active', 404);

        $job->incrementViews();
        $job->load('company');

        $hasApplied = false;
        $availableResumes = collect();

        if (auth('web')->check()) {
            $hasApplied = auth('web')->user()->hasAppliedTo($job->id);
            $availableResumes = auth('web')->user()
                ->resumes()
                ->with('template:id,name')
                ->select([
                    'id',
                    'user_id',
                    'template_id',
                    'title',
                    'version_number',
                    'snapshot_created_at',
                    'generated_pdf_path',
                    'pdf_status',
                ])
                ->latest()
                ->get();
        }

        $relatedJobs = Job::with('company:id,company_name,logo')
            ->active()
            ->where('id', '!=', $job->id)
            ->where(function ($query) use ($job) {
                $query->where('company_id', $job->company_id)
                    ->orWhere('location', $job->location);
            })
            ->limit(4)
            ->get();

        return view('jobs.show', compact('job', 'hasApplied', 'relatedJobs', 'availableResumes'));
    }
}
