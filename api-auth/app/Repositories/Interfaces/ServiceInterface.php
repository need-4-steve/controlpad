<?php

namespace App\Repositories\Interfaces;

interface ServiceInterface {
    public function index($admin=false);
    public function show($id);
    public function create($name);
    public function update($id, $request);
    public function delete($id);
}
