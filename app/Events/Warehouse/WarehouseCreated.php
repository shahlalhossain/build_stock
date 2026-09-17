<?php

namespace App\Events\Warehouse;

use App\Models\Warehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }
}
