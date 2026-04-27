@extends('layouts.app')
@section('title', 'الرسائل')

@push('styles')
<style>
    .messages-page { padding: 1.5rem 0 3rem; }
    .conv-item {
        display: flex; align-items: center; gap: .85rem;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid #f5f5f5;
        text-decoration: none; color: inherit;
        transition: background .15s;
        position: relative;
    }
    .conv-item:hover    { background: #fafbff; }
    .conv-item.unread   { background: #f0f5ff; }
    .conv-avatar {
        width: 48px; height: 48px; border-radius: 50%;
        object-fit: cover; flex-shrink: 0;
        background: #e9ecef;
    }
    .conv-name   { font-weight: 700; font-size: .9rem; color: #222; }
    .conv-job    { font-size: .75rem; color: #2C5AA0; margin-top: .1rem; }
    .conv-preview{
        font-size: .78rem; color: #888;
        overflow: hidden; text-overflow: ellipsis;
        white-space: nowrap; max-width: 200px;
    }
    .conv-preview.unread-text { color: #333; font-weight: 600; }
    .conv-time   { font-size: .7rem; color: #bbb; white-space: nowrap; flex-shrink: 0; }
    .conv-badge  {
        background: #2C5AA0; color: #fff;
        font-size: .65rem; min-width: 18px; height: 18px;
        border-radius: 9px; display: flex;
        align-items: center; justify-content: center;
        padding: 0 5px; font-weight: 700; flex-shrink: 0;
    }
    .empty-state { text-align: center; padding: 4rem 1rem; color: #aaa; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .25; }
</style>
@endpush

@section('content')
<div class="messages-page">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold mb-0">الرسائل</h4>
            @if($totalUnread > 0)
                <small class="text-primary">{{ $totalUnread }} رسالة غير مقروءة</small>
            @else
                <small class="text-muted">{{ $conversations->total() }} محادثة</small>
            @endif
        </div>
    </div>

    <div class="card" style="border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden">

        @if($conversations->isEmpty())
            <div class="empty-state">
                <i class="fas fa-comment-slash"></i>
                لا توجد محادثات بعد
                @if($auth['type'] === \App\Models\User::class)
                    <div class="mt-3">
                        <a href="{{ route('jobs.index') }}"
                           class="btn btn-primary rounded-pill px-4 btn-sm">
                            تصفح الوظائف وتواصل مع الشركات
                        </a>
                    </div>
                @endif
            </div>
        @else
            @foreach($conversations as $conv)
            @php
                $isCompany  = $auth['type'] === \App\Models\Company::class;
                $unreadCount= $isCompany ? $conv->company_unread : $conv->user_unread;
                $otherParty = $isCompany ? $conv->user : $conv->company;
                $avatar     = $isCompany
                    ? ($conv->user->avatar_url ?? asset('images/default-avatar.png'))
                    : ($conv->company->logo_url ?? asset('images/default-company.png'));
                $name       = $isCompany
                    ? ($conv->user->display_name ?? '—')
                    : ($conv->company->company_name ?? '—');
            @endphp
            <a href="{{ route('messages.show', $conv) }}"
               class="conv-item {{ $unreadCount > 0 ? 'unread' : '' }}">

                <img src="{{ $avatar }}" class="conv-avatar" alt="">

                <div class="flex-grow-1 overflow-hidden">
                    <div class="conv-name">{{ $name }}</div>
                    @if($conv->job)
                        <div class="conv-job">
                            <i class="fas fa-briefcase me-1"></i>{{ $conv->job->title }}
                        </div>
                    @endif
                    <div class="conv-preview {{ $unreadCount > 0 ? 'unread-text' : '' }}">
                        {{ $conv->last_message ?? 'ابدأ المحادثة...' }}
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="conv-time">
                        {{ $conv->last_message_at?->diffForHumans(null, true) ?? '' }}
                    </span>
                    @if($unreadCount > 0)
                        <span class="conv-badge">{{ $unreadCount }}</span>
                    @endif
                </div>
            </a>
            @endforeach

            <div class="p-3 d-flex justify-content-center">
                {{ $conversations->links() }}
            </div>
        @endif

    </div>

</div>
</div>
</div>
</div>
@endsection
