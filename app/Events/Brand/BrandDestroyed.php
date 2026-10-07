<?php

namespace App\Events\Brand;

use App\Events\Brand\Concerns\HasBrandNotificationData;
use App\Events\Contracts\NotifiableEvent;
use App\Models\Brand;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrandDestroyed implements NotifiableEvent
{
    use Dispatchable, HasBrandNotificationData, InteractsWithSockets, SerializesModels;

    public $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * The code the notification system uses to find the settings for this event.
     */
    public function notificationEventCode(): string
    {
        return 'brand.deleted';
    }
}
