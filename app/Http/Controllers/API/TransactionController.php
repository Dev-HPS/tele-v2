<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function cancelDsoOrder($dsoOrder)
    {
        $transaction = Transaction::where('order_id', $dsoOrder)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $transaction->update([
            'status' => '99'
        ]);


        return response()->json(['message' => 'Cancel transaction was successfull'], 200);
    }
}
