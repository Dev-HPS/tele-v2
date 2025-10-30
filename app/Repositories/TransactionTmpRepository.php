<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailTmp;
use App\Models\TransactionTmp;
use App\Repositories\Repository as BaseRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransactionTmpRepository implements BaseRepository
{
    private TransactionTmp $model;
    private Transaction $modelTransaction;
    private TransactionDetailTmp $modelTransactionDetailTmp;
    private TransactionDetail $modelTransactionDetail;

    public function __construct(TransactionTmp $transactionTmp, Transaction $transaction, TransactionDetailTmp $transactionDetailTmp, TransactionDetail $transactionDetail)
    {
        $this->model = $transactionTmp;
        $this->modelTransaction = $transaction;
        $this->modelTransactionDetailTmp = $transactionDetailTmp;
        $this->modelTransactionDetail = $transactionDetail;
    }

    public function findAll($type = null) {}

    public function findById($id)
    {
        return $this->model->where('id', $id)->with(['details', 'transactions', 'details.detailTransaction', 'transactions.details'])->first();
    }

    public function findByTicketNumber($id)
    {
        $transaction = $this->modelTransaction->where('id', $id)->first();
        return $this->model->where('ticket_number', $transaction->ticket_number)->with('details')->first();
    }

    public function editTransaction($type = null)
    {
        $role = auth()->user()->role->id;

        return $this->model::query()
            ->select('transactions_tmp.*')
            ->whereIn('status', ['5', '6'])
            ->with(['user', 'type'])
            ->whereHas('type', function ($q) use ($type) {
                $q->where('code', $type);
            })
            ->when($type == 'SR', function ($query) {
                $query->addSelect('status.status_name')
                    ->join('status', function ($join) {
                        $join->on('transactions_tmp.status', '=', 'status.id')
                            ->where('transactions_tmp.type_id', '=', '5d886962-1910-46b1-9626-139961e51d78');
                    });
            })
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->when($role == 'f4367c27-700b-4e34-89d4-75822e97f76c', function ($query) {
                $query->where('sbu_code', auth()->user()->sbu_code);
            })->latest();
    }

    public function create(array $data, $condition = false) {}

    public function approve($id)
    {
        $transactionTmp = $this->model->where('id', $id)->with(['details'])->first();
        $transaction = $this->modelTransaction->where('ticket_number', $transactionTmp->ticket_number)->first();

        $description = "";

        foreach ($transactionTmp->details as $item) {
            if ($item->reason_type == 'EDIT' || $item->reason_type == null) {
                $this->modelTransactionDetail->where('transaction_id', $transaction->id)->where('id', $item->transaction_detail_id)->update([
                    'product_code' => $item->product_code,
                    'product_name' => $item->product_name,
                    'unit' => $item->unit,
                    'qty' => $item->qty,
                    'product_type' => $item->product_type,
                    'detail_status' => 2
                ]);
            }

            if ($item->reason_type == 'DELETE') {
                $this->modelTransactionDetail->where('transaction_id', $transaction->id)->where('id', $item->transaction_detail_id)->delete();
            }
        }

        $this->modelTransactionDetail->where('transaction_id', $transaction->id)->update([
            'detail_status' => 2
        ]);

        $transactionDetail = $this->modelTransactionDetail->where('transaction_id', $transaction->id)->get();

        foreach ($transactionDetail as $item) {
            $description = $description  . $item->qty . ' ' . $item->unit . ' ' . $item->product_name . ', ';
        }

        $this->modelTransaction->where('ticket_number', $transactionTmp->ticket_number)->update([
            "outlet_code" => $transactionTmp->outlet_code,
            "outlet_name" => $transactionTmp->outlet_name,
            "outlet_address" => $transactionTmp->outlet_address,
            "outlet_owner" => $transactionTmp->outlet_owner,
            "outlet_phone" => $transactionTmp->outlet_phone,
            "outlet_longitude" => $transactionTmp->outlet_longitude,
            "outlet_latitude" => $transactionTmp->outlet_latitude,
            "residency" => $transactionTmp->residency,
            "residency_name" => $transactionTmp->residency_name,
            "city" => $transactionTmp->city,
            "city_name" => $transactionTmp->city_name,
            "district" => $transactionTmp->district,
            "district_name" => $transactionTmp->district_name,
            "status" => '2',
            "description" => $description
        ]);


        $result = [];
        $ntiket = "";
        $sbu_code = "";
        $nstatus = "";
        $transaction = $this->modelTransaction->where('ticket_number', $transactionTmp->ticket_number)->first();
        foreach ($transactionDetail as $item) {
            $data = [];
            $ntiket = $transaction->ticket_number;
            $nstatus = 2;
            $data["TELE_ID"] = $transaction->id;
            $data["BARANG_ID"] = $item->id;
            $data["TELE_TICKET"] = $transaction->ticket_number;
            $data["PROD_CODE"] = $item->product_code;
            $data["PROD_NAME"] = $item->product_name;
            $data["PROD_QTY"] = $item->qty;
            $data["SATUAN"] = $item->unit;
            $data["VERIFIKASI_PROD"] = 2;
            $data["PROD_TEXT"] = $transaction->description;
            $data["PROD_QTY_ORDER"] = $item->qty;
            $data["PROD_CODE_ORDER"] = $item->product_code;
            $data["ID_GUDANG"] = $item->warehouse;
            $data["KODESUPP"] = $item->supplier;
            $data["NOPOL"] = $item->vehicle_plate;
            $data["KIRIMAN_KE"] = $item->post;
            $data["KD_POOL"] = $item->pool_code;
            $data["NM_POOL"] = $item->pool_name;
            $data["KAPASITAS"] = $item->vehicle_capacity;
            $data["PRODUCT_ID"] = $item->product_id;
            $data["NOTA_KE"] = $item->nota_ke;
            $sbu_code = $transaction->sbu_code;
            $result[] = $data;
        }

        $data = [
            "sbu_code" => $sbu_code,
            "ntiket" => $ntiket,
            "nstatus" => $nstatus,
            "data" => $result
        ];

        $response =  Http::withHeaders([
            'access-token-dmlt' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MiwiZmlyc3RfbmFtZSI6IlByb3NwZWsiLCJsYXN0X25hbWUiOiJBcGkiLCJlbWFpbCI6ImFwaUBwcm9zcGVrLmNvbSIsInBhc3N3b3JkIjoiNzExMGVkYTRkMDllMDYyYWE1ZTRhMzkwYjBhNTcyYWMwZDJjMDIyMCIsImlwX2FkZHJlc3MiOiIxMjcuMC4wLjEifQ._R2e9nZKlEysCXS6Iu93eOiI7IfY5_U66Ya7utpLBtI',
        ])->withBody(json_encode($data, JSON_UNESCAPED_SLASHES), 'application/json')->post(env('API_URL_BOA') . 'update/status_tiket');

        if ($response->successful()) {
            dump($ntiket, $nstatus, $sbu_code, $response->json(), json_encode($data, JSON_UNESCAPED_SLASHES));

            $this->modelTransactionDetailTmp->where('transaction_tmp_id', $transactionTmp->id)->delete();

            $this->model->where('id', $id)->delete();
        } else {
            $statusCode = $response->status();
            $errorMessage = $response->body();

            dd($statusCode, $errorMessage);
        }
    }

    public function update(array $data, $id)
    {
        $transaction = $this->modelTransaction->where('id', $id)->first();

        $transactionTmp = $this->model->where('ticket_number', $transaction->ticket_number)->first();

        if ($transactionTmp) {
            $this->model->where('ticket_number', $transaction->ticket_number)->update([
                "outlet_code" => $data["outlet_code"],
                "outlet_name" => $data["outlet_name"],
                "outlet_address" => $data["outlet_address"],
                "outlet_owner" => $data["outlet_owner"],
                "outlet_phone" => $data["outlet_phone"],
                "outlet_longitude" => $data["outlet_longitude"],
                "outlet_latitude" => $data["outlet_latitude"],
                "residency" => $data["residency"],
                "residency_name" => $data["residency_name"],
                "city" => $data["city"],
                "city_name" => $data["city_name"],
                "district" => $data["district"],
                "district_name" => $data["district_name"],
                "reason" => $data["reason"],
                "residency" => $data["residency"],
                "residency_name" => $data["residency_name"],
            ]);
        } else {
            $this->model->create([
                "sbu_code" => $transaction->sbu_code,
                "order_date" => $transaction->order_date,
                "delivery_date" => $transaction->delivery_date,
                "description" => $transaction->description,
                "status" => $transaction->status,
                "ticket_number" => $transaction->ticket_number,
                "sort" => $transaction->sort,
                "outlet_code" => $data["outlet_code"],
                "outlet_name" => $data["outlet_name"],
                "outlet_address" => $data["outlet_address"],
                "outlet_owner" => $data["outlet_owner"],
                "outlet_phone" => $data["outlet_phone"],
                "outlet_longitude" => $data["outlet_longitude"],
                "outlet_latitude" => $data["outlet_latitude"],
                "user_id" => $transaction->user_id,
                "residency" => $data["residency"],
                "residency_name" => $data["residency_name"],
                "city" => $data["city"],
                "city_name" => $data["city_name"],
                "district" => $data["district"],
                "district_name" => $data["district_name"],
                "type_id" => $transaction->type_id,
                "additional_description" => $transaction->additional_description,
                "order_type_id" => $transaction->order_type_id,
                "order_code" => $transaction->order_code,
                "order_id" => $transaction->order_id,
                "reason" => $data["reason"],
            ]);
        }
    }

    public function checkProductQty(array $data, $id)
    {
        $transaction = $this->modelTransaction->where('id', $id)->first();

        $transactionDetail = $this->modelTransactionDetail->where('transaction_id', $transaction->id)->where('id', $data['id'])->first();

        if ($data['qty'] > $transactionDetail->qty) {
            return true;
        }
    }

    public function deleteDetail(array $data, $id)
    {
        $transaction = $this->modelTransaction->where('id', $id)->first();

        $transactionTmp = $this->model->where('ticket_number', $transaction->ticket_number)->first();

        if (!$transactionTmp) {
            $transactionTmp = $this->model->create([
                "sbu_code" => $transaction->sbu_code,
                "order_date" => $transaction->order_date,
                "delivery_date" => $transaction->delivery_date,
                "description" => $transaction->description,
                "status" => $transaction->status,
                "ticket_number" => $transaction->ticket_number,
                "sort" => $transaction->sort,
                "outlet_code" => $transaction->outlet_code,
                "outlet_name" => $transaction->outlet_name,
                "outlet_address" => $transaction->outlet_address,
                "outlet_owner" => $transaction->outlet_owner,
                "outlet_phone" => $transaction->outlet_phone,
                "outlet_longitude" => $transaction->outlet_longitude,
                "outlet_latitude" => $transaction->outlet_latitude,
                "user_id" => $transaction->user_id,
                "residency" => $transaction->residency,
                "residency_name" => $transaction->residency_name,
                "city" => $transaction->city,
                "city_name" => $transaction->city_name,
                "district" => $transaction->district,
                "district_name" => $transaction->district_name,
                "type_id" => $transaction->type_id,
                "additional_description" => $transaction->additional_description,
                "order_type_id" => $transaction->order_type_id,
                "order_code" => $transaction->order_code,
                "order_id" => $transaction->order_id,
            ]);
        }

        $transactionDetail = $this->modelTransactionDetail->where('transaction_id', $id)->where('id', $data['id'])->first();

        $transactionDetailTmp = $this->modelTransactionDetailTmp->where('transaction_detail_id', $transactionDetail->id)->first();

        if ($transactionDetailTmp) {
            $this->modelTransactionDetailTmp->where('transaction_detail_id', $transactionDetail->id)->update([
                'reason' => $data['reason'],
            ]);
        } else {
            $this->modelTransactionDetailTmp->create([
                'transaction_id' => $transactionDetail->transaction_id,
                'product_code' =>  $transactionDetail->product_code,
                'product_name' => $transactionDetail->product_name,
                'qty' => $transactionDetail->qty,
                'unit' => $transactionDetail->unit,
                'warehouse' => $transactionDetail->warehouse,
                'supplier' => $transactionDetail->supplier,
                'vehicle_plate' => $transactionDetail->vehicle_plate,
                'post' => $transactionDetail->post,
                'pool_code' => $transactionDetail->pool_code,
                'pool_name' => $transactionDetail->pool_name,
                'vehicle_capacity' => $transactionDetail->vehicle_capacity,
                'detail_status' => $transactionDetail->detail_status,
                'product_id' => $transactionDetail->product_id,
                'ordet_id' => $transactionDetail->ordet_id,
                'nota_ke' => $transactionDetail->nota_ke,
                'product_type' => $transactionDetail->product_type,
                'transaction_detail_id' => $transactionDetail->id,
                'transaction_tmp_id' => $transactionTmp->id,
                'reason' => $data['reason'],
                'reason_type' => 'DELETE'
            ]);
        }
    }

    public function updateDetail(array $data, $id)
    {
        $transaction = $this->modelTransaction->where('id', $id)->first();

        $transactionTmp = $this->model->where('ticket_number', $transaction->ticket_number)->first();

        if (!$transactionTmp) {
            $transactionTmp = $this->model->create([
                "sbu_code" => $transaction->sbu_code,
                "order_date" => $transaction->order_date,
                "delivery_date" => $transaction->delivery_date,
                "description" => $transaction->description,
                "status" => $transaction->status,
                "ticket_number" => $transaction->ticket_number,
                "sort" => $transaction->sort,
                "outlet_code" => $transaction->outlet_code,
                "outlet_name" => $transaction->outlet_name,
                "outlet_address" => $transaction->outlet_address,
                "outlet_owner" => $transaction->outlet_owner,
                "outlet_phone" => $transaction->outlet_phone,
                "outlet_longitude" => $transaction->outlet_longitude,
                "outlet_latitude" => $transaction->outlet_latitude,
                "user_id" => $transaction->user_id,
                "residency" => $transaction->residency,
                "residency_name" => $transaction->residency_name,
                "city" => $transaction->city,
                "city_name" => $transaction->city_name,
                "district" => $transaction->district,
                "district_name" => $transaction->district_name,
                "type_id" => $transaction->type_id,
                "additional_description" => $transaction->additional_description,
                "order_type_id" => $transaction->order_type_id,
                "order_code" => $transaction->order_code,
                "order_id" => $transaction->order_id,
            ]);
        }


        if (isset($data['detail']) && $data['detail'] != false) {
            $transactionDetail = $this->modelTransactionDetailTmp->where('transaction_id', $id)->where('transaction_detail_id', $data['id'])->first();

            if ($transactionDetail) {
                $this->modelTransactionDetailTmp->where('transaction_id', $id)->where('transaction_detail_id', $data['id'])->update([
                    'qty' => $data['qty'],
                    'reason' => $data['reason']
                ]);
                return;
            } else {
                $transactionDetail = $this->modelTransactionDetail->where('transaction_id', $id)->where('id', $data['id'])->first();

                $this->modelTransactionDetailTmp->create([
                    'transaction_id' => $transactionDetail->transaction_id,
                    'product_code' =>  $transactionDetail->product_code,
                    'product_name' => $transactionDetail->product_name,
                    'qty' => $data['qty'],
                    'unit' => $transactionDetail->unit,
                    'warehouse' => $transactionDetail->warehouse,
                    'supplier' => $transactionDetail->supplier,
                    'vehicle_plate' => $transactionDetail->vehicle_plate,
                    'post' => $transactionDetail->post,
                    'pool_code' => $transactionDetail->pool_code,
                    'pool_name' => $transactionDetail->pool_name,
                    'vehicle_capacity' => $transactionDetail->vehicle_capacity,
                    'detail_status' => $transactionDetail->detail_status,
                    'product_id' => $transactionDetail->product_id,
                    'ordet_id' => $transactionDetail->ordet_id,
                    'nota_ke' => $transactionDetail->nota_ke,
                    'product_type' => $transactionDetail->product_type,
                    'transaction_detail_id' => $transactionDetail->id,
                    'transaction_tmp_id' => $transactionTmp->id,
                    'reason' => $data['reason'],
                    'reason_type' => 'EDIT'
                ]);
            }
            return;
        }

        $transaction = $this->modelTransaction->where('id', $id)->first();
        return $this->model->where('ticket_number', $transaction->ticket_number)->update([
            "description" => $data["description"]
        ]);
    }

    public function destroy($id) {}
}
