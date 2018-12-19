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

class ArticleCat extends Model {

    public $show_in_nav = array('1' => '显示', '0' => '不显示');

    /**
     * 获取当前方法名
     * @return type
     */
    public function getName() {
        $where = array();
        $res = $this->where($where)->field('cat_id,cat_name,parent_id')->find();
        return $res['cat_name'];
    }

    public function getInfo() {
        $where = array();
        $res = $this->where($where)->field('cat_id,cat_name,parent_id')->find();
        return $res;
    }

    /**
     * 获取前当标题
     * @return type
     */
    public function getTitle() {
        $info = $this->getInfo();
        $title = '';
        if ($info->parent_id) {
            $parentName = $this->where('cat_id', $info->parent_id)->value('cat_name');

            $title = $parentName . '  <small><i class="ace-icon fa fa-angle-double-right"></i> ' . $info['cat_name'] . '</small>';
        } else {
            $title = $info['cat_name'];
        }
        return $title;
    }

    /**
     * 获取上级方法名
     * @return boolean
     */
    public function getParentNname() {

        $info = $this->getInfo();

        if ($info->parent_id) {
            return $this->where('cat_id', $info->parent_id)->value('cat_name');
        } else {
            return false;
        }
    }

    /**
     * 择选栏目
     */
    public function selectMenu() {
        $res = db('article_cat')
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

    /**
     * 所有菜单
     * @return type
     */
    public function allMenu() {
        $res = db('article_cat')
                ->field('cat_id,cat_name,parent_id')
                ->order('sort_order asc')
                ->select();
        return nodeTree($res);
    }

}
