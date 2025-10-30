<?php

namespace App\Repositories;

use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use App\Helpers\CustomHelper;
use App\Models\TransactionLog;
use App\Models\TransactionType;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use App\Repositories\Repository as BaseRepository;
use Illuminate\Support\Facades\Http;


class TransactionRepository implements BaseRepository
{

    private Transaction $model;
    private TransactionType $modelTransactionType;
    private TransactionLog $modelTransactionLog;
    private TransactionDetail $modelTransactionDetail;

    public function __construct(Transaction $transaction, TransactionType $transactionType, TransactionLog $transactionLog, TransactionDetail $transactionDetail)
    {
        $this->model = $transaction;
        $this->modelTransactionType = $transactionType;
        $this->modelTransactionLog = $transactionLog;
        $this->modelTransactionDetail = $transactionDetail;
    }

    public function findAll($type = null)
    {
        $role = auth()->user()->role->id;

        return $this->model::query()
            ->select('transactions.*')
            ->with(['user', 'type'])
            ->whereHas('type', function ($q) use ($type) {
                $q->where('code', $type);
            })
            ->when($type == 'SR', function ($query) {
                $query->addSelect('status.status_name')
                    ->join('status', function ($join) {
                        $join->on('transactions.status', '=', 'status.id')
                            ->where('transactions.type_id', '=', '5d886962-1910-46b1-9626-139961e51d78');
                    });
            })
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                $query->where('sbu_code', auth()->user()->sbu_code);
            })->latest();
    }

    public function editTransaction($type = null)
    {
        $role = auth()->user()->role->id;

        return $this->model::query()
            ->select('transactions.*')
            ->whereIn('status', ['5', '6'])
            ->with(['user', 'type'])
            ->whereHas('type', function ($q) use ($type) {
                $q->where('code', $type);
            })
            ->when($type == 'SR', function ($query) {
                $query->addSelect('status.status_name')
                    ->join('status', function ($join) {
                        $join->on('transactions.status', '=', 'status.id')
                            ->where('transactions.type_id', '=', '5d886962-1910-46b1-9626-139961e51d78');
                    });
            })
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                $query->where('sbu_code', auth()->user()->sbu_code);
            })->latest();
    }

    public function findById($id)
    {
        return $this->model->where('id', $id)->with('details')->first();
    }

    public function create(array $data, $isTransaction = false)
    {
        try {
            DB::beginTransaction();
            //code...
            $type = $this->modelTransactionType::findOrFail($data['type_id']);
            $lastTicketNumber = $this->model
                ->where('type_id', $data['type_id'])
                ->where('sbu_code', auth()->user()->sbu_code) // <--- filter berdasarkan SBU
                ->whereMonth('created_at', date('m'))
                ->max('sort') ?: 0;

            $sort = $lastTicketNumber + 1;
            $ticketPrefix = Str::padLeft($sort, 6, 0);
            $roman = CustomHelper::numberToRoman(date('m'));

            $ticketNumber = $ticketPrefix . '/'
                . auth()->user()->sbu_code . '/'
                . $type->code . '/'
                . $roman . '/'
                . date('Y');

            $data['sbu_code'] = auth()->user()->sbu_code;
            $data['ticket_number'] = $ticketNumber;
            $data['sort'] = $sort;
            $data['user_id'] = auth()->user()->id;

            $create = $this->model->create($data);

            $lastInsertId = $create->id;

            if ($isTransaction) {
                foreach ($data['detail'] as $row) {
                    $transactionData = [
                        'transaction_id' => $lastInsertId,
                        'product_code' => $row['product_code'],
                        'product_name' => $row['product_name'],
                        'qty' => $row['qty'],
                        'unit' => $row['unit'],
                        'product_type' => $row['product_type']
                    ];

                    // Check if product_id exists in the $row array
                    if (isset($row['product_id'])) {
                        $transactionData['product_id'] = $row['product_id'];
                    }

                    // Check if ordet_id exists in the $row array
                    if (isset($row['ordet_id'])) {
                        $transactionData['ordet_id'] = $row['ordet_id'];
                    }

                    // Insert the transaction detail
                    $this->modelTransactionDetail->create($transactionData);
                }
            }

            $this->modelTransactionLog->create([
                'transaction_id' => $lastInsertId,
                'user_id' => auth()->user()->id,
                'status' => 1,
            ]);

            DB::commit();

            $result = [
                'success' => true
            ];

            return $result;
        } catch (Exception $e) {
            DB::rollback();

            $result = [
                'success' => false,
                'message' => $e->getMessage()
            ];
            return $result;
        }
    }

    public function update(array $data, $id)
    {
        if (isset($data['status'])) {
            $this->modelTransactionLog->create([
                'transaction_id' => $id,
                'user_id' => auth()->user()->id,
                'status' => $data['status'],
            ]);

            if ($data['status'] === 99 && isset($data['additional_description'])) {
                DB::transaction(function () use ($id, $data) {
                    $this->model->where('id', $id)->update([
                        'status' => 99,
                        'additional_description' => $data['additional_description']
                    ]);
                    $this->modelTransactionDetail->where('transaction_id', $id)->update([
                        'detail_status' => 99
                    ]);
                }, 5);
                return;
            }
        }

        if (isset($data['detail']) && $data['detail'] != false) {
            $this->modelTransactionDetail
                ->where('transaction_id', $id)
                ->where('id', $data['id'])
                ->update([
                    'product_code' => $data['product_code'],
                    'product_name' => $data['product_name'],
                    'unit' => $data['product_unit'],
                    'qty' => $data['qty'],
                    'product_type' => $data['product_type']
                ]);
            return;
        }

        return $this->model::where('id', $id)->update($data);
    }

    public function destroy($id)
    {
        return $this->model->destroy($id);
    }

    public function updateVehicle(array $data, $id)
    {

        $this->model::where('id', $id)->whereIn('status', [1, 2])->update($data);

        return $this->modelTransactionLog->create([
            'transaction_id' => $id,
            'user_id' => auth()->user()->id,
            'status' => $data['status'],
        ]);
    }

    public function validateTransaction(array $data, $id)
    {
        $query = $this->model::where('id', $id);
        if (auth()->user()->role->id == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e') {
            $query = $query->whereIn('status', [1, 2]);
        }

        if (auth()->user()->role->id == '2629192e-1c3f-477e-a157-4def565dace3') {
            $query = $query->where('status', 3);
        }

        $query->update($data);

        return $this->modelTransactionLog->create([
            'transaction_id' => $id,
            'user_id' => auth()->user()->id,
            'status' => $data['status'],
        ]);
    }

    public function findTotalSRByMonth()
    {
        $user = auth()->user();
        $role = $user->role->id;

        $status1 = $this->model->where('status', 1)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $status2 = $this->model->where('status', 2)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $status3 = $this->model->where('status', 3)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $status4 = $this->model->where('status', 4)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $status5 = $this->model->where('status', 5)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $status6 = $this->model->where('status', 6)
            ->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        return [$status1, $status2, $status3, $status4, $status5, $status6];
    }

    public function findTotalINQByMonth()
    {
        $user = auth()->user();
        $role = $user->role->id;

        $open = $this->model->where('status', 1)
            ->where('type_id', 'a5b1bcd4-ff7d-451b-a1a8-4dc2f8798d26')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $hold = $this->model->where('status', 2)
            ->where('type_id', 'a5b1bcd4-ff7d-451b-a1a8-4dc2f8798d26')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $close = $this->model->where('status', 3)
            ->where('type_id', 'a5b1bcd4-ff7d-451b-a1a8-4dc2f8798d26')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        return [$open, $hold, $close];
    }

    public function findTotalCPByMonth()
    {
        $user = auth()->user();
        $role = $user->role->id;

        $open = $this->model->where('status', 1)
            ->where('type_id', '47888059-07ce-433f-993a-1a6a7b112d48')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $hold = $this->model->where('status', 2)
            ->where('type_id', '47888059-07ce-433f-993a-1a6a7b112d48')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        $close = $this->model->where('status', 3)
            ->where('type_id', '47888059-07ce-433f-993a-1a6a7b112d48')
            ->when($role == '2629192e-1c3f-477e-a157-4def565dace3', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereMonth('created_at', date('m'))->get()->count();

        return [$open, $hold, $close];
    }

    public function createDsoOrder($id, array $data)
    {
        try {
            DB::beginTransaction();
            $lastTicketNumber = $this->model->where('type_id', '5d886962-1910-46b1-9626-139961e51d78')->whereMonth('created_at', date('m'))->max('sort') ?: 0;
            $sort = $lastTicketNumber + 1;
            $ticketPrefix = Str::padLeft($sort, 6, 0);
            $roman = CustomHelper::numberToRoman(date('m'));
            $ticketNumber = $ticketPrefix . '/' . auth()->user()->sbu_code . '/SR' . '/' . $roman . '/' . date('Y');


            DB::table('transactions')->insert([
                'id' => $id,
                'sbu_code' => $data['order']['ORDER_SBU'],
                'order_date' => Carbon::parse($data['order']['ORDER_DATE'])->format('Y-m-d'),
                'delivery_date' => Carbon::parse($data['order']['ORDER_DATE'])->format('Y-m-d'),
                'description' => $data['description'],
                'order_code' => $data['order']['ORDER_CODE'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'ticket_number' => $ticketNumber,
                'sort' => $sort,
                'user_id' => auth()->user()->id,
                'outlet_code' => $data['outlet']['outlet_code'],
                'outlet_name' => $data['outlet']['outlet_name'],
                'outlet_address' => $data['outlet']['outlet_alamat'],
                'outlet_owner' => $data['outlet']['outlet_pemilik'],
                'outlet_phone' => $data['outlet']['no_telp'],
                'outlet_longitude' => $data['order']['ORDER_BUYER_INFO']['LONGITUDE'],
                'outlet_latitude' => $data['order']['ORDER_BUYER_INFO']['LATITUDE'],
                'residency' => $data['outlet']['kode_karesidenan'],
                'residency_name' => $data['outlet']['nama_karesidenan'],
                'city' => $data['outlet']['kode_kabupaten'],
                'city_name' => $data['outlet']['nama_kabupaten'],
                'district' => $data['outlet']['kode_kecamatan'],
                'district_name' => $data['outlet']['nama_kecamatan'],
                'type_id' => '5d886962-1910-46b1-9626-139961e51d78',
                'order_type_id' => '99e0b67d-829c-44a6-9bd6-50fd93c1b907',
                'order_id' => $id
            ]);

            // $orderProduct = [];

            foreach ($data['order']['ORDER_PRODUCT_INFO'] as $row) {
                DB::table('transaction_details')->insert([
                    'id' => $row['PROD_ORDET_ID'],
                    'transaction_id' => $id,
                    'product_code' => $row['PROD_CODE'],
                    'product_name' => $row['PROD_NAME'],
                    'qty' => $row['PROD_QTY'],
                    'unit' => $row['PROD_UNIT'],
                    'warehouse' => 0,
                    'supplier' => 0,
                    'product_id' => $row['PROD_ID'],
                    'ordet_id' => $row['PROD_ORDET_ID']
                ]);

                // $orderProduct[] = [
                //     'product_id' => $row['PROD_ID'],
                //     'verify_qty' => (int)$row['PROD_QTY'],
                //     'verify_status' => 11,
                //     'verify_text' => ''
                // ];
            }

            $this->modelTransactionLog->create([
                'transaction_id' => $id,
                'user_id' => auth()->user()->id,
                'status' => 1,
            ]);

            DB::commit();

            return [
                'success' => true
            ];


            // $data = [
            //     'order_id' => $id,
            //     'order_product' => $orderProduct,
            //     'order_text' => $data['description']
            // ];

            // $response = Http::dsoapi()->withOptions([
            //     'verify' => false
            // ])->withBody(json_encode($data), 'application/json')->post('v1/internal/order/confirm');

            // $responseData = $response->json();

            // if (isset($responseData['RC']) && $responseData['RC'] === "0000") {
            //     DB::commit();

            //     return [
            //         'success' => true
            //     ];
            // }

            // return [
            //     'success' => false
            // ];
        } catch (Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function findByOrderId($orderId)
    {
        return $this->model::query()->where('order_id', $orderId)->first();
    }

    public function createDetail($transactionId, array $data)
    {
        try {
            DB::beginTransaction();
            $this->modelTransactionDetail::query()->create([
                'transaction_id' => $transactionId,
                'product_code' => $data['product_code'],
                'product_name' => $data['product_name'],
                'qty' => $data['qty'],
                'unit' => $data['product_unit'],
                'product_type' => $data['product_type']
            ]);

            $this->modelTransactionLog->create([
                'transaction_id' => $transactionId,
                'user_id' => auth()->user()->id,
                'status' => 1,
            ]);

            DB::commit();
            return [
                'success' => true
            ];
        } catch (Exception $e) {
            DB::rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function destroyDetail($transactionId, $transactionDetailId)
    {
        return $this->modelTransactionDetail::query()
            ->where('transaction_id', $transactionId)
            ->where('id', $transactionDetailId)->delete();
    }
}
