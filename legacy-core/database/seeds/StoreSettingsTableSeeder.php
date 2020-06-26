<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Repositories\Eloquent\StoreSettingRepository;

class StoreSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $storeSettingRepo = new StoreSettingRepository;
        $reps = User::where('role_id', 5)->doesntHave('storeSettings')->get(); // 5 is the rep role_id
        DB::transaction(function () use ($reps, $storeSettingRepo) {
            foreach ($reps as $rep) {
                $storeSettingRepo->createSettings($rep->id, $rep->full_name);
            }
        });
    }
}
