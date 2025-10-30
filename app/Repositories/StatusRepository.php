<?php

namespace App\Repositories;

use App\Repositories\Repository as BaseRepository;
use App\Models\Status;

class StatusRepository implements BaseRepository
{

    private Status $model;

    public function __construct(Status $status)
    {
        $this->model = $status;
    }

    public function findAll($type = null)
    {
        return $this->model->where('type_id', $type);
    }

    public function findEditTransaction($type = null)
    {
        return $this->model->whereIn('id', ['5', '6'])->where('type_id', $type);
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
        return $this->model->destroy($id);
    }
}
