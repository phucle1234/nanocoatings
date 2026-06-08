<?php

namespace App\Http\Controllers\Admin;

use App\Models\ApiOutboundLog;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * CRUD xem log gọi API ra ngoài (api_outbound_logs).
 * Read-only: chỉ List + Show.
 */
class ApiOutboundLogCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        CRUD::setModel(ApiOutboundLog::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/api-outbound-log');
        CRUD::setEntityNameStrings('API outbound log', 'API outbound logs');

        $this->crud->denyAccess(['create', 'update']);
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id')->label('ID')->type('number');
        CRUD::column('created_at')->label('Thời điểm')->type('datetime');
        CRUD::column('status')->label('Trạng thái')->type('text');
        CRUD::column('http_status')->label('HTTP')->type('number');
        CRUD::column('target_system')->label('Target')->type('text');
        CRUD::column('action')->label('Action')->type('text');
        CRUD::column('method')->label('Method')->type('text');
        CRUD::column('endpoint_url')->label('Endpoint')->type('text')->limit(80);
        CRUD::column('reference_type')->label('Ref type')->type('text');
        CRUD::column('reference_code')->label('Ref code')->type('text')->limit(40);
        CRUD::column('attempt_no')->label('Attempt')->type('number');
        CRUD::column('duration_ms')->label('Duration (ms)')->type('number');
        CRUD::column('error_no')->label('Error no')->type('text');
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
        CRUD::column('target_system')->label('Target system');
        CRUD::column('action')->label('Action');
        CRUD::column('method')->label('Method');
        CRUD::column('endpoint_url')->label('Endpoint URL')->type('textarea');
        CRUD::column('reference_type')->label('Reference type');
        CRUD::column('reference_code')->label('Reference code');
        CRUD::column('attempt_no')->label('Attempt no');
        CRUD::column('duration_ms')->label('Duration (ms)');
        CRUD::column('requested_at')->label('Requested at')->type('datetime');
        CRUD::column('responded_at')->label('Responded at')->type('datetime');
        CRUD::column('error_no')->label('Error no');
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
