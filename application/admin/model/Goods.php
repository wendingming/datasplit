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

class Goods extends Model {
/*
  `is_real` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '真实/虚拟商品：1.真实,0.虚拟',
  `is_on_sale` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否销售：1.上架，0.下架',
  `is_shipping` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商品是否包邮：1.包邮，0.不包邮',
  `is_best` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否精品：1.是，0.否',
  `is_new` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否新品：1.是，0.否',
  `is_hot` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否热销：1.是，0.否',
*/
    public $is_real = array('1' => '真实', '0' => '虚拟');
    public $is_on_sale = array('1' => '上架', '0' => '下架');
    public $is_shipping = array('1' => '包邮', '0' => '不包邮');
    public $is_best = array('1' => '精品', '0' => '非精品');
    public $is_new = array('1' => '新品', '0' => '非新品');
    public $is_hot = array('1' => '热销', '0' => '非热销');

    /**
     *
     */
    public function goods_list($where = array()) {
        $res = db('goods')->where($where)->order('sort_order asc')->select();
        return $res;
    }
}
