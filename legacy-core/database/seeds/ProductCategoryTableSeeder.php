<?php

use App\Models\Category;
use App\Models\Product;

class ProductCategoryTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        DB::beginTransaction();
        $products = Product::all();
        foreach ($products as $product) {
            $category_ids = [];
            $category = Category::where('id', rand(1, 18))->first();
            $category_ids[] = $category['id'];
            while ($category['parent_id'] != 0) {
                $category = Category::where('id', $category['parent_id'])->first();
                $category_ids[] = $category['id'];
            }
            $product->category()->sync($category_ids);
        }
        DB::commit();
    }
}
