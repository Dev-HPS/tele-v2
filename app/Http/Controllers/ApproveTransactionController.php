<?php

namespace App\Http\Controllers;

use App\Helpers\CustomHelper;
use App\Repositories\StatusRepository;
use App\Repositories\TransactionTmpRepository;
use Exception;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Http;

class ApproveTransactionController extends Controller
{
    private TransactionTmpRepository $transactionTmpRepository;
    private StatusRepository $statusRepository;

    public function __construct(TransactionTmpRepository $transactionTmpRepository, StatusRepository $statusRepository)
    {
        $this->transactionTmpRepository = $transactionTmpRepository;
        $this->statusRepository = $statusRepository;
    }

    public function index()
    {
        $title = 'Transaksi';
        $residency = self::residency();
        $url = route('approve-transactions.datatable');
        $status = $this->statusRepository->findEditTransaction('5d886962-1910-46b1-9626-139961e51d78')->get();

        return view('approve-transactions.index', compact([
            'title', 'url', 'status', 'residency'
        ]));
    }

    public function show($id)
    {
        $data = $this->transactionTmpRepository->findById($id);

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

    public function approve($id)
    {
        try {
            $this->transactionTmpRepository->approve($id);

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
            }
        } catch (ConnectException $e) {
            return $e->getMessage();
        }
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->transactionTmpRepository->editTransaction('SR');
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
                            $w->orWhere('outlet_name', 'LIKE', "%$search%");
                            $w->orWhere('ticket_number', 'LIKE', "%$search%");
                        });
                    }
                })
                ->addColumn('detail', function ($row) {
                    $detailBtn = '<a onclick="detail(this)" class="btn btn-primary" data-id="' . $row->id . '">Detail</a>';
                    return $detailBtn;
                })
                ->addColumn('action', function ($row) {
                    $access = ['f4367c27-700b-4e34-89d4-75822e97f76c', '346d417a-544d-48f3-bb4d-1da4ce54dffc', ''];

                    if (in_array(auth()->user()->role->id, $access)) {
                        $url = route('edit-transactions.edit', $row->id);
                        $btn = '<a onclick="approve(this)" data-id="' . $row->id . '" class="btn btn-success">Approve</a>';
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
