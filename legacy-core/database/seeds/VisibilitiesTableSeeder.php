<?php

use Illuminate\Database\Seeder;

class VisibilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('visibilities')->insert([
            [
                "id" => 1,
                "name" => "Corp Retail",
                "description" => "Retail store for corporate.",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 2,
                "name" => "Affiliate",
                "description" => "Affiliate stores.",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 3,
                "name" => "Reseller Retail",
                "description" => "Replicated sites.",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 4,
                "name" => "Registration",
                "description" => "Registration purchase",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 5,
                "name" => "Wholesale",
                "description" => "Wholesale purchase.",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ],
            [
                "id" => 6,
                "name" => "Preferred Retail",
                "description" => "Preferred Retail purchase in backoffice.",
                "created_at" => date('Y-m-d H:i:s'),
                "updated_at" => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
