<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Item;
use App\Models\Price;
use App\Models\Role;
use App\Models\User;
use App\Models\Variant;

class ProductsTableSeeder extends DatabaseSeeder
{
    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        factory(Product::class, 22)->create();

        $products = Product::all();
        $visibilities = [2,3,5];
        $apex_user = User::find(config('site.apex_user_id'));
        foreach ($products as $product) {
            $product->user_pid = $apex_user->pid;
            $product->save();
            $variants = [];
            for ($x = rand(0, 2); $x < 3; $x++) {
                $variants[] = Variant::create([
                    'product_id' => $product->id,
                    'name' => $this->faker->colorName,
                    'option_label' => 'Size',
                ]);
            }
            foreach ($variants as $variant) {
                $variant->visibilities()->sync($visibilities);
                $itemTemplate = [
                'product_id' => $product->id,
                'print' => $variant->name,
                'weight' => rand(1, 10) * .1,
                'length' => rand(1, 25),
                'width' => rand(1, 25),
                'height' => rand(1, 25),
                'custom_sku' => $this->faker->isbn13(),
                'manufacturer_sku' => $this->faker->isbn13(),
                'variant_id' => $variant->id,
                ];
                $price = rand(200, 1000) / 100;
                $item = $this->createItem($itemTemplate, 'S', 1, $price);
                $item = $this->createItem($itemTemplate, 'M', 0, $price);
                $item = $this->createItem($itemTemplate, 'L', 0, $price);
                $item = $this->createItem($itemTemplate, 'XL', 0, $price);
                $item = $this->createItem($itemTemplate, 'XXL', 0, $price * 1.1);
            }
            $product->roles()->sync($visibilities);
        }
        DB::commit();
    }

    private function createPrice($dollarAmount, $item)
    {
        $wholesale = Price::create([
            'price_type_id' => 1,
            'price' => $dollarAmount,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $item->id
        ]);

        $retail = Price::create([
            'price_type_id' => 2,
            'price' => $dollarAmount * 1.3,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $item->id
        ]);

        $premium = Price::create([
            'price_type_id' => 3,
            'price' => $dollarAmount * 1.5,
            'priceable_type' => 'App\Models\Item',
            'priceable_id' => $item->id
        ]);
        return ['wholesale' => $wholesale->price, 'retail' => $retail->price, 'premium' => $premium->price];
    }

    private function createItem($itemTemplate, $size, $default, $dollarAmount)
    {
        $itemTemplate['size'] = $size;
        $itemTemplate['is_default'] = $default;
        $itemTemplate['custom_sku'] = $this->faker->isbn13();
        $itemTemplate['manufacturer_sku'] = $this->faker->isbn13();
        $item = Item::create($itemTemplate);
        $prices = $this->createPrice($dollarAmount, $item);
        $item->wholesale_price = $prices['wholesale'];
        $item->retail_price = $prices['retail'];
        $item->premium_price = $prices['premium'];
        $item->save();
        return $item;
    }
}
