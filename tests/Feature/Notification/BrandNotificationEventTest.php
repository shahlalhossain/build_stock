<?php

namespace Tests\Feature\Notification;

use App\Enums\NotificationReceiverStatus;
use App\Enums\NotificationStatus;
use App\Events\Brand\BrandCreated;
use App\Models\Brand;
use App\Models\NotificationChannel;
use App\Models\NotificationMessage;
use App\Models\NotificationSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\BrandService;
use App\Services\Notification\NotificationService;
use Database\Seeders\BrandNotificationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class BrandNotificationEventTest extends TestCase
{
    use RefreshDatabase;

    protected BrandService $brandService;

    protected User $admin;

    protected User $actor;

    protected function setUp(): void
    {
        parent::setUp();

        NotificationChannel::factory()->create(['code' => 'push', 'driver' => 'push']);
        $this->seed(BrandNotificationSeeder::class);

        $role = Role::create(['type' => 'admin', 'guard_name' => 'web', 'name' => 'Super Admin']);
        $this->admin = User::factory()->create()->assignRole($role);

        $this->actor = User::factory()->create(['name' => 'John']);
        $this->actingAs($this->actor);

        $this->brandService = app(BrandService::class);
    }

    protected function makeBrand(string $name = 'ABC Cement'): Brand
    {
        return $this->brandService->storeBrand(['name' => $name, 'slug' => str($name)->slug()->toString(), 'priority_order' => 0]);
    }

    public function test_creating_a_brand_creates_the_notification_and_receivers(): void
    {
        $brand = $this->makeBrand();

        $message = NotificationMessage::where('event_code', 'brand.created')->sole();

        $this->assertSame('ABC Cement has been created by John.', $message->message_body);
        $this->assertSame($brand->id, $message->data['model_id']);
        $this->assertSame(route('brand.show', $brand->id), $message->data['url']);
        $this->assertSame($this->actor->id, $message->created_by);
        $this->assertSame([$this->admin->id], $message->receivers->pluck('user_id')->all());
    }

    public function test_every_brand_action_creates_its_own_notification(): void
    {
        $brand = $this->makeBrand();
        $this->brandService->updateBrand($brand, ['name' => 'ABC Cement', 'slug' => 'abc-cement', 'priority_order' => 0]);
        $this->brandService->updateBrandStatus($brand->id, 'approved');
        $this->brandService->destroyBrand($brand->id);
        $this->brandService->restoreBrand($brand->id);
        $this->brandService->deleteBrand($brand->id);

        $codes = NotificationMessage::orderBy('id')->pluck('event_code')->all();

        $this->assertSame([
            'brand.created',
            'brand.updated',
            'brand.status_updated',
            'brand.deleted',
            'brand.restored',
            'brand.force_deleted',
        ], $codes);
        $this->assertStringContainsString('approved', NotificationMessage::where('event_code', 'brand.status_updated')->value('message_body'));
    }

    public function test_a_switched_off_setting_creates_no_notification(): void
    {
        NotificationSetting::forEvent('brand.created')->update(['is_active' => false]);

        $this->makeBrand();

        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_the_brand_is_saved_even_if_creating_the_notification_fails(): void
    {
        $this->mock(NotificationService::class)
            ->shouldReceive('createAutomaticNotification')
            ->andThrow(new RuntimeException('Notification system is broken'));

        $brand = $this->makeBrand();

        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'ABC Cement']);
        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_no_notification_is_made_when_the_business_action_is_rolled_back(): void
    {
        $brand = $this->makeBrand('Rolled Back');
        NotificationMessage::query()->delete();

        DB::beginTransaction();
        event(new BrandCreated($brand));
        DB::rollBack();

        $this->assertSame(0, NotificationMessage::count());
    }

    public function test_the_brand_seeder_can_run_twice_without_duplicates(): void
    {
        $this->seed(BrandNotificationSeeder::class);

        $this->assertSame(6, NotificationSetting::where('event_code', 'like', 'brand.%')->count());
    }

    public function test_a_new_brand_is_delivered_by_push_to_the_admins_device_through_the_queue(): void
    {
        // The test queue runs jobs immediately, which stands in for a real worker.
        $this->admin->deviceTokens()->create(['token' => 'admin-device-token-123456']);

        $this->makeBrand();

        $receiver = NotificationMessage::where('event_code', 'brand.created')->sole()->receivers->sole();
        $this->assertSame(NotificationReceiverStatus::Sent, $receiver->status);
        $this->assertSame(NotificationStatus::Completed, $receiver->message->status);
        $this->assertStringStartsWith('log-', $receiver->provider_message_id);
        $this->assertSame(['queued', 'processing', 'provider_response', 'sent'], $receiver->logs()->orderBy('id')->pluck('event')->all());
    }

    public function test_an_admin_without_a_device_gets_a_failed_delivery_but_the_brand_is_still_saved(): void
    {
        $brand = $this->makeBrand();

        $receiver = NotificationMessage::where('event_code', 'brand.created')->sole()->receivers->sole();
        $this->assertSame(NotificationReceiverStatus::Failed, $receiver->status);
        $this->assertDatabaseHas('brands', ['id' => $brand->id]);
    }
}
