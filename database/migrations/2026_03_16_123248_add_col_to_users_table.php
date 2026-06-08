<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['customer_account', 'customer_info'])->nullable()->after('updated_at');
            $table->string('zalo')->nullable()->after('birthday');
            $table->string('city_code')->nullable()->after('zalo');
            $table->string('vehicle')->nullable()->after('city_code');
            $table->string('license_plate')->nullable()->after('vehicle');
            $table->string('facebook')->nullable()->after('license_plate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('zalo');
            $table->dropColumn('city_code');
            $table->dropColumn('vehicle');
            $table->dropColumn('license_plate');
            $table->dropColumn('facebook');
        });
    }
};
