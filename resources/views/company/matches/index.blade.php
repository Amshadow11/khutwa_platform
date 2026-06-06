@extends('layouts.company')

@section('title', 'AI Matching')
@section('page-title', 'AI Matching')

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('company.matches.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Search</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Skill, title, location">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Job</label>
                <select name="job_id" class="form-select form-select-sm">
                    <option value="">All jobs</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}" @selected((string) request('job_id') === (string) $job->id)>
                            {{ Str::limit($job->title, 42) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">Min score</label>
                <input type="number" min="0" max="100" name="min_score" value="{{ request('min_score') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small">Max score</label>
                <input type="number" min="0" max="100" name="max_score" value="{{ request('max_score') }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-sm btn-primary rounded-pill px-3 w-100">
                    <i class="fas fa-search me-1"></i>Search
                </button>
                <a href="{{ route('company.matches.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-magnifying-glass-chart me-2 text-primary"></i>Match Results</span>
        <span class="text-muted small">{{ $matches->total() }} results</span>
    </div>

    @if($matches->isEmpty())
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-chart-simple fa-3x mb-3 d-block" style="opacity:.2"></i>
            No AI match results yet.
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Score</th>
                        <th>Signals</th>
                        <th>Run</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $match->application?->candidate_name }}</div>
                                <div class="text-muted small">{{ $match->application?->candidate_headline }}</div>
                                <div class="text-muted small">{{ $match->application?->candidate_location }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ number_format((float) $match->overall_score, 1) }}/100</span>
                                @if($match->is_reused)
                                    <div class="text-muted small">reused from #{{ $match->reused_from_match_id }}</div>
                                @endif
                            </td>
                            <td class="small">
                                <div><strong>Matched:</strong> {{ collect($match->matched_skills ?? [])->take(5)->implode(', ') ?: 'None' }}</div>
                                <div><strong>Missing:</strong> {{ collect($match->missing_skills ?? [])->take(5)->implode(', ') ?: 'None' }}</div>
                            </td>
                            <td class="small text-muted">
                                #{{ $match->job_match_run_id }}<br>
                                {{ $match->evaluated_at?->format('Y/m/d H:i') }}
                            </td>
                            <td>
                                <a href="{{ route('company.applications.show', $match->application) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-3">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center p-3">
            {{ $matches->links() }}
        </div>
    @endif
</div>
@endsection
