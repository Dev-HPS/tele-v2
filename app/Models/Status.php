<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    CONST CREATED_AT = null;
    CONST UPDATED_AT = null;

    protected $table = 'status';
    
}
