<?php

namespace App\Events;

use App\Models\Resume;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResumeGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Resume $resume) {}
}
