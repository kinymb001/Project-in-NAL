<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Mail\ApproveMail;
use App\Mail\RevisionStatusNotification;
use App\Models\Article;
use App\Models\RevisionArticle;
use App\Models\User;
use App\Models\UserMetal;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->roles()->sync([3]);
        event(new Registered($user));

        return $this->handleResponseSuccess($user, 'Register Successfully');
    }

    public function index(Request $request, User $user)
    {
        if (!Auth::user()->hasRole('admin')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $sort = $request->input('sort');
        $sort_types = ['desc', 'asc'];
        $sort_option = ['name', 'created_at', 'updated_at'];
        $sort_by = $request->input('sort_by');
        $sort = in_array($sort, $sort_types) ? $sort : 'desc';
        $sort_by = in_array($sort_by, $sort_option) ? $sort_by : 'created_at';
        $search = $request->input('query');
        $limit = request()->input('limit') ?? 20;

        $query = User::select('*');

        if ($search) {
            $query = $query->where('name', 'LIKE', '%' . $search . '%');
        }
        $users = $query->orderBy($sort_by, $sort)->paginate($limit);

        return $this->handleResponseSuccess($users, 'Get All Users Successfully');
    }

    public function update(Request $request, User $user)
    {
        if (!Auth::user()->hasPermission('edit_user')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $request->validate([
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users,email,',
            'password' => 'sometimes|string|confirmed|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();

        return $this->handleResponseSuccess($user, 'User successfully update');
    }

    public function destroy(User $user)
    {
        if (!Auth::user()->hasPermission('delete_user')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }

        $user->delete();

        return $this->handleResponseSuccess([], 'User successfully deleted');
    }

    public function saveFavorite(Request $request){
        $request->validate([
            'values' => 'required|array',
        ]);

        $data = [];
        $values = $request->values;
        $id = Auth::id();

        foreach ($values as $value){
            $meta = new UserMetal();
            $meta->user_id = $id;
            $meta->key = config('app.user_meta_key');
            $meta->value = $value;
            $meta->save();
            array_push($data, $meta);
        }

        return $this->handleResponseSuccess($data, 'Thank you for liking our posts ');
    }

    public function unFavorite(Request  $request){

        $request->validate([
            'values' => 'required|array',
        ]);

        $values = $request->values;
        $user = Auth::user();
        foreach ($values as $value) {
            $user->user_metas()->where('key', 'like')->where('value', $value)->delete();
        }

        return $this->handleResponseSuccess(null, 'you have unFavorited');
    }

    public function approve(Request $request, Article $article)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:published,reject',
            'comment' => 'nullable|string',
        ]);

        $author_email = $article->user->email;

        $old_status = $article->status;
        $new_status = $validated['status'];

        if ($old_status === $new_status) {
            return $this->handleResponseErros([], 'Invalid status');
        }

        if ($new_status === 'published') {
            $article->status = 'published';
            $article->save();
            Mail::to($author_email)->send(new ApproveMail($article, 'published'));
        } elseif ($new_status === 'reject') {
            $reason = $validated['comment'] ?? '';
            Mail::to($author_email)->send(new ApproveMail($article, 'reject', $reason));

            $article->status = 'pending';
            $article->save();
        } else {
            return $this->handleResponseErros([], 'Invalid status');
        }

        return $this->handleResponseSuccess($article, 'Article status updated successfully');
    }

    public function approveRevision(RevisionArticle $revision_article, Request $request)
    {
        if ($revision_article->status !== 'pending') {
            return response()->json(['message' => 'You can only approve a pending revision.'], 403);
        }

        $article = Article::findOrFail($revision_article->article_id);

        $article->update([
            'name' => $revision_article->name,
            'slug' => $revision_article->slug,
            'content' => $revision_article->content,
            'description' => $revision_article->description,
            'seo_title' => $revision_article->seo_title,
            'seo_description' => $revision_article->seo_description,
            'status' => 'published',
            'upload_id' => $revision_article->upload_id,
        ]);

        $languages = config('app.language_array');
        foreach ($languages as $language) {
            $revision_article_detail = $revision_article->revisionDetail()->where('lang', $language)->first();
            $article_detail = $article->articleDetail->where('lang', $language)->first();
            $article_detail->title = $revision_article_detail->title;
            $article_detail->description = $revision_article_detail->description;
            $article_detail->content = $revision_article_detail->content;
            $article_detail->save();
        }

        $revision_article->status = 'published';
        $revision_article->save();

        $reason = '';
        if ($revision_article->status === 'published') {
            $reason = 'Your article has been published';
        } elseif ($revision_article->status === 'Reject') {
            $reason = 'Your article has been rejected';
        }

        if ($revision_article->status === 'approved' || $revision_article->status === 'rejected') {
            Mail::to($revision_article->user->email)->send(new RevisionStatusNotification($revision_article, $revision_article->status, $reason));
        }

        $records = RevisionArticle::where('article_id', $revision_article->article_id)->get();
        foreach ($records as $record){
            $record->delete();
        }

        return response()->json(['message' => 'RevisionArticle has been approved and article updated.'], 200);
    }
}
