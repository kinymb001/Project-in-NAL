<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\User\ArticleController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\PostController;
use App\Http\Controllers\User\RevisionArticleController;
use App\Http\Controllers\User\TopPageController;
use App\Http\Controllers\User\UploadController;
use App\Http\Controllers\User\UserController;
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

// login for auth
Route::post('/auth/store', [AuthController::class, 'store']);  //create for user
Route::post('/auth/login', [AuthController::class, 'login']);

// register for customer
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
    Route::delete('/user/delete/{user}', [UserController::class, 'destroy']);
    Route::delete('/user/un-favorite', [UserController::class, 'unFavorite']);
    Route::post('/user/update/{user}', [UserController::class, 'update']);
    Route::post('user/save-favorite', [UserController::class, 'saveFavorite']);
    Route::post('/user/{article}/approve', [UserController::class, 'approve']);
    Route::post('/user/{revision_article}/approveRevision', [UserController::class, 'approveRevision']);
    Route::get('/users', [UserController::class, 'index']);

    //route for auth
    Route::post('/auth/create', [AuthController::class, 'store']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::get('/auth/logout', [AuthController::class, 'logout']);

    //route for post
    Route::delete('/post/delete/{post}', [PostController::class, 'destroy']);
    Route::post('/post/restore', [PostController::class, 'restore']);
    Route::post('/post/updateDetail/{post}', [PostController::class, 'updateDetail']);
    Route::post('/post/update/{post]', [PostController::class, 'update']);
    Route::post('/post/create', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/post/{post}', [PostController::class, 'show']);

    //route for category
    Route::delete('/category/delete/{category}', [CategoryController::class, 'destroy']);
    Route::post('/category/restore', [CategoryController::class, 'restore']);
    Route::post('/category/update/{category}', [CategoryController::class, 'update']);
    Route::post('/category/create', [CategoryController::class, 'store']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/category/{category}', [CategoryController::class, 'show']);

    //route for upload
    Route::post('/upload/store', [UploadController::class, 'store']);

    //route for article
    Route::delete('article/delete/{article}', [ArticleController::class, 'destroy']);
    Route::post('/article/update/{article}', [ArticleController::class, 'update']);
    Route::post('/article/update-detail/{article}', [ArticleController::class, 'updateDetail']);
    Route::post('/article/create', [ArticleController::class, 'store']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/article/{article}', [ArticleController::class, 'show']);
    Route::get('/article/revision/{article}', [ArticleController::class, 'showRevison']);

    //route for revision article
    Route::delete('/revision/{revision}', [RevisionArticleController::class, 'destroy']);
    Route::post('/revision/create', [RevisionArticleController::class, 'store']);
    Route::get('/revisions', [RevisionArticleController::class, 'index']);
    Route::get('/revision/{revision}', [RevisionArticleController::class, 'show']);

    //route for top page
    Route::post('/top-page/create', [TopPageController::class, 'store']);
    Route::post('top-page/update/{top_page}', [TopPageController::class, 'update']);
    Route::post('/top-page/update-detail/{top_page}', [TopPageController::class, 'updateDetails']);
    Route::get('/top-page/{top_page}', [TopPageController::class, 'show']);

    //route for dashboard
    Route::get('/dashboard/count', [DashboardController::class, 'statistics']);
    Route::get('/dashboard/getRecord', [DashboardController::class, 'Records']);

});

