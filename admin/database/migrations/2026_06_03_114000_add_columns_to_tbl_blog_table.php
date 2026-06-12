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
        Schema::table('tbl_blog', function (Blueprint $table) {
            // Change existing columns to allow longer text
            $table->text('descption')->nullable()->change();
            $table->longText('content')->nullable()->change();

            // Operational columns
            if (!Schema::hasColumn('tbl_blog', 'type_blog')) {
                $table->tinyInteger('type_blog')->default(1)->comment('1: Loại điểm đến, 2: Loại thành viên')->after('type');
            }
            if (!Schema::hasColumn('tbl_blog', 'homepage')) {
                $table->tinyInteger('homepage')->default(0)->after('type_blog');
            }
            if (!Schema::hasColumn('tbl_blog', 'hot')) {
                $table->tinyInteger('hot')->default(0)->after('homepage');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_blog', function (Blueprint $table) {
            $table->string('descption')->nullable(false)->change();
            $table->string('content')->nullable(false)->change();

            $table->dropColumn([
                'type_blog', 'homepage', 'hot'
            ]);
        });
    }
};
