@extends('layouts.company')

@section('title', 'إدارة الوظائف')
@section('page-title', 'إدارة الوظائف')

@section('content')

{{-- Header --}}
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold">وظائفك المنشورة</h5>
        <small class="text-muted">إجمالي {{ $jobs->total() }} وظيفة</small>
    </div>
    <a href="{{ route('company.jobs.create') }}"
       class="btn btn-primary rounded-pill px-4">
        <i class="fas fa-plus me-1"></i>نشر وظيفة جديدة
    </a>
</div>

{{-- فلتر الحالة --}}
<div class="d-flex gap-2 mb-3 flex-wrap">
    @foreach(['all' => 'الكل', 'active' => 'نشطة', 'inactive' => 'معطّلة', 'expired' => 'منتهية'] as $val => $label)
        <a href="{{ request()->fullUrlWithQuery(['status' => $val === 'all' ? null : $val]) }}"
           class="btn btn-sm rounded-pill px-3 {{ request('status', 'all') === $val ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if($jobs->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-briefcase fa-3x mb-3 d-block" style="opacity:.25"></i>
            لا توجد وظائف منشورة بعد
            <div class="mt-3">
                <a href="{{ route('company.jobs.create') }}"
                   class="btn btn-primary rounded-pill px-4">نشر أول وظيفة</a>
            </div>
        </div>
    </div>
@else

    {{-- Desktop Table --}}
    <div class="card d-none d-md-block">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>الوظيفة</th>
                        <th>النوع</th>
                        <th>الموقع</th>
                        <th>الطلبات</th>
                        <th>الجديدة</th>
                        <th>الحالة</th>
                        <th>تاريخ النشر</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($jobs as $job)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $job->title }}</div>
                            @if($job->deadline)
                                <small class="text-muted">
                                    ينتهي: {{ $job->deadline->format('Y/m/d') }}
                                    @if($job->is_expired)
                                        <span class="text-danger">(منتهي)</span>
                                    @elseif($job->days_remaining <= 3)
                                        <span class="text-warning">({{ $job->days_remaining }} أيام)</span>
                                    @endif
                                </small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $job->job_type_label }}</span>
                        </td>
                        <td class="text-muted">{{ $job->location ?? '—' }}</td>
                        <td>
                            <a href="{{ route('company.jobs.show', $job) }}"
                               class="text-decoration-none fw-semibold">
                                {{ $job->applications_count }}
                            </a>
                        </td>
                        <td>
                            @if($job->pending_count > 0)
                                <span class="badge bg-warning text-dark">{{ $job->pending_count }} جديد</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $job->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $job->status === 'active' ? 'نشطة' : 'معطّلة' }}
                            </span>
                        </td>
                        <td class="text-muted" style="font-size:.82rem">
                            {{ $job->created_at->format('Y/m/d') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('company.jobs.show', $job) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-2"
                                   title="عرض المتقدمين">
                                    <i class="fas fa-users"></i>
                                </a>
                                <a href="{{ route('company.jobs.edit', $job) }}"
                                   class="btn btn-sm btn-outline-secondary rounded-pill px-2"
                                   title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('company.jobs.toggle', $job) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-{{ $job->status === 'active' ? 'warning' : 'success' }} rounded-pill px-2"
                                            title="{{ $job->status === 'active' ? 'إيقاف' : 'تفعيل' }}">
                                        <i class="fas fa-{{ $job->status === 'active' ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('company.jobs.destroy', $job) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('هل تريد حذف هذه الوظيفة؟')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-2" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div class="d-md-none">
        @foreach($jobs as $job)
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="mb-1 fw-bold">{{ $job->title }}</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-dark" style="font-size:.72rem">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $job->location ?? 'غير محدد' }}
                            </span>
                            <span class="badge bg-light text-dark" style="font-size:.72rem">
                                {{ $job->job_type_label }}
                            </span>
                        </div>
                    </div>
                    <span class="badge bg-{{ $job->status === 'active' ? 'success' : 'secondary' }}">
                        {{ $job->status === 'active' ? 'نشطة' : 'معطّلة' }}
                    </span>
                </div>

                <div class="d-flex gap-3 mb-3" style="font-size:.82rem;color:#666">
                    <span><i class="fas fa-users me-1"></i>{{ $job->applications_count }} طلب</span>
                    @if($job->pending_count > 0)
                        <span class="text-warning fw-semibold">
                            <i class="fas fa-bell me-1"></i>{{ $job->pending_count }} جديد
                        </span>
                    @endif
                    <span class="me-auto text-muted">{{ $job->created_at->diffForHumans() }}</span>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('company.jobs.show', $job) }}"
                       class="btn btn-sm btn-primary rounded-pill flex-fill">
                        <i class="fas fa-users me-1"></i>الطلبات
                    </a>
                    <a href="{{ route('company.jobs.edit', $job) }}"
                       class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('company.jobs.toggle', $job) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-{{ $job->status === 'active' ? 'warning' : 'success' }} rounded-pill px-3">
                            <i class="fas fa-{{ $job->status === 'active' ? 'pause' : 'play' }}"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $jobs->links() }}
    </div>

@endif

@endsection
