<?php
namespace app\index\controller;
use think\Db;

class About extends base
{
    public function index()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/about/index';
        $banner[1]['title_name'] = '关于我们';
        $this->assign('banner', $banner);
        //渲染模板
        return $this -> fetch();
    }
}
?>
