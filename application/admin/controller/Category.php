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

class Category extends Common {

    public function index() {
        $res = db('category')->order('sort_order asc')->select();

        $lists = catnodeTree($res);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function info() {
        $id = input('id');
        if ($id) {
            //当前用户信息
            $info = db('category')->find($id);
            $this->assign('info1', $info);
        }else{
            $parentid = input('parentid')?input('parentid'):0;
            $info['parent_id'] = $parentid;
            $info['cat_icon'] = '';
            $info['is_on'] = 1;
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
        if (!isset($data['parent_id'])) {
            $data['parent_id'] = 0;
        }else{
            if ($data['parent_id'] == null) {
                $data['parent_id'] = 0;
            }
        }


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
            $info = $file->move(ROOT_PATH.'public/static/upload');
            $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        }
        elseif (!empty($data['img_src']))
        {
            $src = $data['img_src'];
        }else{
            $src = '';
        }
        $data['cat_icon'] = $src;
        $data['add_time'] = time();

        $res = model('category')->allowField(true)->save($data);
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
            $info = $file->move(ROOT_PATH.'public/static/upload');
            $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        }
        elseif (!empty($data['img_src']))
        {
            $src = $data['img_src'];
        }else{
            $src = '';
        }
        $data['cat_icon'] = $src;
        $data['update_time'] = time();
        if ($data['parent_id'] == null) {
            $data['parent_id'] = 0;
        }

        $res = model('category')->allowField(true)->save($data, ['cat_id' => $data['cat_id']]);
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
        $res = db('category')->where(['cat_id' => $id])->delete();
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
                $res = db('category')->where(['cat_id' => $k])->update($data);
            }
            if ($res) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        }
    }
}
