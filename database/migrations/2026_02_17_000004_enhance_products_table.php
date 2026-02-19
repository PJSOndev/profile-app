<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('category');
            }

            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stock');
            }

            if (! Schema::hasColumn('products', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('products', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }

            if (! Schema::hasColumn('products', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            if (Schema::hasColumn('products', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            if (Schema::hasColumn('products', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('products', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('products', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
