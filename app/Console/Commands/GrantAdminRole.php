<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class GrantAdminRole extends Command
{
    protected $signature = 'users:grant-admin {email : User email address}';

    protected $description = 'Grant the admin role to an existing web user.';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->info("Admin role granted to {$user->email}.");

        return self::SUCCESS;
    }
}
