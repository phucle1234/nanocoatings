<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 15, 8)->nullable()->change();
            $table->decimal('longitude', 15, 8)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('latitude', 12, 8)->nullable()->change();
            $table->decimal('longitude', 12, 8)->nullable()->change();
        });
    }
};
