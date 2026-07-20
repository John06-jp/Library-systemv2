<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBrandingRequest;
use App\Models\AdminActivity;
use App\Models\BrandingVersion;
use App\Services\BrandingService;
use Illuminate\Http\Request;

class DeveloperBrandingController extends Controller
{
    public function __construct(private readonly BrandingService $branding) {}

    public function edit()
    {
        return view('developer.branding.edit', [
            'current' => $this->branding->active(),
            'originals' => $this->branding->originals(),
            'setting' => $this->branding->setting(),
        ]);
    }

    public function update(UpdateBrandingRequest $request)
    {
        $uploads = [];
        $assetFields = ['banner_path', 'opac_logo_path', 'opac_default_book_cover_path', 'sidebar_logo_path'];

        foreach ($assetFields as $field) {
            if ($request->hasFile($field)) {
                $uploads[$field] = $request->file($field);
            }
        }

        $values = $request->validated();
        // Remove file entries from values since they're handled as uploads
        foreach ($assetFields as $field) {
            unset($values[$field]);
        }

        $this->branding->update($values, $uploads, $request->user());

        return redirect()->route('developer.branding.edit')
            ->with('success', 'Branding settings updated successfully.');
    }

    public function restore(Request $request)
    {
        $field = $request->input('field');

        $this->branding->restore($field ?: null, $request->user());

        $message = $field
            ? "Branding field '{$field}' restored to default."
            : 'All branding settings restored to defaults.';

        return redirect()->route('developer.branding.edit')
            ->with('success', $message);
    }

    public function activity()
    {
        $activities = AdminActivity::query()
            ->where('type', 'like', 'branding%')
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('developer.branding.activity', compact('activities'));
    }

    public function versions()
    {
        $versions = BrandingVersion::query()
            ->with('changer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('developer.branding.versions', compact('versions'));
    }

    public function restoreVersion(Request $request, BrandingVersion $version)
    {
        $this->branding->restoreFromVersion($version, $request->user());

        return redirect()->route('developer.branding.edit')
            ->with('success', "Branding version #{$version->getKey()} restored successfully.");
    }
}
