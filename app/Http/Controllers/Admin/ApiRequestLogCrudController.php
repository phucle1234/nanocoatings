<?php

namespace App\Http\Controllers\Admin;

use App\Models\ApiRequestLog;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * CRUD xem log request vào hệ thống (api_request_logs).
 * Read-only: chỉ List + Show.
 */
class ApiRequestLogCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        CRUD::setModel(ApiRequestLog::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/api-request-log');
        CRUD::setEntityNameStrings('API request log', 'API request logs');

        $this->crud->denyAccess(['create', 'update']);
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id')->label('ID')->type('number');
        CRUD::column('created_at')->label('Thời điểm')->type('datetime');
        CRUD::column('status')->label('Trạng thái')->type('text');
        CRUD::column('http_status')->label('HTTP')->type('number');
        CRUD::column('method')->label('Method')->type('text');
        CRUD::column('endpoint')->label('Endpoint')->type('text')->limit(80);
        CRUD::column('route_name')->label('Route')->type('text')->limit(60);
        CRUD::column('source_system')->label('Source')->type('text');
        CRUD::column('reference_type')->label('Ref type')->type('text');
        CRUD::column('reference_code')->label('Ref code')->type('text')->limit(40);
        CRUD::column('duration_ms')->label('Duration (ms)')->type('number');
        CRUD::column('ip_address')->label('IP')->type('text');

        CRUD::column('error_message')->label('Lỗi')->type('text')->limit(120);

        CRUD::orderBy('id', 'desc');
        CRUD::setOperationSetting('responsiveTable', false);
    }

    protected function setupShowOperation(): void
    {
        CRUD::column('id')->label('ID');
        CRUD::column('request_id')->label('Request ID');
        CRUD::column('status')->label('Trạng thái');
        CRUD::column('http_status')->label('HTTP status');
        CRUD::column('source_system')->label('Source system');
        CRUD::column('method')->label('Method');
        CRUD::column('endpoint')->label('Endpoint');
        CRUD::column('route_name')->label('Route name');
        CRUD::column('reference_type')->label('Reference type');
        CRUD::column('reference_code')->label('Reference code');
        CRUD::column('duration_ms')->label('Duration (ms)');
        CRUD::column('processed_at')->label('Processed at')->type('datetime');
        CRUD::column('ip_address')->label('IP address');
        CRUD::column('user_agent')->label('User agent')->type('textarea');

        CRUD::column('error_message')->label('Error message')->type('textarea');

        CRUD::column('request_headers')->label('Request headers')->type('closure')->function(function ($entry) {
            $data = $entry->request_headers;
            return '<pre class="mb-0" style="white-space:pre-wrap">' . e(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';
        })->escaped(false);

        CRUD::column('request_payload')->label('Request payload')->type('textarea');

        CRUD::column('response_headers')->label('Response headers')->type('closure')->function(function ($entry) {
            $data = $entry->response_headers;
            return '<pre class="mb-0" style="white-space:pre-wrap">' . e(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</pre>';
        })->escaped(false);

        CRUD::column('response_payload')->label('Response payload')->type('textarea');

        CRUD::column('created_at')->label('Created at')->type('datetime');
        CRUD::column('updated_at')->label('Updated at')->type('datetime');
    }
}
