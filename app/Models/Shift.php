<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $table = 'shift';

    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'name',
        'time_in',
        'time_out',
        'role_scope',
        'description',
        'branch_assigned',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
