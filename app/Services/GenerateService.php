<?php

namespace App\Services;

use App\Http\Controllers\Front\FrontController;
use App\Repositories\ArticleRepository;
use App\Repositories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;

class GenerateService
{
    protected $article;
    protected $front;
    protected $category;
    protected $request;
    protected $index;

    public function __construct(ArticleRepository $article,
                                FrontController $front,
                                CategoryRepository $category,
                                Request $request,
                                IndexService $index)
    {
        $this->article = $article;
        $this->front = $front;
        $this->category = $category;
        $this->request = $request;
        $this->index = $index;
    }

    /**
     * 生成首页
     *
     * @return bool
     * @throws \Exception
     */
    public function index()
    {
        //获取页面数据
        $data = $this->front->index()->__toString();

        //首页静态路径
        $path = public_path().'/index.html';

        //写入文件
        if ($this->fwrite($path, $data)) {
            return true;
        }

        throw new \Exception('生成失败！', 500);
    }

    /**
     * 生成列表
     *
     * @return bool
     */
    public function category()
    {
        //目录
        $path = public_path().'/category/';

        //删除目录下文章（目录存在）
        if (file_exists($path)) {
            $this->deleteAll($path);
        }

        //生成路径（目录不存在）
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        //获取所有文章栏目id
        $all_caregory_id = $this->category->getWhereParent('article', ['category_id']);

        //循环生成
        foreach ($all_caregory_id as $category_id) {
            $id = $category_id->category_id;
            $data = $this->front->category($id)->__toString();
            $filename = $id.'.html';
            $this->fwrite($path.$filename, $data);
        }

        return true;
    }

    /**
     * 生成文章
     *
     * @return bool
     */
    public function article()
    {
        if (empty($this->request->get('category'))) {
            $all_article = $this->article->getArticleGennerate();
            //删除目录下文章（目录存在）
            $category_path = public_path().'/article/';
            if (file_exists($category_path)) {
                $this->deleteAll($category_path);
            }
        } else {
            $all_article = $this->article->getArticleGennerateWhere($this->request->get('category'));
            //删除目录下文章（目录存在）
            $alias = $this->category->current($this->request->get('category'))['alias'];
            $category_path = public_path().'/article/'.$alias;
            if (file_exists($category_path)) {
                $this->deleteAll($category_path);
            }
        }

        //循环生成页面
        foreach ($all_article as $article) {
            $id = $article->article_id;
            $data = $this->front->article($id)->__toString();
            $filename = $id.'.html';

            //目录
            $alias = $this->category->current($article->category)['alias'];
            $path = public_path().'/article'.$this->links($id, $alias);

            //生成路径（目录不存在）
            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }

            //更新链接
//            $this->article->update(['links' => $this->links($id, $alias).$filename], $id);

            //写入文件
            $this->fwrite($path.$filename, $data);
        }

        //生成检索目录
        $this->retrieval();

        //返回结果
        return true;
    }


    /**
     * 生成单篇文章
     *
     * @param $article_id
     * @return bool
     */
    public function article_one($article_id)
    {
        //获取文章分类
        $category = $this->article->findOne('article_id', $article_id, 'category')['category'];

        //获取静态页面数据
        $data = $this->front->article($article_id)->__toString();

        //文件名
        $filename = $article_id.'.html';

        //生成目录
        $alias = $this->category->current($category)['alias'];
        $path = public_path().'/article'.$this->links($article_id, $alias);

        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        //写入文件
        return $this->fwrite($path.$filename, $data);
    }

    public function retrieval()
    {
        //获取所有文章
        $all_article = $this->article->retrieval();

        //初始化
        $html = '<meta charset="UTF-8">';

        //构建数据
        foreach ($all_article as $article) {
            $title = $article['title'];
            $links = config('site.article_path').$article['links'];
            $html .= "<a href='/".$links."' target='_blank'>$title</a><br>";
        }

        //生成目录
        $path = public_path().'/article/';
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }

        //写入文件
        return $this->fwrite($path.'/retrieval.html', $html);
    }

    /**
     * 生成slidebar缓存
     *
     * @return bool
     */
    public function slidebar()
    {
        //获取原始数据
        $result = [];
        $data = config('slidebar.original');

        //判断是否取到数据
        if (empty($data)) {
            return false;
        }

        //切割每一项数据
        foreach ($data as $key => $value) {
            $result['generate'][$key] = $this->index->mb_str_split($value);
        }

        //存储到redis
        return Redis::set('slidebar_generate', json_encode($result));
    }

    /**
     * 写入文件
     *
     * @param $file
     * @param $data
     * @return bool
     */
    public function fwrite($file, $data)
    {
        try {
            $fopen = fopen($file, 'w');
            fwrite($fopen, $data);
            fclose($fopen);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 根据文章ID生成URL
     *
     * @param $article_id
     * @return string
     */
    public function links($article_id, $alias)
    {
        //初始化
        $path = '/'.$alias.'/';

        //切割字符串
        $path_array = str_split($article_id, 1);

        //循环添加
        foreach ($path_array as $value) {
            $path .= $value . '/';
        }

        return $path;
    }

    /**
     * 删除目录下所有文件
     *
     * @param $path
     */
    public function deleteAll($dirname) {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        $dir = dir($dirname);
        if($dir){
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                //递归
                $this->deleteAll($dirname . DIRECTORY_SEPARATOR . $entry);
            }
        }
        $dir->close();
        return rmdir($dirname);
    }

    /**
     * 压缩html
     *
     * @param $string
     * @return mixed
     */
    public function compressHtml($string) {

        //清除换行符
        $string = str_replace("\r\n", '', $string);

        //清除换行符
        $string = str_replace("\n", '', $string);

        //清除制表符
        $string = str_replace("\t", '', $string);

        //去掉注释标记
        $pattern = array (
            "/> *([^ ]*) *</",
            "/[\s]+/",
            "/<!--[^!]*-->/",
            "/\" /",
            "/ \"/",
            "'/\*[^*]*\*/'"
        );

        $replace = array (
            ">\\1<",
            " ",
            "",
            "\"",
            "\"",
            ""
        );

        return preg_replace($pattern, $replace, $string);
    }
}