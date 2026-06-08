<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_outbound_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('request_id', 100)->nullable()->index();
            $table->string('target_system', 100)->nullable()->index(); // maincsm, erp, client_a...
            $table->string('action', 100)->nullable()->index(); // customerg, productg...
            $table->string('method', 10)->default('POST')->index();

            $table->text('endpoint_url');
            $table->string('reference_type', 50)->nullable()->index(); // customer, order, product
            $table->string('reference_code', 150)->nullable()->index(); // customer_no, order_no...

            $table->string('status', 20)->default('processing')->index(); // processing, success, failed
            $table->unsignedSmallInteger('http_status')->nullable()->index();
            $table->string('error_no', 100)->nullable()->index();
            $table->text('error_message')->nullable();

            $table->json('request_headers')->nullable();
            $table->longText('request_payload')->nullable();

            $table->json('response_headers')->nullable();
            $table->longText('response_payload')->nullable();

            $table->unsignedInteger('duration_ms')->nullable()->index();
            $table->unsignedSmallInteger('attempt_no')->default(1);

            $table->timestamp('requested_at')->nullable()->index();
            $table->timestamp('responded_at')->nullable()->index();

            $table->timestamps();

            $table->index(['target_system', 'action']);
            $table->index(['reference_type', 'reference_code']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_outbound_logs');
    }
};
