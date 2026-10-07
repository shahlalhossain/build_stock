<?php

namespace Database\Seeders;

use App\Models\NotificationChannel;
use App\Models\NotificationSetting;
use App\Models\NotificationSettingReceiver;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

/**
 * Adds the starting notification settings for the six Brand events. Each gets the
 * Push and Real-time channels (the ones that exist), a template for each, and
 * "Super Admin role" as the receivers. Everything can be changed later on the Notification screens.
 * Safe to run many times: anything that already exists is left alone, missing parts are added.
 * Run with: php artisan db:seed --class=BrandNotificationSeeder
 */
class BrandNotificationSeeder extends Seeder
{
    /**
     * The channels each Brand setting uses (only those found in the database are used).
     */
    protected const CHANNEL_CODES = ['push', 'realtime'];

    /**
     * Event code => [setting name, permission name (information only), title, body].
     */
    protected const EVENTS = [
        'brand.created' => ['Brand Created', 'brand.create', 'Brand Created', '{{brand_name}} has been created by {{actor_name}}.'],
        'brand.updated' => ['Brand Updated', 'brand.edit', 'Brand Updated', '{{brand_name}} has been updated by {{actor_name}}.'],
        'brand.status_updated' => ['Brand Status Updated', 'brand.update-status', 'Brand Status Updated', '{{brand_name}} is now {{brand_status}} (changed by {{actor_name}}).'],
        'brand.deleted' => ['Brand Deleted', 'brand.destroy', 'Brand Deleted', '{{brand_name}} has been moved to the trash by {{actor_name}}.'],
        'brand.restored' => ['Brand Restored', 'brand.restore', 'Brand Restored', '{{brand_name}} has been restored by {{actor_name}}.'],
        'brand.force_deleted' => ['Brand Permanently Deleted', 'brand.delete', 'Brand Permanently Deleted', '{{brand_name}} has been permanently deleted by {{actor_name}}.'],
    ];

    /**
     * Creates the six settings (if missing) and makes sure each has its channels, templates and receivers.
     */
    public function run(): void
    {
        $channels = NotificationChannel::whereIn('code', self::CHANNEL_CODES)->get();

        foreach (self::EVENTS as $eventCode => [$name, $permissionName, $title, $body]) {
            $setting = NotificationSetting::forEvent($eventCode)->first() ?? NotificationSetting::create([
                'name' => $name,
                'event_code' => $eventCode,
                'permission_name' => $permissionName,
                'description' => "Sent when the event {$eventCode} happens.",
                'is_active' => true,
            ]);

            foreach ($channels as $channel) {
                $setting->channels()->syncWithoutDetaching([$channel->id => ['is_active' => true]]);

                NotificationTemplate::firstOrCreate(
                    ['notification_setting_id' => $setting->id, 'channel_id' => $channel->id],
                    ['title' => $title, 'body' => $body, 'variables' => ['brand_name', 'brand_status'], 'is_active' => true]
                );
            }

            $setting->receiverRules()->firstOrCreate([
                'receiver_type' => NotificationSettingReceiver::TYPE_ROLE,
                'receiver_value' => config('boilerplate.access.role.admin', 'Super Admin'),
            ]);
        }
    }
}
