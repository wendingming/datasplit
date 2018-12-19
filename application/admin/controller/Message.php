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

class Message extends Common {

    public function index() {
        $res = db('message')
            ->order('add_time desc')->paginate(15);

        $this->assign('lists', $res);
        return $this->fetch();
    }
    /*
     * 查看
     */
    public function info() {
        $id = input('id');
        if ($id) {
            //当前消息
            $info = db('message')->find($id);
            $this->assign('info1', $info);
        }else{
            $this->error('没有ID参数');
        }
        return $this->fetch();
    }

    /*
     * 删除
     */

    public function del() {
        $id = input('id');
        $res = db('message')->where(['msg_id' => $id])->delete();
        if ($res) {
            $this->success('删除成功', url('index'));
        } else {
            $this->error('删除失败');
        }
    }
}
