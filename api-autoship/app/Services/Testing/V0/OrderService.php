<?php

namespace App\Services\Testing\V0;

use App\Services\Interfaces\V0\OrderServiceInterface;
use CPCommon\Pid\Pid;
use Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderService implements OrderServiceInterface
{
    public function getCart($cartId)
    {
         return json_decode('
         {  
            "pid":"2x1cpvrwlxhkcn2xjlql7be58",
            "buyer_pid":"d59u0ee777f5h4pjrzzomddw7",
            "seller_pid":"abke3zidq5sa8uavui6i24gsv",
            "inventory_user_pid":"abke3zidq5sa8uavui6i24gsv",
            "type":"wholesale",
            "coupon_id":null,
            "lines":[  
               {  
                  "item_id":111,
                  "quantity":1,
                  "price":2.18,
                  "created_at":"2018-09-11 22:31:12",
                  "updated_at":null,
                  "inventory_owner_id":1,
                  "discount":0,
                  "discount_type_id":null,
                  "event_id":null,
                  "pid":"bupj07jl9y8e1sdadj7rzwgi3",
                  "bundle_id":null,
                  "inventory_owner_pid":"abke3zidq5sa8uavui6i24gsv",
                  "bundle_name":null,
                  "tax_class":"09050101",
                  "items":[  
                     {  
                        "id":111,
                        "inventory_id":111,
                        "product_name":"Nikki Jeans",
                        "variant_name":"SandyBrown",
                        "option_label":"Size",
                        "option":"S",
                        "sku":"9783831521869",
                        "premium_shipping_cost":null,
                        "img_url":"https://s3-us-west-2.amazonaws.com/controlpad-hub/107be99360f7a4add71da792d50fd01f.jpg"
                     }
                  ]
               },
               {  
                  "item_id":138,
                  "quantity":1,
                  "price":2.62,
                  "created_at":"2018-09-11 22:31:17",
                  "updated_at":null,
                  "inventory_owner_id":1,
                  "discount":0,
                  "discount_type_id":null,
                  "event_id":null,
                  "pid":"8byy37n679n1g4jw9ltw7jpuy",
                  "bundle_id":null,
                  "inventory_owner_pid":"abke3zidq5sa8uavui6i24gsv",
                  "bundle_name":null,
                  "tax_class":"09050101",
                  "items":[  
                     {  
                        "id":138,
                        "inventory_id":138,
                        "product_name":"Joshuah Shoes",
                        "variant_name":"LightYellow",
                        "option_label":"Size",
                        "option":"L",
                        "sku":"9799035988766",
                        "premium_shipping_cost":null,
                        "img_url":"https://s3-us-west-2.amazonaws.com/controlpad-hub/78a0ed2de1d0a24fff4c1b0fdc907c19.jpg"
                     }
                  ]
               }
            ]
         }', true);
    }

    public function createCheckout($subscription, $buyer)
    {
        return json_decode('{  
            "billing_address":{  
               "name":"Adah Reichel",
               "line_1":"931 Bode Glens",
               "line_2":"Suite 704",
               "city":"North Letha",
               "zip":"84058",
               "state":"VT"
            },
            "shipping_address":{  
               "name":"Adah Reichel",
               "line_1":"1411 W 1250 S",
               "line_2":"",
               "city":"Orem",
               "zip":"84058",
               "state":"UT"
            },
            "shipping_is_billing":false,
            "subtotal":3.21,
            "type":"wholesale",
            "buyer_pid":"d59u0ee777f5h4pjrzzomddw7",
            "seller_pid":"abke3zidq5sa8uavui6i24gsv",
            "inventory_user_pid":"abke3zidq5sa8uavui6i24gsv",
            "discount":0.06,
            "lines":[  
               {  
                  "item_id":40,
                  "price":3.21,
                  "quantity":1,
                  "created_at":"2018-09-11 19:23:49",
                  "updated_at":"2018-09-14 17:17:17",
                  "disabled_at":null,
                  "inventory_owner_pid":"abke3zidq5sa8uavui6i24gsv",
                  "items":[  
                     {  
                        "id":40,
                        "sku":"9783181900710",
                        "option":"XXL",
                        "img_url":"https://s3-us-west-2.amazonaws.com/controlpad-hub/3bcf58ec4f1bc02a4eb9871f5a836145.jpeg",
                        "inventory_id":40,
                        "option_label":"Size",
                        "product_name":"Lily Bow Tie",
                        "variant_name":"Tan",
                        "premium_shipping_cost":null
                     }
                  ],
                  "tax_class":"09050101",
                  "bundle_id":null,
                  "bundle_name":null,
                  "cartline_pid":"3lhjr6uzbbdy6zttaf941sr10",
                  "orderline_pid":"3ye913z8d1phpk5y7sjc3ow28"
               }
            ],
            "pid":"dlc1z0m2sn5a9258g91k881mw",
            "shipping_rate_id":493,
            "shipping":5,
            "tax_invoice_pid":null,
            "tax":0,
            "total":8.15,
            "updated_at":"2018-09-24 20:39:06",
            "created_at":"2018-09-24 20:39:06"
         }');
    }

    public function checkout($subscription, $buyer, $checkout)
    {
        return json_decode('
        {  
            "order":{  
               "pid":"cjnlrh3nwuk4i7tpocf0ao9ps",
               "receipt_id":"cjnlrh3nwuk4i7tpocf0ao9ps",
               "confirmation_code":4012153867,
               "customer_id":106,
               "store_owner_user_id":1,
               "buyer_pid":"d59u0ee777f5h4pjrzzomddw7",
               "buyer_first_name":"Adah",
               "buyer_last_name":"Reichel",
               "buyer_email":"rep@controlpad.com",
               "seller_pid":"abke3zidq5sa8uavui6i24gsv",
               "seller_name":"Controlpad Admin",
               "type_id":1,
               "transaction_id":"0jmgr8rk82xo3um",
               "gateway_reference_id":null,
               "total_price":8.15,
               "subtotal_price":3.21,
               "total_discount":0.06,
               "total_tax":0,
               "total_shipping":5,
               "tax_invoice_pid":null,
               "shipping_rate_id":493,
               "coupon_id":null,
               "paid_at":{  
                  "date":"2018-09-24 20:39:06.577463",
                  "timezone_type":3,
                  "timezone":"UTC"
               },
               "cash":false,
               "status":"unfulfilled",
               "source":"autoship",
               "deleted_at":null,
               "comm_engine_status_id":0,
               "tax_not_charged":true,
               "updated_at":"2018-09-24 20:39:06",
               "created_at":"2018-09-24 20:39:06",
               "type":"Corporate to Rep",
               "lines":[  
                  {  
                     "id":901,
                     "item_id":40,
                     "bundle_id":null,
                     "bundle_name":null,
                     "type":"Product",
                     "name":"",
                     "price":3.21,
                     "quantity":1,
                     "custom_sku":null,
                     "manufacturer_sku":null,
                     "created_at":null,
                     "updated_at":null,
                     "deleted_at":null,
                     "inventory_owner_id":1,
                     "in_comm_engine":0,
                     "discount_amount":0,
                     "discount_type_id":null,
                     "variant":"",
                     "option":"",
                     "event_id":null,
                     "pid":"3ye913z8d1phpk5y7sjc3ow28",
                     "inventory_owner_pid":"abke3zidq5sa8uavui6i24gsv",
                     "items":[  
                        {  
                           "id":40,
                           "sku":"9783181900710",
                           "option":"XXL",
                           "img_url":"https://s3-us-west-2.amazonaws.com/controlpad-hub/3bcf58ec4f1bc02a4eb9871f5a836145.jpeg",
                           "inventory_id":40,
                           "option_label":"Size",
                           "product_name":"Lily Bow Tie",
                           "variant_name":"Tan",
                           "premium_shipping_cost":null
                        }
                     ]
                  }
               ]
            },
            "result_code":1,
            "result":"Order created."
         }
        ');
    }
}
