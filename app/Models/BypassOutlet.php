<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Uuid;

class BypassOutlet extends Model
{
    use HasFactory, SoftDeletes, Uuid;

    public $incrementing = false;

    protected $keyType = 'uuid';

    protected $fillable = [
        'outlet_code',
        'date',
        'status',
        'description',
        'reason',
        'created_by',
        'approved_by'
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'integer'
    ];

    protected $dates = [
        'deleted_at'
    ];

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function outlet()
    {
        return $this->belongsTo(OutletCall::class, 'outlet_code', 'outlet_code');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'Pending';
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_REJECTED:
                return 'Rejected';
            default:
                return 'Unknown';
        }
    }

    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return '<span class="badge bg-warning">Pending</span>';
            case self::STATUS_APPROVED:
                return '<span class="badge bg-success">Approved</span>';
            case self::STATUS_REJECTED:
                return '<span class="badge bg-danger">Rejected</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
    }
}
