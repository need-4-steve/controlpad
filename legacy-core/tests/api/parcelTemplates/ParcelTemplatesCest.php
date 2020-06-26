<?php
namespace parcelTemplates;
use \Step\Api\UserAuth;
use App\Models\ParcelTemplate;

class ParcelTemplatesCest
{
    public function _before(UserAuth $I)
    {
        $this->newParcel = [
            'name' => 'New Parcel',
            'length' => 3,
            'width' => 4,
            'height' => 2,
            'distance_unit' => 'in'
        ];

        $this->parcel = ParcelTemplate::create([
            'name' => 'Admin Parcel',
            'length' => 1.1,
            'width' => 2.1,
            'height' => 3.1,
            'distance_unit' => 'in',
            'user_id' => config('site.apex_user_id')
        ]);

        $this->repParcel = ParcelTemplate::create([
            'name' => 'Rep Parcel',
            'length' => 1.1,
            'width' => 2.1,
            'height' => 3.1,
            'distance_unit' => 'in',
            'user_id' => REP_ID
        ]);
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryCreateAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('POST', '/api/v1/parcels', $this->newParcel);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->newParcel);
    }

    public function tryUpdateAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $parcel = $this->parcel;
        $parcel->name = 'Codeception 2';
        $parcel->length = 3;
        $parcel->width = 4;
        $parcel->height = 5;
        $parcel->distance_unit = 'mm';
        $I->sendAjaxRequest('POST', '/api/v1/parcels/update', $parcel->toArray());
        $I->seeResponseCodeIs(200);
        unset($parcel->created_at);
        unset($parcel->updated_at);
        $I->seeResponseContainsJson($parcel->toArray());
    }

    public function tryShowAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/parcels/'.$this->parcel->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->parcel->name]);
    }

    public function tryDisableAsAdimin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('POST', '/api/v1/parcels/enable', ['id' => $this->parcel->id]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->parcel->name]);
    }

    public function tryDeleteAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('DELETE', '/api/v1/parcels/'.$this->parcel->id);
        $I->seeResponseCodeIs(200);
    }

    public function tryIndexAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/parcels');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->parcel->name]);
    }

    public function tryAllAsAdmin(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('GET', '/api/v1/parcels/all');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->parcel->name]);
    }

    public function tryRepEnable(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendAjaxRequest('POST', '/api/v1/parcels/rep-enable', ['id' => $this->parcel->id]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([]);
    }

    public function tryCreateAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', '/api/v1/parcels', $this->newParcel);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->newParcel);
    }

    public function tryUpdateAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $parcel = $this->repParcel;
        $parcel->name = 'Codeception 2';
        $parcel->length = 3;
        $parcel->width = 4;
        $parcel->height = 5;
        $parcel->distance_unit = 'mm';
        $I->sendAjaxRequest('POST', '/api/v1/parcels/update', $parcel->toArray());
        $I->seeResponseCodeIs(200);
        unset($parcel->created_at);
        unset($parcel->updated_at);
        $I->seeResponseContainsJson($parcel->toArray());
    }

    public function tryShowAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/parcels/'.$this->repParcel->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->repParcel->name]);
    }

    public function tryDisableAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('POST', '/api/v1/parcels/enable', ['id' => $this->repParcel->id]);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->repParcel->name]);
    }

    public function tryDeleteAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('DELETE', '/api/v1/parcels/'.$this->repParcel->id);
        $I->seeResponseCodeIs(200);
    }

    public function tryIndexAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/parcels');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->repParcel->name]);
    }

    public function tryAllAsRep(UserAuth $I)
    {
        $I->loginAsRep();
        $I->sendAjaxRequest('GET', '/api/v1/parcels/all');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['name' => $this->repParcel->name]);
    }
}
