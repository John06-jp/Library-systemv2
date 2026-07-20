<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        // Create developer user
        User::updateOrCreate(
            ['email' => 'developer@library.com'],
            [
                'fname' => 'System',
                'lname' => 'Developer',
                'password' => bcrypt('password'),
                'role' => 'developer',
            ]
        );

        $this->command?->info('Developer user created: developer@library.com / password');
    }
}