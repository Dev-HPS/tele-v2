<?php

namespace App\Http\Controllers;

use App\Repositories\OutletCallRepository;
use App\Services\OutletCallLogService;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;


class ApproveOutletCallController extends Controller
{
    private OutletCallRepository $repository;

    public function __construct(
        OutletCallRepository $transactionRepository,
    ) {
        $this->repository = $transactionRepository;
    }


    public function index()
    {
        $title = 'Approve Outlet Call';

        return view('approve-outlet-call.index', compact([
            'title',
        ]));
    }

    public function approve($id)
    {
        $this->repository->approve($id);

        $result = [
            'success' => true
        ];

        return response()->json($result);
    }

    public function reject(Request $request)
    {
        $this->repository->reject($request);

        $result = [
            'success' => true
        ];

        return response()->json($result);
    }

    public function datatable(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->repository->findApprove();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {

                    $reject = '<a onclick="rejectModal(this)" data-id="' . $row->id . '" class="btn btn-danger">Reject</a>';

                    $approve = '<a onclick="approve(\'' . $row->id . '\')" class="btn btn-success">Approve</a>';

                    return $approve . ' ' . $reject;
                })
                ->rawColumns(['action'])
                ->make(true);
            // ->toJson();
        }
    }
}
