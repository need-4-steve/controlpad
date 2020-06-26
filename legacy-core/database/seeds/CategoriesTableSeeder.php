<?php

use App\Models\Category;

class CategoriesTableSeeder extends DatabaseSeeder
{

    public function run()
    {
        $categories = [
            'Belts' => [
                'Adjustable',
                'Beaded',
                'Leather'
            ],
            'Dresses' => [
                'Daytime',
                'Work',
                'Party & Cocktail',
                'Formal'
            ],
            'Jeans' => [
                'Bootcut',
                'Boyfriend',
                'Curvy',
                'Skinny',
                'Jeggings'
            ],
            'Shoes' => [
                'Boots',
                'Comfort',
                'Evening & Bridal',
                'Flats'
            ],
            'Ties' => [
                'Bow Tie',
                'Patterned',
                'Solid'
            ],
            'Vests' => [
                "Men's",
                "Women's",
                'Sport',
                'Dress',
                'Sweater'
            ]
        ];

        $placement = 0;
        DB::beginTransaction();
        foreach ($categories as $key => $subcategories) {
            $category = factory(Category::class, 1)->create([
                'name' => $key,
                'placement' => $placement++
            ]);
            $subPlacement = 0;
            foreach ($subcategories as $subcategory) {
                factory(Category::class, 1)->create([
                    'name' => $subcategory,
                    'placement' => $subPlacement++,
                    'parent_id' => $category->id,
                    'level' => 1
                ]);
            }
        }
        DB::commit();
    }
}
