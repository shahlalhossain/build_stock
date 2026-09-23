<?php

namespace App\Events\StockTransaction;

use App\Models\StockTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockTransactionStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $stockTransaction;

    public function __construct(StockTransaction $stockTransaction)
    {
        $this->stockTransaction = $stockTransaction;
    }
}
