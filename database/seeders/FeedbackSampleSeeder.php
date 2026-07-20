<?php

namespace Database\Seeders;

use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Sample student/patron feedback for /feedbacks.
 *
 * Run: php artisan db:seed --class=FeedbackSampleSeeder
 */
class FeedbackSampleSeeder extends Seeder
{
    private const TZ = 'Asia/Manila';

    public function run(): void
    {
        $entries = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@student.edu.ph',
                'comments' => 'The study area on the second floor is much quieter now. Thank you for adding more power outlets near the windows.',
                'days_ago' => 1,
                'hour' => 16,
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@student.edu.ph',
                'comments' => 'OPAC search works well, but it would help if reserved books showed an estimated ready date on the hold confirmation screen.',
                'days_ago' => 2,
                'hour' => 11,
            ],
            [
                'name' => null,
                'email' => null,
                'comments' => 'Air conditioning in the reference section was too cold yesterday afternoon. Could staff adjust it during peak hours?',
                'days_ago' => 3,
                'hour' => 14,
            ],
            [
                'name' => 'Ana Reyes',
                'email' => 'ana.reyes@student.edu.ph',
                'comments' => 'Circulation desk staff were very helpful when I had a question about renewing my borrowed books. Fast and friendly service.',
                'days_ago' => 4,
                'hour' => 9,
            ],
            [
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.mendoza@student.edu.ph',
                'comments' => 'Please extend library hours during finals week. Many of us need a safe place to study after 6 PM.',
                'days_ago' => 5,
                'hour' => 18,
            ],
            [
                'name' => 'Patricia Lim',
                'email' => 'patricia.lim@student.edu.ph',
                'comments' => 'The new ebook collection is great. I only wish the download link in the email notification opened directly to the title.',
                'days_ago' => 6,
                'hour' => 10,
            ],
            [
                'name' => null,
                'email' => 'feedback.only@student.edu.ph',
                'comments' => 'Restroom on the ground floor needs more frequent cleaning, especially on busy days.',
                'days_ago' => 7,
                'hour' => 13,
            ],
            [
                'name' => 'Rico Villanueva',
                'email' => 'rico.villanueva@student.edu.ph',
                'comments' => 'Gate terminal scan is quick. The logout feedback popup is a nice touch — keeps the process simple.',
                'days_ago' => 8,
                'hour' => 17,
            ],
            [
                'name' => 'Sofia Garcia',
                'email' => 'sofia.garcia@student.edu.ph',
                'comments' => 'Could the library add more copies of thesis references for BSIT capstone topics? Several titles are always on loan.',
                'days_ago' => 10,
                'hour' => 15,
            ],
            [
                'name' => 'Miguel Torres',
                'email' => null,
                'comments' => 'Wi‑Fi drops sometimes near the periodicals section. Otherwise the space is comfortable for group work.',
                'days_ago' => 12,
                'hour' => 12,
            ],
            [
                'name' => 'Elena Cruz',
                'email' => 'elena.cruz@student.edu.ph',
                'comments' => 'Thank you for the SMS reminders about overdue books. It saved me from a fine last week.',
                'days_ago' => 14,
                'hour' => 8,
            ],
            [
                'name' => 'Anonymous',
                'email' => null,
                'comments' => 'Suggestion: post clearer signage for where to return books after hours. I was unsure which drop box to use.',
                'days_ago' => 18,
                'hour' => 19,
            ],
            [
                'name' => 'James Ong',
                'email' => 'james.ong@student.edu.ph',
                'comments' => 'The catalog filters by program are useful. A “recently added” shelf near the entrance would be even better.',
                'days_ago' => 21,
                'hour' => 11,
            ],
            [
                'name' => 'Liza Fernandez',
                'email' => 'liza.fernandez@student.edu.ph',
                'comments' => 'Reservation pickup was smooth. Staff had my hold ready within two minutes of scanning my ID.',
                'days_ago' => 25,
                'hour' => 14,
            ],
            [
                'name' => null,
                'email' => null,
                'comments' => 'Overall happy with library services this semester. Keep up the good work.',
                'days_ago' => 30,
                'hour' => 16,
            ],
        ];

        $created = 0;

        foreach ($entries as $entry) {
            $submittedAt = Carbon::now(self::TZ)
                ->subDays($entry['days_ago'])
                ->setTime($entry['hour'], ($created * 7) % 60, 0);

            Feedback::create([
                'name' => $entry['name'],
                'email' => $entry['email'],
                'comments' => $entry['comments'],
                'created_at' => $submittedAt,
                'updated_at' => $submittedAt,
            ]);

            $created++;
        }

        $this->command?->info("Seeded {$created} feedback record(s) for /feedbacks.");
    }
}
