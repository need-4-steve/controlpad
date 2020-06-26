<?php

use Illuminate\Database\Seeder;
use App\Models\CustomPage;

class UpdateCustomPages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages = CustomPage::get();
        foreach ($pages as $page) {
            $page->revised_at = $page->updated_at;
            $page->update();
        }
    }
}
