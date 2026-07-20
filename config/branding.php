<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Branding stylesheet (per school / per subdomain)
    |--------------------------------------------------------------------------
    |
    | Point this to a file under /public (served via asset()).
    | Example: BRANDING_CSS=branding/usm.css
    |
    */
    'css_path' => env('BRANDING_CSS', 'branding/branding.css'),

    /*
    |--------------------------------------------------------------------------
    | Image optimization settings
    |--------------------------------------------------------------------------
    */
    'optimization' => [
        'jpeg_quality' => 85,
        'png_compression' => 7,
        'webp_quality' => 80,
        'strip_exif' => true,
        'max_dimensions' => [
            'banner' => [4000, 2000],
            'logo' => [1000, 1000],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Branding defaults (original values)
    |--------------------------------------------------------------------------
    |
    | These are the permanent original values. Database values are overrides.
    | A null/empty override means the application uses the corresponding
    | original configuration value below.
    |
    | Original assets reside under public/images/ and must never be
    | overwritten or deleted.
    |
    */
    'defaults' => [
        // Asset paths (original files under public/images/)
        'banner_path' => 'images/banner.png',
        'opac_banner_path' => 'images/banner.png',
        'opac_logo_path' => 'images/pantasLogo.png',
        'opac_default_book_cover_path' => 'images/defaultBook.png',
        'sidebar_logo_path' => 'images/logo.png',

        // Sidebar brand text
        'sidebar_brand_name' => 'Library System',
        'sidebar_brand_subtitle' => 'Knowledge Gateway',

        // Color fields
        'primary_color' => '#2563EB',
        'secondary_color' => '#475569',
        'accent_color' => '#F59E0B',
        'sidebar_background_color' => '#1E293B',
        'sidebar_text_color' => '#CBD5E1',
        'sidebar_brand_text_color' => '#FFFFFF',
        'sidebar_active_color' => '#3B82F6',
        'sidebar_hover_background_color' => '#334155',
        'sidebar_hover_text_color' => '#F8FAFC',
        'button_color' => '#2563EB',
        'sidebar_footer_background_color' => '#0F172A',
        'table_header_color' => '#1E293B',
        'table_header_text_color' => '#F8FAFC',
        'table_border_color' => '#E2E8F0',
        'table_hover_color' => '#F1F5F9',
    ],
];
