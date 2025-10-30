<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTp extends Model
{
    use HasFactory;

    protected $table = 'user_tp';

    protected $fillable = [
        'user_id',
        'tp',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
