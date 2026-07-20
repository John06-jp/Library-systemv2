# TASK.MD

## Current Task

Track and execute the implementation of a `developer` staff role with isolated branding management in `library-systemv2`.

Primary objective:

- Add a `developer` role that exclusively manages system-wide branding.
- Create the branding data model, service layer, and persistence.
- Build Developer-only routes, dashboard, and branding settings interface.
- Implement safe image uploads, color validation, and WCAG contrast checks.
- Add version snapshots, full/partial restoration, and activity audit logging.
- Serve uploaded branding assets through a dedicated route.
- Keep `admin` and `staff` roles completely isolated from Developer features.

Reference plan:

- `documentation/developer-role-port-plan.md`

## Active Work

- [ ] Start implementation of Developer role and branding system.
- [ ] Create detailed implementation plan.
- [ ] Create this task tracker.

## Confirmed Decisions

- Role checking uses the simple `role` column on `users` table (no Spatie).
- Expected role values: `admin`, `staff`, `developer`.
- `AdminActivityLogger` is used statically — no constructor injection.
- All developer views extend `layouts.sec`.
- Uploaded assets stored under `storage/app/public/branding/`.
- A dedicated `/branding-assets/*` route serves uploaded files.
- Login modal and register modal settings are out of scope.
- Original assets under `public/images` must never be overwritten or deleted.

## Open Questions

- [ ] Does `layouts.sec` already load SweetAlert2, or does a CDN script tag need to be added?
- [ ] Does `DashboardController.php` already exist, or does it need to be created?
- [ ] Should the branding cache use `file` or `redis` driver?
- [ ] Are there any existing hard-coded banner/logo references in Blade layouts that should be replaced as part of this work?

## Milestones

### Milestone 1: Role Support And Services

- [ ] Add `hasRole()` and `hasAnyRole()` methods to `app/Models/User.php`.
- [ ] Add `getNameAttribute()` accessor returning `$this->fullName()`.
- [ ] Create `app/Services/Auth/ModuleAccessService.php` with `isDeveloper()`, `hasLibraryAccess()`, etc.
- [ ] Create `app/Services/ContrastValidator.php` — WCAG contrast ratio calculator.
- [ ] Create `app/Services/ContrastRules.php` — WCAG contrast rule definitions for branding.
- [ ] Create `app/Services/AssetOptimizer.php` — Image compression via Intervention Image.
- [ ] Create `app/Services/BrandingService.php` — Core branding CRUD, caching, versioning, asset management.

### Milestone 2: Database And Models

- [ ] Create migration for `branding_settings` table (asset paths, text fields, color fields, updated_by, timestamps).
- [ ] Create migration for `branding_versions` table (branding_setting_id, snapshot JSON, changed_by, created_at).
- [ ] Run `php artisan migrate`.
- [ ] Create `app/Models/BrandingSetting.php` with `updater()` BelongsTo relationship.
- [ ] Create `app/Models/BrandingVersion.php` with `changer()` and `brandingSetting()` relationships, `$timestamps = false`, `snapshot` cast to array.

### Milestone 3: Middleware And Authorization

- [ ] Create `app/Http/Middleware/EnsureDeveloper.php` using `ModuleAccessService::isDeveloper()`.
- [ ] Register `developer` middleware alias in `bootstrap/app.php`.
- [ ] Verify `admin` and `staff` roles receive `403` on developer routes.

### Milestone 4: Form Request And Controller

- [ ] Create `app/Http/Requests/UpdateBrandingRequest.php` with image validation, color regex, text limits, and WCAG contrast checks.
- [ ] Create `app/Http/Controllers/DeveloperBrandingController.php` with `edit()`, `update()`, `restore()`, `activity()`, `versions()`, `restoreVersion()` methods.
- [ ] Add `developer()` method to `app/Http/Controllers/DashboardController.php` (create file if needed).

### Milestone 5: Routes And Configuration

- [ ] Create `routes/developer.php` with all 7 developer routes under `auth` and `developer` middleware.
- [ ] Register `routes/developer.php` in `bootstrap/app.php`.
- [ ] Replace `config/branding.php` with full version including optimization settings and all branding defaults (exclude login/register modal fields).
- [ ] Add `/branding-assets/{path}` route in `routes/web.php` to serve uploaded files.

### Milestone 6: Views

- [ ] Create `resources/views/dashboards/developer.blade.php` — Dashboard summary with logo, banner, status badge.
- [ ] Create `resources/views/developer/branding/edit.blade.php` — Full branding form with all sections, live preview, contrast checker JS.
- [ ] Create `resources/views/developer/branding/activity.blade.php` — Activity log table with pagination.
- [ ] Create `resources/views/developer/branding/versions.blade.php` — Version history table with restore buttons.
- [ ] Create `resources/views/components/contrast-warnings.blade.php` — JS component for WCAG contrast checking in browser.

### Milestone 7: Verification And Hardening

- [ ] Run `php artisan migrate` and confirm both tables created.
- [ ] Run `php artisan route:list` and confirm all developer routes appear.
- [ ] Create a user with `role = 'developer'` and verify dashboard access.
- [ ] Verify `admin` and `staff` users receive `403` on `/developer/*`.
- [ ] Test branding settings form: update colors, upload images, change text.
- [ ] Test WCAG contrast validation rejects poor contrast pairs.
- [ ] Test image validation rejects invalid types, sizes, dimensions.
- [ ] Test partial restore of individual fields.
- [ ] Test full Restore to Default.
- [ ] Test version snapshot creation and restore from version.
- [ ] Test branding activity log records changes.
- [ ] Test missing custom file falls back to original asset.
- [ ] Test original assets are never deleted after restore.
- [ ] Verify all views render without errors.

## Backlog

- [ ] Replace hard-coded banner/logo references in existing Blade layouts with active branding values.
- [ ] Expose branding colors as CSS custom properties consumed by the default theme.
- [ ] Add Developer navigation isolation (hide Library/Admin links, show only Developer links).
- [ ] Add login redirect for Developer role to `/developer/dashboard`.
- [ ] Add automated PHPUnit tests for Developer role access, branding CRUD, and restoration.
- [ ] Add seed data for a default Developer account.

## Discovered During Planning

- `library-systemv2` uses a simple `role` column on the `users` table (values: `admin`, `staff`). No Spatie roles.
- `AdminActivityLogger` in `library-systemv2` uses static methods with signature `::log(type, title, body, actionUrl, icon, subject, userId)` — different from the instance method in the source.
- The `layouts.sec` Blade layout uses a React admin shell with Blade content injection via `@yield('content')`.
- `intervention/image` version `^2.7` is already available in `composer.json`.
- The existing `config/branding.php` is minimal (only `css_path`). It needs full replacement.
- No `DashboardController.php` exists yet — needs to be created.
- Uploaded branding assets need a dedicated route since there's no existing symlink from `public/` to `storage/app/public/branding/`.
- SweetAlert2 usage in the contrast-warnings component needs verification — may need to add CDN script to `layouts.sec`.
- The `AdminActivity` model already exists and supports `type`, `title`, `body`, `action_url`, `icon`, `subject_type`, `subject_id`, and `user_id` fields.