<?php

namespace App\Repositories;

use App\Article;

class ArticleRepositories
{
    protected $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function getAll($page, $num)
    {
        return $this->article
            ->leftJoin('category', 'article.category', '=', 'category.category_id')
            ->skip(($page-1) * $num)
            ->take($num)
            ->orderBy('article.updated_at', 'desc')
            ->get();
    }

    public function getWhere($value = '*', $page, $num)
    {
        return $this->article
            ->select($value)
            ->skip(($page-1) * $num)
            ->take($num)
            ->orderBy('article.updated_at', 'desc')
            ->get();
    }

    public function findMulti($option, $value, $page, $num)
    {
        return $this->article
            ->join('category', 'article.category', '=', 'category.category_id')
            ->skip(($page-1)*$num)
            ->take($num)
            ->where($option, $value)
            ->orderBy('article.article_id', 'desc')
            ->get();
    }


    public function findOne($option, $value, $data = '*')
    {
        return $this->article
            ->select($data)
            ->where($option, $value)
            ->first();
    }

    public function findOneAndCategoryUser($option, $value)
    {
        return $this->article
            ->join('category', 'category.category_id', '=', 'article.category')
            ->join('profile', 'profile.user_id', '=', 'article.user_id')
            ->join('users', 'users.id', '=', 'article.user_id')
            ->select('article.*', 'category.*', 'profile.*', 'users.email')
            ->where($option, $value)
            ->first();
    }

    public function store($value)
    {
        return $this->article
            ->create($value);
    }

    public function adminCount()
    {
        return $this->article->count();
    }

    public function userCount($user_id)
    {
        return $this->article
            ->where('user_id', $user_id)
            ->count();
    }

    public function update($value, $article_id)
    {
        return $this->article
            ->where('article_id', $article_id)
            ->update($value);
    }

    public function destroy($option, $value)
    {
        return $this->article
            ->where($option, $value)
            ->delete();
    }

    public function getArticleLimitDesc($num)
    {
        return $this->article
            ->join('profile', 'article.user_id', 'profile.user_id')
            ->where('attribute', '<>', 2)
            ->limit($num)
            ->orderby('article.updated_at', 'desc')
            ->get();
    }

    public function getArticleTopDesc($num)
    {
        return $this->article
            ->where('attribute', 3)
            ->limit($num)
            ->orderby('updated_at', 'desc')
            ->get();
    }

    public function getArticleLimitDescCategory($category_id, $num)
    {
        return $this->article
            ->join('profile', 'article.user_id', 'profile.user_id')
            ->where('attribute', '<>', 2)
            ->where('category', $category_id)
            ->limit($num)
            ->orderby('article.updated_at', 'desc')
            ->get();
    }

    public function getArticleTopDescCategory($category_id, $num)
    {
        return $this->article
            ->where('attribute', 3)
            ->where('category', $category_id)
            ->limit($num)
            ->orderby('updated_at', 'desc')
            ->get();
    }

    public function moreArticle($category_id, $page)
    {
        if ($category_id == 0) {
            return $this->article
                ->join('profile', 'article.user_id', 'profile.user_id')
                ->where('attribute', '<>', 2)
                ->skip(config('site.index_page') + ($page-1)*config('site.more_article'))
                ->limit(config('site.more_article'))
                ->orderby('article.updated_at', 'desc')
                ->get();
        }

        return $this->article
            ->join('profile', 'article.user_id', 'profile.user_id')
            ->where('attribute', '<>', 2)
            ->where('category', $category_id)
            ->skip(config('site.index_page') + ($page-1)*config('site.more_article'))
            ->limit(config('site.more_article'))
            ->orderby('article.updated_at', 'desc')
            ->get();

    }

    public function article_rand($category_id, $num)
    {
        return $this->article
            ->where('attribute', '<>', 2)
            ->where('category', $category_id)
            ->limit($num)
            ->inRandomOrder()
            ->get();
    }

}