<?php
namespace app\index\controller;
use think\Db;

class Contact extends base
{
    public function index()
    {
        $nav = $this->__get_nav();
        $this->assign('nav', $nav);
        $banner = $this->__get_banner();
        $banner[1]['title_href'] = '/Index/contact/index';
        $banner[1]['title_name'] = '联系';
        $this->assign('banner', $banner);
        //渲染模板
        return $this -> fetch();
    }
    public function add()
    {
        if(request()->isPost()) {
            $data = input();
            $captcha = new \think\captcha\Captcha();
            if(!$captcha->check($data['msg_code'])){
                $this->error('验证码错误', url('/Index/contact/index'));
            }
            $data1['msg_name'] = $data['msg_name'];
            $data1['msg_tel'] = $data['msg_tel'];
            $data1['msg_content'] = $data['msg_content'];
            $data1['add_time'] = time();
            $data1['msg_ip'] = request()->ip(); ;
            $res = model('message')->allowField(true)->save($data1);
            if ($res) {
                $this->success('留言成功', url('/Index/contact/index'));
            } else {
                $this->error('留言失败');
            }
        }
    }
}
?>
