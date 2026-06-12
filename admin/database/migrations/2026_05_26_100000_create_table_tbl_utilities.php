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
        // 1. Create tbl_utilities
        Schema::create('tbl_utilities', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->string('name');
            $table->string('input_type')->default('number'); // number, text
            $table->tinyInteger('active')->default(1);
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        // 2. Create tbl_type_property_utilities
        Schema::create('tbl_type_property_utilities', function (Blueprint $table) {
            $table->unsignedInteger('type_property_id');
            $table->unsignedInteger('utility_id');
            $table->primary(['type_property_id', 'utility_id']);
        });

        // 3. Create tbl_home_utilities
        Schema::create('tbl_home_utilities', function (Blueprint $table) {
            $table->unsignedInteger('home_id');
            $table->unsignedInteger('utility_id');
            $table->string('value')->nullable();
            $table->primary(['home_id', 'utility_id']);
        });

        // 4. Seed default utilities
        $now = now();
        $floorsId = DB::table('tbl_utilities')->insertGetId([
            'name' => 'Số tầng',
            'input_type' => 'number',
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $entranceId = DB::table('tbl_utilities')->insertGetId([
            'name' => 'Đường vào (m)',
            'input_type' => 'number',
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $facadeId = DB::table('tbl_utilities')->insertGetId([
            'name' => 'Mặt tiền (m)',
            'input_type' => 'number',
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 5. Migrate type_property configuration
        $typeProperties = DB::table('tbl_type_property')->get();
        foreach ($typeProperties as $tp) {
            if (isset($tp->has_floors) && $tp->has_floors == 1) {
                DB::table('tbl_type_property_utilities')->insert([
                    'type_property_id' => $tp->id,
                    'utility_id' => $floorsId,
                ]);
            }
            if (isset($tp->has_entrance) && $tp->has_entrance == 1) {
                DB::table('tbl_type_property_utilities')->insert([
                    'type_property_id' => $tp->id,
                    'utility_id' => $entranceId,
                ]);
            }
            if (isset($tp->has_facade) && $tp->has_facade == 1) {
                DB::table('tbl_type_property_utilities')->insert([
                    'type_property_id' => $tp->id,
                    'utility_id' => $facadeId,
                ]);
            }
        }

        // 6. Migrate home utility values
        $homes = DB::table('tbl_home')->get();
        foreach ($homes as $home) {
            if (isset($home->floors) && $home->floors !== null && $home->floors !== '') {
                DB::table('tbl_home_utilities')->insert([
                    'home_id' => $home->id,
                    'utility_id' => $floorsId,
                    'value' => (string) $home->floors,
                ]);
            }
            if (isset($home->entrance) && $home->entrance !== null && $home->entrance !== '') {
                DB::table('tbl_home_utilities')->insert([
                    'home_id' => $home->id,
                    'utility_id' => $entranceId,
                    'value' => (string) $home->entrance,
                ]);
            }
            if (isset($home->facade) && $home->facade !== null && $home->facade !== '') {
                DB::table('tbl_home_utilities')->insert([
                    'home_id' => $home->id,
                    'utility_id' => $facadeId,
                    'value' => (string) $home->facade,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_home_utilities');
        Schema::dropIfExists('tbl_type_property_utilities');
        Schema::dropIfExists('tbl_utilities');
    }
};
