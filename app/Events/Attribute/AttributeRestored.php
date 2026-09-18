<?php

namespace App\Events\Attribute;

use App\Models\Attribute;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttributeRestored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attribute;

    public function __construct(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }
}
