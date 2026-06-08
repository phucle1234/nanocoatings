<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('npp_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name_vi');
            $table->string('name_en');
            $table->string('code', 10)->unique();
            $table->string('phone_code', 10)->nullable();
            $table->string('region', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npp_countries');
    }
};
