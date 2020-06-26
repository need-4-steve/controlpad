<?php

use App\Models\CustomPage;
use Illuminate\Database\Seeder;

class CustomPageRepTerms extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        CustomPage::create([
            'title' => 'Rep Terms',
            'slug' => 'rep-terms',
            'content' => ' '
        ]);
        DB::commit();
    }
}
