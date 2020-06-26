<?php

use App\Models\Announcement;

class AnnouncementsTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();

        $faker = Faker\Factory::create();

        for ($i = 1; $i <= 20; $i++) {
            $min_time = time() - 2629743; // 1 month ago
            $max_time = time() + 2629743; // 1 month from today
            $start_time = $faker->numberBetween($min = $min_time, $max = $max_time);
            $announcement= array(
                'title' => $faker->word,
                'description' => $faker->text,
                'body' => $faker->text,
            );
            Announcement::create($announcement);
        }
        DB::commit();
    }
}
