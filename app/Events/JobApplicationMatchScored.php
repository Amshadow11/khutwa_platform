<?php

namespace App\Events;

use App\Models\JobApplicationMatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobApplicationMatchScored
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly JobApplicationMatch $match) {}
}
