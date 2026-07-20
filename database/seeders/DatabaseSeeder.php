<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\MarcFrameworkSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MarcFrameworkSeeder::class,
            ProspectusSeeder::class,
            EmployeeSampleSeeder::class,
            StudentSampleSeeder::class,
            AttendanceSampleSeeder::class,
            FeedbackSampleSeeder::class,
            BookSampleSeeder::class,
            LibraryHoldingsReportSampleSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'fname' => 'Test',
                'lname' => 'User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $this->command?->info('Database seeded: MARC framework, programs, students, attendance logs, feedback, books, test user (test@example.com).');
    }
}
