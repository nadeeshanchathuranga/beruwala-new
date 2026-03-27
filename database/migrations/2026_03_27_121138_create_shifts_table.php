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
         Schema::create('shifts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');

        $table->dateTime('start_time');
        $table->decimal('start_amount', 10, 2);

        $table->dateTime('end_time')->nullable();
        $table->decimal('end_amount', 10, 2)->nullable();

        $table->decimal('total_sales', 10, 2)->default(0);
        $table->text('notes')->nullable();

        $table->string('status')->default('open');

       $table->timestamps();
        // foreign key
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
