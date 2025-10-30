<?php

namespace App\Http\Controllers;

use App\Models\BypassOutlet;
use Illuminate\Http\Request;
use App\Repositories\CartRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Str;
use App\Models\OutletCall;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\ConnectException;


class CartController extends Controller
{
    private CartRepository $repository;
    private TransactionRepository $transactionRepository;

    public function __construct(CartRepository $cartRepository, TransactionRepository $transactionRepository)
    {
        $this->repository = $cartRepository;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->repository->groupByOutlet();
    }

    /**
     * Check outlet validation status for cart
     */
    public function checkOutletValidation(Request $request)
    {
        $outletCode = $request->input('outlet_code');

        if (!$outletCode) {
            return response()->json([
                'success' => false,
                'message' => 'Outlet code required'
            ], 400);
        }

        $validation = $this->validateOutletCall($outletCode);

        return response()->json([
            'success' => true,
            'validation' => $validation
        ]);
    }


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        try {
            // Validasi outlet call sebelum menyimpan ke cart
            $outletValidation = $this->validateOutletCall($request->outlet_code);
            if (!$outletValidation['status']) {
                return response()->json([
                    'success' => false,
                    'message' => $outletValidation['message']
                ], 422);
            }

            $req = $request->all();
            for ($i = 0; $i < count($request->item_code); $i++) {
                if ($req['qty'][$i] != null || $req['qty'][$i] != 0) {
                    $data[$i]['user_id'] = auth()->user()->id;
                    $data[$i]['outlet_code'] = $req['outlet_code'];
                    $data[$i]['outlet_name'] = $req['outlet_name'];
                    $data[$i]['outlet_address'] = $req['outlet_address'];
                    $data[$i]['product_code'] = $req['item_code'][$i];
                    $data[$i]['product_name'] = $req['item_name'][$i];
                    $data[$i]['product_picture'] = $req['item_picture'][$i];
                    $data[$i]['qty'] = $req['qty'][$i];
                    $data[$i]['unit'] = $req['item_unit'][$i];
                    $data[$i]['product_type'] = $req['product_type'];
                }
            }

            $create = $this->repository->create($data);

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

    public function show($id)
    {
        //
    }

    public function preview($outlet)
    {
        $title = 'Cart';
        $cart = $this->repository->findByOutlet($outlet);

        if ($cart->count() == 0) {
            return redirect()->route('transactions.create');
        }

        $warehouse = self::warehouse();
        $supplier = self::supplier();

        return view('cart.preview', compact([
            'outlet',
            'title',
            'cart',
            'warehouse',
            'supplier'
        ]));
    }

    public function update(Request $request, $cart)
    {
        $this->repository->update([
            'qty' => $request->qty
        ], $cart);

        return response()->json([
            'success' => true
        ]);
    }

    public function destroy($cart)
    {
        $this->repository->destroy($cart);

        return response()->json([
            'success' => true
        ]);
    }

    public function checkout($outlet, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_date' => 'required|date',
            'delivery_date' => 'required|date|after_or_equal:order_date',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        $validated = $validator->validated();

        $validated['detail'] = $this->repository->findByOutlet($outlet)->toArray();

        $response = Http::dsoapi()->withOptions([
            'verify' => false
        ])->get('v1/internal/outlet/' . $outlet);

        $responseData = $response->json();


        if (isset($responseData['RC']) && $responseData['RC'] === "0000") {
            $dso = self::dsoCreateOrder($outlet, $validated);

            if (!$dso['success']) {
                if (isset($dso['message'])) {
                    return response()->json([
                        'success' => false,
                        'dso' => true,
                        'message' => $dso['message']
                    ]);
                }
                return response()->json([
                    'success' => false
                ]);
            }

            foreach ($validated['detail'] as $key => $value) {
                $validated['detail'][$key]['ordet_id'] = $dso['data']->ORDER_PRODUCT_INFO[$key]->PROD_ORDET_ID;
                $validated['detail'][$key]['product_id'] = $dso['data']->ORDER_PRODUCT_INFO[$key]->PROD_ID;
            }
            $validated['order_id'] = $dso['data']->ORDER_ID;

            $updateStatus = self::dsoUpdateOrderStatus($validated['order_id']);

            if (!$updateStatus) {
                return response()->json([
                    'success' => false
                ]);
            }
        }

        $outletDetail = self::outletById($outlet);

        $validated['outlet_code'] = $outlet;
        $validated['outlet_name'] = $outletDetail->outlet_name;
        $validated['outlet_address'] = $outletDetail->outlet_alamat;
        $validated['outlet_owner'] = $outletDetail->outlet_pemilik;
        $validated['outlet_phone'] = $outletDetail->no_telp;
        $validated['outlet_longitude'] = $outletDetail->outlet_longitude;
        $validated['outlet_latitude'] = $outletDetail->outlet_latitude;
        $validated['residency'] = $outletDetail->kode_karesidenan;
        $validated['residency_name'] = $outletDetail->nama_karesidenan;
        $validated['city'] = $outletDetail->kode_kabupaten;
        $validated['city_name'] = $outletDetail->nama_kabupaten;
        $validated['district'] = $outletDetail->kode_kecamatan;
        $validated['district_name'] = $outletDetail->nama_kecamatan;
        $validated['type_id'] = '5d886962-1910-46b1-9626-139961e51d78';
        $validated['order_id'] = Str::uuid();


        $result = $this->transactionRepository->create($validated, true);

        $this->repository->deleteByOutlet($outlet);

        return response()->json($result);
    }

    private function outletById($outlet)
    {
        $apiURL = env('API_URL_DMLT');
        try {
            $response = Http::withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions(["verify" => false])->get($apiURL . 'outlet/detail/' . auth()->user()->sbu_code . '?outlet_code=' . $outlet);

            if ($response->successful()) {
                $response = $response->object();
                return $response->data[0];
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

    private function dsoCreateOrder($outlet, $data)
    {
        $data = Arr::only($data, ['detail']);

        // dd($data);

        $mapped['order_product'] = [];
        foreach ($data['detail'] as $key => $value) {
            $mapped['order_product'][$key]['product_code'] = $value['product_code'];
            $mapped['order_product'][$key]['product_name'] = $value['product_name'];
            $mapped['order_product'][$key]['product_qty'] = $value['qty'];
            $mapped['order_product'][$key]['product_unit'] = $value['unit'];
            $mapped['order_product'][$key]['product_price'] = 0;
            $mapped['order_product'][$key]['product_picture'] = $value['product_picture'];
        }

        $data = Arr::prepend($mapped, auth()->user()->sbu_code, 'sbu_code');
        $data = Arr::prepend($data, $outlet, 'outlet_code');

        $response = Http::dsoapi()->withOptions([
            'verify' => false,
        ])->withBody(json_encode($data), 'application/json')->post('v1/internal/order/create');

        if ($response->successful()) {
            $response = $response->object();

            if (isset($response->RC) && $response->RC == '0000') {
                $data = $response->DATA->ORDER_DETAIL;

                return [
                    'success' => true,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => $response->RCM
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Validasi outlet call sebelum menambah ke cart
     */
    private function validateOutletCall($outletCode)
    {
        try {
            // Cari data outlet di table outlet_calls
            $today = Carbon::now()->translatedFormat('l');

            $outletCall = OutletCall::where('outlet_code', $outletCode)
                ->where('status', 1)
                ->where('day', $today)
                ->first();

            $baypassOutlet = BypassOutlet::where('outlet_code', $outletCode)
                ->where('date', Carbon::now()->format('Y-m-d'))
                ->where('status', BypassOutlet::STATUS_APPROVED)
                ->first();

            if ($baypassOutlet) {
                return [
                    'status' => true,
                    'message' => 'Outlet memiliki bypass outlet yang disetujui untuk hari ini'
                ];
            }

            // Jika data outlet tidak ditemukan di outlet_calls, boleh insert
            if (!$outletCall) {

                $outletCall = OutletCall::where('outlet_code', $outletCode)
                    ->where('status', 1)
                    ->where('validation', true)
                    ->first();

                if ($outletCall) {
                    return [
                        'status' => false,
                        'message' => "Tidak dapat melakukan transaksi. Outlet call dijadwalkan untuk hari {$outletCall->day}, hari ini adalah {$today}"
                    ];
                }

                // Outlet tidak terdaftar di outlet call, diizinkan melakukan transaksi
                return [
                    'status' => true,
                    'message' => 'Outlet diizinkan melakukan transaksi'
                ];
            }

            // Jika validation false, boleh insert data
            if (!$outletCall->validation) {
                return [
                    'status' => true,
                    'message' => 'Outlet diizinkan melakukan transaksi'
                ];
            }

            // Jika validation true, cek apakah day sama dengan hari ini
            $today = $this->getCurrentDayName();

            if ($outletCall->validation && $outletCall->day !== $today) {
                return [
                    'status' => false,
                    'message' => "Tidak dapat melakukan transaksi. Outlet call dijadwalkan untuk hari {$outletCall->day}, hari ini adalah {$today}"
                ];
            }

            // Jika validation true dan day sama dengan hari ini, boleh insert
            return [
                'status' => true,
                'message' => 'Outlet call hari sesuai, diizinkan melakukan transaksi'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Terjadi kesalahan saat validasi outlet call: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get current day name in Indonesian
     */
    private function getCurrentDayName()
    {
        $englishDay = Carbon::now()->format('l'); // Monday, Tuesday, etc.

        $dayMapping = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        return $dayMapping[$englishDay] ?? $englishDay;
    }

    private function dsoUpdateOrderStatus($orderId)
    {
        $data = [
            'id' => $orderId,
            'status' => 1
        ];
        $apiURL = env('API_URL_DSO_HPS_V2');

        $response = Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IlByb3NwZWsiLCJsYXN0X25hbWUiOiJBcGkiLCJlbWFpbCI6ImFwaUBwcm9zcGVrLmNvbSIsInBhc3N3b3JkIjoiNzExMGVkYTRkMDllMDYyYWE1ZTRhMzkwYjBhNTcyYWMwZDJjMDIyMCIsImlwX2FkZHJlc3MiOiIxMjcuMC4wLjEifQ._R2e9nZKlEysCXS6Iu93eOiI7IfY5_U66Ya7utpLBtI'
        ])->withOptions([
            'verify' => false
        ])->withBody(json_encode($data), 'application/json')->post($apiURL . 'order/update');

        if ($response->successful()) {
            return true;
        }

        return false;
    }
}
