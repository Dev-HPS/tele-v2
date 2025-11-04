<?php

namespace App\Http\Controllers;

use App\Helpers\SbuHelper;
use App\Models\OutletCall;
use App\Models\NonOrderingCategory;
use App\Models\NonOrderingOutlet;
use App\Models\Transaction;
use App\Models\UserTp;
use App\Repositories\OutletCallRepository;
use Illuminate\Http\Request;
use App\Repositories\StatusRepository;
use App\Repositories\TransactionRepository;
use App\Services\DatabaseService;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Arr; // pastikan ini di atas file kamu jika pakai Arr
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    private TransactionRepository $repository;
    private StatusRepository $statusRepository;
    private OutletCallRepository $outletCallRepository;
    private DatabaseService $databaseService;


    public function __construct(
        TransactionRepository $transactionRepository,
        StatusRepository $statusRepository,
        OutletCallRepository $outletCallRepository,
        DatabaseService $databaseService
    ) {
        $this->repository = $transactionRepository;
        $this->statusRepository = $statusRepository;
        $this->outletCallRepository = $outletCallRepository;
        $this->databaseService = $databaseService;
    }

    public function indexOld(Request $request)
    {
        $title = 'Dashboard';
        $sr = $this->repository->findTotalSRByMonth();
        $inq = $this->repository->findTotalINQByMonth();
        $cp = $this->repository->findTotalCPByMonth();
        $status = $this->statusRepository->findAll('5d886962-1910-46b1-9626-139961e51d78')->get()->pluck('status_name');
        // $db = $this->databaseService->callStoredProcedure('sp_pelanggan_aktif_jual', ['', '', '']);

        // $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // foreach ($db['data'] as $index => $item) {

        //     $outletCall = OutletCall::where('outlet_code', 'LIKE', "%{$item->outlet_code}%")->first();

        //     // if ($outletCall) {
        //     OutletCall::where('outlet_code', 'LIKE', "%{$item->outlet_code}%")->whereNull('outlet_name')->update([
        //         'outlet_name' => $item->outlet_name,
        //         'outlet_owner' => $item->outlet_pkp,
        //         'outlet_address' => $item->outlet_alamat_fp,
        //         'outlet_phone' => $item->no_telp,
        //         'tp' => $item->tp,
        //         'tp_name' => $item->tp_name,
        //         'residency' => $item->residency,
        //         'residency_name' => $item->residency_name,
        //         'city' => $item->city,
        //         'city_name' => $item->city_name,
        //         'district' => $item->district,
        //         'district_name' => $item->district_name,
        //         'status' => 1,
        //         'sbu_code' => 'MAB'
        //     ]);
        // }

        // if ($item->city_name != 'PEMALANG') {
        //     continue;
        // }

        // OutletCall::create([
        //     'outlet_code' => $item->outlet_code,
        //     'outlet_name' => $item->outlet_name,
        //     'outlet_owner' => $item->outlet_pkp,
        //     'outlet_address' => $item->outlet_alamat_fp,
        //     'outlet_phone' => $item->no_telp,
        //     'tp' => $item->tp,
        //     'tp_name' => $item->tp_name,
        //     'residency' => $item->residency,
        //     'residency_name' => $item->residency_name,
        //     'city' => $item->city,
        //     'city_name' => $item->city_name,
        //     'district' => $item->district,
        //     'district_name' => $item->district_name,
        //     'day' => 'Sabtu',
        //     'sbu_code' => 'MAB',
        //     'validation' => true,
        //     'status' => 1
        // ]);
        // }


        // Data untuk dashboard baru dengan TP
        $sbuList = $this->getSbuList();
        // $category = $this->databaseService->callDatabaseFunction('sp_kategori_brg');
        // $category = $category['data'];

        $selectedSbu = $request->sbu ?? null;
        $selectedTp = $request->tp ?? null;
        $selectedCategory = $request->category ?? 1;
        $selectedSort = $request->sort ?? 1;

        $tpList = [];
        $tpData = [];

        if ($selectedSbu) {
            $tpList = $this->getTpBySbu($selectedSbu);
        }

        if ($selectedSbu && $selectedTp) {
            $tpData = $this->getTpDashboardData($selectedSbu, $selectedTp, $selectedCategory, $selectedSort);
        }

        // Get non-ordering outlets data for chart
        $nonOrderingData = $this->getNonOrderingOutletsData();

        return view('dashboard', compact([
            'title',
            'sr',
            'inq',
            'cp',
            'status',
            'sbuList',
            // 'category',
            'tpList',
            'tpData',
            'selectedSbu',
            'selectedTp',
            'selectedCategory',
            'selectedSort',
            'nonOrderingData'
        ]));
    }

    public function outletCall($city, Request $request)
    {
        $title = 'Dashboard Outlet Call';

        // Get basic data for filters
        $category = $this->databaseService->callDatabaseFunction('public.get_item_group');
        $category = $category['data'];

        $selectedCategory = $request->category ?? 1;
        $selectedSort = $request->sort ?? 1;

        // Only load data if it's not an initial page load or if explicitly requested
        $data = [];
        if ($request->has('load_data') || $request->isMethod('POST')) {
            $sbu = OutletCall::where('city', $city)->first();
            if ($sbu) {
                $outlet = $this->outletCallRepository->paramOutlet($city);
                $result = $this->databaseService->callDatabaseFunction('public.sp_target_omset_call', [$selectedCategory, $selectedSort, $outlet], $sbu->sbu_code);
                $data = $result['data'] ?? [];
            }
        }

        return view('dashboard-outlet-call', compact([
            'title',
            'data',
            'category',
            'city',
            'selectedCategory',
            'selectedSort'
        ]));
    }

    /**
     * Get outlet call dashboard data via AJAX
     */
    public function getOutletCallDataAjax($city, Request $request)
    {
        try {
            $selectedCategory = $request->category ?? 1;
            $selectedSort = $request->sort ?? 1;

            $sbu = OutletCall::where('city', $city)->first();
            if (!$sbu) {
                return response()->json([
                    'success' => false,
                    'message' => 'SBU tidak ditemukan untuk city tersebut'
                ]);
            }

            $outlet = $this->outletCallRepository->paramOutlet($city);
            $result = $this->databaseService->callDatabaseFunction('public.sp_target_omset_call', [$selectedCategory, $selectedSort, $outlet], $sbu->sbu_code);
            $data = $result['data'] ?? [];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function datatableTpCall(Request $request)
    {
        if ($request->ajax()) {
            // Check if user has specific role for TP filtering
            $userId = auth()->id();
            $userRoleId = auth()->user()->role_id;

            if ($userRoleId === '2629192e-1c3f-477e-a157-4def565dace3') {
                // Filter berdasarkan TP yang dimiliki user
                $data = $this->outletCallRepository->callTpByUser($userId);
            } else {
                // Default behavior untuk role lainnya
                $data = $this->outletCallRepository->callTp();
            }

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a onclick="showModal(this)" data-tp="' . $row->tp . '" class="btn btn-info">Detail</a>';

                    return $btn;
                })
                ->rawColumns(['action'])
                ->toJson();
        }
    }

    public function datatableCityCall(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->outletCallRepository->callCity($request);
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $url = route('dashboard-outlet-call', $row->city);
                    $btn = '<a href="' . $url . '"  class="btn btn-info">Detail</a>';

                    return $btn;
                })

                ->rawColumns(['action'])
                ->toJson();
        }
    }

    /**
     * Get SBU list from outlet_calls
     */
    private function getSbuList()
    {
        $role = auth()->user()->role->id;

        $sbuData = SbuHelper::getSbuData();

        // Filter kalau role tertentu hanya boleh akses sbu_code sesuai user
        // Admin, Head Telemarketing, dan IT Pusat bisa akses semua SBU
        if (!in_array($role, ['26be63b7-b410-46fc-a2f7-8dc3ed9e29ed', '346d417a-544d-48f3-bb4d-1da4ce54dffc', '9d2fe89a-3ed9-4cc1-9be7-45101f8b4fe8'], true)) {
            $sbuData = $sbuData->where('sbu_code', auth()->user()->sbu_code)->values();
        }

        return $sbuData;
    }

    /**
     * Get TP list by SBU
     */
    private function getTpBySbu($sbuCode)
    {
        return OutletCall::select('tp', 'tp_name')
            ->where('sbu_code', $sbuCode)
            ->where('status', 1)
            ->distinct()
            ->orderBy('tp_name')
            ->get();
    }

    /**
     * Get AJAX TP by SBU
     */
    public function getTpBySbuAjax($sbu)
    {
        $tpList = $this->getTpBySbu($sbu);
        return response()->json([
            'success' => true,
            'data' => $tpList
        ]);
    }

    /**
     * Get TP dashboard data via AJAX
     */
    public function getTpDashboardDataAjax(Request $request)
    {
        $sbuCode = $request->sbu;
        $tpCode = $request->tp;
        $category = $request->category ?? 1;
        $sort = $request->sort ?? 1;

        if (!$sbuCode || !$tpCode) {
            return response()->json([
                'success' => false,
                'message' => 'SBU and TP required'
            ]);
        }

        $tpData = $this->getTpDashboardData($sbuCode, $tpCode, $category, $sort);

        return response()->json([
            'success' => true,
            'data' => $tpData
        ]);
    }

    /**
     * Get dashboard data for TP
     */
    private function getTpDashboardData($sbuCode, $tpCode, $category, $sort)
    {
        try {
            $today = Carbon::now()->translatedFormat('l');

            // Get outlets parameter for the TP
            $outlets = OutletCall::where('sbu_code', $sbuCode)
                ->where('tp', $tpCode)
                ->where('status', 1)
                ->where('day', $today)
                ->pluck('outlet_code')
                ->toArray();

            if (empty($outlets)) {
                return [];
            }

            // Join outlet codes with comma
            $outletParam = implode(',', $outlets);

            // Call stored procedure
            $data = $this->databaseService->callDatabaseFunction(
                'public.sp_target_omset_call',
                [$category, $sort, $outletParam],
                $sbuCode
            );

            $rawData = $data['data'] ?? [];

            if (empty($rawData)) {
                return [];
            }

            // Get TP name
            $tpInfo = OutletCall::where('sbu_code', $sbuCode)
                ->where('tp', $tpCode)
                ->first();

            // Aggregate the data
            $totalTarget = 0;
            $totalOmset = 0;
            $totalSisaPiutang = 0;

            foreach ($rawData as $item) {
                $totalTarget += $item->target ?? 0;
                $totalOmset += $item->omset ?? 0;
                $totalSisaPiutang += $item->sld_piutang ?? 0;
            }

            // Return aggregated data as single item
            return [(object)[
                'tp_code' => $tpCode,
                'tp_name' => $tpInfo->tp_name ?? $tpCode,
                'target' => $totalTarget,
                'omset' => $totalOmset,
                'sld_piutang' => $totalSisaPiutang,
                'outlet_count' => count($rawData)
            ]];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * DataTable for outlet list
     */
    public function datatableOutletList(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::now()->translatedFormat('l');

        // Get outlets based on user role and TP settings
        $query = DB::table('outlet_calls as oc')
            ->select([
                'oc.outlet_code',
                'oc.outlet_name',
                'oc.outlet_phone',
                'oc.residency_name as residency_name',
                'oc.city_name as city_name',
                'oc.district_name as district_name',
                'oc.residency as residency_code',
                'oc.city as city_code',
                'oc.district as district_code'
            ])
            ->where('oc.day', $today)
            ->where('oc.status', 1);


        // Filter by user role and TP settings
        if (in_array($user->role_id, ['a4e960e0-467f-4534-9555-f06ab8b901f7', '2629192e-1c3f-477e-a157-4def565dace3'])) {
            // CS and Telemarketing roles - filter by TP settings
            $userTps = UserTp::where('user_id', $user->id)->pluck('tp')->toArray();
            if (!empty($userTps)) {
                $query->whereIn('oc.tp', $userTps);
            } else {
                // If user has no TP settings, show no results
                $query->where('oc.id', '=', null);
            }
        }

        // Exclude outlets that already have transactions today
        $transactedOutlets = Transaction::whereDate('created_at', Carbon::today())
            ->pluck('outlet_code')
            ->toArray();

        if (!empty($transactedOutlets)) {
            $query->whereNotIn('oc.outlet_code', $transactedOutlets);
        }

        // Exclude outlets that already have non-ordering entries today
        $nonOrderingOutlets = NonOrderingOutlet::whereDate('created_at', Carbon::today())
            ->pluck('outlet_code')
            ->toArray();

        if (!empty($nonOrderingOutlets)) {
            $query->whereNotIn('oc.outlet_code', $nonOrderingOutlets);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('outlet_phone', function ($row) {
                return $row->outlet_phone ?? '-';
            })
            ->addColumn('action', function ($row) {
                $orderBtn = '<button type="button" class="btn btn-success btn-sm me-1" 
                    onclick="orderOutlet(\'' . $row->outlet_code . '\', \'' . $row->residency_code . '\', \'' . $row->city_code . '\', \'' . $row->district_code . '\')" 
                    title="Order">
                    <i class="bx bx-cart"></i> Order
                </button>';

                $noOrderBtn = '<button type="button" class="btn btn-danger btn-sm" 
                    onclick="noOrderOutlet(\'' . $row->outlet_code . '\')" 
                    title="No Order">
                    <i class="bx bx-x"></i> No Order
                </button>';

                return $orderBtn . $noOrderBtn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get non-ordering categories
     */
    public function getNonOrderingCategories()
    {
        try {
            $categories = NonOrderingCategory::select('id', 'name')
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store non-ordering outlet
     */
    public function storeNonOrderingOutlet(Request $request)
    {
        try {
            $request->validate([
                'outlet_code' => 'required|string|max:50',
                'category_id' => 'required|uuid|exists:non_ordering_categories,id',
                'description' => 'required|string'
            ]);

            // Check if outlet already has non-ordering entry today
            $existing = NonOrderingOutlet::where('outlet_code', $request->outlet_code)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet sudah memiliki entry non-ordering hari ini'
                ], 422);
            }

            // Check if outlet already has transaction today
            $hasTransaction = Transaction::where('outlet_code', $request->outlet_code)
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if ($hasTransaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet sudah memiliki transaksi hari ini'
                ], 422);
            }

            NonOrderingOutlet::create([
                'outlet_code' => $request->outlet_code,
                'category_id' => $request->category_id,
                'description' => $request->description,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil disimpan'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi error: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get non-ordering outlets data for chart
     */
    private function getNonOrderingOutletsData()
    {
        try {
            // Get data by category for current month
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;
            $user = auth()->user();

            $query = DB::table('non_ordering_outlets as noo')
                ->leftJoin('non_ordering_categories as noc', 'noo.category_id', '=', 'noc.id')
                ->select(
                    'noc.name as category_name',
                    DB::raw('COUNT(*) as total')
                )
                ->whereNull('noo.deleted_at')
                ->whereYear('noo.created_at', $currentYear)
                ->whereMonth('noo.created_at', $currentMonth);

            // Filter by user role - Telemarketing only sees their own data
            if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
                $query->where('noo.created_by', $user->id);
            }

            $nonOrderingStats = $query->groupBy('noc.id', 'noc.name')
                ->orderBy('total', 'desc')
                ->get();

            // Get total for this month
            $totalThisMonthQuery = NonOrderingOutlet::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->whereNull('deleted_at');

            if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
                $totalThisMonthQuery->where('created_by', $user->id);
            }

            $totalThisMonth = $totalThisMonthQuery->count();

            // Get total for last month
            $lastMonth = Carbon::now()->subMonth();
            $totalLastMonthQuery = NonOrderingOutlet::whereYear('created_at', $lastMonth->year)
                ->whereMonth('created_at', $lastMonth->month)
                ->whereNull('deleted_at');

            if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
                $totalLastMonthQuery->where('created_by', $user->id);
            }

            $totalLastMonth = $totalLastMonthQuery->count();

            // Calculate percentage change
            $percentageChange = 0;
            if ($totalLastMonth > 0) {
                $percentageChange = (($totalThisMonth - $totalLastMonth) / $totalLastMonth) * 100;
            }

            return [
                'categories' => $nonOrderingStats->pluck('category_name')->toArray(),
                'series' => $nonOrderingStats->pluck('total')->toArray(),
                'totalThisMonth' => $totalThisMonth,
                'totalLastMonth' => $totalLastMonth,
                'percentageChange' => round($percentageChange, 1),
                'monthName' => Carbon::now()->translatedFormat('F Y')
            ];
        } catch (\Exception $e) {
            return [
                'categories' => [],
                'series' => [],
                'totalThisMonth' => 0,
                'totalLastMonth' => 0,
                'percentageChange' => 0,
                'monthName' => Carbon::now()->translatedFormat('F Y')
            ];
        }
    }

    /**
     * Outlet Call Dashboard
     */
    public function index(Request $request)
    {
        $title = 'Dashboard';
        $user = auth()->user();

        // Get filter parameters
        $selectedDate = $request->date ?? Carbon::now()->format('Y-m-d');
        $selectedTp = $request->tp ?? null;
        $selectedUser = $request->user_id ?? null;

        // Get day name from selected date
        $dayName = Carbon::parse($selectedDate)->format('l');

        // Get available TPs based on user role
        if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
            $tpList = UserTp::where('user_id', $user->id)
                ->join('outlet_calls as oc', 'user_tp.tp', '=', 'oc.tp')
                ->select('user_tp.tp', 'oc.tp_name')
                ->distinct()
                ->get();
            $userList = []; // Telemarketing users don't see user filter
        } else {
            $tpList = OutletCall::select('tp', 'tp_name')
                ->where('status', 1)
                ->distinct()
                ->orderBy('tp_name')
                ->get();

            // Get telemarketing users
            $userList = DB::table('users')
                ->where('role_id', '2629192e-1c3f-477e-a157-4def565dace3')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        // Get dashboard data
        $dashboardData = $this->getOutletCallDashboardData($selectedDate, $selectedTp, $selectedUser);

        return view('dashboard', compact(
            'title',
            'tpList',
            'userList',
            'selectedDate',
            'selectedTp',
            'selectedUser',
            'dayName',
            'dashboardData'
        ));
    }

    /**
     * Get outlet call dashboard data
     */
    private function getOutletCallDashboardData($date, $tp = null, $userId = null)
    {
        try {
            $dayName = Carbon::parse($date)->translatedFormat('l');

            $user = auth()->user();

            // Base query for outlet calls
            $outletCallQuery = OutletCall::where('day', $dayName)
                ->where('status', 1);

            // Apply TP filter
            if ($tp) {
                $outletCallQuery->where('tp', $tp);
            }


            // Apply user role filtering
            if ($userId) {
                $userTps = UserTp::where('user_id', $userId)->pluck('tp')->toArray();
                if (!empty($userTps)) {
                    $outletCallQuery->whereIn('tp', $userTps);
                }
            }

            $totalOutletCalls = $outletCallQuery->count();
            $outletCodes = $outletCallQuery->pluck('outlet_code')->toArray();

            if (empty($outletCodes)) {
                return [
                    'totalOutletCalls' => 0,
                    'totalTransactions' => 0,
                    'totalNonOrdering' => 0,
                    'remainingOutlets' => 0,
                    'chartData' => [],
                    'tpData' => []
                ];
            }

            // Get transactions for the date
            $transactionQuery = Transaction::whereIn('outlet_code', $outletCodes)
                ->whereDate('created_at', $date);

            if ($userId && $user->role_id !== '2629192e-1c3f-477e-a157-4def565dace3') {
                $transactionQuery->where('user_id', $userId);
            }

            // Count distinct outlet_code only
            $totalTransactions = $transactionQuery->distinct('outlet_code')->count('outlet_code');
            $transactionOutlets = $transactionQuery->distinct()->pluck('outlet_code')->toArray();

            // Get non-ordering outlets for the date
            $nonOrderingQuery = NonOrderingOutlet::whereIn('outlet_code', $outletCodes)
                ->whereDate('created_at', $date);

            if ($userId && $user->role_id !== '2629192e-1c3f-477e-a157-4def565dace3') {
                $nonOrderingQuery->where('created_by', $userId);
            } elseif ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') {
                $nonOrderingQuery->where('created_by', $user->id);
            }

            $totalNonOrdering = $nonOrderingQuery->distinct('outlet_code')->count('outlet_code');

            $nonOrderingOutlets = $nonOrderingQuery->pluck('outlet_code')->toArray();

            // Calculate remaining outlets (not processed)
            $processedOutlets = array_merge($transactionOutlets, $nonOrderingOutlets);
            $remainingOutlets = $totalOutletCalls - $totalTransactions - $totalNonOrdering;

            // Get data by TP for chart
            $tpDataQuery = OutletCall::select(
                'tp',
                'tp_name',
                DB::raw('COUNT(*) as total_outlets')
            )
                ->where('day', $dayName)
                ->where('status', 1);

            if ($tp) {
                $tpDataQuery->where('tp', $tp);
            }

            if ($userId) {
                $userTps = UserTp::where('user_id', $userId)->pluck('tp')->toArray();
                if (!empty($userTps)) {
                    $tpDataQuery->whereIn('tp', $userTps);
                }
            }

            $tpData = $tpDataQuery->groupBy('tp', 'tp_name')
                ->orderBy('tp_name')
                ->get()
                ->map(function ($item) use ($date, $userId, $user) {
                    $tpOutlets = OutletCall::where('tp', $item->tp)
                        ->where('day', Carbon::parse($date)->translatedFormat('l'))
                        ->where('status', 1)
                        ->pluck('outlet_code')
                        ->toArray();



                    $tpTransactionQuery = Transaction::whereIn('outlet_code', $tpOutlets)
                        ->whereDate('created_at', $date);

                    if ($userId && $user->role_id !== '2629192e-1c3f-477e-a157-4def565dace3') {
                        $tpTransactionQuery->where('user_id', $userId);
                    }

                    // Count distinct outlet_code only
                    $tpTransactions = $tpTransactionQuery->distinct('outlet_code')->count('outlet_code');
                    $tpTransactionOutlets = $tpTransactionQuery->distinct()->pluck('outlet_code')->toArray();

                    $tpNonOrderingQuery = NonOrderingOutlet::whereIn('outlet_code', $tpOutlets)
                        ->whereDate('created_at', $date);

                    if ($userId && $user->role_id !== '2629192e-1c3f-477e-a157-4def565dace3') {
                        $tpNonOrderingQuery->where('created_by', $userId);
                    } elseif ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') {
                        $tpNonOrderingQuery->where('created_by', $user->id);
                    }

                    $tpNonOrdering = $tpNonOrderingQuery->distinct('outlet_code')->count('outlet_code');
                    $tpNonOrderingOutlets = $tpNonOrderingQuery->pluck('outlet_code')->toArray();

                    $tpProcessedOutlets = array_merge($tpTransactionOutlets, $tpNonOrderingOutlets);
                    $tpRemaining = $item->total_outlets - $tpTransactions - $tpNonOrdering;

                    return [
                        'tp' => $item->tp,
                        'tp_name' => $item->tp_name,
                        'total_outlets' => $item->total_outlets,
                        'transactions' => $tpTransactions,
                        'non_ordering' => $tpNonOrdering,
                        'remaining' => $tpRemaining
                    ];
                });

            return [
                'totalOutletCalls' => $totalOutletCalls,
                'totalTransactions' => $totalTransactions,
                'totalNonOrdering' => $totalNonOrdering,
                'remainingOutlets' => $remainingOutlets,
                'chartData' => [
                    'labels' => ['Transactions', 'Non Ordering', 'Remaining'],
                    'data' => [$totalTransactions, $totalNonOrdering, $remainingOutlets]
                ],
                'tpData' => $tpData
            ];
        } catch (\Exception $e) {
            return [
                'totalOutletCalls' => 0,
                'totalTransactions' => 0,
                'totalNonOrdering' => 0,
                'remainingOutlets' => 0,
                'chartData' => [],
                'tpData' => []
            ];
        }
    }
}
