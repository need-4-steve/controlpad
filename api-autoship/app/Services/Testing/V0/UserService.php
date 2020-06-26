<?php

namespace App\Services\Testing\V0;

use App\Services\Interfaces\V0\UserServiceInterface;

class UserService implements UserServiceInterface
{
    public function getBuyer($buyerPid, $subscription)
    {
        return json_decode('
        {  
           "id":106,
           "pid":"d59u0ee777f5h4pjrzzomddw7",
           "first_name":"Adah",
           "last_name":"Reichel",
           "sponsor_id":1,
           "public_id":"rep",
           "email":"rep@controlpad.com",
           "created_at":"2018-08-30 22:04:14",
           "role_id":5,
           "role":"Rep",
           "billing_address_name":"Adah Reichel",
           "shipping_address_name":"Adah Reichel",
           "business_address_name":"Adah Reichel",
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
           "business_address":{  
              "name":"Adah Reichel",
              "line_1":"452 State St",
              "line_2":"",
              "city":"Orem",
              "zip":"84058",
              "state":"UT"
           },
           "card":{
              "id":1,
              "token":"a38ahf8i4hahgr3dd",
              "user_id":106,
              "type":"subscription",
              "card_type":"Visa",
              "card_digits":"************1111",
              "expiration":"0822",
              "created_at":"2018-08-31 17:22:05",
              "updated_at":"2018-08-31 17:22:05",
              "gateway_customer_id":"9sjak3cs9z0vlwe"
           }
        }');
    }

    public function getUser($buyerPid, $subscription)
    {
        return json_decode('
        {  
            "id":106,
            "pid":"d59u0ee777f5h4pjrzzomddw7",
            "first_name":"Adah",
            "last_name":"Reichel",
            "sponsor_id":1,
            "public_id":"rep",
            "email":"rep@controlpad.com",
            "created_at":"2018-08-30 22:04:14",
            "role_id":5,
            "role":"Rep",
            "billing_address_name":"Adah Reichel",
            "shipping_address_name":"Adah Reichel",
            "business_address_name":"Adah Reichel",
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
            "business_address":{
                "name":"Adah Reichel",
                "line_1":"452 State St",
                "line_2":"",
                "city":"Orem",
                "zip":"84058",
                "state":"UT"
            },
            "card":{
                "id":1,
                "token":"a38ahf8i4hahgr3dd",
                "user_id":106,
                "type":"subscription",
                "card_type":"Visa",
                "card_digits":"************1111",
                "expiration":"0822",
                "created_at":"2018-08-31 17:22:05",
                "updated_at":"2018-08-31 17:22:05",
                "gateway_customer_id":"9sjak3cs9z0vlwe"
            }
        }');
    }
}
