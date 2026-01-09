<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('dob')->nullable();
            $table->boolean('is_subscribed')->default(false);
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_reservations')->default(0);
            $table->decimal('total_spent', 10, 2)->default(0);
            $table->timestamp('last_reservation_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};