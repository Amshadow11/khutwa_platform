<?php

namespace App\Actions\Profile;

use App\Models\Skill;
use App\Models\User;
use App\Services\Profile\SkillNormalizer;

class UpsertUserSkillAction
{
    public function __construct(
        private readonly SkillNormalizer $skillNormalizer,
        private readonly RefreshProfessionalProfileAction $refreshProfile,
    ) {
    }

    public function execute(User $user, array $data): Skill
    {
        $skill = $this->skillNormalizer->findOrCreate($data['name']);

        $user->canonicalSkills()->syncWithoutDetaching([
            $skill->id => [
                'proficiency_level' => $data['proficiency_level'] ?? null,
                'proficiency_score' => $data['proficiency_score'] ?? null,
                'years_experience' => $data['years_experience'] ?? null,
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'source' => 'manual',
                'sort_order' => $data['sort_order'] ?? 0,
            ],
        ]);

        $skill->increment('usage_count');
        $this->refreshProfile->execute($user->fresh());

        return $skill->fresh();
    }
}
