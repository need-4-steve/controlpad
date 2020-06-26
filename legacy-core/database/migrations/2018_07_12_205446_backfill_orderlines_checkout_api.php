<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CPCommon\Pid\Pid;

class BackfillOrderlinesCheckoutApi extends Migration
{

    private $mediaMap;
    private $itemMap;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo "Starting orderline migration: " . microtime(true) . "\n";
        // Create a temporary table to batch pids, items into that will be joined after
        // This prevents iteration of updates
        // This should skip bundle-item lines, we will eventually remove those
        Schema::dropIfExists('orderline_fill');
        Schema::create('orderline_fill', function (Blueprint $table) {
            $table->integer('id')->unique();
            $table->string('pid', 25);
            $table->text('items');
        });
        $this->mediaMap = $this->getMediaMap();
        $this->itemMap = $this->getItemMap();
        $this->mapMediaToItems($this->itemMap);

        // Convert bundles and insert into temp table
        $lastId = 0;
        echo "Running bundles: " . microtime(true) . "\n";
        while (true) {
            $data = []; // array for converting and temp insert
            $bundleLines = DB::table('orderlines as ol')
            ->select('ol.id', 'ol.bundle_id', 'ol.inventory_owner_id as user_id')
            ->whereNull('ol.item_id')
            ->where('ol.id', '>', $lastId)
            ->limit(1000)->get();
            if (empty($bundleLines) || count($bundleLines) == 0) {
                break;
            }
            $lastId = $bundleLines->last()->id;

            foreach ($bundleLines as $key => $bundleLine) {
                $items = $this->getItemsForBundle($bundleLine);
                foreach ($items as $key => $item) { // Merge item data
                    $items[$key] = array_merge((array)$item, (array)$this->itemMap[$item->id]);
                }
                $data[] = [
                    'id' => $bundleLine->id,
                    'pid' => Pid::create(),
                    'items' => json_encode($items)
                ];
            }
            DB::table('orderline_fill')->insert($data);
            $count = DB::update(
                'update orderlines as ol ' .
                'join orderline_fill as olf on olf.id = ol.id ' .
                'set ol.pid = olf.pid, ol.items = olf.items'
            );
            $deleteCount = DB::delete('delete from orderline_fill');
        }
        // Convert items and insert into temp table
        $lastId = 0; // reset lastId
        echo "Running items: " . microtime(true) . "\n";
        echo "May take a couple minutes\n";
        while (true) {
            $data = []; // reset data
            $itemLines = DB::table('orderlines as ol')
            ->select('ol.id', 'ol.item_id')
            ->whereNull('ol.bundle_id')
            ->where('ol.id', '>', $lastId)
            ->limit(7500)->get();
            if (empty($itemLines) || count($itemLines) == 0) {
                break;
            }
            $lastId = $itemLines->last()->id;
            foreach ($itemLines as $key => $itemLine) {
                $data[] = [
                    'id' => $itemLine->id,
                    'pid' => Pid::create(),
                    'items' => json_encode([
                        (array)$this->itemMap[$itemLine->item_id]
                    ])
                ];
            }
            DB::table('orderline_fill')->insert($data);
            $count = DB::update(
                'update orderlines as ol ' .
                'join orderline_fill as olf on olf.id = ol.id ' .
                'set ol.pid = olf.pid, ol.items = olf.items'
            );
            $deleteCount = DB::delete('delete from orderline_fill');
        }

        echo "Merging inventory owner pid: " . microtime(true) . "\n";
        DB::update(
            'update orderlines as ol ' .
            'left join users as owner on owner.id = ol.inventory_owner_id ' .
            'set ol.inventory_owner_pid = owner.pid'
        );
        Schema::dropIfExists('orderline_fill');
        echo "Finished orderline migration: " . microtime(true) . "\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::update('update orderlines set pid = null, items = null, inventory_owner_pid = null');
    }

    private function getItemMap()
    {
        return DB::table('items')->select(
            'items.id as id',
            'items.product_id',
            'products.name as product_name',
            'items.variant_id',
            'items.size as option',
            'items.manufacturer_sku as sku',
            'variants.name as variant_name',
            'variants.option_label as option_label',
            'items.premium_shipping_cost'
        )
        ->leftJoin('variants', 'variants.id', '=', 'items.variant_id')
        ->leftJoin('products', 'products.id', '=', 'items.product_id')
        ->get()->keyBy('id');
    }

    private function mapMediaToItems($items)
    {
        foreach ($items as $key => $item) {
            if (isset($this->mediaMap['variant_media'][$item->variant_id])) {
                $item->img_url = $this->mediaMap['variant_media'][$item->variant_id]->url;
            } elseif (isset($this->mediaMap['product_media'][$item->product_id])) {
                $item->img_url = $this->mediaMap['product_media'][$item->product_id]->url;
            } else {
                $item->img_url = null;
            }
            unset($item->product_id);
            unset($item->variant_id);
        }
    }

    private function getItemsForBundle($bundleLine)
    {
        return DB::table('items')->select(
            'items.id as id',
            'bundle_item.quantity as quantity'
        )
        ->join('bundle_item', 'bundle_item.item_id', '=', 'items.id')
        ->where('bundle_item.bundle_id', '=', $bundleLine->bundle_id)
        ->get();
    }

    private function getImageUrlMapForProducts()
    {
        // Pull the first Image url per Product then map to the mediable_id for easy access
        return DB::table('media')->selectRaw('mediables.mediable_id, coalesce(media.url) as url')
            ->join('mediables', 'mediables.media_id', '=', 'media.id')
            ->where('mediables.mediable_type', 'App\Models\Product')
            ->where('media.type', '=', 'Image')
            ->groupBy('mediables.mediable_id')
            ->get()->keyBy('mediable_id');
    }

    private function getImageUrlMapForVariants()
    {
        // Pull the first Image url per Variant and map to variant id for easy access
        return DB::table('media')->selectRaw('media_variant.variant_id, coalesce(media.url) as url')
            ->join('media_variant', 'media_variant.media_id', '=', 'media.id')
            ->where('media.type', '=', 'Image')
            ->groupBy('media_variant.variant_id')
            ->get()->keyBy('variant_id');
    }

    private function getMediaMap()
    {
        return [
            'product_media' => $this->getImageUrlMapForProducts(),
            'variant_media' => $this->getImageUrlMapForVariants(),
        ];
    }
}
