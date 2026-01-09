<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('tax_rate', 5, 4);
            $table->decimal('tax_amount', 8, 2);
            $table->decimal('total_price', 8, 2);
            $table->string('product_name');
            $table->string('product_sku');
            $table->timestamps();
            $table->unique(['reservation_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};