<?php

namespace Tests\Feature\Notification;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PusherConnectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pretends Pusher is set up (fake keys: nothing is sent to the internet).
     */
    protected function usePusher(): void
    {
        config([
            'broadcasting.default' => 'pusher',
            'broadcasting.connections.pusher.key' => 'public-test-key',
            'broadcasting.connections.pusher.secret' => 'SUPERSECRET-VALUE',
            'broadcasting.connections.pusher.app_id' => '12345',
            'broadcasting.connections.pusher.options.cluster' => 'mt1',
        ]);

        // The app registered its channels while the default broadcaster was still "log",
        // so register them again on the Pusher broadcaster used in this test.
        require base_path('routes/channels.php');
    }

    // ---------- private channel authorization ----------

    public function test_a_user_can_listen_to_their_own_private_channel(): void
    {
        $this->usePusher();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$user->id,
            'socket_id' => '1234.5678',
        ])->assertOk();

        $this->assertStringStartsWith('public-test-key:', $response->json('auth'));
    }

    public function test_a_user_cannot_listen_to_someone_elses_channel(): void
    {
        $this->usePusher();
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->postJson('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$other->id,
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }

    public function test_a_guest_cannot_authorize_any_channel(): void
    {
        $this->usePusher();

        $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.1',
            'socket_id' => '1234.5678',
        ])->assertUnauthorized();
    }

    // ---------- browser script ----------

    public function test_the_pusher_scripts_are_not_loaded_while_broadcasting_is_off(): void
    {
        config(['broadcasting.default' => 'log']);

        $this->actingAs(User::factory()->create())->get(route('my-notification.index'))
            ->assertOk()
            ->assertDontSee('js.pusher.com', false)
            ->assertDontSee('laravel-echo', false);
    }

    public function test_the_pusher_scripts_are_loaded_with_the_public_key_only(): void
    {
        $this->usePusher();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('my-notification.index'))
            ->assertOk()
            ->assertSee('js.pusher.com/8.2.0/pusher.min.js', false)
            ->assertSee('laravel-echo@1.16.1', false)
            ->assertSee('public-test-key', false)
            ->assertSee('App.Models.User.'.$user->id, false)
            ->assertSee('.notification.received', false)
            ->assertDontSee('SUPERSECRET-VALUE', false);
    }

    public function test_nothing_is_loaded_when_pusher_is_chosen_but_no_key_is_set(): void
    {
        config(['broadcasting.default' => 'pusher', 'broadcasting.connections.pusher.key' => null]);

        $this->actingAs(User::factory()->create())->get(route('my-notification.index'))
            ->assertOk()
            ->assertDontSee('js.pusher.com', false);
    }

    public function test_the_realtime_script_is_also_on_normal_pages(): void
    {
        $this->usePusher();

        $this->actingAs(User::factory()->create())->get(route('profile'))
            ->assertOk()
            ->assertSee('js.pusher.com', false);
    }
}
