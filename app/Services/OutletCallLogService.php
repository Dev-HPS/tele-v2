<?php

namespace App\Services;

use App\Models\OutletCallLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutletCallLogService
{
    /**
     * Create a log entry for outlet call activity
     */
    public static function log(
        $outletCallId,
        $action,
        $description = null,
        $oldData = null,
        $newData = null,
        $status = 'pending'
    ) {
        $request = request();

        return OutletCallLog::create([
            'outlet_call_id' => $outletCallId,
            'user_id' => Auth::id(),
            'action' => $action,
            'status' => $status,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Log create action
     */
    public static function logCreate($outletCallId, $newData, $description = null)
    {
        return self::log(
            $outletCallId,
            'create',
            $description ?: 'Menambah data outlet call baru',
            null,
            $newData,
            'pending'
        );
    }

    /**
     * Log update action
     */
    public static function logUpdate($outletCallId, $oldData, $newData, $description = null)
    {
        return self::log(
            $outletCallId,
            'update',
            $description ?: 'Mengupdate data outlet call',
            $oldData,
            $newData,
            'pending'
        );
    }

    /**
     * Log delete action
     */
    public static function logDelete($outletCallId, $oldData, $reason = null)
    {
        return self::log(
            $outletCallId,
            'delete',
            $reason ?: 'Menghapus data outlet call',
            $oldData,
            null,
            'pending'
        );
    }

    /**
     * Log approve action
     */
    public static function logApprove($outletCallId, $oldData, $newData, $description = null)
    {
        return self::log(
            $outletCallId,
            'approve',
            $description ?: 'Menyetujui data outlet call',
            $oldData,
            $newData,
            'approved'
        );
    }

    /**
     * Log reject action
     */
    public static function logReject($outletCallId, $oldData, $reason = null)
    {
        return self::log(
            $outletCallId,
            'reject',
            $reason ?: 'Menolak data outlet call',
            $oldData,
            null,
            'rejected'
        );
    }

    /**
     * Log restore action
     */
    public static function logRestore($outletCallId, $oldData, $newData, $description = null)
    {
        return self::log(
            $outletCallId,
            'restore',
            $description ?: 'Mengembalikan data outlet call',
            $oldData,
            $newData,
            'approved'
        );
    }

    /**
     * Update log status
     */
    public static function updateLogStatus($outletCallId, $action, $status)
    {
        return OutletCallLog::where('outlet_call_id', $outletCallId)
            ->where('action', $action)
            ->update(['status' => $status]);
    }

    /**
     * Get logs for specific outlet call
     */
    public static function getLogsForOutletCall($outletCallId)
    {
        return OutletCallLog::where('outlet_call_id', $outletCallId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get recent logs
     */
    public static function getRecentLogs($limit = 50)
    {
        return OutletCallLog::with(['user', 'outletCall'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by action
     */
    public static function getLogsByAction($action, $limit = 50)
    {
        return OutletCallLog::byAction($action)
            ->with(['user', 'outletCall'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by status
     */
    public static function getLogsByStatus($status, $limit = 50)
    {
        return OutletCallLog::byStatus($status)
            ->with(['user', 'outletCall'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
