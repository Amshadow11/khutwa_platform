<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * يُحدد المستخدم الحالي (شركة أو باحث عمل).
     */
    private function authUser(): object
    {
        return Auth::guard('company')->check()
            ? Auth::guard('company')->user()
            : Auth::guard('web')->user();
    }

    /**
     * GET /notifications
     * قائمة كل الإشعارات.
     */
    public function index(): View
    {
        $user = $this->authUser();

        $notifications = $user->notifications()->paginate(20);
        $unreadCount   = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * GET /notifications/{id}
     * تعليم إشعار كمقروء والتوجيه لرابطه.
     */
    public function read(string $id): RedirectResponse
    {
        $user         = $this->authUser();
        $notification = $user->notifications()->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        return $url
            ? redirect($url)
            : redirect()->route('notifications.index');
    }

    /**
     * POST /notifications/mark-all
     * تعليم كل الإشعارات كمقروءة.
     */
    public function markAll(): RedirectResponse
    {
        $this->authUser()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة');
    }
}
