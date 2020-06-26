<?php

use Illuminate\Database\Seeder;

class ChalkProductRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $skus = [
            "10217118",
            "10217120",
            "10217121",
            "10217128",
            "10317110",
            "10317111",
            "10317123",
            "10317131",
            "10317132",
            "10317133",
            "10317139",
            "10317141",
            "10317144",
            "10317157",
            "10417108",
            "10417112",
            "10417113",
            "10417119",
            "10417124",
            "10417125",
            "10417126",
            "10417128",
            "10417129",
            "10417133",
            "10417134",
            "10417135",
            "10517104",
            "10517108",
            "10517124",
            "10617122",
            "10617123",
            "10617124",
            "10617125",
            "10617126",
            "10617129",
            "10617135",
            "10617136",
            "10717104",
            "10717106",
            "10817102",
            "A181101",
            "A181102",
            "A181103",
            "A181104",
            "B181101",
            "B181102",
            "B181103",
            "B181104",
            "B181105",
            "B181106",
            "B181107",
            "B181108",
            "B181109",
            "B181110",
            "B181111",
            "B181112",
            "B181113",
            "B181114",
            "B181115",
            "B181116",
            "B181117",
            "B181118",
            "B181119",
            "B181120",
            "B181121",
            "B181122",
            "B181123",
            "B181124",
            "C181101",
            "C181102",
            "C181103",
            "C181104",
            "C181105",
            "C181106",
            "C181107",
            "C181108",
            "C181110",
            "C181111",
            "D181101",
            "D181102",
            "D181103",
            "D181104",
            "D181105",
            "E181101",
            "E181102",
            "E181103",
            "32117105",
            "S181101",
            "S181102",
            "S181103",
            "S181104",
            "S181105",
            "S181106",
            "S181107",
            "S181108",
            "10417127",
            "10617118",
            "10617119",
            "10617121",
            "10717105",
            "10417131",
            "21118101",
            "21118102",
            "21118103",
            "21118104",
            "21118105",
            "21118106",
            "21118107",
            "21118108",
            "21118110",
            "21118111",
        ];

        $products = DB::table('products')
                        ->select('products.id', 'products.name', 'rep_role.visibility_id as rep_role_id', 'customer_role.visibility_id as customer_role_id')
                        ->join('items', 'products.id', '=', 'items.product_id')
                        ->leftJoin('product_visibility as rep_role', 'products.id', '=', DB::raw('rep_role.product_id AND rep_role.visibility_id = 5'))
                        ->leftJoin('product_visibility as customer_role', 'products.id', '=', DB::raw('customer_role.product_id AND customer_role.visibility_id = 3'))
                        ->whereIn('items.manufacturer_sku', $skus)
                        ->groupBy('products.id')
                        ->get();

        DB::beginTransaction();
        foreach ($products as $product) {
            echo "Updating roles on ".$product->name."\n";
            if ($product->rep_role_id != 5) {
                DB::table('product_role')->insert([
                    'product_id' => $product->id,
                    'visibility_id' => 5
                ]);
            }

            if ($product->customer_role_id != 3) {
                DB::table('product_role')->insert([
                    'product_id' => $product->id,
                    'visibility_id' => 3
                ]);
            }
        }
        DB::commit();
    }
}
