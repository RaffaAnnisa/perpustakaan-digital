<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            // Referensi lintas modul: hanya angka, TANPA foreign key ke tabel modul lain
            $table->unsignedBigInteger('member_id')->index();
            $table->unsignedBigInteger('processed_by')->nullable(); // id user pustakawan
            $table->date('loan_date');
            $table->date('due_date');
            $table->dateTime('returned_at')->nullable();
            $table->unsignedInteger('fine_per_day');                // snapshot tarif denda
            $table->string('status')->default('borrowed');          // borrowed | returned
            $table->timestamps();
        });

        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            // One-to-Many: Loan -> LoanItem (satu modul)
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->unsignedBigInteger('book_id')->index();         // referensi lintas modul
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'book_id']);
        });

        Schema::create('fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->unique()->constrained('loans')->cascadeOnDelete();
            $table->unsignedInteger('days_late');
            $table->unsignedInteger('amount');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
        Schema::dropIfExists('loan_items');
        Schema::dropIfExists('loans');
    }
};
