<?php

namespace App\Http\Controllers;

use App\Models\NonOrderingOutlet;
use App\Models\NonOrderingCategory;
use App\Models\OutletCall;
use App\Models\UserTp;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;

class NonOrderingOutletController extends Controller
{
    /**
     * Display a listing of the non-ordering outlets.
     */
    public function index(Request $request)
    {
        $title = 'Non Ordering Outlets';
        $categories = NonOrderingCategory::select('id', 'name')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('non-ordering-outlets.index', compact('title', 'categories'));
    }

    /**
     * Get SBU options for filter
     */
    public function getSbuOptions()
    {
        try {
            $user = auth()->user();

            // Get distinct SBU from outlet_calls
            $sbuList = OutletCall::select('sbu_code', 'sbu_name')
                ->whereNull('deleted_at')
                ->distinct()
                ->orderBy('sbu_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sbuList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading SBU options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get TP options by SBU
     */
    public function getTpBySbu(Request $request)
    {
        try {
            $sbuCode = $request->get('sbu_code');
            $user = auth()->user();

            $query = OutletCall::select('tp', 'tp_name')
                ->where('sbu_code', $sbuCode)
                ->whereNull('deleted_at')
                ->distinct();

            // Filter by user role - Telemarketing only sees their assigned TPs
            if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
                $userTps = UserTp::where('user_id', $user->id)->pluck('tp')->toArray();
                if (!empty($userTps)) {
                    $query->whereIn('tp', $userTps);
                }
            }

            $tpList = $query->orderBy('tp_name')->get();

            return response()->json([
                'success' => true,
                'data' => $tpList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading TP options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cities by TP
     */
    public function getCitiesByTp(Request $request)
    {
        try {
            $tp = $request->get('tp');
            $sbuCode = $request->get('sbu_code');

            $cities = OutletCall::select('city', 'city_name')
                ->where('sbu_code', $sbuCode)
                ->where('tp', $tp)
                ->whereNull('deleted_at')
                ->distinct()
                ->orderBy('city_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading city options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get districts by city
     */
    public function getDistrictsByCity(Request $request)
    {
        try {
            $city = $request->get('city');
            $tp = $request->get('tp');
            $sbuCode = $request->get('sbu_code');

            $districts = OutletCall::select('district', 'district_name')
                ->where('sbu_code', $sbuCode)
                ->where('tp', $tp)
                ->where('city', $city)
                ->whereNull('deleted_at')
                ->distinct()
                ->orderBy('district_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $districts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading district options: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DataTable for non-ordering outlets
     */
    public function datatable(Request $request)
    {
        $query = DB::table('non_ordering_outlets as noo')
            ->leftJoin('non_ordering_categories as noc', 'noo.category_id', '=', 'noc.id')
            ->leftJoin('users as u', 'noo.created_by', '=', 'u.id')
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (outlet_code) * 
              FROM outlet_calls 
              ORDER BY outlet_code, created_at DESC) as oc'),
                'noo.outlet_code',
                '=',
                'oc.outlet_code'
            )

            ->select([
                'noo.id',
                'noo.outlet_code',
                'oc.outlet_name',
                'oc.district_name as district_name',
                'oc.city_name as city_name',
                'oc.residency_name as residency_name',
                'noc.name as category_name',
                'noo.description',
                'u.name as created_by_name',
                'noo.created_at'
            ])
            ->where('oc.status', 1)
            ->whereNull('noo.deleted_at');


        // Apply filters
        if ($request->filled('date_filter')) {
            $this->applyDateFilter($query, $request->date_filter, $request->start_date, $request->end_date);
        }

        if ($request->filled('category_id')) {
            $query->where('noo.category_id', $request->category_id);
        }

        // Apply SBU filter
        if ($request->filled('sbu_filter') && $request->sbu_filter !== 'All') {
            $query->where('oc.sbu_code', $request->sbu_filter);
        }

        // Apply TP filter
        if ($request->filled('tp_filter') && $request->tp_filter !== 'All') {
            $query->where('oc.tp', $request->tp_filter);
        }

        // Apply City filter
        if ($request->filled('city_filter') && $request->city_filter !== 'All') {
            $query->where('oc.city', $request->city_filter);
        }

        // Apply District filter
        if ($request->filled('district_filter') && $request->district_filter !== 'All') {
            $query->where('oc.district', $request->district_filter);
        }

        // Filter by user role - Telemarketing only sees their own data
        $user = auth()->user();
        if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
            $query->where('noo.created_by', $user->id);

            // Also filter by assigned TPs
            $userTps = UserTp::where('user_id', $user->id)->pluck('tp')->toArray();
            if (!empty($userTps)) {
                $query->whereIn('oc.tp', $userTps);
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('outlet_name', function ($row) {
                return $row->outlet_name ?? $row->outlet_code;
            })
            ->editColumn('district_name', function ($row) {
                return $row->district_name ?? '-';
            })
            ->editColumn('city_name', function ($row) {
                return $row->city_name ?? '-';
            })
            ->editColumn('residency_name', function ($row) {
                return $row->residency_name ?? '-';
            })
            ->editColumn('category_name', function ($row) {
                return $row->category_name ?? '-';
            })
            ->editColumn('created_by_name', function ($row) {
                return $row->created_by_name ?? '-';
            })
            ->editColumn('created_at', function ($row) {
                return Carbon::parse($row->created_at)->format('d/m/Y H:i:s');
            })
            ->filterColumn('outlet_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(oc.outlet_name as TEXT)) LIKE ? OR LOWER(CAST(noo.outlet_code as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%", "%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('outlet_code', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(noo.outlet_code as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('district_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(oc.district_name as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('city_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(oc.city_name as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('residency_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(oc.residency_name as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('category_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(noc.name as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('description', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(noo.description as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->filterColumn('created_by_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(CAST(u.name as TEXT)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
            })
            ->rawColumns(['outlet_name'])
            ->make(true);
    }

    /**
     * Export to PDF
     */
    public function exportPdf(Request $request)
    {
        $query = DB::table('non_ordering_outlets as noo')
            ->leftJoin('non_ordering_categories as noc', 'noo.category_id', '=', 'noc.id')
            ->leftJoin('users as u', 'noo.created_by', '=', 'u.id')
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (outlet_code) * 
              FROM outlet_calls 
              ORDER BY outlet_code, created_at DESC) as oc'),
                'noo.outlet_code',
                '=',
                'oc.outlet_code'
            )->select([
                'noo.id',
                'noo.outlet_code',
                'oc.outlet_name',
                'oc.district_name as district_name',
                'oc.city_name as city_name',
                'oc.residency_name as residency_name',
                'noc.name as category_name',
                'noo.description',
                'u.name as created_by_name',
                'noo.created_at'
            ])
            ->whereNull('noo.deleted_at');

        // Apply filters
        if ($request->filled('date_filter')) {
            $this->applyDateFilter($query, $request->date_filter, $request->start_date, $request->end_date);
        }

        if ($request->filled('category_id')) {
            $query->where('noo.category_id', $request->category_id);
        }

        // Apply SBU filter
        if ($request->filled('sbu_filter') && $request->sbu_filter !== 'All') {
            $query->where('oc.sbu_code', $request->sbu_filter);
        }

        // Apply TP filter
        if ($request->filled('tp_filter') && $request->tp_filter !== 'All') {
            $query->where('oc.tp', $request->tp_filter);
        }

        // Apply City filter
        if ($request->filled('city_filter') && $request->city_filter !== 'All') {
            $query->where('oc.city', $request->city_filter);
        }

        // Apply District filter
        if ($request->filled('district_filter') && $request->district_filter !== 'All') {
            $query->where('oc.district', $request->district_filter);
        }

        // Filter by user role - Telemarketing only sees their own data
        $user = auth()->user();
        if ($user->role_id === '2629192e-1c3f-477e-a157-4def565dace3') { // Telemarketing role
            $query->where('noo.created_by', $user->id);

            // Also filter by assigned TPs
            $userTps = UserTp::where('user_id', $user->id)->pluck('tp')->toArray();
            if (!empty($userTps)) {
                $query->whereIn('oc.tp', $userTps);
            }
        }

        $data = $query->orderBy('noo.created_at', 'desc')->get();

        $filterInfo = $this->getFilterInfo($request);

        // Generate HTML from view
        $html = view('non-ordering-outlets.pdf', compact('data', 'filterInfo'))->render();

        // Create PDF using mPDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => storage_path('app/mpdf-temp')
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('non-ordering-outlets-' . date('Y-m-d') . '.pdf', 'I'); // 'D' for download
    }

    /**
     * Apply date filter to query
     */
    private function applyDateFilter($query, $filter, $startDate = null, $endDate = null)
    {
        $now = Carbon::now();

        switch ($filter) {
            case 'today':
                $query->whereDate('noo.created_at', $now->toDateString());
                break;
            case 'yesterday':
                $query->whereDate('noo.created_at', $now->subDay()->toDateString());
                break;
            case 'this_week':
                $query->whereBetween('noo.created_at', [
                    $now->startOfWeek()->toDateString(),
                    $now->endOfWeek()->toDateString()
                ]);
                break;
            case 'last_week':
                $startOfLastWeek = $now->subWeek()->startOfWeek();
                $endOfLastWeek = $now->endOfWeek();
                $query->whereBetween('noo.created_at', [
                    $startOfLastWeek->toDateString(),
                    $endOfLastWeek->toDateString()
                ]);
                break;
            case 'this_month':
                $query->whereYear('noo.created_at', $now->year)
                    ->whereMonth('noo.created_at', $now->month);
                break;
            case 'last_month':
                $lastMonth = $now->subMonth();
                $query->whereYear('noo.created_at', $lastMonth->year)
                    ->whereMonth('noo.created_at', $lastMonth->month);
                break;
            case 'date_range':
                if ($startDate && $endDate) {
                    $query->whereBetween('noo.created_at', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }
                break;
        }
    }

    /**
     * Get filter information for PDF
     */
    private function getFilterInfo(Request $request)
    {
        $info = [];

        if ($request->filled('date_filter')) {
            $filterNames = [
                'today' => 'Hari Ini',
                'yesterday' => 'Kemarin',
                'this_week' => 'Minggu Ini',
                'last_week' => 'Minggu Lalu',
                'this_month' => 'Bulan Ini',
                'last_month' => 'Bulan Lalu',
                'date_range' => 'Range Tanggal'
            ];

            $info['date_filter'] = $filterNames[$request->date_filter] ?? $request->date_filter;

            if ($request->date_filter === 'date_range' && $request->filled(['start_date', 'end_date'])) {
                $info['date_range'] = Carbon::parse($request->start_date)->format('d/m/Y') . ' - ' .
                    Carbon::parse($request->end_date)->format('d/m/Y');
            }
        }

        if ($request->filled('category_id')) {
            $category = NonOrderingCategory::find($request->category_id);
            $info['category'] = $category ? $category->name : 'Tidak diketahui';
        }

        return $info;
    }
}
