<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * One test account per role for checking the dashboard locally: test-<role>@sharet.test.
 * Password comes from LOCAL_TEST_PASSWORD in .env. Refuses to run in production.
 *   php artisan db:seed --class=LocalTestUsersSeeder
 */
class LocalTestUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Refusing to create test accounts in production.');
            return;
        }

        $password = env('LOCAL_TEST_PASSWORD');
        if (! $password) {
            $this->command->error('Set LOCAL_TEST_PASSWORD in .env first.');
            return;
        }

        foreach (['super_admin', 'admin', 'operations', 'individual', 'merchant', 'business', 'techie'] as $role) {
            $user = User::updateOrCreate(
                ['email' => "test-{$role}@sharet.test"],
                ['name' => 'Test '.ucwords(str_replace('_', ' ', $role)), 'password' => Hash::make($password), 'email_verified_at' => now()]
            );
            $user->syncRoles([$role]);
            $this->command->info("test-{$role}@sharet.test");
        }
    }
}
