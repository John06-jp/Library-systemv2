<?php

namespace Tests\Unit;

use App\Services\ContrastRules;
use App\Services\ContrastValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ContrastValidatorTest extends TestCase
{
    public function test_black_and_white_have_maximum_contrast(): void
    {
        $validator = new ContrastValidator;

        $this->assertEqualsWithDelta(21.0, $validator->ratio('#000000', '#FFFFFF'), 0.001);
        $this->assertTrue($validator->passesAA('#000000', '#FFFFFF'));
        $this->assertFalse($validator->passesAA('#777777', '#FFFFFF'));
    }

    public function test_invalid_hex_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ContrastValidator)->relativeLuminance('red');
    }

    public function test_branding_rules_include_all_required_pairs(): void
    {
        $rules = ContrastRules::for('branding');

        $this->assertCount(4, $rules);
        $this->assertSame(
            [
                'sidebar_brand_text_color',
                'sidebar_text_color',
                'sidebar_hover_text_color',
                'table_header_text_color',
            ],
            array_column($rules, 'foreground'),
        );
    }
}
