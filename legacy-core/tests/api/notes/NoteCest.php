<?php
namespace notes;

use \ApiTester;
use \App\Models\User;
use \App\Models\Note;
use \Step\Api\UserAuth;

class NoteCest
{
    private $user;
    
    public function _before(UserAuth $I)
    {
        $this->user = User::where('role_id', 5)->first();
    }

    public function _after(UserAuth $I)
    {
    }

    // tests
    public function tryToGetIndex(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('GET', '/api/v1/notes/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108]);
    }

    public function tryToGetRelatedNotes(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('GET', '/api/v1/notes/related-notes/' .$this->user->id . '/user');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(['noteable_id' => $this->user->id]);
    }

    public function tryToPostCreate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $request = [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'User',
            'user_id' => 108
            ];
        $I->sendAjaxRequest('POST', '/api/v1/notes/create', $request);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
    }

    public function tryToDeleteDelete(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('DELETE', '/api/v1/notes/delete/' . $this->user->id);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([0 => 'Successfully deleted.']);
    }
}
