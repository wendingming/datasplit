<?php
namespace app\index\controller;

use think;
use think\Db;
use think\Controller;

class base extends Controller
{
    public $test;   //声明公共变量
    public $nav;   //声明公共变量:导航条
    public $banner;   //声明公共变量:面包屑

    function __construct() {
        parent::__construct();
        //新闻类别
        $sql = 'SELECT * FROM our_article_cat WHERE show_in_nav=1 order by sort_order,cat_id';
        $news_cat = Db::query($sql);
        $this->assign('news_cat', $news_cat);
        //新闻
        $sql = 'SELECT * FROM our_article WHERE is_open=1 order by add_time';
        $news = Db::query($sql);
        $this->assign('news', $news);
    }

    function __get_nav() {
        $nav = array
        (
            array
            (
                "id" => "395440",
                "title_href" => "/",
                "title_name" => "首页",
                "child" => ""
            ),
            array
            (
                "id" => "395443",
                "title_href" => "/Index/about/index",
                "title_name" => "关于我们",
                "child" => ""
            ),
            array
            (
                "id" => "400446",
                "title_href" => "/Index/fuwu/index",
                "title_name" => "业务",
                "child" =>
                    array(
                        array
                        (
                            "id" => "396742",
                            "title_href" => "/Index/fuwu/index",
                            "title_name" => "小程序开发"
                        ),
                        array
                        (
                            "id" => "396824",
                            "title_href" => "/Index/fuwu/website",
                            "title_name" => "网站订制"
                        ),
                        array
                        (
                            "id" => "396825",
                            "title_href" => "/Index/fuwu/erp",
                            "title_name" => "云供应链"
                        )
                    )
            ),
            array
            (
                "id" => "396874",
                "title_href" => "/Index/example/index",
                "title_name" => "案例",
                "child" => ""
            ),
            array
            (
                "id" => "397402",
                "title_href" => "/Index/news/index",
                "title_name" => "新闻",
                "child" => ""
            ),
            array
            (
                "id" => "397252",
                "title_href" => "/Index/customer/index",
                "title_name" => "客户",
                "child" => ""
            ),
            array
            (
                "id" => "397401",
                "title_href" => "/Index/team/index",
                "title_name" => "团队",
                "child" => ""
            ),
            array
            (
                "id" => "395447",
                "title_href" => "/Index/contact/index",
                "title_name" => "联系",
                "child" => ""
            )
        );
        /*
            <ul class="z-nav-conter clearfix">
                <li class="nav395440" data-id="395440"><a href="/"><span>首页</span></a></li>
                <li class="nav395443" data-id="395443"><a href="/Index/about/index"><span>关于我们</span></a></li>
                <li class="nav400446" data-id="400446"><a href="/Index/fuwu/index"><span>业务</span></a>
                    <input type="checkbox" id="inputNavSub400446" class="do-m-menustate do-m-sub" />
                    <label for="inputNavSub400446" class="icon-isSub"></label>
                    <ul class="z-nav-sub">
                        <li class="nav396742" data-id="396742"><a href="/Index/fuwu/index"><span>工商注册</span></a></li>
                        <li class="nav396824" data-id="396824"><a href="/Index/fuwu/website"><span>网站建设</span></a></li>
                        <li class="nav396825" data-id="396825"><a href="/Index/fuwu/erp"><span>会计代理</span></a></li>
                    </ul>
                </li>
                <li class="nav396874" data-id="396874"><a href="/396874.html"><span>案例</span></a>
                    <input type="checkbox" id="inputNavSub396874" class="do-m-menustate do-m-sub" />
                    <label for="inputNavSub396874" class="icon-isSub"></label>
                    <ul class="z-nav-sub"></ul>
                </li>
                <li class="nav397402" data-id="397402"><a href="/397402.html"><span>新闻</span></a></li>
                <li class="nav397252" data-id="397252"><a href="/397252.html"><span>客户</span></a></li>
                <li class="nav397401" data-id="397401"><a href="/397401.html"><span>团队</span></a></li>
                <li class="nav395447" data-id="395447"><a href="/395447.html"><span>联系</span></a></li>
            </ul>
         * */
        return $nav;
    }

    function __get_banner() {
        $banner[0]['title_href'] = '/';
        $banner[0]['title_name'] = '首页';
        return $banner;
    }
}