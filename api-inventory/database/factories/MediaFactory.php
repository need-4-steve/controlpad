<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\Media::class, function (Faker\Generator $faker) {
    return [
        "type" => "Image",
        "filename" => "",
        "url" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc.jpg",
        "url_xl" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_xl.jpg",
        "url_lg" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_lg.jpg",
        "url_md" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_md.jpg",
        "url_sm" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_sm.jpg",
        "url_xs" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_xs.jpg",
        "url_xxs" => "https://s3-us-west-2.amazonaws.com/controlpad-hub/6cb70b65adf008359f33182de1ba3bbc-url_xxs.jpg",
        "title" => "Default Belt",
        "description" => "Belt",
        "height" => 0,
        "width" => 0,
        "size" => 0,
        "extension" => "jpg",
        "disabled_at" => null,
        "created_at" => "2018-02-21 21:02:51",
        "updated_at" => "2018-02-21 21:02:51",
        "user_id" => 1,
        "is_public" => 0
    ];
});
