<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HistoryTrait;
use App\Models\StoreSettingsKey;

class StoreSetting extends Model
{

    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'store_settings';

    protected $fillable = [
        'key',
        'value',
        'user_id',
        'user_pid',
        'key_id',
    ];

    public static $updateRules = [
        'store_slogan'              => 'string',
        'banner_image_1'            => 'string',
        'banner_image_2'            => 'string',
        'banner_image_3'            => 'string',
        'banner_text_title'         => 'string',
        'banner_text_subtitle'      => 'string',
        'banner_text_caption'       => 'string',
        'category_text_1'           => 'string',
        'category_text_2'           => 'string',
        'story_profile_image'       => 'string',
        'story_grid_image_1'        => 'string',
        'story_grid_image_2'        => 'string',
        'story_grid_image_3'        => 'string',
        'story_grid_image_4'        => 'string',
        'story_title'               => 'string',
        'story_heading_1'           => 'string',
        'story_heading_2'           => 'string',
        'story_heading_3'           => 'string',
        'story_heading_4'           => 'string',
        'story_text_1'              => 'string',
        'story_text_2'              => 'string',
        'story_text_3'              => 'string',
        'story_text_4'              => 'string',
        'story_intro'               => 'string',
        'display_name'              => 'string',
        'shipping_fulfillment_time' => 'string',
        'logo'                      => 'string',
        'show_store_banner'         => 'boolean'
    ];
}
