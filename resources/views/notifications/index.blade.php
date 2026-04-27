@extends('layouts.app')
@section('title', 'الإشعارات')

@push('styles')
<style>
    .notif-page { padding: 1.5rem 0 3rem; }
    .notif-item {
        display: flex; align-items: flex-start; gap: .85rem;
        padding: .9rem 1.25rem;
        border-bottom: 1px solid #f5f5f5;
        transition: background .15s; position: relative;
    }
    .notif-item.unread { background: #f0f5ff; }
    .notif-item:hover  { background: #fafbff; }
    .notif-icon {
        width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .notif-icon.application  { background: rgba(44,90,160,.1); color: #2C5AA0; }
    .notif-icon.status       { background: rgba(40,167,69,.1); color: #28a745; }
    .notif-icon.message      { background: rgba(248,181,0,.1); color: #F8B500; }
    .notif-icon.default      { background: #f0f0f0; color: #888; }
    .notif-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #2C5AA0; position: absolute;
        top: 1.1rem; left: 1.1rem;
    }
    .notif-body  { font-size: .88rem; color: #333; line-height: 1.5; }
    .notif-time  { font-size: .72rem; color: #bbb; margin-top: .25rem; }
    .mark-all-btn {
        font-size: .82rem; color: #2C5AA0;
        text-decoration: none; cursor: pointer;
    }
    .mark-all-btn:hover { text-decoration: underline; }
    .empty-state { text-align: center; padding: 4rem 1rem; color: #aaa; }
    .empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; opacity: .25; }
</style>
@endpush

@section('content')
<div class="notif-page">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-7">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h4 class="fw-bold mb-0">الإشعارات</h4>
            @if($unreadCount > 0)
                <small class="text-primary">{{ $unreadCount }} غير مقروء</small>
            @endif
        </div>
        @if($unreadCount > 0)
            <form action="{{ route('notifications.markAll') }}" method="POST">
                @csrf
                <button type="submit" class="mark-all-btn btn btn-link p-0">
                    <i class="fas fa-check-double me-1"></i>تعليم الكل مقروء
                </button>
            </form>
        @endif
    </div>

    <div class="card" style="border:none;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden">

        @if($notifications->isEmpty())
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                لا توجد إشعارات
            </div>
        @else
            @foreach($notifications as $notif)
            @php
                $data    = $notif->data;
                $type    = $data['type'] ?? 'default';
                $isUnread= is_null($notif->read_at);

                $iconClass = match($type) {
                    'application_received' => 'application',
                    'status_changed'       => 'status',
                    'new_message'          => 'message',
                    default                => 'default',
                };
                $icon = match($type) {
                    'application_received' => 'fas fa-user-plus',
                    'status_changed'       => 'fas fa-tasks',
                    'new_message'          => 'fas fa-comment',
                    default                => 'fas fa-bell',
                };
            @endphp

            <a href="{{ route('notifications.read', $notif->id) }}"
               class="notif-item text-decoration-none {{ $isUnread ? 'unread' : '' }}">

                @if($isUnread)
                    <span class="notif-dot"></span>
                @endif

                <div class="notif-icon {{ $iconClass }}">
                    <i class="{{ $icon }}"></i>
                </div>

                <div class="flex-grow-1">
                    <div class="notif-body">{{ $data['message'] ?? '—' }}</div>
                    <div class="notif-time">
                        <i class="fas fa-clock me-1"></i>
                        {{ $notif->created_at->diffForHumans() }}
                    </div>
                </div>

                <i class="fas fa-angle-left text-muted" style="font-size:.8rem;flex-shrink:0;margin-top:.25rem"></i>
            </a>
            @endforeach

            <div class="p-3 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>

</div>
</div>
</div>
</div>
@endsection
