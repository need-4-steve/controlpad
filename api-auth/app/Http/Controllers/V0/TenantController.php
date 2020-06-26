<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Interfaces\TenantInterface;
use App\Tenant;

class TenantController extends Controller
{
    public function __construct(TenantInterface $TenantRepo) {
        $this->TenantRepo = $TenantRepo;
    }


    public function index() {
        return response()->json($this->TenantRepo->index());
    }

    public function show($org_id) {
        return response()->json($this->TenantRepo->show($org_id));
    }

    public function create(Request $request) {
        $this->validate($request, [
            'name' => 'required',
            'read_host' => 'required',
            'write_host' => 'required',
            'db_name' => 'required',
            'domain' => 'required'
        ]);
        return response()->json($this->TenantRepo->create(
                $request->input('name'),
                $request->input('read_host'),
                $request->input('write_host'),
                $request->input('db_name'),
                $request->input('domain')
            ));
    }

    public function update($id, Request $request) {
        $this->validate($request, Tenant::$updateRules);
        $updated = $this->TenantRepo->update($id, $request->only(Tenant::$updateFields));
        if (!$updated) {
            return response()->json(['error' => 'Could not find a tenant with an ID of ' . $id], 404);
        }
        return response()->json($this->TenantRepo->show($id));
    }

    public function delete($id) {
        $deleted = $this->TenantRepo->delete($id);
        if (!$deleted) {
            return response()->json(['error' => 'Could not find a tenant with an ID of ' . $id], 404);
        }
        return response()->json('Success.');
    }
}
