<?php

namespace App\Events\Brand\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Shared notification details for all Brand events.
 * The event class only has to say its own event code.
 */
trait HasBrandNotificationData
{
    /**
     * Gives the template values for this brand (name, status, link...).
     *
     * @return array<string, mixed>
     */
    public function notificationData(): array
    {
        return [
            'model' => 'Brand',
            'model_id' => $this->brand->id,
            'action' => substr($this->notificationEventCode(), strlen('brand.')),
            'brand_name' => $this->brand->name,
            'brand_status' => $this->brand->status,
            'url' => route('brand.show', $this->brand->id),
        ];
    }

    /**
     * The logged-in user who did the action.
     */
    public function notificationActor(): ?User
    {
        return Auth::user();
    }
}
