<?php

namespace App\Services;

use InvalidArgumentException;

class ContrastRules
{
    /**
     * @return list<array{foreground: string, background: string, label: string, minimum: float}>
     */
    public static function for(string $context): array
    {
        if ($context !== 'branding') {
            throw new InvalidArgumentException("Unknown contrast-rule context [{$context}].");
        }

        return [
            [
                'foreground' => 'sidebar_brand_text_color',
                'background' => 'sidebar_background_color',
                'label' => 'Sidebar brand text',
                'minimum' => 4.5,
            ],
            [
                'foreground' => 'sidebar_text_color',
                'background' => 'sidebar_background_color',
                'label' => 'Sidebar text',
                'minimum' => 4.5,
            ],
            [
                'foreground' => 'sidebar_hover_text_color',
                'background' => 'sidebar_hover_background_color',
                'label' => 'Sidebar hover text',
                'minimum' => 4.5,
            ],
            [
                'foreground' => 'table_header_text_color',
                'background' => 'table_header_color',
                'label' => 'Table header text',
                'minimum' => 4.5,
            ],
        ];
    }
}
