<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Invoice;
use App\Models\Inventory;
use App\Models\Role;
use Carbon\Carbon;

class InvoicesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        $users = User::where('role_id', Role::where('name', 'Rep')->first()->id)->orWhere('id', config('site.apex_user_id'))->get();
        $customers = User::where('role_id', Role::where('name', 'Customer')->first()->id)->get();

        foreach ($users as $user) {
            $subtotal = mt_rand(5, 15);
            $shipping = mt_rand(1, 5);

            $invoice = Invoice::create([
                'customer_id'         => $customers->random()->id,
                'store_owner_user_id' => $user->id,
                'token'               => time().str_random(20),
                'expires_at'          => Carbon::now()->addDays(30)->toDateTimeString(),
                'subtotal_price'      => $subtotal,
                'total_shipping'      => $shipping,
                'uid'                 => "I" . strtoupper(str_random(5))
            ]);
            $inventory = DB::table('inventories')->where('user_id', $user->id)->get()->random(3);
            $invoiceItems = [];
            foreach ($inventory as $inv) {
                $quantity = mt_rand(1, 5);
                DB::table('inventories')->where('id', $inv->id)->update(['quantity_staged' => $quantity]);
                $invoiceItems[] = [
                    'item_id'  => $inv->item_id,
                    'quantity' => $quantity
                ];
            }
            $invoice->invoiceItems()->sync($invoiceItems);
        }
        DB::commit();
    }
}
