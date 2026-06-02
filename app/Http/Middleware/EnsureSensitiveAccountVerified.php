<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSensitiveAccountVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = Auth::guard('company')->user() ?: Auth::guard('web')->user();

        if ($actor instanceof User && ! $actor->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'يجب تفعيل البريد الإلكتروني أولاً.'], 403)
                : redirect()->route('verification.notice');
        }

        if ($actor instanceof Company && (! $actor->is_verified || $actor->status !== 'active')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'حساب الشركة غير مفعل بعد.'], 403)
                : redirect()->route('company.dashboard')
                    ->with('error', 'حساب الشركة قيد المراجعة من الإدارة.');
        }

        return $next($request);
    }
}
