<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create or update admin user
        $user = User::updateOrCreate(
            ['username' => 'umdsoft'],
            [
                'telegram_id' => 999999999,
                'first_name' => 'Umidbek',
                'last_name' => 'Admin',
                'email' => 'umdsoft@ishtop.uz',
                'password' => 'Umidbek19952812',
                'phone' => '+998901234567',
                'is_verified' => true,
            ]
        );

        // Assign admin role
        if (!$user->hasRole('admin')) {
            $user->assignRole($role);
        }

        $this->command->info('Admin user created: username=umdsoft, email=umdsoft@ishtop.uz');
    }
}
