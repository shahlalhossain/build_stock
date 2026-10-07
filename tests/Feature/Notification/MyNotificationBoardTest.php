<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationPriority;
use App\Enums\NotificationReceiverStatus;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationReceiver;
use App\Models\User;
use Database\Seeders\NotificationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyNotificationBoardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected NotificationChannel $board;

    protected function setUp(): void
    {
        parent::setUp();

        $this->board = NotificationChannel::factory()->create(['code' => 'realtime', 'driver' => 'realtime']);
        $this->user = User::factory()->create();
    }

    /**
     * Adds a delivery to a user's board (default: a sent, unread one on the board channel).
     *
     * @param  array<string, mixed>  $overrides  Delivery columns to change.
     * @param  array<string, mixed>  $messageData  Columns of the notification itself.
     */
    protected function addItem(?User $user = null, array $overrides = [], array $messageData = []): NotificationReceiver
    {
        $message = NotificationMessage::factory()->create($messageData + ['title' => 'Hello', 'message_body' => 'Body text']);

        return NotificationReceiver::factory()->create($overrides + [
            'notification_message_id' => $message->id,
            'user_id' => ($user ?? $this->user)->id,
            'channel_id' => $this->board->id,
            'status' => NotificationReceiverStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    // ---------- what is on the board ----------

    public function test_the_board_lists_only_my_sent_notifications_on_the_board_channel(): void
    {
        $mine = $this->addItem(messageData: ['title' => 'Mine visible']);
        $this->addItem(User::factory()->create(), messageData: ['title' => 'Someone elses']);
        $this->addItem(overrides: ['status' => NotificationReceiverStatus::Queued], messageData: ['title' => 'Not sent yet']);
        $this->addItem(overrides: ['status' => NotificationReceiverStatus::Failed], messageData: ['title' => 'Failed one']);
        $this->addItem(overrides: ['channel_id' => NotificationChannel::factory()->create(['code' => 'push'])->id], messageData: ['title' => 'Other channel']);
        $this->addItem(overrides: ['user_deleted_at' => now()], messageData: ['title' => 'Deleted by me']);

        $response = $this->actingAs($this->user)->get(route('my-notification.index'))->assertOk();

        $response->assertSee('Mine visible')
            ->assertDontSee('Someone elses')
            ->assertDontSee('Not sent yet')
            ->assertDontSee('Failed one')
            ->assertDontSee('Other channel')
            ->assertDontSee('Deleted by me');
        $this->assertSame($mine->id, $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json('items.0.id'));
    }

    public function test_the_board_needs_login(): void
    {
        $this->get(route('my-notification.index'))->assertRedirect(route('login'));
        $this->getJson(route('my-notification.summary'))->assertUnauthorized();
    }

    public function test_unread_items_are_marked_new_and_the_unread_filter_works(): void
    {
        $this->addItem(overrides: ['read_at' => now()], messageData: ['title' => 'Old read one']);
        $this->addItem(messageData: ['title' => 'Fresh unread one']);

        $this->actingAs($this->user)->get(route('my-notification.index', ['filter' => 'unread']))
            ->assertSee('Fresh unread one')
            ->assertDontSee('Old read one');

        $this->actingAs($this->user)->get(route('my-notification.index'))
            ->assertSee('Fresh unread one')
            ->assertSee('Old read one');
    }

    public function test_the_text_comes_from_the_channels_template_and_links_are_made_safe(): void
    {
        $this->addItem(messageData: ['title' => 'Linked', 'data' => ['url' => '/brand/5']]);
        $this->addItem(messageData: ['title' => 'Evil', 'data' => ['url' => 'javascript:alert(1)']]);

        $items = $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json('items');

        $byTitle = collect($items)->keyBy('title');
        $this->assertSame('/brand/5', $byTitle['Linked']['url']);
        $this->assertNull($byTitle['Evil']['url']);
    }

    public function test_the_page_escapes_html_in_messages(): void
    {
        $this->addItem(messageData: ['title' => '<script>alert(1)</script>']);

        $this->actingAs($this->user)->get(route('my-notification.index'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    // ---------- read / delete ----------

    public function test_marking_as_read_saves_the_time_and_lowers_the_unread_count(): void
    {
        $first = $this->addItem();
        $this->addItem();

        $response = $this->actingAs($this->user)->postJson(route('my-notification.read', $first->id))
            ->assertOk()
            ->assertJson(['success' => true, 'unread_count' => 1]);

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertSame(1, $response->json('unread_count'));

        // Marking again keeps the first read time.
        $time = $first->fresh()->read_at;
        $this->travel(5)->minutes();
        $this->actingAs($this->user)->postJson(route('my-notification.read', $first->id))->assertOk();
        $this->assertEquals($time, $first->fresh()->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        $this->addItem();
        $this->addItem();
        $other = $this->addItem(User::factory()->create());

        $this->actingAs($this->user)->postJson(route('my-notification.read-all'))->assertOk()->assertJson(['unread_count' => 0]);

        $this->assertSame(0, NotificationReceiver::where('user_id', $this->user->id)->whereNull('read_at')->count());
        $this->assertNull($other->fresh()->read_at);
    }

    public function test_delete_hides_the_notification_but_keeps_the_record(): void
    {
        $item = $this->addItem();

        $this->actingAs($this->user)->deleteJson(route('my-notification.destroy', $item->id))
            ->assertOk()->assertJson(['unread_count' => 0]);

        $this->assertNotNull($item->fresh()->user_deleted_at);
        $this->assertDatabaseHas('notification_receivers', ['id' => $item->id]);
        $this->assertSame([], $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json('items'));
        $this->actingAs($this->user)->postJson(route('my-notification.read', $item->id))->assertNotFound();
    }

    public function test_a_user_cannot_touch_someone_elses_notification(): void
    {
        $theirs = $this->addItem(User::factory()->create());

        $this->actingAs($this->user)->postJson(route('my-notification.read', $theirs->id))->assertNotFound();
        $this->actingAs($this->user)->deleteJson(route('my-notification.destroy', $theirs->id))->assertNotFound();

        $this->assertNull($theirs->fresh()->read_at);
        $this->assertNull($theirs->fresh()->user_deleted_at);
    }

    public function test_a_notification_that_is_not_sent_yet_cannot_be_read_or_deleted(): void
    {
        $queued = $this->addItem(overrides: ['status' => NotificationReceiverStatus::Queued]);

        $this->actingAs($this->user)->postJson(route('my-notification.read', $queued->id))->assertNotFound();
    }

    public function test_admins_still_see_a_deleted_notification_on_the_details_page(): void
    {
        $this->seed(NotificationPermissionSeeder::class);
        $item = $this->addItem(messageData: ['priority' => NotificationPriority::High]);
        $this->actingAs($this->user)->deleteJson(route('my-notification.destroy', $item->id))->assertOk();

        $admin = User::factory()->create()->givePermissionTo('notification.show');

        $this->actingAs($admin)->get(route('notification.show', $item->notification_message_id))
            ->assertOk()
            ->assertSee($this->user->name);
    }

    // ---------- header bell ----------

    public function test_the_bell_summary_has_the_unread_count_and_at_most_ten_latest_items(): void
    {
        foreach (range(1, 12) as $ignored) {
            $this->addItem();
        }
        $this->addItem(overrides: ['read_at' => now()]);

        $summary = $this->actingAs($this->user)->getJson(route('my-notification.summary'))->json();

        $this->assertSame(12, $summary['unread_count']);
        $this->assertCount(10, $summary['items']);
    }

    public function test_pages_contain_the_bell_and_the_my_notifications_link(): void
    {
        $this->actingAs($this->user)->get(route('my-notification.index'))
            ->assertSee('id="notification-list"', false)
            ->assertSee(route('my-notification.index'), false)
            ->assertSee(route('my-notification.summary'), false);
    }
}
