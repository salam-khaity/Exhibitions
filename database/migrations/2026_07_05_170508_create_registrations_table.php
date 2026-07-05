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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')
                ->constrained('exhibitions')
                ->onDelete('cascade');
            $table->foreignId('visitor_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('ticket_code', 50)->unique();
            $table->enum('status', ['confirmed', 'attended', 'cancelled'])
                ->default('confirmed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
