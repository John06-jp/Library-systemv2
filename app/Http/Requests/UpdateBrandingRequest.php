<?php

namespace App\Http\Requests;

use App\Services\ContrastRules;
use App\Services\ContrastValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('developer') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Asset fields
            'banner_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'opac_banner_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'opac_logo_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'opac_default_book_cover_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'sidebar_logo_path' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            // Text fields
            'sidebar_brand_name' => 'nullable|string|max:60',
            'sidebar_brand_subtitle' => 'nullable|string|max:100',

            // Color fields
            'primary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_background_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_brand_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_active_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_hover_background_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_hover_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'button_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'sidebar_footer_background_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'table_header_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'table_header_text_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'table_border_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'table_hover_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    protected function prepareForValidation(): void
    {
        $colorFields = [
            'primary_color', 'secondary_color', 'accent_color',
            'sidebar_background_color', 'sidebar_text_color', 'sidebar_brand_text_color',
            'sidebar_active_color', 'sidebar_hover_background_color', 'sidebar_hover_text_color',
            'button_color', 'sidebar_footer_background_color',
            'table_header_color', 'table_header_text_color', 'table_border_color', 'table_hover_color',
        ];

        $normalized = [];
        foreach ($colorFields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $normalized[$field] = strtoupper($this->input($field));
            }
        }

        $this->merge($normalized);
    }

    /**
     * Run WCAG contrast checks after validation passes.
     */
    protected function passedValidation(): void
    {
        $validator = app(ContrastValidator::class);
        $data = $this->validated();
        $rules = ContrastRules::for('branding');
        $failures = [];

        foreach ($rules as $rule) {
            $foreground = $data[$rule['foreground']] ?? null;
            $background = $data[$rule['background']] ?? null;

            if ($foreground === null || $background === null) {
                continue;
            }

            $ratio = $validator->ratio($foreground, $background);

            if ($ratio < $rule['minimum']) {
                $failures[] = "{$rule['label']}: {$foreground} on {$background} has a contrast ratio of "
                    .round($ratio, 2).":1, which is below the minimum {$rule['minimum']}:1.";
            }
        }

        if ($failures !== []) {
            throw ValidationException::withMessages([
                'contrast' => ['WCAG contrast check failed:']
                    + $failures,
            ]);
        }
    }
}