<?php

use Illuminate\Database\Seeder;
use App\Models\CustomLinkType;

class CustomLinksTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CustomLinkType::create([
            'name' => 'Corporate Links for Rep Sites',
            'key' => 'corporate_rep_site_links'
        ]);
    }
}
