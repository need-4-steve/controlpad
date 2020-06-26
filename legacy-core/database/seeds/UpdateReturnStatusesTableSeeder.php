<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\ReturnStatus;

class UpdateReturnStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datetime = Carbon::now()->toDateTimeString();
        Eloquent::unguard();
        $ReturnStatus1 = ReturnStatus::where('id', 1)->first();
        $ReturnStatus1->update([
            'name' => 'Open',
            'keyname' => str_slug('Open'),
            'created_at' => $datetime,
            'updated_at' => $datetime]);

        $ReturnStatus2 = ReturnStatus::where('id', 2)->first();
        $ReturnStatus2->update([
            'name' => 'Pending',
            'keyname' => str_slug('Pending'),
            'created_at' => $datetime, 'updated_at' => $datetime]);

        $ReturnStatus3 = ReturnStatus::where('id', 3)->first();
        $ReturnStatus3->update([
            'name' => 'Closed',
            'keyname' => str_slug('Closed'),
            'created_at' => $datetime,
            'updated_at' => $datetime]);

        $ReturnStatus4 = ReturnStatus::where('id', 4)->first();
        $ReturnStatus4->delete();
        $ReturnStatus5 = ReturnStatus::where('id', 5)->first();
        $ReturnStatus5->delete();
        $ReturnStatus6 = ReturnStatus::where('id', 6)->first();
        $ReturnStatus6->delete();
    }
}
