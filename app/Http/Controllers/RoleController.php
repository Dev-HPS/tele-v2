<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\RoleRepository;

class RoleController extends Controller
{
    private RoleRepository $repository;

    public function __construct(RoleRepository $roleRepository) {
        $this->repository = $roleRepository;
    }

    public function index() {

    }

    public function store() {

    }
    
    public function show() {

    }

    public function update() {

    }

    public function destroy() {
        
    }
}
