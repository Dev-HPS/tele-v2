<?php

namespace App\Http\Controllers;

use App\Models\BypassOutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class BypassOutletController extends Controller
{
    public function index()
    {
        $title = 'Bypass Outlet';

        // Get TP list
        $tpList = DB::table('outlet_calls')
            ->where('deleted_at', null)
            ->select('tp', 'tp_name')
            ->groupBy('tp', 'tp_name')
            ->orderBy('tp_name')
            ->get();

        return view('bypass-outlet.index', compact('title', 'tpList'));
    }

    public function create()
    {
        $title = 'Tambah Bypass Outlet';

        // Get TP list
        $tpList = DB::table('tp_master')
            ->where('deleted_at', null)
            ->select('tp', 'tp_name')
            ->orderBy('tp_name')
            ->get();

        return view('bypass-outlet.create', compact('title', 'tpList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'outlet_code' => 'required|string|max:50',
            'date' => 'required|date',
            'description' => 'required|string'
        ]);

        try {
            BypassOutlet::create([
                'outlet_code' => $request->outlet_code,
                'date' => $request->date,
                'description' => $request->description,
                'status' => BypassOutlet::STATUS_PENDING,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bypass outlet berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function datatable(Request $request)
    {
        $query = BypassOutlet::with(['creator', 'approver', 'outlet'])->get();

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
            ->addColumn('action', function ($row) {
                $buttons = '';

                // Only Leader and Admin can edit pending items
                if (
                    $row->status == BypassOutlet::STATUS_PENDING &&
                    in_array(auth()->user()->role_id, [
                        'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b', // Leader
                        '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
                    ])
                ) {
                    $buttons .= '<button type="button" class="btn btn-sm btn-primary me-1" 
                        onclick="editBypassOutlet(\'' . $row->id . '\')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>';

                    $buttons .= '<button type="button" class="btn btn-sm btn-danger" 
                        onclick="deleteBypassOutlet(\'' . $row->id . '\')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                return $buttons ?: '<span class="text-muted">-</span>';
            })
            ->rawColumns(['status_badge', 'action', 'outlet.outlet_name', 'outlet.district_name', 'outlet.city_name', 'outlet.tp_name'])
            ->make(true);
    }

    public function edit($id)
    {
        $bypassOutlet = BypassOutlet::findOrFail($id);

        // Check if user can edit (only pending items by Leader/Admin)
        if (
            $bypassOutlet->status != BypassOutlet::STATUS_PENDING ||
            !in_array(auth()->user()->role_id, [
                'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b', // Leader
                '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
            ])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengedit data ini'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $bypassOutlet
        ]);
    }

    public function update(Request $request, $id)
    {
        $bypassOutlet = BypassOutlet::findOrFail($id);

        // Check if user can edit
        if (
            $bypassOutlet->status != BypassOutlet::STATUS_PENDING ||
            !in_array(auth()->user()->role_id, [
                'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b', // Leader
                '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
            ])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk mengedit data ini'
            ], 403);
        }

        $request->validate([
            'outlet_code' => 'required|string|max:50',
            'date' => 'required|date',
            'description' => 'required|string'
        ]);

        try {
            $bypassOutlet->update([
                'outlet_code' => $request->outlet_code,
                'date' => $request->date,
                'description' => $request->description
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bypass outlet berhasil diupdate'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $bypassOutlet = BypassOutlet::findOrFail($id);

        // Check if user can delete
        if (
            $bypassOutlet->status != BypassOutlet::STATUS_PENDING ||
            !in_array(auth()->user()->role_id, [
                'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b', // Leader
                '346d417a-544d-48f3-bb4d-1da4ce54dffc'  // Admin
            ])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses untuk menghapus data ini'
            ], 403);
        }

        try {
            $bypassOutlet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bypass outlet berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper methods for AJAX
    public function getCitiesByTp(Request $request)
    {
        $tp = $request->get('tp');

        $cities = DB::table('outlet_calls as outlet_call')

            ->where('outlet_call.tp', $tp)
            ->where('outlet_call.deleted_at', null)
            ->select('outlet_call.city as city_code', 'outlet_call.city_name')
            ->distinct()
            ->orderBy('outlet_call.city_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function getDistrictsByCity(Request $request)
    {
        $tp = $request->get('tp');
        $city = $request->get('city');


        $districts = DB::table('outlet_calls as outlet_call')
            ->where('outlet_call.tp', $tp)
            ->where('outlet_call.city', $city)
            ->where('outlet_call.deleted_at', null)
            ->select('outlet_call.district as district_code', 'outlet_call.district_name')
            ->distinct()
            ->orderBy('outlet_call.district_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    public function getOutletsByDistrict(Request $request)
    {
        $tp = $request->get('tp');
        $district = $request->get('district');

        $outlets = DB::table('outlet_calls as outlet_call')
            ->where('outlet_call.tp', $tp)
            ->where('outlet_call.district', $district)
            ->select('outlet_call.outlet_code', 'outlet_call.outlet_name')
            ->distinct()
            ->orderBy('outlet_call.outlet_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $outlets
        ]);
    }
}
