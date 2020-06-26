<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomPage;
use Breezewish\Marked\Marked;
use Breezewish\Marked\Renderer;
use App\Repositories\Contracts\CustomPageRepositoryContract;
use App\Repositories\Eloquent\Traits\CommonCrudTrait;
use Carbon\Carbon;

class CustomPageRepository implements CustomPageRepositoryContract
{
    use CommonCrudTrait;
    /**
     * Create a new instances of Doc
     *
     * @param array $inputs
     * @return bool|Doc
     */
    public function createUpdate(array $inputs = [])
    {
        $page = CustomPage::firstOrCreate(['slug' => $inputs['slug']]);
        $page->title = $inputs['title'];
        $page->content = $inputs['content'];
        if ($page->save()) {
            return $page;
        }
        return false;
    }

    public function createRevisedUpdate(array $inputs = [])
    {
        $page = CustomPage::firstOrCreate(['slug' => $inputs['slug']]);
        $page->title = $inputs['title'];
        $page->content = $inputs['content'];
        $page->revised_at = Carbon::now()->toDateTimeString();
        if ($page->save()) {
            return $page;
        }
        return false;
    }

    public function show($slug)
    {
        $page = CustomPage::where('slug', $slug)->latest()->first();

        return $page;
    }

    public function index()
    {
        return CustomPage::all();
    }

    // function to render a custom page to a usable object
    // so that we can display it in modals/custom pages/etc
    public function renderPage($pageName)
    {
        $page = $this->show($pageName);
        if (!$page) {
            abort(404);
        }
        Marked::setOptions(array(
            'gfm'          => true,
            'tables'       => true,
            'breaks'       => false,
            'pedantic'     => false,
            'sanitize'     => true,
            'smartLists'   => false,
            'smartypants'  => false,
            'langPrefix'   => 'lang-',
            'xhtml'        => false,
            'headerPrefix' => '',
            'highlight'    => null,
            'renderer'     => new Renderer()
        ));
        $page->content = Marked::render($page->content);
        $page->content = html_entity_decode($page->content);
        return $page;
    }
}
