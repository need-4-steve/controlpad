<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionUser;
use App\Models\User;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\SubscriptionRepository;
use App\Services\Subscription\SubscriptionService;
use App\Services\Csv\CsvService;
use App\Services\Tax\TaxService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\SubscriptionPlanRequest;

class SubscriptionController extends Controller
{
    protected $subscriptionRepo;

    public function __construct(
        AuthRepository $authRepo,
        SubscriptionRepository $subscriptionRepo,
        SubscriptionService $subscriptionService,
        CsvService $csvService
    ) {
        $this->authRepo = $authRepo;
        $this->subscriptionRepo = $subscriptionRepo;
        $this->subscriptionService = $subscriptionService;
        $this->CsvService = $csvService;
        $this->settings = app('globalSettings');
    }
/**
 * This is all of the plans with price of each plan.
 *
 *
 */
    public function allSubscriptions()
    {
        return Subscription::with('price')->paginate(15);
    }

/**
 * This is all of the users subscription with the user, price and subscription.
 *
 */
    public function userSubscriptions(Request $request)
    {
        $userSubscriptions = $this->subscriptionRepo->allUserSubscriptions($request);
        foreach ($userSubscriptions as $subscription) {
            $subscription->billing_date = ($subscription->ends_at == null ? null : $subscription->ends_at->format('Y-m-d'));
            $count = count($subscription->attempts);
            if ($count > 0) {
                $subscription->description = $subscription->attempts[$count-1]->description;
            }
        }
        return $userSubscriptions;
    }

/**
 * This is all of the plans and they are paginated.
 *
 */
    public function index()
    {
        return $this->subscriptionRepo->paginate();
    }

/**
 * This creates a new plan.
 *
 */
    public function create(SubscriptionPlanRequest $request)
    {
        $request['plan_price'] = $request->input('price.price');
        $subscription = $this->subscriptionRepo->create($request->all());
        if (! $subscription) {
            $response = $this->createResponse(
                true,
                HTTP_BAD_REQUEST,
                'Failed to create subscription.',
                $subscription
            );
            return $response;
        }

        $response = $this->createResponse(
            false,
            200,
            'Subscription created.',
            $subscription
        );
        return $response;
    }

/**
 * This is the auth user's plan with price. This also has duplicate information in
 *it and needs to be cleaned up.
 *
 */
    public function show()
    {
        $user = auth()->user();
        $lastSubscription = $user->subscriptions()->with('subscription.price')->first();
        $user->load('subscriptions');
        $data = [
            'user'             => $user,
            'lastSubscription' => $lastSubscription,
            'has_active_plan'  => false,
            'renew_soon'       => false,
            'plan'             => Subscription::first()
        ];
        if (isset($lastSubscription) && isset($lastSubscription->subscription)) {
            $data['price'] = $lastSubscription->subscription->price->price;
        }
        if ($lastSubscription !== null) {
            $now = Carbon::now();
            $subscriptDT = Carbon::parse($lastSubscription->ends_at);
            if ($now->lte($subscriptDT)) {
                $data['has_active_plan'] = true;
            }
            if ($subscriptDT->diffInDays($now, true) < 7) {
                $data['renew_soon'] = true;
            }
        }
        return response()->json($data, 200);
    }

    /**
     * This updates auto renew.
     *
     *@param int $id This is the id of the plan that is being updated.
     */
    public function autoRenewUpdate(Request $request)
    {
        $update = $this->subscriptionRepo->updateAutoRenew($request);
        return $update;
    }

    /**
     * This updates a plan.
     *
     *@param int $id This is the id of the plan that is being updated.
     */
    public function edit(SubscriptionPlanRequest $request, $id)
    {
        $input = $request->all();
        $subscription = $this->subscriptionRepo->find($id);

        if (!$subscription) {
            $response = $this->createResponse(
                true,
                HTTP_BAD_REQUEST,
                'Failed to find subscription.',
                $subscription
            );
            return $response;
        }
        $input['plan_price'] = $input['price']['price'];
        $subscription->update($input);
        $subscription->price->update($input['price']);

        $response = $this->createResponse(
            false,
            200,
            'Subscription updated.',
            $subscription
        );
        return $response;
    }

/**
 * This renews the auth users plan for the next 30 days.
 *
 */
    public function renewSubscription()
    {
        $user = auth()->user();
        $subscription = $this->subscriptionService->userRenew($user->id);
        if (isset($subscription['error']) && $subscription['error'] == true) {
            $response = $this->createResponse(
                true,
                402,
                $subscription['message'],
                $subscription
            );
        } else {
            $response = $this->createResponse(
                false,
                200,
                'Successfully updated',
                $subscription
            );
        }
        return $response;
    }

/**
 * This alow a superadmin to renuew a user easily.
 *
 */
    public function adminPay()
    {
        $user = request()->all();
        $subscription = $this->subscriptionService->renewUserForAdmin($user);
        if (isset($subscription['error']) && $subscription['error'] == true) {
            $response = $this->createResponse(
                true,
                402,
                $subscription['message'],
                $subscription
            );
        } else {
            $response = $this->createResponse(
                false,
                200,
                'Successfully updated',
                $subscription
            );
        }
        return $response;
    }


/**
 * This will get the renew amount and new ends_at date.
 *
 */
    public function renewSubscriptionAmount($user_id)
    {
        $subscriptionAmount = $this->subscriptionRepo->renewAmount(
            $user_id,
            $this->authRepo->getCorporateBusinessAddress()
        );
        if (! $subscriptionAmount || isset($subscriptionAmount['error'])) {
            $response = $this->createResponse(
                true,
                402,
                'Something went wrong',
                $subscriptionAmount
            );
        } else {
            $response = $this->createResponse(
                false,
                200,
                'Successfully',
                $subscriptionAmount
            );
        }

        return $response;
    }

/**
 * This deletes a plan.
 *
 *@param int $id This is the id of the plan that is deleted.
 */
    public function delete($id)
    {
        $hasUser = $this->subscriptionRepo->subscriptionHasUsers($id);
        if ($hasUser) {
            return response()->json(['This subscription could not be deleted because one or more users are subscribed to it.'], HTTP_FORBIDDEN);
        }
        $subscription = $this->subscriptionRepo->delete($id);
        if (! $subscription) {
            return response()->json('failed to find subscription', HTTP_BAD_REQUEST);
        }
        return response()->json('subscription deleted successfully');
    }

/**
 * Shows a plan with the price.
 *
 *@param int $id This is the id of the plan that is showing.
 */
    public function showPlan($id)
    {
        return Subscription::with('price')->where('id', $id)->first();
    }

/**
 * This updates a token so we can charge them monthly.
 *
 */
    public function tokenUpdate()
    {
        $data = request()->all();
        $user = auth()->user();
        return $this->subscriptionRepo->updateSubscriptionToken($user->id, $data);
    }

/**
 * Update the ends at field
 *
 */
    public function updateEndsAt(Request $request)
    {
        return $this->subscriptionRepo->updateEndsAt($request);
    }

/**
 * Get and calculates subcription transaction amount, count, and auto renew users.
 *
 */
    public function transactionReport(Request $request)
    {
        $transactions = $this->subscriptionService->calculateTransactions($request);
        if (!$transactions) {
            return response()->json('failed to find report', HTTP_BAD_REQUEST);
        }
        return response()->json($transactions);

         return $transactions;
    }
/**
 * Get the receipts for a user
 *
 */
    public function userReceipt($user_id, Request $request)
    {
        $receipt = $this->subscriptionRepo->userReceipt($user_id, $request);
        if (!$receipt) {
            return response()->json('failed to find receipts', HTTP_BAD_REQUEST);
        }
        return response()->json($receipt);
    }
/**
 * Get the receipts for a user
 *
 */
    public function allReceipt(Request $request)
    {
        $receipt = $this->subscriptionRepo->getAllReceipt($request);
        if (!$receipt) {
            return response()->json('failed to find receipts', HTTP_BAD_REQUEST);
        }
        return response()->json($receipt);
    }
    /**
     * Create and download the subscription info
     *
     */
    public function csvDownloadSubscriptions()
    {
        $request = request()->all();
        $fileName = 'users_subscritpion';

        $subscriptionHeader = [
            'user_id',
            'first_name',
            'last_name',
            'title',
            'price',
            'created_at',
            'ends_at',
            'auto_renew',
            'fail_description',
            'last_fail_attempt',
            ];
        $userSubscriptions = $this->subscriptionRepo->allUserSubscriptions($request);
        $csv = $this->CsvService->createCSVDownload($fileName, $subscriptionHeader, $userSubscriptions);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    /**
     * Create and download the subscription receipt info
     *
     */
    public function csvDownloadSubscriptionsReceipt()
    {
        $request = request()->all();
        $fileName = 'subscritpion_receipts';

        $subscriptionHeader = [
            'user_id',
            'first_name',
            'last_name',
            'title',
            'total_price',
            'subtotal_price',
            'total_tax',
            'transaction_id',
            'created_at'
            ];
        $subscriptionsReceipt = $this->subscriptionRepo->getAllReceipt($request, false);
        $csv = $this->CsvService->createCSVDownload($fileName, $subscriptionHeader, $subscriptionsReceipt);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }

    public function csvsendmailSubscriptionsReceipt()
    {
        $request = request()->all();
        $fileName = 'subscritpion_receipts';

        $subscriptionHeader = [
            'user_id',
            'first_name',
            'last_name',
            'title',
            'total_price',
            'subtotal_price',
            'total_tax',
            'transaction_id',
            'created_at'
            ];
        $subscriptionsReceipt = $this->subscriptionRepo->getAllReceipt($request, false);
        $csv = $this->CsvService->createCSVsendasemail($fileName, $subscriptionHeader, $subscriptionsReceipt);
        return response()->download($csv[0], $csv[1], $csv[2]);
    }


    public function getTaxAdmin()
    {
        $request = request()->all();
        if ($request['price'] > 0 && $this->settings->getGlobal('tax_subscription', 'show') == true) {
            $user = User::where('id', $request['user_id'])->with('billingAddress')->with('shippingAddress')->first();
            $companyUser = User::select('pid')->where('id', '=', config('site.apex_user_id'))->first();
            $request['price'] = $request['price'] / $request['duration'];
            $taxInvoice = (new TaxService)->createSubscriptionTaxInvoice(
                $user->billingAddress,
                $user->shippingAddress,
                $this->authRepo->getCorporateBusinessAddress(),
                json_decode(json_encode($request)),
                isset($request['quantity']) ? $request['quantity'] : 1,
                $companyUser->pid,
                false
            );
            if (isset($taxInvoice->error)) {
                return response($taxInvoice->error, 500);
            }
            // For now support old variable
            $taxInvoice->total_tax_amount = $taxInvoice->tax;
            return response()->json($taxInvoice, HTTP_SUCCESS);
        }
        return response()->json(['pid' => null, 'tax' => 0, 'total_tax_amount' => 0], HTTP_SUCCESS);
    }

    public function getTax()
    {
        $request = request()->all();
        if ($request['subscription']['price'] > 0 && $this->settings->getGlobal('tax_subscription', 'show') == true) {
            $companyUser = User::select('pid')->where('id', '=', config('site.apex_user_id'))->first();
            $taxInvoice = (new TaxService)->createSubscriptionTaxInvoice(
                $request['billing_address'],
                $request['shipping_address'],
                $this->authRepo->getCorporateBusinessAddress(),
                json_decode(json_encode($request['subscription'])),
                isset($request['quantity']) ? $request['quantity'] : 1,
                $companyUser->pid,
                false
            );
            if (isset($taxInvoice->error)) {
                return response($taxInvoice->error, 500);
            }
            $taxInvoice->total_tax_amount = $taxInvoice->tax;
            return response()->json($taxInvoice, HTTP_SUCCESS);
        }
        return response()->json(['pid' => null, 'tax' => 0, 'total_tax_amount' => 0], HTTP_SUCCESS);
    }
}
