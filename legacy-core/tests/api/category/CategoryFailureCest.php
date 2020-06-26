<?php
namespace category;

use \Step\Api\UserAuth;

use App\Models\Category;
use App\Models\Media;

class CategoryFailureCest
{
    public function _before()
    {
        $this->category = factory(Category::class, 1)->create()->toArray();
        $this->media = Media::first();
        $this->media->categories()->attach($this->category['id']);
    }

    public function _after()
    {
    }

    public function tryCreate(UserAuth $I)
    {
        $category = factory(Category::class, 1)->make()->toArray();
        $category['file'] = $this->media->id;
        $I->sendPOST('/api/v1/categories', $category);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryUpdate(UserAuth $I)
    {
        $category = factory(Category::class, 1)->make()->toArray();
        $category['id'] = $this->category['id'];
        $file = Media::orderBy('id', 'DESC')->first();
        $category['file'] = $file->id;
        $I->sendPATCH('/api/v1/categories/'.$category['id'], $category);
        $I->dontSeeResponseCodeIs(200);
    }

    public function tryDelete(UserAuth $I)
    {
        $I->sendDELETE('/api/v1/categories/'. $this->category['id']);
        $I->dontSeeResponseCodeIs(200);
        $I->seeRecord('categories', $this->category);
    }
}
