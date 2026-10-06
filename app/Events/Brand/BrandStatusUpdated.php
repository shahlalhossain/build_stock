<?php

namespace App\Events\Brand;

use App\Models\Brand;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BrandStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
}
