<?php

namespace Tests\Feature\Notification;

use App\Exceptions\GeneralException;
use App\Models\NotificationChannel;
use App\Models\NotificationReceiver;
use App\Services\Notification\ChannelManager;
use App\Services\Notification\Channels\NotificationChannelInterface;
use App\Services\Notification\NotificationResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use stdClass;
use Tests\TestCase;

class ChannelManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function fakeChannel(): NotificationChannelInterface
    {
        return new class implements NotificationChannelInterface
        {
            public function send(NotificationReceiver $receiver): NotificationResult
            {
                return NotificationResult::success('msg-1');
            }
        };
    }

    public function test_a_registered_driver_is_resolved_and_used_to_send(): void
    {
        $manager = new ChannelManager;
        $manager->extend('fake', fn () => $this->fakeChannel());

        $channel = NotificationChannel::factory()->create(['driver' => 'fake']);
        $receiver = NotificationReceiver::factory()->create(['channel_id' => $channel->id]);

        $result = $manager->send($receiver);

        $this->assertTrue($result->successful);
        $this->assertSame('msg-1', $result->providerMessageId);
    }

    public function test_drivers_can_come_from_the_config_file(): void
    {
        config(['notification.drivers.fake' => get_class($this->fakeChannel())]);

        $this->assertInstanceOf(NotificationChannelInterface::class, (new ChannelManager)->driver('fake'));
    }

    public function test_an_unknown_driver_gives_a_clear_error(): void
    {
        $this->expectException(GeneralException::class);
        (new ChannelManager)->driver('nope');
    }

    public function test_a_class_that_is_not_a_channel_is_rejected(): void
    {
        $manager = new ChannelManager;
        $manager->extend('bad', fn () => new stdClass);

        $this->expectException(GeneralException::class);
        $manager->driver('bad');
    }

    public function test_failure_results_carry_the_error_and_retry_flag(): void
    {
        $result = NotificationResult::failure('Bad token', retryable: false);

        $this->assertFalse($result->successful);
        $this->assertFalse($result->retryable);
        $this->assertSame('Bad token', $result->errorMessage);
    }
}
