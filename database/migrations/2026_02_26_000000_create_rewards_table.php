<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 8, 2);
            $table->enum('type', ['earned', 'redeemed']);
            $table->foreignId('reservation_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'type']);
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('reward_discount', 8, 2)->default(0)->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('reward_discount');
        });

        Schema::dropIfExists('rewards');
    }
};
