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
        Schema::create('tbl_interior_amenities_home', function (Blueprint $table) {
            $table->unsignedInteger('home_id');
            $table->unsignedInteger('interior_amenity_id');
            $table->primary(['home_id', 'interior_amenity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_interior_amenities_home');
    }
};
