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

class Category extends Model {

    public $is_on = array('1' => '上架', '0' => '下架');

    /**
     *列表
     */
    public function category_list($where = array()) {
        $res = db('category')->where($where)->order('sort_order asc')->select();
        return $res;
    }

    /**
     *单个
     */
    public function category_one($id = 0) {
        $res = db('category')->where('cat_id=' .$id)->find();
        return $res;
    }
    /**
     * 择选栏目
     */
    public function selectMenu() {
        $res = db('category')
            ->field('cat_id,cat_name,parent_id')
            ->order('sort_order asc')
            ->select();
        $tmpArr = catnodeTree($res);

        $data = array();
        foreach ($tmpArr as $k => $v) {
            $name = $v['level'] == 0 ? '<b>' . $v['cat_name'] . '</b>' : '├─' . $v['cat_name'];

            $name = str_repeat("│        ", $v['level']) . $name;
            $data[$v['cat_id']] = $name;
        }
        // dump($data);
        //exit;
        return $data;
    }
}
