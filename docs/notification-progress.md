# Notification System: Progress Context

## Locked decisions
- Scope: **Push notifications only**. Architecture stays channel-agnostic (interface + ChannelManager); only the `push` channel is seeded and built.
- Table for the spec's `notifications` is named **`notification_messages`** (avoids a clash with Laravel's built-in `notifications` table, since `User` uses `Notifiable`).
- Push providers: `LogPushProvider` (dev/tests) now; `FirebasePushProvider` later when credentials exist. **No new composer packages** without asking.
- Rules: simple beginner-friendly code; obey `PROJECT_CONTEXT.md`; plain-English comment on every method; run `vendor/bin/pint`; never run git add/commit/push (user does).
- Listener must be auto-discovered (`handle()` method), never manually registered. Routes are flat (`notification.index`). Brand pattern for CRUD.

## Phase 0: Test setup (DONE, no code changes needed)
- `.env.testing` (git-ignored) already points tests at `build_stock_test`. Dev DB `build_stock` is not touched by `php artisan test`.
- `phpunit.xml` already uses `QUEUE_CONNECTION=sync`, array cache/session/mail.
- Baseline: 16 tests, 15 pass, **1 pre-existing failure**: `tests/Feature/ExampleTest.php` expects 200 on `/` but the app redirects to login (302). Unrelated to notifications; left untouched.

## Phase 1: Database (DONE)
- 10 migrations `2026_10_06_1100xx_*` run on dev DB (additive only) and on the test DB (via RefreshDatabase). Tables: notification_channels, notification_settings, notification_setting_channels, notification_setting_receivers, notification_templates, notification_messages, notification_receivers, notification_logs, user_notification_preferences, user_device_tokens.
- Deviations from spec (all deliberate): `notification_messages` instead of `notifications`; `notification_receivers.notification_message_id` instead of `notification_id`; added `notification_setting_receivers` (receiver_type user|role|permission, receiver_value) and `user_device_tokens`; status/type/priority are plain strings (PHP enums come in Phase 2); soft deletes + created_by/updated_by/deleted_by only on channels and settings (Brand pattern); templates use hard delete so the (setting, channel) unique key holds; `notification_settings.event_code` is a plain index (uniqueness will be validated in the Request, ignoring trashed rows).
- Seeders (standalone, NOT in AuthSeeder because it truncates users/roles/permissions; both safe to re-run): `NotificationChannelSeeder` (push), `NotificationPermissionSeeder` (29 `notification*.*` permissions + 5 `manage.*` parents + role "Notification Manager"). Run: `php artisan db:seed --class=...`.
- Added minimal `app/Models/NotificationChannel.php` (needed by the seeder); Phase 2 extends it.
- Pint passed. Tests: still 15 pass / 1 pre-existing failure (ExampleTest).

## Phase 2: Models and enums (DONE)
- Enums (`app/Enums`): NotificationType, NotificationPriority, NotificationStatus, NotificationReceiverStatus (each has `label()`; status enums have `isFinished()`).
- Models: NotificationChannel (finished), NotificationSetting, NotificationSettingReceiver (extra, for receiver rules), NotificationTemplate, NotificationMessage (table `notification_messages`), NotificationReceiver, NotificationLog (no updated_at), UserNotificationPreference, UserDeviceToken (`token` hidden in JSON).
- Scopes: `active`, `ordered`, `forEvent`, `withStatus`, `ofType`, `enabled`. `NotificationSetting::activeChannels()` = channel on globally AND on for the setting.
- `User` got `deviceTokens()` and `notificationPreferences()` via `UserRelationship` trait (only existing file touched; Pint only re-sorted the imports).
- Factories: NotificationChannel, NotificationSetting, NotificationMessage, NotificationReceiver.
- Not added (kept simple): Spatie activity logging on the config models. Can be added in Phase 3 if wanted.
- Tests: `tests/Feature/Notification/NotificationModelsTest.php`, 14 tests pass (relationships, casts, scopes, unique constraints). Full suite: 29 pass, 1 pre-existing failure (ExampleTest).

## Phase 3: Configuration screens (DONE)
- Three Brand-pattern modules, route prefixes `notification-channel`, `notification-setting`, `notification-template` (flat names, `permission:` middleware per route using the seeded permission names; super admin passes via the existing `Gate::before`).
  - Channels: list, create, edit (code is read-only), show, trash, restore, enable/disable. Soft delete refused while linked to a setting; force delete refused if delivery history exists.
  - Settings: CRUD + trash/restore + enable/disable; channel checkboxes (pivot) and receiver rules (users, roles, permissions via Select2). `event_code` format `module.action`, unique among non-deleted rows; restore blocked on duplicates; force delete blocked if notification history exists.
  - Templates: CRUD + enable/disable, hard delete, no trash; setting+channel fixed after create; `{{placeholder}}` check against the listed variables.
- Sidebar: "Notifications" group (`layout/includes/sidebar.blade.php`), shown with `@canany`.
- Deliberate deviations: NO Event/Listener classes for these config modules (they would be empty stubs); activity logging via Spatie `LogsActivity` on the 3 models instead; extra shared Blade partials for the forms; Brand's wrong-toast bug (`session('success')` in the error toast) was not copied.
- Template create form pre-selects setting/channel from `?notification_setting_id=&channel_id=` (used by the "Add Template" link on the Setting show page).
- Known note: saving a setting drops links to channels that are now inactive (form only lists active channels).
- Tests: 33 new CRUD tests + 14 model tests. Full suite: 62 pass, 1 pre-existing failure (ExampleTest). Parallel agents collided on the shared test DB; run suites one at a time.
- Pint passes on all new code. (Pint `--test` on the whole repo flags old migrations/seeders; not touched.)
- Views were verified by render tests only, not in a browser. Please click through the 3 screens once.

## Phase 4: Core services (DONE, no external providers)
All in `app/Services/Notification/`; config in `config/notification.php` (`drivers` map, `chunk_size` = 500).
- `TemplateRenderer`: replaces `{{name}}` only. Allowed names = standard (`actor_name`, `actor_email`, `created_at`, `url`) + the template's own `variables`. Disallowed/missing/array values become empty text; no PHP is ever evaluated.
- `ReceiverResolver`: `resolve(setting)` returns a **query** of active users from the setting's rules (user ids, roles, permissions incl. via roles); deleted roles/permissions are ignored; duplicates removed. Query (not list) so big groups can be chunked.
- `Channels/NotificationChannelInterface` (`send(NotificationReceiver): NotificationResult`) and `NotificationResult` (`success()` / `failure($msg, $retryable)`; provider response must be pre-sanitized).
- `ChannelManager`: `driver($name)`, `forChannel($channel)`, `send($receiver)`, `extend($name, $classOrClosure)`; looks up `config('notification.drivers')`. Unknown driver or class not implementing the interface throws `GeneralException`. No switch statements.
- `NotificationService`: `createAutomaticNotification($eventCode, $data, $actor)`, `createManualNotification(...)` (same tables/pipeline), `resolveNotificationSetting`, `resolveChannels` (channel on + on for setting + has an ACTIVE template), `resolveReceivers`, `createNotification`, `createReceivers` (chunked inserts, status pending). Returns `null` when nothing should be sent (no/inactive setting, no usable channel, nobody to notify). Message title/body come from the first channel's template; each channel renders its own final text at send time (Phase 6/7).
- Not yet in the service (by plan): `dispatchJobs()` (Phase 7), user-preference filtering (Phase 10), listener try/catch and after-commit handling (Phase 5).
- Tests: 24 new (renderer 6, resolver 5, channel manager 5, service 8). Pint clean.

## Phase 5: Event system (DONE)
- `App\Events\Contracts\NotifiableEvent` (`notificationEventCode()`, `notificationData()`, `notificationActor()`). Any event implementing it is handled by the one listener.
- `App\Listeners\Notifications\ProcessNotificationEvent`: `handle(NotifiableEvent)`, auto-discovered (confirmed with `php artisan event:list`; not registered anywhere). Implements `ShouldHandleEventsAfterCommit`, so it runs only after the business transaction commits (nothing is created if the action rolls back) and catches every `Throwable` (logs `Log::error`) so a notification problem can never fail the business action. Important: the after-commit callback runs inside `DB::commit()` in the service, so an uncaught exception there would have made BrandService report failure after a successful save.
- Brand events (6) now `implements NotifiableEvent` and use trait `Events/Brand/Concerns/HasBrandNotificationData` (data: model, model_id, action, brand_name, brand_status, url). Constructors/services untouched; actor = `Auth::user()`. Mapping: BrandCreated=brand.created, BrandUpdated=brand.updated, BrandStatusUpdated=brand.status_updated, BrandDestroyed (soft delete)=brand.deleted, BrandRestored=brand.restored, BrandDeleted (force)=brand.force_deleted.
- `BrandNotificationSeeder` (standalone, safe to re-run, NOT run on the dev DB yet): 6 settings, push channel, push template, receiver rule = role "Super Admin". Run `php artisan db:seed --class=BrandNotificationSeeder` to try it. Note: no `brand.*` permissions exist in this project (Brand routes are not permission-gated), so the default rule uses a role; `permission_name` on the settings is informational only. Until Phases 6/7, receivers just stay `pending`.
- Tests: 6 new end-to-end tests with the real BrandService (all 6 actions, switched-off setting, notification failure keeps the brand, rollback creates nothing, seeder idempotent). Full suite: 92 pass, 1 pre-existing failure (ExampleTest). Pint clean.
- To add a new event later: implement `NotifiableEvent` on it, fire it from the service, add a setting + template + receiver rule on the screens.

## Phase 6: Push channel (DONE)
- `Channels/PushNotificationChannel` (registered as driver `push` in `config/notification.php`): loads the user's ACTIVE `user_device_tokens`; no device = permanent failure (`providerStatus` `no_device`, not retryable); renders the channel's own template per receiver via the new `TemplateRenderer::renderForReceiver()` (manual notifications / no template = text saved on the notification); sends to every device; success if at least one device was reached; invalid-token results switch that token off; retryable only if some device had a temporary error. Stored response contains only masked tokens (`***last6`) and error text has full tokens replaced.
- `Providers/PushProvider` interface (`send(UserDeviceToken, title, body, data): NotificationResult`) and `LogPushProvider` (only logs the masked token). Provider chosen by `config('notification.push.provider')` (`NOTIFICATION_PUSH_PROVIDER`, default `log`) and bound in `AppServiceProvider::registerPushProvider()`. To add Firebase later: write a class implementing `PushProvider`, list it under `notification.push.providers`, set the env var. No package was added.
- `NotificationResult::failure()` got a 4th param `providerStatus`; constant `STATUS_INVALID_TOKEN`.
- `UserDeviceToken::maskedToken()`.
- Device token API (any logged-in user, own tokens only): `POST /device-token` (`device-token.store`, upsert by token; a token already on another user moves to the current user) and `DELETE /device-token` (`device-token.destroy`). `DeviceTokensController`, `DeviceTokenService`, `DeviceTokenRequest`. Nothing in the browser calls them yet: a real web client needs a service worker + Firebase JS (not built; waits for Firebase credentials and your decision).
- Tests: 13 new (channel 8, device token 5). Pint clean. Full suite: 105 pass, 1 pre-existing failure.
- Housekeeping: running Pint on the whole `config/` folder reformatted unrelated files; I reverted them. Only run Pint on specific files.

## Phase 7: Queue (DONE)
- `App\Jobs\Notifications\SendNotificationJob($receiverId)`: `afterCommit`; loads the receiver fresh; skips if already finished or missing; cancels (log `skipped`) if the notification is cancelled/inactive, the channel is inactive/deleted, or the user is inactive; otherwise `processing` (attempts+1, log) -> `ChannelManager::send()` -> `sent` (sent_at, provider_message_id/status/response, logs `provider_response` + `sent`) or `failed` (failed_at, error_message, log `failed`); an unexpected exception is caught and recorded as a failed delivery (retry policy comes in Phase 8); finally `NotificationService::refreshStatus()`.
- `NotificationService::dispatchJobs($message)` (called at the end of both create methods, after the DB transaction): sets the notification to `processing` BEFORE dispatching, then in chunks (`notification.chunk_size`) flips receivers `pending` -> `queued` (+queued_at, log `queued`) and dispatches one job each; a future `scheduled_at` delays the jobs. Returns the number queued.
- `NotificationService::refreshStatus()`: any pending/queued/processing -> `processing`; all sent/delivered -> `completed`; all failed -> `failed`; mix -> `partial`; all cancelled -> `cancelled`.
- `NotificationReceiver::addLog()` helper writes `notification_logs` lines.
- Full flow verified in tests: Brand created -> listener (after commit) -> notification + receivers -> queued -> job -> push channel -> log provider -> receiver `sent` + notification `completed`; admin without a device -> `failed` while the brand is still saved.
- Tests: 10 new job/dispatch tests + 2 new brand end-to-end tests; NotificationServiceTest now uses `Queue::fake()`. Full suite: 115 pass, 1 pre-existing failure. Pint clean.
- Operations: `QUEUE_CONNECTION=database` in `.env`. A worker must run for real sending: `php artisan queue:work` (or `queue:listen`). Not run/verified against the real dev queue yet (tests use the sync driver).
- Known limits (deliberate, for later): priority does not pick a separate queue; if the queue itself is down at dispatch time, receivers stay `pending` (a "re-queue pending" command could be added in Phase 13); no retry yet.

## Phase 8: Retry and failure handling (DONE)
- Config `config/notification.php` -> `retry`: `max_attempts` 3, `backoff` [60, 300] seconds (last value reused).
- `SendNotificationJob`: `$tries` = max attempts; `backoff()`; after a failed send: permanent (`retryable=false`) -> final `failed`; temporary and attempts left -> receiver stays `queued`, `next_retry_at` set, `error_message` saved, log `retry` ("Attempt N failed ... Retrying in X seconds"), and the job puts itself back with `release($seconds)` (Laravel queue backoff); attempts used up -> `failed` with "Gave up after N attempts. Last error: ...". `failed(?Throwable)` hook marks the delivery failed if the queue itself gives up (e.g. DB crash); it never touches finished deliveries. A successful retry clears `error_message`/`next_retry_at`. Attempts are counted in `notification_receivers.attempts`.
- `ResponseSanitizer` (`Services/Notification`): hides values of fields named like password/secret/token/authorization/api_key..., masks `Bearer xxx` and `password=xxx` style text, cuts values at 2000 characters. Applied by the job to `provider_response`, log payloads and `error_message` before saving (second safety net on top of channels masking tokens).
- A crash inside a channel is now treated as a temporary failure (retried), not final.
- Tests: 9 new (retry backoff/give-up/permanent/recovery, limits from config, `failed()` hook, secrets removed, long text). Full suite: 126 pass, 1 pre-existing failure. Pint clean.
- Note: the retry waits (60 s, 300 s) only really happen with a real queue driver; tests assert the delay passed to `release()`.

## Phase 9: User preferences (DONE)
- Rule: a channel is ON for a user unless a `user_notification_preferences` row says `is_enabled = false` (no row = on).
- `App\Services\UserNotificationPreferenceService`: `channelsFor(User)` (active channels + the user's choice), `setPreference(User, channelId, bool)` (rejects unavailable channels with `GeneralException`), `isEnabled(User, channelId)`, `switchedOff(userIds, channelIds)` (one query per chunk, used when creating receivers).
- `NotificationService::createReceivers()` skips switched-off user+channel pairs. New private `saveAndDispatch()` (shared by automatic and manual) saves message + receivers in one transaction and dispatches; if everybody opted out (0 receivers) the message is deleted and `null` is returned (nothing queued). `NotificationService` now also injects `UserNotificationPreferenceService`.
- `SendNotificationJob::reasonToSkip()` re-checks the preference, so a user who switches a channel off after queuing gets the delivery cancelled (log `skipped`: "The user switched this channel off.", attempts stay 0).
- UI: new "Notification Preferences" tab on the profile page (`resources/views/account/partials/notification-preferences.blade.php`, included from `account/profile.blade.php`; `ProfileController::profile` passes `notificationChannels`). Switches save instantly by AJAX; on error the switch reverts. Route `PUT /notification-preference` (`notification-preference.update`, own preferences only, no permission needed).
- Existing files touched: `ProfileController` (extra view data), `profile.blade.php` (tab + include), `routes/web.php`. The profile page's existing "Notification" tab (sample/placeholder in-app list) was left untouched.
- Tests: 11 new (defaults, skip, enabled, all-off creates nothing, manual respects it, job re-check, service list, endpoint on/off, own-only, validation/guest, profile page checked/unchecked). Full suite: 137 pass, 1 pre-existing failure. Pint clean.

## Phase 10: Manual notifications (DONE)
- Routes (`notification.*`, all permission protected): `GET /notification/create` (`notification.send`), `GET /notification/receiver-search` (`notification.send`, Select2 ajax: active users by name/email/username, max 20), `POST /notification/preview` (`notification.send`, JSON, saves nothing), `POST /notification` (`notification.send`, creates + queues, redirects to details), `GET /notification/{notificationMessage}` (`notification.show`).
- `NotificationsController` (thin), `SendManualNotificationRequest` (title<=255, message<=2000, priority enum, channels = active ids, users = active existing ids, roles = existing role names, at least one user or role, `scheduled_at` must be in the future), `ManualNotificationService` (`resolveUserIds` users+roles, active only, deduped; `preview`; `send`) which calls the SAME `NotificationService::createManualNotification()` -> chunked receivers -> queue -> job -> channel as automatic notifications. User preferences are respected; if nobody can receive it nothing is created and the user sees a message; cap `notification.manual.max_receivers` (config, default 10000).
- `ReceiverResolver::userIdsForRoles()` is now public (shared by the resolver and the manual service).
- UI: `notification/create.blade.php` (form, Select2 for users via ajax and roles, Preview modal -> Send) and `notification/show.blade.php` (details: type/event/title/message/priority/status badge/creator/scheduled, delivery counts by status, first 100 deliveries with attempts/sent time/next retry/provider id/error, latest 50 timeline steps). Sidebar: "Send Notification" under Notifications (shown with `notification.send`).
- Security: arbitrary users cannot send (permission on every route); receivers only active users; deliveries visible only with `notification.show`.
- Tests: 14 new (permissions, search, preview counts, validation, send + queue, scheduling, opt-out, cap, end-to-end push, details page). Full suite: 151 pass, 1 pre-existing failure. Pint clean.
- Not in this phase (Phase 11): notifications list/dashboard, log viewer with filters, failure history, richer details (per-receiver log drill-down), sidebar entry for the list. Details page currently reached only after sending (no list yet).

## Phase 11: Reporting, documentation and final QA (DONE)
- `notification.index` (permission `notification.index`): dashboard cards for the last 30 days (`NotificationReportService::summary()`: notification counts by status, delivery counts, derived success rate = (sent+delivered)/(sent+delivered+failed), "No data" when empty) + `NotificationsDataTable` with filters (type, status, event, date from/to) + link to failure history. Sidebar: "Notification List".
- `notification-log.index` / `.show` (permissions `notification-log.index` / `.show`): `NotificationLogsDataTable` with filters date from/to, event, channel, status, user (name/email), notification id; "Failure History" button (Event = failed); log details page with request/response payloads. Sidebar: "Notification Logs".
- Details page got a "Back to List" button.
- `docs/notification-system.md` written (architecture, schema, event naming, adding an event / channel / provider, receiver resolution, template variables, queue flow, retry, screens and permissions, configuration, testing, troubleshooting, known limits). `.env.example` got `NOTIFICATION_PUSH_PROVIDER=log`.
- Tests: 6 new reporting tests (permissions, summary numbers and rate, empty state, list filters, all 7 log filters, log details). Full suite: 157 pass, 1 pre-existing failure (ExampleTest). Notification suite alone: 142 tests.
- QA pass: Pint clean on all new/changed files; every new method has a plain-English docblock (only pre-existing methods in untouched code lack one); listener confirmed auto-discovered via `php artisan event:list`; no unrelated files modified (13 modified existing files, all intended, listed in the final report); scratch DBs created by parallel agents were dropped.

## Open items for the owner
1. Click through the screens in a browser (views are covered by render tests only): channel / setting / template CRUD, Send Notification + Preview modal, profile > Notification Preferences, Notification List + Logs filters.
2. Run `php artisan db:seed --class=NotificationPermissionSeeder` (and Channel + Brand seeders) on the dev DB, start `php artisan queue:work`, then try creating a brand.
3. Decide on Firebase (credentials + JWT package + service worker) when real push is needed.
4. Pre-existing: `tests/Feature/ExampleTest.php` fails (redirect to login), unrelated.

## Post-Phase 11 notes
- `docs/notification-user-guide-bn.md`: Bangla user manual and verification guide (setup, step-by-step tests A-K, codebase review guide, troubleshooting, commit preparation, sign-off checklist).
- Bug found while writing the guide and fixed: the template form wrongly rejected standard placeholders (`{{actor_name}}`, `{{actor_email}}`, `{{created_at}}`, `{{url}}`) when they were not listed in "Variables" (this would have blocked editing the seeded Brand templates from the screen). `ChecksTemplatePlaceholders` now always allows the standard ones; new test `test_standard_placeholders_are_allowed_without_being_listed`. Suite: 158 pass, 1 pre-existing failure (ExampleTest).

## Scope change: In-app board + real-time (Pusher) channel
Decision (owner, 2026-10-07): keep the push channel AND add a `realtime` channel (Pusher via Laravel Broadcasting). The "My Notifications" board is simply the user's own `realtime` deliveries in `notification_receivers`; Mark as Read / Delete update columns on that row. Firebase/FCM stays a separate, later option.

### Step 1: Board + realtime channel (DONE, works without a Pusher account)
- Migration `2026_10_07_100000_add_board_columns_to_notification_receivers_table` (`read_at`, `user_deleted_at`, index `receiver_board_index`) - already migrated on the dev DB (additive).
- `config/broadcasting.php` (pusher/log/null; default from `BROADCAST_CONNECTION`, currently `log`), `config/notification.php` (`realtime` driver, `board_channel`, `always_on_channels`).
- `Events/Notifications/NotificationReceived` (private channel `App.Models.User.{id}`, event `notification.received`), `Channels/RealtimeNotificationChannel`, `MyNotificationService`, `MyNotificationsController`, routes `my-notification.*`, page `my-notification/index.blade.php`.
- Header bell (static demo replaced by real data: count badge, latest 10, scrolls, Mark as read / Delete via AJAX, empty state, "See All" link) and its script `layout/includes/notification-bell-script.blade.php`; sidebar item "My Notifications". The old commented TODO blocks in `header.blade.php` / `master.blade.php` were removed because they are now implemented.
- `realtime` is always on: hidden from profile preferences and ignored by the opt-out check.
- `NotificationChannelSeeder` now also seeds `realtime`; `BrandNotificationSeeder` now adds missing channels/templates (push + realtime) to existing Brand settings (idempotent). **Not run on the dev DB yet**: run both seeders.
- Tests: 21 new (`MyNotificationBoardTest`, `RealtimeChannelTest`). Full suite: 179 pass, 1 pre-existing failure (ExampleTest). Pint clean on changed code (old files restored after an accidental whole-folder Pint run).

### Step 2: Pusher connection (DONE; keys are added by the owner)
- Package `pusher/pusher-php-server` ^7.3 added (only that package was added to `composer.lock`; `composer audit` reports 5 advisories in laravel/framework, league/commonmark, league/flysystem, phpseclib: pre-existing, unrelated).
- `routes/channels.php` (`App.Models.User.{id}` = own id only), `withBroadcasting()` in `bootstrap/app.php` (`POST /broadcasting/auth`, middleware `web` + `auth:web`).
- `layout/includes/notification-realtime-script.blade.php` (included from `master.blade.php`): loads pusher-js 8.2.0 + Echo 1.16.1 (CDN) only when `BROADCAST_CONNECTION=pusher` and a key exists; listens on the user's private channel for `.notification.received`; shows a Toastify pop-up (click opens the link or the board), updates the bell, and triggers `notification:received`; the My Notifications page refreshes its list on that event. Only the public key/cluster reach the browser.
- `.env.example`: `PUSHER_APP_ID/KEY/SECRET/CLUSTER` names added. The owner's `.env` was NOT touched.
- Tests: 7 new (`PusherConnectionTest`: own channel authorized with a signed response, other user's channel 403, guest 401, scripts absent when broadcasting is off or no key, scripts present with the key only and never the secret). Full suite: 186 pass, 1 pre-existing failure. Pint clean.
- Not verified here (needs a real Pusher account): the live browser connection, pop-up and Debug Console. See the Bangla guide, test M.
