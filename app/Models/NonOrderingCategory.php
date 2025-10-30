<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonOrderingCategory extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'id' => 'string',
        'deleted_at' => 'timestamp',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the non-ordering outlets for the category.
     */
    public function nonOrderingOutlets()
    {
        return $this->hasMany(NonOrderingOutlet::class, 'category_id');
    }
}
