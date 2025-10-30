<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionDetail extends Model
{
    use HasFactory, Uuid;

    CONST CREATED_AT = null;
    CONST UPDATED_AT = null;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'transaction_id', 'product_code', 'product_name', 'qty', 'unit', 'warehouse', 'supplier', 'ordet_id', 'product_id', 'product_type'
    ];
}
