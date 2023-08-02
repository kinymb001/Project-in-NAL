<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Article;
use App\Models\RevisionArticle;
use App\Models\RevisionDetail;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RevisionArticleController extends BaseController
{
    const SEO_KEYS = ['Article', 'Development', 'SEO'];

    public function index(Request $request){
        $language = $request->input('language');
        $languages = config('app.language_array');
        $language = in_array($language, $languages) ? $language : '';
        $status = $request->input('status');
        $layout_status = ['pending', 'approved', 'reject'];
        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $status = in_array($status, $layout_status) ? $status : 'pending';
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? config('app.paginate');

        $query = RevisionArticle::select('*');

        if ($status) {
            $query = $query->where('status', $status);
        }
        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        if ($language){
            $query = $query->whereHas('revision_detail', function ($qr) use ($language) {
                $qr->where('language', $language);
            });
            $query = $query->with(['revision_detail' => function ($qr) use ($language) {
                $qr->where('language', $language);
            }]);
        }
        $revision = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleResponseSuccess($revision, 'Get All Revisions');
    }

    public function store(Request $request, Article $article)
    {

        if(Auth::id() != $article->user_id){
            return $this->handleResponseErros([], 'you isn`t the author' );
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

        $revision = new RevisionArticle();
        $revision->user_id = $article->user_id;
        $revision->name = $request->name;
        $revision->slug = Str::of($request->name)->slug('-');
        $revision->description = $request->description;
        $revision->seo_title = $get_title;
        $revision->seo_description = $get_des;
        $revision->status = 'pending';
        $revision->type = $request->type;
        $revision->article_id = $article->id;

        $latestRevision = RevisionArticle::where('article_id', $article->id)->latest('revision_number')->first();
        $revisionNumber = $latestRevision ? $latestRevision->revision_number + 1 : 1;
        $revision->revision_number = $revisionNumber;

        if ($request->upload_ids){
            $revision->upload_id = $request->upload_ids;
            deleteImage($request->upload_ids);
        }
        $revision->save();
        $revision->categories()->sync($request->input('categories', []));

        $languages = config('app.language_array');
        foreach($languages as $language){
            $revision_detail = new RevisionDetail();
            $name =  translate($language, $request->name);
            $revision_detail->name = $name;
            $revision_detail->slug = Str::of($name)->slug('-');
            $revision_detail->description = translate($language, $request->description);
            $revision_detail->seo_title = translate($language, $get_title);
            $revision_detail->seo_description = translate($language, $get_des);
            $revision_detail->article_id = $article->id;
            $revision_detail->revision_id = $revision->id;
            $revision_detail->language = $language;
            $revision_detail->save();
        }

        return $this->handleResponseSuccess($revision, 'Create Article successfully!');
    }

    public function show(RevisionArticle $revision)
    {
        $revision->categoris = $revision->categories()->where('status', 'public');
        $revision->revision_detail = $revision->revisionDetail()->get();
        $revision->uploads = Upload::find($revision->upload_id);
        return $this->handleResponseSuccess('Get article successfully', $revision);
    }

    public function update(Request $request, RevisionArticle $revision, Article $article){
        if(Auth::id() != $article->user_id){
            return $this->handleResponseErros([], 'you isn`t the author' );
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

        $revision->user_id = $article->user_id;
        $revision->name = $request->name;
        $revision->slug = Str::of($request->name)->slug('-');
        $revision->description = $request->description;
        $revision->seo_title = $get_title;
        $revision->seo_description = $get_des;
        $revision->status = 'pending';
        $revision->type = $request->type;
        $revision->article_id = $article->id;

        if ($request->upload_ids){
            $revision->upload_id = $request->upload_ids;
            deleteImage($request->upload_ids);
        }
        $revision->save();
        $revision->categories()->sync($request->input('categories', []));

        $languages = config('app.language_array');
        foreach($languages as $language){
            $revision_detail = new RevisionDetail();
            $name =  translate($language, $request->name);
            $revision_detail->name = $name;
            $revision_detail->slug = Str::of($name)->slug('-');
            $revision_detail->description = translate($language, $request->description);
            $revision_detail->seo_title = translate($language, $get_title);
            $revision_detail->seo_description = translate($language, $get_des);
            $revision_detail->article_id = $article->id;
            $revision_detail->revision_id = $revision->id;
            $revision_detail->language = $language;
            $revision_detail->save();
        }

        return $this->handleResponseSuccess($revision, 'Create Article successfully!');
    }

    public function destroy(RevisionArticle $revision, Request $request){
        $ID_delete = $request->input('ids');

            $revision->whereIn('id', $ID_delete)->delete();
            return $this->handleResponseSuccess('RevisionArticle softDelete successfully!', []);
    }

}
