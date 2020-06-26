<?php

namespace App\Http\Controllers\V0;

use App\Http\Controllers\Controller;
use App\Models\TaxConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class TaxConnectionController extends Controller
{

    public function index(Request $request)
    {
        $this->validate($request, ['page' => 'required|integer']);
        $params = $request->only(TaxConnection::$indexParams);

        $queryBuilder = TaxConnection::select('*');
        if (isset($params['merchant_id'])) {
            $queryBuilder->where('merchant_id', $params['merchant_id']);
        }
        if (isset($params['type'])) {
            $queryBuilder->where('type', $params['type']);
        }
        if (isset($params['active'])) {
            $queryBuilder->where('active', filter_var($params['active'], FILTER_VALIDATE_BOOLEAN));
        }
        $responseBody = $queryBuilder->paginate(isset($params['per_page']) ? $params['per_page'] : 15);

        return response()->json($responseBody);
    }

    public function create(Request $request)
    {
        $this->validate($request, TaxConnection::$createRules);
        $newConnection = new TaxConnection($request->only(TaxConnection::$createFields));

        $service = $newConnection->getService();
        $this->validate($request, $service->getCredentialValidationArray());
        if (!$service->validateCredentials()) {
            return response()->json(['credentials' => ['Invalid']], 422);
        }

        $newConnection->save();

        if ($newConnection->active) {
            // Deactivate old connections
            $this->deactiveOtherMerchantConnections($newConnection->merchant_id, $newConnection->id);
        }
        return response()->json($newConnection, 201);
    }

    public function show($id)
    {
        return response()->json(TaxConnection::where('id', $id)->first());
    }

    public function update($id, Request $request)
    {
        $taxConnection = $request->only(TaxConnection::$updateFields);

        $currentConnection = TaxConnection::where('id', $id)->first();
        $activatingConnection = (isset($taxConnection['active']) && $taxConnection['active'] && !$currentConnection->active);
        $shouldValidateAuth = $activatingConnection;

        if (isset($taxConnection['credentials'])) {
            // validate auth
            $shouldValidateAuth = true;
            // Check that the account is the same
            if (!$currentConnection->getService()->isAccountSame($taxConnection)) {
                return response()->json(['error' => 'Account must be the same for update'], 400);
            }
             // So we can validate them and return them
            $currentConnection->credentials = json_decode(json_encode($taxConnection['credentials']));
             // For the db
            $taxConnection['credentials'] = base64_encode(Crypt::encrypt(json_encode($taxConnection['credentials'])));
        }

        if ($shouldValidateAuth && !$currentConnection->getService()->validateCredentials()) {
            return response()->json(['credentials' => ['Invalid']], 422);
        }
        // Update database
        TaxConnection::where('id', $id)->update($taxConnection);

        if ($activatingConnection) {
            // Deactivate old connections if we activated
            // Performed after new connection is ready to prevent connect loss
            $this->deactiveOtherMerchantConnections($currentConnection->merchant_id, $id);
        }
        $currentConnection->active = $taxConnection['active'];
        return response()->json($currentConnection);
    }

    private function deactiveOtherMerchantConnections($merchantId, $activeId)
    {
        TaxConnection::where('merchant_id', $merchantId)->where('id', '<>', $activeId)->update(['active' => false]);
    }
}
