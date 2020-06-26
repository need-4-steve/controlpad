<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;

class TagController extends Controller
{
    /**
    * Get names for all product tags.
    *
    * @return Response
    */
    public function getProductTags()
    {
        $productTags = Tag::where('taggable_type', 'App\Models\Product')->select('name')->get();
        return response()->json($productTags, HTTP_SUCCESS);
    }
}
