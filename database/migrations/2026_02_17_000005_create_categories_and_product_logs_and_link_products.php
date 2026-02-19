<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table): void {
                $table->increments('id');
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'category_id')) {
                $table->unsignedInteger('category_id')->nullable()->after('category');
            }
            if (! Schema::hasColumn('products', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('is_active');
            }
            if (! Schema::hasColumn('products', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('added_by');
            }
            if (! Schema::hasColumn('products', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }
        });

        if (! $this->foreignKeyExists('products', 'products_category_id_foreign')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            });
        }
        if (! $this->foreignKeyExists('products', 'products_added_by_foreign')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();
            });
        }
        if (! $this->foreignKeyExists('products', 'products_updated_by_foreign')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            });
        }
        if (! $this->foreignKeyExists('products', 'products_deleted_by_foreign')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('product_logs')) {
            Schema::create('product_logs', function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->integer('product_id')->nullable();
                $table->string('action', 50);
                $table->json('details')->nullable();
                $table->unsignedBigInteger('performed_by')->nullable();
                $table->timestamp('performed_at')->nullable();
                $table->timestamps();
            });
        } else {
            DB::statement('ALTER TABLE product_logs MODIFY product_id INT NULL');
        }

        if (! $this->foreignKeyExists('product_logs', 'product_logs_product_id_foreign')) {
            Schema::table('product_logs', function (Blueprint $table): void {
                $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            });
        }
        if (! $this->foreignKeyExists('product_logs', 'product_logs_performed_by_foreign')) {
            Schema::table('product_logs', function (Blueprint $table): void {
                $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        $categories = DB::table('products')
            ->select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        foreach ($categories as $categoryName) {
            $existing = DB::table('categories')->where('name', $categoryName)->first();

            $categoryId = $existing?->id ?? DB::table('categories')->insertGetId([
                'name' => $categoryName,
                'description' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')
                ->where('category', $categoryName)
                ->whereNull('category_id')
                ->update(['category_id' => $categoryId]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_logs')) {
            Schema::table('product_logs', function (Blueprint $table): void {
                if (Schema::hasColumn('product_logs', 'product_id')) {
                    $table->dropForeign(['product_id']);
                }
                if (Schema::hasColumn('product_logs', 'performed_by')) {
                    $table->dropForeign(['performed_by']);
                }
            });
            Schema::dropIfExists('product_logs');
        }

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
            }
            if (Schema::hasColumn('products', 'added_by')) {
                $table->dropForeign(['added_by']);
            }
            if (Schema::hasColumn('products', 'updated_by')) {
                $table->dropForeign(['updated_by']);
            }
            if (Schema::hasColumn('products', 'deleted_by')) {
                $table->dropForeign(['deleted_by']);
            }
        });

        Schema::table('products', function (Blueprint $table): void {
            if (Schema::hasColumn('products', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
            if (Schema::hasColumn('products', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('products', 'added_by')) {
                $table->dropColumn('added_by');
            }
            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropColumn('category_id');
            }
        });

        Schema::dropIfExists('categories');
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS count FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$tableName, $constraintName]
        );

        return (int) ($result->count ?? 0) > 0;
    }
};
