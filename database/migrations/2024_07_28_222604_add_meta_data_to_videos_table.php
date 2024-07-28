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
        Schema::table('videos', function (Blueprint $table) {
            //
            $table->integer('date_raw')->nullable()->after('date');
            $table->string('title')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('episode')->nullable();
            $table->integer('season')->nullable();
            $table->integer('view_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            //
            $table->dropColumn('date_raw');
            $table->dropColumn('title');
            $table->dropColumn('duration');
            $table->dropColumn('episode');
            $table->dropColumn('season');
            $table->dropColumn('view_count');
        });
    }
};
