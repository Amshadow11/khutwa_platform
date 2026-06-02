<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Jobs\AI\GenerateCoverLetterJob;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CoverLetterController extends Controller
{
    /**
     * POST /ai/cover-letter
     *
     * Body:
     *   job_id: required
     *   tone:   optional (professional|formal|friendly|concise)
     *
     * Returns: JSON مع نص الرسالة
     */
    public function generate(Request $request): JsonResponse
    {
        $request->validate([
            'job_id' => ['required', 'exists:jobs,id'],
            'tone'   => ['sometimes', 'in:professional,formal,friendly,concise'],
        ], [
            'job_id.required' => 'يرجى تحديد الوظيفة',
            'job_id.exists'   => 'الوظيفة غير موجودة',
        ]);

        $user = Auth::guard('web')->user();
        $job = Job::with('company')->findOrFail($request->job_id);
        $requestId = (string) Str::uuid();

        Cache::put("ai:cover-letter:{$requestId}", [
            'status' => 'queued',
            'job' => [
                'title' => $job->title,
                'company' => $job->company->company_name,
            ],
        ], now()->addDay());

        GenerateCoverLetterJob::dispatch(
            requestId: $requestId,
            userId: $user->id,
            jobId: $job->id,
            tone: $request->tone ?? 'professional',
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب إنشاء رسالة التغطية للمعالجة.',
            'data' => [
                'queued' => true,
                'request_id' => $requestId,
            ],
        ], 202);
    }

    public function show(string $requestId): JsonResponse
    {
        $payload = Cache::get("ai:cover-letter:{$requestId}");

        abort_unless($payload, 404);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }
}
