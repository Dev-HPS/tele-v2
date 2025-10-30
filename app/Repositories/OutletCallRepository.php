<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\OutletCall;
use App\Services\OutletCallLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class OutletCallRepository
{

    private OutletCall $model;

    public function __construct(OutletCall $outletCall)
    {
        $this->model = $outletCall;
    }

    public function findAll($request = null)
    {
        $role = auth()->user()->role->id;

        $query = $this->model::query()
            ->select('outlet_calls.*')
            ->orderBy('outlet_name', 'asc')
            ->when($role == '05836b4d-3e6b-4e44-bce3-f94d05ac4b7d' || $role == 'dc9f2cb5-258e-4039-9b6f-6025a6ae389e', function ($query) {
                $query->where('sbu_code', auth()->user()->sbu_code);
            })
            ->whereIn('status', [0, 1]);

        // Apply filters if provided
        if ($request) {
            if ($request->has('sbu_filter') && $request->sbu_filter != 'All') {
                $query->where('sbu_code', $request->sbu_filter);
            }

            if ($request->has('tp_filter') && $request->tp_filter != 'All') {
                $query->where('tp', $request->tp_filter);
            }

            if ($request->has('city_filter') && $request->city_filter != 'All') {
                $query->where('city', $request->city_filter);
            }

            if ($request->has('district_filter') && $request->district_filter != 'All') {
                $query->where('district', $request->district_filter);
            }

            if ($request->has('day_filter') && $request->day_filter != 'All') {
                $query->where('day', $request->day_filter);
            }

            // Handle duplicate filter
            if ($request->has('duplicate_filter') && $request->duplicate_filter != 'All') {
                if ($request->duplicate_filter == 'duplicate') {
                    // Show only duplicates - outlets that appear more than once on the selected day
                    $dayFilter = $request->has('day_filter') && $request->day_filter != 'All' ? $request->day_filter : null;

                    if ($dayFilter) {
                        $query->whereIn('outlet_code', function ($subQuery) use ($dayFilter) {
                            $subQuery->select('outlet_code')
                                ->from('outlet_calls')
                                ->where('day', $dayFilter)
                                ->whereIn('status', [0, 1])
                                ->groupBy('outlet_code', 'outlet_name')
                                ->havingRaw('COUNT(*) > 1');
                        });
                    }
                } elseif ($request->duplicate_filter == 'unique') {
                    // Show only unique - outlets that appear exactly once on the selected day
                    $dayFilter = $request->has('day_filter') && $request->day_filter != 'All' ? $request->day_filter : null;

                    if ($dayFilter) {
                        $query->whereIn('outlet_code', function ($subQuery) use ($dayFilter) {
                            $subQuery->select('outlet_code')
                                ->from('outlet_calls')
                                ->where('day', $dayFilter)
                                ->whereIn('status', [0, 1])
                                ->groupBy('outlet_code')
                                ->havingRaw('COUNT(*) = 1');
                        });
                    }
                }
            }

            // Handle empty/null filter
            if ($request->has('empty_filter') && $request->empty_filter != 'All') {
                $field = $request->empty_filter;
                $query->where(function ($q) use ($field) {
                    $q->whereNull($field)
                        ->orWhere($field, '')
                        ->orWhere($field, 'like', '-%');
                });
            }
        }

        return $query->latest();
    }

    public function findApprove()
    {
        return $this->model::query()
            ->select('outlet_calls.*')->where('status', 0)->latest();
    }

    public function approve($id)
    {
        $data = $this->model->find($id);
        $oldData = $data->toArray();

        if ($data->type == 'Tambah') {
            $result = $this->model->where('id', $id)->update(['status' => 1, 'updated_at' => now()]);

            // Log approve action
            $newData = $this->model->find($id)->toArray();
            OutletCallLogService::logApprove($id, $oldData, $newData, 'Data outlet call disetujui');

            return $result;
        } else {
            $result = $this->model->where('id', $id)->update(['status' => 2, 'updated_at' => now()]);

            // Log approve delete action
            $newData = $this->model->find($id)->toArray();
            OutletCallLogService::logApprove($id, $oldData, $newData, 'Penghapusan data outlet call disetujui');

            return $result;
        }
    }

    public function reject($request)
    {
        $data = $this->model->find($request->id);
        $oldData = $data->toArray();

        $result = $this->model->where('id', $request->id)->update([
            'status' => 2,
            'description' => $request->description,
            'updated_at' => now()
        ]);

        // Log reject action
        OutletCallLogService::logReject($request->id, $oldData, $request->description);

        return $result;
    }

    public function destroy($request)
    {
        $data = $this->model->find($request->id);
        $oldData = $data->toArray();

        $result = $this->model->where('id', $request->id)->update([
            'status' => 0,
            'type' => 'Hapus',
            'reason' => $request->reason,
        ]);

        // Log delete action
        OutletCallLogService::logDelete($request->id, $oldData, $request->reason);

        return $result;
    }

    public function callTp()
    {
        $today = Carbon::now()->translatedFormat('l');

        $query = $this->model
            ->select(['tp', 'tp_name', DB::raw('COUNT(tp) as call')])
            ->where('day', $today)
            ->where('status', 1)
            ->groupBy(['tp', 'tp_name']);

        if (!is_null(Auth::user()->sbu_code)) {
            $query->where('sbu_code', Auth::user()->sbu_code);
        }

        return $query->get();
    }

    public function callTpByUser($userId)
    {
        $today = Carbon::now()->translatedFormat('l');


        $query = $this->model
            ->select(['outlet_calls.tp', 'outlet_calls.tp_name', DB::raw('COUNT(outlet_calls.tp) as call')])
            ->join('user_tp', 'outlet_calls.tp', '=', 'user_tp.tp')
            ->where('user_tp.user_id', $userId)
            ->where('outlet_calls.day', $today)
            ->where('outlet_calls.status', 1)
            ->groupBy(['outlet_calls.tp', 'outlet_calls.tp_name']);

        if (!is_null(Auth::user()->sbu_code)) {
            $query->where('outlet_calls.sbu_code', Auth::user()->sbu_code);
        }

        return $query->get();
    }

    public function callCity($request)
    {
        $today = Carbon::now()->translatedFormat('l');

        $data = $this->model->select(['city', 'city_name', DB::raw('COUNT(city) as call')])->where('tp', $request->tp)->where('status', 1)->groupBy(['city', 'city_name'])->where('day', $today)->get();

        return $data;
    }

    public function paramOutlet($city)
    {
        $today = Carbon::now()->translatedFormat('l');

        return implode(
            ',',
            $this->model
                ->where('city', $city)
                ->where('status', 1)
                ->where('day', $today)
                ->pluck('outlet_code')
                ->toArray()
        );
    }

    public function store($request)
    {
        foreach ($request->outlet_code as $outletJson) {
            $outlet = json_decode($outletJson, true);

            $outletCall = $this->model->where('outlet_code', $outlet['outlet_code'])->where('day', $request->day)->where('status', 1)->first();

            if ($outletCall) {
                continue;
            }

            $data = [
                'outlet_code' => $outlet['outlet_code'],
                'outlet_name' => $outlet['outlet_name'],
                'outlet_owner' => $outlet['outlet_pkp'],
                'outlet_phone' => $outlet['no_telp'],
                'outlet_address' => $outlet['outlet_alamat_fp'],
                'tp' => $outlet['tp'],
                'tp_name' => $outlet['tp_name'],
                'residency' => $outlet['residency'],
                'residency_name' => $outlet['residency_name'],
                'city' => $outlet['city'],
                'city_name' => $outlet['city_name'],
                'district' => $outlet['district'],
                'district_name' => $outlet['district_name'],
                'day' => $request->day,
                'status' => 0,
                'sbu_code' => $request->sbu_code ?? Auth::user()->sbu_code,
                'type' => 'Tambah',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $createdOutlet = $this->model->create($data);

            // Log create action
            OutletCallLogService::logCreate($createdOutlet->id, $data, 'Menambah outlet call baru: ' . $outlet['outlet_name']);
        }
    }
}
