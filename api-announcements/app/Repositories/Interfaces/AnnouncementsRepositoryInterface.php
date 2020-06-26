<?php

namespace App\Repositories\Interfaces;

interface AnnouncementsRepositoryInterface
{
    public function index();
    public function show($id);
    public function create($request);
    public function edit($id, $request);
    public function delete($id);
}
