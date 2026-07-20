<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;

class DashboardController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    /**
     * Admin/staff dashboard (existing if already defined elsewhere).
     */
    public function index()
    {
        return view('dashboard');
    }

    /**
     * Developer dashboard showing current branding summary.
     */
    public function developer()
    {
        $branding = $this->branding->active();
        $originals = $this->branding->originals();
        $setting = $this->branding->setting();
        $assetUrls = [
            'banner_path' => $this->branding->assetUrl('banner_path'),
            'sidebar_logo_path' => $this->branding->assetUrl('sidebar_logo_path'),
        ];

        $customizedCount = 0;
        foreach ($branding as $field => $value) {
            if (isset($originals[$field]) && $value !== $originals[$field]) {
                $customizedCount++;
            }
        }

        return view('dashboards.developer', compact('branding', 'originals', 'setting', 'assetUrls', 'customizedCount'));
    }
}
