<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;

class CategoryHeadersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $users = User::where('role_id', 5)->orWhere('id', config('site.apex_user_id'))->get();
        $categories = Category::where('parent_id', null)->get();

        foreach ($users as $user) {
            foreach ($categories as $category) {
                $user->categories()->attach($category->id, ['header' => "20% off " . $category->name]);
            }
        }
        DB::commit();
    }
}
