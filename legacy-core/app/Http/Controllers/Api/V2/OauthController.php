<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\OauthTokenRepository;
use App\Services\Oauth\FacebookOauthService;

use Illuminate\Http\Request;

use App\Data\FacebookPersistantDataInterface;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

class OauthController extends Controller
{
    /* @var \App\Repositories\Eloquent\OauthTokenRepository */
    protected $oauthRepo;

    /* @var \App\Services\Oauth\FacebookOauthService */
    protected $facebookService;

    public function __construct(OauthTokenRepository $oauthRepo, FacebookOauthService $facebookService)
    {
        $this->oauthRepo = $oauthRepo;
        $this->facebookService = $facebookService;
    }

    public function connect()
    {
        $oauthUrls = collect(['facebook' => $this->facebookService->generateLoginUrl()]);

        return response()->json($oauthUrls);
    }
    /**
     * Disconnect the currently logged in account with an
     * account from a 3rd-party service.
     *
     * @method associate
     * @param  string    $driver facebook, instagram, etc
     * @return \Illuminate\Http\JsonResponse
     */
    public function disconnect($driver)
    {
        $oauthService = $this->resolveService($driver);
        $token = $oauthService->getLocalToken();
        $deleted = $this->oauthRepo->delete($token);

        $responsePayload = [
            'error' => false,
            'data'  => $deleted,
            'code'  => HTTP_SUCCESS,
        ];

        if (! $deleted) {
            $responsePayload['error'] = true;
            $responsePayload['data'] = 'Unable to delete token.';
            $responsePayload['code'] = HTTP_SERVER_ERROR;
        }

        return response()->json($responsePayload);
    }

    /**
     * Resolve which service we are using based off of the
     * string name for the driver.
     *
     * @method resolveService
     * @param  string         $driver The string name of the service
     * @return null|\App\Services\Oauth\OauthServiceInferface
     */
    private function resolveService($driver)
    {
        $returnService = null;
        $service = strtolower($driver);

        switch ($service) {
            case 'facebook':
                $returnService = $this->facebookService;
                break;
            default:
                break;
        }

        return $returnService;
    }
}
