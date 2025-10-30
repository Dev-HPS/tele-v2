<?php

namespace App\Repositories;

use App\Repositories\Repository as BaseRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserRepository implements BaseRepository
{

    private User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function findAll($type = null)
    {
        return $this->model->with('role')->withCount('userDetails')
            ->whereNot('username', 'offline')->latest();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data, $condition = false)
    {
        return DB::transaction(function () use ($data, $condition) {
            $user = $this->model->create($data);

            foreach ($data['residency'] as $key => $value) {
                $user->userDetails()->create([
                    'residency' => $value,
                ]);
            }

            return true;
        });
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $this->model::where('id', $id)->update([
                'username' => $data['username'],
                'name' => $data['name'],
                'sbu_code' => $data['sbu_code'],
                'role_id' => $data['role_id']
            ]);

            $user = $this->model->where('id', $id)->first();
            $user->userDetails()->delete();

            foreach ($data['residency'] as $value) {
                $user->userDetails()->create([
                    'residency' => $value
                ]);
            }

            return true;
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $user = $this->model->find($id);
            $user->delete();
            $user->userDetails()->delete();
        });
    }

    public function updatePassword(array $data, string $id)
    {
        return $this->model::query()->where('id', $id)->update([
            'password' => $data['password']
        ]);
    }
}
