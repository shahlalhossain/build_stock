<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    protected $table = 'approval_logs';

    protected $fillable = [
        'model_type',
        'model_id',
        'action_name',
        'actioned_by',
        'actioned_at',
        'remarks',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    public function actionedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }
}
