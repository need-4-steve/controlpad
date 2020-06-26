<?php

namespace App\Services\Testing\V0;

use App\Services\Interfaces\V0\SettingsServiceInterface;
use DB;
use Carbon\Carbon;

class SettingsService implements SettingsServiceInterface
{
    public function getSettings($orgId = null)
    {
        return json_decode('
        {  
            "address":{  
               "key":"address",
               "value":"553 East Technology Ave Building C, Ste 1300 Orem, Utah 84097",
               "show":"true"
            },
            "autoship_display_name":{  
               "key":"autoship_display_name",
               "value":"Subscribe & Save",
               "show":"false"
            },
            "back_office_logo":{  
               "key":"back_office_logo",
               "value":"https://controlpad-hub.s3.us-west-2.amazonaws.com/cp_2723d092b63885e0d7c260cc007e8b9d/da8603c6c9c36ba890f69dad931f52bc-url_md.png",
               "show":"1"
            },
            "company_name":{  
               "key":"company_name",
               "value":"Controlpad",
               "show":"1"
            },
            "from_email":{  
               "key":"from_email",
               "value":"no-reply@controlpad.com",
               "show":"true"
            }
         }
        ');
    }
}
