<?php namespace App\Services\PayMan;

use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Services\PayMan\PayManCommon;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class PaymentService extends PayManCommon
{
    use CommonCrudTrait;

    public function __construct(
        AuthRepository $authRepo
    ) {
        $this->authRepo = $authRepo;
        $this->client = new \GuzzleHttp\Client();
    }

    /**
    * create the basic params for a payman request.
    *
    */
    public function getParams($request)
    {
        $query = [
            'page' =>isset($request['page']) ? $request['page'] : 1,
            'count' => isset($request['per_page']) ? $request['per_page'] : 15,
            'teamId' => 'rep'
        ];
        if (isset($request['start_date'])) {
            $query['startDate'] = Carbon::createFromFormat('Y-m-d', $request['start_date'], 'UTC')->setTimezone('UTC')->startOfDay();
        }
        if (isset($request['end_date'])) {
            $query['endDate'] = Carbon::createFromFormat('Y-m-d', $request['end_date'], 'UTC')->setTimezone('UTC')->endOfDay();
        }

        return $query;
    }
    /**
    * get the payment list for payquicker.
    *
    */
    public function paymentsList($request)
    {
        $query = $this->getParams($request);
        if (isset($request['status'])) {
            if ($request['status'] === 'all') {
            } else {
                $query['status'] = $request['status'];
            }
        }
        if (isset($request['submitted'])) {
            $query['submitted'] = $request['submitted'];
        }
        return $this->getWithQuery('payment-batches', ['query' => $query]);
    }

    /**
    * get the payment info for payquicker.
    *
    */
    public function payments($id, $request)
    {
        $query = $this->getParams($request);
        $query['paymentBatchId'] = $id;
        $paymentDetails = $this->getWithQuery('payments', ['query' => $query]);
        foreach ($paymentDetails['data'] as $index => $detail) {
            $user = User::where('id', $detail['userId'])->select('first_name', 'last_name')->first();
            $paymentDetails['data'][$index]['name'] = $user['full_name'];
        }
        return $paymentDetails;
    }
    /**
    * this is to have payman push payment info to  payquicker.
    *
    */
    public function submitPayment($batch_id)
    {
        return $this->get('payment-batches/' . $batch_id . '/submit');
    }
}
