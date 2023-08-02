<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Article;
use App\Models\Category;
use App\Models\Post;
use App\Models\RevisionArticle;
use App\Models\Upload;
use App\Models\User;

class DashboardController extends BaseController
{
    public function statistics()
    {
        $postCount = Post::count();
        $articleCount = Article::count();
        $revisionCount = RevisionArticle::count();
        $categoryCount = Category::count();
        $userCount = User::count();

        return response()->json([
            'post_count' => $postCount,
            'article_count' => $articleCount,
            'revision_count' => $revisionCount,
            'category_count' => $categoryCount,
            'user_count' => $userCount,
        ]);
    }

    public function Records()
    {
        $posts = Post::all()->orderByDesc('created_at')->take(10)->get();
        foreach ($posts as $post){
            $post->detail = $post->post_detail()->get();
            $post->upload = Upload::find($posts->upload_id)->pluck('url');
        }

        $articles = Article::all()->orderByDesc('created_at')->take(10)->get();
        foreach ($articles as $article){
            $article->detail = $article->articleDetails()->get();
            $article->upload = Upload::find($article->upload_id)->pluck('url');
        }

        $categories = Category::all()->orderByDesc('created_at')->take(10)->get();
        foreach ($categories as $category){
            $category->upload = Upload::find($category->upload_id)->pluck('url');
        }

        $revisionArtcles = RevisionArticle::all()->orderByDesc('created_at')->take(10)->get();
        foreach ($revisionArtcles as $revisionArtcle){
            $revisionArtcle->detail = $revisionArtcle->articleDetails()->get();
            $revisionArtcle->upload = Upload::find($revisionArtcle->upload_id)->pluck();
        }

        $record = [
            'posts' => $posts,
            'categories' => $categories,
            'articles' => $articles,
            'revisionArtcles' => $revisionArtcles
        ];

        return $this->handleResponseSuccess($record, 'get data successfully');
    }

}
