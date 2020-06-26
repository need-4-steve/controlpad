<?php

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Inventory;
use App\Models\User;
use Carbon\Carbon;

class UpdateInvetoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $apex_user = User::find(config('site.apex_user_id'));
        $items = Item::all();
        for ($i = 105; $i <= 109; $i++) {
            DB::table('inventories')->where('user_id', $apex_user->id)
            ->where('item_id', $items[$i]->id)
            ->update(['quantity_available' => 0,
            'expires_at' => Carbon::now()->addYears(2)->toDateTimeString()
            ]);
        }
        $inventory = Inventory::where('user_id', 106)->whereBetween('item_id', [105, 109])->get();
        if ($inventory) {
            foreach ($inventory as $inv) {
                DB::table('inventories')->where('item_id', $inv->item_id)->delete();
            }
        }

        DB::table('inventories')->insert([
            'user_id'            => 106,
            'owner_id'           => 106,
            'item_id'            => 107,
            'quantity_available' => 0,
            'quantity_staged'    => 0,
            'created_at'         => Carbon::now(),
            'updated_at'         => Carbon::now(),
            'purchased_at'       => Carbon::now(),
            'expires_at'         => Carbon::now()->addYears(2),
        ]);

        DB::commit();
    }
}
