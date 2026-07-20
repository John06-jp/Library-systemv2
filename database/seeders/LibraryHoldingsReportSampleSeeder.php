<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Program;
use App\Models\ProgramCourse;
use App\Models\ProgramYear;
use Illuminate\Database\Seeder;

/**
 * Sample catalog data for testing the CHED-style Library Holdings Report (Report 1).
 *
 * Run: php artisan db:seed --class=LibraryHoldingsReportSampleSeeder
 */
class LibraryHoldingsReportSampleSeeder extends Seeder
{
    public function run(): void
    {
        $bsed = Program::where('program_code', 'BSED')->first();

        if (! $bsed) {
            $this->command?->warn('BSED program not found. Run ProspectusSeeder first.');

            return;
        }

        $this->seedEnglishMajorCourses($bsed);

        $copies = $this->holdingsDefinitions();
        $created = 0;
        $skipped = 0;

        foreach ($copies as $row) {
            if (Book::withTrashed()->where('accession_no', $row['accession_no'])->exists()) {
                $skipped++;

                continue;
            }

            $book = Book::create(array_merge([
                'availability' => 'Available',
                'library_name' => 'Academic Library',
                'section' => 'Main stacks',
                'cataloging_source_e' => 'rda',
            ], $row));

            $book->programs()->sync([$bsed->id]);
            $created++;
        }

        $bsedBooks = Book::query()
            ->whereHas('programs', fn ($q) => $q->where('programs.id', $bsed->id))
            ->whereNotNull('course')
            ->where('course', '!=', '')
            ->count();

        $this->command?->info("Library holdings sample: {$created} new copies seeded for BSED ({$skipped} skipped — already present).");
        $this->command?->info("BSED now has {$bsedBooks} copies with a course — ready for Library Holdings Report.");
        $this->command?->info('Test: Circulation → Library Holdings Report → BSED → suffix "MAJOR IN ENGLISH".');
    }

    protected function seedEnglishMajorCourses(Program $bsed): void
    {
        $year3 = ProgramYear::firstOrCreate(
            ['program_id' => $bsed->id, 'year_level' => 3],
            ['year_level' => 3]
        );

        $majorCourses = [
            ['ENG301', 'Introduction to Linguistics'],
            ['ENG302', 'Language, Culture and Society'],
            ['ENG303', 'Structure of English'],
            ['ENG304', 'Language Programs and Policies in Multilingual Societies'],
            ['ENG305', 'Language Learning Materials Development'],
            ['ENG306', 'Teaching and Assessment of Literature Studies'],
            ['ENG307', 'Teaching and Assessment of Macro Skills'],
            ['ENG308', 'Teaching and Assessment of Grammar'],
            ['ENG309', 'Children and Adolescent Literature'],
            ['ENG310', 'Speech and Theatre Arts'],
        ];

        foreach ($majorCourses as [$code, $name]) {
            ProgramCourse::firstOrCreate(
                ['program_year_id' => $year3->id, 'course_code' => $code],
                ['course_name' => $name]
            );
        }

        $year4 = ProgramYear::firstOrCreate(
            ['program_id' => $bsed->id, 'year_level' => 4],
            ['year_level' => 4]
        );

        ProgramCourse::firstOrCreate(
            ['program_year_id' => $year4->id, 'course_code' => 'ENG401'],
            ['course_name' => 'Teaching Internship']
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function holdingsDefinitions(): array
    {
        $rows = [
            // Introduction to Linguistics — 3 titles, 6 copies
            ['GG-RPT-0001', 'Introduction to linguistics', 'Gonzales, J. & Enoc, J.', 'Introduction to Linguistics', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0002', 'Introduction to linguistics', 'Gonzales, J. & Enoc, J.', 'Introduction to Linguistics', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0003', 'Introduction to linguistics', 'Gonzales, J. & Enoc, J.', 'Introduction to Linguistics', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0004', 'Easy linguistics', 'Bantawig, R. B.', 'Introduction to Linguistics', 'prof ed', '2020', 'Printed'],
            ['GG-RPT-0005', 'Easy linguistics', 'Bantawig, R. B.', 'Introduction to Linguistics', 'prof ed', '2020', 'Printed'],
            ['GG-RPT-0006', 'Principles of historical linguistics', 'Henrich, Hock Hans', 'Introduction to Linguistics', 'prof ed', '2021', 'Electronic'],
            ['GG-RPT-0007', 'Introduction to linguistics', 'Paraan, L. C. A. & Pawilen, G. T.', 'Introduction to Linguistics', 'prof ed', '2024', 'Printed'],

            // Language, Culture and Society
            ['GG-RPT-0008', 'Language, culture and society', 'Mananay, J. & Sumalinog, G.', 'Language, Culture and Society', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0009', 'Language, culture and society', 'Mananay, J. & Sumalinog, G.', 'Language, Culture and Society', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0010', 'Language, culture and society', 'Mananay, J. & Sumalinog, G.', 'Language, Culture and Society', 'prof ed', '2021', 'Printed'],

            // Structure of English
            ['GG-RPT-0011', 'Structures of English', 'Dapat, Enoc, Gonzales, & Anlagan', 'Structure of English', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0012', 'Structures of English', 'Dapat, Enoc, Gonzales, & Anlagan', 'Structure of English', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0013', 'Structures of English', 'Dapat, Enoc, Gonzales, & Anlagan', 'Structure of English', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0014', 'Structures of English', 'Dapat, Enoc, Gonzales, & Anlagan', 'Structure of English', 'prof ed', '2021', 'Printed'],

            // Gen Ed — Understanding the Self
            ['GG-RPT-0015', 'Understanding the self', 'Corpuz, Lucas, Andas, Dayagbil, & Gacasan', 'Understanding the Self', 'gen ed', '2020', 'Printed'],
            ['GG-RPT-0016', 'Understanding the self', 'Corpuz, Lucas, Andas, Dayagbil, & Gacasan', 'Understanding the Self', 'gen ed', '2020', 'Printed'],

            // Readings in Philippine History
            ['GG-RPT-0017', 'Readings in Philippine history', 'Asuncion, N. & Cruz, G. R. C.', 'Readings in Philippine History', 'gen ed', '2022', 'Printed'],
            ['GG-RPT-0018', 'A course module for readings in Philippine history', 'Candelaria, Alporha, & Kunting', 'Readings in Philippine History', 'gen ed', '2021', 'Printed'],

            // Principles of Teaching (prospectus course)
            ['GG-RPT-0019', 'Principles of teaching 1', 'Ancheta, Antonio R.', 'Principles of Teaching', 'prof ed', '2019', 'Printed'],
            ['GG-RPT-0020', 'Principles of teaching 2', 'Ancheta, Antonio R.', 'Principles of Teaching', 'prof ed', '2023', 'Printed'],
            ['GG-RPT-0021', 'Principles of teaching 2', 'Ancheta, Antonio R.', 'Principles of Teaching', 'prof ed', '2023', 'Printed'],

            // Curriculum Planning
            ['GG-RPT-0022', 'Curriculum development for teachers', 'Ornstein, Allan C.', 'Curriculum Planning', 'prof ed', '2019', 'Printed'],
            ['GG-RPT-0023', 'Curriculum development for teachers', 'Ornstein, Allan C.', 'Curriculum Planning', 'prof ed', '2019', 'Printed'],

            // Research in Education
            ['GG-RPT-0024', 'Research in education', 'Cohen, Louis', 'Research in Education', 'prof ed', '2018', 'Printed'],
            ['GG-RPT-0025', 'Educational research methods', 'Creswell, John W.', 'Research in Education', 'prof ed', '2022', 'Printed'],

            // Art Appreciation
            ['GG-RPT-0026', 'Reading visual arts', 'Fajardo, Cuesta, & Nebria', 'Art Appreciation', 'gen ed', '2022', 'Printed'],
            ['GG-RPT-0027', 'Modular approach to art appreciation', 'Inocian, R. B.', 'Art Appreciation', 'gen ed', '2021', 'Printed'],
            ['GG-RPT-0028', 'Art appreciation for general education curriculum', 'Ramos, Arnulfo B.', 'Art Appreciation', 'gen ed', '2020', 'Printed'],

            // Science, Technology and Society
            ['GG-RPT-0029', 'Science, technology, and society', 'McNamara, Valverde, & Beleno', 'Science, Technology and Society', 'gen ed', '2024', 'Printed'],
            ['GG-RPT-0030', 'Science, technology and society', 'Aldea, Caronan, & Candido', 'Science, Technology and Society', 'gen ed', '2022', 'Printed'],

            // Teaching and Assessment of Literature Studies
            ['GG-RPT-0031', 'The teaching and assessment of literature studies', 'Bacus, Terogo, Bustos, & Dapat', 'Teaching and Assessment of Literature Studies', 'prof ed', '2022', 'Printed'],
            ['GG-RPT-0032', 'The teaching and assessment of literature studies', 'Bacus, Terogo, Bustos, & Dapat', 'Teaching and Assessment of Literature Studies', 'prof ed', '2022', 'Printed'],
            ['GG-RPT-0033', 'The teaching and assessment of literature studies', 'Bacus, Terogo, Bustos, & Dapat', 'Teaching and Assessment of Literature Studies', 'prof ed', '2022', 'Printed'],

            // Children and Adolescent Literature
            ['GG-RPT-0034', 'Exploring contemporary and popular literature', 'Sipe, Lawrence R.', 'Children and Adolescent Literature', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0035', 'Exploring contemporary and popular literature', 'Sipe, Lawrence R.', 'Children and Adolescent Literature', 'prof ed', '2021', 'Printed'],
            ['GG-RPT-0036', 'Children\'s literature in the Philippines', 'Torres, Myra T.', 'Children and Adolescent Literature', 'prof ed', '2020', 'Printed'],
            ['GG-RPT-0037', 'Children\'s literature in the Philippines', 'Torres, Myra T.', 'Children and Adolescent Literature', 'prof ed', '2020', 'Printed'],
            ['GG-RPT-0038', 'Children\'s literature in the Philippines', 'Torres, Myra T.', 'Children and Adolescent Literature', 'prof ed', '2020', 'Printed'],
            ['GG-RPT-0039', 'Young adult literature today', 'Cart, Michael', 'Children and Adolescent Literature', 'prof ed', '2024', 'Printed'],
            ['GG-RPT-0040', 'Young adult literature today', 'Cart, Michael', 'Children and Adolescent Literature', 'prof ed', '2024', 'Printed'],

            // Teaching Internship
            ['GG-RPT-0041', 'Teaching internship', 'Borabo, M. L. & Din, H. G. B.', 'Teaching Internship', 'prof ed', '2022', 'Printed'],

            // Facilitating Learner-Centered Teaching
            ['GG-RPT-0042', 'Facilitating learner-centered teaching', 'Alda, Abao, Dayagbil, & Dalagan', 'Facilitating Learner-Centered Teaching', 'prof ed', '2022', 'Printed'],
            ['GG-RPT-0043', 'Facilitating learner-centered teaching', 'Alda, Abao, Dayagbil, & Dalagan', 'Facilitating Learner-Centered Teaching', 'prof ed', '2022', 'Printed'],

            // Ethics
            ['GG-RPT-0044', 'Ethics and moral philosophy', 'Velasquez, Manuel G.', 'Ethics', 'gen ed', '2017', 'Printed'],
            ['GG-RPT-0045', 'Ethics for modern living', 'Dayagbil, Florante T.', 'Ethics', 'gen ed', '2023', 'Printed'],
            ['GG-RPT-0046', 'Ethics for modern living', 'Dayagbil, Florante T.', 'Ethics', 'gen ed', '2023', 'Printed'],
        ];

        return array_map(function (array $row, int $index) {
            [$accession, $title, $author, $course, $curriculum, $year, $contentType] = $row;

            return [
                'accession_no' => $accession,
                'rfid' => 'RFID-'.str_replace('-', '', $accession),
                'barcode' => 'BC-'.str_replace('-', '', $accession),
                'title_statement' => $title,
                'main_author' => $author,
                'course' => $course,
                'curriculum' => $curriculum,
                'pub_year' => $year,
                'content_type' => $contentType,
                'publisher' => 'Rex Book Store',
                'call_number' => 'LB'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT).' 2024',
                'year' => $this->yearLevelForCourse($course),
            ];
        }, $rows, array_keys($rows));
    }

    protected function yearLevelForCourse(string $course): string
    {
        $year1 = [
            'Understanding the Self', 'Readings in Philippine History', 'Foundations of Education',
            'Assessment in Learning 1', 'Facilitating Learner-Centered Teaching',
        ];
        $year4 = ['Teaching Internship', 'Practice Teaching in Secondary 1', 'Research in Education'];

        if (in_array($course, $year1, true)) {
            return '1st Year';
        }
        if (in_array($course, $year4, true)) {
            return '4th Year';
        }

        return '2nd Year';
    }
}
