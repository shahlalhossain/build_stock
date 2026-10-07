<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Models\NotificationChannel;
use App\Models\NotificationLog;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\User;
use App\Services\Notification\NotificationReportService;
use Database\Seeders\NotificationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $viewer;

    protected NotificationChannel $push;

    protected NotificationChannel $sms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NotificationPermissionSeeder::class);

        $this->push = NotificationChannel::factory()->create(['name' => 'Push']);
        $this->sms = NotificationChannel::factory()->create(['name' => 'SMS']);
        $this->viewer = User::factory()->create()->givePermissionTo(['notification.index', 'notification.show', 'notification-log.index', 'notification-log.show']);
    }

    /**
     * Calls a DataTable list the way the browser does and returns the ids of the rows it gives back.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, int>
     */
    protected function listIds(string $route, array $filters = []): array
    {
        $response = $this->actingAs($this->viewer)->getJson(route($route, $filters + ['draw' => 1, 'start' => 0, 'length' => 50, 'order' => [['column' => 0, 'dir' => 'asc']], 'columns' => [['data' => 'id', 'orderable' => 'true']]]), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk();

        return collect($response->json('data'))->pluck('id')->sort()->values()->all();
    }

    protected function makeLog(string $event, NotificationReceiver $receiver, array $attributes = []): NotificationLog
    {
        $log = $receiver->addLog($event, $attributes['status'] ?? null, $attributes['message'] ?? null);

        if (isset($attributes['created_at'])) {
            $log->forceFill(['created_at' => $attributes['created_at']])->save();
        }

        return $log;
    }

    // ---------- access ----------

    public function test_the_list_and_log_screens_need_their_permissions(): void
    {
        $nobody = User::factory()->create();
        $log = NotificationLog::create(['notification_receiver_id' => NotificationReceiver::factory()->create()->id, 'event' => 'sent']);

        $this->actingAs($nobody)->get(route('notification.index'))->assertForbidden();
        $this->actingAs($nobody)->get(route('notification-log.index'))->assertForbidden();
        $this->actingAs($nobody)->get(route('notification-log.show', $log->id))->assertForbidden();

        $this->actingAs($this->viewer)->get(route('notification.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('notification-log.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('notification-log.show', $log->id))->assertOk()->assertSee('sent');
    }

    // ---------- dashboard numbers ----------

    public function test_the_summary_counts_the_last_30_days_and_calculates_the_success_rate(): void
    {
        $recent = NotificationMessage::factory()->create(['status' => NotificationStatus::Partial]);
        NotificationMessage::factory()->create(['status' => NotificationStatus::Completed]);
        $old = NotificationMessage::factory()->create(['status' => NotificationStatus::Failed]);
        $old->forceFill(['created_at' => now()->subDays(45)])->save();

        foreach ([NotificationReceiverStatus::Sent, NotificationReceiverStatus::Delivered, NotificationReceiverStatus::Sent, NotificationReceiverStatus::Failed, NotificationReceiverStatus::Queued, NotificationReceiverStatus::Cancelled] as $status) {
            NotificationReceiver::factory()->create(['notification_message_id' => $recent->id, 'channel_id' => $this->push->id, 'status' => $status]);
        }
        NotificationReceiver::factory()->create(['notification_message_id' => $old->id, 'status' => NotificationReceiverStatus::Failed])
            ->forceFill(['created_at' => now()->subDays(45)])->save();

        $summary = app(NotificationReportService::class)->summary(30);

        $this->assertSame(2, $summary['notifications']['total']);
        $this->assertSame(1, $summary['notifications']['partial']);
        $this->assertSame(0, $summary['notifications']['failed']);
        $this->assertSame(6, $summary['deliveries']['total']);
        $this->assertSame(3, $summary['deliveries']['succeeded']);
        $this->assertSame(1, $summary['deliveries']['failed']);
        $this->assertSame(1, $summary['deliveries']['waiting']);
        $this->assertSame(1, $summary['deliveries']['cancelled']);
        $this->assertSame(75.0, $summary['deliveries']['success_rate']); // 3 / (3 + 1)
    }

    public function test_the_success_rate_is_empty_when_there_is_no_data(): void
    {
        $summary = app(NotificationReportService::class)->summary(30);

        $this->assertSame(0, $summary['deliveries']['total']);
        $this->assertNull($summary['deliveries']['success_rate']);

        $this->actingAs($this->viewer)->get(route('notification.index'))->assertSee('No data');
    }

    // ---------- notification list filters ----------

    public function test_the_notification_list_can_be_filtered(): void
    {
        $auto = NotificationMessage::factory()->create(['event_code' => 'brand.created', 'status' => NotificationStatus::Completed]);
        $manual = NotificationMessage::factory()->create(['type' => NotificationType::Manual, 'status' => NotificationStatus::Failed]);
        $old = NotificationMessage::factory()->create(['event_code' => 'brand.updated']);
        $old->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->assertSame([$auto->id, $manual->id, $old->id], $this->listIds('notification.index'));
        $this->assertSame([$manual->id], $this->listIds('notification.index', ['type' => 'manual']));
        $this->assertSame([$auto->id], $this->listIds('notification.index', ['status' => 'completed']));
        $this->assertSame([$auto->id], $this->listIds('notification.index', ['event' => 'created']));
        $this->assertSame([$auto->id, $manual->id], $this->listIds('notification.index', ['date_from' => now()->subDay()->toDateString()]));
        $this->assertSame([$old->id], $this->listIds('notification.index', ['date_to' => now()->subDays(5)->toDateString()]));
    }

    // ---------- log filters ----------

    public function test_the_log_list_can_be_filtered_by_every_filter(): void
    {
        $anna = User::factory()->create(['name' => 'Anna Reporter', 'email' => 'anna@example.com']);
        $bob = User::factory()->create(['name' => 'Bob Other', 'email' => 'bob@example.com']);
        $first = NotificationMessage::factory()->create();
        $second = NotificationMessage::factory()->create();

        $annaPush = NotificationReceiver::factory()->create(['notification_message_id' => $first->id, 'user_id' => $anna->id, 'channel_id' => $this->push->id]);
        $bobSms = NotificationReceiver::factory()->create(['notification_message_id' => $second->id, 'user_id' => $bob->id, 'channel_id' => $this->sms->id]);

        $queued = $this->makeLog('queued', $annaPush, ['status' => 'queued']);
        $failed = $this->makeLog('failed', $bobSms, ['status' => 'failed', 'message' => 'Provider said no']);
        $oldLog = $this->makeLog('sent', $annaPush, ['status' => 'sent', 'created_at' => now()->subDays(10)]);

        $this->assertSame([$queued->id, $failed->id, $oldLog->id], $this->listIds('notification-log.index'));
        $this->assertSame([$failed->id], $this->listIds('notification-log.index', ['event' => 'failed']));
        $this->assertSame([$queued->id, $oldLog->id], $this->listIds('notification-log.index', ['channel_id' => $this->push->id]));
        $this->assertSame([$failed->id], $this->listIds('notification-log.index', ['status' => 'failed']));
        $this->assertSame([$failed->id], $this->listIds('notification-log.index', ['notification_id' => $second->id]));
        $this->assertSame([$queued->id, $oldLog->id], $this->listIds('notification-log.index', ['user' => 'anna@']));
        $this->assertSame([$queued->id, $failed->id], $this->listIds('notification-log.index', ['date_from' => now()->subDay()->toDateString()]));
        $this->assertSame([$oldLog->id], $this->listIds('notification-log.index', ['date_to' => now()->subDays(5)->toDateString()]));
        $this->assertSame([$queued->id], $this->listIds('notification-log.index', ['user' => 'Anna', 'event' => 'queued', 'channel_id' => $this->push->id]));
    }

    public function test_the_log_details_page_shows_request_and_response_data(): void
    {
        $receiver = NotificationReceiver::factory()->create();
        $log = $receiver->addLog('provider_response', 'sent', null, ['to' => '***abc123'], ['provider' => 'log']);

        $this->actingAs($this->viewer)->get(route('notification-log.show', $log->id))
            ->assertOk()
            ->assertSee('***abc123')
            ->assertSee('provider');
    }
}
