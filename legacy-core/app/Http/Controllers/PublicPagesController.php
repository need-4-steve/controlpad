<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Repositories\Eloquent\CustomPageRepository;
use Breezewish\Marked\Marked;
use Breezewish\Marked\Renderer;
use App\Repositories\Eloquent\StoreSettingRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Services\Store\RepStore;
use App\Services\Store\Store;
use App\Models\User;

class PublicPagesController extends Controller
{
    protected $storeSettingRepo;
    protected $settingsService;
    protected $customPagesRepo;
    protected $userRepo;

    public function __construct(
        CustomPageRepository $customPagesRepo,
        ProductRepository $productRepository,
        StoreSettingRepository $storeSettingRepo,
        UserRepository $userRepo
    ) {
        $this->customPagesRepo = $customPagesRepo;
        $this->productRepository = $productRepository;
        $this->storeSettingRepo = $storeSettingRepo;
        $this->settingsService = app('globalSettings');
        $this->userRepo = $userRepo;
    }
    public function getIndex()
    {
        $landing_page = $this->settingsService->getGlobal('landing_page', 'value');
        if ($landing_page) {
            return redirect('//' . config('app.url') . '/' . $landing_page);
        }

        return view('public-pages.home');
    }
    public function getAbout()
    {
        $store_owner = session()->get('store_owner');
        if (!isset($store_owner)) {
            $store_owner = $this->userRepo->find(config('site.apex_user_id'));
        }

        if ($store_owner->hasRole(['Rep']) && $this->settingsService->getGlobal('replicated_site', 'show')) {
            $store = new RepStore([], $store_owner, null, null, false);
        } elseif ($store_owner->id === config('site.apex_user_id') && $this->settingsService->getGlobal('use_built_in_store', 'show')) {
            $store = new Store([], config('site.apex_user_id'), null, null, false);
        } else {
            return abort(404);
        }
        $settings = $this->storeSettingRepo->getSettingsByUser($store_owner->id);
        $user = $store_owner;
        return view('public-pages.about-me', compact('user', 'settings', 'store_owner', 'store'));
    }
    public function getMyLife()
    {
        return view('public-pages.my-life');
    }

    public function getContact()
    {
        $request = request()->all();
        if ($user = session()->get('store_owner')) {
            $store = new RepStore($request, $user, $this->productRepository);
            return view('public-pages.customer-service', compact('user', 'store'));
        }
        return view('public-pages.contact');
    }

    /*
     * Function to generate a web page version of a stored custom page
     *
     */
    public function getPage()
    {
        $pageName = request()->route()->getName();
        $page = $this->customPagesRepo->renderPage($pageName);
        return view('public-pages.show', compact('page'));
    }

    public function pageEdit($page_name)
    {
        $page = $this->customPagesRepo->show($page_name);
        if (!$page) {
            abort(404);
        }
        return view('public-pages.page-editor', compact('page'));
    }
}
