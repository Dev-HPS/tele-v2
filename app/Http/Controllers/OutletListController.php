<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use App\Services\DatabaseService;
use App\Models\OutletCall;
use App\Models\Transaction;
use App\Models\NonOrderingOutlet;

class OutletListController extends Controller
{
    private DatabaseService $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }
    public function index(Request $request)
    {
        $title = 'Daftar Outlet';
        $selectedDate = $request->get('date', Carbon::now()->format('Y-m-d'));
        $selectedTp = $request->get('tp', '');
        $selectedKaresidenan = $request->get('karesidenan', '');
        $selectedKota = $request->get('kota', '');
        $selectedKecamatan = $request->get('kecamatan', '');

        $dayName = Carbon::parse($selectedDate)->translatedFormat('l');
        $isToday = Carbon::parse($selectedDate)->isToday();

        // Get filter options based on user role
        $user = auth()->user();
        $roleId = $user->role_id;

        // Check if user is telemarketing
        $isTelemarketing = $roleId === 'a9c8a2bf-1398-4a3a-af69-38cf2b1eec0b';

        // Get TP list based on role
        // if ($isTelemarketing) {
        //     // For telemarketing, only show TPs assigned to them
        //     $tpList = DB::table('user_tp')
        //         ->join('tp_master', 'user_tp.tp', '=', 'tp_master.tp')
        //         ->where('user_tp.user_id', $user->id)
        //         ->where('user_tp.deleted_at', null)
        //         ->where('tp_master.deleted_at', null)
        //         ->select('tp_master.tp', 'tp_master.tp_name')
        //         ->distinct()
        //         ->get();
        // } else {
        //     // For other roles, show all TPs
        //     $tpList = DB::table('tp_master')
        //         ->where('deleted_at', null)
        //         ->select('tp', 'tp_name')
        //         ->orderBy('tp_name')
        //         ->get();
        // }

        // // Get Karesidenan list
        // $karesidenanList = DB::table('residency_master')
        //     ->where('deleted_at', null)
        //     ->select('residency_code', 'residency_name')
        //     ->orderBy('residency_name')
        //     ->get();

        // // Get Kota list (filtered by karesidenan if selected)
        // $kotaQuery = DB::table('city_master')
        //     ->where('deleted_at', null);

        // if ($selectedKaresidenan) {
        //     $kotaQuery->where('residency_code', $selectedKaresidenan);
        // }

        // $kotaList = $kotaQuery->select('city_code', 'city_name')
        //     ->orderBy('city_name')
        //     ->get();

        // // Get Kecamatan list (filtered by kota if selected)
        // $kecamatanQuery = DB::table('district_master')
        //     ->where('deleted_at', null);

        // if ($selectedKota) {
        //     $kecamatanQuery->where('city_code', $selectedKota);
        // }

        // $kecamatanList = $kecamatanQuery->select('district_code', 'district_name')
        //     ->orderBy('district_name')
        //     ->get();

        return view('outlet-list.index', compact(
            'title',
            'selectedDate',
            'selectedTp',
            'selectedKaresidenan',
            'selectedKota',
            'selectedKecamatan',
            'dayName',
            'isToday',
            // 'tpList',
            // 'karesidenanList',
            // 'kotaList',
            // 'kecamatanList',
            'isTelemarketing'
        ));
    }

    public function datatable(Request $request)
    {
        $selectedDate = $request->get('date', Carbon::now()->format('d-m-Y'));
        $day = Carbon::parse($request->get('date'))->translatedFormat('l');

        $selectedSbu = $request->get('sbu', '');
        $selectedTp = $request->get('tp', '');
        $selectedKota = $request->get('kota', '');
        $selectedKecamatan = $request->get('kecamatan', '');

        $user = auth()->user();
        $roleId = $user->role_id;
        $isTelemarketing = $roleId === '2629192e-1c3f-477e-a157-4def565dace3';
        $isToday = Carbon::parse($selectedDate)->isToday();

        // Parse the selected date for comparison
        $dateToCheck = Carbon::parse($selectedDate)->format('Y-m-d');

        $query = DB::table('outlet_calls as outlet_call')
            ->where('outlet_call.day', $day)
            ->whereNull('outlet_call.deleted_at')
            ->where('outlet_call.status', 1)
            ->select([
                'outlet_call.id',
                'outlet_call.outlet_code',
                'outlet_call.outlet_name',
                'outlet_call.outlet_phone',
                'outlet_call.tp_name',
                'outlet_call.residency_name',
                'outlet_call.city_name',
                'outlet_call.district_name',
                'outlet_call.residency as residency_code',
                'outlet_call.city as city_code',
                'outlet_call.district as district_code',
                'outlet_call.tp',
                'outlet_call.sbu_code',
                DB::raw("(CASE 
                    WHEN EXISTS(
                        SELECT 1 FROM transactions 
                        WHERE transactions.outlet_code = outlet_call.outlet_code 
                        AND DATE(transactions.order_date) = '{$dateToCheck}'
                        AND transactions.deleted_at IS NULL
                        LIMIT 1
                    ) THEN 'Order'
                    WHEN EXISTS(
                        SELECT 1 FROM non_ordering_outlets 
                        WHERE non_ordering_outlets.outlet_code = outlet_call.outlet_code 
                        AND DATE(non_ordering_outlets.created_at) = '{$dateToCheck}'
                        AND non_ordering_outlets.deleted_at IS NULL
                        LIMIT 1
                    ) THEN 'No Order'
                    ELSE 'Belum Aksi'
                END) as status")
            ]);

        // Role-based filtering
        if ($isTelemarketing) {
            $userTps = DB::table('user_tp')
                ->where('user_id', $user->id)
                ->pluck('tp')
                ->toArray();

            if (!empty($userTps)) {
                $query->whereIn('outlet_call.tp', $userTps);
            }
        }

        // Apply filters
        if ($selectedSbu) {
            $query->where('outlet_call.sbu_code', $selectedSbu);
        }

        if ($selectedTp) {
            $query->where('outlet_call.tp', $selectedTp);
        }

        if ($selectedKota) {
            $query->where('outlet_call.city', $selectedKota);
        }

        if ($selectedKecamatan) {
            $query->where('outlet_call.district', $selectedKecamatan);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status', function ($row) {
                if ($row->status == 'Order') {
                    return '<span class="badge bg-success">Order</span>';
                } elseif ($row->status == 'No Order') {
                    return '<span class="badge bg-warning">No Order</span>';
                } else {
                    return '<span class="badge bg-secondary">Belum Aksi</span>';
                }
            })
            ->addColumn('action', function ($row) use ($isToday) {
                $buttons = '';

                // Detail Piutang button - always available
                $buttons .= '<button type="button" class="btn btn-sm btn-info me-1" 
                    onclick="showPiutangDetail(\'' . $row->outlet_code . '\')" 
                    title="Detail Piutang">
                    <i class="fas fa-money-bill"></i> Detail Piutang
                </button>';

                // Only show action buttons if today and outlet hasn't taken any action yet
                if ($isToday && $row->status == 'Belum Aksi') {
                    $buttons .= '<button type="button" class="btn btn-sm btn-success me-1" 
                        onclick="orderOutlet(\'' . $row->outlet_code . '\', \'' . $row->residency_code . '\', \'' . $row->city_code . '\', \'' . $row->district_code . '\')" 
                        title="Order">
                        <i class="fas fa-plus"></i> Order
                    </button>';

                    $buttons .= '<button type="button" class="btn btn-sm btn-warning" 
                        onclick="noOrderOutlet(\'' . $row->outlet_code . '\')" 
                        title="Tidak Order">
                        <i class="fas fa-times"></i> No Order
                    </button>';
                } elseif (!$isToday) {
                    $buttons .= '<span class="badge bg-secondary">Tidak dapat melakukan aksi order</span>';
                } elseif ($row->status != 'Belum Aksi') {
                    $buttons .= '<span class="badge bg-info">Sudah melakukan aksi</span>';
                }

                return $buttons;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function getCitiesByResidency(Request $request)
    {
        $residencyCode = $request->get('residency_code');

        $cities = DB::table('city_master')
            ->where('residency_code', $residencyCode)
            ->where('deleted_at', null)
            ->select('city_code', 'city_name')
            ->orderBy('city_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    public function getDistrictsByCity(Request $request)
    {
        $cityCode = $request->get('city_code');

        $districts = DB::table('district_master')
            ->where('city_code', $cityCode)
            ->where('deleted_at', null)
            ->select('district_code', 'district_name')
            ->orderBy('district_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $districts
        ]);
    }

    /**
     * Get piutang detail for outlet
     */
    public function getPiutangDetail(Request $request)
    {
        try {
            $outletCode = $request->get('outlet_code');

            if (!$outletCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet code is required'
                ], 400);
            }

            // Get SBU code for the outlet
            $outlet = OutletCall::where('outlet_code', $outletCode)->first();

            if (!$outlet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet not found'
                ], 404);
            }

            // Call stored procedure with fixed parameters [1, 1] and outlet code
            $result = $this->databaseService->callDatabaseFunction(
                'public.sp_target_omset_call',
                [1, 1, $outletCode],
                $outlet->sbu_code
            );

            $data = $result['data'] ?? [];

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found for this outlet'
                ]);
            }

            // Get the first record (should be the outlet data)
            $piutangData = $data[0];

            return response()->json([
                'success' => true,
                'data' => [
                    'outlet_code' => $outletCode,
                    'outlet_name' => $outlet->outlet_name,
                    'target' => $piutangData->target ?? 0,
                    'omset' => $piutangData->omset ?? 0,
                    'sld_piutang' => $piutangData->sld_piutang ?? 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving piutang data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSbuOptions()
    {
        try {
            $role = auth()->user()->role->id;
            $sbuData = \App\Helpers\SbuHelper::getSbuData();

            // Filter kalau role tertentu hanya boleh akses sbu_code sesuai user
            // Admin, Head Telemarketing, dan IT Pusat bisa akses semua SBU
            if (!in_array($role, ['26be63b7-b410-46fc-a2f7-8dc3ed9e29ed', '346d417a-544d-48f3-bb4d-1da4ce54dffc', '9d2fe89a-3ed9-4cc1-9be7-45101f8b4fe8'], true)) {
                $sbuData = $sbuData->where('sbu_code', auth()->user()->sbu_code)->values();
            }

            return response()->json([
                'success' => true,
                'data' => $sbuData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getTp(Request $request)
    {
        $sbuCode = $request->sbu_code;
        return $this->databaseService->callDatabaseFunction('public.sp_master_tp', [], $sbuCode);
    }

    public function getCitiesByTp(Request $request)
    {
        $tp = $request->tp;
        $sbuCode = $request->sbu_code;
        return $this->databaseService->callDatabaseFunction('public.sp_kabupaten_tp', [$tp], $sbuCode);
    }

    public function getDistrictsByTpCity(Request $request)
    {
        $tp = $request->tp;
        $city = $request->city;
        $sbuCode = $request->sbu_code;
        return $this->databaseService->callDatabaseFunction('public.sp_kecamatan_tp', [$tp, $city], $sbuCode);
    }
}
