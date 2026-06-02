<?php

namespace App\Http\Controllers\AI;

use App\Actions\Files\StorePrivateUploadAction;
use App\Http\Controllers\Controller;
use App\Jobs\AI\ParseCvJob;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CVParserController extends Controller
{
    public function __construct(
        private readonly StorePrivateUploadAction $storePrivateUpload,
    ) {}

    /**
     * POST /ai/cv/parse
     *
     * يستقبل: cv_path أو cv_file
     * يُرجع: JSON ببيانات الـ CV المستخرجة
     *
     * متاح لجميع المستخدمين (مجاني بدون حد)
     */
    public function parse(Request $request): JsonResponse
    {
        $request->validate([
            'cv_path'        => ['required_without:cv_file', 'nullable', 'string'],
            'cv_file'        => ['required_without:cv_path', 'nullable', 'file', 'mimes:pdf', 'max:5120'],
            'update_profile' => ['boolean'],
        ]);

        $user = Auth::guard('web')->user();

        // تحديد مسار الـ CV
        if ($request->hasFile('cv_file')) {
            $storedCv = $this->storePrivateUpload->execute(
                $request->file('cv_file'),
                'cvs/' . date('Y/m'),
                config('files.allowed_cv_mimes')
            );

            $cvPath = $storedCv->path;
        } else {
            $cvPath = $request->cv_path;

            abort_unless(
                Application::where('user_id', $user->id)->where('cv_path', $cvPath)->exists(),
                403,
                'غير مصرح لك بتحليل هذا الملف.'
            );
        }

        ParseCvJob::dispatch(
            userId: $user->id,
            cvPath: $cvPath,
            updateProfile: $request->boolean('update_profile', false),
        );

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال ملف الـ CV للمعالجة. ستظهر النتيجة بعد اكتمال التحليل.',
            'data' => [
                'queued' => true,
                'cv_path' => $cvPath,
            ],
        ], 202);
    }

    /**
     * POST /ai/cv/parse-and-update
     *
     * يحلّل الـ CV ويعبّئ الملف الشخصي تلقائياً.
     */
    public function parseAndUpdate(Request $request): JsonResponse
    {
        $request->merge(['update_profile' => true]);
        return $this->parse($request);
    }
}
