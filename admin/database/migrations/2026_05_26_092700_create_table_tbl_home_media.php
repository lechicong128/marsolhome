<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_home_media', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('home_id');
            $table->string('url');
            $table->string('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->index('home_id');
        });

        // Migrate existing data from tbl_home.media JSON columns
        $homes = DB::table('tbl_home')
            ->whereNotNull('media')
            ->where('media', '!=', '')
            ->where('media', '!=', '[]')
            ->get();

        foreach ($homes as $home) {
            $mediaUrls = json_decode($home->media, true);
            $mediaCaptions = json_decode($home->media_captions, true) ?? [];

            if (is_array($mediaUrls)) {
                foreach ($mediaUrls as $idx => $url) {
                    DB::table('tbl_home_media')->insert([
                        'home_id' => $home->id,
                        'url' => $url,
                        'caption' => $mediaCaptions[$idx] ?? '',
                        'sort_order' => $idx,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_home_media');
    }
};
