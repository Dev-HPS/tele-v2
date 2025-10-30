<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;


class TransactionTmp extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $table = 'transactions_tmp';

    protected $fillable = [
        'sbu_code', 'order_date', 'delivery_date', 'description', 'status', 'vehicle_plate',
        'ticket_number', 'sort',
        'outlet_code', 'outlet_name', 'outlet_address', 'outlet_owner', 'outlet_phone',
        'outlet_longitude', 'outlet_latitude', 'user_id',
        'residency', 'residency_name', 'city', 'city_name', 'district', 'district_name', 'type_id', 'order_code', 'order_id', 'reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetailTmp::class, 'transaction_tmp_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'type_id', 'id');
    }

    public function transactions()
    {
        return $this->belongsTo(Transaction::class, 'ticket_number', 'ticket_number');
    }
}
