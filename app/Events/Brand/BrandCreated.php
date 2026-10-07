<?php

namespace App\Events\Brand;

use App\Events\Brand\Concerns\HasBrandNotificationData;
use App\Events\Contracts\NotifiableEvent;
use App\Models\Brand;
use Illuminate\Queue\SerializesModels;

/**
 * Class BrandCreated.
 */
class BrandCreated implements NotifiableEvent
{
    use HasBrandNotificationData, SerializesModels;

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
        return 'brand.created';
    }
}
