<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * يضيف HTTP Security Headers على كل Response.
     *
     * يحل مشكلة غياب أي Security Headers في المشروع الأصلي.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // منع تضمين الموقع في iframe (حماية من Clickjacking)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // منع المتصفح من تخمين نوع المحتوى (MIME Sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // تفعيل XSS Filter في المتصفحات القديمة
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // التحكم في الـ Referrer المُرسَل عند التنقل
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // تعطيل صلاحيات غير ضرورية
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS — إجبار HTTPS في Production فقط
        if (app()->isProduction()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
