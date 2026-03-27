<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (Schema::hasTable('syn_logs') && Schema::hasColumn('syn_logs', 'record_id')) {
                Schema::table('syn_logs', function (Blueprint $table) {
                    $table->renameColumn('record_id', 'module');
                });
            }

            return;
        }

        DB::statement('ALTER TABLE syn_logs CHANGE record_id module VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            if (Schema::hasTable('syn_logs') && Schema::hasColumn('syn_logs', 'module')) {
                Schema::table('syn_logs', function (Blueprint $table) {
                    $table->renameColumn('module', 'record_id');
                });
            }

            return;
        }

        DB::statement('ALTER TABLE syn_logs CHANGE module record_id VARCHAR(255)');
    }
};
