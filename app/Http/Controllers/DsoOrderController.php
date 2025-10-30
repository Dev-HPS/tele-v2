<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;

class DsoOrderController extends Controller
{
    private TransactionRepository $repository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->repository = $transactionRepository;
    }

    public function index()
    {
        $title = 'Order DSO';
        $data = self::listOrderDso();

        return view('transactions.dso', compact([
            'title',
            'data'
        ]));
    }

    public function store($id, Request $request)
    {
        $result = $this->repository->createDsoOrder($id, $request->all());

        return response()->json($result);
    }

    private function listOrderDso()
    {
        $response = Http::dsoapi()->withOptions([
            'query' => [
                'kode_sbu' => auth()->user()->sbu_code,
                'status_order' => '1,7'
            ],
            'verify' => false
        ])->get('v1/internal/order');


        if ($response->successful()) {
            $response = $response->object();

            if (isset($response->RC) && $response->RC == '0015') {
                return [];
            }
            return $response->DATA->ORDER_DATA;
        }

        return [];
    }

    public function detailOrderDso($id, $outlet)
    {
        $responses = Http::pool(fn(Pool $pool) => [
            $pool->withHeaders([
                'x-internal-access-token' => config('dsoapi.token'),
            ])->withOptions([
                'verify' => false
            ])->baseUrl(config('dsoapi.url'))->get('v1/internal/order/' . $id),
            $pool->withHeaders([
                'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IkFyZHkgIiwibGFzdF9uYW1lIjoiU3VyeWEiLCJlbWFpbCI6ImFyZHlkYW5nZXJvdXNAZ21haWwuY29tIiwicGFzc3dvcmQiOiI2ZDdkZWQxODc3OGQ4ZjY4NTRhNDE4Nzg2ZDA3MzA1MDIxN2UxNTAyIiwiaXBfYWRkcmVzcyI6IjEwMy44My4xNzkuMjEwIn0.2SFCqr_MB7LyLv8JmtmAgPBTfgAk2KqOVs2T2MfOSTA'
            ])->withOptions([
                'query' => [
                    'outlet_code' => $outlet,
                ],
                'verify' => false
            ])->get(env('API_URL_DMLT') . 'outlet/detail/' . auth()->user()->sbu_code)
        ]);

        // dd($responses);

        if ($responses[0]->failed() && $responses[1]->failed()) {
            return response()->json([
                'success' => false,
            ]);
        }

        $checkExist = $this->repository->findByOrderId($responses[0]->object()->DATA->ORDER_DETAIL->ORDER_ID);

        if ($checkExist) {
            return response()->json([
                'success' => true,
                'exist' => true,
                'order' => $responses[0]->object()->DATA->ORDER_DETAIL,
                'outlet' => $responses[1]->object()->data[0]
            ]);
        }

        return response()->json([
            'success' => true,
            'order' => $responses[0]->object()->DATA->ORDER_DETAIL,
            'outlet' => $responses[1]->object()->data[0]
        ]);
    }
}
