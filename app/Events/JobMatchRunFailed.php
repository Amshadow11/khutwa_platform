<?php

namespace App\Events;

use App\Models\JobMatchRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobMatchRunFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly JobMatchRun $run) {}
}
