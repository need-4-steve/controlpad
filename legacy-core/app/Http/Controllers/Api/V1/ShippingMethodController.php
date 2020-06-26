<?php namespace App\Http\Controllers\Api\V1;

use Response;
use Input;
use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use App\Repositories\Eloquent\CartRepository;
use App\Repositories\Eloquent\ShippingMethodRepository;

class ShippingMethodController extends Controller
{

    public function __construct(CartRepository $cart, ShippingMethodRepository $shippingMethod)
    {
        $this->cart = $cart;
        $this->shippingMethod = $shippingMethod;
    }

    public function getIndex()
    {
        return Response::json(ShippingMethod::all());
    }

    public function getAllShippingMethods()
    {
        $shippingMethods = ShippingMethod::all();
        return $shippingMethods;
    }

    public function getWeight()
    {
        $total_weight = 0;
        $cart = $this->cart->show('cart_id');
        foreach ($cart->lines as $line) {
            if ($line->product->type == 'Product') {
                foreach ($line->product->items as $item) {
                    $total_weight += $item->weight;
                }
            }
        }
        return Response::json($total_weight);
    }

    public function getShippingCost()
    {
        return $this->shippingMethod->getShippingCost();
    }
}
