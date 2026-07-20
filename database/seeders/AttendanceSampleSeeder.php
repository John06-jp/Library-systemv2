<?php

namespace Database\Seeders;

use App\Models\AttendanceLog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Sample library attendance (IN/OUT pairs) for reports and the attendance logs UI.
 *
 * Run: php artisan db:seed --class=AttendanceSampleSeeder
 */
class AttendanceSampleSeeder extends Seeder
{
    private const TZ = 'Asia/Manila';

    public function run(): void
    {
        $students = Student::query()
            ->whereIn('qrcode', [
                'S-00000001', 'S-00000002', 'S-00000003', 'S-00000004', 'S-00000005',
                'S-00000006', 'S-00000007', 'S-00000008', 'S-00000009', 'S-00000010',
            ])
            ->orderBy('qrcode')
            ->get();

        if ($students->isEmpty()) {
            $this->command?->warn('No sample students found. Run StudentSampleSeeder first.');

            return;
        }

        $visitPatterns = [
            [0, 1, 2, 3, 4],
            [0, 2, 4],
            [1, 3, 5, 6, 7, 8, 9, 10, 11, 12, 13],
            [0, 1, 4, 5, 9, 10],
            [2, 3, 6, 7, 11, 12],
            [0, 3, 6, 9, 12],
            [1, 2, 5, 8, 11],
            [0, 1, 2, 5, 6, 7, 10, 11, 12],
            [4, 8, 12],
            [0, 2, 5, 7, 9, 11, 13],
        ];

        $inHours = [7, 8, 8, 9, 9, 10];
        $inMinutes = [0, 15, 30, 45];
        $outHours = [14, 15, 16, 17, 17, 18];
        $outMinutes = [0, 10, 20, 30, 45];

        $created = 0;
        $today = Carbon::now(self::TZ)->startOfDay();

        foreach ($students as $index => $student) {
            $daysAgoList = $visitPatterns[$index] ?? [0, 2, 4];

            foreach ($daysAgoList as $dayOffset => $daysAgo) {
                $day = $today->copy()->subDays($daysAgo);

                $inAt = $day->copy()
                    ->setTime(
                        $inHours[($index + $dayOffset) % count($inHours)],
                        $inMinutes[($index + $dayOffset) % count($inMinutes)]
                    );

                $outAt = $day->copy()
                    ->setTime(
                        $outHours[($index + $dayOffset) % count($outHours)],
                        $outMinutes[($index + $dayOffset) % count($outMinutes)]
                    );

                foreach ([['IN', $inAt], ['OUT', $outAt]] as [$status, $scannedAt]) {
                    AttendanceLog::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'status' => $status,
                            'scanned_at' => $scannedAt,
                        ],
                        []
                    );
                    $created++;
                }
            }
        }

        // A few patrons currently "in" the library today (no OUT yet).
        foreach ($students->take(3) as $index => $student) {
            $scannedAt = $today->copy()->setTime(9 + $index, 5 * $index);
            AttendanceLog::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'status' => 'IN',
                    'scanned_at' => $scannedAt,
                ],
                []
            );
            $created++;
        }

        $this->command?->info("Attendance sample data seeded ({$created} IN/OUT row(s) across {$students->count()} students, last 14 days).");
    }
}
