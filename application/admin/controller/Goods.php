<?php

/**
 *  
 * @file   Menu.php  
 * @date   2016-8-30 11:46:22 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

use think\Loader;
use think\File;

class Goods extends Common {

    public function index() {
        $res = db('goods')->where('is_delete = 0')->order('sort_order asc')->select();
        $lists = $res;
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function info() {
        $id = input('id');
        $cat = Loader::model('Category')->selectMenu();
        //下拉菜单
        $this->assign('selectCat', $cat);
        if ($id) {
            //当前用户信息
            $info = db('goods')->find($id);
            $g_img = explode(",", $info['goods_img']);
            $info['goods_img'] = $g_img;
            $this->assign('info1', $info);
        }else{
            $info['cat_id'] = 0;
            $info['goods_thumb'] = '';
            $info['is_on_sale'] = 1;
            $this->assign('info1', $info);
        }
        $cat = Loader::model('Category')->selectMenu();
        //下拉菜单
        $this->assign('selectCat', $cat);
        return $this->fetch();
    }
    /*
     * 添加
     */

    public function add() {
        $data = input();
        //图片上传
        $allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
        // 获取表单上传文件
        $file = request()->file('img_file_src');
        if(!empty($file)) {
            if(!get_file_suffix($file->getInfo('name'), $allow_suffix))
            {
                $this->error('文件不合法，必须图片文件。');
            }
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH.'public/static/upload/goods');
            $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        }
        elseif (!empty($data['img_src']))
        {
            $src = $data['img_src'];
        }else{
            $src = '';
        }
        $data['goods_thumb'] = $src;

        $goods_img = '';
        for($i=0;$i<5;$i++){
            //图片上传
            $allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
            // 获取表单上传文件
            $file = request()->file('img_file_src_' .$i);
            if(!empty($file)) {
                if(!get_file_suffix($file->getInfo('name'), $allow_suffix))
                {
                    $this->error('文件不合法，必须图片文件。');
                }
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH.'public/static/upload/goods');
                $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
            }
            elseif (!empty($data['img_src_' .$i]))
            {
                $src = $data['img_src_' .$i];
            }else{
                $src = '';
            }
            if($src!=''){
                if($goods_img!=''){
                    $goods_img .= ',' .$src;
                }else{
                    $goods_img = $src;
                }
            }
        }
        $data['goods_img'] = $goods_img;
        $data['add_time'] = time();

        $res = model('goods')->allowField(true)->save($data);
        if ($res) {
            $this->success('操作成功', url('index'));
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 修改
     */

    public function edit() {

        $data = input();
        //图片上传
        $allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
        // 获取表单上传文件
        $file = request()->file('img_file_src');
        if(!empty($file)) {
            if(!get_file_suffix($file->getInfo('name'), $allow_suffix))
            {
                $this->error('文件不合法，必须图片文件。');
            }
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH.'public/static/upload/goods');
            $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        }
        elseif (!empty($data['img_src']))
        {
            $src = $data['img_src'];
        }else{
            $src = '';
        }
        $data['goods_thumb'] = $src;

        $goods_img = '';
        for($i=0;$i<5;$i++){
            //图片上传
            $allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
            // 获取表单上传文件
            $file = request()->file('img_file_src_' .$i);
            if(!empty($file)) {
                if(!get_file_suffix($file->getInfo('name'), $allow_suffix))
                {
                    $this->error('文件不合法，必须图片文件。');
                }
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->move(ROOT_PATH.'public/static/upload/goods');
                $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
            }
            elseif (!empty($data['img_src_' .$i]))
            {
                $src = $data['img_src_' .$i];
            }else{
                $src = '';
            }
            if($src!=''){
                if($goods_img!=''){
                    $goods_img .= ',' .$src;
                }else{
                    $goods_img = $src;
                }
            }
        }
        $data['goods_img'] = $goods_img;
        $data['update_time'] = time();

        $res = model('goods')->allowField(true)->save($data, ['goods_id' => $data['goods_id']]);
        if ($res) {
            $this->success('操作成功', url('index'));
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 删除
     */

    public function del() {
        $id = input('id');
        $data['goods_id'] = $id;
        $data['update_time'] = time();
        $data['is_delete'] = 1;
        $res = model('goods')->allowField(true)->save($data, ['goods_id' => $data['goods_id']]);
        if ($res) {
            $this->success('操作成功', url('index'));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 排序
     */
    public function setListorder() {

        if ($_POST['listorder']) {
            $listorder = $_POST['listorder'];
            foreach ($listorder as $k => $v) {
                $data = array();
                $data['sort_order'] = $v;
                $data['update_time'] = time();
                $res = db('goods')->where(['goods_id' => $k])->update($data);
            }
            if ($res) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        }
    }
}
