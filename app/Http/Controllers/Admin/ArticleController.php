<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Article;
use App\Models\ArticleDetail;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ArticleController extends BaseController
{

    const SEO_KEYS = ['Article', 'Development', 'SEO'];

    public function index(Request $request)
    {
        $status = $request->input('status');
        $layout_status = ['pending', 'published', 'reject'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'published';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');

        $query = Article::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $articles = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleResponseSuccess($articles, 'Get All Articles');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasPermission('create_category')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'name' => 'required|max:255',
            'categories' => 'required|array',
            'content' => 'required',
            'upload_ids' => 'array'
        ]);

        $keywords = self::SEO_KEYS;
        $get_title = implode(" - ", $keywords) . " - " . $request->title;
        $get_des = Str::limit($request->description, 150);

        $article = new Article();
        $article->user_id = Auth::id();
        $article->name = $request->name;
        $article->slug = Str::of($request->name)->slug('-');
        $article->description = $request->description;
        $article->seo_title = $get_title;
        $article->seo_description = $get_des;
        $article->status = 'pending';
        $article->type = $request->type;
        if ($request->upload_ids){
            $article->upload_id = $request->upload_id;
            deleteImage($request->upload_ids);
        }
        $article->save();
        $article->categories()->sync($request->input('categories', []));

        $languages = config('app.language_array');
        foreach($languages as $language){
            $article_detail = new ArticleDetail();
            $name =  translate($language, $request->name);
            $article_detail->name = $name;
            $article_detail->slug = Str::of($name)->slug('-');
            $article_detail->description = translate($language, $request->description);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }

        return $this->handleResponseSuccess($article, 'Create Article successfully!');
    }

    public function show(Article $article)
    {
        $article->categoris = $article->categories()->where('status', 'public');
        $article->article_detail = $article->articleDetails()->get();
        $article->uploads = Upload::find($article->upload_id);
        return $this->handleResponseSuccess('Get article successfully', $article);
    }

    public function update(Request $request, Article $article)
    {
        if (!Auth::user()->hasPermission('update_post')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        if($article->status === 'published'){
            return $this->handleResponseErros([], 'Article is public');
        }

        $request->validate([
            'name' => 'required|max:255',
            'status' => 'required|in:pending,published,reject',
            'categories' => 'required|array',
            'content' => 'required',
            'upload_ids' => 'array'
        ]);

        $keywords = self::SEO_KEYS;
        $get_title = implode(" - ", $keywords) . " - " . $request->title;
        $get_des = Str::limit($request->description, 150);

        $article->name = $request->name;
        $article->slug = Str::of($request->name)->slug('-');
        $article->description = $request->description;
        $article->seo_title = $get_title;
        $article->seo_description = $get_des;
        $article->status = 'pending';
        $article->content = $request->contents;
        if ($request->upload_ids){
            $article->upload_id = $request->upload_ids;
            deleteImage($request->upload_ids);
        }
        $article->save();
        $article->categories()->sync($request->input('categories', []));
        $article->articleDetails()->delete();
        $languages = config('app.language_array');
        foreach($languages as $language){
            $article_detail = new ArticleDetail();
            $name =  translate($language, $request->name);
            $article_detail->name = $name;
            $article_detail->slug = Str::of($name)->slug('-');
            $article_detail->description = translate($language, $request->description);
            $article_detail->content = translate($language, $request->contents);
            $article_detail->article_id = $article->id;
            $article_detail->language = $language;
            $article_detail->save();
        }

        return $this->handleResponseSuccess('Update Post successfully', $article);
    }

    public function updateDetail(Request $request, Article $article){
        $request->validate([
            'language'=> 'required|string|max: 10',
            'name' => 'required|string|max: 255',
            'description' => 'string',
            'content' => 'required',
        ]);

        $language = $request->language;

        $article_detail = $article->articleDetails()->where('lang', $language)->first();
        $article_detail->name = $request->name;
        $article_detail->description = $request->description;
        $article_detail->content = $request->contents;
        $article_detail->save();

        return $this->handleResponseSuccess($article_detail, 'Article detail updated successfully');
    }

    public function showRevison(Article $article){

        if ($article->revisions()->get()->count() === 0 ) {
            return response()->json(['message' => 'No revisions found for this article.'], 404);
        }

        $revision = $article->revisions()->get();

        return $this->handleResponseSuccess($revision, 'Get revision for article successfully');
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
        Article::onlyTrashed()->whereIn('id', $id)->restore();

        return $this->handleResponse([], 'Article restored successfully!');
    }

    public function destroy(Article $article, Request $request)
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
            $article->whereIn('id', $ID_delete)->delete();
            return $this->handleResponseSuccess('Post softDelete successfully!', []);
        }
        if ($option === 'hardDelete') {
            $article->withTrashed()->whereIn('id', $ID_delete)->forceDelete();

            return $this->handleResponseSuccess('Post hardDelete successfully!', []);
        }
        }
}
