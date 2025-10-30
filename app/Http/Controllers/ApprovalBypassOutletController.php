<?php

namespace App\Http\Controllers;

use App\Models\BypassOutlet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class ApprovalBypassOutletController extends Controller
{
    public function index()
    {
        $title = 'Approval Bypass Outlet';

        return view('approval-bypass-outlet.index', compact('title'));
    }

    public function datatable(Request $request)
    {
        $query = BypassOutlet::with(['creator', 'approver', 'outlet'])
            ->select([
                'bypass_outlets.*'
            ]);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('creator_name', function ($row) {
                return $row->creator ? $row->creator->name : '-';
            })
            ->editColumn('outlet.outlet_name', function ($row) {
                return $row->outlet->outlet_name ?? '';
            })
            ->editColumn('outlet.district_name', function ($row) {
                return $row->outlet->district_name ?? '';
            })
            ->editColumn('outlet.city_name', function ($row) {
                return $row->outlet->city_name ?? '';
            })
            ->editColumn('outlet.tp_name', function ($row) {
                return $row->outlet->tp_name ?? '';
            })
            ->addColumn('approver_name', function ($row) {
                return $row->approver ? $row->approver->name : '-';
            })
            ->addColumn('status_badge', function ($row) {
                return $row->status_badge;
            })
            ->addColumn('date_formatted', function ($row) {
                return Carbon::parse($row->date)->format('d F Y');
            })
            ->addColumn('created_at_formatted', function ($row) {
                return Carbon::parse($row->created_at)->format('d F Y H:i');
            })
            ->addColumn('action', function ($row) {
                $buttons = '';

                // Only Head Telemarketing and Admin can approve/reject
                if (
                    $row->status == BypassOutlet::STATUS_PENDING &&
                    in_array(auth()->user()->role_id, [
                        '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed', // Head Telemarketing
                        '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
                    ])
                ) {
                    $buttons .= '<button type="button" class="btn btn-sm btn-success me-1" 
                        onclick="approveBypassOutlet(\'' . $row->id . '\')" title="Approve">
                        <i class="fas fa-check"></i> Approve
                    </button>';

                    $buttons .= '<button type="button" class="btn btn-sm btn-danger" 
                        onclick="rejectBypassOutlet(\'' . $row->id . '\')" title="Reject">
                        <i class="fas fa-times"></i> Reject
                    </button>';
                }

                // Show view button for all approved/rejected items
                // if ($row->status != BypassOutlet::STATUS_PENDING) {
                //     $buttons .= '<button type="button" class="btn btn-sm btn-info" 
                //         onclick="viewBypassOutlet(\'' . $row->id . '\')" title="View">
                //         <i class="fas fa-eye"></i> View
                //     </button>';
                // }

                return $buttons ?: '<span class="text-muted">-</span>';
            })
            ->rawColumns(['status_badge', 'action', 'outlet.outlet_name', 'outlet.district_name', 'outlet.city_name', 'outlet.tp_name'])
            ->make(true);
    }

    public function show($id)
    {
        $bypassOutlet = BypassOutlet::with(['creator', 'approver'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bypassOutlet->id,
                'outlet_code' => $bypassOutlet->outlet_code,
                'date' => Carbon::parse($bypassOutlet->date)->format('d F Y'),
                'description' => $bypassOutlet->description,
                'reason' => $bypassOutlet->reason,
                'status' => $bypassOutlet->status_text,
                'creator_name' => $bypassOutlet->creator ? $bypassOutlet->creator->name : '-',
                'approver_name' => $bypassOutlet->approver ? $bypassOutlet->approver->name : '-',
                'created_at' => Carbon::parse($bypassOutlet->created_at)->format('d F Y H:i'),
                'updated_at' => Carbon::parse($bypassOutlet->updated_at)->format('d F Y H:i')
            ]
        ]);
    }

    public function approve(Request $request, $id)
    {
        $bypassOutlet = BypassOutlet::findOrFail($id);

        // Check if user can approve (only Head Telemarketing and Admin)
        if (!in_array(auth()->user()->role_id, [
            '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed', // Head Telemarketing
            '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk approve data ini'
            ], 403);
        }

        // Check if still pending
        if ($bypassOutlet->status != BypassOutlet::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah diproses sebelumnya'
            ], 400);
        }

        try {
            $bypassOutlet->update([
                'status' => BypassOutlet::STATUS_APPROVED,
                'approved_by' => auth()->id(),
                'reason' => null // Clear any previous rejection reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bypass outlet berhasil diapprove'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $bypassOutlet = BypassOutlet::findOrFail($id);

        // Check if user can reject (only Head Telemarketing and Admin)
        if (!in_array(auth()->user()->role_id, [
            '26be63b7-b410-46fc-a2f7-8dc3ed9e29ed', // Head Telemarketing
            '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
        ])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk reject data ini'
            ], 403);
        }

        // Check if still pending
        if ($bypassOutlet->status != BypassOutlet::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Data sudah diproses sebelumnya'
            ], 400);
        }

        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $bypassOutlet->update([
                'status' => BypassOutlet::STATUS_REJECTED,
                'approved_by' => auth()->id(),
                'reason' => $request->reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bypass outlet berhasil direject'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
