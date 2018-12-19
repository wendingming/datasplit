<?php
namespace app\index\controller;
use think\Db;

class Index extends base
{
    public function index()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $this->assign('banner', $banner);
        $res = db('article')
            ->join("our_article_cat","our_article_cat.cat_id=our_article.cat_id","LEFT")
            ->field("our_article.*,our_article_cat.cat_name")
            ->order('sort_order desc,article_id')
            ->where('our_article.cat_id=85 and is_open=1')
            ->paginate(4);
        $this->assign('lists', $res);
        $res = db('article')
            ->join("our_article_cat","our_article_cat.cat_id=our_article.cat_id","LEFT")
            ->field("our_article.*,our_article_cat.cat_name")
            ->order('sort_order desc,article_id')
            ->where('our_article.cat_id=86 and is_open=1')
            ->paginate(4);
        $this->assign('listsc', $res);
        //渲染模板
        return $this -> fetch();
    }
}
?>
