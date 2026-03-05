<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the initial admin account.
     *
     * IMPORTANT: The original hardcoded admin/admin123 credentials have been
     * removed. This seeder creates a secure default admin. After first login,
     * change the password immediately via the Settings page.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@hotelks.com',
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'HotelKS@2024!Secure')),
                'role'     => 'super_admin',
            ]
        );

        $this->command->info('Default admin created. Username: admin');
        $this->command->warn('Set ADMIN_DEFAULT_PASSWORD in .env to customise the initial password.');
    }
}
