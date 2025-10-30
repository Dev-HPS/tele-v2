<?php

namespace App\Repositories;

interface Repository {

    public function findAll($type = null);
    public function findById($id);
    public function create(array $data, $condition = false);
    public function update(array $data, $id);
    public function destroy($id);
    
}