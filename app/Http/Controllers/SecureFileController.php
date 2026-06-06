<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Message;
use App\Services\Files\PrivateFileStorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function __construct(private readonly PrivateFileStorageService $files) {}

    public function applicationCv(Application $application): StreamedResponse
    {
        $actor = Auth::guard('company')->user() ?: Auth::guard('web')->user();

        Gate::forUser($actor)->authorize('downloadCv', $application);
        abort_unless($application->cv_path, 404);

        return $this->files->download(
            $application->cv_path,
            "application-{$application->id}-cv.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function applicationSubmittedResume(Application $application): StreamedResponse
    {
        $actor = Auth::guard('company')->user() ?: Auth::guard('web')->user();

        Gate::forUser($actor)->authorize('downloadSubmittedResume', $application);
        abort_unless($application->submitted_resume_pdf_path, 404);

        return $this->files->download(
            $application->submitted_resume_pdf_path,
            "application-{$application->id}-submitted-resume.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }

    public function messageAttachment(Message $message): StreamedResponse
    {
        $actor = Auth::guard('company')->user() ?: Auth::guard('web')->user();

        Gate::forUser($actor)->authorize('downloadAttachment', $message);
        abort_unless($message->attachment_path, 404);

        return $this->files->download(
            $message->attachment_path,
            $message->attachment_name ?: "message-{$message->id}-attachment"
        );
    }
}
