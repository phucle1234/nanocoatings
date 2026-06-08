<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use CrudTrait;

    protected $table = 'api_request_logs';

    protected $fillable = [
        'request_id',
        'source_system',
        'endpoint',
        'route_name',
        'method',
        'reference_type',
        'reference_code',
        'status',
        'http_status',
        'error_message',
        'request_headers',
        'request_payload',
        'response_headers',
        'response_payload',
        'duration_ms',
        'processed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
        'processed_at' => 'datetime',
    ];
}
