<?php

namespace App\Repositories\Interfaces;

interface EventsRepositoryInterface
{
    public function index($params);
    public function show($id);
    public function create($params);
    public function edit($id, $ownerId, $params);
    public function delete($id, $ownerId);
}
