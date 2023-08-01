<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\TopPageController;
use App\Http\Controllers\Admin\RevisionArticleController;
use App\Http\Controllers\Admin\UploadController;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
// login for admin
Route::post('/admin/login', [AuthController::class, 'login']);

//register, login for user
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/register', [UserController::class, 'register']);

//send email
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return response()->json(['code' => 200, 'message' => "Verified successfully"], 200);
})->middleware(['auth', 'signed'])->name('verification.verify');

//resend email
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1']);

//group route functions
Route::group(['middleware' => ['jwt.verify']], function() {
    //router for user
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::post('/user/refresh', [UserController::class, 'refresh']);
    Route::post('/user/update/{user}', [UserController::class, 'update'])->can('update', User::class);

    //route for admin
    Route::post('/admin/create', [AuthController::class, 'store']);
    Route::get('/admin/logout', [AuthController::class, 'logout']);
    Route::post('/admin/refresh', [AuthController::class, 'refresh']);
    Route::get('/admin/users', [AuthController::class, 'index'])->can('viewAny', User::class);
    Route::post('/admin/update/{user}', [AuthController::class, 'update'])->can('update', User::class);
    Route::delete('/admin/delete/{user}', [AuthController::class, 'destroy'])->can('delete', User::class);
    Route::post('/admin/approve/{article}', [AuthController::class, 'approve']);
    Route::post('/admin/approve-revision/{revisionArticle}', [AuthController::class, 'approveRevision']);

    //route for post
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/post/{post}', [PostController::class, 'show']);
    Route::post('/post/create', [PostController::class, 'store'])->can('create', Post::class);
    Route::post('/post/update/{post}', [PostController::class, 'update'])->can('update', Post::class);
    Route::delete('/post/delete/{post}', [PostController::class, 'destroy'])->can('delete', Post::class);
    Route::post('/post/update_detail/{post}', [PostController::class, 'updateDetail']);

    //route for user like post
    Route::post('/like/create', [AuthController::class, 'saveFavorite']);
    Route::post('/like/unlike', [AuthController::class, 'unFavorite']);
    Route::get('/like/show', [AuthController::class, 'indexFavorite']);


    //route for category
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{category}', [CategoryController::class, 'show']);
    Route::post('/category/create', [CategoryController::class, 'store'])->can('create', Category::class);
    Route::post('/category/update/{category}',  [CategoryController::class, 'update'])->can('update', Category::class);
    Route::delete('/category/delete/{category}',  [CategoryController::class, 'destroy'])->can('delete', Category::class);

    //route for upload
    Route::post('/upload/store', [UploadController::class, 'store']);

    //route for article
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/article/create', [ArticleController::class, 'store']);
    Route::get('/article/{article}', [ArticleController::class, 'show']);
    Route::get('/article/revision/{article}', [ArticleController::class, 'showRevison']);
    Route::post('/article/update/{article}', [ArticleController::class, 'update']);
    Route::post('/article/update-detail/{article}', [ArticleController::class, 'updateDetail']);
    Route::delete('article/delete/{article}', [ArticleController::class, 'destroy']);


    //route for revision article
    Route::get('/revisions', [RevisionArticleController::class, 'index']);
    Route::post('/revision/create', [RevisionArticleController::class, 'store']);
    Route::get('/revision/{revision}', [RevisionArticleController::class, 'show']);
    Route::delete('/revision/{revision}', [RevisionArticleController::class, 'destroy']);


    //route for top page
    Route::get('/top-pages', [TopPageController::class, 'index']);
    Route::post('/top-page/create', [TopPageController::class, 'store']);
    Route::get('/top-page/{top_page}', [TopPageController::class, 'show']);
    Route::post('top-page/update/{top_page}', [TopPageController::class, 'update']);
    Route::post('/top-page/update-detail/{top_page}', [TopPageController::class, 'updateDetails']);

});

