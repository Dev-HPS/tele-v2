<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuid;

class TransactionDetailTmp extends Model
{
    use HasFactory, Uuid;

    const CREATED_AT = null;
    const UPDATED_AT = null;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $table = 'transaction_details_tmp';

    protected $fillable = [
        'transaction_id',
        'product_code',
        'product_name',
        'qty',
        'unit',
        'warehouse',
        'supplier',
        'ordet_id',
        'product_id',
        'product_type',
        'transaction_detail_id',
        'transaction_tmp_id',
        'reason',
        'reason_type'
    ];

    public function detailTransaction()
    {
        return $this->belongsTo(TransactionDetail::class, 'transaction_detail_id');
    }
}
