<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParcelTemplateRequest;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\ParcelTemplateRepository;

class ParcelTemplateController extends Controller
{
    public function __construct(
        AuthRepository $authRepo,
        ParcelTemplateRepository $parcelTemplateRepo
    ) {
        $this->authRepo = $authRepo;
        $this->parcelTemplateRepo = $parcelTemplateRepo;
    }

    public function all()
    {
        $userId = $this->authRepo->getOwnerId();
        $parcels = $this->parcelTemplateRepo->all($userId);
        return response()->json($parcels, HTTP_SUCCESS);
    }

    public function create(ParcelTemplateRequest $parcelTemplateRequest)
    {
        $request = $parcelTemplateRequest->all();
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->create($request, $userId);
        return response()->json($parcel, HTTP_SUCCESS);
    }

    public function delete($id)
    {
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->find($id);
        if (!$parcel) {
            return response()->json('Could not find Parcel Template', HTTP_BAD_REQUEST);
        }
        if ($parcel->user_id !== $userId) {
            return response()->json('Unauthorized', 403);
        }
        $this->parcelTemplateRepo->delete($id);
        return response()->json('Parcel Template Deleted', HTTP_SUCCESS);
    }

    public function enable()
    {
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->find(request()->get('id'));
        if (!$parcel) {
            return response()->json('Could not find Parcel Template', HTTP_BAD_REQUEST);
        }
        if ($parcel->user_id !== $userId) {
            return response()->json('Unauthorized', 403);
        }
        $parcel = $this->parcelTemplateRepo->toggleEnable($parcel);
        return response()->json($parcel, HTTP_SUCCESS);
    }

    public function repEnable()
    {
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->find(request()->get('id'));
        if (!$parcel) {
            return response()->json('Could not find Parcel Template', HTTP_BAD_REQUEST);
        }
        if ($parcel->user_id !== $userId) {
            return response()->json('Unauthorized', 403);
        }
        $parcel = $this->parcelTemplateRepo->toggleRepEnable($parcel);
        return response()->json($parcel, HTTP_SUCCESS);
    }

    public function index()
    {
        $userId = $this->authRepo->getOwnerId();
        $parcels = $this->parcelTemplateRepo->index($userId);
        return response()->json($parcels, HTTP_SUCCESS);
    }

    public function update(ParcelTemplateRequest $parcelTemplateRequest)
    {
        $request = $parcelTemplateRequest->all();
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->find($request['id']);
        if (!$parcel) {
            return response()->json('Could not find Parcel Template', HTTP_BAD_REQUEST);
        }
        if ($parcel->user_id !== $userId) {
            return response()->json('Unauthorized', 403);
        }
        $parcel = $this->parcelTemplateRepo->update($parcel, $request);
        return response()->json($parcel, HTTP_SUCCESS);
    }

    public function show($id)
    {
        $userId = $this->authRepo->getOwnerId();
        $parcel = $this->parcelTemplateRepo->find($id);
        if (!$parcel) {
            return response()->json('Could not find Parcel Template', HTTP_BAD_REQUEST);
        }
        if ($parcel->user_id === $userId
            or $parcel->user_id === config('site.apex_user_id')
            and $parcel->disabled_at === null
        ) {
            return response()->json($parcel, HTTP_SUCCESS);
        }
        return response()->json('Unauthorized', 403);
    }
}
