<?php

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();
        $superadmin = Role::where('name', 'Superadmin')->first();
        $url = parse_url(config('app.url'));
        if (isset($url['host'])) {
            $url = $url['host'];
        } else {
            $url = $url['path'];
        }

        // apex user
        factory(User::class, 'admin', 1)->create([
            'id' => config('site.apex_user_id'),
            'first_name' => config('site.company_name'),
            'last_name' => 'Admin',
            'email' => 'superadmin@' . $url,
            'role_id' => $superadmin->id,
            'seller_type_id' => 1,
            'sponsor_id' => 0,
            'join_date' => Carbon::now()
        ]);

        // Controlpad Rep
        factory(User::class, 'rep', 1)->create([
            'id' => 106,
            'first_name' => 'Adah',
            'last_name' => 'Reichel',
            'email' => 'rep@controlpad.com',
            'public_id' => 'rep',
            'seller_type_id' => 2, // reseller
            'sponsor_id' => config('site.apex_user_id'),
            'join_date' => Carbon::now()
        ]);

        // Controlpad Affiliate
        factory(User::class, 'rep', 1)->create([
            'id' => 107,
            'first_name' => 'Adrian',
            'last_name' => 'Riker',
            'email' => 'affiliate@controlpad.com',
            'public_id' => 'affiliate',
            'seller_type_id' => 1, // affiliate
            'sponsor_id' => config('site.apex_user_id'),
            'join_date' => Carbon::now()
        ]);

        // Controlpad Admin
        factory(User::class, 'admin', 1)->create([
            'id' => 108,
            'first_name' => "Buddy",
            'last_name' => "D'Amore",
            'email' => 'admin@controlpad.com',
            'join_date' => Carbon::now()
        ]);

        // Controlpad Superadmin
        factory(User::class, 'admin', 1)->create([
            'id' => 109,
            'email' => 'superadmin@controlpad.com',
            'role_id' => $superadmin->id,
            'join_date' => Carbon::now()
        ]);

        // Controlpad Customer
        factory(User::class, 'customer', 1)->create([
            'email' => 'customer@controlpad.com',
            'join_date' => Carbon::now()
        ]);

        DB::update("ALTER TABLE users AUTO_INCREMENT = 200;");
        DB::commit();
    }
}
