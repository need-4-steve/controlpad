<?php namespace App\Repositories\Eloquent;

use Auth;
use Config;
use Carbon\Carbon;
use Input;
use Session;
use Validator;
use DB;
use App\Models\Cart;
use App\Models\Cartline;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Media;
use App\Models\Product;
use App\Models\Price;
use App\Models\Promotion;
use App\Models\User;
use App\Repositories\Eloquent\PromotionRepository;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use App\Repositories\Eloquent\UserSettingsRepository;
use App\Repositories\Eloquent\AuthRepository;

class InventoryRepository
{
    use CommonCrudTrait;

    public function __construct(
        UserSettingsRepository $userSettings,
        AuthRepository $authRepo
    ) {
        $this->settings = app('globalSettings');
        $this->authRepo = new AuthRepository;
        $this->csvHeaders = ["name", "print", "id"];
        $this->userSettingsRepo = $userSettings;
    }

    public function create($userId, $item, $userPid = null, $quantity_available = 0)
    {
        if ($userPid == null) {
            $user = User::select('pid')->where('id', $userId)->first();
            $userPid = ($user == null ? null : $user->pid);
        }
        $inventory = Inventory::create([
            'item_id'            => $item->id,
            'user_id'            => $userId,
            'user_pid'           => $userPid,
            'owner_id'           => $userId,
            'owner_pid'          => $userPid,
            'quantity_available' => $quantity_available,
            'purchased_at'       => Carbon::now(),
        ]);
        if ($userId !== config('site.apex_user_id')) {
            $price = new Price;
            $price->price_type_id = 4; // Inventory Price Type
            $price->price = $item->msrp()->first()->price;
            $price->priceable_type = Inventory::class;
            $inventory->price()->save($price);
        }
        return $inventory;
    }

    public function getInventoryByProduct($request, $userId)
    {
        $products = Product::userInventory($userId)
            ->search($request['search_term'])
            ->orderBy($request['column'], $request['order']);

        return $products->paginate($request['per_page']);
    }

    public function getRepInventoryByUser($user_id)
    {
        return Inventory::where('user_id', $user_id)
                    ->with('price', 'item.product.category', 'item.prices')
                    ->has('item.product')
                    ->where('quantity_available', '>', 0)
                    ->get()
                    ->sortBy('id');
    }

    /**
     * Function to grab inventory for a user
     *
     * @param int, request
     * @return object
     */
    public function getInventoryByUser($userId, $request)
    {
        $select = [
            'inventories.id',
            'item_id',
            'inventories.user_id',
            'quantity_available',
            'product_id',
            'products.user_id AS product_user_id',
            'size',
            'print',
            'custom_sku',
            'manufacturer_sku',
            'name',
            'inventories.disabled_at',
            'inventories.expires_at',
            'items.created_at',
            DB::raw('max((CASE prices.price_type_id WHEN 1 THEN prices.price END)) as wholesale_price,
             max((CASE prices.price_type_id WHEN 2 THEN prices.price END)) as msrp,
             max((CASE prices.price_type_id WHEN 3 THEN prices.price END)) as premium_price')
        ];
        $inventory = Inventory::select($select)
            ->join('items', 'items.id', '=', 'inventories.item_id')
            ->join('products', 'products.id', '=', 'items.product_id')
            ->join('prices', function ($join) {
                $join->on('priceable_id', '=', 'items.id')
                ->where('priceable_type', '=', Item::class);
            })
            ->where('inventories.user_id', '=', $userId)
            ->where('inventories.owner_id', '=', $userId)
            ->groupBy('inventories.id');

        // only add search term if we have a search term
        if (!empty($request['search_term'])) {
            $inventory = $inventory->where(function ($query) use ($request) {
                $query->where('size', 'LIKE', '%'.$request['search_term'].'%')
                ->orWhere('print', 'LIKE', '%'.$request['search_term'].'%')
                ->orWhere('custom_sku', 'LIKE', '%'.$request['search_term'].'%')
                ->orWhere('manufacturer_sku', 'LIKE', '%'.$request['search_term'].'%')
                ->orWhere('name', 'LIKE', '%'. $request['search_term'] . '%')
                ;
            })->orderBy($request['column'], $request['order']);
        } else {
            $inventory = $inventory->orderBy($request['column'], $request['order']);
        }


        if ($userId !== config('site.apex_user_id')) {
            $select[] = DB::raw('MAX(rep_price.price) AS rep_price');
            $inventory->select($select)
                ->leftjoin('prices as rep_price', function ($join) {
                    $join->on('rep_price.priceable_id', '=', 'inventories.id')
                    ->where('rep_price.priceable_type', '=', Inventory::class)
                    ->where('rep_price.price_type_id', '=', 4);
                });
        }

        $inventory = $inventory->paginate($request['per_page']);

        foreach ($inventory as $inv) {
            $inv->default_media = Media::join('mediables', function ($join) use ($inv) {
                $join->on('mediables.media_id', '=', 'media.id')
                ->where('mediables.mediable_type', '=', 'App\Models\Product')
                ->where('mediables.mediable_id', '=', $inv->product_id);
            })->take(1)->first();
        }

        return $inventory;
    }

    /**
     * Get a product (with various details) that belongs to a user
     *
     * @param int $inventory_id
     * @param int $user_id
     * @return \App\Models\Inventory
     */
    public function getInventoryItemByUser($inventory_id, $user_id)
    {
        return Inventory::with('price', 'item.product', 'item.prices')
                    ->has('item.product')
                    ->where('user_id', $user_id)
                    ->where('id', $inventory_id)
                    ->first();
    }

    /**
     * Get an inventory by item id and user id.
     *
     * @param int $itemId
     * @param int $userId
     * @return Inventory $inventory
     */
    public function getInventoryByUserAndItem($itemId, $userId)
    {
        $inventory = Inventory::where('item_id', $itemId)->where('user_id', $userId)->first();
        return $inventory;
    }

    /**
     * Get an inventory object that belongs to a user, no details,
     * used to check for locks
     *
     * @param int $item_id
     * @param int $user_id
     * @return \App\Models\Inventory
     */
    public function getInventoryObjects($item_ids, $user_id)
    {
        return Inventory::whereIn('item_id', $item_ids)
                        ->where('user_id', $user_id)
                        ->get();
    }

    /**
     * See if a product is available
     *
     * @param $product
     * @param $product_quantity
     * @return int quantity_available
     */
    public function quantityAvailable($item_id, $store_owner_id, $in_bundle = false)
    {
        $inventory = Inventory::where('item_id', $item_id)->where('user_id', $store_owner_id);
        // prevent selling fbc products in a bundle that is not owned by corp
        if ($in_bundle) {
            $inventory = $inventory->where('owner_id', $store_owner_id);
        }
        $inventory = $inventory->first();
        if ($inventory) {
            return $inventory->quantity_available;
        }
        return null;
    }

    /**
     * This method checks to see if the specified item is in use in ANY rep's store
     *
     * @param int $item_id
     * @return int $quantity
     */
    public function checkItemInventory($item_id)
    {
        $quantity = Inventory::where('item_id', $item_id)->where('user_id', '!=', 1)->sum('quantity_available');
        $quantity += Inventory::where('item_id', $item_id)->sum('quantity_staged');
        return $quantity;
    }

    /**
     * add or substract from inventory
     *
     * @param object $lines this is from the orderlines
     * @param int $inventory_user_id this it a user id
     * @param boolean $subtract
     * @return DB
     */
    public function updateInventory($lines, $inventory_user_id = null, $subtract = false, $newUserRegistration = false)
    {
        $ownerId = $this->authRepo->getOwnerId();
        $owner = User::select('pid')->where('id', $ownerId)->first();
        $ownerPid = ($owner == null ? null : $owner->pid);
        // grab the id of the user that is being purchased from
        if ($inventory_user_id == null) {
            if (!session()->has('store_owner') or session('store_owner.seller_type_id') === 1) {
                $inventory_user_id = config('site.apex_user_id');
            } else {
                $inventory_user_id = session('store_owner.id');
            }
        }
        if ($inventory_user_id != null && $inventory_user_id !== $ownerId) {
            $inventory_user = User::select('pid')->where('id', $inventory_user_id)->first();
            $inventory_user_pid = ($inventory_user == null ? null : $inventory_user->pid);
        } else {
            $inventory_user_pid = $ownerPid;
        }

        DB::beginTransaction();
        foreach ($lines as $line) {
            if ($line->item_id !== null) {
                $inventory = Inventory::where('item_id', $line->item_id)
                                        ->where('user_id', $inventory_user_id)
                                        ->first();
                $productType = $line->item->product->type_id;
                // if a rep is buying inventory
                if (auth()->check() && auth()->user()->hasRole(['Rep']) && $inventory == null && $subtract == false) {
                    if ($productType == 1) {
                        $inventory = $this->create($ownerId, $line->item, $ownerPid, $line->quantity);
                        $inventory = $this->checkToDisable($inventory, $inventory_user_id);
                    }
                } elseif ($subtract) { // if subtracting inventory
                    if ($productType === 5
                        && $inventory->user_id === $inventory->owner_id
                        && $inventory->user_id === config('site.apex_user_id')) {
                        $expiresAt = Carbon::now()
                                        ->addDays($inventory->item->product->duration)
                                        ->toDateTimeString();
                        $inventory->update([
                            'owner_id'           => $ownerId,
                            'owner_pid'          => $ownerPid,
                            'expires_at'         => $expiresAt,
                            'quantity_available' => $line->quantity,
                            'purchased_at'       => Carbon::now(),
                        ]);
                        $inventory->item->product->roles()->sync([3]);
                    } else {
                        // throw error if inventory is empty
                        if (empty($inventory)) {
                            abort(400, 'Unable to find inventory to update owned by that user');
                        }
                        $inventory->update([
                            'quantity_available' => $inventory->quantity_available - $line->quantity,
                        ]);
                    }
                // if a new rep is buying a starter kit
                } elseif ($newUserRegistration == true) {
                    if ($productType == 1) {
                        $inventory = Inventory::where('item_id', $line->item_id)->where('user_id', $inventory_user_id)->first();
                        if (!empty($inventory)) {
                            $inventory->update([
                                'quantity_available' => $inventory->quantity_available + $line->quantity,
                            ]);
                            // This isn't suppose to be happening. Logger is here to debug.
                            logger()->error([
                                'message'      => 'Starter Kit inventory on registration already exists',
                                'auth_user_id' => auth()->check() ? auth()->id() : 'N/A',
                                'inventory'    => $inventory,
                                'orderline'    => $line
                            ]);
                        } else {
                            $inventory = $this->create($inventory_user_id, $line->item, $inventory_user_pid, $line->quantity);
                            $inventory = $this->checkToDisable($inventory, $inventory_user_id);
                        }
                    }
                } else { // if adding inventory
                    // throw error if inventory is empty
                    if (empty($inventory)) {
                        abort(400, 'Unable to find inventory to update owned by that user');
                    }
                    $inventory->update([
                        'quantity_available' => $inventory->quantity_available + $line->quantity,
                        'expires_at' => null
                    ]);
                }
            }

            if (isset($inventory) && $inventory['quantity_available'] == 0) {
                $time = intval($this->settings->getGlobal('sold_out', 'value'));
                $expires = Carbon::now()->addHours($time)->toDateTimeString();
                $inventory->update([
                    'expires_at' => $expires
                ]);
            }
        }

        return DB::commit();
    }

    public function checkToDisable($inventory, $userId)
    {
        $userSettings = $this->userSettingsRepo->show($userId);
        if (isset($userSettings) && $userId !== config('site.apex_user_id') and !$userSettings->show_new_inventory) {
            $inventory->disabled_at = date("Y-m-d H:i:s");
            $inventory->save();
        }
        return $inventory;
    }

    public function subtractStagedInventory($lines, $inventory_user_id)
    {
        DB::beginTransaction();
        foreach ($lines as $line) {
            $inventory = Inventory::where('item_id', $line->item_id)
                            ->where('user_id', $inventory_user_id)->first();
            $inventory->update([
                'quantity_staged' => $inventory->quantity_staged - $line->quantity
            ]);
        }
        DB::commit();
        return $lines;
    }

    public function import($fileContents, $userId)
    {
        $errorMessages = [];

        $inputHeaders = $this->csvHeaders;
        // parse the file into array
        $contentArray = str_getcsv($fileContents, "\n");

        // setup headers from first row
        $headers = explode(",", array_shift($contentArray));
        if (!in_array('id', $headers)) {
            return [
                'errors' => ["The CSV is missing 'id' in the header"]
            ];
        }
        $headerArray = [];
        foreach ($headers as $key => $header) {
            $headerArray[] = $header;
            // set the index of our id field for grabbing items later
            if ($header == 'id') {
                $idIndex = $key;
            }

            if ($header == 'name') {
                $nameIndex = $key;
            }
        }

        $firstSizeIndex = count($inputHeaders);
        $user = User::select('pid')->where('id', $userId)->first();
        $userPid = ($user == null ? null : $user->pid);

        DB::beginTransaction();
        // get inventory list
        $totalChangedInventory = collect();
        foreach ($contentArray as $row) {
            $rowArray = explode(",", $row);
            $keyIndex = 0;

            for ($rowIndex = $firstSizeIndex; $rowIndex < count($rowArray); $rowIndex++) {
                if (!isset($rowArray[$rowIndex]) || $rowArray[$rowIndex] == '' || $rowArray[$rowIndex] == 0) {
                    continue;
                }

                // fix some format issues with CSV
                $size = $headerArray[$rowIndex];
                if (substr($size, -1, 1) == '"') {
                    $size = substr($size, 0, strlen($size)-1);
                }
                if (substr($size, 0, 1) == '"') {
                    $size = substr($size, 1, strlen($size));
                }
                $size = str_replace('""', '"', $size);

                $item = Item::where('product_id', (int) $rowArray[$idIndex])
                    ->where('size', $size)
                    ->first();
                // add to the error message by including that this item couldn't be added
                if (empty($item)) {
                    if (is_numeric($rowArray[$rowIndex]) && $rowArray[$rowIndex] > 0) {
                        $errorMessages[] = 'Size: ' . $size
                                            . ' not added to inventory.'
                                            . ' Size does not exist in database'
                                            . ' for product '
                                            . $rowArray[$nameIndex]
                                            . ' with product id '
                                            . $rowArray[$idIndex];
                    }
                } else {
                    $inventory = Inventory::where('item_id', $item->id)
                        ->where('user_id', $userId)
                        ->first();

                    if (is_numeric($rowArray[$rowIndex]) and $rowArray[$rowIndex] > 0) {
                        if (!isset($inventory)) {
                            $inventory = $this->create($userId, $item, $userPid);
                        }
                        $inventory->quantity_available += $rowArray[$rowIndex];
                        $inventory->quantity_imported += $rowArray[$rowIndex];
                        $inventory->save();
                        $totalChangedInventory->push($inventory);
                    }
                }
            }
        }

        if (empty($errorMessages)) {
            DB::commit();
        } else {
            return [
                'errors' => $errorMessages
            ];
        }

        return [
            'errors' => false,
            'inventory' => $totalChangedInventory
        ];
    }

    public function csvExport($inputHeaders = null, $template = true)
    {
        DB::beginTransaction();
        if ($inputHeaders == null) {
            $inputHeaders = $this->csvHeaders;
        }
        $csvFile = [];
        $allSizes = Item::join('products', 'items.product_id', '=', 'products.id')
            ->where('user_id', config('site.apex_user_id'))
            ->groupBy('size')->pluck('size')
            ->toArray();

        usort($allSizes, 'self::sortSize');
        $products = Product::select('id', 'name')
            ->where('user_id', config('site.apex_user_id'))
            ->with('items')->orderBy('name')
            ->get();

        foreach ($products as $product) {
            foreach ($product->items as $item) {
                $product->print = $item->print;
            }
        }

        foreach ($inputHeaders as $key => $head) {
            $csvHeaders[$key] = $head;
        }

        foreach ($allSizes as $key => $size) {
            $csvHeaders[] = $size;
        }
        $csvFile[] = $csvHeaders;

        foreach ($products as $product) {
            $productRow = [];
            $productRow = array_fill(0, count($allSizes) + count($inputHeaders) - 1, '');
            foreach ($inputHeaders as $key => $header) {
                $productRow[$key] = str_replace(',', ';', $product->$header); // Commas in product name will mess with the import of inventory
            }
            if ($template == false) {
                foreach ($product->items as $item) {
                    $key = array_search($item->size, $csvHeaders);
                    if ($key != false) {
                        $inventory = Inventory::select('quantity_available')
                            ->where('item_id', $item->id)
                            ->where('user_id', config('site.apex_user_id'))
                            ->first();

                        if (isset($inventory)) {
                            $productRow[$key] = $inventory->quantity_available;
                        }
                    }
                }
            }
            $csvFile[] = $productRow;
        }
        DB::commit();
        return $this->arrayToCsv($csvFile);
    }

    /**
      * Formats a line (passed as a fields  array) as CSV and returns the CSV as a string.
      * Adapted from http://us3.php.net/manual/en/function.fputcsv.php#87120
      */
    public function arrayToCsv(
        array &$outerFields,
        $delimiter = ',',
        $enclosure = '',
        $encloseAll = false,
        $nullToMysqlNull = false
    ) {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');

        $output = array();
        foreach ($outerFields as $innerField) {
            foreach ($innerField as $field) {
                if ($field === null && $nullToMysqlNull) {
                    $output[] = 'NULL';
                    continue;
                }
                // Enclose fields containing $delimiter, $enclosure or whitespace
                if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                    $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                } else {
                    $output[] = $field;
                }
            }
            $lastElement = array_pop($output);
            $lastElement .= "\r\n";
            $output[] = $lastElement;
        }

        $output = implode($delimiter, $output);
        $output = str_replace("\r\n,", "\r\n", $output);
        return $output;
    }

    public function toggleDisable($id)
    {
        $inventory = Inventory::find($id);

        if (isset($inventory->disabled_at)) {
            $inventory->disabled_at = null;
        } else {
            $inventory->disabled_at = date("Y-m-d H:i:s");
        }

        $inventory->save();

        return $inventory;
    }

    public function getItemOwnerId($userId, $itemId)
    {
        if (!isset($userId)) {
            $userId = config('site.apex_user_id');
        }
        $inventory = Inventory::where('item_id', $itemId)->where('user_id', $userId)->first();
        if (empty($inventory)) {
            return null;
        }
        return $inventory->owner_id;
    }

    public function itemsOwnerInventoryCheck($items, $requestTypeId, $product)
    {
        $ownedInventory = true;
        foreach ($items as $item) {
            if (isset($item['id']) and $requestTypeId !== $product->type_id) {
                $inventoryCount = Inventory::where('item_id', $item['id'])
                                    ->where('user_id', $this->authRepo->getOwner()->id)
                                    ->where('owner_id', '!=', config('site.apex_user_id'))
                                    ->count();
                if ($inventoryCount > 0) {
                    return ['error' => 'Can not update product type that has already been sold to a rep.'];
                }
            }
            if (isset($item['id']) and $ownedInventory) {
                $inventory = Inventory::where('item_id', $item['id'])
                                ->where('user_id', $this->authRepo->getOwner()->id)
                                ->first();
                if (!isset($inventory)) {
                    return ['error' => 'Inventory not found'];
                }

                if ($inventory->user_id !== $inventory->owner_id) {
                    $ownedInventory = false;
                }
            }
        }
        return $ownedInventory;
    }

    public function relistFulfilledByCorporate($itemId)
    {
        $inventory = Inventory::where('item_id', $itemId)
                        ->where('user_id', config('site.apex_user_id'))
                        ->where('owner_id', '!=', config('site.apex_user_id'))
                        ->with('item.product')
                        ->first();
        if (!$inventory) {
            return ['error' => 'Could not find inventory for item.'];
        }
        if ($inventory->item->product->type_id !== 5) {
            return ['error' => 'Can not relist products that are not type fulfilled by corporate.'];
        }
        $inventory->expires_at = Carbon::now()->addDays($inventory->item->product->duration)->toDateTimeString();
        $inventory->save();
        $inventory->status = 'Active';
        return $inventory;
    }

    public function updateExpirationDate($inventoryId, $expirationDate)
    {
        $inventory = Inventory::find($inventoryId);
        if ($expirationDate !== '') {
            $inventory->expires_at = Carbon::parse($expirationDate)->endOfDay()->toDateTimeString();
        } else {
            $inventory->expires_at = null;
        }
        $inventory->save();
        return $inventory;
    }

    public function updateQuantity(int $id, int $quantity)
    {
        $inventory = Inventory::find($id);
        $inventory->quantity_available = $quantity;
        if ($inventory->user_id === $inventory->owner_id and $quantity > 0) {
            $inventory->expires_at = null;
        } elseif ($quantity === 0) {
            $time = intval($this->settings->getGlobal('sold_out', 'value'));
            $expiresAt = Carbon::now()->addHours($time)->toDateTimeString();
            $inventory->expires_at = $expiresAt;
        }
        $inventory->save();
        return $inventory;
    }

    private function sortSize($a, $b)
    {
        $a = strtoupper($a);
        $b = strtoupper($b);

        $sizes = [
            "ONE SIZE" => 0,
            "XXXS" => 1,
            "3XS" => 2,
            "3XSMALL" => 3,
            "XXS" => 4,
            "2XS" => 5,
            "2XSMALL" => 6,
            "XS" => 7,
            "XSMALL" => 8,
            "S" => 9,
            "SMALL" => 10,
            "M" => 11,
            "MEDIUM" => 12,
            "L" => 13,
            "LARGE" => 14,
            "XL" => 15,
            "XLARGE" => 16,
            "XXL" => 17,
            "2XL" => 18,
            "2XLARGE" => 19,
            "XXXL" => 20,
            "3XL" => 21,
            "3XLARGE" => 22
        ];

        // Comparing aplphabetical sizes with numerical sizes.
        if (is_numeric($a) && !is_numeric($b)) {
            return 1;
        } elseif (!is_numeric($a) && is_numeric($b)) {
            return -1;
        } elseif (is_numeric($a) && is_numeric($b)) {
            return $this->compareSizes($a, $b);
        }

        // Comparing aplphabetical sizes.
        // If size is not predifined then it appends at the end in alphabetical order.
        if (!isset($sizes[$a]) && !isset($sizes[$b])) {
            return $this->compareSizes($a, $b);
        } elseif (!isset($sizes[$b])) {
            return -1;
        } elseif (!isset($sizes[$a])) {
            return 1;
        }

        return $this->compareSizes($sizes[$a], $sizes[$b]);
    }

    private function compareSizes($a, $b)
    {
        if ($a > $b) {
            return 1;
        } elseif ($a < $b) {
            return -1;
        }
        return 0;
    }

    public function addReturnedInventory($lines, $user_id)
    {
        $user = User::select('pid')->where('id', $user_id)->first();
        $userPid = ($user == null ? null : $user->pid);
        foreach ($lines as $line) {
            if ($line['item_id'] !== null) {
                $inventory = Inventory::where('item_id', $line['item_id'])
                    ->where('user_id', $user_id)
                    ->first();
                if ($inventory == null) {
                    $item = Item::where('id', $line['item_id'])->first();
                    $this->create($user_id, $item, $userPid, $line['inventoryQuantity']);
                } else {
                    $inventory->update([
                        'quantity_available' => $inventory->quantity_available + $line['inventoryQuantity'],
                    ]);
                }
            }
        }
    }

    public function removeReturnedInventory($lines, $customer_id)
    {
        foreach ($lines as $line) {
            if ($line['item_id'] !== null) {
                $inventory = Inventory::where('item_id', $line['item_id'])
                    ->where('user_id', $customer_id)
                    ->first();
                if ($inventory !== null) {
                    if ($line['quantity'] > $inventory->quantity_available) {
                        $inventory->update([
                            'quantity_available' => 0,
                        ]);
                    } else {
                        $inventory->update([
                            'quantity_available' => $inventory->quantity_available - $line['quantity'],
                        ]);
                    }
                }
            }
        }
    }
}
