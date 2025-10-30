<?php

namespace App\Http\Controllers;

use App\Models\OutletCallLog;
use App\Services\OutletCallLogService;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OutletCallLogController extends Controller
{
    public function index()
    {
        $title = 'Log Aktivitas Outlet Call';

        return view('outlet-call-logs.index', compact('title'));
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $query = OutletCallLog::with(['user', 'outletCall'])
                ->select('outlet_call_logs.*');

            // Apply filters
            if ($request->has('action_filter') && $request->action_filter != 'All') {
                $query->where('action', $request->action_filter);
            }

            if ($request->has('status_filter') && $request->status_filter != 'All') {
                $query->where('status', $request->status_filter);
            }

            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $data = $query->orderBy('created_at', 'desc');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'Unknown User';
                })
                ->addColumn('outlet_name', function ($row) {
                    return $row->outletCall ? $row->outletCall->outlet_name : 'Data Terhapus';
                })
                ->addColumn('action_label', function ($row) {
                    $badges = [
                        'create' => 'bg-success',
                        'update' => 'bg-info',
                        'delete' => 'bg-danger',
                        'approve' => 'bg-primary',
                        'reject' => 'bg-warning',
                        'restore' => 'bg-secondary'
                    ];

                    $badgeClass = $badges[$row->action] ?? 'bg-dark';
                    return '<span class="badge ' . $badgeClass . '">' . $row->action_label . '</span>';
                })
                ->addColumn('status_label', function ($row) {
                    $badges = [
                        'pending' => 'bg-warning',
                        'approved' => 'bg-success',
                        'rejected' => 'bg-danger'
                    ];

                    $badgeClass = $badges[$row->status] ?? 'bg-dark';
                    return '<span class="badge ' . $badgeClass . '">' . $row->status_label . '</span>';
                })
                ->addColumn('created_at_formatted', function ($row) {
                    return $row->created_at->format('d/m/Y H:i:s');
                })
                ->addColumn('action', function ($row) {
                    return '<button class="btn btn-sm btn-info" onclick="showLogDetail(\'' . $row->id . '\')">Detail</button>';
                })
                ->rawColumns(['action_label', 'status_label', 'action'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $log = OutletCallLog::with(['user', 'outletCall'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'user_name' => $log->user ? $log->user->name : 'Unknown User',
                'outlet_name' => $log->outletCall ? $log->outletCall->outlet_name : 'Data Terhapus',
                'action' => $log->action_label,
                'status' => $log->status_label,
                'description' => $log->description,
                'old_data' => $log->old_data,
                'new_data' => $log->new_data,
                'ip_address' => $log->ip_address,
                'user_agent' => $log->user_agent,
                'created_at' => $log->created_at->format('d/m/Y H:i:s')
            ]
        ]);
    }

    public function getStats()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_logs' => OutletCallLog::count(),
            'today_logs' => OutletCallLog::whereDate('created_at', $today)->count(),
            'month_logs' => OutletCallLog::where('created_at', '>=', $thisMonth)->count(),
            'pending_logs' => OutletCallLog::where('status', 'pending')->count(),
            'approved_logs' => OutletCallLog::where('status', 'approved')->count(),
            'rejected_logs' => OutletCallLog::where('status', 'rejected')->count(),
        ];

        // Action statistics
        $actionStats = OutletCallLog::select('action', DB::raw('count(*) as total'))
            ->groupBy('action')
            ->pluck('total', 'action')
            ->toArray();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'action_stats' => $actionStats
        ]);
    }
}
