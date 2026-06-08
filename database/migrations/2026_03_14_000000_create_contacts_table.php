<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string(column: 'order_number')->nullable();
            $table->string('Title', 100)->nullable();
            $table->string('Fullname', 255);
            $table->string('Phone', 20);
            $table->string('Email', 255)->nullable();
            $table->text('Content')->nullable();
            $table->string('Invoice', 100)->nullable();
            $table->string('QRcode', 255)->nullable();
            $table->string('Status', 100)->nullable();
            $table->tinyInteger('Type')->default(0)->comment('0: bảo hành, 1: liên hệ, 2: đăng ký thông tin');
            $table->datetime('Date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
