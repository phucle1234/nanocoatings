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
        Schema::create('product_vehicle_fitments', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to products
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');
            
            // Vehicle information (nullable for universal fit)
            $table->string('manufacturer', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('year', 10)->nullable();
            
            // Additional fitment details
            $table->string('trim', 100)->nullable(); // Variant: LX, EX, Sport, etc.
            $table->string('engine', 50)->nullable(); // Engine size: 2.0L, 2.4L, etc.
            
            // Metadata
            $table->boolean('is_verified')->default(false); // Verified by admin
            $table->text('notes')->nullable(); // Special notes
            
            $table->timestamps();
            
            // ✅ UNIQUE constraint - Each combination only once per product
            $table->unique(
                ['product_id', 'manufacturer', 'model', 'year', 'trim', 'engine'],
                'unique_vehicle_fitment'
            );
            
            // ✅ INDEXES for performance
            $table->index(['manufacturer', 'model', 'year'], 'idx_vehicle_search');
            $table->index('product_id', 'idx_product');
            $table->index(['manufacturer', 'is_verified'], 'idx_manufacturer_verified');
            $table->index('is_verified', 'idx_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_vehicle_fitments');
    }
};
