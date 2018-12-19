<?php

/**
 *  
 * @file   Menu.php  
 * @date   2016-9-1 15:48:53 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\model;

use think\Model;

class Article extends Model {

    public $is_open = array('1' => '显示', '0' => '不显示');

    /**
     *
     */
    public function article_list($where = array()) {
        $res = db('article')->where($where)->order('sort_order asc')->select();
        return $res;
    }

}
