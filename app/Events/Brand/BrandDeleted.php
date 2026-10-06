<?php

namespace App\Events\Brand;

use App\Models\Brand;
use Illuminate\Queue\SerializesModels;

/**
 * Class BrandDeleted.
 */
class BrandDeleted
{
    use SerializesModels;

    public $brand;

    public function __construct(Brand $brand)
    {
        $this->brand = $brand;
    }
}
