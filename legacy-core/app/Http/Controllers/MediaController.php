<?php namespace App\Http\Controllers;
 use Auth;
use Cache;
use Config;
use Input;
use Redirect;
use Validator;
use App\Models\Media;
use App\Models\Tag;
use App\Models\User;
use App\Services\Upload\UploadAmazonS3;
use App\Repositories\Eloquent\MediaRepository;
use App\Http\Controllers\Controller;
 class MediaController extends Controller
{
    private $settingsService;
     public function __construct(
        MediaRepository $media
    ) {
        $this->media = $media;
        $this->settingsService = app('globalSettings');
    }
     /**
     * Display a listing of media
     *
     * @return Response
     */
    public function index()
    {
        if (auth()->user()->hasRole(['Admin', 'Superadmin'])) {
            return view('media.index', compact('filter'));
        } elseif (auth()->user()->hasRole(['Rep'])) {
            if (auth()->user()->hasSellerType(['Reseller']) and
                $this->settingsService->getGlobal('reseller_media_library', 'show') or
                auth()->user()->hasSellerType(['Affiliate']) and
                $this->settingsService->getGlobal('affiliate_media_library', 'show')) {
                return view('media.index', compact('filter'));
            }
        }
        return redirect('/dashboard');
    }
     /**
     * Display a listing of media belonging to a user
     *
     * @return Response
     */
    public function user($id)
    {
        $user = User::findOrFail($id);
        if (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
            $filter = str_replace('-', ' ', $filter);
        }
        return view('media.index', compact('user', 'filter'));
    }
     /**
     * Display a listing of media belonging to all reps
     *
     * @return Response
     */
    public function reps()
    {
        $reps = true;
        return view('media.index', compact('reps'));
    }
     /**
     * Display a listing of media shared with reps
     *
     * @return Response
     */
    public function sharedWithReps()
    {
        $shared_with_reps = true;
        if (isset($_GET['filter'])) {
            $filter = $_GET['filter'];
            $filter = str_replace('-', ' ', $filter);
        }
        return view('media.index', compact('shared_with_reps', 'filter'));
    }
     /**
     * Show the form for creating a new media
     *
     * @return Response
     */
    public function create()
    {
        $tags = Tag::where('taggable_type', 'Media')->select('name')->groupBy('name')->get();
        return view('media.create', compact('tags'));
    }
     /**
     * Upload media
     */
     public function store()
    {
        $file = request()->file('file');
        $media = $this->media->process($file);
        return $media;
    }
     /**
     * Display the specified media.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $media = Media::findOrFail($id);
        $tags = $media->tags;
        return view('media.show', compact('media', 'tags'));
    }
     /**
     * Display the specified media via ajax.
     *
     * @param  int  $id
     * @return Response
     */
    public function showAJAX($id)
    {
        $media = Media::findOrFail($id);
        $tags = $media->tags;
        return view('media.show-ajax', compact('media', 'tags'));
    }
     /**
     * Show the form for editing the specified media.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $media = Media::find($id);
         //convert date from unix time if exists
        if ($media->expires_at !== null) {
            $media->expires_at = date('Y-m-d', $media->expires_at);
        }
         $tags = Tag::where('taggable_type', 'Media')->select('name')->groupBy('name')->get();
        return view('media.edit', compact('media', 'tags'));
    }
     /**
     * Update the specified media in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $media = Media::findOrFail($id);
        if ($media->user_id === Auth::id() || Auth::user()->hasRole(['Superadmin', 'Admin'])) {
            // validation
            $rules = [
                'media' => 'sometimes|max:5000',
            ];
            $validator = Validator::make($data = request()->all(), $rules);
             if ($validator->fails()) {
                return Redirect::back()->withErrors($validator)->withInput();
            }
             // format checkboxes for db
            if (isset($data['is_public'])) {
                $data['is_public'] = 1;
            } else {
                $data['is_public'] = 0;
            }
             // if role is Superadmin, Admin, set owner id to 0
            if (Auth::user()->hasRole(['Superadmin', 'Admin'])) {
                $data['user_id'] = Config::get('site.apex_user_id');
            }
             // update db
            $media->update($data);
             // store tags
            if (isset($data['tags'])) {
                foreach ($data['tags'] as $tag) {
                    $new_tag = Tag::create([
                        'name' => $tag['name']
                    ]);
                    $media->tags()->save($new_tag);
                }
            }
             if (Auth::user()->hasRole(['Superadmin', 'Admin'])) {
                $user_id = Config::get('site.apex_user_id');
            } else {
                $user_id = Auth::user()->id;
            }
            return  Redirect::back()->with('message', 'Resource updated.');
        } else {
            return Redirect::back()->with('message', 'You are not authorized to edit this resource.');
        }
    }
     /**
     * Remove the specified media from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $media = Media::find($id);
        if ($media->user_id === Auth::id() || Auth::user()->hasRole(['Superadmin', 'Admin'])) {
            // delete images
            $files = [];
            $files[] = $media;
            deleteImages($files);
             // delete tags
            if (isset($media->tags)) {
                foreach ($media->tags as $tag) {
                    Tag::destroy($tag->id);
                }
            }
             // delete media row
            Media::destroy($id);
             return Redirect::back()->with('message', 'Resource deleted.');
        } else {
            return Redirect::back()->with('message', 'You are not authorized to delete this resource.');
        }
    }
     /**
     * Remove the specified media from storage through AJAX.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroyAJAX($id)
    {
        $media = Media::find($id);
        if ($media->user_id === Auth::id() || Auth::user()->hasRole(['Superadmin', 'Admin'])) {
            // delete images
            $files = [];
            $files[] = $media;
            deleteImages($files);
            // delete tags
            if (isset($media->tags)) {
                foreach ($media->tags as $tag) {
                    Tag::destroy($tag->id);
                }
            }
            // delete media row
            Media::destroy($id);
        } else {
            return Redirect::back()->with('message', 'You are not authorized to delete this resource.');
        }
    }
     /**
     * Remove media.
     */
    public function delete()
    {
        $data = request()->all();
        if (isset($data['ids'])) {
            foreach ($data['ids'] as $id) {
                $media = Media::find($id);
                if ($media->user_id === Auth::id() || Auth::user()->hasRole(['Superadmin', 'Admin'])) {
                        // delete tags
                    if (isset($media->tags)) {
                        foreach ($media->tags as $tag) {
                            Tag::destroy($tag->id);
                        }
                    }
                            // delete Amazon S3 file
                                $awsS3 = new UploadAmazonS3;
                    $awsS3->removeUploadedFile($media);
                            // delete media row
                            Media::destroy($id);
                } else {
                    return Redirect::back()->with('message', 'You are not authorized to delete this resource.');
                }
            }
            if (count(request()->get('ids')) > 1) {
                return Redirect::back()->with('message', 'Resources deleted.');
            } else {
                return Redirect::back()->with('message', 'Resource deleted.');
            }
        } else {
            return Redirect::back()->with('message_danger', 'You must select at least 1 file.');
        }
    }
     /**
     * Disable media.
     */
    public function disable()
    {
        $data = request()->all();
        if (isset($data['ids'])) {
            foreach (request()->get('ids') as $id) {
                Media::find($id)->update(['disabled' => 1]);
            }
            if (count(request()->get('ids')) > 1) {
                return Redirect::back()->with('message', 'Resource disabled.');
            } else {
                return Redirect::back()->with('message', 'Resource disabled.');
            }
        } else {
            return Redirect::back()->with('message_danger', 'You must select at least 1 file.');
        }
    }
     /**
     * Enable media.
     */
    public function enable()
    {
        $data = request()->all();
        if (isset($data['ids'])) {
            foreach (request()->get('ids') as $id) {
                Media::find($id)->update(['disabled' => 0]);
            }
            if (count(request()->get('ids')) > 1) {
                return Redirect::back()->with('message', 'Resource enabled.');
            } else {
                return Redirect::back()->with('message', 'Resource enabled.');
            }
        } else {
            return Redirect::back()->with('message_danger', 'You must select at least 1 file.');
        }
    }
     /**
     * Detach media
     */
    public function detach($model, $model_id, $media_id)
    {
        $model = $model::find($model_id);
        $model->media()->detach($media_id);
    }
}
