<?php

namespace App\Services\Profile;

use App\Models\Skill;
use Illuminate\Support\Str;

class SkillNormalizer
{
    public function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\+#\.\s-]+/u', '', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public function findOrCreate(string $name, array $attributes = []): Skill
    {
        $normalized = $this->normalize($name);
        $slug = Str::slug($normalized);

        if ($slug === '') {
            $slug = 'skill-' . substr(sha1($normalized), 0, 12);
        }

        return Skill::firstOrCreate(
            ['normalized_name' => $normalized],
            [
                'name' => trim($name),
                'slug' => $slug,
                'category' => $attributes['category'] ?? null,
                'type' => $attributes['type'] ?? 'technical',
                'is_verified' => $attributes['is_verified'] ?? false,
                'metadata' => $attributes['metadata'] ?? null,
            ]
        );
    }
}
