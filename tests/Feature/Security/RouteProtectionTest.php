<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class RouteProtectionTest extends TestCase
{
    public function test_company_job_write_routes_require_company_verification_middleware(): void
    {
        $routes = collect(app('router')->getRoutes())->keyBy(fn ($route) => $route->getName());

        foreach ([
            'company.jobs.create',
            'company.jobs.store',
            'company.jobs.edit',
            'company.jobs.update',
            'company.jobs.destroy',
            'company.jobs.toggle',
            'company.jobs.matches.run',
            'company.matches.index',
        ] as $routeName) {
            $this->assertContains('company.verified', $routes[$routeName]->gatherMiddleware());
        }
    }

    public function test_sensitive_file_routes_require_signed_urls_and_verified_accounts(): void
    {
        $routes = collect(app('router')->getRoutes())->keyBy(fn ($route) => $route->getName());

        foreach ([
            'secure-files.applications.cv',
            'secure-files.applications.submitted-resume',
            'secure-files.messages.attachment',
        ] as $routeName) {
            $middleware = $routes[$routeName]->gatherMiddleware();

            $this->assertContains('signed', $middleware);
            $this->assertContains('sensitive.verified', $middleware);
        }
    }

    public function test_ai_and_apply_routes_require_verified_users(): void
    {
        $routes = collect(app('router')->getRoutes())->keyBy(fn ($route) => $route->getName());

        foreach ([
            'ai.cv.parse',
            'ai.cv.parse-update',
            'ai.cover-letter.generate',
            'jobs.apply',
        ] as $routeName) {
            $this->assertContains('verified', $routes[$routeName]->gatherMiddleware());
        }
    }
}
