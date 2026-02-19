<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'price',
        'category',
        'category_id',
        'description',
        'is_active',
        'added_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function categoryRef(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
