<?php

use App\Models\User;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\Price;
use Carbon\Carbon;

class InventoriesTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();
        $faker = Faker\Factory::create();
        $apex_user = User::find(config('site.apex_user_id'));
        $items = Item::all();
        $purchased_at = Carbon::now()->subMonths(9);

        foreach ($items as $item) {
            $inventory = DB::table('inventories')->insert([
                'user_id'            => $apex_user->id,
                'user_pid'           => $apex_user->pid,
                'item_id'            => $item->id,
                'quantity_available' => mt_rand(100, 10000),
                'quantity_staged'    => 0,
                'owner_id'           => $apex_user->id,
                'owner_pid'          => $apex_user->pid,
                'created_at'         => $purchased_at,
                'updated_at'         => $purchased_at,
                'purchased_at'       => $purchased_at,
            ]);
        }
        $users = User::whereNotIn('id', [config('site.apex_user_id')])->where('role_id', 5)->get();
        foreach ($users as $user) {
            for ($i = 1; $i <= 20; $i++) {
                $purchase = Carbon::now()->subMonths(mt_rand(1, 6));
                try {
                    $price = mt_rand(5000, 17500) / 100;
                    // needs to be in a try catch because of database restrictions for user_id and item_id
                    $inventory = Inventory::create([
                        'user_id'      => $user->id,
                        'user_pid'     => $user->pid,
                        'item_id'      => $items->random()->id,
                        'owner_id'     => $user->id,
                        'owner_pid'    => $user->pid,
                        'quantity_available' => mt_rand(20, 50),
                        'created_at' => $purchase,
                        'updated_at' => $purchase,
                        'purchased_at' => $purchase,
                        'inventory_price' => $price,
                    ]);
                    $this->createPrice($inventory, $price);
                } catch (\Exception $e) {
                }
            }
        }
        DB::commit();
    }

    private function createPrice($inventory, $price)
    {
        $price = Price::create([
            'price_type_id'  => 4,
            'price'          => $price,
            'priceable_type' => 'App\Models\Inventory',
            'priceable_id'   => $inventory->id
        ]);
    }
}
