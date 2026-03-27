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
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('expected_cash', 10, 2)->nullable()->after('total_sales');
            $table->decimal('variance_amount', 10, 2)->nullable()->after('expected_cash');
            $table->text('closing_notes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['expected_cash', 'variance_amount', 'closing_notes']);
        });
    }
};
