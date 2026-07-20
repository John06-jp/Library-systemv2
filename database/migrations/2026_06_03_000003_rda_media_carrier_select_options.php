<?php

use App\Models\MarcField;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $fields = [
            [
                'tag' => '337',
                'subfield' => 'a',
                'options' => ['Unmediated', 'Audio', 'Computer', 'Microform', 'Video', 'Other'],
            ],
            [
                'tag' => '338',
                'subfield' => 'a',
                'options' => ['Volume', 'Online resource', 'Audio disc', 'CD-ROM', 'Sheet', 'Object'],
            ],
        ];

        foreach ($fields as $row) {
            MarcField::query()
                ->where('tag', $row['tag'])
                ->where('subfield', $row['subfield'])
                ->update([
                    'input_type' => 'select',
                    'options' => $row['options'],
                ]);
        }
    }

    public function down(): void
    {
        foreach (['337', '338'] as $tag) {
            MarcField::query()
                ->where('tag', $tag)
                ->where('subfield', 'a')
                ->update([
                    'input_type' => 'text',
                    'options' => null,
                ]);
        }
    }
};
