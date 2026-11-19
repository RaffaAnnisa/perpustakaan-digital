<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('max_loans');        // maksimal buku yang boleh dipinjam bersamaan
            $table->unsignedInteger('loan_days');        // lama pinjam (hari)
            $table->unsignedInteger('fine_per_day');     // denda per hari (Rupiah)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_types');
    }
};
