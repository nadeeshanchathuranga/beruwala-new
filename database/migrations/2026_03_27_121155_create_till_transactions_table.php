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
       Schema::create('till_transactions', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('shift_id')->nullable();
        $table->unsignedBigInteger('user_id');

        $table->string('transaction_type'); // cash_in / cash_out
        $table->text('note')->nullable();

        $table->decimal('amount', 10, 2);

         $table->timestamps();

        // foreign keys
        $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('till_transactions');
    }
};
