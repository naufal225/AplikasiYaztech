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
        Schema::create('official_travels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->date('date_start');
            $table->date('date_end');
            $table->bigInteger('total');
            $table->enum('status_1', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('status_2', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_travels');
    }
};
