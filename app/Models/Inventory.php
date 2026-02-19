<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'date',
        'branch_assigned',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'date' => 'datetime',
        ];
    }
}
