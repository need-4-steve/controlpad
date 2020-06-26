<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Media;
use App\Models\Category;
use App\Models\Bundle;

class ControlpadMediaTableSeeder extends Seeder
{
    public function __construct()
    {
        $this->images = [
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc', 'title' => 'Default Belt', 'description' => 'Belt', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/551754815c8bd1df7cea3affaab113d8', 'title' => 'Default Dress', 'description' => 'Dress', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/75ac7b31038099969dfbb9b1c1d2f2a0', 'title' => 'Default Jean', 'description' => 'Jean', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/b6e39c1cd5664063a04efcfc43f18086', 'title' => 'Default Shoes', 'description' => 'Shoes', 'ext' => 'png'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/b36f84aa0e6d92850c1ac5e070d351ab', 'title' => 'Default Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/57c68c450716f30ed5e2d48b12584f75', 'title' => 'Default Vest', 'description' => 'Vest', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/efa9d3df24c7538c18c53faf9751cb82', 'title' => 'Belt', 'description' => 'Belt', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/574a2139cce50578fd5115862d4503d6', 'title' => 'Belt', 'description' => 'Belt', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/6ef60cc6fbeeccb7c107ed23ca7bdc89', 'title' => 'Belt', 'description' => 'Belt', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/8cdfdee7a5833978f98eb67376cddeb8', 'title' => 'Bow Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/5c5dbd63a9876b363f64642a5926bd6c', 'title' => 'Bow Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/3bcf58ec4f1bc02a4eb9871f5a836145', 'title' => 'Bow Tie', 'description' => 'Tie', 'ext' => 'jpeg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/6bded9005f617a0717bd38fe1473a7db', 'title' => 'Bow Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/9a3f4294edb88688c87bfabeda1f08a6', 'title' => 'Bow Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/658d0081a2ebc01d849c56e8cfb18b05', 'title' => 'Dress', 'description' => 'Dress', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/d69bbb34ba47139f4fcde037ce8264c7', 'title' => 'Dress', 'description' => 'Dress', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/107be99360f7a4add71da792d50fd01f', 'title' => 'Jeans', 'description' => 'Jean', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/bf9c5bbb567b5a7fbbb3921a250b3510', 'title' => 'Jeans', 'description' => 'Jean', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/1c5c7c410a840b4756ef28a1f5015612', 'title' => 'Jeans', 'description' => 'Jean', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/15308cad20fd011b851a151f75367cd1', 'title' => 'Shoes', 'description' => 'Shoes', 'ext' => 'png'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/b12ee6f49c9dcc37078d58d1b679a019', 'title' => 'Shoes', 'description' => 'Shoes', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/5869bf6c5ea1b52743ef21d76e1b92dd', 'title' => 'Shoes', 'description' => 'Shoes', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/78a0ed2de1d0a24fff4c1b0fdc907c19', 'title' => 'Shoes', 'description' => 'Shoes', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/300abb042c715bd2cc44ce5bde39805e', 'title' => 'Tie', 'description' => 'Tie', 'ext' => 'jpeg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/f803f587daac1074535e34b09c5c203f', 'title' => 'Tie', 'description' => 'Tie', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/4f6398cbd22c53dd3996241996ee6d83', 'title' => 'Tie', 'description' => 'Tie', 'ext' => 'jpeg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/10c73764320fc6618ee581801d55ff14', 'title' => 'Vest', 'description' => 'Vest', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/aca7971ee691696bbaa6d35f0d976295', 'title' => 'Vest', 'description' => 'Vest', 'ext' => 'jpg'],
            ['url' => 'https://s3-us-west-2.amazonaws.com/controlpad-hub/9c92f9fc81b3086a4aa154dd16f059f0', 'title' => 'Vest', 'description' => 'Vest', 'ext' => 'jpg'],
        ];
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        foreach ($this->images as $image) {
            $this->createMedia($image);
        }

        $defaultImages = Media::whereRaw("media.title LIKE '%Default%'")->get();
        $productImages = Media::whereRaw("media.title NOT LIKE '%Default%'")->get();

        foreach ($defaultImages as $image) {
            $category = Category::where('name', 'LIKE', '%'.$image->description.'%')->first();
            $image->categories()->attach($category);
        }

        $products = Product::with('variants')->get();
        foreach ($products as $key => $product) {
            $product->media()->attach($productImages[$key]);
            $product->name .= " ".$productImages[$key]['title'];
            $product->save();
            $product->category()->attach(Category::whereRaw("categories.name LIKE '%".$productImages[$key]['description']."%'")->first());
            foreach ($product->variants as $variant) {
                $variant->media()->attach($productImages->where('description', $productImages[$key]['description'])->random(1));
            }
        }

        $bundles = Bundle::all();
        foreach ($bundles as $bundle) {
            foreach ($bundle->items as $item) {
                $bundle->media()->attach($item->product->media->first());
            }
            $bundle->category()->attach($bundle->items()->first()->product()->first()->category()->first());
        }
        DB::commit();
    }

    private function createMedia($image)
    {
        $url = $image['url'];
        $ext = $image['ext'];

        $media = [
            'type' => 'Image',
            'url' => $url.".".$ext,
            'url_xxs' => $url . '-url_xxs.'.$ext,
            'url_xs' => $url . '-url_xs.'.$ext,
            'url_sm' => $url . '-url_sm.'.$ext,
            'url_md' => $url . '-url_md.'.$ext,
            'url_lg' => $url . '-url_lg.'.$ext,
            'url_xl' => $url . '-url_xl.'.$ext,
            'title' => $image['title'],
            'description' => $image['description'],
            'extension' => $ext,
            'user_id' => config('site.apex_user_id')
        ];

        Media::create($media);
    }
}
