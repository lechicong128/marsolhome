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
        Schema::create('tbl_home', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('code')->nullable();
            $table->integer('type')->nullable();
            $table->integer('property_type')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('ward_id')->nullable();
            $table->string('address')->nullable();
            $table->double('price')->nullable();
            $table->double('area')->nullable();
            $table->integer('direction_id')->nullable();
            $table->integer('beds')->nullable();
            $table->integer('baths')->nullable();
            $table->integer('legal_id')->nullable();
            $table->tinyint('status')->default(0);
            $table->integer('interior_id')->nullable();
            $table->text('interior_note')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_role')->nullable();
            $table->string('contact_time')->nullable();
            $table->string('video_url')->nullable();
            $table->text('media')->nullable();
            $table->text('media_captions')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->string('name_location')->nullable();
            $table->integer('customer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_home');
    }
};
