<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\ResumeTemplate;
use App\Services\Profile\SkillNormalizer;
use Illuminate\Database\Seeder;

class ProfessionalIdentitySeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLanguages();
        $this->seedResumeTemplates();
        $this->seedSkills();
    }

    private function seedLanguages(): void
    {
        foreach ([
            ['name' => 'Arabic', 'native_name' => 'العربية', 'iso_code' => 'ar'],
            ['name' => 'English', 'native_name' => 'English', 'iso_code' => 'en'],
            ['name' => 'French', 'native_name' => 'Français', 'iso_code' => 'fr'],
            ['name' => 'German', 'native_name' => 'Deutsch', 'iso_code' => 'de'],
            ['name' => 'Turkish', 'native_name' => 'Türkçe', 'iso_code' => 'tr'],
        ] as $language) {
            Language::firstOrCreate(['iso_code' => $language['iso_code']], $language);
        }
    }

    private function seedResumeTemplates(): void
    {
        foreach ([
            [
                'name' => 'Modern',
                'slug' => 'modern',
                'view_path' => 'resumes.templates.modern',
                'supports_rtl' => true,
                'settings_schema' => [
                    'accent_color' => ['type' => 'color', 'default' => '#1f6feb'],
                    'font_family' => ['type' => 'string', 'default' => 'Tajawal'],
                ],
            ],
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'view_path' => 'resumes.templates.classic',
                'supports_rtl' => true,
                'settings_schema' => [
                    'accent_color' => ['type' => 'color', 'default' => '#111827'],
                    'font_family' => ['type' => 'string', 'default' => 'Tajawal'],
                ],
            ],
            [
                'name' => 'Compact ATS',
                'slug' => 'compact',
                'view_path' => 'resumes.templates.compact',
                'supports_rtl' => true,
                'settings_schema' => [
                    'single_column' => ['type' => 'boolean', 'default' => true],
                    'compact' => ['type' => 'boolean', 'default' => true],
                ],
            ],
            [
                'name' => 'Modern Professional',
                'slug' => 'modern-professional',
                'view_path' => 'resumes.templates.modern',
                'supports_rtl' => true,
                'settings_schema' => [
                    'accent_color' => ['type' => 'color', 'default' => '#1f6feb'],
                    'font_family' => ['type' => 'string', 'default' => 'Tajawal'],
                ],
            ],
            [
                'name' => 'ATS Clean',
                'slug' => 'ats-clean',
                'view_path' => 'resumes.templates.compact',
                'supports_rtl' => true,
                'settings_schema' => [
                    'single_column' => ['type' => 'boolean', 'default' => true],
                    'compact' => ['type' => 'boolean', 'default' => true],
                ],
            ],
        ] as $template) {
            ResumeTemplate::firstOrCreate(['slug' => $template['slug']], $template);
        }
    }

    private function seedSkills(): void
    {
        $normalizer = app(SkillNormalizer::class);

        $skills = [
            ['PHP', 'technical', 'Backend'],
            ['Laravel', 'technical', 'Backend'],
            ['MySQL', 'technical', 'Database'],
            ['JavaScript', 'technical', 'Frontend'],
            ['Vue.js', 'technical', 'Frontend'],
            ['React', 'technical', 'Frontend'],
            ['Tailwind CSS', 'technical', 'Frontend'],
            ['REST APIs', 'technical', 'API'],
            ['Project Management', 'business', 'Management'],
            ['Communication', 'soft', 'Soft Skills'],
            ['Problem Solving', 'soft', 'Soft Skills'],
            ['Leadership', 'soft', 'Soft Skills'],
            ['Sales', 'business', 'Business'],
            ['Digital Marketing', 'business', 'Marketing'],
            ['Accounting', 'business', 'Finance'],
        ];

        foreach ($skills as [$name, $type, $category]) {
            $normalizer->findOrCreate($name, [
                'type' => $type,
                'category' => $category,
                'is_verified' => true,
            ]);
        }
    }
}
