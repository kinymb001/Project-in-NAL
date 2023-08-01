<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Post;
use App\Models\Article;
use App\Models\Category;
use App\Models\RevisionArticle;
use App\Models\Upload;

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

    public function PostRecords()
    {
        $posts = Post::latest()->take(10)->get();
        foreach ($posts as $post){
            $post->detail = $post->post_detail()->get();
            $post->upload = Upload::find($posts->upload_id)->pluck('url');
        }

        return $this->handleResponseSuccess($posts, 'get data successfully');
    }

    public function ArticleRecord(){
        $articles = Article::latest()->take(10)->get();
        foreach ($articles as $article){
            $article->detail = $article->articleDetails()->get();
            $article->upload = Upload::find($article->upload_id)->pluck('url');
        }

        return $this->handleResponseSuccess($articles, 'get data successfully');
    }

    public function CategoryRecord(){
        $categories = Category::latest()->take(10)->get();
        foreach ($categories as $category){
            $category->upload = Upload::find($category->upload_id)->pluck('url');
        }
        return $this->handleResponseSuccess($categories, 'get data successfully');
    }

    public function revisionArticleRecord(){
        $revisionArtcles = RevisionArticle::latest()->take(10)->get();
        foreach ($revisionArtcles as $revisionArtcle){
            $revisionArtcle->detail = $revisionArtcle->articleDetails()->get();
            $revisionArtcle->upload = Upload::find($revisionArtcle->upload_id)->pluck();
        }

        return $this->handleResponseSuccess($revisionArtcles, 'get data successfully');
    }
}
