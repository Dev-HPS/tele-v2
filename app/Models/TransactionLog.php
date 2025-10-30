<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionLog extends Model
{
    use HasFactory, Uuid;

    CONST UPDATED_AT = null;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'transaction_id', 'user_id', 'status'
    ];
}
