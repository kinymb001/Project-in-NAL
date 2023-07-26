<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Post;
use App\Models\PostDetail;
use App\Models\PostMetal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class PostController extends BaseController
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $layout_status = ['public', 'unpublic'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'public';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');

        $query = Post::select('*');

        if($status){
            $query = $query->where('status', $status);
        }
        if($search){
            $query = $query->where('name', 'LIKE', '%'.$search.'%');
        }
        $posts = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleResponseSuccess('Get All Posts successfully', $posts);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create_post')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $rules = [
            'name' => 'required|max:255',
            'status' => 'required|in:public,unpublic',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
            'meta_keys' => 'array',
            'meta_values' => 'array',
        ];
        $msg = [
            'name.required' => 'Name must enter',
            'status.required' => 'Status must enter',
            'status.in' => 'Status has two value are public or unpublic'
        ];
        $request->validate($rules, $msg);

        $post = new Post();
        $post->user_ID = Auth::id();
        $post->name = $request->name;
        $post->slug = Str::of($request->name)->slug('-');
        $post->description = $request->description;
        $post->status = $request->status;
        $post->type = $request->type;

        $post->save();

        $languages = config('app.language_array');

        foreach($languages as $language){
            $post_detail = new PostDetail();
            $name =  translate($language, $request->name);
            $post_detail->name = $name;
            $post_detail->slug = Str::of($name)->slug('-');
            $post_detail->decription = translate($language, $request->description);
            $post_detail->post_id = $post->id;
            $post_detail->language = $language;
            $post_detail->save();
        }

        if($request->has('meta_key') && $request->has('meta_value')){
            $meta_keys = $request->meta_key;
            $meta_values = $request->meta_value;

            foreach ($meta_keys as $i => $meta_key){
                $post_meta = new PostMetal();
                $value = $meta_values[$i];
                $post_meta->post_ID = $post->id;
                $post_meta->meta_key = $meta_key;

                if(is_file($value)){
                    $name = Str::random(10);
                    $path = $value->storeAs('public/post/' . date('Y/m/d'), $name);
                    $post_meta->meta_value = asset(Storage::url($path));
                }else{
                    $post_meta->meta_value = $value;
                }
                $post_meta->save();
            }
        }

        $post->categories()->sync($request->input('categories', []));

        return $this->handleResponseSuccess('Create Post successfully', $post);
    }

    public function show(Post $post)
    {
        $post->catrgoris = $post->categories()->where('status', 'public');
        $post->post_metas = $post->post_metas()->get();
        $post->post_detail = $post->post_detail()->get();
        return $this->handleResponseSuccess('Get Post successfully', $post);
    }

    public function update(Request $request, Post $post)
    {
        if (!Auth::user()->hasPermission('update_post')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $rules = [
            'name' => 'required|max:255',
            'status' => 'required|in:public,unpublic',
            'image' =>  'image|mimes:png,jpg,jpeg,svg|max:10240',
            'category_id' => 'required|array',
            'meta_keys' => 'array',
            'meta_values' => 'array',
        ];
        $msg = [
            'name.required' => 'Name must enter',
            'status.required' => 'Status must enter',
            'status.in' => 'Status has two value are public or unpublic'
        ];
        $request->validate($rules, $msg);


        $post->name = $request->name;
        $post->slug = Str::of($request->name)->slug('-');
        $post->description = $request->description;
        $post->status = $request->status;
        $post->type = $request->type;
        $post->save();
        $post->categories()->sync($request->input('categories', []));
        $post->post_detail()->delete();

        $translator = new GoogleTranslate();
        $languages = config('app.language_array');
        foreach($languages as $language){
            $post_detail = new PostDetail();
            $post_detail->name = $translator->setSource('en')->setTarget($language)->translate($request->name);
            $post_detail->description = $translator->setSource('en')->setTarget($language)->translate($request->description);
            $post_detail->post_id = $post->id;
            $post_detail->language = $language;
            $post_detail->save();
        }

        if($request->has('meta_key') && $request->has('meta_value')){
            $post_metas = $post->post_metas()->get();
            foreach ($post_metas as $post_meta){
                $value = $post_meta->meta_value;
                if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
                    $path = 'public' . Str::after($post_meta->value, 'storage');
                    Storage::delete($path);
                }
                $post_meta->delete();
            }
            $meta_keys = $request->meta_key;
            $meta_values = $request->meta_value;

            foreach ($meta_keys as $i => $meta_key){
                $post_meta = new PostMetal();
                $value = $meta_values[$i];
                $post_meta->post_ID = $post->id;
                $post_meta->meta_key = $meta_key;

                if(is_file($value)){
                    $name = Str::random(10);
                    $path = $value->storeAs('public/post/' . date('Y/m/d'), $name);
                    $post_meta->meta_value = asset(Storage::url($path));
                }else{
                    $post_meta->meta_value = $value;
                }
                $post_meta->save();
            }
        }
        return $this->handleResponseSuccess('Update Post successfully', $post);
    }

    public function updateDetail(Request $request, Post $post){
        $request->validate([
            'language'=> 'required|string|max: 10',
            'name' => 'required|string|max: 255',
            'description' => 'string',
        ]);

        $language = $request->language;

        $post_detail = $post->post_detail()->where('lang', $language)->first();
        $post_detail->name = $request->name;
        $post_detail->description = $request->description;
        $post_detail->save();

        return $this->handleResponseSuccess($post_detail, 'Post detail updated successfully');
    }

    public function restore(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'id' => 'required',
        ]);

        $id = $request->input('id');

        $id = is_array($id) ? $id : [$id];
        Post::onlyTrashed()->whereIn('id', $id)->restore();

        return $this->handleResponse([], 'Post restored successfully!');
    }

    public function destroy(Post $post, Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'ids' => 'required',
            'option' => 'in:softDelete,hardDelete',
        ]);

        $option = $request->option ?? config('app.option_delete');
        $ID_delete = $request->input('ids');

        if ($option === 'softDelete') {
            $post->whereIn('id', $ID_delete)->delete();
            return $this->handleResponseSuccess('Post softDelete successfully!', []);
        }
        if ($option === 'hardDelete') {
            $post->withTrashed()->whereIn('id', $ID_delete)->forceDelete();
            $post_metas = PostMetal::whereIn('post_id', $ID_delete)->get();

            foreach ($post_metas as $post_meta) {
                $path = parse_url($post_meta->url, PHP_URL_PATH);
                $old_path = str_replace('/storage', '/public', $path);
                Storage::delete($old_path);
            }
            return $this->handleResponseSuccess('Post hardDelete successfully!', []);
        }
    }
}
