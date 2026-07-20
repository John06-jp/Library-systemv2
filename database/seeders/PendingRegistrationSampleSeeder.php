<?php

namespace Database\Seeders;

use App\Models\PendingEmployee;
use App\Models\PendingStudent;
use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PendingRegistrationSampleSeeder extends Seeder
{
    private const STUDENT_COUNT = 28;

    private const EMPLOYEE_COUNT = 22;

    /** @var list<string> */
    private array $courses = ['BSCS', 'BSIT', 'BEED', 'BSED', 'BSBA', 'BSN'];

    /** @var list<string> */
    private array $years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

    /** @var list<string> */
    private array $firstNames = [
        'Aaron', 'Beatrice', 'Christian', 'Diana', 'Ethan', 'Faith', 'Gabriel', 'Hannah',
        'Isaac', 'Jasmine', 'Kevin', 'Leah', 'Miguel', 'Nina', 'Oscar', 'Paula',
        'Quinn', 'Rachel', 'Samuel', 'Therese', 'Ulrich', 'Violet', 'Wesley', 'Xena',
        'Yuri', 'Zara', 'Angela', 'Benjamin',
    ];

    /** @var list<string> */
    private array $lastNames = [
        'Abad', 'Balagtas', 'Cortez', 'Domingo', 'Evangelista', 'Fernandez', 'Gutierrez',
        'Hernandez', 'Ignacio', 'Jimenez', 'Kalaw', 'Lorenzo', 'Magbanua', 'Navarro',
        'Ocampo', 'Pascual', 'Quintos', 'Rivera', 'Salazar', 'Tolentino', 'Urbano',
        'Valdez', 'Wong', 'Yap', 'Zamora', 'Alcantara', 'Bermudez', 'Castro',
    ];

    /** @var list<string> */
    private array $designations = [
        'Instructor I',
        'Instructor II',
        'Assistant Professor',
        'Library Staff',
        'Library Assistant',
        'Program Chair',
        'College Librarian',
    ];

    public function run(): void
    {
        $programNames = Program::pluck('program_name', 'program_code');
        $courses = $programNames->isNotEmpty()
            ? $programNames->keys()->all()
            : $this->courses;

        $studentCount = 0;
        for ($i = 1; $i <= self::STUDENT_COUNT; $i++) {
            $idNumber = sprintf('PEND-26-%05d', $i);
            $course = $courses[($i - 1) % count($courses)];

            PendingStudent::updateOrCreate(
                ['id_number' => $idNumber],
                [
                    'firstname' => $this->firstNames[$i - 1],
                    'lastname' => $this->lastNames[$i - 1],
                    'middle_initial' => chr(64 + (($i % 26) ?: 26)),
                    'birthday' => Carbon::create(2002, 1, 1)->addDays($i * 11)->toDateString(),
                    'course' => $course,
                    'year' => $this->years[($i - 1) % count($this->years)],
                    'mobile_number' => sprintf('0917%07d', 5000000 + $i),
                    'address' => sprintf('%d Sample St, Test City', 100 + $i),
                    'emergency_person' => 'Emergency Contact '.$i,
                    'emergency_relationship' => $i % 2 === 0 ? 'Mother' : 'Father',
                    'emergency_number' => sprintf('0918%07d', 5000000 + $i),
                    'emergency_address' => sprintf('%d Sample St, Test City', 100 + $i),
                    'created_at' => Carbon::now()->subHours($i),
                    'updated_at' => Carbon::now()->subHours($i),
                ],
            );

            $studentCount++;
        }

        $employeeCount = 0;
        for ($i = 1; $i <= self::EMPLOYEE_COUNT; $i++) {
            $employeeId = sprintf('PEND-FAC-26-%03d', $i);
            $program = $courses[($i - 1) % count($courses)];
            $designation = $this->designations[($i - 1) % count($this->designations)];

            PendingEmployee::updateOrCreate(
                ['employee_id' => $employeeId],
                [
                    'role_id' => 2,
                    'firstname' => $this->firstNames[($i + 3) % count($this->firstNames)],
                    'lastname' => $this->lastNames[($i + 5) % count($this->lastNames)],
                    'middle_initial' => chr(64 + (($i + 7) % 26 ?: 26)),
                    'department' => $programNames->get($program, $program),
                    'position' => $designation,
                    'designation' => $designation,
                    'program' => $program,
                    'year_start_work' => (string) (2015 + ($i % 10)),
                    'birth_date' => Carbon::create(1985, 1, 1)->addDays($i * 13)->toDateString(),
                    'mobile_number' => sprintf('0920%07d', 6000000 + $i),
                    'address' => sprintf('%d Faculty Ave, Test City', 200 + $i),
                    'emergency_contact_name' => 'Emergency Contact '.$i,
                    'emergency_contact_relationship' => 'Spouse',
                    'emergency_contact_number' => sprintf('0921%07d', 6000000 + $i),
                    'created_at' => Carbon::now()->subHours($i * 2),
                    'updated_at' => Carbon::now()->subHours($i * 2),
                ],
            );

            $employeeCount++;
        }

        $this->command?->info(sprintf(
            'Pending registration samples seeded: %d students, %d employees (visit /pending to test pagination).',
            $studentCount,
            $employeeCount,
        ));
        $this->command?->info('Try: /pending?tab=employees  |  search "Aaron" or "Instructor"');
    }
}
