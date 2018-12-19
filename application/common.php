<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------


use think\Db;
use think\Config;
// 应用公共文件
function p($str) {
    echo '<pre>';
    print_r($str);
}

function nodeTree($arr, $id = 0, $level = 0) {
    static $array = array();
    foreach ($arr as $v) {
        if ($v['parentid'] == $id) {
            $v['level'] = $level;
            $array[] = $v;
            nodeTree($arr, $v['id'], $level + 1);
        }
    }
    return $array;
}


function catnodeTree($arr, $id = 0, $level = 0) {
    static $array = array();
    foreach ($arr as $v) {
        if ($v['parent_id'] == $id) {
            $v['level'] = $level;
            $array[] = $v;
            catnodeTree($arr, $v['cat_id'], $level + 1);
        }
    }
    return $array;
}
/**
 * 数组转树
 * @param type $list
 * @param type $root
 * @param type $pk
 * @param type $pid
 * @param type $child
 * @return type
 */
function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'parentid', $child = '_child') {
    // 创建Tree
    $tree = array();
    if (is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId = 0;
            if (isset($data[$pid])) {
                $parentId = $data[$pid];
            }
            if ((string) $root == $parentId) {
                $tree[] = &$list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 下拉选择框
 */
function select($array = array(), $id = 0, $str = '', $default_option = '') {
    $string = '<select ' . $str . '>';
    $default_selected = (empty($id) && $default_option) ? 'selected' : '';
    if ($default_option)
        $string .= "<option value='' $default_selected>$default_option</option>";
    if (!is_array($array) || count($array) == 0)
        return false;
    $ids = array();
    if (isset($id))
        $ids = explode(',', $id);
    foreach ($array as $key => $value) {
        $selected = in_array($key, $ids) ? 'selected' : '';
        $string .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
    }
    $string .= '</select>';
    return $string;
}

/**
 * 复选框
 *
 * @param $array 选项 二维数组
 * @param $id 默认选中值，多个用 '逗号'分割
 * @param $str 属性
 * @param $defaultvalue 是否增加默认值 默认值为 -99
 * @param $width 宽度
 */
function checkbox($array = array(), $id = '', $str = '', $defaultvalue = '', $width = 0, $field = '') {
    $string = '';
    $id = trim($id);
    if ($id != '')
        $id = strpos($id, ',') ? explode(',', $id) : array($id);
    if ($defaultvalue)
        $string .= '<input type="hidden" ' . $str . ' value="-99">';
    $i = 1;
    foreach ($array as $key => $value) {
        $key = trim($key);
        $checked = ($id && in_array($key, $id)) ? 'checked' : '';
        if ($width)
            $string .= '<label class="ib" style="width:' . $width . 'px">';
        $string .= '<input type="checkbox" ' . $str . ' id="' . $field . '_' . $i . '" ' . $checked . ' value="' . $key . '"> ' . $value;
        if ($width)
            $string .= '</label>';
        $i++;
    }
    return $string;
}

/**
 * 单选框
 *
 * @param $array 选项 二维数组
 * @param $id 默认选中值
 * @param $str 属性
 */
function radio($array = array(), $id = 0, $str = '', $width = 0, $field = '') {
    $string = '';
    foreach ($array as $key => $value) {
        $checked = trim($id) == trim($key) ? 'checked' : '';
        if ($width)
            $string .= '<label class="ib" style="width:' . $width . 'px">';
        $string .= '<input type="radio" ' . $str . ' id="' . $field . '_' . $key . '" ' . $checked . ' value="' . $key . '"> ' . $value;
        if ($width)
            $string .= '</label>';
    }
    return $string;
}

/**
 * 字符串加密、解密函数
 *
 *
 * @param	string	$txt		字符串
 * @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
 * @param	string	$key		密钥：数字、字母、下划线
 * @param	string	$expiry		过期时间
 * @return	string
 */
function encry_code($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
    $ckey_length = 4;
    $key = md5($key != '' ? $key : config('encry_key'));
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(strtr(substr($string, $ckey_length), '-_', '+/')) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . rtrim(strtr(base64_encode($result), '+/', '-_'), '=');
    }
}

/**
 * 获取文件后缀名,并判断是否合法
 *
 * @param string $file_name
 * @param array $allow_type
 * @return blob
 */
function get_file_suffix($file_name, $allow_type = array())
{
    $tmparray = explode('.', $file_name);
    $tmpname = array_pop($tmparray);
    $file_suffix = strtolower($tmpname);
    if (empty($allow_type))
    {
        return $file_suffix;
    }
    else
    {
        if (in_array($file_suffix, $allow_type))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}


function getUserInfoTable($uid)
{
    $i = $uid % 10;
    $table = sprintf("user_info_%s", $i);
    $check_sql = "SELECT COUNT(1) exist from INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='our' AND table_name ='{$table}' ";
    $result = $this->link->fetchOne($check_sql);

    $sql = <<<EOD
CREATE TABLE IF NOT EXISTS `{$table}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `comic_id` int(10) unsigned NOT NULL DEFAULT '0',
  `chapter_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '章节ID',
  `t` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '时间',
  `read_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '阅读状态 1已阅读 2未阅读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
EOD;

    if ($result['exist'] == 0) {
        $this->link->execute($sql);
    }
    return $table;
}
//哈希分表
/*function get_hash_table($table, $userid) {
    $str = crc32($userid);
    if ($str < 0) {
        $hash = "0" . substr(abs($str), 0, 1);
    } else {
        $hash = substr($str, 0, 2);
    }
    return $table . "_" . $hash;
}*/
//哈希分库
function calc_hash_db($u, $s = 4)
{
    $h = sprintf("%u", crc32($u));
    $h1 = intval(fmod($h, $s));
    return $h1;
}


/*for($i=1;$i<100;$i++)
{
    echo calc_hash_db($i);
    echo "<br>";
}*/
//哈希分表
function calc_hash_tbl($u, $n = 256, $m = 16)
{
    $h = sprintf("%u", crc32($u));
    $h1 = intval($h / $n);
    $h2 = $h1 % $n;
    $h3 = base_convert($h2, 10, $m);
    $h4 = sprintf("%02s", $h3);
    return $h4;
}
//根据ID，哈希分表
function hashID($id, $max)
{
    $md5 = md5($id);
    $str1 = substr($md5, 0, 2);
    $str2 = substr($md5, -2, 2);
    $newStr = intval(intval($str1 . $str2, 16));
    $hashID = $newStr % $max + 1;
    return $hashID;
}
//根据ID数组，获取哈希分表合并的表的SQL语句
function get_hash_select_table($id, $table,$only=0)
{
    $table_name_array = array();
    if(is_array($id)){
        foreach($id as $v){
            $data = Db('datasplit')->where("ds_type_hash=1 and ds_status=1 and ds_name='" .$table. "'")->find();
            if($data){
                $ds_num = $data['ds_num'];
                $ds_field_hash = $data['ds_field_hash'];
                $hID = hashID($v,$ds_num);
                $table_name = $table ."_" .$hID;
                if(!in_array($table_name,$table_name_array)){
                    $table_name_array[] = $table_name;
                }
            }else{
                return false;
            }
        }
    }else{
        return false;
    }
    $i = 0;
    $config = Config::get();
    $out_table_sql = "(";
    foreach($table_name_array as $v){
        if($only == 1){
            if($i == 0){
                $out_table_sql .= "select * from " .$config['database']['prefix'] .$v. " where " .$ds_field_hash ." in (" .implode(',',$id). ")" ;
            }else{
                $out_table_sql .= " union all select * from " .$config['database']['prefix'] .$v. " where " .$ds_field_hash ." in (" .implode(',',$id). ")" ;
            }
        }else{
            if($i == 0){
                $out_table_sql .= "select * from " .$config['database']['prefix'] .$v ;
            }else{
                $out_table_sql .= " union all select * from " .$config['database']['prefix'] .$v;
            }
        }
        $i++;
    }
    $out_table_sql .= ")";
    return $out_table_sql;
}
//根据ID数组，获取哈希分表合并的表
function get_hash_table($id, $table)
{
    $table_name_array = array();
    if(is_array($id)){
        foreach($id as $v){
            $data = Db('datasplit')->where("ds_type_hash=1 and ds_status=1 and ds_name='" .$table. "'")->find();
            if($data){
                $ds_num = $data['ds_num'];
                $hID = hashID($v,$ds_num);
                $table_name = $table ."_" .$hID;
                if(!in_array($table_name,$table_name_array)){
                    $table_name_array[] = $table_name;
                }
            }else{
                return false;
            }
            return $table_name_array;
        }
    }else{
        return false;
    }
}
//获取哈希分表新增ID——单个
function new_hash_id($table)
{
    $data = Db('datasplit')->where("ds_type_hash=1 and ds_status=1 and ds_name='" .$table. "'")->find();
    if($data){
        $newiddata = Db($table .'_0hashautoid')->find();
        $newid = $newiddata['id'] + 1;
        return $newid;
    }else{
        return false;
    }
    return $table_name_array;
}
//获取哈希分表新增ID——N个
function new_hash_id_array($table,$n)
{
    $data = Db('datasplit')->where("ds_type_hash=1 and ds_status=1 and ds_name='" .$table. "'")->find();
    if($data){
        $newiddata = Db($table .'_0hashautoid')->find();
        for($i=1;$i<$n+1;$i++){
            $newid[] = $newiddata['id'] + $i;
        }
        return $newid;
    }else{
        return false;
    }
    return $table_name_array;
}
//获取全部表
function get_all_table()
{
    $sql = "show tables";
    $re = Db::query($sql);
    //转换为索引数组
    $data = [];
    foreach ($re as $index => $item) {
        array_push($data, $item['Tables_in_our']);
    }
    return $data;
}