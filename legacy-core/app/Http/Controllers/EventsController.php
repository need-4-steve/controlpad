<?php namespace App\Http\Controllers;

use App\Services\Store\Store;
use App\Services\Store\RepStore;
use App\Services\UserStatus\UserStatusService;
use App\Repositories\Eloquent\ProductRepository;
use App\Models\Event;
use Carbon\Carbon;

class EventsController extends Controller
{
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->settingsService = app('globalSettings');
    }

    private function getStoreOwnerId()
    {
        $storeOwnerId = session()->get('store_owner');
        if (!$storeOwnerId) {
            return 1;
        }
        return $storeOwnerId->id;
    }

    public function eventStore()
    {
        $storeOwnerId = $this->getStoreOwnerId();
        if ($storeOwnerId !== 1) {
            $store = (object) array( 'rep' => session()->get('store_owner'), 'categories' => [] );
            $userStatusService = new UserStatusService;
            // Redirect if rep can't sell
            if (!$userStatusService->checkUserIdPermission($storeOwnerId, 'sell')) {
                return redirect($userStatusService->getSellRedirectUrl());
            }
        }
        if ($storeOwnerId !== 1 && !$this->settingsService->getGlobal('allow_reps_events', 'show') ||
            $storeOwnerId == 1 && !$this->settingsService->getGlobal('use_built_in_store', 'show')
        ) {
            abort(404);
        }
        return view('events.event-store', compact('storeOwnerId', 'store'));
    }

    public function eventShow($eventId)
    {
        $request = request()->all();
        $store_owner = session()->get('store_owner');
        if (!$store_owner) {
            $storeOwnerId = config('site.apex_user_id');
        } else {
            $storeOwnerId = $store_owner->id;
        }
        if ($store_owner && !$this->settingsService->getGlobal('allow_reps_events', 'show')) {
            abort(404);
        }
        if ($store_owner && $this->settingsService->getGlobal('replicated_site', 'show')) {
            $store = new RepStore($request, $store_owner, $this->productRepository, $eventId);
            if ($store->rep->settings->hide_products) {
                return redirect('/store/events');
            }
            $userStatusService = new UserStatusService;
            // Redirect if rep can't sell
            if (!$userStatusService->checkUserIdPermission($storeOwnerId, 'sell')) {
                return redirect($userStatusService->getSellRedirectUrl());
            }
        } elseif (! $store_owner && $this->settingsService->getGlobal('use_built_in_store', 'show')) {
            $store = new Store($request, $storeOwnerId, $this->productRepository, $eventId);
        } else {
            return abort(404);
        }
        $storeOwnerId = $this->getStoreOwnerId();
        $event = Event::where('id', $eventId)->first();

        if ($event == null || $event->status === 'closed' || (int)$event->sponsor_id !== (int)$storeOwnerId || $event->sale_end <= Carbon::now()) {
            return abort(404);
        }

        return view('events.event', compact('storeOwnerId', 'store', 'store_owner', 'eventId'));
    }

    public function productShow($slug)
    {
        $storeOwnerId = $this->getStoreOwnerId();
        $userStatusService = new UserStatusService;
        // Redirect if rep can't sell
        if (!$userStatusService->checkUserIdPermission($storeOwnerId, 'sell')) {
            return redirect($userStatusService->getSellRedirectUrl());
        }
        return view('events.event-product-show', compact('storeOwnerId'));
    }

    public function index()
    {
        if (!$this->settingsService->getGlobal('allow_reps_events', 'show') && auth()->user()->hasRole(['Rep'])) {
            abort(404);
        }
        return view('events.index');
    }
}
