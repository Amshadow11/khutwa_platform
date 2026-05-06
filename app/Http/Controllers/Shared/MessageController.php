<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MessageController extends Controller
{
    // ========================================================
    // تحديد هوية المستخدم الحالي
    // ========================================================

    /**
     * يُرجع المستخدم الحالي (شركة أو باحث عمل) مع نوعه.
     */
    private function currentAuth(): array
    {
        if (Auth::guard('company')->check()) {
            return [
                'model'      => Auth::guard('company')->user(),
                'type'       => Company::class,
                'guard'      => 'company',
                'dashboard'  => 'company.dashboard',
            ];
        }

        return [
            'model'      => Auth::guard('web')->user(),
            'type'       => User::class,
            'guard'      => 'web',
            'dashboard'  => 'user.dashboard',
        ];
    }

    // ========================================================
    // قائمة المحادثات
    // ========================================================

    /**
     * GET /messages
     */
    public function index(): View
    {
        $auth = $this->currentAuth();

        if ($auth['type'] === Company::class) {
            $conversations = Conversation::with(['user:id,username,full_name,profile_picture', 'job:id,title'])
                ->forCompany($auth['model']->id)
                ->orderByDesc('last_message_at')
                ->paginate(20);
        } else {
            $conversations = Conversation::with(['company:id,company_name,logo', 'job:id,title'])
                ->forUser($auth['model']->id)
                ->orderByDesc('last_message_at')
                ->paginate(20);
        }

        // إجمالي الغير مقروء
        $totalUnread = $auth['type'] === Company::class
            ? Conversation::forCompany($auth['model']->id)->sum('company_unread')
            : Conversation::forUser($auth['model']->id)->sum('user_unread');

        return view('messages.index', compact('conversations', 'auth', 'totalUnread'));
    }

    // ========================================================
    // فتح محادثة
    // ========================================================

    /**
     * GET /messages/{conversation}
     */
    public function show(Conversation $conversation): View
    {
        $auth = $this->currentAuth();
        $this->authorizeConversation($conversation, $auth);

        // تحديد الغير مقروء للطرف الحالي
        $conversation->markReadFor($auth['type']);

        // جلب الرسائل مع Polymorphic sender
        $messages = $conversation->messages()
            ->with('sender')
            ->get();

        // تحديد الطرف الآخر للعرض
        $otherParty = $auth['type'] === Company::class
            ? $conversation->user
            : $conversation->company;

        return view('messages.show', compact('conversation', 'messages', 'auth', 'otherParty'));
    }

    // ========================================================
    // إنشاء محادثة جديدة أو فتح موجودة
    // ========================================================

    /**
     * POST /messages/start
     *
     * يُستخدم عند بدء محادثة من صفحة وظيفة أو ملف شركة.
     */
    public function start(Request $request): RedirectResponse
    {
        $rules = [
            'company_id' => ['sometimes', 'exists:companies,id'],
            'user_id'    => ['sometimes', 'exists:users,id'],
            'job_id'     => ['nullable', 'exists:jobs,id'],
        ];

        $request->validate($rules);

        if (Auth::guard('company')->check()) {
            $company = Auth::guard('company')->user();
            abort_unless($request->filled('user_id'), 422);

            $conversation = Conversation::firstOrCreate([
                'company_id' => $company->id,
                'user_id'    => $request->user_id,
                'job_id'     => $request->job_id,
            ]);
        } else {
            abort_unless(Auth::guard('web')->check(), 403);
            $user = Auth::guard('web')->user();
            abort_unless($request->filled('company_id'), 422);

            $conversation = Conversation::firstOrCreate([
                'company_id' => $request->company_id,
                'user_id'    => $user->id,
                'job_id'     => $request->job_id,
            ]);
        }

        return redirect()->route('messages.show', $conversation);
    }

    // ========================================================
    // إرسال رسالة
    // ========================================================

    /**
     * POST /messages/{conversation}/send
     */
    public function send(Request $request, Conversation $conversation): RedirectResponse
    {
        $auth = $this->currentAuth();
        $this->authorizeConversation($conversation, $auth);

        $request->validate([
            'body'       => ['required_without:attachment', 'nullable', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [
            'body.required_without' => 'يرجى كتابة رسالة أو إرفاق ملف',
            'attachment.mimes'      => 'الملفات المدعومة: PDF, JPG, PNG, WEBP',
            'attachment.max'        => 'حجم الملف يجب أن يكون أقل من 5MB',
        ]);

        // رفع المرفق إذا وُجد
        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;

        if ($request->hasFile('attachment')) {
            $file           = $request->file('attachment');
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $realMime = $finfo->file($file->path());

            $allowedMimes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ];

            abort_if(
                ! in_array($realMime, $allowedMimes, true),
                422,
                'نوع الملف غير مدعوم. الملفات المسموحة: PDF, JPG, PNG, WEBP'
            );
            $attachmentPath = $file->store('messages/' . date('Y/m'), 'public');
            $attachmentName = $file->getClientOriginalName();
            $attachmentType = $file->getMimeType();
        }

        // إنشاء الرسالة
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => $auth['type'],
            'sender_id'       => $auth['model']->id,
            'body'            => $request->body ?? '',
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
        ]);

        // تحديث بيانات المحادثة
        $conversation->updateLastMessage($message, $auth['type']);
        $recipient = $auth['type'] === Company::class
            ? $conversation->user
            : $conversation->company;

        if ($recipient) {
            $recipient->notify(new NewMessage($message, $conversation));
        }
        return redirect()
            ->route('messages.show', $conversation)
            ->with('success', 'تم إرسال الرسالة');
    }

    // ========================================================
    // Helper
    // ========================================================

    private function authorizeConversation(Conversation $conversation, array $auth): void
    {
        $allowed = $auth['type'] === Company::class
            ? $conversation->company_id === $auth['model']->id
            : $conversation->user_id   === $auth['model']->id;

        abort_if(! $allowed, 403, 'غير مصرح لك بالوصول لهذه المحادثة');
    }
}
