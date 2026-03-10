<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $password = config('app.admin_password', env('ADMIN_PASSWORD'));

        if (empty($password)) {
            $password = Str::random(16);
            $this->command->warn("ADMIN_PASSWORD not set in .env — generated random password: {$password}");
        }

        // Create or update admin user
        $user = User::updateOrCreate(
            ['username' => config('app.admin_username', env('ADMIN_USERNAME', 'admin'))],
            [
                'telegram_id' => (int) config('app.admin_telegram_id', env('ADMIN_TELEGRAM_ID', 0)),
                'first_name' => 'Admin',
                'last_name' => '',
                'email' => config('app.admin_email', env('ADMIN_EMAIL', 'admin@kadrgo.uz')),
                'password' => $password,
                'phone' => config('app.admin_phone', env('ADMIN_PHONE', '')),
                'is_verified' => true,
            ]
        );

        // Assign admin role
        if (!$user->hasRole('admin')) {
            $user->assignRole($role);
        }

        $this->command->info("Admin user created: username={$user->username}, email={$user->email}");
    }
}
