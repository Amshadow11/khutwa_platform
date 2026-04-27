@extends('layouts.company')

@section('title', 'الطلبات الواردة')
@section('page-title', 'الطلبات الواردة')

@section('content')

{{-- إحصائيات سريعة --}}
<div class="row g-3 mb-4">
    @php
    $statsCards = [
        ['label'=>'إجمالي الطلبات', 'value'=>$stats['total'],      'icon'=>'users',        'color'=>'blue'],
        ['label'=>'طلبات جديدة',   'value'=>$stats['pending'],     'icon'=>'clock',        'color'=>'orange'],
        ['label'=>'قائمة مختصرة',  'value'=>$stats['shortlisted'], 'icon'=>'star',         'color'=>'purple'],
        ['label'=>'مقبولون',       'value'=>$stats['accepted'],    'icon'=>'check-circle', 'color'=>'green'],
    ];
    @endphp
    @foreach($statsCards as $s)
    <div class="col-6 col-lg-3">
        <div class="stat-card stat-{{ $s['color'] }}">
            <div class="stat-icon"><i class="fas fa-{{ $s['icon'] }}"></i></div>
            <div class="stat-value">{{ $s['value'] }}</div>
            <div class="stat-label">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- الفلاتر --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('company.applications.index') }}"
              class="d-flex gap-2 align-items-center flex-wrap">

            <select name="status" class="form-select form-select-sm"
                    style="width:auto;min-width:140px" onchange="this.form.submit()">
                <option value="">كل الحالات</option>
                @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'قائمة مختصرة','interview'=>'مقابلة','accepted'=>'مقبول','rejected'=>'مرفوض'] as $v=>$l)
                    <option value="{{ $v }}" {{ request('status')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>

            <select name="job_id" class="form-select form-select-sm"
                    style="width:auto;min-width:160px" onchange="this.form.submit()">
                <option value="">كل الوظائف</option>
                @foreach($jobs as $job)
                    <option value="{{ $job->id }}" {{ request('job_id')==$job->id ? 'selected':'' }}>
                        {{ Str::limit($job->title, 30) }}
                    </option>
                @endforeach
            </select>

            @if(request()->hasAny(['status','job_id']))
                <a href="{{ route('company.applications.index') }}"
                   class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-times me-1"></i>مسح
                </a>
            @endif

            <span class="text-muted small ms-auto">{{ $applications->total() }} نتيجة</span>
        </form>
    </div>
</div>

@if($applications->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-inbox fa-3x mb-3 d-block" style="opacity:.25"></i>
            لا توجد طلبات تطابق الفلتر المحدد
        </div>
    </div>
@else

{{-- Desktop Table --}}
<div class="card d-none d-md-block">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>المتقدم</th><th>الوظيفة</th><th>تاريخ التقديم</th>
                    <th>الحالة</th><th>تغيير الحالة</th><th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                <tr class="{{ $app->status === 'pending' ? 'table-warning bg-opacity-10' : '' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $app->user->avatar_url }}"
                                 style="width:38px;height:38px;border-radius:50%;object-fit:cover" alt="">
                            <div>
                                <div class="fw-semibold" style="font-size:.88rem">
                                    {{ $app->user->display_name }}
                                </div>
                                <div class="text-muted" style="font-size:.75rem">
                                    {{ $app->user->phone ?? $app->user->email }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:.85rem">{{ $app->job->title ?? '—' }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $app->job->location ?? '' }}</div>
                    </td>
                    <td class="text-muted" style="font-size:.8rem">
                        {{ $app->applied_at?->format('Y/m/d') }}<br>
                        <small>{{ $app->applied_at?->diffForHumans() }}</small>
                    </td>
                    <td>
                        <span class="badge bg-{{ $app->status_color }}">{{ $app->status_label }}</span>
                    </td>
                    <td>
                        <form action="{{ route('company.applications.updateStatus', $app) }}"
                              method="POST">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm"
                                    style="width:130px;font-size:.78rem"
                                    onchange="this.form.submit()">
                                @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'قائمة مختصرة','interview'=>'مقابلة','accepted'=>'مقبول','rejected'=>'مرفوض'] as $v=>$l)
                                    <option value="{{ $v }}" {{ $app->status===$v?'selected':'' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('company.applications.show', $app) }}"
                           class="btn btn-sm btn-outline-primary rounded-pill px-3"
                           style="font-size:.78rem">عرض</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile Cards --}}
<div class="d-md-none">
    @foreach($applications as $app)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
                <img src="{{ $app->user->avatar_url }}"
                     style="width:44px;height:44px;border-radius:50%;object-fit:cover" alt="">
                <div class="flex-grow-1 overflow-hidden">
                    <div class="fw-semibold">{{ $app->user->display_name }}</div>
                    <div class="text-muted" style="font-size:.78rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        {{ $app->job->title ?? '—' }}
                    </div>
                </div>
                <span class="badge bg-{{ $app->status_color }} flex-shrink-0">
                    {{ $app->status_label }}
                </span>
            </div>
            <form action="{{ route('company.applications.updateStatus', $app) }}"
                  method="POST" class="mb-2">
                @csrf @method('PATCH')
                <select name="status" class="form-select form-select-sm"
                        onchange="this.form.submit()">
                    @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'قائمة مختصرة','interview'=>'مقابلة','accepted'=>'مقبول','rejected'=>'مرفوض'] as $v=>$l)
                        <option value="{{ $v }}" {{ $app->status===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </form>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-muted" style="font-size:.75rem">
                    <i class="fas fa-clock me-1"></i>{{ $app->applied_at?->diffForHumans() }}
                </span>
                <a href="{{ route('company.applications.show', $app) }}"
                   class="btn btn-sm btn-outline-primary rounded-pill px-3"
                   style="font-size:.78rem">عرض الملف</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="d-flex justify-content-center mt-3">
    {{ $applications->appends(request()->query())->links() }}
</div>

@endif

@endsection
