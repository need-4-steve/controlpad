<?php namespace App\Http\Controllers\Api\V1;

use League\Csv\Writer;
use App\Http\Controllers\Controller;
use App\Http\Requests\BankUpdateRequest;
use App\Services\PayMan\PayManService;
use Illuminate\Http\Response;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Storage;

class BankController extends Controller
{
    public function __construct(PayManService $payMan)
    {
        $this->paymentManager = $payMan;
    }

   /**
   * This is to show a user banking information.
   * @param int $user_id
   * @return Response
   */
    public function show($user_id)
    {
        if (auth()->check()) {
            $request = request()->all();
            $banking = $this->paymentManager->getBanking(auth()->id());
            if (isset($request['number'])) {
                $number = $banking['number'];
            } else {
                $number = substr($banking['number'], -4);
            }
            $banking = [
                'account' => $number,
                'validated' => $banking['validated'],
                'type' => $banking['type'],
                'routing' => $banking['routing']
            ];
            return response()->json($banking, 200);
        }
        return response()->json("Not authorized.", 401);
    }

    /**
    * This is to generate a CSV of all user's banking information.
    * @param int $user_id
    * @return Response
    */
    public function downloadCsvAllUsers()
    {
        $bankingResponse = $this->paymentManager->getBankingAllUsers();
        // throw error if errored out
        if (!isset($bankingResponse['totalPage'])) {
            return response()->json('Error when attempting to get banking data', 400);
        }
        $totalPages = $bankingResponse['totalPage'];
        $bankingData = $bankingResponse['data'];
        for ($page = 2; $page <= $totalPages; $page++) {
            $bankingResponse = $this->paymentManager->getBankingAllUsers($page);
            $bankingData = array_merge($bankingData, $bankingResponse['data']);
        }

        $csvHeaders = [
            'User ID',
            'User Name',
            'Routing Number',
            'Account Number',
            'Type',
            'Validated'
        ];

        $bankingCsv = [];
        foreach ($bankingData as $bankingRow) {
            $bankingCsv[] = [
                $bankingRow['userId'],
                $bankingRow['name'],
                // saves leading 0s in numbers
                '="' . $bankingRow['routing'] . '"',
                '="' . $bankingRow['number'] . '"',
                $bankingRow['type'],
                $bankingRow['validated'] ? 'true' : 'false'
            ];
        }

        // all data captured and formatted, generate a csv
        $fileName = 'banking-all-users.csv';
        $csv = $this->createCSVDownload($fileName, $csvHeaders, $bankingCsv);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    /**
    * This is to create or update a user banking information.
    * @param array $data The data need to contain $data = [
    * @return Response
    */
    public function createCSVDownload($fileName, $dataHeaders, $csvData)
    {
        $dirName  = $fileName.'/'.date('Y-m-d');
        $savedFileDir = storage_path().'/temp/'.$dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv';
        $header = ['Content-Type' => 'application/octet-stream'];
        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne($dataHeaders);
        foreach ($csvData as $csvRow) {
            $csv->insertOne($csvRow);
        }
        Storage::disk('public')->put($dirName.'/'.$fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $csv);
        return [$savedFileDir, $fileName.'_as_of_'.date('Y-m-d_H_i_A').'.csv', $header];
    }

    /**
    * This is to create or update a user banking information.
    * @param array $data The data need to contain $data = [
    *   "name" => "Account Name",
    *   "routing" => "324377516",
    *   "number" => "123456789",
    *   "type" => "checking",
    *   "bankName" =>"Some Bank",
    *   "authorization" => true]
    * @return Response
    */
    public function updateCreate(BankUpdateRequest $request)
    {
        $data = request()->all();

        if (!(isset($data['routing']) && $this->validateRouting($data['routing']))) {
            return response()->json('Invalid or no routing number provided.', 400);
        }

        $user_id = auth()->id();
        $bank = $this->paymentManager->updateOrCreateBanking($user_id, $data);
        if ($bank === false || (is_array($bank) && isset($bank['errors']))) {
            return response()->json('There was an error when attempting to '
                                    . 'add bank information.  Please verify'
                                    . ' and try again.', 400);
        }

        $bankAccount = $this->paymentManager->getBanking($user_id);
        return response()->json($bankAccount);
    }

    /**
     * Validate a routing number
     *
     * @return boolean
     */
    public function validateRouting($routingNumber = 0)
    {
        $routingNumber = preg_replace('[\D]', '', $routingNumber); //only digits
        if (strlen($routingNumber) != 9) {
            return false;
        }

        $checkSum = 0;
        for ($i = 0, $j = strlen($routingNumber); $i < $j; $i+= 3) {
            //loop through routingNumber character by character
            $checkSum += ($routingNumber[$i] * 3);
            $checkSum += ($routingNumber[$i+1] * 7);
            $checkSum += ($routingNumber[$i+2]);
        }

        if ($checkSum != 0 and ($checkSum % 10) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * This is used to verify that we have the banking info right, after an account
    * has been created or update the user will receive two small deposits that is
    * used to make sure that we have the right account.
    *
    * @param String $userId
    * @param int $amount1
    * @param int $amount2
    * @return Response
    */
    public function verify()
    {
        $data = request()->all();
        $user_id = auth()->user()->id;
        $result = $this->paymentManager->verifyBanking($user_id, $data);

        if ($result['success']) {
            return response()->json($result['description'], 200);
        } else {
            return response()->json($result['description'], 400);
        }
    }

    /**
    * This is used to verify that we have a billing address that is correctly
    * set.  Without a billing address we can't update a credit card.
    *
    * @param array $billingAddress
    * @return Response
    */
    public function checkBillingForCardUpdate(AddressRequest $billingAddress)
    {
        // we ran the above request object and it worked, return true
        return response()->json(true, 200);
    }
}
