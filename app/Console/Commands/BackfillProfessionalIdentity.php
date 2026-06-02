<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Profile\ProfileCompletenessService;
use App\Services\Profile\ProfileSearchDocumentBuilder;
use App\Services\Profile\SkillNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillProfessionalIdentity extends Command
{
    protected $signature = 'profile:backfill-professional-identity {--user-id= : Backfill a single user}';

    protected $description = 'Backfill structured professional identity data from legacy user profile fields.';

    public function handle(
        SkillNormalizer $skills,
        ProfileCompletenessService $completeness,
        ProfileSearchDocumentBuilder $searchDocument,
    ): int {
        $query = User::query();

        if ($this->option('user-id')) {
            $query->whereKey($this->option('user-id'));
        }

        $count = 0;

        $query->chunkById(100, function ($users) use ($skills, $completeness, $searchDocument, &$count) {
            foreach ($users as $user) {
                $profile = $user->professionalProfile()->firstOrCreate([]);

                $profile->fill([
                    'headline' => $profile->headline ?: Str::limit($user->bio ?? '', 160, ''),
                ])->save();

                foreach ($this->splitLegacySkills($user->skills) as $index => $skillName) {
                    $skill = $skills->findOrCreate($skillName, ['source' => 'legacy']);

                    $user->canonicalSkills()->syncWithoutDetaching([
                        $skill->id => [
                            'source' => 'legacy',
                            'sort_order' => $index + 1,
                        ],
                    ]);
                }

                $completeness->refresh($user);
                $searchDocument->refresh($user);
                $count++;
            }
        });

        $this->info("Backfilled professional identity for {$count} user(s).");

        return self::SUCCESS;
    }

    private function splitLegacySkills(?string $skills): array
    {
        if (! $skills) {
            return [];
        }

        return collect(preg_split('/[,،\n]+/u', $skills) ?: [])
            ->map(fn ($skill) => trim($skill))
            ->filter()
            ->unique(fn ($skill) => mb_strtolower($skill))
            ->values()
            ->all();
    }
}
