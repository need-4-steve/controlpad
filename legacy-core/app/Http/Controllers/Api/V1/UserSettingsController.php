<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserSettingsUpdateRequest;
use App\Http\Requests\SubscriptionCardRequest;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\CustomPageRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\PayMan\PayManService;
use Input;
use Response;

class UserSettingsController extends Controller
{
    public function __construct(
        UserRepository $userRepo,
        AuthRepository $authRepo,
        UserSettingsRepository $userSettingsRepo,
        PayManService $payman,
        CustomPageRepository $customPageRepo
    ) {
        $this->userRepo = $userRepo;
        $this->authRepo = $authRepo;
        $this->userSettingsRepo = $userSettingsRepo;
        $this->payman = $payman;
        $this->customPageRepo = $customPageRepo;
    }

    /**
     * Update a user's settings
     *
     * @return userSettings
     */
    public function update(UserSettingsUpdateRequest $request)
    {
        if ($this->userSettingsRepo->update($request)) {
            cache()->forget('user-settings-'.auth()->id());
            return response()->json($request, 200);
        } else {
            return response()->json(
                ['Error creating user setting'],
                402
            );
        }
    }

    /**
     * Show a user's settings
     *
     * @param int user_id
     * @return userSettings
     */
    public function show($user_id)
    {
        // if we aren't an admin and we aren't grabbing our own settings
        if (!$this->authRepo->isOwnerAdmin()
            && ($this->authRepo->getOwnerId() != $user_id)
        ) {
            return response()->json(
                [$this->messages['Unauthorized']],
                403
            );
        }

        return response()->json($this->userSettingsRepo->show($user_id), 200);
    }

    /**
    * To save token for credit Card
    *
    * @param object $request
    * @return $token
    *
    */
    public function createToken(SubscriptionCardRequest $subscriptionRequest)
    {
        $request = $subscriptionRequest->all();
        $newToken = $this->payman->cardToken($request, auth()->id());

        if (!is_array($newToken) || !$newToken['success']) {
            return response()->json(['error' => true,
                                    'payman_message' => 'Error updating the card token, may be an invalid card.'], 400);
        } else {
            $this->userSettingsRepo->newOrUpdateCardToken($newToken, $this->authRepo->getOwnerId(), $request['type']);
            return response()->json($newToken, 200);
        }
    }

    /**
    * To show info for credit Card
    *
    * @param
    * @return
    *
    */
    public function cardInfoShow()
    {
        if (auth()->check()) {
            return $this->userSettingsRepo->showCardInfo($this->authRepo->getOwnerId());
        } else {
            return response()->json(
                ['Unauthorized'],
                403
            );
        }
    }

    public function deleteCardInfo($id)
    {
        $isDeleted = $this->userSettingsRepo->deleteCardInfo($id, $this->authRepo->getOwnerId());
        if ($isDeleted) {
            return response()->json(
                ['Card information deleted.'],
                200
            );
        }
        return response()->json(
            ['You can not delete this card'],
            403
        );
    }
    public function mySettings($mySettingsRequest)
    {

        if (auth()->user()->hasRole(['Rep']) && !$this->userRepo->checkIfAcceptedTerms()) {
            $terms = $this->customPageRepo->renderPage('rep-terms');
            $termsAccepted = false;
        } else {
            $terms = null;
            $termsAccepted = true;
        }
        $rep_settings = ['terms' => $terms, 'termsAccepted' => $termsAccepted];
        return response()->json($rep_settings);
    }
}
