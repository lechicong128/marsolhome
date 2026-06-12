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
        Schema::create('tbl_utility_options', function (Blueprint $table) {
            $table->unsignedInteger('id', true);
            $table->unsignedInteger('utility_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('utility_id')->references('id')->on('tbl_utilities')->onDelete('cascade');
        });

        // Migrate existing choices to tbl_utility_options before dropping the column
        if (Schema::hasColumn('tbl_utilities', 'choices')) {
            $utilities = DB::table('tbl_utilities')->where('input_type', 'select')->get();
            foreach ($utilities as $utility) {
                if (!empty($utility->choices)) {
                    $choices = explode(',', $utility->choices);
                    foreach ($choices as $choice) {
                        $trimmed = trim($choice);
                        if ($trimmed !== '') {
                            DB::table('tbl_utility_options')->insert([
                                'utility_id' => $utility->id,
                                'name' => $trimmed,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }

            // Drop choices column from tbl_utilities
            Schema::table('tbl_utilities', function (Blueprint $table) {
                $table->dropColumn('choices');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('tbl_utilities', 'choices')) {
            Schema::table('tbl_utilities', function (Blueprint $table) {
                $table->text('choices')->nullable()->comment('Comma separated choices for select inputs');
            });
        }

        // Migrate options back to comma-separated choices before dropping table
        $options = DB::table('tbl_utility_options')->get()->groupBy('utility_id');
        foreach ($options as $utilityId => $opts) {
            $choicesStr = $opts->pluck('name')->implode(',');
            DB::table('tbl_utilities')->where('id', $utilityId)->update(['choices' => $choicesStr]);
        }

        Schema::dropIfExists('tbl_utility_options');
    }
};
