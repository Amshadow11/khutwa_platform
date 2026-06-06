<?php

namespace Tests\Feature\Search;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class JobSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_job_search_filters_active_jobs_by_keyword_and_location(): void
    {
        $company = $this->createCompany();

        $matching = $this->createJob($company, [
            'title' => 'Senior Laravel Engineer',
            'description' => 'Build APIs and queues.',
            'requirements' => 'Laravel Redis MySQL',
            'location' => 'Riyadh',
        ]);

        $this->createJob($company, [
            'title' => 'Product Designer',
            'description' => 'Design mobile flows.',
            'requirements' => 'Figma',
            'location' => 'Jeddah',
        ]);

        $this->get(route('jobs.index', [
            'keyword' => 'Laravel',
            'location' => 'Riyadh',
        ]))
            ->assertOk()
            ->assertSee($matching->title)
            ->assertDontSee('Product Designer');
    }

    public function test_public_job_search_excludes_inactive_and_expired_jobs(): void
    {
        $company = $this->createCompany();

        $active = $this->createJob($company, ['title' => 'Active Backend Role']);
        $this->createJob($company, ['title' => 'Inactive Backend Role', 'is_active' => false]);
        $this->createJob($company, ['title' => 'Expired Backend Role', 'deadline' => now()->subDay()->toDateString()]);

        $this->get(route('jobs.index', ['keyword' => 'Backend']))
            ->assertOk()
            ->assertSee($active->title)
            ->assertDontSee('Inactive Backend Role')
            ->assertDontSee('Expired Backend Role');
    }

    public function test_public_job_search_filters_boolean_flags_without_marking_absent_flags_active(): void
    {
        $company = $this->createCompany();

        $remote = $this->createJob($company, [
            'title' => 'Remote Support Engineer',
            'remote_work' => true,
            'urgent' => false,
        ]);

        $this->createJob($company, [
            'title' => 'Onsite Support Engineer',
            'remote_work' => false,
            'urgent' => true,
        ]);

        $this->get(route('jobs.index', [
            'keyword' => 'Support',
            'remote_work' => '1',
        ]))
            ->assertOk()
            ->assertSee($remote->title)
            ->assertDontSee('Onsite Support Engineer');
    }

    private function createCompany(): Company
    {
        return Company::query()->create([
            'company_name' => 'Search Company',
            'email' => Str::random(10) . '@company.test',
            'password' => Hash::make('password'),
            'status' => 'active',
            'is_verified' => true,
        ]);
    }

    private function createJob(Company $company, array $overrides = []): Job
    {
        return Job::query()->create(array_merge([
            'company_id' => $company->id,
            'title' => 'Backend Engineer',
            'description' => 'Build reliable backend systems.',
            'requirements' => 'PHP Laravel SQL',
            'category' => 'job',
            'job_type' => 'full_time',
            'experience_level' => 'mid',
            'location' => 'Riyadh',
            'remote_work' => false,
            'urgent' => false,
            'status' => 'active',
            'is_active' => true,
            'deadline' => now()->addMonth()->toDateString(),
        ], $overrides));
    }
}
