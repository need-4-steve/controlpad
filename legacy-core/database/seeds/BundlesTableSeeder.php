<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Item;
use App\Models\Price;
use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\Role;
use App\Models\User;

class BundlesTableSeeder extends DatabaseSeeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        factory(Bundle::class, 20)->create();

        $bundles = Bundle::all();
        $apex_user = User::find(config('site.apex_user_id'));
        $visibilities = [5];
        $items = Item::where('id', '<', 100)->get();

        foreach ($bundles as $bundle) {
            $price = Price::create([
                'price_type_id' => 1,
                'price' => rand(20000, 100000) / 100,
                'priceable_type' => Bundle::class,
                'priceable_id' => $bundle->id
            ]);
            $bundle->wholesale_price = $price->price;
            $bundle->user_pid = $apex_user->pid;
            $bundle->save();
            $bundleItems = $items->random(10);
            foreach ($bundleItems as $bundleItem) {
                $bundle->items()->attach($bundleItem, ['quantity' => rand(1, 5)]);
            }
            $bundle->roles()->sync($visibilities);
        }
        // save a bundle for a rep for testing purposes
        $bundle = Bundle::first();
        $bundle->user_id = 106;
        $bundle->user_pid = User::find(106)->pid;
        $bundle->save();
        DB::commit();
    }
}
