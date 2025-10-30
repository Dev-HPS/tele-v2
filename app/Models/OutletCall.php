<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutletCall extends Model
{
    use HasFactory, Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'outlet_code',
        'outlet_name',
        'outlet_owner',
        'outlet_phone',
        'outlet_address',
        'tp',
        'tp_name',
        'residency',
        'residency_name',
        'city',
        'city_name',
        'district',
        'district_name',
        'day',
        'type',
        'status',
        'reason',
        'description',
        'validation',
        'sbu_code'
    ];
}
