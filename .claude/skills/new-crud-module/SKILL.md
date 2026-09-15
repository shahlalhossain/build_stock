---
name: new-crud-module
description: Scaffold a new CRUD domain (Model, Migration, Requests, Service, Controller, DataTable, Events, Listener, Blade views, routes) following this project's Brand-module pattern. Use when adding a new manageable entity (e.g. "add a Category module", "scaffold Warehouse CRUD").
---

# New CRUD Module

Scaffold a new domain end-to-end by replicating the **Brand module**, the verified reference implementation in this codebase (see `PROJECT_CONTEXT.md` §5, §7). Do not invent a different shape — match Brand's files line-for-line in structure, only substituting names/fields.

## Input

Read the argument as `<ModelName> [--with-status] [--field name:type ...]`.

- `<ModelName>`: PascalCase singular (e.g. `Category`). Derive: table = snake_case plural, route prefix/name = kebab-or-snake singular matching existing conventions (e.g. `brand`, `division`), variable names = camelCase singular/plural.
- `--with-status`: only pass this if the module needs an approval workflow (`status` enum pending/approved/rejected + `ApprovalLog` morphMany), like Brand. **Default: omit** — most modules (geo tables, FAQs, lookups) don't need it. Ask the user if unstated and it's not obviously a lookup table.
- `--field name:type`: extra fillable fields beyond `name`/`slug`/`description`/`priority_order`/`is_active`. If none given, ask what fields the entity needs before scaffolding — don't guess business fields.

## Before writing anything

1. Re-read the current versions of these reference files (don't rely on memory — they may have changed since `PROJECT_CONTEXT.md` was written):
   - [app/Models/Brand.php](../../../app/Models/Brand.php)
   - [app/Http/Controllers/BrandsController.php](../../../app/Http/Controllers/BrandsController.php)
   - [app/Services/BrandService.php](../../../app/Services/BrandService.php)
   - [app/Services/BaseService.php](../../../app/Services/BaseService.php)
   - [app/DataTables/BrandsDataTable.php](../../../app/DataTables/BrandsDataTable.php)
   - [app/Http/Requests/Brand/StoreBrandRequest.php](../../../app/Http/Requests/Brand/StoreBrandRequest.php) and `UpdateBrandRequest.php`
   - [app/Listeners/BrandEventListener.php](../../../app/Listeners/BrandEventListener.php)
   - [app/Events/Brand/*.php](../../../app/Events/Brand/)
   - [resources/views/brand/*.blade.php](../../../resources/views/brand/)
   - The `brand.*` route group in [routes/web.php](../../../routes/web.php)
2. Confirm the plan with the user before writing files if the module has any non-trivial field (foreign keys, enums, file uploads) — this is non-trivial multi-file work per the user's global planning preference. A pure copy of Brand's shape with only cosmetic renames can proceed directly.

## Files to generate

For a module `Foo` (table `foos`):

1. **Migration** `database/migrations/<timestamp>_create_foos_table.php` — copy Brand's migration shape: `id`, business fields, `--with-status` adds `status` enum + nothing else special (ApprovalLog table already exists, reuse it), `priority_order`, `is_active`, `created_by`/`updated_by` as plain `integer` (not FK-constrained, matching existing convention), `timestamps()`, `deleted_by` + `softDeletes()`.
2. **Model** `app/Models/Foo.php` — `SoftDeletes, LogsActivity`; `$fillable`, `$guarded = ['id','created_at','updated_at']`, `casts()` for timestamps/booleans/integers; `getActivitylogOptions()` with `useLogName('foo')->logAll()->logOnlyDirty()`; `creator()/updater()/deleter() : BelongsTo` to `User`; if `--with-status`, add `approvalLogs() : morphMany(ApprovalLog::class, 'model')`. Only split into `App\Models\Traits\*` sub-traits if the model is going to be as complex as `User`/`Role` — for a plain lookup-style entity, keep it a single file like `Brand`.
3. **Requests** `app/Http/Requests/Foo/StoreFooRequest.php` + `UpdateFooRequest.php` — `authorize()` returns `true` (matches existing convention — note this in the summary as a known gap, don't silently "fix" it here). `rules()` + matching `messages()` array, one message per rule, phrased like Brand's (`'{Field} is Required'`, etc.).
4. **Service** `app/Services/FooService.php extends BaseService` — constructor injects `Foo $foo` into `$this->model`. Methods: `storeFoo`, `updateFoo`, `destroyFoo`, `restoreFoo`, `deleteFoo` (+ `updateFooStatus` only if `--with-status`), each wrapped in `DB::beginTransaction/commit/rollBack`, catching `ModelNotFoundException` (rethrow) and `Throwable`/`Exception` (log + throw `GeneralException` with a user-facing message). Destroy sets `deleted_by` via `activity()->withoutLogs()` before `->delete()`; restore reverses it; force-delete logs an explicit `forceDeleted` activity event. Fire the matching Event at the end of each method.
5. **Events** `app/Events/Foo/FooCreated.php`, `FooUpdated.php`, `FooDestroyed.php`, `FooRestored.php`, `FooDeleted.php` (+ `FooStatusUpdated.php` if `--with-status`) — copy Brand's event class shape (plain `Dispatchable`, public readonly model property).
6. **Listener** `app/Listeners/FooEventListener.php` — subscriber pattern like `BrandEventListener`: one `on*` method per event (empty stub body), `subscribe($events)` registering all of them. Rely on Laravel 12 auto-discovery — do **not** register it anywhere manually.
7. **DataTable** `app/DataTables/FoosDataTable.php` — `#[AllowDynamicProperties]`, public `$showTrashed`; `query()` branches `onlyTrashed()`/`withoutTrashed()`; `dataTable()` with `addIndexColumn()`, badge columns for booleans/enums via `rawColumns()`, `actions` column rendering `foo.actions`/`foo.actions_trashed` partials; `html()` with `serverSide/processing/pageLength/lengthMenu` matching Brand's; `getColumns()` mirroring Brand's column set adjusted for Foo's fields.
8. **Controller** `app/Http/Controllers/FoosController.php` — constructor-injects `FooService`; methods `index` (sets `$dataTable->showTrashed = false`), `create`, `store`, `show` (eager-loads `creator,updater,deleter` + `approvalLogs.actionedBy` if `--with-status`), `edit`, `update`, (`updateStatus` if `--with-status`), `destroy`, `trash` (`showTrashed = true`), `restore`, `delete`. Same try/catch shape as `BrandsController` (`GeneralException` → flash/JSON error; `Throwable` → generic message; `ModelNotFoundException` → 404 JSON on the AJAX endpoints).
9. **Views** `resources/views/foo/`: `index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`, `trashed.blade.php`, `actions.blade.php`, `actions_trashed.blade.php` — copy Brand's Blade structure (`@extends('layout.master')`, DataTable script push, SweetAlert2 confirm + jQuery AJAX + Toastify feedback via `sessionStorage`, no Vite/`@vite` directives — use the same plain `asset()` links already present in `layout/master.blade.php`).
10. **Routes** — add a `Route::group(['prefix' => 'foo', 'as' => 'foo.'], ...)` block in `routes/web.php` inside the existing `auth:web` group, positioned near related domains. Route names flat and unprefixed (`foo.index`, never `admin.foo.index`), matching every other domain group exactly (index/create/store/trash, then `{foo}`-scoped show/edit/update/destroy/restore/delete, plus `update-status` if `--with-status`).

## Blade View Rules

These narrow down step 9 with the exact conventions confirmed in `brand/*.blade.php`, `district/create.blade.php`, and `user/show.blade.php` — don't improvise beyond them.

1. **Form markup (create/edit)**: rows are `<div class="row mb-2">` with a `col-12 col-md-4 col-form-label text-md-end text-start` label (+ `form-mandatory` class if required) and a `col-12 col-md-8` input column. Every field shows `@error('field') <small class="text-danger">{{ $message }}</small> @enderror` directly under the input, plus `is-invalid` appended to the input's class when errored. Text inputs repopulate with `value="{{ old('field', $model->field ?? '') }}"` on edit forms. Form tag always carries `enctype="multipart/form-data"` even if the module has no file upload yet (matches Brand). Footer is a `card-footer` with Cancel (link to index) + Reset (`type="reset"`) on the left, Submit on the right. Page-level validation errors also get a dismissible `@if($errors->any())` alert block above the fields (seen in `create`, optional on `edit` — check the specific Brand file being copied).
2. **Show page is always a full page, never a modal.** `show.blade.php` renders a `card` with a definition-style `<table>` (`th` label right-aligned / `td` value left-aligned), Edit/Destroy (or Restore/Delete if trashed) actions below it. **Modals are reserved for secondary in-page actions only** — e.g. Brand's `#statusUpdateModal` (status change) and User's OTP-verification / change-password modals — never as a substitute for the show route itself. If the new module needs an action like this (status change, password reset, etc.), add a `<button data-bs-toggle="modal" data-bs-target="#xModal">` on the show page plus the modal markup + its own AJAX submit handler, following Brand's `statusUpdateModal` structure exactly (form-in-modal, `POST` via AJAX, inline `#xError` div for 422 validation errors, toast + reload on success/other-error).
3. **Cascading parent/child selects**: two valid mechanisms exist, pick based on which the field is —
   - **Parent select populated server-side**: if the form has a single dependent select whose options are always the full parent list (e.g. `district/create.blade.php`'s `division_id`), pass the collection from the controller (`$data['divisions'] = ...`) and render a plain `@foreach` options loop — no AJAX needed.
   - **Child select populated client-side on parent change**: if the child list depends on a *runtime-selected* parent (e.g. thana depending on whichever district was just picked), wire the existing `getDistrictsByDivision` / `getThanasByDistrict` GET endpoints via jQuery `.on('change')` + `$.ajax` to repopulate the child `<select>`'s options, matching whatever the current Districts/Thanas views already do for this — read those views' script blocks before replicating, don't invent a new endpoint per module.
4. **Dropdown option sources**: `App\Helper\FormHelper` currently defines several static `*Options()` methods (marital status, gender, education, etc.) but **grep confirms none are actually called anywhere in the app** — it's unused/future scaffolding, not an active convention. Do not assume a new field should pull from `FormHelper` just because a same-named list exists there. Only use it if the user explicitly says so for that field; otherwise hardcode the options inline (matching how every current module does it) or source them from a real relation/table.
5. **Toast/AJAX script block is one fixed snippet, reused verbatim per action type** — copy it rather than re-deriving: page-load toast-from-`sessionStorage` block (identical in every `index`/`show` view), then one `$(document).on('click', '.<action>-<module>', ...)` handler per destructive action (`destroy`/`restore`/`delete`) using `Swal.fire` confirm → `$.ajax` → `sessionStorage.setItem` for the toast message/type → redirect or `location.reload()`. Selector class and data attribute follow `.<action>-<module>` / `data-<module>-id` (e.g. `.destroy-brand`, `data-brand-id`) — rename only the `<module>` token, keep the rest of the handler structure identical.

## After generating

- Run `vendor/bin/pint` on the new files only (not the whole repo) and report any changes it made.
- Remind the user explicitly: **no route-level authorization exists yet** in this app (`AdminMiddleware`/`MemberMiddleware` were removed, nothing replaced them — see `PROJECT_CONTEXT.md` §10/§11). The new routes will be reachable by any authenticated user regardless of role until that's addressed — flag it, don't silently add ad-hoc gating that doesn't match how the rest of the app will eventually enforce RBAC.
- List every file created/modified so the user can review the diff before committing (never `git add`/`commit`/`push` — prepare the diff only, per standing instructions).
- If new Composer/npm dependencies would help (e.g. a slug-generation package), do not add them — ask first.
