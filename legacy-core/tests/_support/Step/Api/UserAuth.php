<?php namespace Step\Api;

use CPCommon\Jwt\Jwt;

class UserAuth extends \ApiTester
{
    public function loginAsSuperadmin()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'superadmin@controlpad.com', 'password' => 'password2']);
        $token = $I->grabDataFromResponseByJsonPath('$.cp_token')[0];
        $claims = Jwt::verify($token, env('JWT_SECRET'));
        $orgId = $claims['orgId'];
        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->haveHttpHeader('X-Cp-Org-Id', $orgId);
    }

    public function loginAsAdmin()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'admin@controlpad.com', 'password' => 'password2']);
        $token = $I->grabDataFromResponseByJsonPath('$.cp_token')[0];
        $claims = Jwt::verify($token, env('JWT_SECRET'));
        $orgId = $claims['orgId'];
        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->haveHttpHeader('X-Cp-Org-Id', $orgId);
    }

    public function loginAsRep()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'rep@controlpad.com', 'password' => 'password2']);
        $token = $I->grabDataFromResponseByJsonPath('$.cp_token')[0];
        $claims = Jwt::verify($token, env('JWT_SECRET'));
        $orgId = $claims['orgId'];
        $I->haveHttpHeader('Authorization', 'Bearer ' . $token);
        $I->haveHttpHeader('X-Cp-Org-Id', $orgId);
    }

    public function loginAsSuperadminForAcceptanceTest()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'superadmin@controlpad.com', 'password' => 'password2']);
    }

    public function loginAsAdminForAcceptanceTest()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'admin@controlpad.com', 'password' => 'password2']);
    }

    public function loginAsRepForAcceptanceTest()
    {
        $I = $this;
        $I->sendAjaxPostRequest('/api/external/authenticate', ['email' => 'rep@controlpad.com', 'password' => 'password2']);
    }

    public function createInventory($userId = 1)
    {
        \DB::beginTransaction();
        $inventory = factory(\App\Models\Inventory::class, 'withRelations')->create([
            'quantity_available' => 100,
            'user_id' => $userId,
            'owner_id' => $userId,
        ]);
        $item = $inventory->item;
        factory(\App\Models\Price::class, 'wholesale')->create([
            'priceable_id' => $item->id
        ]);
        factory(\App\Models\Price::class, 'retail')->create([
            'priceable_id' => $item->id
        ]);
        factory(\App\Models\Price::class, 'premium')->create([
            'priceable_id' => $item->id
        ]);
        factory(\App\Models\Price::class, 'inventory')->create([
            'priceable_id' => $inventory->id
        ]);
        \DB::commit();
        return $inventory;
    }
}
