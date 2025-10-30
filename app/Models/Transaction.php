<?php

namespace App\Models;

use App\Models\User;
use App\Traits\Uuid;
use App\Models\TransactionLog;
use App\Models\TransactionType;
use App\Models\TransactionDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'sbu_code',
        'order_date',
        'delivery_date',
        'description',
        'status',
        'vehicle_plate',
        'ticket_number',
        'sort',
        'outlet_code',
        'outlet_name',
        'outlet_address',
        'outlet_owner',
        'outlet_phone',
        'outlet_longitude',
        'outlet_latitude',
        'user_id',
        'residency',
        'residency_name',
        'city',
        'city_name',
        'district',
        'district_name',
        'type_id',
        'order_code',
        'order_id',
        'tele_order'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(TransactionType::class, 'type_id', 'id');
    }

    public function logs()
    {
        return $this->hasMany(TransactionLog::class, 'transaction_id', 'id');
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id', 'id');
    }
}
