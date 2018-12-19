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

class News extends Common {

    public function index() {
        $res = db('article')
            ->join("our_article_cat","our_article_cat.cat_id=our_article.cat_id","LEFT")
            ->field("our_article.*,our_article_cat.cat_name")
            ->order('cat_id asc,sort_order desc,article_id desc')->paginate(15);

        $this->assign('lists', $res);
        return $this->fetch();
    }
    /*
     * 查看
     */
    public function info() {
        $id = input('id');
        if ($id) {
            //当前新闻
            $info = db('article')->find($id);
            $this->assign('info1', $info);
        }else{
            $parentid = input('parentid')?input('parentid'):0;
            $info['cat_id'] = $parentid;
            $info['is_open'] = 1;
            $info['file_url'] = '';
            $this->assign('info1', $info);
        }
        $cat = Loader::model('ArticleCat')->selectMenu();
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
            $info = $file->move(ROOT_PATH.'public/static/upload');
            $src = $info->getSaveName();  //在测试的时候也可以直接打印文件名称来查看
        }
        elseif (!empty($data['img_src']))
        {
            $src = $data['img_src'];
        }else{
            $src = '';
        }
        $data1 = array();
        //$data1['article_id'] = $data['article_id'];
        $data1['cat_id'] = $data['cat_id'];
        $data1['title'] = $data['title'];
        $data1['description'] = $data['description'];
        $data1['sort_order'] = $data['sort_order'];
        $data1['is_open'] = $data['is_open'];
        $data1['content'] = $data['content'];
        $data1['add_time'] = time();
        $data1['link'] = $data['link'];
        $data1['file_url'] = $src;
        $res = model('article')->allowField(true)->save($data1);
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
        $data1 = array();
        $data1['article_id'] = $data['article_id'];
        $data1['cat_id'] = $data['cat_id'];
        $data1['title'] = $data['title'];
        $data1['description'] = $data['description'];
        $data1['sort_order'] = $data['sort_order'];
        $data1['is_open'] = $data['is_open'];
        $data1['content'] = $data['content'];
        //$data1['add_time'] = time();
        $data1['update_time'] = time();
        $data1['link'] = $data['link'];
        $data1['file_url'] = $src;

        $res = model('article')->allowField(true)->save($data1, ['id' => $data1['article_id']]);
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
        $res = db('article')->where(['article_id' => $id])->delete();
        if ($res) {
            $this->success('操作成功', url('index'));
        } else {
            $this->error('操作失败');
        }
    }


    public function catlist() {
        $res = db('article_cat')->order('sort_order asc')->select();

        $lists = catnodeTree($res);
        $this->assign('lists', $lists);
        return $this->fetch();
    }

    /*
     * 查看
     */

    public function catinfo() {
        $id = input('id');
        if ($id) {
            //当前用户信息
            $info = db('article_cat')->find($id);
            $this->assign('info1', $info);
        }else{
            $parentid = input('parentid')?input('parentid'):0;
            $info['parent_id'] = $parentid;
            $info['show_in_nav'] = 1;
            $this->assign('info1', $info);
        }
        $cat = Loader::model('ArticleCat')->selectMenu();
        //下拉菜单
        $this->assign('selectCat', $cat);
        return $this->fetch();
    }
    /*
     * 添加
     */

    public function catadd() {
        $data = input();
        if ($data['parent_id'] == null) {
            $data['parent_id'] = 0;
        }
        $res = model('article_cat')->allowField(true)->save($data);
        if ($res) {
            $this->success('操作成功', url('catlist'));
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 修改
     */

    public function catedit() {

        $data = input();
        $data['update_time'] = time();
        if ($data['parent_id'] == null) {
            $data['parent_id'] = 0;
        }

        $res = model('article_cat')->allowField(true)->save($data, ['cat_id' => $data['cat_id']]);
        if ($res) {
            $this->success('操作成功', url('catlist'));
        } else {
            $this->error('操作失败');
        }
    }

    /*
     * 删除
     */

    public function catdel() {
        $id = input('id');
        $res = db('article_cat')->where(['cat_id' => $id])->delete();
        if ($res) {
            $this->success('操作成功', url('catlist'));
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
                $res = db('article_cat')->where(['cat_id' => $k])->update($data);
            }
            if ($res) {
                $this->success('操作成功', url('catlist'));
            } else {
                $this->error('操作失败');
            }
        }
    }
}
