<?php

namespace App\Repositories\Interfaces;

use Illuminate\Http\Request;

interface ReservationInterface
{
    public function index(Request $params);
    public function show($reservationGroupId);
    public function create($request);
    public function update($reservationGroupId, $inventories);
    public function destroy($reservationGroupId);
}
