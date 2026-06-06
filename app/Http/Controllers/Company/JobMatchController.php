<?php

namespace App\Http\Controllers\Company;

use App\Actions\Matching\RunJobApplicationMatchingAction;
use App\Actions\Matching\SearchJobApplicationMatchesAction;
use App\Data\Matching\JobMatchSearchFilters;
use App\Http\Controllers\Controller;
use App\Http\Requests\Matching\RunJobMatchingRequest;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JobMatchController extends Controller
{
    public function index(Request $request, SearchJobApplicationMatchesAction $search): View
    {
        $company = Auth::guard('company')->user();
        $filters = JobMatchSearchFilters::fromRequest($request);
        $matches = $search->execute($company, $filters);
        $jobs = $company->jobs()->select('id', 'title')->latest()->get();

        return view('company.matches.index', compact('matches', 'jobs', 'filters'));
    }

    public function store(
        RunJobMatchingRequest $request,
        Job $job,
        RunJobApplicationMatchingAction $runMatching
    ): RedirectResponse {
        $company = $request->user('company');
        Gate::forUser($company)->authorize('runMatching', $job);

        $run = $runMatching->execute($job, $company, $company);

        return redirect()
            ->route('company.jobs.show', $job)
            ->with('success', 'AI matching has been queued. Run #' . $run->id . ' will process applicants in the background.');
    }
}
