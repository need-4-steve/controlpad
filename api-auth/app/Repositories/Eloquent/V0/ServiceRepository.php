<?php

namespace App\Repositories\Eloquent\V0;

use App\Repositories\Interfaces\ServiceInterface;
use App\Service;

class ServiceRepository implements ServiceInterface {

    public function index($admin=false) {
        $service = $admin ? new Service : Service::where('client_visible', true);
        return $service->simplePaginate(200);
    }

    public function show($id, $admin=false) {
        $service = $admin ? new Service : Service::where('client_visible', true);
        return $service->where('id', $id)->first();
    }

    public function create($name) {
        $tenant = new Service;
        $tenant->name = $name;
        $tenant->save();
        return $tenant;
    }

    public function update($id, $request) {
        return Service::where('id', $id)->update($request);
    }

    public function delete($id) {
        return Service::where('id', $id)->delete();
    }

}
