# Notification System (Push)

A central, event-driven notification system. Business modules never send anything themselves; they fire an event, and the notification system decides **who** gets **what**, on **which channel**, and delivers it in the background.

Scope today: **push notifications only**. The design is channel-agnostic, so other channels can be added later without touching the core (see "Adding a new channel").

- Source of truth for build history and decisions: `docs/notification-progress.md`.
- Project conventions this system follows: `PROJECT_CONTEXT.md`.

---

## 1. Architecture

```
Business action (e.g. BrandService::storeBrand)
        │  fires
        ▼
Domain event (BrandCreated implements NotifiableEvent)
        │  (after the DB transaction is committed)
        ▼
ProcessNotificationEvent  (one auto-discovered listener, catches every error)
        ▼
NotificationService ── NotificationSetting (is the event enabled? which channels?)
        │            ── ReceiverResolver   (who? users / roles / permissions)
        │            ── TemplateRenderer    (text with {{variables}})
        ▼
notification_messages  +  notification_receivers (one row per user x channel)
        ▼
Queue (database)  ──►  SendNotificationJob (one per receiver)
        ▼
ChannelManager ──► PushNotificationChannel ──► PushProvider (log / Firebase later)
        ▼
Receiver status + notification_logs + overall notification status
```

Rules the code follows:

- Business modules contain **no** channel logic.
- Delivery is **always** asynchronous (queue). The HTTP request only saves rows and queues jobs.
- A notification problem **never** breaks the business action (listener catches everything and runs only after the transaction commits).
- Manual notifications use the **same** tables, queue, job and channels as automatic ones.

### Where things live

| Part | Location |
|---|---|
| Event contract | `app/Events/Contracts/NotifiableEvent.php` |
| Brand events (examples) | `app/Events/Brand/*` + trait `Concerns/HasBrandNotificationData` |
| Listener | `app/Listeners/Notifications/ProcessNotificationEvent.php` |
| Core services | `app/Services/Notification/` (`NotificationService`, `ReceiverResolver`, `TemplateRenderer`, `ChannelManager`, `ManualNotificationService`, `NotificationReportService`, `ResponseSanitizer`, `NotificationResult`) |
| Channels | `app/Services/Notification/Channels/` |
| Providers | `app/Services/Notification/Providers/` |
| Queue job | `app/Jobs/Notifications/SendNotificationJob.php` |
| Preferences / device tokens | `app/Services/UserNotificationPreferenceService.php`, `app/Services/DeviceTokenService.php` |
| Admin screens | controllers `Notification*Controller`, views `resources/views/notification*`, DataTables `app/DataTables/Notification*` |
| Config | `config/notification.php` |
| Tests | `tests/Feature/Notification/` |

---

## 2. Database schema

Tables are named after the project's conventions. The spec's `notifications` table is called **`notification_messages`** so it never clashes with Laravel's built-in `notifications` table.

| Table | Purpose | Key columns |
|---|---|---|
| `notification_channels` | Ways to send (push, sms...) | `code` (unique), `driver`, `is_active`, `sort_order`, soft deletes + audit columns |
| `notification_settings` | Which business event sends notifications | `event_code` (index), `name`, `permission_name/code` (information only), `is_active`, soft deletes + audit |
| `notification_setting_channels` | Setting <-> channel (many-to-many) | unique (`notification_setting_id`, `channel_id`), `is_active` |
| `notification_setting_receivers` | Who receives it | `receiver_type` (`user`/`role`/`permission`), `receiver_value` (user id / role name / permission name) |
| `notification_templates` | Text per setting and channel | unique (`notification_setting_id`, `channel_id`), `subject`, `title`, `body`, `variables` (JSON list of allowed names), `is_active` |
| `notification_messages` | One notification | `type` (automatic/manual), `event_code`, `title`, `message_body`, `data` (JSON), `priority`, `status`, `scheduled_at`, `created_by` |
| `notification_receivers` | One delivery = one user on one channel | `notification_message_id`, `user_id`, `channel_id`, `status`, `attempts`, `queued_at/sent_at/delivered_at/failed_at/next_retry_at`, `provider_message_id/status/response`, `error_message`; unique (message, user, channel) |
| `notification_logs` | Step-by-step delivery history | `notification_receiver_id`, `event`, `status`, `message`, `request_payload`, `response_payload`, `created_at` |
| `user_notification_preferences` | A user's own channel switch | unique (`user_id`, `channel_id`), `is_enabled` (no row = on) |
| `user_device_tokens` | Where push is sent | `user_id`, `token` (unique), `platform`, `is_active`, `last_used_at` |

Statuses (PHP enums in `app/Enums`):

- Notification: `draft, pending, processing, completed, partial, failed, cancelled`
- Delivery (receiver): `pending, queued, processing, sent, delivered, failed, cancelled`
- Type: `automatic, manual`. Priority: `low, normal, high, urgent`.

"Sent" means handed to the provider, **not** that the user saw it. `delivered` is only set when a provider confirms delivery (no provider does this yet).

---

## 3. Event naming convention

`<module>.<action>` in lowercase, e.g. `brand.created`. Format enforced on the Setting screen: `^[a-z0-9_]+(\.[a-z0-9_]+)+$`.

Standard actions: `created`, `updated`, `status_updated`, `deleted` (soft delete), `restored`, `force_deleted`. Other actions are fine (`purchase.approved`).

Brand mapping: `BrandCreated` = `brand.created`, `BrandUpdated` = `brand.updated`, `BrandStatusUpdated` = `brand.status_updated`, `BrandDestroyed` = `brand.deleted`, `BrandRestored` = `brand.restored`, `BrandDeleted` = `brand.force_deleted`.

---

## 4. Adding a new notification event (e.g. `purchase.approved`)

1. Make the event implement `App\Events\Contracts\NotifiableEvent`:
   - `notificationEventCode()` returns `'purchase.approved'`
   - `notificationData()` returns plain text/number values for templates (e.g. `purchase_no`, `url`)
   - `notificationActor()` returns the acting user (e.g. `Auth::user()`)
2. Fire it from the service after the action: `event(new PurchaseApproved($purchase));`
3. In **Notifications > Settings**, create a setting with event code `purchase.approved`, tick the channels, and add receivers (users, roles, permissions).
4. In **Notifications > Templates**, add a template for each channel. List every custom placeholder in "Variables".

No change is needed in `NotificationService`, `ChannelManager`, the queue or any channel. The listener is found automatically (do **not** register it in a provider).

Optionally, add a seeder like `database/seeders/BrandNotificationSeeder.php` to ship default settings.

---

## 5. Receiver resolution

`ReceiverResolver::resolve($setting)` reads the setting's rules (`notification_setting_receivers`) and returns a **query** of active users (a query so big groups are read in chunks):

- `user` rule: that user id.
- `role` rule: every user holding the role (Spatie).
- `permission` rule: every user holding the permission, directly or through a role.

Roles/permissions that no longer exist are ignored; duplicates are removed; inactive users are excluded. The notification system does not depend on permissions internally: `permission_name/code` on a setting is information only.

To support a new rule type (department, branch, manager...), add a type constant to `NotificationSettingReceiver` and a branch in `ReceiverResolver`.

---

## 6. Template variables

Syntax: `{{variable_name}}` (spaces inside the braces are allowed). Only these are replaced:

- Standard: `actor_name`, `actor_email`, `created_at`, `url`.
- Anything listed in the template's **Variables** field (e.g. `brand_name`).

Anything else, a missing value, or a non-text value becomes empty text. No PHP is ever evaluated. When a template lists Variables, the Template screen refuses any other placeholder except the standard ones above (standard ones never need listing). If Variables is empty, this check is skipped.

A notification's stored title/body come from the first channel's template; each channel renders its **own** template when it sends (`TemplateRenderer::renderForReceiver`). Manual notifications use the text typed by the sender (no placeholders).

---

## 7. Queue flow

1. `NotificationService::saveAndDispatch()` saves the message and receivers (chunks of `notification.chunk_size`) in one transaction. Users who switched the channel off get no row. If nobody is left, nothing is kept.
2. `dispatchJobs()` sets the notification to `processing`, flips receivers `pending` -> `queued` in chunks and dispatches one `SendNotificationJob` per receiver (`afterCommit`). A future `scheduled_at` delays the jobs.
3. The job re-checks the delivery (still active user/channel/notification, user preference), marks `processing`, calls `ChannelManager::send()`, stores the (sanitized) provider answer, writes log lines, and refreshes the notification status:
   - any delivery unfinished -> `processing`; all ok -> `completed`; all failed -> `failed`; mix -> `partial`; all cancelled -> `cancelled`.

**A queue worker must be running** (`QUEUE_CONNECTION=database`): `php artisan queue:work`. Without it, deliveries stay `queued`.

---

## 8. Retry mechanism

Config (`config/notification.php` -> `retry`): `max_attempts` = 3, `backoff` = [60, 300] seconds.

- Temporary error and attempts left: delivery stays `queued`, `next_retry_at` and `error_message` are saved, a `retry` log line is written, and the job releases itself back to the queue with the backoff delay.
- Permanent error (`NotificationResult::failure(..., retryable: false)`, e.g. no device, invalid device): fails immediately.
- All attempts used: `failed` with "Gave up after N attempts. Last error: ...".
- If the queue itself gives up (crash outside our handling), `SendNotificationJob::failed()` marks the delivery `failed`.
- Provider answers and error text are cleaned by `ResponseSanitizer` before saving (hides password/secret/token/authorization/api_key values, `Bearer xxx`, cuts at 2000 characters).

---

## 9. Channel architecture

```php
interface NotificationChannelInterface {
    public function send(NotificationReceiver $receiver): NotificationResult;
}
```

`ChannelManager` maps a channel's `driver` (column on `notification_channels`) to a class listed in `config('notification.drivers')`. No switch statements. A channel must not throw for normal failures; it returns `NotificationResult::success(...)` or `::failure($message, $retryable)`.

`PushNotificationChannel` sends to every active device token of the user (success if at least one device is reached; invalid tokens are switched off; only masked tokens like `***abc123` are stored).

## 10. Provider architecture

A channel decides **what** to send; a provider knows **how** to talk to one outside service.

```php
interface PushProvider {
    public function send(UserDeviceToken $device, string $title, string $body, array $data = []): NotificationResult;
}
```

The provider is chosen by `NOTIFICATION_PUSH_PROVIDER` (default `log`) through `config('notification.push.providers')`, bound in `AppServiceProvider::registerPushProvider()`. `log` (`LogPushProvider`) only writes the message to the application log. Keep credentials in `.env`/config, never in the database or in results.

### Adding a new provider (e.g. Firebase)

1. Create `app/Services/Notification/Providers/FirebasePushProvider.php` implementing `PushProvider`. Return `NotificationResult::failure(..., retryable: false, providerStatus: NotificationResult::STATUS_INVALID_TOKEN)` for dead device tokens.
2. Add it to `config/notification.php` -> `push.providers` (`'firebase' => FirebasePushProvider::class`) and read credentials from `config/services.php` / `.env`.
3. Set `NOTIFICATION_PUSH_PROVIDER=firebase`.

(Not built yet: needs Firebase credentials, a JWT/Google auth library for FCM v1 (ask before adding a package), and a browser service worker that registers tokens with `POST /device-token`.)

## 11. Adding a new channel (e.g. SMS)

1. Add a record in **Notifications > Channels** (code `sms`, driver `sms`).
2. Create `app/Services/Notification/Channels/SmsNotificationChannel.php` implementing `NotificationChannelInterface`, and a provider interface/class for the SMS vendor.
3. Register the driver in `config/notification.php` -> `drivers` (`'sms' => SmsNotificationChannel::class`).
4. In each Setting, tick the channel and add a template for it.

`NotificationService`, settings, receivers, queue and existing channels keep working unchanged. If the channel needs a recipient other than a device (phone, email), read it from the user in the channel's `send()`.

---

## 11b. "My Notifications" board and the real-time channel

- **Channel `realtime`** (`RealtimeNotificationChannel`): sending = broadcast a `NotificationReceived` event (`ShouldBroadcastNow`) to the user's private channel `App.Models.User.{id}` through Laravel Broadcasting (`config/broadcasting.php`; `BROADCAST_CONNECTION=log` for local use, `pusher` for real-time). A broadcaster error (e.g. Pusher down) is a temporary failure and follows the normal retry rules.
- **The board is the user's own deliveries on the board channel** (`config('notification.board_channel')`, default `realtime`) with status `sent`/`delivered` and `user_deleted_at` empty. No extra table: columns `read_at` and `user_deleted_at` were added to `notification_receivers`. An offline user simply sees the notification on the board later (unread = `read_at` empty).
- **Mark as read / Mark all / Delete** update only those two columns of the user's own row. "Delete" **hides** the notification from the user; the row stays for admins (details page, logs).
- `MyNotificationService` (`app/Services`) holds the logic; `MyNotificationsController` is thin. Routes (login only, own data only): `my-notification.index`, `.summary` (JSON for the header bell), `.read`, `.read-all`, `.destroy`.
- Header bell (`layout/includes/header.blade.php`) and its script (`layout/includes/notification-bell-script.blade.php`) read `my-notification.summary`; the dropdown shows the latest 10 (scrolls) with Mark as read / Delete and a "See All Notifications" link. The script exposes `window.notificationBell` (`prepend`, `setCount`, `load`) for live updates.
- `always_on_channels` (`config/notification.php`): channels users cannot switch off (default `realtime`, because the board is their inbox). They are hidden from the profile preference switches and ignored when receivers are created.
- **Pusher setup** (package `pusher/pusher-php-server` is installed):
  1. Create a Channels app in the Pusher dashboard and note app id, key, secret, cluster.
  2. In `.env`: `BROADCAST_CONNECTION=pusher`, `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER` (names are in `.env.example`).
  3. `php artisan config:clear` and **restart the queue worker** (the worker is the one that calls Pusher).
  - Only the **key and cluster** go to the browser, never the secret.
- **Authorization:** `routes/channels.php` lets a user listen only to `App.Models.User.{their own id}`. The auth endpoint is `POST /broadcasting/auth` (web + `auth:web`, registered by `withBroadcasting()` in `bootstrap/app.php`).
- **Browser:** `layout/includes/notification-realtime-script.blade.php` loads pusher-js 8.2.0 and Laravel Echo 1.16.1 from CDNs **only when** `BROADCAST_CONNECTION=pusher` and a key is set. On `.notification.received` it shows a pop-up, updates the bell (`window.notificationBell.prepend`) and triggers the `notification:received` document event (the My Notifications page listens and refreshes its list).

## 12. Screens and permissions

| Screen | Route name | Permission |
|---|---|---|
| Notification list + dashboard numbers + filters | `notification.index` | `notification.index` |
| Send notification (compose, preview, send) | `notification.create/preview/store/receiver-search` | `notification.send` |
| Notification details (deliveries, timeline) | `notification.show` | `notification.show` |
| Logs list (filters: date, event, channel, status, user, notification id; "Failure History" button) | `notification-log.index` | `notification-log.index` |
| Log details | `notification-log.show` | `notification-log.show` |
| Channels / Settings / Templates CRUD | `notification-channel.*`, `notification-setting.*`, `notification-template.*` | `notification-channel.*`, `notification-setting.*`, `notification-template.*` |
| My Notifications board + header bell | `my-notification.*` | any logged-in user (own data only) |
| My notification preferences (profile tab) | `notification-preference.update` | any logged-in user (own data only) |
| Register a push device | `device-token.store/destroy` | any logged-in user (own tokens only) |

Seed the permissions and a "Notification Manager" role: `php artisan db:seed --class=NotificationPermissionSeeder`. The super admin passes every check through the existing `Gate::before`.

Dashboard numbers cover the last 30 days. Success rate = (sent + delivered) / (sent + delivered + failed); waiting and cancelled deliveries are not counted in the rate.

---

## 13. Configuration

`config/notification.php`:

| Key | Default | Meaning |
|---|---|---|
| `drivers` | `['push' => PushNotificationChannel::class]` | channel driver -> class |
| `chunk_size` | 500 | receivers created / queued per chunk |
| `retry.max_attempts` | 3 | tries per delivery |
| `retry.backoff` | `[60, 300]` | seconds before the 2nd, 3rd... attempt |
| `manual.max_receivers` | 10000 | max people per manual notification |
| `push.provider` | env `NOTIFICATION_PUSH_PROVIDER` or `log` | which push provider |
| `board_channel` | `realtime` | channel whose deliveries form the "My Notifications" board |
| `always_on_channels` | `['realtime']` | channels users cannot switch off |

Seed starter data (all safe to re-run, none are in `AuthSeeder` because it wipes users/roles/permissions):

```bash
php artisan db:seed --class=NotificationChannelSeeder      # the push channel
php artisan db:seed --class=NotificationPermissionSeeder   # permissions + Notification Manager role
php artisan db:seed --class=BrandNotificationSeeder        # brand.* settings, push templates, receiver = Super Admin role
php artisan queue:work                                     # run the worker
```

---

## 14. Testing

```bash
php artisan test tests/Feature/Notification      # notification suite
php artisan test                                  # everything
```

Tests use the separate `build_stock_test` database (`.env.testing`) and the sync queue (jobs run immediately, standing in for a worker). Run test suites one at a time: they share that database.

Note: `tests/Feature/ExampleTest.php` fails because `/` redirects to login; this predates the notification system.

## 15. Troubleshooting

| Symptom | Check |
|---|---|
| No notification row after an action | Setting exists and is **active**? Event code matches exactly? At least one channel is on (globally and for the setting) **with an active template**? Receivers resolve to at least one active user? Everyone may have switched the channel off. Look for `Notification could not be created for event ...` in the log (Log Viewer). |
| Deliveries stay `queued` | The queue worker is not running: `php artisan queue:work`. |
| Delivery `failed` with "no active device" | The user has no row in `user_device_tokens` (or all are inactive). The browser/app must call `POST /device-token`. |
| Delivery `failed` after 3 attempts | Open the notification details or the log (filter Event = failed) and read the error and provider response. |
| Delivery `cancelled` | The user/channel/notification became inactive, or the user switched the channel off (see the `skipped` log line). |
| Notification stuck in `processing` | Some deliveries are `queued`/`processing`. Check the worker and `jobs` / `failed_jobs` tables. If the queue was down while dispatching, deliveries may still be `pending` (there is no automatic re-queue command yet). |
| A brand action seems to create nothing but the brand is saved | By design: notification errors never fail the business action. Read the application log. |
| Push "sent" but nothing appears on the device | Expected while `NOTIFICATION_PUSH_PROVIDER=log`: the log provider only writes to `storage/logs`. |

## 16. Known limits / future work

- Firebase provider and the browser service worker are not built.
- `delivered` status needs provider callbacks (webhooks); none exist yet.
- `priority` does not pick a different queue.
- No automatic re-queue of deliveries left `pending` when the queue was down.
- The profile page's older "Notification" tab is a static placeholder, unrelated to this system (the header bell and the My Notifications page are the real board).
- Real-time pop-ups need a Pusher account and keys in `.env`; without them the `log` broadcaster only writes to the log and the board/bell still work on page load.
- Only Brand events are wired. Other modules follow section 4.
