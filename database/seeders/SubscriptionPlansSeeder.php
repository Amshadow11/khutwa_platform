<?php

namespace Database\Seeders;

use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            // ==============================
            // الخطة المجانية
            // ==============================
            [
                'plan' => [
                    'name'          => 'مجاني',
                    'slug'          => 'free',
                    'description'   => 'للشركات الناشئة والصغيرة',
                    'price'         => 0,
                    'billing_cycle' => 'monthly',
                    'trial_days'    => 0,
                    'sort_order'    => 1,
                    'is_active'     => true,
                    'is_public'     => true,
                ],
                'features' => [
                    // ── الميزات الأساسية ──
                    'max_jobs_per_month' => '2',
                    'featured_jobs'      => '0',
                    'urgent_jobs'        => 'false',
                    'analytics'          => 'false',
                    'ai_matching'        => 'false',
                    'api_access'         => 'false',
                    'team_members'       => '1',
                    'messaging_limit'    => '20',
                    'cv_downloads'       => '10',
                    // ── AI Features ──
                    'ai_cover_letter_limit'    => '3',     // 3 رسائل/شهر مجاناً
                    'ai_cv_parser'             => 'true',  // مجاني للجميع
                    'ai_job_description'       => 'false',
                    'ai_job_matching'          => 'false',
                    'ai_screening_questions'   => 'false',
                    'ai_auto_filtering'        => 'false',
                    'ai_smart_search'          => 'true',  // مجاني للجميع
                ],
            ],

            // ==============================
            // الخطة الأساسية
            // ==============================
            [
                'plan' => [
                    'name'          => 'أساسي',
                    'slug'          => 'basic',
                    'description'   => 'للشركات النشطة في التوظيف',
                    'price'         => 29.00,
                    'billing_cycle' => 'monthly',
                    'trial_days'    => 7,
                    'sort_order'    => 2,
                    'is_active'     => true,
                    'is_public'     => true,
                ],
                'features' => [
                    'max_jobs_per_month' => '10',
                    'featured_jobs'      => '2',
                    'urgent_jobs'        => 'true',
                    'analytics'          => 'false',
                    'ai_matching'        => 'false',
                    'api_access'         => 'false',
                    'team_members'       => '3',
                    'messaging_limit'    => '100',
                    'cv_downloads'       => '50',
                    // ── AI Features ──
                    'ai_cover_letter_limit'    => '20',    // 20 رسالة/شهر
                    'ai_cv_parser'             => 'true',
                    'ai_job_description'       => 'true',  // ✅ متاح
                    'ai_job_matching'          => 'false',
                    'ai_screening_questions'   => 'true',  // ✅ متاح
                    'ai_auto_filtering'        => 'false',
                    'ai_smart_search'          => 'true',
                ],
            ],

            // ==============================
            // الخطة الاحترافية
            // ==============================
            [
                'plan' => [
                    'name'          => 'احترافي',
                    'slug'          => 'pro',
                    'description'   => 'للشركات الكبيرة والمؤسسات',
                    'price'         => 79.00,
                    'billing_cycle' => 'monthly',
                    'trial_days'    => 14,
                    'sort_order'    => 3,
                    'is_active'     => true,
                    'is_public'     => true,
                ],
                'features' => [
                    'max_jobs_per_month' => '-1',
                    'featured_jobs'      => '10',
                    'urgent_jobs'        => 'true',
                    'analytics'          => 'true',
                    'ai_matching'        => 'true',
                    'api_access'         => 'false',
                    'team_members'       => '10',
                    'messaging_limit'    => '-1',
                    'cv_downloads'       => '-1',
                    // ── AI Features ──
                    'ai_cover_letter_limit'    => '-1',    // غير محدود
                    'ai_cv_parser'             => 'true',
                    'ai_job_description'       => 'true',
                    'ai_job_matching'          => 'true',  // ✅ متاح
                    'ai_screening_questions'   => 'true',
                    'ai_auto_filtering'        => 'true',  // ✅ متاح
                    'ai_smart_search'          => 'true',
                ],
            ],

            // ==============================
            // الخطة المؤسسية
            // ==============================
            [
                'plan' => [
                    'name'          => 'مؤسسي',
                    'slug'          => 'enterprise',
                    'description'   => 'حلول مخصصة للمؤسسات الكبرى',
                    'price'         => 199.00,
                    'billing_cycle' => 'monthly',
                    'trial_days'    => 14,
                    'sort_order'    => 4,
                    'is_active'     => true,
                    'is_public'     => true,
                ],
                'features' => [
                    'max_jobs_per_month' => '-1',
                    'featured_jobs'      => '-1',
                    'urgent_jobs'        => 'true',
                    'analytics'          => 'true',
                    'ai_matching'        => 'true',
                    'api_access'         => 'true',
                    'team_members'       => '-1',
                    'messaging_limit'    => '-1',
                    'cv_downloads'       => '-1',
                    // ── AI Features ──
                    'ai_cover_letter_limit'    => '-1',
                    'ai_cv_parser'             => 'true',
                    'ai_job_description'       => 'true',
                    'ai_job_matching'          => 'true',
                    'ai_screening_questions'   => 'true',
                    'ai_auto_filtering'        => 'true',
                    'ai_smart_search'          => 'true',
                ],
            ],
        ];

        foreach ($plans as $data) {
            $plan = SubscriptionPlan::updateOrCreate(
                ['slug' => $data['plan']['slug']],
                $data['plan']
            );

            foreach ($data['features'] as $key => $value) {
                PlanFeature::updateOrCreate(
                    ['plan_id' => $plan->id, 'feature_key' => $key],
                    ['feature_value' => $value]
                );
            }
        }

        $this->command->info('✅ تم إنشاء خطط الاشتراك الأربع مع AI features بنجاح');
    }
}