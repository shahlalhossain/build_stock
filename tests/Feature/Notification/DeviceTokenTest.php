<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use App\Models\UserDeviceToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_cannot_register_a_device(): void
    {
        $this->postJson(route('device-token.store'), ['token' => 'abc'])->assertUnauthorized();
    }

    public function test_a_user_can_register_a_device(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('device-token.store'), ['token' => 'tok-1', 'platform' => 'android', 'device_name' => 'Pixel'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('user_device_tokens', [
            'user_id' => $user->id, 'token' => 'tok-1', 'platform' => 'android', 'is_active' => true,
        ]);
    }

    public function test_registering_the_same_token_again_does_not_duplicate_and_moves_it_to_the_new_user(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($first)->postJson(route('device-token.store'), ['token' => 'shared'])->assertOk();
        UserDeviceToken::query()->update(['is_active' => false]);
        $this->actingAs($second)->postJson(route('device-token.store'), ['token' => 'shared'])->assertOk();

        $this->assertSame(1, UserDeviceToken::count());
        $this->assertSame($second->id, UserDeviceToken::first()->user_id);
        $this->assertTrue(UserDeviceToken::first()->is_active);
    }

    public function test_bad_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('device-token.store'), ['platform' => 'toaster'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'platform']);
    }

    public function test_a_user_can_only_remove_their_own_token(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $owner->deviceTokens()->create(['token' => 'mine']);

        $this->actingAs($other)->deleteJson(route('device-token.destroy'), ['token' => 'mine'])->assertOk();
        $this->assertSame(1, UserDeviceToken::count());

        $this->actingAs($owner)->deleteJson(route('device-token.destroy'), ['token' => 'mine'])->assertOk();
        $this->assertSame(0, UserDeviceToken::count());
    }
}
