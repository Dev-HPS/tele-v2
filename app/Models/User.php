<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Traits\Uuid;
use App\Models\Transaction;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'uuid';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'sbu_code',
        'role_id'
    ];

    protected $with = ['userDetails'];

    protected $appends = ['userResidence'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Hash::make($value),
        );
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function userDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserDetail::class);
    }

    public function userTp(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserTp::class);
    }

    public function getUserResidenceAttribute()
    {
        return $this->userDetails()->pluck('residency')->implode(',');
    }
}
