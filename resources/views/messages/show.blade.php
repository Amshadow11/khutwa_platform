@extends('layouts.app')
@section('title', 'محادثة')

@push('styles')
<style>
    /* ===== Layout ===== */
    .chat-page { height: calc(100vh - 64px); display: flex; flex-direction: column; }
    .chat-header {
        background: #fff; padding: .85rem 1.25rem;
        border-bottom: 1px solid #eee;
        display: flex; align-items: center; gap: .75rem;
        position: sticky; top: 64px; z-index: 50;
        box-shadow: 0 1px 6px rgba(0,0,0,.05);
    }
    .chat-header img {
        width: 42px; height: 42px; border-radius: 50%;
        object-fit: cover; border: 2px solid #f0f0f0;
    }
    .chat-header .name  { font-weight: 700; font-size: .92rem; color: #222; }
    .chat-header .sub   { font-size: .72rem; color: #2C5AA0; }

    /* ===== Messages Area ===== */
    .chat-body {
        flex: 1; overflow-y: auto; padding: 1.25rem;
        background: #f4f6fb;
        display: flex; flex-direction: column; gap: .6rem;
    }

    /* ===== Bubbles ===== */
    .bubble-wrap {
        display: flex; align-items: flex-end; gap: .5rem;
    }
    .bubble-wrap.mine     { flex-direction: row-reverse; }
    .bubble-wrap .avatar  {
        width: 30px; height: 30px; border-radius: 50%;
        object-fit: cover; flex-shrink: 0; margin-bottom: 2px;
    }
    .bubble {
        max-width: 72%; padding: .65rem .9rem;
        border-radius: 16px; font-size: .88rem; line-height: 1.6;
        word-break: break-word; position: relative;
    }
    .bubble.theirs {
        background: #fff; color: #333;
        border-radius: 16px 16px 16px 4px;
        box-shadow: 0 1px 4px rgba(0,0,0,.07);
    }
    .bubble.mine {
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        color: #fff;
        border-radius: 16px 16px 4px 16px;
    }
    .bubble-time {
        font-size: .65rem; opacity: .6; display: block;
        margin-top: .3rem;
        text-align: left;
    }
    .bubble.mine .bubble-time { text-align: right; }

    /* Attachment in bubble */
    .bubble-attachment {
        display: flex; align-items: center; gap: .5rem;
        background: rgba(255,255,255,.15); border-radius: 8px;
        padding: .5rem .75rem; margin-top: .4rem;
        text-decoration: none; font-size: .78rem;
    }
    .bubble.theirs .bubble-attachment {
        background: rgba(44,90,160,.08); color: #2C5AA0;
    }
    .bubble.mine .bubble-attachment { color: #fff; }

    /* Date separator */
    .date-sep {
        text-align: center; font-size: .72rem; color: #bbb;
        margin: .5rem 0; position: relative;
    }
    .date-sep span {
        background: #f4f6fb; padding: 0 .75rem;
        position: relative; z-index: 1;
    }
    .date-sep::before {
        content: ''; position: absolute; top: 50%; left: 0; right: 0;
        height: 1px; background: #e5e7eb;
    }

    /* ===== Input Area ===== */
    .chat-footer {
        background: #fff; padding: .75rem 1rem;
        border-top: 1px solid #eee;
        position: sticky; bottom: 0;
    }
    .chat-footer form { display: flex; gap: .5rem; align-items: flex-end; }
    .msg-input {
        flex: 1; border: 1.5px solid #e5e7eb; border-radius: 20px;
        padding: .6rem 1rem; font-size: .9rem; font-family: inherit;
        resize: none; max-height: 120px; outline: none;
        transition: border-color .2s;
    }
    .msg-input:focus { border-color: #2C5AA0; }
    .btn-send {
        width: 42px; height: 42px; border-radius: 50%;
        background: linear-gradient(135deg, #2C5AA0, #1e4085);
        border: none; color: #fff; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; transition: opacity .2s;
    }
    .btn-send:hover { opacity: .88; }
    .btn-attach {
        width: 38px; height: 38px; border-radius: 50%;
        border: 1.5px solid #e5e7eb; background: #fff;
        color: #888; cursor: pointer; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        transition: all .2s;
    }
    .btn-attach:hover { border-color: #2C5AA0; color: #2C5AA0; }

    /* Mobile padding */
    @media (max-width: 576px) {
        .chat-body { padding: .75rem; }
        .bubble { max-width: 85%; }
    }
</style>
@endpush

@section('content')
<div class="chat-page">

    {{-- Header --}}
    <div class="chat-header">
        <a href="{{ route('messages.index') }}"
           class="btn btn-sm btn-light rounded-pill px-2 me-1">
            <i class="fas fa-arrow-right"></i>
        </a>

        @php
            $isCompany = $auth['type'] === \App\Models\Company::class;
            $avatar = $isCompany
                ? ($otherParty->avatar_url ?? asset('images/default-avatar.png'))
                : ($otherParty->logo_url ?? asset('images/default-company.png'));
            $name = $isCompany
                ? ($otherParty->display_name ?? '—')
                : ($otherParty->company_name ?? '—');
        @endphp

        <img src="{{ $avatar }}" alt="">
        <div>
            <div class="name">{{ $name }}</div>
            @if($conversation->job)
                <div class="sub">
                    <i class="fas fa-briefcase me-1"></i>{{ $conversation->job->title }}
                </div>
            @endif
        </div>
    </div>

    {{-- Messages Body --}}
    <div class="chat-body" id="chatBody">

        @if($messages->isEmpty())
            <div class="text-center text-muted py-5" style="font-size:.88rem">
                <i class="fas fa-comment fa-2x mb-2 d-block" style="opacity:.2"></i>
                ابدأ المحادثة...
            </div>
        @else
            @php $lastDate = null; @endphp
            @foreach($messages as $msg)
                @php
                    $msgDate = $msg->created_at->toDateString();
                    $isMine  = ($msg->sender_type === $auth['type'] && $msg->sender_id === $auth['model']->id);
                    $senderAvatar = $isMine
                        ? ($isCompany ? $auth['model']->logo_url : $auth['model']->avatar_url)
                        : $avatar;
                @endphp

                {{-- Date Separator --}}
                @if($msgDate !== $lastDate)
                    <div class="date-sep">
                        <span>{{ $msg->created_at->isToday() ? 'اليوم' : ($msg->created_at->isYesterday() ? 'أمس' : $msg->created_at->format('Y/m/d')) }}</span>
                    </div>
                    @php $lastDate = $msgDate; @endphp
                @endif

                <div class="bubble-wrap {{ $isMine ? 'mine' : '' }}">
                    <img src="{{ $senderAvatar }}" class="avatar" alt="">

                    <div class="bubble {{ $isMine ? 'mine' : 'theirs' }}">
                        @if($msg->body)
                            {{ $msg->body }}
                        @endif

                        @if($msg->attachment_path)
                            <a href="{{ $msg->attachment_url }}"
                               target="_blank" class="bubble-attachment">
                                <i class="fas fa-{{ str_contains($msg->attachment_type ?? '', 'pdf') ? 'file-pdf' : 'file-image' }}"></i>
                                {{ $msg->attachment_name ?? 'مرفق' }}
                            </a>
                        @endif

                        <span class="bubble-time">
                            {{ $msg->created_at->format('H:i') }}
                            @if($isMine)
                                @if($msg->read_at)
                                    <i class="fas fa-check-double ms-1" style="color:rgba(255,255,255,.7)"></i>
                                @else
                                    <i class="fas fa-check ms-1" style="opacity:.5"></i>
                                @endif
                            @endif
                        </span>
                    </div>
                </div>
            @endforeach
        @endif

    </div>

    {{-- Footer - Input --}}
    <div class="chat-footer">
        @if(session('success'))
            <div class="alert alert-success py-1 px-2 mb-2"
                 style="font-size:.78rem;border-radius:8px;border:none">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('messages.send', $conversation) }}"
              method="POST" enctype="multipart/form-data" id="msgForm">
            @csrf

            {{-- Preview المرفق --}}
            <div id="attachPreview" class="mb-2 d-none">
                <span class="badge bg-light text-dark px-3 py-2"
                      style="border:1px solid #eee;border-radius:8px;font-size:.78rem">
                    <i class="fas fa-paperclip me-1"></i>
                    <span id="attachName"></span>
                    <button type="button" onclick="clearAttach()"
                            class="btn-close ms-2" style="font-size:.5rem"></button>
                </span>
            </div>

            <div class="d-flex gap-2 align-items-end">
                {{-- زر المرفقات --}}
                <label class="btn-attach mb-0" title="إرفاق ملف">
                    <i class="fas fa-paperclip" style="font-size:.85rem"></i>
                    <input type="file" name="attachment" id="attachInput"
                           accept=".pdf,.jpg,.jpeg,.png,.webp" hidden
                           onchange="showAttach(this)">
                </label>

                {{-- حقل الرسالة --}}
                <textarea name="body" id="msgInput"
                          class="msg-input"
                          placeholder="اكتب رسالتك..."
                          rows="1"
                          onkeydown="handleKey(event)"></textarea>

                {{-- زر الإرسال --}}
                <button type="submit" class="btn-send">
                    <i class="fas fa-paper-plane" style="font-size:.85rem"></i>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    window.Echo.private('conversation.{{ $conversation->id }}')
        .listen('.message.sent', function(data) {
            console.log('NEW MESSAGE:', data);

            var chatBody = document.getElementById('chatBody');
            var wrap = document.createElement('div');
            wrap.className = 'bubble-wrap';
            wrap.innerHTML =
                '<img src="{{ $avatar }}" class="avatar" alt="">' +
                '<div class="bubble theirs">' +
                    (data.body ? data.body : '') +
                    '<span class="bubble-time">' + data.created_at + '</span>' +
                '</div>';

            chatBody.appendChild(wrap);
            chatBody.scrollTop = chatBody.scrollHeight;
        });
});
</script>
@endpush