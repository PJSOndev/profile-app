<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'shift_id')) {
                $table->unsignedBigInteger('shift_id')->nullable()->after('branch_assigned');
                $table->foreign('shift_id')
                    ->references('shift_id')
                    ->on('shift')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
        });
    }
};
