<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutletCallLog extends Model
{
    use HasFactory;

    protected $table = 'outlet_call_logs';

    protected $fillable = [
        'outlet_call_id',
        'user_id',
        'action',
        'status',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship to User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Relationship to OutletCall
     */
    public function outletCall()
    {
        return $this->belongsTo(OutletCall::class, 'outlet_call_id', 'id');
    }

    /**
     * Scope untuk filter berdasarkan aksi
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        $labels = [
            'create' => 'Tambah Data',
            'update' => 'Update Data',
            'delete' => 'Hapus Data',
            'approve' => 'Approve Data',
            'reject' => 'Reject Data',
            'restore' => 'Restore Data',
        ];

        return $labels[$this->action] ?? $this->action;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $labels[$this->status] ?? $this->status;
    }
}
