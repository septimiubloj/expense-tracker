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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->date('occurred_on');
            $table->string('payee')->nullable();
            $table->string('reference')->nullable();
            $table->text('memo')->nullable();
            $table->bigInteger('amount_minor');
            $table->string('status')->default('pending');
            $table->uuid('transfer_group_id')->nullable();
            $table->timestamps();

            $table->index(['book_id', 'occurred_on']);
            $table->index(['account_id', 'occurred_on']);
            $table->index(['category_id', 'occurred_on']);
            $table->index('transfer_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
