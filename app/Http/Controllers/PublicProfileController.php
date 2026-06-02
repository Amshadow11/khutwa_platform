<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(string $username, ProfileCompletenessService $completeness): View
    {
        $user = User::query()
            ->where('username', $username)
            ->active()
            ->with([
                'professionalProfile',
                'canonicalSkills',
                'experiences',
                'educations',
                'projects',
                'certifications',
                'languages',
            ])
            ->firstOrFail();

        $profile = $user->professionalProfile()->firstOrCreate([]);
        abort_if($profile->profile_visibility !== 'public', 404);

        $completion = $completeness->calculate($user);
        $canonicalUrl = route('profiles.public.show', $user->username);
        $description = str($profile->headline ?: $user->bio ?: $user->display_name)
            ->limit(155)
            ->toString();

        return view('profiles.public.show', compact('user', 'profile', 'completion', 'canonicalUrl', 'description'));
    }
}
