<?php
namespace app\index\controller;
use think\Db;

class Fuwu extends base
{
    public function index()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/fuwu/index';
        $banner[1]['title_name'] = '小程序开发';
        $this->assign('banner', $banner);
        //渲染模板
        return $this -> fetch();
    }

    public function website()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/fuwu/index';
        $banner[1]['title_name'] = '业务';
        $banner[2]['title_href'] = '/Index/fuwu/website';
        $banner[2]['title_name'] = '网站订制';
        $this->assign('banner', $banner);
        //渲染模板
        return $this -> fetch();
    }

    public function erp()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/fuwu/index';
        $banner[1]['title_name'] = '业务';
        $banner[2]['title_href'] = '/Index/fuwu/erp';
        $banner[2]['title_name'] = '云供应链';
        $this->assign('banner', $banner);
        //渲染模板
        return $this -> fetch();
    }
}
?>
