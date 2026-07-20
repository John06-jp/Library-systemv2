# Developer Role Implementation Plan

> **Goal:** Add a `developer` staff role that exclusively manages system-wide branding (banner, sidebar logo, OPAC assets, and default colors) in `library-systemv2`.

---

## Goal

Add a separate `developer` staff role that exclusively manages the system-wide branding.

The role boundary is strict:

- Only `developer` can access Developer pages and branding actions.
- All other roles (including `admin`, `staff`) receive `403` on Developer routes.
- Developer cannot access admin or staff modules.
- Unauthorized access to `/developer/*` returns `403 Forbidden`.

---

## Branding Scope

Developer can:

- View a Developer dashboard with current branding summary.
- Upload or replace the application banner, OPAC banner, OPAC logo, OPAC default book cover, and sidebar logo.
- Change sidebar brand name and subtitle text.
- Change all branding colors (primary, secondary, accent, sidebar, table, button, etc.) via color picker and hex input.
- Preview current and original branding side by side.
- Restore one customized value or restore all original values.
- View branding activity log (branding-related only).
- View and restore from branding version history.

**Out of scope (not in this implementation):**

- Login modal settings (logo, text, colors, placeholders)
- Register modal settings (Attendance/Library logos, text, colors, labels)

---

## Original Values

Define permanent original values in `config/branding.php`, including all original asset paths and every customizable color. Original assets remain under `public/images` and must never be overwritten or deleted.

Database values are overrides. A null override means the application uses the corresponding original configuration value.

---

## Data Model

Create a singleton-style `branding_settings` table with nullable override columns covering:

- **Asset paths:** `banner_path`, `opac_banner_path`, `opac_logo_path`, `opac_default_book_cover_path`, `sidebar_logo_path`
- **Text fields:** `sidebar_brand_name`, `sidebar_brand_subtitle`
- **Color fields:** `primary_color`, `secondary_color`, `accent_color`, `sidebar_background_color`, `sidebar_text_color`, `sidebar_brand_text_color`, `sidebar_active_color`, `sidebar_hover_background_color`, `sidebar_hover_text_color`, `button_color`, `sidebar_footer_background_color`, `table_header_color`, `table_header_text_color`, `table_border_color`, `table_hover_color`
- `updated_by` (FK to users), timestamps

Create a `branding_versions` table for version snapshots:

- `branding_setting_id` (FK), `snapshot` (JSON), `changed_by` (FK to users), `created_at` (timestamp)

Add `BrandingSetting` and `BrandingVersion` models. Create a centralized `BrandingService` that:

- Loads defaults from config, merges database overrides
- Resolves public URLs for assets
- Caches active branding (forever, invalidated on update/restore)
- Handles safe file replacement (store new → commit DB → delete old custom file)
- Restores single field or all to defaults
- Takes version snapshots before every change
- Falls back to original assets when custom files are missing or deleted

---

## Authorization And Routing

Role checking uses a simple `role` column on the `users` table. Expected role values: `admin`, `staff`, `developer`. Add `hasRole()` and `hasAnyRole()` helper methods to the User model.

The `ModuleAccessService` determines module access:

- `isDeveloper()` — checks `$user->hasRole('developer')`
- `hasLibraryAccess()` — checks `admin`, `staff`
- `hasAttendanceAccess()` — reserved for future attendance roles

Create `EnsureDeveloper` middleware that uses `ModuleAccessService::isDeveloper()` and aborts with 403 on failure. No Super Admin bypass.

Create `routes/developer.php` with authenticated Developer-only routes:

| Method | URI | Name |
|---|---|---|
| GET | `/developer/dashboard` | `dashboard.developer` |
| GET | `/developer/branding` | `developer.branding.edit` |
| PUT | `/developer/branding` | `developer.branding.update` |
| POST | `/developer/branding/restore` | `developer.branding.restore` |
| GET | `/developer/branding/activity` | `developer.branding.activity` |
| GET | `/developer/branding/versions` | `developer.branding.versions` |
| POST | `/developer/branding/versions/{version}/restore` | `developer.branding.restore-version` |

Register the `developer` middleware alias and load `routes/developer.php` in `bootstrap/app.php`.

---

## Key Implementation Details

These are important specifics that the implementer must account for:

1. **`AdminActivityLogger` uses static methods** — Logging is done via `AdminActivityLogger::log(type, title, body, actionUrl, icon, subject, userId)`. All calls in `BrandingService` and `DeveloperBrandingController` must use this static signature. The `BrandingService` should not inject `AdminActivityLogger` as a constructor dependency — call it statically instead.

2. **Layout is `layouts.sec`** — All developer views must extend `layouts.sec`. This layout uses a React admin shell with Blade content injection via `@yield('content')`.

3. **Role system is simple `role` column** — No Spatie roles or complex permission system. Add `hasRole()` and `hasAnyRole()` to the User model. Expected values: `admin`, `staff`, `developer`.

4. **SweetAlert2 dependency** — The contrast-warnings component uses SweetAlert2 for popups. Verify it's loaded in `layouts.sec` or add the CDN script tag there.

5. **Branding asset serving** — Uploaded assets are stored under `storage/app/public/branding/`. The `BrandingService::assetUrl()` method returns URLs prefixed with `/branding-assets/`. This requires either a route that serves files from the `branding` subdirectory of storage, or a symbolic link. Add a route in `web.php` or create a Storage symlink.

6. **Intervention Image** — Already available in `composer.json` as `"intervention/image": "^2.7"`. Used by `AssetOptimizer` for image compression.

---

## User Interface

### Developer Dashboard

Shows current branding status with:

- Current sidebar logo thumbnail and banner preview
- Customized/Original badge
- Last updated by and timestamp
- Link to open Branding Settings

### Branding Settings Page

A full-page form containing:

- **Banner section:** Current banner preview, original banner preview, file upload control, restore button
- **OPAC Banner section:** Same layout as banner
- **OPAC Library Logo section:** Same layout with logo previews
- **OPAC Default Book Cover section:** Same layout with book cover previews
- **Sidebar logo section:** Current and original logo previews, file upload, restore button
- **Sidebar brand text section:** Brand name and subtitle text inputs with reset buttons
- **Color palette section:** Live preview sidebar/banner mockup that updates in real time, followed by color picker + hex input for every branding color, each with a reset button
- **Actions:** Save Changes, Cancel, Version History, Restore to Default (with confirmation)

### Branding Activity Page

Paginated table showing branding-related audit entries: date, developer name, action type badge, and details.

### Branding Versions Page

Paginated table of version snapshots: version number, snapshot date/time, changed by, fields included summary, and Restore button with confirmation.

---

## Upload And Color Safety

Use a Form Request to validate:

- **Images:** PNG, JPG/JPEG, WebP only. Max file sizes (banners: 5MB, logos: 2MB, book covers: 4MB). Minimum dimensions enforced. Files stored under `storage/app/public/branding/{banners,logos,opac}/`.
- **Colors:** Six-digit `#RRGGBB` only, normalized to uppercase. Reject named colors, alpha values, CSS expressions, URLs, variables, and incomplete hex values.
- **Text:** Max 60 chars for brand name, 100 for subtitle.

Store a new image and commit its database path before deleting the previous custom image. Never delete original/public files.

After validation passes, run WCAG contrast checks on these branding color pairs:

- Sidebar brand text on Sidebar background
- Sidebar text on Sidebar background
- Sidebar hover text on Sidebar hover background
- Table header text on Table header background

Block saving if any pair fails minimum contrast ratio (4.5:1 for normal text, 3:1 for large text).

---

## Application Integration

Expose active branding values to Blade views through the `BrandingService`. The developer views use these values directly. No view composer or CSS custom property integration is needed for the initial implementation — that can be added later as a separate task.

---

## Restore To Default

Full restoration must:

1. Verify the exact `developer` role (via middleware).
2. Capture previous override values for audit logging (via version snapshot).
3. Clear every custom database value inside a transaction.
4. Commit the restored state.
5. Clear the branding cache.
6. Delete only previous custom asset files.
7. Preserve every original asset.
8. Record the action via `AdminActivityLogger::log()`.
9. Redirect with a success message.

If the database operation fails, custom files remain untouched. Partial restore clears only the selected override and deletes only its replaced custom file when applicable.

---

## Audit And Error Handling

Use the existing `AdminActivityLogger` to record the acting Developer, action type, changed fields, and full or partial restoration. Developer sees branding-related activity only (filtered by `type like 'branding%'`).

Handle validation, storage, database, cache, missing-file, and authorization failures. Missing custom images automatically fall back to original assets without breaking the page.

---

## Verification

Verify that:

- Developer can access, update, and restore branding.
- `admin` and `staff` roles receive `403` on Developer routes.
- Image and color validation rejects unsafe input.
- File replacement deletes only obsolete custom files.
- Original assets are never deleted.
- Full and partial restoration return the correct original values.
- Cache invalidation, missing-file fallback, and activity logging work.

Run `php artisan migrate`, `php artisan route:list`, and manually verify the developer dashboard, branding settings form, activity log, and version history pages.

---

## Implementation Tasks

### Task 1: Role Support In User Model

Add `hasRole()` and `hasAnyRole()` methods to `app/Models/User.php` that check the `role` column. Expected role values: `admin`, `staff`, `developer`.

Also add a `getNameAttribute()` accessor that returns `$this->fullName()` for use in views.

### Task 2: Services Layer

Create these services in order:

1. `app/Services/Auth/ModuleAccessService.php` — Module access logic (`isDeveloper()`, `hasLibraryAccess()`, etc.)
2. `app/Services/ContrastValidator.php` — WCAG contrast ratio calculator (`relativeLuminance()`, `ratio()`, `passesAA()`)
3. `app/Services/ContrastRules.php` — WCAG contrast rule definitions for branding (sidebar brand text, sidebar text, hover text, table header text pairs)
4. `app/Services/AssetOptimizer.php` — Image compression/resize via Intervention Image (`optimize()` method with banner/logo type dimensions)
5. `app/Services/BrandingService.php` — Core branding CRUD, caching, versioning, asset management. Uses `AdminActivityLogger::log()` statically (not injected). Excludes any login/register modal functionality.

### Task 3: Database Layer

1. Create `database/migrations/..._create_branding_settings_table.php` with all asset, text, and color columns
2. Create `database/migrations/..._create_branding_versions_table.php` with snapshot JSON and foreign keys
3. Run `php artisan migrate`

### Task 4: Models

1. `app/Models/BrandingSetting.php` — Singleton branding settings with `updater()` BelongsTo relationship to User
2. `app/Models/BrandingVersion.php` — Version snapshots with `changer()` BelongsTo to User, `brandingSetting()` BelongsTo to BrandingSetting, `$timestamps = false`, `snapshot` cast to array

### Task 5: Middleware

Create `app/Http/Middleware/EnsureDeveloper.php` that checks `ModuleAccessService::isDeveloper()` and sets session `active_module` to `'developer'`. Register as `developer` alias in `bootstrap/app.php`.

### Task 6: Form Request

Create `app/Http/Requests/UpdateBrandingRequest.php` with:

- `authorize()` — Checks `$this->user()->hasRole('developer')`
- `rules()` — Image validation (mimes, max sizes, dimensions), color regex (`/^#[0-9A-Fa-f]{6}$/`), text max lengths
- `passedValidation()` — WCAG contrast checks using `ContrastRules::for('branding')` and `ContrastValidator::ratio()`
- `prepareForValidation()` — Uppercases color values

### Task 7: Controller

Create `app/Http/Controllers/DeveloperBrandingController.php` with methods:

- `edit()` — Returns branding edit view with current and original values
- `update(UpdateBrandingRequest)` — Calls `BrandingService::update()`
- `restore(Request)` — Calls `BrandingService::restore()`
- `activity()` — Returns activity view with paginated branding activities
- `versions()` — Returns versions view with paginated version history
- `restoreVersion(Request, int $version)` — Calls `BrandingService::restoreFromVersion()`

### Task 8: Dashboard Controller

Add `developer()` method to `app/Http/Controllers/DashboardController.php` (create file if it doesn't exist) that returns the developer dashboard view.

### Task 9: Routes

Create `routes/developer.php` with all developer routes under `auth` and `developer` middleware. Register the route file in `bootstrap/app.php` (use `Route::middleware('web')->group()` approach or load in `AppServiceProvider`).

### Task 10: Views

Create these Blade views (all extend `layouts.sec`):

1. `resources/views/dashboards/developer.blade.php` — Dashboard summary with logo, banner, status badge
2. `resources/views/developer/branding/edit.blade.php` — Full branding form with all sections, live preview, contrast checker JS
3. `resources/views/developer/branding/activity.blade.php` — Activity log table with pagination
4. `resources/views/developer/branding/versions.blade.php` — Version history table with restore buttons
5. `resources/views/components/contrast-warnings.blade.php` — JS component for WCAG contrast checking in browser

### Task 11: Configuration

Replace `config/branding.php` with the full version including:

- `css_path` — Keep existing
- `optimization` — JPEG quality 85, PNG compression 7, WebP quality 80, strip EXIF, max dimensions for banner (4000x2000) and logo (1000x1000)
- `defaults` — All branding defaults: asset paths, sidebar brand text, all color values. **Exclude** any login/register modal fields.

### Task 12: Asset Serving Route

Add a route so that `/branding-assets/*` serves files from `storage/app/public/branding/`:

```php
Route::get('/branding-assets/{path}', function (string $path) {
    return response()->file(storage_path('app/public/branding/'.$path));
})->where('path', '.*');
```

---

## Completion Criteria

The work is complete when:

- Developer role exists and can access `/developer/dashboard`, `/developer/branding`, and related pages
- Admin/staff roles receive `403` on developer routes
- Branding settings can be edited (banner, logo, OPAC assets, colors, text)
- Changes persist, cache correctly, and display in the UI
- Branding activity log records all changes
- Branding version history captures snapshots and enables restore
- Full and partial restoration work correctly
- Original assets are never deleted
- Missing custom files fall back to originals
- All views render without errors
- `php artisan migrate` runs cleanly
- `php artisan route:list` shows all developer routes