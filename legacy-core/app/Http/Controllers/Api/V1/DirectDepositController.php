<?php

namespace App\Http\Controllers\api\v1;

use Illuminate\Http\Request;

use App\Http\Requests;
use Response;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\PayMan\DirectDepositService;
use App\Services\PayMan\PayManService;

class DirectDepositController extends Controller
{
    public function __construct(DirectDepositService $directDepositService, PayManService $payManService)
    {
        $this->directDepositService = $directDepositService;
        $this->payManService = $payManService;
    }
/**
 * Get the Batch index is the Direct Deposit batches for both open and
 * closed batches.
 *
 * A Direct Deposit Batch is a group of files that need to be deposited
 * into a Rep's bank account. If the file is open, it still needs to
 * have a NACH file made for it.
 *
 *
 */
    public function getBatchIndex()
    {
        $data = request()->all();
        $index = $this->directDepositService->batch($data);
        $pages = $this->payManService->payManPagination($index, $data);
        return $pages;
    }
/**
*  Post Batch Submitted moves a file from being open to closed.
*
*/
    public function postBatchSubmitted($id)
    {
        return $this->directDepositService->batchSubmitted($id);
    }
/**
* Get Detail shows the details for a specific batch.
* IE: transaction ID, amount, fees, card holder.
*
*/
    public function getDetail()
    {
        $data = request()->all();
        $details = $this->directDepositService->detail($data);
        $details = $this->payManService->payManPagination($details, $data);
        return response()->json($details, 200);
    }

    public function getValidations()
    {
        $data = request()->all();
        $validations = $this->directDepositService->getValidations($data);
        $validations = $this->payManService->payManPagination($validations, $data);
        return response()->json($validations, 200);
    }
/**
* Get user account index retrieves users accounts number and type, etc
*
*/
    public function getUserAccountIndex()
    {
        $data = request()->all();
        $account = $this->directDepositService->userAccounts($data);
        $pages = $this->payManService->payManPagination($account, $data);
        return $pages;
    }
/**
* This will Download the NACHA file. It creates a file for each auth
* user in the resources folder and then downloads it to their download folder.
*
*/
    public function getDownload($id)
    {
        $nacha = $this->directDepositService->download($id);
        $fileName = base_path("resources/assets/nacha/NACHA-".auth()->id().".txt");
        $file = fopen($fileName, "w");
                fwrite($file, $nacha);
                flush();
                fclose($file);
        return response()->download($fileName);
    }

    public function getBatchID($id)
    {
        return $this->directDepositService->batchId($id);
    }

    public function getValidatedUsers()
    {
        $users = $this->directDepositService->getValidatedUsers();
        return response()->json($users, HTTP_SUCCESS);
    }
}
