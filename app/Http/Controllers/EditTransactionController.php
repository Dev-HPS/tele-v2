<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelper;
use App\Repositories\StatusRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TransactionTmpRepository;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

class EditTransactionController extends Controller
{

    private TransactionRepository $repository;
    private StatusRepository $statusRepository;
    private TransactionTmpRepository $transactionTmpRepository;

    public function __construct(TransactionRepository $transactionRepository, StatusRepository $statusRepository, TransactionTmpRepository $transactionTmpRepository)
    {
        $this->repository = $transactionRepository;
        $this->statusRepository = $statusRepository;
        $this->transactionTmpRepository = $transactionTmpRepository;
    }

    public function index()
    {
        $residency = self::residency();
        $title = 'Transaksi';
        $url = route('edit-transactions.datatable');
        $status = $this->statusRepository->findEditTransaction('5d886962-1910-46b1-9626-139961e51d78')->get();

        return view('edit-transactions.index', compact([
            'title',
            'url',
            'residency',
            'status'
        ]));
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

    public function edit($transaction)
    {
        try {
            $title = 'Edit Transaksi';
            $data = $this->repository->findById($transaction);
            $warehouse = self::warehouse();
            $supplier = self::supplier();

            $residency = self::residency();
            $residency = collect($residency);
            $userDetails = auth()->user()->userDetails;
            $userDetails = $userDetails->pluck('residency');
            $residency = $residency->whereIn('kode_kar', $userDetails->toArray());

            return view('edit-transactions.edit', compact([
                'title',
                'data',
                'warehouse',
                'supplier',
                'transaction',
                'residency'
            ]));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function updateOutlet(Request $request, $id)
    {
        if (in_array('undefined', [$request->outlet_code, $request->city, $request->district]) || $request->reason == null) {
            return response()->json([
                'success' => false,
                'message' => "Harap lengkapi data yang ada"
            ]);
        }
        try {
            $this->transactionTmpRepository->update($request->all(), $id);

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
            'product_type' => 'string',
            'product_name' => 'string',
            'product_code' => 'string',
            'product_unit' => 'string',
            'qty' => 'required|numeric',
            'reason' => 'required|string',
            'id' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ]);
        }

        $validated = $validator->validated();
        $checkProductQty = $this->transactionTmpRepository->checkProductQty($validated, $transaction);

        if ($checkProductQty) {
            return response()->json([
                'success' => false,
                'message' => "Quantity produk tidak boleh lebih dari data yang ada"
            ]);
        }
        $validated['detail'] = true;
        $validated['id'] = $detail;
        $this->transactionTmpRepository->updateDetail($validated, $transaction);

        $validated['detail'] = false;

        $data = $this->transactionTmpRepository->findByTicketNumber($transaction);
        $transactionData = [];
        $transactionData['description'] = '';
        foreach ($data->details as $item) {
            $transactionData['description'] .= ' ' . $item["qty"];
            $transactionData['description'] .= ' ' . $item["unit"];
            $transactionData['description'] .= ' ' . $item["product_name"] . ',';
        }
        $transactionData['description'] = ltrim($transactionData['description']);

        $this->transactionTmpRepository->updateDetail($transactionData, $transaction);

        return response()->json([
            'success' => true
        ]);
    }

    public function deleteDetail($transaction, $detail, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_type' => 'string',
            'product_name' => 'string',
            'product_code' => 'string',
            'product_unit' => 'string',
            'reason' => 'required|string',
            'id' => 'string'
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

        $this->transactionTmpRepository->deleteDetail($validated, $transaction);

        return response()->json([
            'success' => true
        ]);
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

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->editTransaction('SR');
            return Datatables::of($data)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->get('statusCode')) {
                        $status = $request->get('statusCode');
                        if ($status == 'All') {
                            $query->whereIn('status', ['5', '6']);
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
                    $access = ['2629192e-1c3f-477e-a157-4def565dace3', '346d417a-544d-48f3-bb4d-1da4ce54dffc', 'f4367c27-700b-4e34-89d4-75822e97f76c', 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e'];

                    if (in_array(auth()->user()->role->id, $access)) {
                        $url = route('edit-transactions.edit', $row->id);
                        $btn = '<a href="' . $url . '" data-id="' . $row->id . '" class="btn btn-warning">Edit</a>';
                        return $btn;
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
