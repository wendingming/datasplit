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

class Datasplit extends Model {

    public $ds_table_database = array('0' => '分表', '1' => '分库');
    public $ds_type_hash = array('0' => '否', '1' => '是');
    public $ds_type_date = array('0' => '否', '1' => '日期【日】', '2' => '日期【周】', '3' => '日期【月】', '4' => '日期【年】');
    public $ds_status = array('0' => '未启用', '1' => '启用');

    /**
     *列表
     */
    public function datasplit_list($where = array()) {
        $res = db('datasplit')->where($where)->order('sort_order asc')->select();
        return $res;
    }

    /**
     *单个
     */
    public function datasplit_one($id = 0) {
        $res = db('datasplit')->where('ds_id=' .$id)->find();
        return $res;
    }
}
