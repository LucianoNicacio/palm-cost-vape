<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');
            $table->string('state', 2)->nullable()->after('city');
            $table->string('zip_code', 10)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'state', 'zip_code']);
        });
    }
};
