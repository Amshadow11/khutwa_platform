<?php

namespace App\Events;

use App\Models\Application;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserAppliedToJob
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Application $application) {}
}
