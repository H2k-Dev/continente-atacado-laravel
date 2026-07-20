<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpSyncLog extends Model
{
    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'erp_source',
        'status',
        'categories_created',
        'categories_updated',
        'products_created',
        'products_updated',
        'products_deactivated',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}