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
        Schema::table('tbl_utilities', function (Blueprint $table) {
            $table->tinyInteger('transaction_type')->default(3)->comment('1: Only Sell, 2: Only Rent, 3: Both');
            $table->text('choices')->nullable()->comment('Comma separated choices for select inputs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_utilities', function (Blueprint $table) {
            $table->dropColumn(['transaction_type', 'choices']);
        });
    }
};
