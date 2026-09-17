<?php

namespace App\Events\Store;

use App\Models\Store;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StoreDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $store;

    public function __construct(Store $store)
    {
        $this->store = $store;
    }
}
