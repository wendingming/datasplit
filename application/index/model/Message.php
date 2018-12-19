<?php

/**
 *  
 * @file   Menu.php  
 * @date   2016-9-1 15:48:53 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\index\model;

use think\Model;

class Message extends Model {

    public $is_open = array('1' => '显示', '0' => '不显示');

    /**
     *
     */
    public function message_list($where = array()) {
        $res = db('message')->where($where)->order('msg_id desc')->select();
        return $res;
    }

}
