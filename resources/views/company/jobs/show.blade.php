@extends('layouts.company')

@section('title', $job->title)
@section('page-title', $job->title)

@section('content')
<div class="row g-3">

    {{-- معلومات الوظيفة --}}
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <div class="d-flex gap-2 flex-wrap mb-2">
                            <span class="badge bg-{{ $job->status === 'active' ? 'success' : 'secondary' }}">
                                {{ $job->status === 'active' ? 'نشطة' : 'معطّلة' }}
                            </span>
                            <span class="badge bg-light text-dark">{{ $job->job_type_label }}</span>
                            @if($job->urgent)<span class="badge bg-danger">⚡ عاجلة</span>@endif
                            @if($job->remote_work)<span class="badge bg-info">عن بُعد</span>@endif
                        </div>
                        <div class="text-muted small">
                            <i class="fas fa-map-marker-alt me-1"></i>{{ $job->location ?? 'غير محدد' }}
                            @if($job->salary)
                                &ensp;<i class="fas fa-money-bill me-1"></i>{{ $job->salary }}
                            @endif
                            @if($job->deadline)
                                &ensp;<i class="fas fa-calendar me-1"></i>
                                ينتهي {{ $job->deadline->format('Y/m/d') }}
                                @if($job->is_expired)
                                    <span class="text-danger">(منتهي)</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('company.jobs.edit', $job) }}"
                           class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="fas fa-edit me-1"></i>تعديل
                        </a>
                        <form action="{{ route('company.jobs.toggle', $job) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm btn-outline-{{ $job->status === 'active' ? 'warning' : 'success' }} rounded-pill px-3">
                                <i class="fas fa-{{ $job->status === 'active' ? 'pause' : 'play' }} me-1"></i>
                                {{ $job->status === 'active' ? 'إيقاف' : 'تفعيل' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- إحصائيات الطلبات --}}
    <div class="col-12">
        <div class="row g-2 mb-3">
            @foreach([
                ['label'=>'إجمالي', 'value'=>$applicationStats['total'], 'color'=>'blue'],
                ['label'=>'جديدة',  'value'=>$applicationStats['pending'],'color'=>'orange'],
                ['label'=>'مختصرة', 'value'=>$applicationStats['shortlisted'],'color'=>'purple'],
                ['label'=>'مقبولة', 'value'=>$applicationStats['accepted'],'color'=>'green'],
            ] as $s)
            <div class="col-6 col-md-3">
                <div class="stat-card stat-{{ $s['color'] }}" style="padding:1rem">
                    <div class="stat-value" style="font-size:1.4rem">{{ $s['value'] }}</div>
                    <div class="stat-label">{{ $s['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- قائمة المتقدمين --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users me-2 text-primary"></i>المتقدمون على هذه الوظيفة
            </div>

            @if($job->applications->isEmpty())
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-user-slash fa-3x mb-3 d-block" style="opacity:.2"></i>
                    لا يوجد متقدمون على هذه الوظيفة حتى الآن
                </div>
            @else
                {{-- Desktop --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>المتقدم</th><th>تاريخ التقديم</th>
                                <th>CV</th><th>الحالة</th><th>تغيير الحالة</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($job->applications as $app)
                            <tr class="{{ $app->status === 'pending' ? 'table-warning bg-opacity-10' : '' }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $app->user->avatar_url }}"
                                             style="width:36px;height:36px;border-radius:50%;object-fit:cover" alt="">
                                        <div>
                                            <div class="fw-semibold" style="font-size:.88rem">
                                                {{ $app->user->display_name }}
                                            </div>
                                            <div class="text-muted" style="font-size:.75rem">
                                                {{ $app->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted" style="font-size:.8rem">
                                    {{ $app->applied_at?->format('Y/m/d') }}<br>
                                    <small>{{ $app->applied_at?->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @if($app->cv_url)
                                        <a href="{{ $app->cv_url }}" target="_blank"
                                           class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $app->status_color }}">
                                        {{ $app->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('company.applications.updateStatus', $app) }}"
                                          method="POST">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm"
                                                style="width:130px;font-size:.78rem"
                                                onchange="this.form.submit()">
                                            @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'مختصرة','interview'=>'مقابلة','accepted'=>'مقبول','rejected'=>'مرفوض'] as $v=>$l)
                                                <option value="{{ $v }}" {{ $app->status===$v?'selected':'' }}>{{ $l }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <a href="{{ route('company.applications.show', $app) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-3"
                                       style="font-size:.78rem">ملف كامل</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile --}}
                <div class="d-md-none p-3">
                    @foreach($job->applications as $app)
                    <div class="card mb-2">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <img src="{{ $app->user->avatar_url }}"
                                     style="width:40px;height:40px;border-radius:50%;object-fit:cover" alt="">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold" style="font-size:.88rem">{{ $app->user->display_name }}</div>
                                    <div class="text-muted" style="font-size:.75rem">
                                        {{ $app->applied_at?->diffForHumans() }}
                                    </div>
                                </div>
                                <span class="badge bg-{{ $app->status_color }}">{{ $app->status_label }}</span>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <form action="{{ route('company.applications.updateStatus', $app) }}"
                                      method="POST" class="flex-grow-1">
                                    @csrf @method('PATCH')
                                    <select name="status" class="form-select form-select-sm"
                                            onchange="this.form.submit()">
                                        @foreach(['pending'=>'قيد المراجعة','viewed'=>'تمت المشاهدة','shortlisted'=>'مختصرة','interview'=>'مقابلة','accepted'=>'مقبول','rejected'=>'مرفوض'] as $v=>$l)
                                            <option value="{{ $v }}" {{ $app->status===$v?'selected':'' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="{{ route('company.applications.show', $app) }}"
                                   class="btn btn-sm btn-outline-primary rounded-pill px-2 flex-shrink-0">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
