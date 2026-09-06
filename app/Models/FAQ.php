<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FAQ extends Model
{
    use SoftDeletes,
        LogsActivity;

    protected $table = 'faqs';

    protected $fillable = [
        'faq_category_id',
        'question',
        'answer',
        'language',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active'  => 'boolean',
        ];
    }

    protected static $recordEvents = ['created', 'updated', 'deleted'];

    public function getActivitylogOptions() : LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->useLogName('FAQ')
            ->setDescriptionForEvent(fn(string $eventName) => "Record has been {$eventName}");
    }

    public function creator() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter() : BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function faqCategory() : BelongsTo
    {
        return $this->belongsTo(FAQCategory::class, 'faq_category_id', 'id');
    }
}
