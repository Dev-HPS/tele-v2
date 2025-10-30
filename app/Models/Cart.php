<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory, Uuid;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'user_id',
        'outlet_code',
        'outlet_name',
        'outlet_address',
        'product_code',
        'product_name',
        'qty',
        'unit',
        'product_type',
        'product_picture'
    ];
}
