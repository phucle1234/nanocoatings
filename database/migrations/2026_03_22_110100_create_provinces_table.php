<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('npp_provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('npp_countries')->cascadeOnDelete();
            $table->string('name_vi');
            $table->string('name_en');
            $table->string('code', 10);
            $table->string('type', 50)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['country_id', 'code']);
            $table->index(['country_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npp_provinces');
    }
};
