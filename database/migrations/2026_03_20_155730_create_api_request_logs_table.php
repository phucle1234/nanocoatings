<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('request_id', 100)->nullable();
            $table->string('source_system', 100)->nullable();

            $table->string('endpoint', 255)->nullable();
            $table->string('route_name', 255)->nullable();
            $table->string('method', 10)->nullable();

            $table->string('reference_type', 50)->nullable();
            $table->string('reference_code', 150)->nullable();

            $table->string('status', 20)->default('processing');
            $table->unsignedSmallInteger('http_status')->nullable();

            $table->text('error_message')->nullable();

            $table->json('request_headers')->nullable();
            $table->longText('request_payload')->nullable();

            $table->json('response_headers')->nullable();
            $table->longText('response_payload')->nullable();

            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index('request_id');
            $table->index(['status', 'created_at']);
            $table->index(['reference_type', 'reference_code']);
            $table->index(['source_system', 'created_at']);
            $table->index('processed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
