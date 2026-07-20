<?php

namespace App\Http\Controllers;

use App\Services\BrandingService;
use Illuminate\Http\Request;

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

        $customizedCount = 0;
        foreach ($branding as $field => $value) {
            if (isset($originals[$field]) && $value !== $originals[$field]) {
                $customizedCount++;
            }
        }

        return view('dashboards.developer', compact('branding', 'originals', 'setting', 'customizedCount'));
    }
}