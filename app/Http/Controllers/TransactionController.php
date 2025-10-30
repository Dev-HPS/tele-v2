<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use CustomHelper;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Http;
use App\Repositories\StatusRepository;
use Illuminate\Support\Facades\Validator;
use App\Repositories\TransactionRepository;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    private TransactionRepository $repository;
    private StatusRepository $statusRepository;

    public function __construct(TransactionRepository $transactionRepository, StatusRepository $statusRepository)
    {
        $this->repository = $transactionRepository;
        $this->statusRepository = $statusRepository;
    }

    public function index()
    {
        $residency = self::residency();
        $title = 'Transaksi';
        $url = route('transactions.datatable');
        $status = $this->statusRepository->findAll('5d886962-1910-46b1-9626-139961e51d78')->get();

        return view('transactions.transaction', compact([
            'title',
            'url',
            'residency',
            'status'
        ]));
    }

    public function store(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'residency' => 'required|string',
        //     'city' => 'required|string',
        //     'district' => 'required|string',
        //     'outlet_code' => 'required|string',
        //     'order_date' => 'required|date',
        //     'delivery_date' => 'required|date',
        //     'vehicle_plate' => 'nullable|string',
        //     'description' => 'nullable|string',
        // ]);

        // if($validator->fails()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => $validator->errors()
        //     ]);
        // }

        // $req = $request->all();

        // for($i = 0; $i < count($request->item_code); $i++) {
        //     $result[$i]['product_code'] = $req['item_code'][$i];
        //     $result[$i]['product_name'] = $req['item_name'][$i];
        //     $result[$i]['qty'] = $req['qty'][$i];
        // }

        // $validated = $validator->validated();

        // $validated['detail'] = $result;

        // $outlet = self::outletById($validated['residency'], $validated['city'], $validated['district'], $validated['outlet_code']);

        // $validated['outlet_name'] = $outlet->outlet_name;
        // $validated['outlet_address'] = $outlet->outlet_alamat;
        // $validated['outlet_owner'] = $outlet->outlet_pemilik;
        // $validated['outlet_phone'] = $outlet->no_telp;
        // $validated['outlet_longitude'] = $outlet->outlet_longitude;
        // $validated['outlet_latitude'] = $outlet->outlet_latitude;
        // $validated['residency_name'] = $outlet->nama_karesidenan;
        // $validated['city_name'] = $outlet->nama_kabupaten;
        // $validated['district_name'] = $outlet->nama_kecamatan;
        // $validated['type_id'] = '5d886962-1910-46b1-9626-139961e51d78';

        // $result = $this->repository->create($validated);

        // return response()->json($result);
    }

    public function create(Request $request)
    {
        $title = 'Tambah Transaksi';
        $residency = self::residency();
        $residency = collect($residency);
        $userDetails = auth()->user()->userDetails;
        $userDetails = $userDetails->pluck('residency');
        $residency = $residency->whereIn('kode_kar', $userDetails->toArray());

        // Pre-population data from dashboard
        $prePopulate = [
            'outlet_code' => $request->get('outlet_code'),
            'residency' => $request->get('residency'),
            'city' => $request->get('city'),
            'district' => $request->get('district')
        ];

        return view('transactions.create', compact([
            'title',
            'residency',
            'prePopulate'
        ]));
    }

    public function show($id)
    {
        $data = $this->repository->findById($id);

        if (is_null($data)) {
            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function edit($transaction)
    {
        try {
            $title = 'Edit Transaksi';
            $data = $this->repository->findById($transaction);
            $warehouse = self::warehouse();
            $supplier = self::supplier();

            return view('transactions.edit', compact([
                'title',
                'data',
                'warehouse',
                'supplier',
                'transaction'
            ]));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function update($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'residency' => 'required|string',
                'city' => 'required|string',
                'district' => 'required|string',
                'outlet_code' => 'required|string',
                'order_date' => 'required|date',
                'delivery_date' => 'required|date',
                'vehicle_plate' => 'nullable|string',
                'description' => 'nullable|string',
                'additional_description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }
            $validated = $validator->validated();

            $outlet = self::outletById($validated['residency'], $validated['city'], $validated['district'], $validated['outlet_code']);

            $validated['outlet_name'] = $outlet->outlet_name;
            $validated['outlet_address'] = $outlet->outlet_alamat;
            $validated['outlet_owner'] = $outlet->outlet_pemilik;
            $validated['outlet_phone'] = $outlet->no_telp;
            $validated['outlet_longitude'] = $outlet->outlet_longitude;
            $validated['outlet_latitude'] = $outlet->outlet_latitude;
            $validated['residency_name'] = $outlet->nama_karesidenan;
            $validated['city_name'] = $outlet->nama_kabupaten;
            $validated['district_name'] = $outlet->nama_kecamatan;

            $update = $this->repository->update($validated, $id);

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function updateDetail($transaction, $detail, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|string',
            'product_name' => 'required|string',
            'product_code' => 'required|string',
            'product_unit' => 'required|string',
            'qty' => 'required|numeric',
            //            'warehouse' => 'required|string',
            //            'supplier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }
        $validated = $validator->validated();
        $validated['detail'] = true;
        $validated['id'] = $detail;
        $this->repository->update($validated, $transaction);

        $validated['detail'] = false;

        $data = $this->repository->findById($transaction);
        $transactionData = [];
        $transactionData['description'] = '';
        foreach ($data->details as $item) {
            $transactionData['description'] .= ' ' . $item["qty"];
            $transactionData['description'] .= ' ' . $item["unit"];
            $transactionData['description'] .= ' ' . $item["product_name"] . ',';
        }
        $transactionData['description'] = ltrim($transactionData['description']);
        $this->repository->update($transactionData, $transaction);

        return response()->json([
            'success' => true
        ]);
    }

    public function storeDetail($transaction, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'required|string',
            'product_name' => 'required|string',
            'product_code' => 'required|string',
            'product_unit' => 'required|string',
            'qty' => 'required|numeric',
        ]);

        $validated = $validator->validated();

        $this->repository->createDetail($transaction, $validated);

        return response()->json([
            'success' => true
        ]);
    }

    public function updateDeliveryDate($transaction, Request $request)
    {
        $this->repository->update([
            'delivery_date' => $request->delivery_date
        ], $transaction);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy($id)
    {
        $this->repository->destroy($id);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroyDetail($transaction, $detail)
    {
        $this->repository->destroyDetail($transaction, $detail);

        return response()->json([
            'success' => true
        ]);
    }

    public function cancel($transaction, Request $request)
    {
        $this->repository->update([
            'status' => 99,
            'additional_description' => $request->input('description')
        ], $transaction);

        return response()->json([
            'success' => true
        ]);
    }

    public function closeOrder($transaction, Request $request)
    {
        try {

            DB::beginTransaction();

            $transactionData = Transaction::where('id', $transaction)->with(['details'])->first();

            if ($transactionData->order_id != 0) {
                $orderProduct = [];

                foreach ($transactionData->details as $item) {
                    $orderProduct[] = [
                        'product_id' => $item->ordet_id,
                        'return_qty' => 0,
                        'return_text' => ''
                    ];
                }

                $data = [
                    "order_id" => $transactionData->order_id,
                    "order_product" => $orderProduct,
                    "order_text" => ""
                ];

                Http::dsoapi()->withOptions([
                    'verify' => false
                ])->withBody(json_encode($data), 'application/json')->post('v1/internal/order/complete');
            }

            $this->repository->update(['status' => 6], $transaction);

            DB::commit();

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateVehicle($vehicle, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'vehicle_plate' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()
                ]);
            }

            $validated = $validator->validated();
            $validated['status'] = 2;

            $update = $this->repository->updateVehicle($validated, $vehicle);

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function validateTransaction($transaction)
    {
        try {
            $data = [];
            if (auth()->user()->role->id == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e') {
                $data = ['status' => 3];
            }

            if (auth()->user()->role->id == '2629192e-1c3f-477e-a157-4def565dace3') {
                $data = ['status' => 4];
            }

            $this->repository->validateTransaction($data, $transaction);

            return response()->json([
                'success' => true
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    private function residency()
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'karesidenan/' . auth()->user()->sbu_code);

            if ($response->successful()) {
                $response = $response->object();
                return $response->data;
            } else {
                return [];
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function cities($residency)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'kabupaten/' . auth()->user()->sbu_code . '/' . $residency);

            if ($response->successful()) {
                $response = $response->object();
                return response()->json([
                    'success' => true,
                    'data' => $response->data
                ]);
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function districts($residency, $city)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'kecamatan/' . auth()->user()->sbu_code . '/' . $residency . '/' . $city);

            if ($response->successful()) {
                $response = $response->object();
                return response()->json([
                    'success' => true,
                    'data' => $response->data
                ]);
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function outlet($residency, $city, $district)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'outlet/' . auth()->user()->sbu_code . '/' . $residency . '/' . $city . '/' . $district);

            if ($response->successful()) {
                $response = $response->object();
                return response()->json([
                    'success' => true,
                    'data' => $response->data
                ]);
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function outletById($residency, $city, $district, $outlet)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'outlet/' . auth()->user()->sbu_code . '/' . $residency . '/' . $city . '/' . $district . '?outlet_code=' . $outlet);

            if ($response->successful()) {
                $response = $response->object();

                // if($local) {
                //     return response()->json([
                //         'success' => true,
                //         'data' => $response->data[0]
                //     ]);
                // }
                return $response->data[0];
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function productByOutlet($outlet)
    {
        $apiURL = env('API_URL');

        $response = Http::withHeaders([
            'dso-acces-token' => '436be6ca3e99ae8612605e00a849162888211ec151b1e6c390855dbd55f6313e'
        ])->withOptions(["verify" => false])->get($apiURL . 'master/barang?sbu_code=' . auth()->user()->sbu_code . '&kd_outlet=' . $outlet);

        if ($response->successful()) {
            $response = $response->object();
            return response()->json([
                'success' => true,
                'data' => $response->DATA
            ]);
        }
    }

    public function productTypeBySBU()
    {
        $apiURL = env('API_URL_DMLT');

        $response = Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
        ])->withOptions(["verify" => false])->get($apiURL . 'jenis/barang/' . auth()->user()->sbu_code);

        if ($response->successful()) {
            $response = $response->object();

            return response()->json([
                'success' => true,
                'data' => $response->data
            ]);
        }
    }

    public function productsByType($type)
    {
        $apiURL = env('API_URL_DMLT');

        $response = Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
        ])->withOptions(["verify" => false])->get($apiURL . 'all/barang/' . auth()->user()->sbu_code . '/' . $type);

        if ($response->successful()) {
            $response = $response->object();

            return response()->json([
                'success' => true,
                'data' => $response->data
            ]);
        }
    }

    public function vehicle()
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'kendaraan/' . auth()->user()->sbu_code);

            if ($response->successful()) {
                $response = $response->object();
                return $response;
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    private function warehouse()
    {
        $apiURL = env('API_URL_DMLT');

        $response = Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
        ])->withOptions(["verify" => false])->get($apiURL . 'gudang/' . auth()->user()->sbu_code);

        if ($response->successful()) {
            $response = $response->object();
            return $response->data;
        }

        return [];
    }

    private function supplier()
    {
        $apiURL = env('API_URL_DMLT');

        $response = Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
        ])->withOptions(["verify" => false])->get($apiURL . 'supplier/' . auth()->user()->sbu_code);

        if ($response->successful()) {
            $response = $response->object();
            return $response->data;
        }

        return [];
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->findAll('SR');
            return Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->get('statusCode')) {
                        $status = $request->get('statusCode');
                        if ($status == 'All') {
                            $query->whereIn('status', ['1', '2', '3', '4', '5', '6', '98', '99']);
                        } else {
                            $query->where('status', $status);
                        }
                    }
                    if ($request->get('residency')) {
                        $residency = $request->get('residency');
                        if ($residency != 'All') {
                            $query->where('residency', $residency);
                        }
                    }
                    if ($request->get('city')) {
                        $city = $request->get('city');
                        if ($city != 'All') {
                            $query->where('city', $city);
                        }
                    }
                    if ($request->get('district')) {
                        $district = $request->get('district');
                        if ($district != 'All') {
                            $query->where('district', $district);
                        }
                    }
                    if (!empty($request->get('orderDate'))) {
                        $orderDate = $request->get('orderDate');
                        $query->where('order_date', $orderDate);
                    }
                    if (!empty($request->get('deliveryDate'))) {
                        $deliveryDate = $request->get('deliveryDate');
                        $query->where('delivery_date', $deliveryDate);
                    }
                    if (!empty($request->get('search'))) {
                        $query->where(function ($w) use ($request) {
                            $search = $request->get('search');
                            $w->whereRaw('LOWER(outlet_name) LIKE ?', ["%" . strtolower($search) . "%"])
                                ->orWhereRaw('LOWER(ticket_number) LIKE ?', ["%" . strtolower($search) . "%"]);
                        });
                    }
                })
                ->addColumn('detail', function ($row) {
                    $detailBtn = '<a onclick="detail(this)" class="btn btn-primary" data-id="' . $row->id . '">Detail</a>';
                    return $detailBtn;
                })
                ->addColumn('action', function ($row) {
                    $access = ['2629192e-1c3f-477e-a157-4def565dace3', '346d417a-544d-48f3-bb4d-1da4ce54dffc', 'f4367c27-700b-4e34-89d4-75822e97f76c'];

                    if (in_array(auth()->user()->role->id, $access)) {
                        $cancel = '<a onclick="cancelModal(this)" data-id="' . $row->id . '" class="btn btn-danger">Cancel</a>';

                        if ($row->status == 5) {
                            $closeBtn = '<a onclick="closeOrder(this)" data-id="' . $row->id . '" class="btn btn-warning">Close Order</a>';

                            return $closeBtn . ' ' . $cancel;
                        }

                        if ($row->status <= 2) {
                            $url = route('transactions.edit', $row->id);
                            $btn = '<a href="' . $url . '" data-id="' . $row->id . '" class="btn btn-warning">Edit</a>';
                            return $btn . ' ' . $cancel;
                        }
                    }
                })
                ->addColumn('order_txt', function ($row) {
                    return CustomHelper::parseDate($row->order_date);
                })
                ->addColumn('delivery_txt', function ($row) {
                    return CustomHelper::parseDate($row->delivery_date);
                })
                ->addColumn('status_txt', function ($row) {
                    return $row->status_name;
                })
                ->addColumn('created_txt', function ($row) {
                    return CustomHelper::parseDate($row->created_at, true);
                })
                ->rawColumns(['action', 'detail', 'status_txt', 'created_txt'])
                ->make(true);
            // ->toJson();
        }
    }
}
