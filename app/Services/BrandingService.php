<?php

namespace App\Services;

use App\Models\BrandingSetting;
use App\Models\BrandingVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class BrandingService
{
    public const CACHE_KEY = 'branding.active';

    public const ASSET_FIELDS = [
        'banner_path',
        'opac_banner_path',
        'opac_logo_path',
        'opac_default_book_cover_path',
        'sidebar_logo_path',
    ];

    public const TEXT_FIELDS = [
        'sidebar_brand_name',
        'sidebar_brand_subtitle',
    ];

    public const COLOR_FIELDS = [
        'primary_color',
        'secondary_color',
        'accent_color',
        'sidebar_background_color',
        'sidebar_text_color',
        'sidebar_brand_text_color',
        'sidebar_active_color',
        'sidebar_hover_background_color',
        'sidebar_hover_text_color',
        'button_color',
        'sidebar_footer_background_color',
        'table_header_color',
        'table_header_text_color',
        'table_border_color',
        'table_hover_color',
    ];

    private const UPLOAD_CONFIGURATION = [
        'banner_path' => ['type' => 'banner', 'directory' => 'branding/banners'],
        'opac_banner_path' => ['type' => 'banner', 'directory' => 'branding/opac'],
        'opac_logo_path' => ['type' => 'logo', 'directory' => 'branding/opac'],
        'opac_default_book_cover_path' => ['type' => 'book_cover', 'directory' => 'branding/opac'],
        'sidebar_logo_path' => ['type' => 'logo', 'directory' => 'branding/logos'],
    ];

    public function __construct(private readonly AssetOptimizer $optimizer) {}

    /**
     * Return defaults merged with the singleton's non-null overrides.
     *
     * @return array<string, mixed>
     */
    public function active(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            $defaults = config('branding.defaults', []);
            $setting = BrandingSetting::query()->first();
            $overrides = $setting
                ? Arr::only($setting->getAttributes(), $this->fields())
                : [];

            return array_replace($defaults, array_filter(
                $overrides,
                static fn (mixed $value): bool => $value !== null,
            ));
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function originals(): array
    {
        return config('branding.defaults', []);
    }

    public function setting(): BrandingSetting
    {
        return BrandingSetting::query()->firstOrCreate([]);
    }

    public function assetUrl(string $field): string
    {
        if (! in_array($field, self::ASSET_FIELDS, true)) {
            throw new InvalidArgumentException("Unknown branding asset field [{$field}].");
        }

        $setting = BrandingSetting::query()->first();
        $customPath = $setting?->{$field};

        if (is_string($customPath) && $this->customAssetExists($customPath)) {
            return url('/branding-assets/'.ltrim($this->relativeBrandingPath($customPath), '/'));
        }

        $original = config("branding.defaults.{$field}");

        return is_string($original) && $original !== '' ? asset($original) : '';
    }

    /**
     * Persist scalar overrides and uploaded assets.
     *
     * @param  array<string, mixed>  $values
     * @param  array<string, UploadedFile>  $uploads
     */
    public function update(array $values, array $uploads = [], ?User $actor = null): BrandingSetting
    {
        $setting = $this->setting();
        $changes = Arr::only($values, [...self::TEXT_FIELDS, ...self::COLOR_FIELDS]);
        $newPaths = [];

        try {
            foreach (Arr::only($uploads, self::ASSET_FIELDS) as $field => $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $configuration = self::UPLOAD_CONFIGURATION[$field];
                $newPaths[$field] = $this->optimizer->optimize(
                    $file,
                    $configuration['type'],
                    $configuration['directory'],
                );
            }

            $changes = array_replace($changes, $newPaths);
            $changes = array_filter(
                $changes,
                fn (mixed $value, string $field): bool => in_array($field, $this->fields(), true)
                    && $setting->{$field} !== $value,
                ARRAY_FILTER_USE_BOTH,
            );

            if ($changes === []) {
                return $setting;
            }

            $oldAssets = Arr::only($setting->getAttributes(), array_keys(Arr::only($changes, self::ASSET_FIELDS)));

            DB::transaction(function () use ($setting, $changes, $actor): void {
                $this->snapshot($setting, $actor);
                $setting->fill($changes);
                $setting->updated_by = $actor?->getKey();
                $setting->save();
            });
        } catch (Throwable $exception) {
            $this->deleteCustomAssets(array_values($newPaths));
            throw $exception;
        }

        $this->forgetCache();
        $this->deleteCustomAssets(array_values($oldAssets));
        $this->log(
            'branding.updated',
            'Branding updated',
            'Changed: '.implode(', ', array_keys($changes)),
            $setting,
            $actor,
        );

        return $setting->refresh();
    }

    public function restore(?string $field = null, ?User $actor = null): BrandingSetting
    {
        $setting = $this->setting();

        if ($field !== null && ! in_array($field, $this->fields(), true)) {
            throw new InvalidArgumentException("Unknown branding field [{$field}].");
        }

        $fields = $field === null ? $this->fields() : [$field];
        $changedFields = array_values(array_filter(
            $fields,
            static fn (string $name): bool => $setting->{$name} !== null,
        ));

        if ($changedFields === []) {
            return $setting;
        }

        $oldAssets = Arr::only($setting->getAttributes(), array_intersect($changedFields, self::ASSET_FIELDS));

        DB::transaction(function () use ($setting, $changedFields, $actor): void {
            $this->snapshot($setting, $actor);
            $setting->fill(array_fill_keys($changedFields, null));
            $setting->updated_by = $actor?->getKey();
            $setting->save();
        });

        $this->forgetCache();
        $this->deleteCustomAssets(array_values($oldAssets));
        $this->log(
            $field === null ? 'branding.restored_all' : 'branding.restored',
            $field === null ? 'Branding restored to defaults' : 'Branding field restored',
            $field === null ? 'All branding overrides were cleared.' : "Restored: {$field}",
            $setting,
            $actor,
        );

        return $setting->refresh();
    }

    public function restoreFromVersion(BrandingVersion|int $version, ?User $actor = null): BrandingSetting
    {
        $version = is_int($version) ? BrandingVersion::query()->findOrFail($version) : $version;
        $setting = $this->setting();

        if ((int) $version->branding_setting_id !== (int) $setting->getKey()) {
            throw new InvalidArgumentException('The branding version does not belong to the active settings.');
        }

        $restored = array_fill_keys($this->fields(), null);
        foreach (Arr::only($version->snapshot, $this->fields()) as $field => $value) {
            $restored[$field] = $value;
        }

        $currentAssets = Arr::only($setting->getAttributes(), self::ASSET_FIELDS);
        $restoredAssets = array_filter(Arr::only($restored, self::ASSET_FIELDS));

        DB::transaction(function () use ($setting, $restored, $actor): void {
            $this->snapshot($setting, $actor);
            $setting->fill($restored);
            $setting->updated_by = $actor?->getKey();
            $setting->save();
        });

        $this->forgetCache();
        $this->deleteCustomAssets(array_diff(array_filter($currentAssets), $restoredAssets));
        $this->log(
            'branding.version_restored',
            'Branding version restored',
            "Restored branding version {$version->getKey()}.",
            $setting,
            $actor,
        );

        return $setting->refresh();
    }

    public function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return list<string>
     */
    public function fields(): array
    {
        return [...self::ASSET_FIELDS, ...self::TEXT_FIELDS, ...self::COLOR_FIELDS];
    }

    private function snapshot(BrandingSetting $setting, ?User $actor): BrandingVersion
    {
        return $setting->versions()->create([
            'snapshot' => Arr::only($setting->getAttributes(), $this->fields()),
            'changed_by' => $actor?->getKey(),
            'created_at' => now(),
        ]);
    }

    private function log(
        string $type,
        string $title,
        string $body,
        BrandingSetting $setting,
        ?User $actor,
    ): void {
        AdminActivityLogger::log(
            $type,
            $title,
            $body,
            null,
            'palette',
            $setting,
            $actor?->getKey(),
        );
    }

    /**
     * @param  array<int, mixed>  $paths
     */
    private function deleteCustomAssets(array $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && $this->isCustomAsset($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function customAssetExists(string $path): bool
    {
        return $this->isCustomAsset($path) && Storage::disk('public')->exists($path);
    }

    private function isCustomAsset(string $path): bool
    {
        return str_starts_with(str_replace('\\', '/', $path), 'branding/');
    }

    private function relativeBrandingPath(string $path): string
    {
        return substr(str_replace('\\', '/', $path), strlen('branding/'));
    }
}
