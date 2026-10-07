<?php

namespace Tests\Feature\Notification;

use App\Models\NotificationChannel;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\User;
use Database\Seeders\NotificationPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTemplateCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(NotificationPermissionSeeder::class);
    }

    protected function userWith(array $permissions = []): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $this->actingAs($user);

        return $user;
    }

    protected function makeTemplate(array $overrides = []): NotificationTemplate
    {
        return NotificationTemplate::create(array_merge([
            'notification_setting_id' => NotificationSetting::factory()->create()->id,
            'channel_id' => NotificationChannel::factory()->create()->id,
            'body' => 'Hello {{brand_name}}',
            'variables' => ['brand_name'],
            'is_active' => true,
        ], $overrides));
    }

    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'notification_setting_id' => NotificationSetting::factory()->create()->id,
            'channel_id' => NotificationChannel::factory()->create()->id,
            'subject' => 'New brand',
            'title' => 'Brand {{ brand_name }}',
            'body' => 'Brand {{brand_name}} was added by {{user_name}}',
            'variables' => "brand_name\nuser_name, brand_name",
        ], $overrides);
    }

    public function test_index_loads_for_permitted_user(): void
    {
        $this->userWith(['notification-template.index']);

        $this->get(route('notification-template.index'))->assertOk();
    }

    public function test_index_is_forbidden_without_permission(): void
    {
        $this->userWith();

        $this->get(route('notification-template.index'))->assertForbidden();
    }

    public function test_store_creates_template_with_parsed_variables(): void
    {
        $this->userWith(['notification-template.create']);
        $payload = $this->validPayload();

        $this->post(route('notification-template.store'), $payload)
            ->assertRedirect(route('notification-template.index'));

        $template = NotificationTemplate::firstOrFail();
        $this->assertSame(['brand_name', 'user_name'], $template->variables);
        $this->assertTrue($template->is_active);
        $this->assertSame($payload['channel_id'], $template->channel_id);
    }

    public function test_duplicate_setting_and_channel_is_rejected(): void
    {
        $this->userWith(['notification-template.create']);
        $existing = $this->makeTemplate();

        $this->post(route('notification-template.store'), $this->validPayload([
            'notification_setting_id' => $existing->notification_setting_id,
            'channel_id' => $existing->channel_id,
        ]))->assertSessionHasErrors('channel_id');

        $this->assertSame(1, NotificationTemplate::count());
    }

    public function test_unknown_placeholder_is_rejected(): void
    {
        $this->userWith(['notification-template.create']);

        $this->post(route('notification-template.store'), $this->validPayload([
            'body' => 'Hi {{ghost_name}}',
            'variables' => 'brand_name',
        ]))->assertSessionHasErrors('body');

        $this->assertSame(0, NotificationTemplate::count());
    }

    public function test_standard_placeholders_are_allowed_without_being_listed(): void
    {
        $this->userWith(['notification-template.create']);

        $this->post(route('notification-template.store'), $this->validPayload([
            'title' => 'Brand Created',
            'body' => '{{brand_name}} created by {{actor_name}} ({{actor_email}}) at {{created_at}}: {{url}}',
            'variables' => 'brand_name',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, NotificationTemplate::count());
    }

    public function test_update_cannot_change_setting_or_channel(): void
    {
        $this->userWith(['notification-template.edit']);
        $template = $this->makeTemplate();
        $originalSetting = $template->notification_setting_id;
        $originalChannel = $template->channel_id;

        $this->patch(route('notification-template.update', $template->id), [
            'notification_setting_id' => NotificationSetting::factory()->create()->id,
            'channel_id' => NotificationChannel::factory()->create()->id,
            'body' => 'Changed {{brand_name}}',
            'variables' => 'brand_name',
            'is_active' => 0,
        ])->assertRedirect(route('notification-template.index'));

        $template->refresh();
        $this->assertSame($originalSetting, $template->notification_setting_id);
        $this->assertSame($originalChannel, $template->channel_id);
        $this->assertSame('Changed {{brand_name}}', $template->body);
        $this->assertFalse($template->is_active);
    }

    public function test_toggle_flips_status(): void
    {
        $this->userWith(['notification-template.update-status']);
        $template = $this->makeTemplate();

        $this->postJson(route('notification-template.toggle-status', $template->id))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => false]);
        $this->assertFalse($template->fresh()->is_active);

        $this->postJson(route('notification-template.toggle-status', $template->id))
            ->assertJson(['is_active' => true]);
    }

    public function test_delete_removes_the_row(): void
    {
        $this->userWith(['notification-template.delete']);
        $template = $this->makeTemplate();

        $this->deleteJson(route('notification-template.destroy', $template->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('notification_templates', ['id' => $template->id]);
    }
}
