<?php
namespace App\Services\PayMan;

use App\Models\Order;
use App\Models\User;
use GuzzleHttp;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Services\PayMan\PayManCommon;

class DirectDepositService extends PayManCommon
{
    use CommonCrudTrait;

    public function __construct()
    {
    }

    public function batch($data)
    {
        $query = [
            'query' => [
                'page' =>       $data['page'],
                'count' =>      $data['per_page'],
                'startDate' =>  $data['start_date'],
                'endDate' =>    $data['end_date'],
                'submitted' =>  $data['submitted'],
            ]
        ];

         return $this->getWithQuery('payout-files', $query);
    }

    public function batchSubmitted($id)
    {
        return $this->get('payout-files/'.$id.'/mark-submitted');
    }

    public function detail($data)
    {
        $query = [
            'query' => [
                'page' =>           $data['page'],
                'count' =>          $data['per_page'],
                'paymentFileId' =>   $data['paymentFileId'],
            ]
        ];
        $processingFee = $this->getWithQuery('payments', $query);
        if ($processingFee['data'] === null) {
            return [
                'total' => $processingFee['total'],
                'totalPage' => $processingFee['totalPage'],
                'data' => null
            ];
        }
        $details =[];

        $userid = collect($processingFee['data'])->pluck('userId');
        $users = User::whereIn('id', $userid)->get();
        foreach ($processingFee['data'] as $detail) {
            $user = $users->where('id', $detail['userId'])->first();
            if ($user !== null) {
                $detail['user']['repId'] = $detail['userId'];
                $detail['user']['name']= $user['first_name'] . ' ' . $user['last_name'];
            }
            $details [] = $detail;
        }
        return [
            'total' => $processingFee['total'],
            'totalPage' => $processingFee['totalPage'],
            'data' => $details
        ];
    }

    public function batchId($id)
    {
        return $this->get('payout-files/'. $id);
    }

    public function userAccounts($data)
    {
        $query = [
        'query' => [
            'page'  =>     $data['page'],
            'count' =>      $data['per_page']]
        ];
        $userAccounts = $this->getWithQuery('user-accounts', $query);
        return $userAccounts;
    }

    public function getValidations($data)
    {
        $query = [
            'query' => [
                'page' => $data['page'],
                'count' => $data['per_page'],
                'paymentFileId' => $data['payment_file_id']
            ]
        ];
        $validations = $this->getWithQuery('user-account-validations', $query);

        $userids = collect($validations['data'])->pluck('userId');
        $users = User::select('id', 'first_name', 'last_name')->whereIn('id', $userids)->get();
        foreach ($validations['data'] as &$validation) {
            $user = $users->where('id', intval($validation['userId']))->first();
            if ($user !== null) {
                $validation['userName']= $user['first_name'] . ' ' . $user['last_name'];
            }
        }
        return $validations;
    }

    public function download($id)
    {
        $status = explode('/', url()->previous());
        if ($status[4] === 'closed-detail') {
            $query = [
                'query' => [
                ]
            ];
        } else {
            $query = [
                'query' => [
                    'achId'  => 1
                ]
            ];
        }
        return $this->getWithQueryNoJson('payout-files/'. $id .'/file', $query);
    }

    public function getValidatedUsers()
    {
        return $this->getWithQuery('/user-accounts/list-validated-users', null);
    }
}
