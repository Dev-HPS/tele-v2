<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NonOrderingOutlet extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    protected $fillable = [
        'outlet_code',
        'category_id',
        'description',
        'created_by',
    ];

    protected $casts = [
        'id' => 'string',
        'category_id' => 'string',
        'created_by' => 'string',
        'deleted_at' => 'timestamp',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Get the category that owns the non-ordering outlet.
     */
    public function category()
    {
        return $this->belongsTo(NonOrderingCategory::class, 'category_id');
    }

    /**
     * Get the outlet call that owns the non-ordering outlet.
     */
    public function outletCall()
    {
        return $this->belongsTo(OutletCall::class, 'outlet_code', 'outlet_code');
    }

    /**
     * Get the user who created this non-ordering outlet.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
