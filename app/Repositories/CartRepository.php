<?php

namespace App\Repositories;

use App\Repositories\Repository as BaseRepository;
use App\Models\Cart;

class CartRepository implements BaseRepository
{

    private Cart $model;

    public function __construct(Cart $cart)
    {
        $this->model = $cart;
    }

    public function findAll($type = null)
    {
        return $this->model->where('user_id', auth()->user()->id)->latest();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data, $condition = false)
    {
        foreach ($data as $row) {
            $this->model->create($row);
        }
        return true;
    }

    public function update(array $data, $id)
    {
        return $this->model::where('id', $id)->update($data);
    }

    public function destroy($id)
    {
        $condition = explode('_', $id);

        return $this->model->where('outlet_code', $condition[1])->where('product_code', $condition[0])->delete($id);
    }

    public function groupByOutlet()
    {
        $query = $this->model->select('outlet_code', 'outlet_name', 'outlet_address')
            ->where('user_id', auth()->user()->id)
            ->groupBy(['outlet_code', 'outlet_name', 'outlet_address'])
            ->get();

        // dd($query->groupBy('outlet_name'));

        return $query->groupBy('outlet_code');
    }

    public function findByOutlet($outletCode, $group = false)
    {
        $query = $this->model->where('user_id', auth()->user()->id)
            ->where('outlet_code', $outletCode)->get();

        // if($group) {
        //     $data = collect($query);

        //     $result = $data->groupBy('product_code')->mapWithKeys(function ($group, $key) {
        //         return [
        //             $key =>
        //                 [
        //                     'id' => $group->first()->id,
        //                     'product_code' => $key, // $key is what we grouped by, it'll be constant by each  group of rows
        //                     'product_name' => $group->first()->product_name,
        //                     'unit' => $group->first()->unit,
        //                     'qty' => $group->sum('qty'),
        //                 ]
        //         ];
        //     });

        //     return $result->values();
        // }


        // return $query;

        $data = collect($query);

        $result = $data->groupBy('product_code')->mapWithKeys(function ($group, $key) {
            return [
                $key =>
                [
                    'id' => $group->first()->id,
                    'outlet_code' => $group->first()->outlet_code,
                    'outlet_name' => $group->first()->outlet_name,
                    'outlet_address' => $group->first()->outlet_address,
                    'product_code' => $key, // $key is what we grouped by, it'll be constant by each  group of rows
                    'product_name' => $group->first()->product_name,
                    'unit' => $group->first()->unit,
                    'qty' => $group->sum('qty'),
                    'product_type' => $group->first()->product_type,
                    'product_picture' => $group->first()->product_picture
                ]
            ];
        });

        return $result->values();
    }

    public function deleteByOutlet($outlet)
    {
        return $this->model->where('outlet_code', $outlet)->where('user_id', auth()->user()->id)->delete();
    }
}
