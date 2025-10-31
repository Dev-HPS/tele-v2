<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelper;
use App\Helpers\SbuHelper;
use App\Repositories\OutletCallRepository;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\OutletCall;
use Mpdf\Mpdf;
use Exception;


class OutletCallController extends Controller
{
    private OutletCallRepository $repository;
    private DatabaseService $databaseService;


    public function __construct(
        OutletCallRepository $transactionRepository,
        DatabaseService $databaseService
    ) {
        $this->repository = $transactionRepository;
        $this->databaseService = $databaseService;
    }

    public function index()
    {
        $title = 'Outlet Call';
        $tp = $this->databaseService->callDatabaseFunction('public.sp_master_tp');
        $tp = $tp['data'];


        return view('outlet-call.index', compact([
            'title',
            'tp'
        ]));
    }

    public function create()
    {
        $title = 'Tambah Data';

        $day = CustomHelper::getHariSeninSampaiSabtu();

        return view('outlet-call.create', compact([
            'title',
            'day'
        ]));
    }

    public function store(Request $request)
    {
        try {
            $this->repository->store($request);

            $result = [
                'success' => true
            ];

            return response()->json($result);
        } catch (Exception $e) {
            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];

            return response()->json($result);
        }
    }

    public function edit($id)
    {
        try {
            $title = 'Edit Data';

            return view('outlet-call.edit', compact([
                'title',

            ]));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function destroy($id, Request $request)
    {
        $request->merge(['id' => $id]);
        $this->repository->destroy($request);

        $result = [
            'success' => true
        ];

        return response()->json($result);
    }

    public function getTpOptions()
    {
        try {
            $role = auth()->user()->role->id;

            $tpData = OutletCall::select('tp', 'tp_name')
                ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                    $query->where('sbu_code', auth()->user()->sbu_code);
                })
                ->where('status', 1)
                ->distinct()
                ->orderBy('tp_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tpData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getSbuOptions()
    {
        try {
            $role = auth()->user()->role->id;

            $sbuData = SbuHelper::getSbuData();

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

    public function getTpBySbu(Request $request)
    {
        try {
            $role = auth()->user()->role->id;
            $sbuCode = $request->sbu_code;

            $tpData = OutletCall::select('tp', 'tp_name')
                ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                    $query->where('sbu_code', auth()->user()->sbu_code);
                })
                ->when($sbuCode && $sbuCode != 'All', function ($query) use ($sbuCode) {
                    $query->where('sbu_code', $sbuCode);
                })
                ->where('status', 1)
                ->distinct()
                ->orderBy('tp_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tpData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCitiesByTp(Request $request)
    {
        try {
            $role = auth()->user()->role->id;
            $tp = $request->tp;
            $sbuCode = $request->sbu_code;

            $cityData = OutletCall::select('city', 'city_name')
                ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                    $query->where('sbu_code', auth()->user()->sbu_code);
                })
                ->when($sbuCode && $sbuCode != 'All', function ($query) use ($sbuCode) {
                    $query->where('sbu_code', $sbuCode);
                })
                ->when($tp && $tp != 'All', function ($query) use ($tp) {
                    $query->where('tp', $tp);
                })
                ->where('status', 1)
                ->whereNotNull('city')
                ->distinct()
                ->orderBy('city_name')
                ->get();


            return response()->json([
                'success' => true,
                'data' => $cityData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getDistrictsByCity(Request $request)
    {
        try {
            $role = auth()->user()->role->id;
            $tp = $request->tp;
            $city = $request->city;
            $sbuCode = $request->sbu_code;

            $districtData = OutletCall::select('district', 'district_name')
                ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                    $query->where('sbu_code', auth()->user()->sbu_code);
                })
                ->when($sbuCode && $sbuCode != 'All', function ($query) use ($sbuCode) {
                    $query->where('sbu_code', $sbuCode);
                })
                ->when($tp && $tp != 'All', function ($query) use ($tp) {
                    $query->where('tp', $tp);
                })
                ->when($city && $city != 'All', function ($query) use ($city) {
                    $query->where('city', $city);
                })
                ->where('status', 1)
                ->whereNotNull('district')
                ->distinct()
                ->orderBy('district_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $districtData
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function exportPdf(Request $request)
    {

        $role = auth()->user()->role->id;

        $query = OutletCall::select('outlet_calls.*')
            ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($q) {
                $q->where('sbu_code', auth()->user()->sbu_code);
            })
            ->where('status', 1);

        // Apply filters
        if ($request->sbu_filter && $request->sbu_filter != 'All') {
            $query->where('sbu_code', $request->sbu_filter);
        }

        if ($request->tp_filter && $request->tp_filter != 'All') {
            $query->where('tp', $request->tp_filter);
        }

        if ($request->city_filter && $request->city_filter != 'All') {
            $query->where('city', $request->city_filter);
        }

        if ($request->district_filter && $request->district_filter != 'All') {
            $query->where('district', $request->district_filter);
        }

        if ($request->day_filter && $request->day_filter != 'All') {

            $query->where('day', $request->day_filter);
        }

        $data = $query->orderBy('sbu_code')->orderBy('tp_name')->orderBy('outlet_name')->get();

        // Prepare filter info for PDF
        $filters = [
            'SBU' => $request->sbu_filter && $request->sbu_filter != 'All' ? $request->sbu_filter : 'Semua',
            'TP' => $request->tp_filter && $request->tp_filter != 'All' ? $request->tp_filter : 'Semua',
            'Kabupaten' => $request->city_filter && $request->city_filter != 'All' ? $request->city_filter : 'Semua',
            'Kecamatan' => $request->district_filter && $request->district_filter != 'All' ? $request->district_filter : 'Semua',
            'Hari' => $request->day_filter && $request->day_filter != 'All' ? $request->day_filter : 'Semua'
        ];


        // (opsional) long HTML safety
        ini_set('pcre.backtrack_limit', '5000000');
        ini_set('pcre.recursion_limit', '5000000');
        ini_set('memory_limit', '512M'); // kalau perlu

        // Generate HTML from blade template
        $html = view('outlet-call.pdf', compact('data', 'filters'))->render();



        // Create mPDF instance
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P', // Portrait orientation
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'margin_header' => 9,
            'margin_footer' => 9,
            'tempDir' => storage_path('app/mpdf-temp')
        ]);

        $mpdf->WriteHTML($html);


        // Set filename
        $filename = 'outlet-call-' . date('Y-m-d-H-i-s') . '.pdf';

        // Output PDF for download
        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }


    public function tp(Request $request)
    {
        $sbuCode = $request->sbu_code;

        return $this->databaseService->callDatabaseFunction('public.sp_master_tp', [], $sbuCode);
    }


    public function cities($tp)
    {
        $sbuCode = request()->get('sbu_code');
        return  $this->databaseService->callDatabaseFunction('public.sp_kabupaten_tp', [$tp], $sbuCode);
    }

    public function districts($tp, $city)
    {
        $sbuCode = request()->get('sbu_code');
        return  $this->databaseService->callDatabaseFunction('public.sp_kecamatan_tp', [$tp, $city], $sbuCode);
    }

    public function outlet($tp, $city, $district)
    {
        $sbuCode = request()->get('sbu_code');
        return $this->databaseService->callDatabaseFunction('public.sp_pelanggan_aktif_jual', [$tp, $city, $district], $sbuCode);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->findAll($request);
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {

                    if ($row->status == '0') {
                        return '<span class="badge bg-warning">Pending</span>';
                    } elseif ($row->status == '1') {
                        return '<span class="badge bg-success">Approved</span>';
                    }
                })
                ->addColumn('action', function ($row) {
                    $piutangBtn = '<button onclick="showPiutangModal(\'' . $row->outlet_code . '\', \'' . addslashes($row->outlet_name) . '\')" class="btn btn-info btn-sm me-1" title="Detail Piutang"><i class="bx bx-money"></i></button>';
                    $syncBtn = '<button onclick="syncOutletData(\'' . $row->outlet_code . '\', \'' . addslashes($row->outlet_name) . '\')" class="btn btn-success btn-sm me-1" title="Sync Data"><i class="bx bx-sync"></i></button>';
                    $deleteBtn = '<a onclick="deleteModal(this)" data-id="' . $row->id . '" class="btn btn-danger btn-sm" title="Hapus"><i class="bx bx-trash"></i></a>';
                    return $piutangBtn . $syncBtn . $deleteBtn;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    }

    public function getPiutang(Request $request)
    {
        try {
            $outletCode = $request->outlet_code;

            if (!$outletCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet code is required'
                ]);
            }

            // Get SBU code for the outlet
            $outlet = OutletCall::where('outlet_code', $outletCode)->first();

            if (!$outlet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet not found'
                ]);
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function syncOutlet(Request $request)
    {
        try {
            $outletCode = $request->outlet_code;

            if (!$outletCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet code is required'
                ]);
            }

            // Get SBU code for the outlet
            $outlet = OutletCall::where('outlet_code', $outletCode)->first();

            if (!$outlet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Outlet not found'
                ]);
            }

            // Call stored procedure sp_detail_outlet with outlet_code
            $result = $this->databaseService->callDatabaseFunction(
                'public.sp_detail_outlet',
                [$outletCode],
                $outlet->sbu_code
            );

            if (empty($result['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data outlet code ' . $outletCode . ' gagal di sync.',
                ]);
            }

            $data = $result['data'][0];


            OutletCall::where('outlet_code', $outletCode)->update([
                // 'outlet_name' => $data->outlet_name,
                // 'outlet_owner' => $data->outlet_pkp,
                'outlet_phone' => $data->no_telp,
                'outlet_address' => $data->outlet_alamat,
            ]);


            return response()->json([
                'success' => true,
                'message' => 'Data outlet berhasil disinkronkan',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
