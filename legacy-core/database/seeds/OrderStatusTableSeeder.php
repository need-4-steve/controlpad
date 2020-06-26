<?php
use Illuminate\Database\Seeder;
use App\Models\OrderStatus;
class OrderStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        OrderStatus::create([
            'name'          => 'unfulfilled',
            'position'   => 1,
            'visible'       => true
        ]);
        OrderStatus::create([
            'name'          => 'fulfilled',
            'position'   => 2,
            'visible'       => true
        ]);
        OrderStatus::create([
            'name'          => 'hold',
            'position'   => 3,
            'visible'       => false
        ]);
        OrderStatus::create([
            'name'          => 'cancelled',
            'position'   => 4,
            'visible'       => false
        ]);
        OrderStatus::create([
            'name'          => 'self-pickup',
            'position'   => 5,
            'visible'       => false
        ]);
        DB::table('order_status')->update(['default' => true]);
        DB::commit();
        $orderStatus = OrderStatus::select('id', 'name', 'position', 'visible', 'default')->orderBy('position')->get();
        cache()->forever('order-status', $orderStatus);
    }
}
