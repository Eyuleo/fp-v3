<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'first_name'        => 'Admin',
            'last_name'         => 'User',
            'email'             => 'admin@example.com',
            'password'          => bcrypt('password'),
            'role'              => 'admin',
            'email_verified_at' => now(),
            'is_active'         => true,
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: password');
    }
}
