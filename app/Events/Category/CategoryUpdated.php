<?php

namespace App\Events\Category;

use App\Models\Category;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}
