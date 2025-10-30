<?php

namespace App\Repositories;

use App\Repositories\Repository as BaseRepository;
use App\Models\Role;

class RoleRepository implements BaseRepository
{

    private Role $model;

    public function __construct(Role $role)
    {
        $this->model = $role;
    }

    public function findAll($type = null)
    {
        return $this->model->whereNot('name', 'Admin')->get();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data, $condition = false)
    {
        return $this->model->create($data);
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
