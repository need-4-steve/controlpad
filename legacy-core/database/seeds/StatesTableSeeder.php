<?php

use App\Models\State;
use Faker\Factory as Faker;

class StatesTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();
        State::create(['abbr'=>'AL', 'full_name'=>"Alabama"]);
        State::create(['abbr'=>'AK', 'full_name'=>"Alaska"]);
        State::create(['abbr'=>'AZ', 'full_name'=>"Arizona"]);
        State::create(['abbr'=>'AR', 'full_name'=>"Arkansas"]);
        State::create(['abbr'=>'CA', 'full_name'=>"California"]);
        State::create(['abbr'=>'CO', 'full_name'=>"Colorado"]);
        State::create(['abbr'=>'CT', 'full_name'=>"Connecticut"]);
        State::create(['abbr'=>'DE', 'full_name'=>"Delaware"]);
        State::create(['abbr'=>'DC', 'full_name'=>"District Of Columbia"]);
        State::create(['abbr'=>'FL', 'full_name'=>"Florida"]);
        State::create(['abbr'=>'GA', 'full_name'=>"Georgia"]);
        State::create(['abbr'=>'HI', 'full_name'=>"Hawaii"]);
        State::create(['abbr'=>'ID', 'full_name'=>"Idaho"]);
        State::create(['abbr'=>'IL', 'full_name'=>"Illinois"]);
        State::create(['abbr'=>'IN', 'full_name'=>"Indiana"]);
        State::create(['abbr'=>'IA', 'full_name'=>"Iowa"]);
        State::create(['abbr'=>'KS', 'full_name'=>"Kansas"]);
        State::create(['abbr'=>'KY', 'full_name'=>"Kentucky"]);
        State::create(['abbr'=>'LA', 'full_name'=>"Louisiana"]);
        State::create(['abbr'=>'ME', 'full_name'=>"Maine"]);
        State::create(['abbr'=>'MD', 'full_name'=>"Maryland"]);
        State::create(['abbr'=>'MA', 'full_name'=>"Massachusetts"]);
        State::create(['abbr'=>'MI', 'full_name'=>"Michigan"]);
        State::create(['abbr'=>'MN', 'full_name'=>"Minnesota"]);
        State::create(['abbr'=>'MS', 'full_name'=>"Mississippi"]);
        State::create(['abbr'=>'MO', 'full_name'=>"Missouri"]);
        State::create(['abbr'=>'MT', 'full_name'=>"Montana"]);
        State::create(['abbr'=>'NE', 'full_name'=>"Nebraska"]);
        State::create(['abbr'=>'NV', 'full_name'=>"Nevada"]);
        State::create(['abbr'=>'NH', 'full_name'=>"New Hampshire"]);
        State::create(['abbr'=>'NJ', 'full_name'=>"New Jersey"]);
        State::create(['abbr'=>'NM', 'full_name'=>"New Mexico"]);
        State::create(['abbr'=>'NY', 'full_name'=>"New York"]);
        State::create(['abbr'=>'NC', 'full_name'=>"North Carolina"]);
        State::create(['abbr'=>'ND', 'full_name'=>"North Dakota"]);
        State::create(['abbr'=>'OH', 'full_name'=>"Ohio"]);
        State::create(['abbr'=>'OK', 'full_name'=>"Oklahoma"]);
        State::create(['abbr'=>'OR', 'full_name'=>"Oregon"]);
        State::create(['abbr'=>'PA', 'full_name'=>"Pennsylvania"]);
        State::create(['abbr'=>'RI', 'full_name'=>"Rhode Island"]);
        State::create(['abbr'=>'SC', 'full_name'=>"South Carolina"]);
        State::create(['abbr'=>'SD', 'full_name'=>"South Dakota"]);
        State::create(['abbr'=>'TN', 'full_name'=>"Tennessee"]);
        State::create(['abbr'=>'TX', 'full_name'=>"Texas"]);
        State::create(['abbr'=>'UT', 'full_name'=>"Utah"]);
        State::create(['abbr'=>'VT', 'full_name'=>"Vermont"]);
        State::create(['abbr'=>'VA', 'full_name'=>"Virginia"]);
        State::create(['abbr'=>'WA', 'full_name'=>"Washington"]);
        State::create(['abbr'=>'WV', 'full_name'=>"West Virginia"]);
        State::create(['abbr'=>'WI', 'full_name'=>"Wisconsin"]);
        State::create(['abbr'=>'WY', 'full_name'=>"Wyoming"]);
        DB::commit();
    }
}
