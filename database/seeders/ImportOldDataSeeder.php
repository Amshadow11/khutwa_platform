<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder لاستيراد البيانات الحالية من النظام القديم.
 *
 * الاستخدام:
 *   php artisan db:seed --class=ImportOldDataSeeder
 *
 * ملاحظة: يُفترض أن قاعدة البيانات القديمة (company_db) موجودة على نفس السيرفر.
 * إذا لم تكن كذلك، يمكن تشغيل SQL التحويل يدوياً.
 */
class ImportOldDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 بدء استيراد البيانات من النظام القديم...');

        $this->importCompanies();
        $this->importUsers();
        $this->importJobs();
        $this->importApplications();

        $this->command->info('✅ اكتمل استيراد البيانات بنجاح!');
    }

    // ========================================================
    // استيراد الشركات
    // ========================================================
    private function importCompanies(): void
    {
        $this->command->info('  → استيراد الشركات...');

        $old = DB::connection('old_db')->table('companies')->get();

        foreach ($old as $row) {
            Company::updateOrCreate(
                ['email' => $row->email],
                [
                    'id'               => $row->id,
                    'company_name'     => $row->company_name,
                    'email'            => $row->email,
                    'password'         => $row->password, // بالفعل password_hash — لا إعادة تشفير
                    'phone'            => $row->phone,
                    'phone_code'       => $row->phone_code ?? 'YE',
                    'logo'             => $row->logo,
                    'profile_picture'  => $row->profile_picture,
                    'description'      => $row->description,
                    'address'          => $row->address,
                    'website'          => $row->website,
                    'industry'         => $row->industry,
                    'company_size'     => $row->company_size ?? 'small',
                    'subscription_plan'=> $row->subscription_plan ?? 'free',
                    'status'           => $row->status ?? 'pending',
                    'is_verified'      => (bool) $row->is_verified,
                    'views'            => $row->views ?? 0,
                    'last_login'       => $row->last_login,
                    'created_at'       => $row->created_at,
                    'updated_at'       => $row->updated_at,
                ]
            );
        }

        $this->command->info("     تم استيراد {$old->count()} شركة");
    }

    // ========================================================
    // استيراد المستخدمين
    // ========================================================
    private function importUsers(): void
    {
        $this->command->info('  → استيراد المستخدمين...');

        $old = DB::connection('old_db')->table('users')->get();

        foreach ($old as $row) {
            User::updateOrCreate(
                ['email' => $row->email],
                [
                    'id'              => $row->id,
                    'username'        => $row->username,
                    'full_name'       => $row->full_name,
                    'email'           => $row->email,
                    'password'        => $row->password,
                    'phone'           => $row->phone,
                    'profile_picture' => $row->profile_picture,
                    'bio'             => $row->bio,
                    'address'         => $row->address,
                    'skills'          => $row->skills,
                    'experience'      => $row->experience,
                    'education'       => $row->education,
                    'linkedin_url'    => $row->linkedin_url,
                    'github_url'      => $row->github_url,
                    'portfolio_url'   => $row->portfolio_url,
                    'birth_date'      => $row->birth_date,
                    'gender'          => $row->gender,
                    'status'          => $row->status ?? 'active',
                    'is_active'       => (bool) ($row->is_active ?? 1),
                    'last_login'      => $row->last_login,
                    'created_at'      => $row->created_at,
                    'updated_at'      => $row->updated_at,
                ]
            );
        }

        $this->command->info("     تم استيراد {$old->count()} مستخدم");
    }

    // ========================================================
    // استيراد الوظائف (posts → jobs)
    // ========================================================
    private function importJobs(): void
    {
        $this->command->info('  → استيراد الوظائف...');

        $old = DB::connection('old_db')->table('posts')->get();

        foreach ($old as $row) {
            Job::updateOrCreate(
                ['id' => $row->id],
                [
                    'id'               => $row->id,
                    'company_id'       => $row->company_id,
                    'title'            => $row->title,
                    'description'      => $row->description,
                    'requirements'     => $row->requirements,
                    'benefits'         => $row->benefits,
                    // تحويل category العربي → انجليزي
                    'category'         => $row->category === 'تدريب' ? 'training' : 'job',
                    'job_type'         => $row->job_type,
                    'experience_level' => $row->experience_level,
                    'location'         => $row->location,
                    'remote_work'      => (bool) ($row->remote_work ?? 0),
                    'salary'           => $row->salary,
                    'status'           => $row->status ?? 'active',
                    'is_active'        => (bool) ($row->is_active ?? 1),
                    'featured'         => (bool) ($row->featured ?? 0),
                    'urgent'           => (bool) ($row->urgent ?? 0),
                    'deadline'         => $row->deadline,
                    'views'            => $row->views ?? 0,
                    'post_date'        => $row->post_date ?? $row->created_at,
                    'created_at'       => $row->created_at,
                    'updated_at'       => $row->updated_at,
                ]
            );
        }

        $this->command->info("     تم استيراد {$old->count()} وظيفة");
    }

    // ========================================================
    // استيراد الطلبات (requests → applications)
    // ========================================================
    private function importApplications(): void
    {
        $this->command->info('  → استيراد طلبات التقديم...');

        $old = DB::connection('old_db')->table('requests')->get();
        $imported = 0;

        foreach ($old as $row) {
            // تجاهل الطلبات التي وظيفتها أو مستخدمها غير موجودين
            $jobExists  = Job::find($row->post_id);
            $userExists = User::find($row->user_id);
            if (! $jobExists || ! $userExists) continue;

            // تجنب Duplicate (unique: job_id + user_id)
            $exists = Application::where('job_id', $row->post_id)
                                  ->where('user_id', $row->user_id)
                                  ->exists();
            if ($exists) continue;

            Application::create([
                'job_id'          => $row->post_id,
                'user_id'         => $row->user_id,
                'cover_letter'    => $row->cover_letter,
                'cv_path'         => $row->cv ?? $row->resume_path,
                'about'           => $row->about,
                'applicant_name'  => $row->name_req,
                'applicant_email' => $row->email,
                'applicant_phone' => $row->phone,
                'status'          => $row->status ?? 'pending',
                'notes'           => $row->notes,
                'applied_at'      => $row->request_date ?? $row->created_at_r,
                'created_at'      => $row->created_at_r,
                'updated_at'      => $row->created_at_r,
            ]);

            $imported++;
        }

        $this->command->info("     تم استيراد {$imported} طلب تقديم");
    }
}
