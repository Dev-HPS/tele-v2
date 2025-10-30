<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Status;
use App\Models\Transaction;
use App\Traits\ResponseJson;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    use ResponseJson;

    public function index(Request $request)
    {
        $sbu_code = $request->sbu_code;
        $order_date = $request->order_date;
        $residency = $request->residency;

        if (!$sbu_code || !$order_date) {
            return $this->respondError(null, "Silahkan masukan kode sbu dan tanggal order", 400);
        }

        $data = Transaction::query()
            ->select(
                'transactions.id',
                'transactions.ticket_number',
                'transactions.sbu_code',
                'transactions.order_date',
                'transactions.outlet_code',
                'transactions.outlet_name',
                'st.status_name as status_name'
            )
            ->leftJoin('status as st', 'transactions.status', '=', 'st.id')
            ->withSum('details as sumqty', 'qty')
            ->where('transactions.status', '2')
            ->where('transactions.type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->where('transactions.sbu_code', $sbu_code)
            ->whereDate('transactions.order_date', $order_date);


        if ($residency) {
            $data = $data->where('residency', $residency);
        }

        $data = $data->get();

        if ($data->isEmpty()) {
            return $this->respondError(null, "Data Order tidak tersedia", 404);
        }

        return $this->respondSuccess($data, null, 200);
    }

    public function detailOrder(Request $request)
    {
        $ticket_number = $request->ticket_number;

        if (!$ticket_number) {
            return $this->respondError(null, "Silahkan masukan ticket number", 400);
        }

        $transaction = Transaction::where('ticket_number', $ticket_number)->with(['details'])->first();

        if (!$transaction) {
            return $this->respondError(null, "Data Order tidak ditemukan", 404);
        }

        $result = [];

        foreach ($transaction->details as $item) {
            $data = [
                "id" => $item->id,
                "transaction_id" => $item->transaction_id,
                "ticket_number" => $transaction->ticket_number,
                "product_code" => $item->product_code,
                "product_name" => $item->product_name,
                "unit" => $item->unit,
                "qty" => $item->qty
            ];

            array_push($result, $data);
        }

        return $this->respondSuccess($result, null, 200);
    }

    public function updateOrder(Request $request)
    {
        $ticket_number = $request->ticket_number;
        $statusId = $request->status;

        $transaction = Transaction::where('ticket_number', $ticket_number)->with(['details'])->first();

        if (!$transaction) {
            return $this->respondError(null, "Data Order tidak ditemukan", 404);
        }

        $status = Status::where('id', $statusId)->first();

        if (!$status) {
            return $this->respondError(null, "Kode status tidak valid", 404);
        }

        $transaction->update([
            'status' => $statusId
        ]);

        $transaction->details()->update([
            'detail_status' => $statusId
        ]);

        return $this->respondSuccess(null, "Data Order berhasil di update", 200);
    }
}
