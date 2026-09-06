<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $table = 'notices';

    protected $fillable = [
        'notice_category_id',

        'uuid',
        'notice_no',

        'title',
        'title_bn',

        'slug',

        'summary',
        'summary_bn',

        'content',
        'content_bn',

        'published_by',
        'published_at',

        'effective_date',
        'expiry_date',

        'priority',

        'is_featured',
        'is_pinned',
        'is_active',

        'status',

        'reviewed_by',
        'reviewed_at',
        'revision_notes',

        'approved_by',
        'approved_at',
        'approve_notes',

        'rejected_by',
        'rejected_at',
        'rejection_notes',
    ];

    protected $casts = [
        'published_at'      => 'datetime',
        'effective_date'    => 'date',
        'expiry_date'       => 'date',

        'reviewed_at'       => 'datetime',
        'approved_at'       => 'datetime',
        'rejected_at'       => 'datetime',

        'is_featured'       => 'boolean',
        'is_pinned'         => 'boolean',
        'is_active'         => 'boolean',

        'priority'          => 'integer',
        'view_count'        => 'integer',
    ];

    protected $hidden = [];

    protected $guarded = [];
}
