<?php
namespace app\index\controller;
use think\Db;

class Example extends base
{
    public function index()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/example/index';
        $banner[1]['title_name'] = '案例';
        $this->assign('banner', $banner);

        $res = db('article')
            ->join("our_article_cat","our_article_cat.cat_id=our_article.cat_id","LEFT")
            ->field("our_article.*,our_article_cat.cat_name")
            ->order('sort_order desc,article_id')
            ->where('our_article.cat_id=2 and is_open=1')
            ->paginate(6);
        $this->assign('lists', $res);
        //渲染模板
        return $this -> fetch();
    }
    public function view()
    {
        $id = input('id');
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/news/index';
        $banner[1]['title_name'] = '案例';
        $sql = "SELECT A.*,B.cat_name FROM our_article A left join our_article_cat B ON A.cat_id=B.cat_id WHERE A.article_id={$id} order by A.sort_order,A.article_id";
        $news = Db::query($sql);
        $banner[2]['title_href'] = '/Index/news/view/id/' .$id;
        $banner[2]['title_name'] = $news[0]['title'];

        $this->assign('banner', $banner);
        $this->assign('news_info', $news[0]);
        //渲染模板
        return $this -> fetch();
    }
}
?>
