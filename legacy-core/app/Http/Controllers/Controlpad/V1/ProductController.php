<?php

namespace App\Http\Controllers\Controlpad\V1;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\ItemRepository;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $itemRepo;

    public function __construct(ItemRepository $itemRepo)
    {
        $this->itemRepo = $itemRepo;
    }

    /**
     * Return all "products" (read: Items) in a particular
     * format for Mcomm to consume via our API.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function mcommIndex()
    {
        try {
            $items = $this->itemRepo->all();

            $payload = [];

            foreach ($items as $item) {
                $payload[] = [
                    'ItemCode' => $item->custom_sku,
                    'ItemName' => $item->product->name,
                    'ItemSize' => $item->size,
                ];
            }

            return response()->json($payload);
        } catch (Exception $e) {
            return "Error: " . $e;
        }
    }
}
