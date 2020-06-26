<?php namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryImportRequest;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\BundleRepository;
use App\Repositories\Eloquent\FulfilledByCorporateRepository;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\ItemRepository;
use App\Models\Inventory;
use App\Models\User;
use App\Models\Price;
use App\Services\Inventory\InventoryService;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;

class InventoryController extends Controller
{
    protected $inventoryRepo;
    protected $orderRepo;
    protected $authRepo;
    protected $fulfilledByCorporateRepo;
    protected $bundleRepo;
    protected $userSettingsRepo;
    protected $itemRepo;

    public function __construct(
        InventoryRepository $inventoryRepo,
        OrderRepository $orderRepo,
        AuthRepository $authRepo,
        FulfilledByCorporateRepository $fulfilledByCorpRepo,
        ProductRepository $productRepo,
        BundleRepository $bundleRepo,
        UserSettingsRepository $userSettingsRepo,
        ItemRepository $itemRepo,
        InventoryService $inventoryService
    ) {
        $this->inventoryRepo = $inventoryRepo;
        $this->orderRepo = $orderRepo;
        $this->authRepo = $authRepo;
        $this->fulfilledByCorpRepo = $fulfilledByCorpRepo;
        $this->settingsService = app('globalSettings');
        $this->productRepo = $productRepo;
        $this->bundleRepo = $bundleRepo;
        $this->userSettingsRepo = $userSettingsRepo;
        $this->itemRepo = $itemRepo;
        $this->inventoryService = $inventoryService;
    }
    /**
     * retreives inventory by user
     *
     * @param Request $request
     * @return ResponseJson
     */
    public function index(Request $request)
    {
        $user_id = null;
        $inventories = null;
        $rules = [
            'column' => 'required',
            'order' => 'required',
            'user_id' => 'sometimes|integer'
        ];
        $this->validate($request, $rules);
        // Determine the inventory to be returned based on request and authentication
        if (auth()->user()->hasRole(['Superadmin', 'Admin'])) {
            if (isset($request['user_id']) && $request['user_id']) {
                $user_id = $request['user_id'];
            } else {
                $user_id = config('site.apex_user_id');
            }
        } else {
            if (isset($request['sellerTypeId']) && $request['sellerTypeId'] == 1) {
                $user_id = config('site.apex_user_id');
            } else {
                $user_id = auth()->id();
            }
        }
        $inventories = $this->inventoryRepo->getInventoryByUser($user_id, $request);
        foreach ($inventories as $inventory) {
            if (isset($inventory->expires_at)) {
                $inventory->expires_at = Carbon::parse($inventory->expires_at)->format('Y-m-d');
            }
        }
        return $inventories;
    }

    public function getByProduct()
    {
        $request = request()->all();
        return $this->inventoryRepo->getInventoryByProduct($request, $this->authRepo->getOwnerId());
    }

    public function rep()
    {
        return $this->inventoryRepo->getRepInventoryByUser(auth()->user()->id);
    }

    public function savePrice()
    {
        $request = request()->all();
        $newPrice = $request['rep_price'];
        $inventory = Inventory::where('id', $request['id'])->with('price')->first();
        $newPrice = round($newPrice, 2);
        if (isset($inventory->price)) {
            $price = $inventory->price;
            $price->price = $newPrice;
            $price->save();
        } else {
            $priceData = [
                'price_type_id' => 4,
                'price' => $newPrice,
                'priceable_type' => 'App\Models\Inventory',
                'priceable_id' => $inventory->id
            ];
            $price = Price::create($priceData);
        }
        return $price;
    }

    public function fulfilledByCorporate()
    {
        $request = request()->all();
        $inventory = $this->fulfilledByCorpRepo->getInventory($request, auth()->user());

        $userId = $this->authRepo->getOwnerId();

        $timezone = $this->userSettingsRepo->getUserTimeZone($userId);
        foreach ($inventory as $key => $value) {
            $value['expires_time'] = Carbon::parse($value['expires_at'], 'UTC')->setTimezone($timezone)->toDateTimeString();
        }
        return response()->json($inventory, HTTP_SUCCESS);
    }

    public function relistFulfilledByCorporate()
    {
        $request = request()->all();
        $validator = Validator::make($request, ['item_id' => 'required|int']);
        if ($validator->fails()) {
            return $validator->errors();
        }
        $inventory = $this->fulfilledByCorpRepo->relist($request['item_id']);
        if (isset($inventory['error'])) {
            return response()->json($inventory['error'], HTTP_BAD_REQUEST);
        }
        return response()->json($inventory, HTTP_SUCCESS);
    }
    /**
    * This is to save the quantity of a product so the owner can make adjustments when needed.
    * @param  $request
    * @return $newQuantity
    */
    public function saveQuantity()
    {
        $request = request()->all();
        $item = $this->itemRepo->find($request['inventory']['item_id'])->load('product');
        if ($this->settingsService->getGlobal('reseller_create_product', 'show')
            && $item->product->user_id === $this->authRepo->getOwnerId()
            || $this->authRepo->isOwnerAdmin()
            && $item->product->user_id === $this->authRepo->getOwnerId()
        ) {
            $inventory = $this->inventoryRepo->updateQuantity($request['inventory']['id'], (int) $request['quantity']);
            return response()->json($inventory->quantity_available, HTTP_SUCCESS);
        } elseif ($this->settingsService->getGlobal('rep_edit_inventory', 'show') && auth()->user()->hasRole(['Rep'])) {
            $inventory = $this->inventoryRepo->updateQuantity($request['inventory']['id'], (int) $request['quantity']);
            return response()->json($inventory->quantity_available, HTTP_SUCCESS);
        }
        return response()->json(['Not authorized to change quantity.'], 403);
    }
    /**
     * Check to see if inventory is available to purchase
     * This also checks the session to see what inventory to check
     *
     * @return Response error, message, and quantity_available
     */
    public function checkAvailability()
    {
        $request = request()->all();
        $userId = config('site.apex_user_id');
        if (session()->has('store_owner') && session()->get('store_owner.seller_type_id') != 1) {
            $userId = session()->get('store_owner.id');
        }
        if ($this->authRepo->isOwnerRep()) {
            $minMaxCheck = $this->productRepo->checkMinMax($request['item_id'], $request['quantity']);
            if ($minMaxCheck !== true) {
                return response()->json(['error' => true,
                    'message' => $minMaxCheck['error']['description'],
                    'quantity_available' => $minMaxCheck['error']['allowed']], 200);
            }
        }
        $quantityAvailable = $this->inventoryRepo->quantityAvailable($request['item_id'], $userId);
        if ($quantityAvailable === null) {
            return response()->json(['error' => true,
                'message' => 'Inventory for that item could not be found.',
                'quantity_available' => 0
            ], HTTP_NOT_FOUND);
        }
        if ($quantityAvailable < $request['quantity']) {
            return response()->json([
                'error' => true,
                'message' => 'The selected quantity is NOT available. Quantity Available: '.$quantityAvailable,
                'quantity_available' => $quantityAvailable
            ], 200);
        }
        return response()->json([
            'error' => false,
            'message' => 'The selected quantity is available.',
            'quantity_available' => $quantityAvailable
        ], 200);
    }

    public function bundleCheckAvailability()
    {
        $request = request()->all();
        $bundle = $this->bundleRepo->show($request['bundle_id']);
        $available = $this->inventoryService->checkBundleInventoryAvailability($bundle, $request['quantity']);
        if ($available['error']) {
            return response()->json([
                'error' => true,
                'message' => 'The selected quantity is NOT available. Quantity Available: '.$available['quantityAvailable'],
                'quantity_available' => $available['quantityAvailable']
            ], 200);
        }
        return response()->json([
            'error' => false,
            'message' => 'The selected quantity is available.',
            'quantity_available' => $available['quantityAvailable']
        ], 200);
    }

    public function csvImport(InventoryImportRequest $importRequest)
    {
        $contents = file_get_contents($importRequest->file('file'));
        $userId = $importRequest->get('userId');

        $importResult = $this->inventoryRepo->import($contents, $userId);

        if ($importResult['errors']) {
            $message = 'Failed to import. ';
            foreach ($importResult['errors'] as $error) {
                $message .= $error . "\r\n";
            }
            return response()->json($importResult['errors'], 404);
        }
        return response()->json('Successfully imported.', 200);
    }

    public function csvExport()
    {
        $template = request()->get('template');
        if (!isset($template)) {
            $template = true;
        }
        $filename = 'template.csv';
        if ($template == false) {
            $filename = 'inventory_export.csv';
        }
        $csvFile = $this->inventoryRepo->csvExport(null, $template);
        return response($csvFile)
            ->withHeaders([
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Content-Length' => strlen($csvFile)
            ]);
    }

    public function toggleDisable($id)
    {
        $inventory = $this->inventoryRepo->find($id);

        if ($inventory->user_id === $this->authRepo->getOwnerId()) {
            $inventory = $this->inventoryRepo->toggleDisable($inventory->id);
            if (isset($inventory->disabled_at)) {
                return response()->json(['inventory' => $inventory, 'message' => 'Product Hidden in Store'], 200);
            }
            return response()->json(['inventory' => $inventory, 'message' => 'Product Shown in Store'], 200);
        }

        return response()->json('Unauthorized', 403);
    }

    public function updateExpirationDate()
    {
        $request = request()->all();
        $inventory = $this->inventoryRepo->updateExpirationDate($request['id'], $request['expires_at']);
        return response()->json($inventory, HTTP_SUCCESS);
    }
}
