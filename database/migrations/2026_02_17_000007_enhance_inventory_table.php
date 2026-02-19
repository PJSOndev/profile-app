<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventory', 'branch_assigned')) {
                $table->string('branch_assigned')->nullable()->after('quantity');
            }

            if (! Schema::hasColumn('inventory', 'notes')) {
                $table->text('notes')->nullable()->after('branch_assigned');
            }

            if (! Schema::hasColumn('inventory', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('inventory', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('inventory', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (! $this->indexExists('inventory', 'inventory_product_id_index')) {
            Schema::table('inventory', function (Blueprint $table): void {
                $table->index('product_id');
            });
        }

        if (! $this->indexExists('inventory', 'inventory_date_index')) {
            Schema::table('inventory', function (Blueprint $table): void {
                $table->index('date');
            });
        }

        if (! $this->indexExists('inventory', 'inventory_type_index')) {
            Schema::table('inventory', function (Blueprint $table): void {
                $table->index('type');
            });
        }

        if (! $this->foreignKeyExists('inventory', 'inventory_product_id_foreign')) {
            Schema::table('inventory', function (Blueprint $table): void {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        }

        if (! $this->foreignKeyExists('inventory', 'inventory_created_by_foreign')) {
            Schema::table('inventory', function (Blueprint $table): void {
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table): void {
            if (Schema::hasColumn('inventory', 'created_by') && $this->foreignKeyExists('inventory', 'inventory_created_by_foreign')) {
                $table->dropForeign(['created_by']);
            }

            if (Schema::hasColumn('inventory', 'product_id') && $this->foreignKeyExists('inventory', 'inventory_product_id_foreign')) {
                $table->dropForeign(['product_id']);
            }
        });

        Schema::table('inventory', function (Blueprint $table): void {
            if ($this->indexExists('inventory', 'inventory_type_index')) {
                $table->dropIndex('inventory_type_index');
            }

            if ($this->indexExists('inventory', 'inventory_date_index')) {
                $table->dropIndex('inventory_date_index');
            }

            if ($this->indexExists('inventory', 'inventory_product_id_index')) {
                $table->dropIndex('inventory_product_id_index');
            }

            if (Schema::hasColumn('inventory', 'updated_at')) {
                $table->dropColumn('updated_at');
            }

            if (Schema::hasColumn('inventory', 'created_at')) {
                $table->dropColumn('created_at');
            }

            if (Schema::hasColumn('inventory', 'created_by')) {
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('inventory', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('inventory', 'branch_assigned')) {
                $table->dropColumn('branch_assigned');
            }
        });
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS count FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$tableName, $constraintName]
        );

        return (int) ($result->count ?? 0) > 0;
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS count FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$tableName, $indexName]
        );

        return (int) ($result->count ?? 0) > 0;
    }
};
