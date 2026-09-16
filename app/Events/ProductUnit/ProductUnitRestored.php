<?php

namespace App\Events\ProductUnit;

use App\Models\ProductUnit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductUnitRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $productUnit;

    public function __construct(ProductUnit $productUnit)
    {
        $this->productUnit = $productUnit;
    }
}
