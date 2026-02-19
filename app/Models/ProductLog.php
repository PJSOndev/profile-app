<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLog extends Model
{
    protected $fillable = [
        'product_id',
        'action',
        'details',
        'performed_by',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
