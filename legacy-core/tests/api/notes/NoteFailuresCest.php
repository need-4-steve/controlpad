<?php
namespace notes;

use \ApiTester;
use \App\Models\User;
use \Step\Api\UserAuth;

class NoteFailuresCest
{
    private $user;

    public function _before(ApiTester $I)
    {
         $this->user = User::where('role_id', 5)->first();
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToGetIndex(UserAuth $I)
    {
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('GET', '/api/v1/notes/');
        $I->seeResponseCodeIs(401);
    }

    public function tryToGetRelatedNotes(UserAuth $I)
    {
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('GET', '/api/v1/notes/related-notes/' .$this->user->id . '/user');
        $I->seeResponseCodeIs(401);
    }

    public function tryToPostCreate(UserAuth $I)
    {
        $request = [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'User',
            'user_id' => 108
            ];
        $I->sendAjaxRequest('POST', '/api/v1/notes/create', $request);
        $I->seeResponseCodeIs(401);
    }

    public function tryToDeleteDelete(UserAuth $I)
    {
        $I->haveRecord('notes', [
            'body' => 'Here is a note',
            'noteable_id' => $this->user->id,
            'noteable_type' => 'App\Models\User',
            'user_id' => 108
            ]);
        $I->sendAjaxRequest('DELETE', '/api/v1/notes/delete/' . $this->user->id);
        $I->seeResponseCodeIs(401);
    }
}
