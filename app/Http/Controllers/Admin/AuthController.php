<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Mail\ApproveMail;
use App\Models\Article;
use App\Models\User;
use App\Models\UserMetal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends BaseController
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'store']]);
    }

    public function store(Request $request, User $user){

        if (!Auth::user()->hasPermission('create_user')) {
            return $this->handleResponseErros(null, 'Unauthorized')->setStatusCode(403);
        }
        $request->validate([
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $user->roles()->sync([2]);

        return $this->handleResponseSuccess($user, 'Create Editor Successfully!');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $cerrent = $request->only('email', 'password');

        if (!$token = auth()->attempt($cerrent)) {
            return $this->handleResponseSuccess([], 'Unauthorized')->setStatusCode(401);
        }

        return $this->createNewToken($token);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
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

    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user()
        ]);
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

}
