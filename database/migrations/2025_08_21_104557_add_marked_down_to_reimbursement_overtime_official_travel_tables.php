<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            $table->boolean('marked_down')->default(false);
            $table->string('customer');
        });
        Schema::table('overtimes', function (Blueprint $table) {
            $table->boolean('marked_down')->default(false);
            $table->string('customer');
        });
        Schema::table('official_travels', function (Blueprint $table) {
            $table->boolean('marked_down')->default(false);
            $table->string('customer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reimbursements', function (Blueprint $table) {
            //
        });
    }
};
