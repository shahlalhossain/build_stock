# নোটিফিকেশন সিস্টেম — ইউজার ম্যানুয়াল ও যাচাই গাইড (বাংলা)

> **কার জন্য:** প্রজেক্ট ওনার/ডেভেলপার, যিনি কাল সকালে প্রসেস, ফাংশনালিটি ও কোডবেস নিজে যাচাই করবেন।
> **পরিধি:** শুধু **Push Notification** (SMS/Email/WhatsApp/Telegram/Slack বানানো হয়নি, তবে কাঠামো ভবিষ্যতের জন্য প্রস্তুত)।
> **সম্পূরক ডকুমেন্ট:** `docs/notification-system.md` (ইংরেজি টেকনিক্যাল ডক), `docs/notification-progress.md` (ধাপে ধাপে কী করা হয়েছে)।

---

## 0. এই গাইড কীভাবে ব্যবহার করবেন

| ধাপ | কী করবেন | আনুমানিক সময় |
|---|---|---|
| 1 | অধ্যায় 2: প্রস্তুতি (সিড, ওয়ার্কার, লগ) | 10 মিনিট |
| 2 | অধ্যায় 3: স্ক্রিন ও ফাংশনালিটি টেস্ট (চেকবক্সে টিক দিন) | 60–90 মিনিট |
| 3 | অধ্যায় 4: কোডবেস রিভিউ | 45–60 মিনিট |
| 4 | অধ্যায় 8: কমিট প্রস্তুতি | 10 মিনিট |

**সততার নোট (গুরুত্বপূর্ণ):** আমি স্ক্রিনগুলো (Blade ভিউ) শুধু **অটোমেটেড টেস্টে HTML রেন্ডার করে** যাচাই করেছি, **ব্রাউজারে চোখে দেখে নয়**। তাই ডিজাইন/লেআউট/জাভাস্ক্রিপ্ট (Select2, Preview modal, ফিল্টার) আপনার কাছেই প্রথম যাচাই হবে। কোনো কিছু ভাঙা পেলে নোট করে রাখুন।
এছাড়া **আসল পুশ ডেলিভারি (Firebase) বানানো হয়নি** — পুশ এখন শুধু লগ ফাইলে লেখা হয় (`log` প্রোভাইডার)।

> ⚠️ চলমান `php artisan serve` বন্ধ করবেন না। ওয়ার্কার ও লগ দেখার জন্য **আলাদা টার্মিনাল** খুলুন।

---

## 1. সিস্টেম এক নজরে (সহজ ভাষায়)

**মূল নিয়ম:** Brand/Product ইত্যাদি মডিউল নিজে কখনো পুশ/এসএমএস পাঠায় না। মডিউল শুধু বলে “একটা ঘটনা ঘটেছে” (Event)। বাকি সব নোটিফিকেশন সিস্টেম করে।

```
Brand তৈরি হলো
   ↓  (Event: brand.created)
লিসেনার ধরলো  ── ডেটাবেস কমিট হওয়ার পরে
   ↓
Notification Setting দেখে: ইভেন্টটি চালু? কোন কোন চ্যানেল? কারা পাবে?
   ↓
নোটিফিকেশন + প্রতি (ইউজার × চ্যানেল) একটি Receiver রো তৈরি
   ↓
Queue (database) → Worker → SendNotificationJob
   ↓
Push Channel → Push Provider (এখন: log)
   ↓
Receiver স্ট্যাটাস + লগ + নোটিফিকেশনের সামগ্রিক স্ট্যাটাস
```

**মনে রাখার 5টি বিষয়:**
1. পাঠানো **সবসময় ব্যাকগ্রাউন্ডে** (Queue)। তাই **Worker চালু না থাকলে কিছুই যাবে না** (রো `queued` হয়ে থাকবে)।
2. নোটিফিকেশনে সমস্যা হলেও **Brand সেভ হবে** (ব্যবসায়িক কাজ কখনো আটকায় না)।
3. ইউজার নিজের প্রোফাইল থেকে চ্যানেল বন্ধ করতে পারে; বন্ধ থাকলে তাকে পাঠানো হয় না।
4. “Sent” মানে প্রোভাইডারকে দেওয়া হয়েছে — ইউজার দেখেছে, এমন নিশ্চয়তা নয়।
5. ম্যানুয়াল নোটিফিকেশনও **একই পাইপলাইন** ব্যবহার করে (আলাদা কোনো সিস্টেম নেই)।

### স্ট্যাটাসের অর্থ

| নোটিফিকেশন স্ট্যাটাস | অর্থ |
|---|---|
| pending | তৈরি হয়েছে, কিউতে যায়নি |
| processing | কিছু ডেলিভারি এখনো বাকি |
| completed | সব ডেলিভারি সফল (sent/delivered) |
| partial | কিছু সফল, কিছু ব্যর্থ |
| failed | সব ব্যর্থ |
| cancelled | সব বাতিল |

| ডেলিভারি (Receiver) স্ট্যাটাস | অর্থ |
|---|---|
| pending → queued | কিউতে ঢুকেছে |
| processing | ওয়ার্কার পাঠাচ্ছে |
| sent | প্রোভাইডারকে দেওয়া হয়েছে |
| failed | ব্যর্থ (সর্বোচ্চ 3 বার চেষ্টার পর, অথবা স্থায়ী ত্রুটিতে সঙ্গে সঙ্গে) |
| cancelled | ইউজার/চ্যানেল নিষ্ক্রিয় বা ইউজার চ্যানেল বন্ধ করেছে |

---

## 2. প্রস্তুতি (Setup)

### 2.1 বর্তমান ডেভ ডেটাবেসের অবস্থা (আমি যাচাই করেছি)

- 10টি নতুন টেবিল মাইগ্রেট করা আছে।
- `push` চ্যানেল সিড করা আছে (চ্যানেল সংখ্যা = 1)।
- `notification.*` পারমিশন ও **Notification Manager** রোল সিড করা আছে।
- **`BrandNotificationSeeder` এখনো চালানো হয়নি** (Notification Setting সংখ্যা = 0)।
- Super Admin ইউজার: `admin@gmail.com` (type = admin, id = 1)।
- Queue: `database`, Log: `daily`।

### 2.2 সিড চালান (নিরাপদ, বারবার চালালেও ডুপ্লিকেট হয় না)

```bash
cd /var/www/html/personal/build_stock
php artisan db:seed --class=NotificationChannelSeeder
php artisan db:seed --class=NotificationPermissionSeeder
php artisan db:seed --class=BrandNotificationSeeder
```

প্রত্যাশিত ফলাফল: 6টি Brand সেটিং (`brand.created`, `brand.updated`, `brand.status_updated`, `brand.deleted`, `brand.restored`, `brand.force_deleted`), প্রতিটিতে Push চ্যানেল + টেমপ্লেট + রিসিভার = **Super Admin রোল**।

> সিড গুলো `AuthSeeder` এর ভেতরে **নেই** (কারণ AuthSeeder ইউজার/রোল/পারমিশন মুছে ফেলে)। তাই কখনো `php artisan db:seed` (ক্লাস ছাড়া) চালাবেন না।

### 2.3 Worker চালু করুন (টার্মিনাল 2)

```bash
php artisan queue:listen
```

(`queue:listen` প্রতি জবে কোড/কনফিগ নতুন করে লোড করে — টেস্টের দিন সুবিধাজনক। প্রোডাকশনে `queue:work` ব্যবহার হয়; কোড/`.env` বদলালে সেটি রিস্টার্ট করতে হয়।)

### 2.4 লগ দেখুন (টার্মিনাল 3)

```bash
tail -f storage/logs/laravel-$(date +%F).log | grep --line-buffered -E "push:log|Notification"
```

### 2.5 টেস্ট ডিভাইস টোকেন বানান

ব্রাউজারে পুশ রেজিস্টার করার ফ্রন্টএন্ড (Service Worker) এখনো নেই, তাই টেস্টের জন্য হাতে টোকেন তৈরি করুন:

```bash
php artisan tinker --execute='App\Models\User::find(1)->deviceTokens()->create(["token" => "device-token-abc123", "platform" => "web", "device_name" => "Test Browser"]);'
```

লগে টোকেন আসবে **মাস্ক করা** (`***abc123`) — পুরো টোকেন কোথাও লেখা হয় না। এটি যাচাইয়ের একটি পয়েন্ট।

### 2.5.1 দরকারি SQL (ফলাফল যাচাইয়ের জন্য)

```sql
SELECT id, type, event_code, title, status, priority, created_at FROM notification_messages ORDER BY id DESC LIMIT 10;
SELECT id, notification_message_id, user_id, channel_id, status, attempts, next_retry_at, provider_message_id, error_message FROM notification_receivers ORDER BY id DESC LIMIT 10;
SELECT id, notification_receiver_id, event, status, message, created_at FROM notification_logs ORDER BY id DESC LIMIT 20;
```

---

## 3. ফাংশনালিটি টেস্ট (ধাপে ধাপে)

লগইন: `admin@gmail.com` (Super Admin — সব পারমিশন পায়)। সাইডবারে নতুন মেনু গ্রুপ **“Notifications”** থাকবে, ভেতরে: Notification List, Send Notification, Notification Logs, Channels, Settings, Templates।

### টেস্ট A — মেনু ও পারমিশন

- [ ] Super Admin হিসেবে সাইডবারে “Notifications” গ্রুপ ও 6টি লিংক দেখা যাচ্ছে।
- [ ] একটি সাধারণ ইউজার (কোনো নোটিফিকেশন পারমিশন নেই) দিয়ে লগইন করলে মেনু গ্রুপ **দেখা যায় না**।
- [ ] ওই ইউজার সরাসরি `/notification`, `/notification/create`, `/notification-log`, `/notification-channel` ইত্যাদি URL-এ গেলে **403 (Forbidden)** পায়।
- [ ] শুধু `notification.send` পারমিশন দেওয়া ইউজার “Send Notification” দেখে, কিন্তু Notification List/Logs দেখে না।

পারমিশন দেওয়ার দ্রুত উপায়: Roles/Users স্ক্রিন থেকে, অথবা টার্মিনালে:

```bash
php artisan tinker --execute='$u = App\Models\User::find(ইউজার_আইডি); $u->givePermissionTo("notification.send");'
```

পারমিশনের তালিকা (সব `NotificationPermissionSeeder` থেকে): `notification.index/show/send`, `notification-log.index/show`, এবং `notification-channel.*`, `notification-setting.*`, `notification-template.*` (index, show, create, edit, update-status, destroy, trash, restore, delete — টেমপ্লেটে trash নেই)।

### টেস্ট B — Channels স্ক্রিন (`/notification-channel`)

- [ ] তালিকায় **Push Notification** (`code: push`) দেখা যায়।
- [ ] “Add New” দিয়ে একটি টেস্ট চ্যানেল বানান: নাম `Test SMS`, code `test_sms` (শুধু ছোট হাতের অক্ষর/সংখ্যা/আন্ডারস্কোর), driver `test_sms`।
- [ ] একই code দিয়ে আবার বানাতে গেলে ত্রুটি আসে (ইউনিক)। বড় হাতের অক্ষর বা স্পেস দিলেও ত্রুটি।
- [ ] Edit পেজে **code পরিবর্তন করা যায় না** (read-only)।
- [ ] Enable/Disable বাটনে স্ট্যাটাস বদলায় (টোস্ট আসে)।
- [ ] Destroy করলে Trash Box-এ যায়; Restore করলে ফেরত আসে।
- [ ] Push চ্যানেলটি কোনো Setting-এ ব্যবহৃত হলে Destroy **আটকে যায়** ("Remove this channel from all notification settings...")।
- [ ] Trash থেকে Permanent Delete: ডেলিভারি হিস্ট্রি আছে এমন চ্যানেলে **আটকে যায়**; টেস্ট চ্যানেলটি মুছে যায়।
- [ ] অ্যাক্টিভিটি লগে পরিবর্তনগুলো (`notification_channel` লগ) দেখা যায়।

পরিষ্কার করুন: টেস্ট চ্যানেল মুছে দিন (Push চ্যানেল রাখুন)।

### টেস্ট C — Settings স্ক্রিন (`/notification-setting`)

- [ ] 6টি Brand সেটিং তালিকায় আছে (সিড করার পর)।
- [ ] `Brand Created` সেটিং খুলে দেখুন (Show): চ্যানেল = Push, রিসিভার রুল = Role: Super Admin, টেমপ্লেট লিংক।
- [ ] নতুন সেটিং তৈরি: নাম `Purchase Approved`, event code `purchase.approved`। ফরম্যাট ভুল (`PurchaseApproved`, `purchase`) হলে ত্রুটি আসে।
- [ ] একই event code দিয়ে দ্বিতীয় সেটিং বানাতে গেলে ত্রুটি আসে।
- [ ] Channels বক্সে শুধু **সক্রিয়** চ্যানেল দেখা যায়; টিক দিলে লিংক হয়।
- [ ] “Who receives it” বক্সে Specific Users / Roles / Permissions (Select2 মাল্টি-সিলেক্ট) কাজ করে; সেভ করে Edit-এ ফিরলে নির্বাচন আগের মতো দেখা যায়।
- [ ] Enable/Disable কাজ করে। **Disable করলে ওই ইভেন্টে আর নোটিফিকেশন তৈরি হয় না** (টেস্ট E-তে দেখবেন)।
- [ ] Destroy → Trash → Restore। Restore করার সময় একই event code-এর আরেকটি সেটিং থাকলে আটকে যায়।
- [ ] যে সেটিং থেকে নোটিফিকেশন তৈরি হয়েছে তার Permanent Delete **আটকে যায়**।

### টেস্ট D — Templates স্ক্রিন (`/notification-template`)

- [ ] 6টি Brand টেমপ্লেট (Push) আছে।
- [ ] নতুন টেমপ্লেট: Setting = `Purchase Approved`, Channel = Push, Title `Purchase Approved`, Body `{{purchase_no}} approved by {{actor_name}}.`
  - Variables-এ শুধু `other_name` লিখে সেভ করুন → **ত্রুটি আসা উচিত** (`purchase_no` তালিকায় নেই; ত্রুটিতে নামটি দেখাবে)।
  - Variables-এ `purchase_no` লিখে সেভ করুন → সফল। (`{{actor_name}}` তালিকায় না লিখলেও চলে — স্ট্যান্ডার্ড ভ্যারিয়েবল সবসময় অনুমোদিত।)
  - Variables **ফাঁকা** রাখলে এই যাচাই চলে না (সেভ হয়ে যায়); তবে অনুমোদিত নয় এমন নাম পাঠানোর সময় খালি টেক্সট হয়ে যায়।
- [ ] একই Setting+Channel জোড়ায় দ্বিতীয় টেমপ্লেট → ত্রুটি (ইউনিক)।
- [ ] Edit-এ Setting ও Channel বদলানো যায় না।
- [ ] Enable/Disable কাজ করে। **টেমপ্লেট নিষ্ক্রিয় হলে ওই চ্যানেলে নোটিফিকেশন যায় না** (চ্যানেল “usable” ধরা হয় না)।
- [ ] Delete করলে সরাসরি মুছে যায় (টেমপ্লেটে Trash নেই)।

**টেমপ্লেট ভ্যারিয়েবল:** স্ট্যান্ডার্ড — `{{actor_name}}`, `{{actor_email}}`, `{{created_at}}`, `{{url}}`; Brand ইভেন্টে আরও — `{{brand_name}}`, `{{brand_status}}`। তালিকার বাইরের নাম খালি টেক্সট হয় (কোনো PHP চলে না)।

পরিষ্কার করুন: টেস্টের `Purchase Approved` সেটিং ও টেমপ্লেট মুছুন (বা নিষ্ক্রিয় রাখুন)।

### টেস্ট E — মূল ফ্লো: Brand → স্বয়ংক্রিয় নোটিফিকেশন (সবচেয়ে গুরুত্বপূর্ণ)

**প্রস্তুতি:** সিড হয়েছে (2.2), Worker চলছে (2.3), ডিভাইস টোকেন আছে (2.5)।

1. **Brand তৈরি করুন** (`/brand/create`), নাম যেমন `Test Brand 1`।
2. প্রত্যাশিত (কয়েক সেকেন্ডের মধ্যে):
- [ ] Brand সফলভাবে তৈরি হয়েছে (কোনো ত্রুটি নেই, পেজ দ্রুত লোড হয়েছে)।
- [ ] `/notification`-এ নতুন সারি: Type = Automatic, Event = `brand.created`, Status = **Completed**, Deliveries = 1 (রিসিভার Super Admin, চ্যানেল Push)।
- [ ] View → Details: Message = `Test Brand 1 has been created by Super Admin.`, ডেলিভারি স্ট্যাটাস **Sent**, Attempts = 1, Provider Message ID `log-...` দিয়ে শুরু, Timeline-এ ধাপ: `queued → processing → provider_response → sent`।
- [ ] লগ টার্মিনালে `[push:log] Push message` লাইন; টোকেন `***abc123` রূপে (পুরো টোকেন নেই)।
- [ ] `/notification-log`-এ একই ধাপগুলো দেখা যায়।

3. **বাকি 5টি ইভেন্ট** একে একে চালান এবং প্রতিটির জন্য নতুন নোটিফিকেশন তৈরি হচ্ছে কি না দেখুন:

| Brand-এ কাজ | প্রত্যাশিত Event code |
|---|---|
| Edit করে Update | `brand.updated` |
| Show পেজ থেকে স্ট্যাটাস পরিবর্তন (Approve/Reject) | `brand.status_updated` (বার্তায় নতুন স্ট্যাটাস থাকবে) |
| Destroy (Trash-এ পাঠানো) | `brand.deleted` |
| Trash থেকে Restore | `brand.restored` |
| Trash থেকে Permanent Delete | `brand.force_deleted` |

- [ ] ছয়টির প্রতিটিতে নোটিফিকেশন তৈরি হয়েছে।

4. **Setting নিষ্ক্রিয় করার প্রভাব:** `Brand Updated` সেটিং Disable করে Brand আপডেট করুন → **নতুন নোটিফিকেশন তৈরি হবে না** কিন্তু Brand আপডেট হবে। পরে আবার Enable করুন।

5. **Worker বন্ধ রেখে টেস্ট:** Worker বন্ধ করে (Ctrl+C) একটি Brand বানান → Brand সেভ হয়, নোটিফিকেশন তৈরি হয় এবং ডেলিভারি **`queued`** অবস্থায় থাকে, নোটিফিকেশন **Processing**। Worker আবার চালু করলে সঙ্গে সঙ্গে `sent` হয়ে নোটিফিকেশন **Completed**। এটাই প্রমাণ যে পাঠানো সম্পূর্ণ ব্যাকগ্রাউন্ডে।

### টেস্ট F — ব্যর্থতা ও Retry

**F1. ডিভাইস নেই (স্থায়ী ত্রুটি, রিট্রাই হবে না):**
- ডিভাইস টোকেন নিষ্ক্রিয় করুন:
  ```bash
  php artisan tinker --execute='App\Models\UserDeviceToken::query()->update(["is_active" => false]);'
  ```
- Brand তৈরি করুন।
- [ ] **Brand সফলভাবে সেভ হয়েছে** (নোটিফিকেশনের ব্যর্থতা Brand আটকায় না)।
- [ ] ডেলিভারি **Failed**, Attempts = 1, Error = “The user has no active device registered for push.”, নোটিফিকেশন স্ট্যাটাস **Failed**। রিট্রাই নেই (কারণ স্থায়ী ত্রুটি)।
- টোকেন আবার চালু করুন:
  ```bash
  php artisan tinker --execute='App\Models\UserDeviceToken::query()->update(["is_active" => true]);'
  ```

**F2. অস্থায়ী ত্রুটি → Retry/Backoff (সময় লাগে ~6 মিনিট):**
1. `.env`-এ `NOTIFICATION_PUSH_PROVIDER=wrong` লিখুন (ভুল প্রোভাইডার নাম — ইচ্ছাকৃত)।
2. `php artisan config:clear` চালান এবং **Worker রিস্টার্ট করুন**।
3. Brand তৈরি করুন।
4. প্রত্যাশিত:
- [ ] 1ম চেষ্টা ব্যর্থ → ডেলিভারি **queued**, `next_retry_at` ≈ 60 সেকেন্ড পরে, Error লেখা, Timeline-এ `retry` লাইন।
- [ ] 2য় চেষ্টা (~1 মিনিট পর) ব্যর্থ → পরের রিট্রাই ≈ 300 সেকেন্ড পরে।
- [ ] 3য় চেষ্টার পর **Failed**, Error = “Gave up after 3 attempts. Last error: ...”।
5. **ঠিক করুন:** `.env`-এ আবার `NOTIFICATION_PUSH_PROVIDER=log`, `php artisan config:clear`, Worker রিস্টার্ট।

(দ্রুত বিকল্প: `php artisan test --filter=NotificationRetryTest` — রিট্রাই লজিক সেকেন্ডে যাচাই করে।)

### টেস্ট G — ইউজার প্রেফারেন্স (প্রোফাইল)

- [ ] `/profile`-এ নতুন ট্যাব **“Notification Preferences”**; Push-এর সুইচ **অন** (ডিফল্ট)।
- [ ] সুইচ অফ করলে সঙ্গে সঙ্গে সেভ হয় (সবুজ টোস্ট)। ত্রুটি হলে সুইচ আগের অবস্থায় ফেরে।
- [ ] সুইচ অফ রেখে Brand তৈরি করুন → Super Admin-এর জন্য **কোনো রিসিভার/নোটিফিকেশন তৈরি হয় না** (কেবল একজনই রিসিভার, তাই পুরো নোটিফিকেশনই তৈরি হয় না)।
- [ ] সুইচ আবার অন করে Brand তৈরি করুন → আগের মতো কাজ করে।
- [ ] অতিরিক্ত: কিউতে থাকা অবস্থায় সুইচ অফ করলে ডেলিভারি **Cancelled** হয় (Timeline-এ “The user switched this channel off.”)।
- [ ] অন্য ইউজারের প্রেফারেন্স নিজের অ্যাকাউন্ট থেকে বদলানো যায় না (সার্ভার শুধু লগইন ইউজারেরটা বদলায়)।

### টেস্ট H — ম্যানুয়াল নোটিফিকেশন (`/notification/create`)

- [ ] ফর্মে: Title, Message, Priority, Channels (চেকবক্স), Specific Users (টাইপ করে সার্চ — কমপক্ষে 2 অক্ষর), Everyone with Role, Send Later At।
- [ ] খালি ফর্মে **Preview** চাপলে ত্রুটির তালিকা আসে (Message, Channels, Users...)।
- [ ] Title/Message লিখুন, Channel = Push, Role = Super Admin → **Preview**: মোডালে টেক্সট, Priority, Channels, People/Deliveries সংখ্যা, নমুনা নাম। **এই ধাপে কিছু সেভ/সেন্ড হয় না** (DB-তে নতুন রো নেই)।
- [ ] মোডাল থেকে **Send** → নোটিফিকেশন Details পেজে যায়; Type = Manual; ডেলিভারি `sent`; Created By = আপনি।
- [ ] প্রেফারেন্সে চ্যানেল বন্ধ থাকা ইউজার থাকলে Preview-তে “Skipped” সংখ্যা দেখায়।
- [ ] “Send Later At”-এ আগামী কয়েক মিনিট পরের সময় দিন → ডেলিভারি `queued` থাকে, নির্ধারিত সময়ে পাঠানো হয়। **অতীতের সময় দিলে** ত্রুটি।
- [ ] কোনো ইউজার/রোল না বেছে Send করলে ত্রুটি।
- [ ] নিষ্ক্রিয় ইউজার সার্চে আসে না।
- [ ] `notification.send` পারমিশন ছাড়া ইউজার ফর্ম খুলতে/সেন্ড করতে পারে না (403) — অর্থাৎ যে কেউ যে কাউকে নোটিফিকেশন পাঠাতে পারে না।

### টেস্ট I — রিপোর্টিং (`/notification` ও `/notification-log`)

**Notification List:**
- [ ] উপরে 4টি কার্ড (শেষ 30 দিন): Notifications, Deliveries, Sent/Delivered + Success rate, Failed Deliveries। নিচে ফর্মুলার ব্যাখ্যা: Success rate = (sent + delivered) ÷ (sent + delivered + failed)।
- [ ] ডেটা না থাকলে Success rate **“No data”** দেখায় (0% নয়)।
- [ ] কার্ডের সংখ্যা SQL-এর সাথে মেলান:
  ```sql
  SELECT status, COUNT(*) FROM notification_receivers WHERE created_at >= NOW() - INTERVAL 30 DAY GROUP BY status;
  ```
- [ ] ফিল্টার: Type, Status, Event (টাইপ করলে ~0.4 সেকেন্ড পর ফিল্টার), Date From/To, Reset বাটন।

**Notification Logs:**
- [ ] ফিল্টার: Date From/To, Event, Channel, Status, Notification ID, User (নাম/ইমেইল)।
- [ ] **Failure History** বাটন → Event = failed ফিল্টার হয়।
- [ ] View → লগের Request/Response ডেটা (সিক্রেট `[hidden]` বা মাস্ক করা থাকবে)।

### টেস্ট J — ডিভাইস টোকেন API (ঐচ্ছিক)

`POST /device-token` ও `DELETE /device-token` (লগইন ইউজার, নিজের টোকেন)। ব্রাউজার কনসোল থেকে (লগইন অবস্থায়, CSRF টোকেন দিয়ে) বা Postman দিয়ে চেষ্টা করতে পারেন। শর্ত: `token` আবশ্যক, `platform` = web/android/ios। একই টোকেন আবার পাঠালে ডুপ্লিকেট হয় না; অন্য ইউজার পাঠালে টোকেনটি তার নামে চলে যায়। এটি ভবিষ্যতের Service Worker-এর জন্য তৈরি।

### টেস্ট K — অটোমেটেড টেস্ট চালান

```bash
php artisan test tests/Feature/Notification   # 142টি টেস্ট, সব পাস হওয়ার কথা
php artisan test                              # মোট 157 পাস + 1টি পুরোনো ব্যর্থতা
```

**1টি ব্যর্থতা প্রত্যাশিত:** `ExampleTest` (“/” এ রিডাইরেক্ট হয় লগইনে, 302) — নোটিফিকেশন সিস্টেমের আগে থেকেই আছে, সম্পর্কহীন।
টেস্টগুলো আলাদা ডেটাবেসে (`build_stock_test`, `.env.testing`) চলে — আপনার ডেভ ডেটা অক্ষত থাকে। **টেস্ট একবারে একটি করে চালান** (একই টেস্ট ডেটাবেস শেয়ার করে)।

### টেস্ট L — My Notifications বোর্ড ও হেডার বেল (নতুন)

**প্রস্তুতি:** নতুন কলাম মাইগ্রেট করা আছে (ডেভ ডেটাবেসে আমি চালিয়েছি)। এখন সিড চালান (নিরাপদ, বারবার চালানো যায়):

```bash
php artisan db:seed --class=NotificationChannelSeeder   # নতুন realtime চ্যানেল যোগ হবে
php artisan db:seed --class=BrandNotificationSeeder     # Brand সেটিংয়ে realtime চ্যানেল ও টেমপ্লেট যোগ হবে
```

এরপর Worker চালু রেখে একটি Brand তৈরি করুন (Super Admin রিসিভার)।

- [ ] **Channels** স্ক্রিনে **Real-time Notification** (`realtime`) দেখা যায়।
- [ ] হেডারের বেল আইকনে লাল সংখ্যা (অপঠিত) আসে। বেল খুললে সর্বশেষ নোটিফিকেশন, নতুনগুলো **bold**।
- [ ] ড্রপডাউন ৮টির বেশি হলে ভেতরে **স্ক্রল** হয়; মোবাইল/ছোট স্ক্রিনে প্যানেল স্ক্রিনের বাইরে যায় না।
- [ ] ড্রপডাউনে ✓ (Mark as read) চাপলে bold চলে যায়, সংখ্যা কমে, **পেজ রিলোড ছাড়াই**। 🗑 (Delete) চাপলে সারিটি মিলিয়ে যায়, সংখ্যা ঠিক থাকে।
- [ ] সব মুছে ফেললে “No Notifications / You're all caught up!” দেখায়।
- [ ] “See All Notifications” অথবা সাইডবারের **My Notifications** → `/my-notifications` পেজ খোলে।
- [ ] পেজে: নতুনগুলোতে “New” ব্যাজ ও হালকা নীল ব্যাকগ্রাউন্ড; **All / Unread** ট্যাব; **Mark as Read**, **Mark All as Read**, **Delete** (সতর্কবার্তা সহ) কাজ করে; পেজিনেশন (১৫টি করে)।
- [ ] SQL দিয়ে মেলান: Read করলে `read_at` সেট হয়; Delete করলে `user_deleted_at` সেট হয় এবং **সারিটি মুছে যায় না**:
  ```sql
  SELECT id, user_id, status, read_at, user_deleted_at FROM notification_receivers ORDER BY id DESC LIMIT 10;
  ```
- [ ] Delete করা নোটিফিকেশন ইউজারের বোর্ডে আর আসে না, কিন্তু অ্যাডমিনের Notification Details পেজে (`/notification/{id}`) ডেলিভারি হিসেবে এখনো থাকে।
- [ ] অন্য ইউজারের নোটিফিকেশনে Read/Delete করার চেষ্টা (URL/ID বদলে) **404** দেয় (শুধু নিজেরটা বদলানো যায়)।
- [ ] প্রোফাইল → Notification Preferences ট্যাবে **Real-time চ্যানেল দেখা যায় না** (বোর্ড সবসময় চালু); শুধু Push-এর সুইচ আছে।
- [ ] Worker বন্ধ রেখে Brand বানালে বোর্ডে কিছু আসে না (ডেলিভারি `queued`); Worker চালু করলে আসে।

**Pusher যুক্ত করার আগে:** `BROADCAST_CONNECTION=log` থাকায় রিয়েল-টাইম টোস্ট আসবে না — শুধু লগে `Broadcasting [notification.received]` লাইন দেখা যাবে; বোর্ড ও বেল পেজ লোডে (বা বেল খুলে রিলোডে) ঠিকই কাজ করে। রিয়েল-টাইম টোস্ট ধাপ ২-এর কাজ (Pusher কী বসানোর পর)।

### টেস্ট M — Pusher রিয়েল-টাইম (আপনার Key বসানোর পর)

**১. Pusher থেকে Key সংগ্রহ:** Pusher ড্যাশবোর্ডে **Channels** অ্যাপ তৈরি করুন (ক্লাস্টার আপনার ব্যবহারকারীদের কাছাকাছি বেছে নিন), তারপর অ্যাপের **App Keys** থেকে `app_id`, `key`, `secret`, `cluster` নিন।

**২. `.env`-এ বসান (আপনি নিজে; Secret চ্যাটে বা গিটে দেবেন না):**

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=আপনার_app_id
PUSHER_APP_KEY=আপনার_key
PUSHER_APP_SECRET=আপনার_secret
PUSHER_APP_CLUSTER=আপনার_cluster
```

**৩. কনফিগ রিফ্রেশ ও Worker রিস্টার্ট (জরুরি):**

```bash
php artisan config:clear
# চলমান queue:listen/queue:work বন্ধ করে (Ctrl+C) আবার চালু করুন
php artisan queue:listen
```

(Pusher-কে কল করে **Worker**, তাই Worker রিস্টার্ট না করলে পুরোনো সেটিং চলে।)

**৪. যাচাই:**
- [ ] লগইন করা ব্রাউজারে DevTools → Console-এ লিখুন: `Echo.connector.pusher.connection.state` → **`connected`** আসার কথা। Network ট্যাবে `broadcasting/auth` কল **200** দিলে অথরাইজেশন ঠিক আছে।
- [ ] আরেকটি ট্যাব/ব্রাউজারে Brand তৈরি করুন → প্রথম ব্রাউজারে কয়েক সেকেন্ডের মধ্যে: **টোস্ট** (শিরোনাম — লেখা), বেলের **সংখ্যা +১**, বেলের তালিকার শীর্ষে নতুন সারি, `/my-notifications` খোলা থাকলে **তালিকা নিজে থেকে রিফ্রেশ**।
- [ ] টোস্টে ক্লিক করলে সংশ্লিষ্ট লিংক (যেমন Brand পেজ) খোলে।
- [ ] Pusher ড্যাশবোর্ডের **Debug Console**-এ `private-App.Models.User.1` চ্যানেলে `notification.received` ইভেন্ট দেখা যায়।
- [ ] **অফলাইন টেস্ট:** লগআউট করে (বা ব্রাউজার বন্ধ করে) Brand তৈরি করুন; পরে লগইন করলে বেলে অপঠিত সংখ্যা আছে এবং বোর্ডে নোটিফিকেশনটি আছে (Pusher-এর টোস্ট মিস হলেও কিছু হারায় না)।
- [ ] **নিরাপত্তা টেস্ট:** কনসোলে `Echo.private('App.Models.User.2')` (অন্যের আইডি) চেষ্টা করলে `broadcasting/auth` **403** দেয় — অন্যের চ্যানেল শোনা যায় না।
- [ ] পেজ সোর্সে (View Source) আপনার **Secret নেই** (শুধু Key ও Cluster আছে)।
- [ ] ভুল Secret দিয়ে (সাময়িক) Brand তৈরি করলে: ডেলিভারি retry (১ মিনিট, ৫ মিনিট) হয়ে শেষে **Failed**, ত্রুটি “Real-time broadcast failed: …”, আর বোর্ডে ওই নোটিফিকেশন আসে না। Secret ঠিক করে `config:clear` + Worker রিস্টার্ট করুন।
- [ ] `BROADCAST_CONNECTION=log`-এ ফেরালে পেজে Pusher স্ক্রিপ্ট লোড হয় না (কোনো বাইরের রিকোয়েস্ট নেই)।

**সমস্যা হলে:** (ক) টোস্ট আসে না → `config:clear` ও Worker রিস্টার্ট করেছেন? ব্রাউজারে অ্যাডব্লকার/প্রাইভেসি এক্সটেনশন `js.pusher.com` আটকাচ্ছে না তো? (খ) `broadcasting/auth` 419 → সেশন/CSRF শেষ, পেজ রিলোড করুন। (গ) 403 নিজের চ্যানেলেও → লগইন ইউজার আর চ্যানেলের আইডি এক কি না দেখুন। (ঘ) Worker লগে Pusher ত্রুটি → Key/Secret/Cluster মিলিয়ে দেখুন।

---

## 4. কোডবেস রিভিউ গাইড

### 4.1 পড়ার প্রস্তাবিত ক্রম (ভেতর থেকে বাইরে)

| # | ফাইল | কেন/কী দেখবেন |
|---|---|---|
| 1 | `docs/notification-system.md` | পুরো আর্কিটেকচারের সারাংশ |
| 2 | `database/migrations/2026_10_06_1100*` (10টি) | টেবিল, ইনডেক্স, ইউনিক কনস্ট্রেইন্ট |
| 3 | `app/Enums/*` | স্ট্যাটাস/টাইপ/প্রায়োরিটি (ম্যাজিক স্ট্রিং নেই) |
| 4 | `app/Models/Notification*.php`, `UserDeviceToken`, `UserNotificationPreference` | রিলেশন, স্কোপ, cast |
| 5 | `app/Events/Contracts/NotifiableEvent.php` + `app/Events/Brand/*` | ইভেন্ট কীভাবে নিজেকে বর্ণনা করে |
| 6 | `app/Listeners/Notifications/ProcessNotificationEvent.php` | একটি মাত্র লিসেনার, সব এরর ধরে, কমিটের পরে চলে |
| 7 | `app/Services/Notification/NotificationService.php` | কেন্দ্রীয় সমন্বয়কারী (তৈরি + কিউতে পাঠানো) |
| 8 | `ReceiverResolver`, `TemplateRenderer`, `ChannelManager` | কারা পাবে / টেক্সট / কোন ক্লাস পাঠাবে |
| 9 | `app/Jobs/Notifications/SendNotificationJob.php` | স্ট্যাটাস ফ্লো, রিট্রাই, ব্যর্থতা |
| 10 | `Channels/PushNotificationChannel.php`, `Providers/*` | চ্যানেল বনাম প্রোভাইডার পৃথক |
| 11 | `ResponseSanitizer`, `NotificationResult` | সিক্রেট পরিষ্কার, ফলাফলের ধরন |
| 12 | `ManualNotificationService`, `UserNotificationPreferenceService`, `DeviceTokenService` | ম্যানুয়াল/প্রেফারেন্স/টোকেন |
| 13 | Controllers (`Notification*Controller`, `DeviceTokensController`) | পাতলা কি না |
| 14 | `routes/web.php` (“Notification Management” অংশ) | প্রতিটি রুটে `permission:` মিডলওয়্যার |
| 15 | `resources/views/notification*`, `account/partials/notification-preferences.blade.php` | UI |
| 16 | `tests/Feature/Notification/*` | কী কী যাচাই হচ্ছে |

### 4.2 আর্কিটেকচার-নিয়ম যাচাই (গ্রেপ দিয়ে প্রমাণ করুন)

```bash
# 1) BrandService এ চ্যানেল-নির্দিষ্ট কোড নেই (ফলাফল শুধু আগের event() লাইনগুলো)
grep -n -i "push\|sms\|email\|notification" app/Services/BrandService.php

# 2) লিসেনার আলাদাভাবে রেজিস্টার করা হয়নি (ফাঁকা হওয়ার কথা)
grep -rn "ProcessNotificationEvent" app/Providers bootstrap

# 3) অটো-ডিসকভারি কাজ করছে
php artisan event:list | grep -A1 NotifiableEvent

# 4) কোনো switch/if-else চ্যানেল বাছাই নেই; ম্যাপিং কনফিগে
grep -rnE "switch \(|=== 'sms'|=== 'push'" app/Services/Notification app/Jobs/Notifications
cat config/notification.php

# 5) সিক্রেট/টোকেন লগে পুরো যায় না (শুধু maskedToken)
grep -rn "maskedToken\|ResponseSanitizer" app | head -20

# 6) কিউ ছাড়া সরাসরি পাঠানো নেই — channel->send() শুধু job/ChannelManager-এ
grep -rn "->send(" app/Services/Notification app/Jobs app/Http app/Listeners
```

### 4.3 রিভিউ চেকলিস্ট

- [ ] ব্যবসায়িক মডিউল (BrandService) নোটিফিকেশন ডেলিভারি কোড রাখে না।
- [ ] লিসেনার `ShouldHandleEventsAfterCommit` ইমপ্লিমেন্ট করে এবং `try/catch Throwable` দিয়ে সব এরর লগে লেখে।
- [ ] জব `afterCommit`, `$tries = 3`, `backoff()`, `failed()` হুক আছে।
- [ ] স্থায়ী ত্রুটিতে (`retryable: false`) রিট্রাই হয় না।
- [ ] `notification_receivers`-এ (message, user, channel) ইউনিক — ডুপ্লিকেট ডেলিভারি অসম্ভব।
- [ ] রিসিভার তৈরি `chunkById` দিয়ে চাঙ্কে (10 হাজার+ ইউজারে মেমরি নিরাপদ)।
- [ ] প্রতিটি নোটিফিকেশন রুটে `permission:` মিডলওয়্যার; ম্যানুয়াল সেন্ডে `notification.send`।
- [ ] ডিভাইস টোকেন JSON-এ লুকানো (`$hidden`), লগে মাস্ক করা।
- [ ] প্রতিটি মেথডের ওপর সহজ বাংলা/ইংরেজি ডকব্লক (নন-প্রোগ্রামারদের বোঝার জন্য) — র‍্যান্ডম 10টি ফাইল খুলে দেখুন।
- [ ] কোড সহজ: ছোট মেথড, একক দায়িত্ব, ম্যাজিক স্ট্রিং নেই (Enums)।
- [ ] Brand পেটার্ন অনুসরণ: Controller → Request → Service → DataTable → Blade; ফ্ল্যাট রুট নাম (`notification-channel.index`, `admin.` প্রিফিক্স নেই)।
- [ ] নতুন কোনো কম্পোজার প্যাকেজ যোগ হয়নি (`git diff composer.json` ফাঁকা)।
- [ ] Vite/নতুন ফ্রন্টএন্ড ফ্রেমওয়ার্ক যোগ হয়নি (Blade + jQuery + Bootstrap)।

### 4.4 ইচ্ছাকৃত বিচ্যুতি (স্পেক থেকে আলাদা) — বিবেচনা করে নিন

| বিষয় | কী করা হয়েছে | কেন |
|---|---|---|
| টেবিলের নাম | `notifications` → **`notification_messages`** | Laravel-এর বিল্ট-ইন `notifications` টেবিলের সাথে সংঘাত এড়াতে (User মডেলে `Notifiable` আছে) |
| কলামের নাম | `notification_id` → `notification_message_id` | উপরের কারণে স্পষ্টতা |
| অতিরিক্ত টেবিল | `notification_setting_receivers`, `user_device_tokens` | কারা পাবে তা কনফিগে রাখতে (হার্ড-কোড নয়); পুশের ঠিকানা রাখতে |
| Providers টেবিল | বানানো হয়নি | কনফিগ/`.env` দিয়ে প্রোভাইডার বাছাই (সিক্রেট DB-তে নয়) |
| Status/Type কলাম | enum() নয়, string; PHP Enum দিয়ে নিয়ন্ত্রণ | ভবিষ্যতে নতুন মান যোগ সহজ |
| Soft delete | শুধু Channels ও Settings-এ (Brand পেটার্ন) | টেমপ্লেটে হার্ড ডিলিট, যাতে (setting, channel) ইউনিক থাকে |
| Event/Listener ক্লাস | কনফিগ স্ক্রিনগুলোর জন্য বানানো হয়নি | ফাঁকা স্টাব হতো; পরিবর্তন Spatie Activity Log-এ ধরা হয় |
| পারমিশন-ভিত্তিক রিসিভার (Brand) | ডিফল্ট রুল = **Super Admin রোল** | প্রজেক্টে `brand.*` পারমিশনই নেই (Brand রুট পারমিশন-গেটেড নয়) |
| Firebase | বানানো হয়নি, `log` প্রোভাইডার | ক্রেডেনশিয়াল/লাইব্রেরি এখনো নেই (নতুন প্যাকেজ আপনার অনুমতি ছাড়া নয়) |
| Phase 9 (অতিরিক্ত চ্যানেল) | বাদ | আপনার নির্দেশ: শুধু Push |
| Product/Purchase ইভেন্ট | শুধু Brand ওয়্যার করা | আপনার নির্দেশ: শুধু প্রাসঙ্গিক কাজ |

### 4.5 মূল পরিবর্তনের পরিসর (`git`)

আমি `git add/commit/push` চালাইনি। সব পরিবর্তন আনস্টেজড। পরিবর্তিত পুরোনো ফাইল **13টি** (বাকি সব নতুন):

`.env.example`, `routes/web.php`, `resources/views/layout/includes/sidebar.blade.php`, `resources/views/account/profile.blade.php`, `app/Http/Controllers/ProfileController.php`, `app/Models/Traits/Relationship/UserRelationship.php`, `app/Providers/AppServiceProvider.php`, এবং 6টি Brand ইভেন্ট (`app/Events/Brand/Brand*.php`)।

```bash
git status --short | grep -v '^??'     # পরিবর্তিত পুরোনো ফাইল
git diff --stat                        # পরিবর্তনের পরিমাণ
git status --short | grep '^??'        # নতুন ফাইল
```

অপ্রত্যাশিত কোনো ফাইল (যেমন অন্য মডিউল, `config/*` পুরোনো ফাইল) পরিবর্তিত দেখলে জানান।

### 4.6 কোড স্টাইল চেক

```bash
vendor/bin/pint --test app/Services/Notification app/Jobs/Notifications app/Listeners/Notifications app/Http/Controllers/Notification*.php tests/Feature/Notification
```

> সতর্কতা: পুরো `config/` বা পুরো রিপোজিটরিতে `vendor/bin/pint` চালাবেন না — পুরোনো ফাইল রিফরম্যাট হয়ে অপ্রয়োজনীয় ডিফ তৈরি করে (একবার এমন হয়েছিল, আমি ফেরত নিয়েছি)।

---

## 5. ডেটাবেস টেবিল — এক নজরে

| টেবিল | কাজ |
|---|---|
| `notification_channels` | পাঠানোর মাধ্যম (push…) |
| `notification_settings` | কোন ইভেন্টে নোটিফিকেশন যাবে |
| `notification_setting_channels` | সেটিং ↔ চ্যানেল |
| `notification_setting_receivers` | কারা পাবে (user / role / permission) |
| `notification_templates` | সেটিং+চ্যানেল ভিত্তিক টেক্সট |
| `notification_messages` | একটি নোটিফিকেশন (automatic/manual) |
| `notification_receivers` | প্রতি (ইউজার × চ্যানেল) একটি ডেলিভারি + ট্র্যাকিং |
| `notification_logs` | ধাপে ধাপে ইতিহাস (ডিবাগিংয়ের জন্য) |
| `user_notification_preferences` | ইউজারের নিজের চ্যানেল অন/অফ (রো না থাকলে = অন) |
| `user_device_tokens` | পুশ পাঠানোর ঠিকানা |

---

## 6. নতুন কিছু যোগ করার সংক্ষিপ্ত নিয়ম

**নতুন ইভেন্ট (যেমন `purchase.approved`):** (1) ইভেন্ট ক্লাসে `NotifiableEvent` ইমপ্লিমেন্ট করুন (3টি মেথড); (2) সার্ভিস থেকে `event(...)` ফায়ার করুন; (3) স্ক্রিনে Setting + Template + Receiver যোগ করুন। `NotificationService`/Queue/Channel কিছুই বদলাতে হয় না। বিস্তারিত: `docs/notification-system.md` সেকশন 4।

**নতুন চ্যানেল (যেমন SMS):** চ্যানেল রেকর্ড → `SmsNotificationChannel` (ইন্টারফেস ইমপ্লিমেন্ট) → প্রোভাইডার ক্লাস → `config/notification.php` এর `drivers`-এ রেজিস্টার → টেমপ্লেট। সেকশন 11।

**নতুন প্রোভাইডার (যেমন Firebase):** `PushProvider` ইমপ্লিমেন্ট → `config/notification.php`-এর `push.providers`-এ যোগ → `.env`-এ `NOTIFICATION_PUSH_PROVIDER=firebase`। সেকশন 10।

---

## 7. সমস্যা হলে কী দেখবেন

| লক্ষণ | সম্ভাব্য কারণ/সমাধান |
|---|---|
| ইভেন্টের পর কোনো নোটিফিকেশন তৈরি হয়নি | (1) Setting আছে ও **Active**? (2) event code হুবহু মেলে? (3) চ্যানেল চালু + সেটিংয়ে টিক + **সক্রিয় টেমপ্লেট** আছে? (4) রিসিভার রুল অন্তত একজন সক্রিয় ইউজার দেয়? (5) সবাই প্রেফারেন্সে চ্যানেল বন্ধ করেছে? (6) লগে `Notification could not be created for event` খুঁজুন |
| ডেলিভারি `queued` হয়ে আছে | Worker চলছে না → `php artisan queue:listen` |
| ডেলিভারি `failed`: “no active device” | ইউজারের সক্রিয় `user_device_tokens` নেই (2.5 দেখুন) |
| `failed`: “Gave up after 3 attempts” | Details/Logs-এ Error ও Provider response দেখুন |
| ডেলিভারি `cancelled` | ইউজার/চ্যানেল নিষ্ক্রিয় বা ইউজার চ্যানেল বন্ধ করেছে (`skipped` লগ) |
| নোটিফিকেশন `processing`-এ আটকে | কিছু ডেলিভারি এখনো `queued/processing`; Worker ও `jobs`/`failed_jobs` টেবিল দেখুন |
| `.env` বদলালাম, কাজ করছে না | `php artisan config:clear` + Worker রিস্টার্ট |
| “sent” কিন্তু ডিভাইসে কিছু আসেনি | স্বাভাবিক — `log` প্রোভাইডার শুধু `storage/logs`-এ লেখে |
| পারমিশন দিলাম কিন্তু 403 | Spatie ক্যাশ: `php artisan permission:cache-reset` |

---

## 8. কমিট প্রস্তুতি (আপনি নিজে চালাবেন)

আমি কোনো `git` কমান্ড চালাইনি (আপনার নিয়ম অনুযায়ী)। পুরো কাজ একসাথে স্টেজ করতে চাইলে:

```bash
git add database/migrations/2026_10_06_1100* database/migrations/2026_10_07_100000_* \
        database/seeders/Notification* database/seeders/BrandNotificationSeeder.php \
        database/factories/Notification*.php \
        app/Enums app/Models/Notification*.php app/Models/UserDeviceToken.php app/Models/UserNotificationPreference.php \
        app/Models/Traits/Relationship/UserRelationship.php \
        app/Events app/Listeners/Notifications app/Jobs/Notifications \
        app/Services/Notification app/Services/DeviceTokenService.php app/Services/UserNotificationPreferenceService.php \
        app/Services/Notification*Service.php app/Services/MyNotificationService.php \
        app/Http/Controllers/Notification*.php app/Http/Controllers/MyNotificationsController.php app/Http/Controllers/DeviceTokensController.php app/Http/Controllers/ProfileController.php \
        app/Http/Requests/Notification* app/Http/Requests/DeviceToken \
        app/DataTables/Notification*.php app/Providers/AppServiceProvider.php \
        config/notification.php config/broadcasting.php routes/web.php routes/channels.php bootstrap/app.php \
        composer.json composer.lock .env.example \
        resources/views/notification* resources/views/my-notification resources/views/account \
        resources/views/layout \
        tests/Feature/Notification docs/notification-*.md
git status    # নিশ্চিত হয়ে নিন কিছু বাদ পড়েনি/বাড়তি যায়নি
```

প্রস্তাবিত কমিট মেসেজ (conventional commits), একটি বা ধাপভিত্তিক:

```
feat(notification): add push notification system

Event-driven notification system with channel/provider abstraction,
settings, templates, receivers, queue delivery with retry, user
preferences, manual notifications, reporting screens, tests and docs.
```

ধাপভিত্তিক করতে চাইলে `docs/notification-progress.md`-এর Phase 1–11 অনুযায়ী ভাগ করতে পারেন (প্রতি ধাপের শেষে আমি যে কমিট মেসেজ প্রস্তাব করেছিলাম সেগুলো ব্যবহার করুন)।

---

## 9. পরিচিত সীমাবদ্ধতা ও আপনার সিদ্ধান্ত বাকি

**সীমাবদ্ধতা:**
- আসল পুশ (Firebase) ও ব্রাউজার Service Worker নেই — পুশ এখন লগে লেখা হয়।
- `delivered` স্ট্যাটাস কখনো সেট হয় না (প্রোভাইডারের কলব্যাক/ওয়েবহুক নেই); ডেলিভারি `sent` পর্যন্ত যায়।
- `priority` আলাদা কিউ বেছে নেয় না (শুধু তথ্য হিসেবে সংরক্ষিত)।
- কিউ বন্ধ থাকা অবস্থায় ডিসপ্যাচ হলে রিসিভার `pending` থেকে যেতে পারে; স্বয়ংক্রিয় “re-queue” কমান্ড নেই।
- শুধু Brand ইভেন্ট ওয়্যার করা; অন্য মডিউল এই গাইডের অধ্যায় 6 অনুযায়ী যোগ করতে হবে।
- প্রোফাইলের পুরোনো “Notification” ট্যাবটি স্ট্যাটিক ডেমো (এই সিস্টেমের অংশ নয়), অপরিবর্তিত রাখা হয়েছে।
- Notification Details পেজে প্রথম 100টি ডেলিভারি ও শেষ 50টি টাইমলাইন ধাপ দেখায় (বিশাল তালিকার জন্য)।

**আপনার সিদ্ধান্ত:**
1. Firebase কবে/কীভাবে (ক্রেডেনশিয়াল + JWT/Google Auth লাইব্রেরি — অনুমতি চাইব + Service Worker)?
2. `ExampleTest` ঠিক করা হবে কি না (আলাদা ছোট কাজ)।
3. আর কোন মডিউলে (Product, Purchase…) ইভেন্ট ওয়্যার করতে চান।
4. Brand ডিফল্ট রিসিভার (Super Admin রোল) ঠিক আছে কি না।

---

## 10. চূড়ান্ত সাইন-অফ চেকলিস্ট

| ক্ষেত্র | যাচাই হয়েছে? | নোট |
|---|---|---|
| সিড ও Worker প্রস্তুতি (অধ্যায় 2) | ☐ | |
| মেনু ও পারমিশন (টেস্ট A) | ☐ | |
| Channels CRUD (B) | ☐ | |
| Settings CRUD + রিসিভার (C) | ☐ | |
| Templates CRUD + ভ্যারিয়েবল (D) | ☐ | |
| Brand → নোটিফিকেশন (6টি ইভেন্ট) (E) | ☐ | |
| Worker বন্ধ/চালু আচরণ (E.5) | ☐ | |
| ব্যর্থতা: ডিভাইস নেই, Brand সেভ হয় (F1) | ☐ | |
| Retry/Backoff (F2) | ☐ | |
| ইউজার প্রেফারেন্স (G) | ☐ | |
| ম্যানুয়াল: Preview/Send/Schedule (H) | ☐ | |
| লিস্ট/ড্যাশবোর্ড/লগ ফিল্টার (I) | ☐ | |
| অটোমেটেড টেস্ট (K) | ☐ | |
| কোডবেস রিভিউ (অধ্যায় 4) | ☐ | |
| কমিট (অধ্যায় 8) | ☐ | |
