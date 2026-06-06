<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline', 180)->nullable();
            $table->string('current_title', 180)->nullable()->index();
            $table->string('current_company', 180)->nullable()->index();
            $table->string('industry', 120)->nullable()->index();
            $table->string('seniority_level', 80)->nullable()->index();
            $table->string('location_country', 120)->nullable()->index();
            $table->string('location_city', 120)->nullable()->index();
            $table->boolean('open_to_work')->default(false)->index();
            $table->string('profile_visibility', 40)->default('public')->index();
            $table->json('public_sections')->nullable();
            $table->json('preferred_job_types')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->decimal('profile_completeness_score', 5, 2)->default(0);
            $table->timestamp('profile_completed_at')->nullable();
            $table->timestamp('last_indexed_at')->nullable();
            $table->json('search_document')->nullable();
            $table->json('ai_profile_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 140);
            $table->string('normalized_name', 160)->unique();
            $table->string('slug', 180)->unique();
            $table->string('category', 120)->nullable()->index();
            $table->string('type', 60)->default('technical')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_verified')->default(false)->index();
            $table->unsignedInteger('usage_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('skill_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('alias', 160);
            $table->string('normalized_alias', 180)->index();
            $table->string('locale', 10)->nullable()->index();
            $table->timestamps();
            $table->unique(['skill_id', 'normalized_alias', 'locale']);
        });

        Schema::create('user_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('proficiency_level', 40)->nullable();
            $table->unsignedTinyInteger('proficiency_score')->nullable();
            $table->decimal('years_experience', 4, 1)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('endorsement_count')->default(0);
            $table->string('source', 60)->default('manual')->index();
            $table->decimal('confidence_score', 4, 3)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('evidence')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'skill_id']);
            $table->index(['user_id', 'is_featured', 'sort_order']);
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('native_name', 120)->nullable();
            $table->string('iso_code', 10)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('user_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained()->cascadeOnDelete();
            $table->string('proficiency_level', 40)->nullable();
            $table->unsignedTinyInteger('proficiency_score')->nullable();
            $table->boolean('is_native')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'language_id']);
        });

        Schema::create('user_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 180);
            $table->string('company_name', 180)->nullable()->index();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employment_type', 60)->nullable()->index();
            $table->string('location', 180)->nullable();
            $table->boolean('is_remote')->default(false);
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false)->index();
            $table->text('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->json('skills_snapshot')->nullable();
            $table->string('source', 60)->default('manual')->index();
            $table->decimal('confidence_score', 4, 3)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'sort_order']);
        });

        Schema::create('user_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('institution_name', 220)->index();
            $table->string('degree', 160)->nullable()->index();
            $table->string('field_of_study', 180)->nullable()->index();
            $table->string('grade')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->json('activities')->nullable();
            $table->string('source', 60)->default('manual')->index();
            $table->decimal('confidence_score', 4, 3)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'sort_order']);
        });

        Schema::create('user_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('role', 160)->nullable();
            $table->string('project_url', 500)->nullable();
            $table->string('repository_url', 500)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->json('skills_snapshot')->nullable();
            $table->json('media')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'is_featured', 'sort_order']);
        });

        Schema::create('user_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 220);
            $table->string('issuing_organization', 220)->nullable()->index();
            $table->string('credential_id', 180)->nullable();
            $table->string('credential_url', 500)->nullable();
            $table->date('issued_at')->nullable()->index();
            $table->date('expires_at')->nullable();
            $table->boolean('does_not_expire')->default(false);
            $table->string('verification_status', 40)->default('unverified')->index();
            $table->json('skills_snapshot')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'sort_order']);
        });

        Schema::create('resume_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('view_path', 220);
            $table->boolean('supports_rtl')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings_schema')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('resume_templates');
            $table->foreignId('parent_resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->foreignId('target_job_id')->nullable()->constrained('jobs')->nullOnDelete();
            $table->string('title', 180);
            $table->string('slug', 200);
            $table->string('visibility', 40)->default('private')->index();
            $table->string('public_token', 80)->nullable()->unique();
            $table->string('private_share_token', 80)->nullable()->unique();
            $table->boolean('is_default')->default(false)->index();
            $table->unsignedInteger('version_number')->default(1);
            $table->string('locale', 10)->default('ar');
            $table->string('direction', 3)->default('rtl');
            $table->text('tailored_summary')->nullable();
            $table->json('settings')->nullable();
            $table->json('profile_snapshot')->nullable();
            $table->unsignedInteger('snapshot_version')->default(1);
            $table->string('snapshot_hash', 80)->nullable()->index();
            $table->timestamp('snapshot_created_at')->nullable();
            $table->decimal('completeness_score', 5, 2)->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('last_generated_at')->nullable();
            $table->string('generated_pdf_path')->nullable();
            $table->string('pdf_status', 40)->default('pending')->index();
            $table->text('pdf_error')->nullable();
            $table->json('render_metadata')->nullable();
            $table->json('seo_metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'slug']);
        });

        Schema::create('resume_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->string('section_key', 80)->index();
            $table->string('title', 120);
            $table->string('source_type', 40)->default('profile');
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['resume_id', 'section_key']);
        });

        Schema::create('resume_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_section_id')->constrained()->cascadeOnDelete();
            $table->string('item_type', 80);
            $table->unsignedBigInteger('item_id')->nullable();
            $table->json('custom_payload')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_section_items');
        Schema::dropIfExists('resume_sections');
        Schema::dropIfExists('resumes');
        Schema::dropIfExists('resume_templates');
        Schema::dropIfExists('user_certifications');
        Schema::dropIfExists('user_projects');
        Schema::dropIfExists('user_educations');
        Schema::dropIfExists('user_experiences');
        Schema::dropIfExists('user_languages');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('user_skills');
        Schema::dropIfExists('skill_aliases');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('professional_profiles');
    }
};
