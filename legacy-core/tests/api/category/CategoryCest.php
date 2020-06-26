<?php
namespace category;

use \Step\Api\UserAuth;

use App\Models\Category;
use App\Models\Media;

class CategoryCest
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

    public function tryIndex(UserAuth $I)
    {
        $I->sendGET('/api/v1/categories');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->category);
    }

    public function tryGetHierarchy(UserAuth $I)
    {
        $I->wantTo('Get the hierarchy');
        $I->sendGET('/api/v1/categories/hierarchy');
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->category);
    }

    public function tryCreate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $category = factory(Category::class, 1)->make()->toArray();
        $category['file'] = $this->media->id;
        $I->sendPOST('/api/v1/categories', $category);
        $I->seeResponseCodeIs(200);
        unset($category['file']);
        $I->seeResponseContainsJson($category);
        $I->seeResponseContainsJson(['media' => $this->media->toArray()]);
        $I->seeRecord('categories', $category);
    }

    public function tryShow(UserAuth $I)
    {
        $I->sendGET('/api/v1/categories/'.$this->category['id']);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($this->category);
        $I->seeResponseContainsJson(['media' => $this->media->toArray()]);
    }

    public function tryUpdate(UserAuth $I)
    {
        $I->loginAsAdmin();
        $category = factory(Category::class, 1)->make()->toArray();
        $category['id'] = $this->category['id'];
        $category['placement'] = $this->category['placement'];
        $file = Media::orderBy('id', 'DESC')->first();
        $category['file'] = $file->id;
        $I->sendPATCH('/api/v1/categories/'.$category['id'], $category);
        $I->seeResponseCodeIs(200);
        unset($category['file']);
        $I->seeResponseContainsJson($category);
        $I->seeResponseContainsJson(['media' => $file->toArray()]);
        $I->dontSeeResponseContainsJson(['media' => $this->media->toArray()]);
        $I->seeRecord('categories', $category);
    }

    public function tryPlacement(UserAuth $I)
    {
        $I->loginAsAdmin();
        $place = ['placement' => 3];
        $I->sendPatch('/api/v1/categories/placement/'.$this->category['id'], $place);
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson($place);

        // Makes sure that the placement of categories is in order.
        $placement = array_column(json_decode($I->grabResponse()), 'placement');
        $categoryCount = Category::where('parent_id', null)->count();

        $count = 0;
        $order = [];
        while ($count < $categoryCount) {
            $order[] = $count;
            $count++;
        }

        $isInOrder = false;
        if ($placement === $order) {
            $isInOrder = true;
        }
        $I->assertTrue($isInOrder);
    }

    public function tryDelete(UserAuth $I)
    {
        $I->loginAsAdmin();
        $I->sendDELETE('/api/v1/categories/'. $this->category['id']);
        $I->seeResponseCodeIs(200);
        $I->dontSeeRecord('categories', $this->category);
    }
}
