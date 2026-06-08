<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;

class ApiOutboundLog extends Model
{
    use MassPrunable;
    use CrudTrait;
    protected $fillable = [
        'request_id',
        'target_system',
        'action',
        'method',
        'endpoint_url',
        'reference_type',
        'reference_code',
        'status',
        'http_status',
        'error_no',
        'error_message',
        'request_headers',
        'request_payload',
        'response_headers',
        'response_payload',
        'duration_ms',
        'attempt_no',
        'requested_at',
        'responded_at',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
        'requested_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDays(30));
    }
}
