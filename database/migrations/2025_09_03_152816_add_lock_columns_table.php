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
        Schema::table('overtimes', function (Blueprint $table) {
            $table->unsignedBigInteger('locked_by')->nullable()->after('marked_down');
            $table->timestamp('locked_at')->nullable()->after('locked_by');
        });

        Schema::table('official_travels', function (Blueprint $table) {
            $table->unsignedBigInteger('locked_by')->nullable()->after('marked_down');
            $table->timestamp('locked_at')->nullable()->after('locked_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn(['locked_by', 'locked_at']);
        });

        Schema::table('official_travels', function (Blueprint $table) {
            $table->dropColumn(['locked_by', 'locked_at']);
        });
    }
};
