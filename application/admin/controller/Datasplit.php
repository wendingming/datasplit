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
use think\Config;
use think\Db;
use lib\DateClass;
use lib\TableClass;

class Datasplit extends Common {
    public function _initialize(){
        $this->config = Config::get();
        parent::_initialize();
    }

    public function index() {
        $res = db('datasplit')->order('sort_order asc')->select();

        $this->assign('lists', $res);
        return $this->fetch();
    }

    /*
     * 查看
     */
    public function info() {
        $id = input('id');
        if ($id) {
            //当前记录信息
            $info = db('datasplit')->find($id);
            $this->assign('info1', $info);
            $res = db('datasplit_detail')->where('ds_id=' .$id)->order('ds_status asc,ds_d_id asc')->select();
            $this->assign('id', $id);
        }else{
            $info['ds_table_database'] = 0;
            $info['ds_type_hash'] = 0;
            $info['ds_type_date'] = 0;
            $info['ds_status'] = 0;
            $info['ds_id'] = 0;
            $res = array();
            $this->assign('info1', $info);
        }
        $this->assign('lists', $res);
        return $this->fetch();
    }
    /*
     * 添加/编辑  保存
     */

    public function save() {

        $data = input();
        //print_r($data);die;
        $data_save['ds_id'] = $data['ds_id'];
        $data_save['ds_name'] = $data['ds_name'];
        $data_save['ds_table_database'] = $data['ds_table_database'];
        $data_save['ds_num'] = $data['ds_num'];
        $data_save['ds_type_hash'] = $data['ds_type_hash'];
        $data_save['ds_field_hash'] = $data['ds_field_hash'];
        $data_save['ds_type_date'] = $data['ds_type_date'];
        $data_save['ds_field_date'] = $data['ds_field_date'];
        $data_save['ds_status'] = $data['ds_status'];
        $data_save['ds_remark'] = $data['ds_remark'];
        $data_save['sort_order'] = $data['sort_order'];
        $data_save['child_name'] = $data['child_name'];
        $data_save['child_id_field'] = $data['child_id_field'];
        $data_save['child_id'] = $data['child_id'];
        $data_save['child_name2'] = $data['child_name2'];
        $data_save['child_id_field2'] = $data['child_id_field2'];
        $data_save['child_id2'] = $data['child_id2'];
        $data_save['update_time'] = time();
        if($data_save['ds_id'] == 0 || $data_save['ds_id'] == ''){
            $data_save['create_time'] = time();
        }
        if($data['ds_num'] < 2 || $data['ds_num'] > 1000){
            $this->error('操作失败：分表数量不能小于2，大于1000!');
        }
        if($data_save['ds_id'] == 0 || $data_save['ds_id'] == ''){
            $res = model('Datasplit')->save($data_save);
        }else{
            $res = model('Datasplit')->allowField(true)->save($data_save, ['ds_id' => $data_save['ds_id']]);
        }
        if ($res) {
            $this->success('操作成功', url('index'));
        } else {
            $this->error('操作失败');
        }
    }
    /*
     * 添加
     */

    public function add() {
        $data = input();
        $datahave = Db('datasplit')->where("ds_name = '" .$data['ds_name'] ."'")->find();
        if(count($datahave)>0){
            $this->error('操作失败：' .$data['ds_name'] .'分表已经存在');
        }
        $data['add_time'] = time();
        if($data['ds_num'] < 2 || $data['ds_num'] > 1000){
            $this->error('操作失败：分表数量不能小于2，大于1000.');
        }
        $res = model('datasplit')->allowField(true)->save($data);
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
        $data['update_time'] = time();
        if($data['ds_num'] < 2 || $data['ds_num'] > 1000){
            $this->error('操作失败：分表数量不能小于2，大于1000.');
        }

        $res = model('datasplit')->allowField(true)->save($data, ['ds_id' => $data['ds_id']]);
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
        $res = db('datasplit')->where(['ds_id' => $id])->delete();
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
                $res = Db('datasplit')->where(['ds_id' => $k])->update($data);
            }
            if ($res) {
                $this->success('操作成功', url('index'));
            } else {
                $this->error('操作失败');
            }
        }
    }

    /*
     * 生成
     */

    public function create() {
        $id = input('id');
        $p = input('page');
        $do_child = input('do_child');
        if($p != ''){
            $page = $p;
        }else{
            $page = 0;
        }
        $res = db('datasplit')->where(['ds_id' => $id])->find();
        if(!$res){
            $this->response(self::RESPONSE_ERROR,'找不到记录');
        }
        //类型:0.分表,1.分库
        $ds_table_database = $res['ds_table_database'];
        if($ds_table_database == 0){
            //HASH类型:0.否,1.是
            $ds_type = $res['ds_type_hash'];
            if($ds_type == 1) {
                $curl = $this->split_table($res, $page, $do_child);
                if ($curl == 99999) {
                    if($do_child != 1){//执行完毕，加上更新时间，执行次数+1
                        $sql = "UPDATE {$this->config['database']['prefix']}datasplit set ds_editnum = ds_editnum + 1,update_time=" . time() . " where ds_id = " . $id;
                        $re1 = Db::execute($sql);
                        $sql = "UPDATE {$this->config['database']['prefix']}datasplit_detail set ds_status = ds_status + 1,update_time=" . time() . " where ds_id = " . $id;
                        $re2 = Db::execute($sql);
                    }
                    $this->response(self::RESPONSE_SUCCES, '表拆分执行完毕', 1);
                } else {
                    $this->response(self::RESPONSE_SUCCES, '成功拆分表第' . ($page + 1) . '页，将自动继续下一页分表', 0);
                }
            }else{
                //日期分表方式:0.否,1.日,2.周,3.月,4.年
                $ds_type = $res['ds_type_date'];
                if($ds_type != 0 and $ds_type != '') {
                    $curl = $this->split_table_date($res, $page, $do_child);
                    if ($curl == 99999) {
                        if($do_child != 1) {//执行完毕，加上更新时间，执行次数+1
                            $sql = "UPDATE {$this->config['database']['prefix']}datasplit set ds_editnum = ds_editnum + 1,update_time=" . time() . " where ds_id = " . $id;
                            $re1 = Db::execute($sql);
                            $sql = "UPDATE {$this->config['database']['prefix']}datasplit_detail set ds_status = ds_status + 1,update_time=" . time() . " where ds_id = " . $id;
                            $re2 = Db::execute($sql);
                        }
                        $this->response(self::RESPONSE_SUCCES, '表拆分执行完毕' .$page, 1);
                    } else {
                        $this->response(self::RESPONSE_SUCCES, '成功拆分表第' . ($page + 1) . '页，将自动继续下一页分表', 0);
                    }
                }
            }
        }elseif($ds_table_database == 1){
            //分库
            die;
        }else{
            $this->response(self::RESPONSE_ERROR,'参数错误');
        }
    }

    /**
     * 分表-hash
     */
    public function split_table($data,$page = 0,$do_child = 0) {
        //状态:0.未启用，1.启用
        $ds_status = $data['ds_status'];
        if($ds_status != 1){
            $this->response(self::RESPONSE_ERROR,'错误，未启用');
        }
        if($data['ds_field_hash'] == ''){
            $this->response(self::RESPONSE_ERROR,'错误，没有主表HASH字段');
        }
        if($data['child_name'] != ''){
            if($data['child_id'] == ''){
                $this->response(self::RESPONSE_ERROR,'错误，子表1没有ID字段');
            }
            if($data['child_id_field'] == ''){
                $this->response(self::RESPONSE_ERROR,'错误，子表1没有关联主表ID字段');
            }
        }
        if($data['child_name2'] != ''){
            if($data['child_id2'] == ''){
                $this->response(self::RESPONSE_ERROR,'错误，子表2没有ID字段');
            }
            if($data['child_id_field2'] == ''){
                $this->response(self::RESPONSE_ERROR,'错误，子表2没有关联主表ID字段');
            }
        }
        //hash分表方式:0.否,1.是
        $ds_type = $data['ds_type_hash'];
        if($ds_type == 0) {
            $this->response(self::RESPONSE_ERROR, '未开启按hash分表');
        }
        //hash分表/分库数量
        $ds_num = $data['ds_num'];

        //已经执行过分表的次数
        $ds_editnum = $data['ds_editnum'];
        if($do_child != 1){//主表分表
            if ($ds_editnum>0){
                //已经分过表了，再次重新分表：逻辑：旧分表改表名+_tmp+分表次数，再重新生成新的分表，再把以前分表的数据根据新分表id的hash值导入新分表，重新分表结束后，旧分表保留【或删除】
                if($page == 0){
                    $max_edit = $ds_editnum+1;
                    $data_detail = db('datasplit_detail')->where(['ds_id' => $data['ds_id'],'ds_status' => 1])->select();
                    //改表名
                    $sql = "";
                    foreach($data_detail as $v){
                        $sql = "alter table " .$v['name']. " rename tmp_" .$v['name']. "_" .$max_edit .";";
                        $re1 = Db::execute($sql);
                        $sql = "update {$this->config['database']['prefix']}datasplit_detail set `name`=CONCAT('tmp_',`name`,'_" .$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                        $re2 = Db::execute($sql);
                        if($v['child_name'] != ''){
                            $sql = "alter table " .$v['child_name']. " rename tmp_" .$v['child_name']. "_" .$max_edit .";";
                            $re1 = Db::execute($sql);
                            $sql = "update {$this->config['database']['prefix']}datasplit_detail set `child_name`=CONCAT('tmp_',`child_name`,'_" .$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                            $re2 = Db::execute($sql);
                        }
                        if($v['child_name2'] != ''){
                            $sql = "alter table " .$v['child_name2']. " rename tmp_" .$v['child_name2']. "_" .$max_edit;
                            $re1 = Db::execute($sql);
                            $sql = "update {$this->config['database']['prefix']}datasplit_detail set `child_name2`=CONCAT('tmp_',`child_name2`,'_" .$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                            $re2 = Db::execute($sql);
                        }
                    }
                }
                $data_details = db('datasplit_detail')->where(['ds_id' => $data['ds_id'],'ds_status' => 1])->order('ds_id')->select();
                $res1 = $this->edit_split_hash_table($data,$data_details,$page);
                if ($res1 == 99999) {
                    return 99999;
                } else {
                    return $page;
                }
            }else{
                //第一次分表
                $res1 = $this->first_split_hash_table($data,$page);
                if ($res1 == 99999) {
                    return 99999;
                } else {
                    return $page;
                }
            }
        }else{//子表分表
            if ($ds_editnum>1){
                //已经分过表，需要调用上次分表的子表数据，填充新分表的子表数据
                //主表分表完毕，把旧子表数据插入对应的新子表分表
                $data_detail1 = db('datasplit_detail')->where(['ds_id' => $data['ds_id'], 'ds_status' => 2])->select();//上次分表数据
                $data_detail2 = db('datasplit_detail')->where(['ds_id' => $data['ds_id'], 'ds_status' => 1])->select();//当前分表数据
                if($page<count($data_detail2)) {
                    $datai = $data_detail2[$page];
                    if ($data['child_name'] != '') {
                        foreach ($data_detail1 as $v1) {
                            $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $v1['child_name'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field'] . "` = b.`" . $data['ds_field_hash'] . "`";
                            $re3 = Db::execute($sql);
                        }
                    }
                    if ($data['child_name2'] != '') {
                        foreach ($data_detail1 as $v1) {
                            $sql = "insert into " . $datai['child_name2'] . " select a.* from `" . $v1['child_name2'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field2'] . "` = b.`" . $data['ds_field_hash'] . "`";
                            $re3 = Db::execute($sql);
                        }
                    }
                    return $page;
                }else{
                    return 99999;
                }
            }else{
                //第一次分表，主表分表结束，从子表调数据，添加到子表分表里
                $data_detail = db('datasplit_detail')->where("ds_id = {$data['ds_id']} and ds_status = 1")->order('ds_d_id')->select();
                if($page<count($data_detail)) {
                    $datai = $data_detail[$page];
                    //主表分表完毕，把子表数据插入对应的子表分表
                    if ($data['child_name'] != '') {
                        $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $this->config['database']['prefix'] . $data['child_name'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field'] . "`=b.`" . $data['ds_field_hash'] . "`";
                        $re3 = Db::execute($sql);
                    }
                    if ($data['child_name2'] != '') {
                        $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $this->config['database']['prefix'] . $data['child_name2'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field2'] . "`=b.`" . $data['ds_field_hash'] . "`";
                        $re4 = Db::execute($sql);
                    }
                    return $page;
                }else{
                    return 99999;
                }
            }
        }
        die;
    }

    /**
     * hash分表【第一次】
     * 逻辑：创建自增ID表，此表自增ID=根据hash字段【必须是自增字段】判断自增ID最大值，以后新数据自增ID通过此表replace into 1. 如果发现表中已经有此行数据（根据主键或者唯一索引判断）则先删除此行数据，然后插入新的数据。实现自增ID。
     * 新建分表：根据分表数N，新建【表_1~表_N】
     * 从原表获取数据，根据hash字段，获取HashID，写入对应【表_hashID】中
     */
    public function first_split_hash_table($data,$page = 0) {
        //Db::startTrans(); //开启事务
        //分表分库ID
        $ds_id = $data['ds_id'];
        //分表分库名称【总表总库名称】
        $ds_name = $data['ds_name'];
        //hash分表/子表
        $child_name = $data['child_name'];
        //hash分表/子表【关联主表字段】
        $child_id_field = $data['child_id_field'];
        //hash分表/子表【自增ID】
        $child_id = $data['child_id'];
        //hash分表/子表2
        $child_name2 = $data['child_name2'];
        //hash分表/子表字段2
        $child_id_field2 = $data['child_id_field2'];
        //hash分表/子表【自增ID】
        $child_id2 = $data['child_id2'];
        //hash分表/分库数量
        $ds_num = $data['ds_num'];
        //用于hash分表的字段：ID
        $ds_field_hash = $data['ds_field_hash'];
        $table = get_all_table();

        $table_name = $this->config['database']['prefix'] .$ds_name;
        $table_name_child = $this->config['database']['prefix'] .$child_name;
        $table_name_child2 = $this->config['database']['prefix'] .$child_name2;
        if(!in_array($table_name,$table)){
            $this->response(self::RESPONSE_ERROR,'操作失败：数据库里没有该表' .$table_name);
        }
        if($child_name!=''){
            if(!in_array($table_name_child,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1:' .$table_name_child .'不存在');
            }
            if($child_id_field == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1字段没有填写');
            }
            if($child_id == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1自增ID字段没有填写');
            }
        }
        if($child_name2!=''){
            if(!in_array($table_name_child2,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2:' .$table_name_child2 .'不存在');
            }
            if($child_id_field2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2字段没有填写');
            }
            if($child_id2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2自增ID字段没有填写');
            }
        }
        $count = db($ds_name)->count();
        if($count < 10){
            $this->response(self::RESPONSE_ERROR,'操作失败：最少10条记录才能分表，少于10条记录还分啥表。');
        }
        //记录hash自增长ID的表：【主表名hashkeyid】
        $hashID_table = $this->config['database']['prefix'] .$ds_name .'_hashkeyid';
        if(!in_array($hashID_table,$table)){
            //第一次创建自增ID表
            $maxuid = db($ds_name)->MAX($ds_field_hash);
            $sql = "
              CREATE TABLE `{$hashID_table}` (
                  `id` bigint(20) NOT NULL AUTO_INCREMENT,
                  `stub` char(1) NOT NULL DEFAULT '',
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `unique_stub` (`stub`)
                ) ENGINE=INNODB AUTO_INCREMENT={$maxuid} DEFAULT CHARSET=utf8;
                ";
            $re = Db::execute($sql);
            $sql = "insert into {$hashID_table}(stub) values('1')";
            $re1 = Db::execute($sql);
            if($child_name != ''){//子表1自增ID表
                $hashID_child_table = $this->config['database']['prefix'] .$child_name .'_hashkeyid';
                $maxuid1 = db($child_name)->MAX($child_id);
                if(!in_array($hashID_child_table,$table)) {
                    $sql = "
                      CREATE TABLE `{$hashID_child_table}` (
                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                          `stub` char(1) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `unique_stub` (`stub`)
                        ) ENGINE=INNODB AUTO_INCREMENT={$maxuid1} DEFAULT CHARSET=utf8;
                    ";
                    $re = Db::execute($sql);
                    $sql = "insert into {$hashID_child_table}(stub) values('1')";
                    $re1 = Db::execute($sql);
                }
            }
            if($child_name2 != ''){//子表2自增ID表
                $hashID_child_table2 = $this->config['database']['prefix'] .$child_name2 .'_hashkeyid';
                $maxuid2 = db($child_name2)->MAX($child_id2);
                if(!in_array($hashID_child_table2,$table)) {
                    $sql = "
                      CREATE TABLE `{$hashID_child_table2}` (
                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                          `stub` char(1) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `unique_stub` (`stub`)
                        ) ENGINE=INNODB AUTO_INCREMENT={$maxuid2} DEFAULT CHARSET=utf8;
                    ";
                    $re = Db::execute($sql);
                    $sql = "insert into {$hashID_child_table2}(stub) values('1')";
                    $re1 = Db::execute($sql);
                }
            }
        }
        //hash分表
        for($i=1;$i<$ds_num+1;$i++){
            $create_hashtable = $this->config['database']['prefix'] .$ds_name ."_" .$i;
            if(!in_array($create_hashtable,$table)){
                //建表
                $sql="create table {$create_hashtable} LIKE {$table_name}";
                $re = Db::execute($sql);
                $sql="ALTER TABLE `{$create_hashtable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                $re = Db::execute($sql);
                $sql="ALTER TABLE `{$create_hashtable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                $re = Db::execute($sql);
                $t=time();
                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,create_time,ds_status) values ({$ds_id},'{$create_hashtable}',{$t},0)";
                $reID = Db::execute($sql);
                if($child_name != ''){
                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_hashtable])->find();
                    $create_hashtable_child = $this->config['database']['prefix'] .$child_name ."_" .$i;
                    if(!in_array($create_hashtable_child,$table)){
                        $sql="create table {$create_hashtable_child} LIKE {$table_name_child}";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                        $re = Db::execute($sql);
                        $t=time();
                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_hashtable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                        $re = Db::execute($sql);
                    }
                }
                if($child_name2 != ''){
                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_hashtable])->select();
                    $create_hashtable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$i;
                    if(!in_array($create_hashtable_child2,$table)){
                        $sql="create table {$create_hashtable_child2} LIKE {$table_name_child2}";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                        $re = Db::execute($sql);
                        $t=time();
                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_hashtable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                        $re = Db::execute($sql);
                    }
                }
            }
        }
        //从原表获取数据，循环$ds_num次，写入hash分表里
        $pagecount = ceil($count/$ds_num);
        $strlimit = $page*$pagecount .',' .$pagecount;
        if($page != 0){
            $strlimit = ($page*$pagecount) .',' .$pagecount;
        }
        if($page<$ds_num){
            //循环写入
            $datai = Db($ds_name)->limit($strlimit)->order($ds_field_hash)->select();
            $data_insert = array();
            for($i=1;$i<$ds_num+1;$i++){
                $data_insert['data_' .$i] = array();
            }
            foreach ($datai as $key => $v) {
                $hID = hashID($v[$ds_field_hash],$ds_num);
                $data_insert['data_' .$hID][] = $v;
            }
            for($i=1;$i<$ds_num+1;$i++){
                if(count($data_insert['data_' .$i])>0){
                    $res = Db($ds_name ."_" .$i)->insertAll($data_insert['data_' .$i]);
                    if(!$res){
                        $this->response(self::RESPONSE_ERROR,'写入失败');
                    }
                }else{
                    //没有这个$i的hash数据
                }
            }
            return $page;
        }else{
            return 99999;
        }
        die;
    }

    /**
     * hash分表【第N次分表】
     * 逻辑：旧分表改表名_tmp，再重新生成新的分表，再把以前分表的数据根据新分表id的hash值导入新分表，重新分表结束后，旧分表保留【或删除】
     */
    public function edit_split_hash_table($data,$data_detail,$page = 0) {
        if(empty($data_detail)){
            $this->response(self::RESPONSE_ERROR,'没有原表数据');
        }
        //分表分库ID
        $ds_id = $data['ds_id'];
        //分表分库名称【总表总库名称】
        $ds_name = $data['ds_name'];
        //hash分表/分库数量
        $ds_num = $data['ds_num'];
        //用于hash分表的字段：ID
        $ds_field_hash = $data['ds_field_hash'];
        //hash分表/子表
        $child_name = $data['child_name'];
        //hash分表/子表【关联主表字段】
        $child_id_field = $data['child_id_field'];
        //hash分表/子表【自增ID】
        $child_id = $data['child_id'];
        //hash分表/子表2
        $child_name2 = $data['child_name2'];
        //hash分表/子表字段2
        $child_id_field2 = $data['child_id_field2'];
        //hash分表/子表【自增ID】
        $child_id2 = $data['child_id2'];

        $table_name = $this->config['database']['prefix'] .$ds_name;
        $table = get_all_table();

        $table_name_child = $this->config['database']['prefix'] .$child_name;
        $table_name_child2 = $this->config['database']['prefix'] .$child_name2;
        if(!in_array($table_name,$table)){
            $this->response(self::RESPONSE_ERROR,'操作失败：数据库里没有该表' .$table_name);
        }
        if($child_name!=''){
            if(!in_array($table_name_child,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1:' .$table_name_child .'不存在');
            }
            if($child_id_field == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1字段没有填写');
            }
            if($child_id == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1自增ID字段没有填写');
            }
        }
        if($child_name2!=''){
            if(!in_array($table_name_child2,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2:' .$table_name_child2 .'不存在');
            }
            if($child_id_field2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2字段没有填写');
            }
            if($child_id2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2自增ID字段没有填写');
            }
        }

        //hash分表
        for($i=1;$i<$ds_num+1;$i++){
            $create_hashtable = $this->config['database']['prefix'] .$ds_name ."_" .$i;
            if(!in_array($create_hashtable,$table)){
                $sql="create table {$create_hashtable} LIKE {$table_name}";
                $re = Db::execute($sql);
                $sql="ALTER TABLE `{$create_hashtable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                $re = Db::execute($sql);
                $sql="ALTER TABLE `{$create_hashtable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                $re = Db::execute($sql);
                $t=time();
                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,create_time,ds_status) values ({$ds_id},'{$create_hashtable}',{$t},0)";
                $re = Db::execute($sql);
                if($child_name != ''){
                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_hashtable])->find();
                    $create_hashtable_child = $this->config['database']['prefix'] .$child_name ."_" .$i;
                    if(!in_array($create_hashtable_child,$table)){
                        $sql="create table {$create_hashtable_child} LIKE {$table_name_child}";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                        $re = Db::execute($sql);
                        $t=time();
                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_hashtable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                        $re = Db::execute($sql);
                    }
                }
                if($child_name2 != ''){
                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_hashtable])->select();
                    $create_hashtable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$i;
                    if(!in_array($create_hashtable_child2,$table)){
                        $sql="create table {$create_hashtable_child2} LIKE {$table_name_child2}";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                        $re = Db::execute($sql);
                        $sql="ALTER TABLE `{$create_hashtable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                        $re = Db::execute($sql);
                        $t=time();
                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_hashtable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                        $re = Db::execute($sql);
                    }
                }
            }
        }
        $pagecount = count($data_detail);
        if($page<$pagecount){
            //循环写入
            $sql = 'select * from ' .$data_detail[$page]['name'] .' A order by ' .$ds_field_hash;
            $datai = Db::query($sql);
            $data_insert = array();
            for($i=1;$i<$ds_num+1;$i++){
                $data_insert['data_' .$i] = array();
            }
            foreach ($datai as $key => $v) {
                $hID = hashID($v[$ds_field_hash],$ds_num);
                $data_insert['data_' .$hID][] = $v;
            }
            for($i=1;$i<$ds_num+1;$i++){
                if(count($data_insert['data_' .$i])>0){
                    $res = Db($ds_name ."_" .$i)->insertAll($data_insert['data_' .$i]);
                    if(!$res){
                        $this->response(self::RESPONSE_ERROR,'写入失败');
                    }
                }else{
                    //没有这个$i的hash数据
                }
            }
            return $page;
        }else{
            return 99999;
        }
        die;
    }

    /**
     * 分表-日期
     */
    public function split_table_date($data, $page = 0, $do_child = 0) {
        //状态:0.未启用，1.启用
        $ds_status = $data['ds_status'];
        if($ds_status != 1){
            $this->response(self::RESPONSE_ERROR,'错误，未启用');
        }
        //hash分表/子表
        $child_name = $data['child_name'];
        //hash分表/子表【关联主表字段】
        $child_id_field = $data['child_id_field'];
        //hash分表/子表【自增ID】
        $child_id = $data['child_id'];
        //hash分表/子表2
        $child_name2 = $data['child_name2'];
        //hash分表/子表字段2
        $child_id_field2 = $data['child_id_field2'];
        //hash分表/子表【自增ID】
        $child_id2 = $data['child_id2'];

        //分表分库名称【总表总库名称】
        $ds_name = $data['ds_name'];

        $table = get_all_table();

        $table_name = $this->config['database']['prefix'] .$ds_name;
        $table_name_child = $this->config['database']['prefix'] .$child_name;
        $table_name_child2 = $this->config['database']['prefix'] .$child_name2;
        if(!in_array($table_name,$table)){
            $this->response(self::RESPONSE_ERROR,'操作失败：数据库里没有该表' .$table_name);
        }
        if($child_name!=''){
            if(!in_array($table_name_child,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1:' .$table_name_child .'不存在');
            }
            if($child_id_field == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1字段没有填写');
            }
            if($child_id == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1自增ID字段没有填写');
            }
        }
        if($child_name2!=''){
            if(!in_array($table_name_child2,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2:' .$table_name_child2 .'不存在');
            }
            if($child_id_field2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2字段没有填写');
            }
            if($child_id2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2自增ID字段没有填写');
            }
        }
        //日期分表方式:0.否,1.日,2.周,3.月,4.年
        $ds_type = $data['ds_type_date'];
        if($ds_type == 0) {
            $this->response(self::RESPONSE_ERROR, '未开启按日期分表');
        }

        //已经执行过分表的次数
        $ds_editnum = $data['ds_editnum'];
        if($do_child != 1) {//主表分表
            if ($ds_editnum>0){
                //已经分过表了，再次重新分表：逻辑：旧分表改表名:tmp_+表名+分表次数，再重新生成新的分表，再把以前分表的数据根据日期规则导入新分表，重新分表结束后，旧分表保留【或删除】
                if($page == 0){
                    $max_edit = $ds_editnum+1;
                    $data_detail = db('datasplit_detail')->where(['ds_id' => $data['ds_id'],'ds_status' => 1])->select();
                    //改表名
                    foreach($data_detail as $v){
                        $sql="alter table " .$v['name']. " rename tmp_" .$v['name']. "_" .$max_edit;
                        $re1 = Db::execute($sql);
                        $sql="update {$this->config['database']['prefix']}datasplit_detail set `name`=CONCAT('tmp_',`name`, '_".$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                        $re2 = Db::execute($sql);
                        if($v['child_name'] != ''){
                            $sql = "alter table " .$v['child_name']. " rename tmp_" .$v['child_name']. "_" .$max_edit .";";
                            $re1 = Db::execute($sql);
                            $sql = "update {$this->config['database']['prefix']}datasplit_detail set `child_name`=CONCAT('tmp_',`child_name`,'_" .$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                            $re2 = Db::execute($sql);
                        }
                        if($v['child_name2'] != ''){
                            $sql = "alter table " .$v['child_name2']. " rename tmp_" .$v['child_name2']. "_" .$max_edit;
                            $re1 = Db::execute($sql);
                            $sql = "update {$this->config['database']['prefix']}datasplit_detail set `child_name2`=CONCAT('tmp_',`child_name2`,'_" .$max_edit. "') where ds_d_id=" .$v['ds_d_id'] .";";
                            $re2 = Db::execute($sql);
                        }
                    }
                }
                $data_details = db('datasplit_detail')->where(['ds_id' => $data['ds_id'],'ds_status' => 1])->order('ds_id')->select();
                $res1 = $this->edit_split_date_table($data,$data_details,$page);
                if ($res1 == 99999) {
                    return 99999;
                } else {
                    return $page;
                }
            }else{
                //第一次分表
                $res1 = $this->first_split_date_table($data,$page);
                if ($res1 == 99999) {
                    $ds_name = $data['ds_name'];
                    //用于分表的字段：ID
                    $ds_field_hash = $data['ds_field_hash'];
                    //记录自增长ID的表：【主表名datekeyid】
                    $hashID_table = $this->config['database']['prefix'] .$ds_name .'_datekeyid';
                    if(!in_array($hashID_table,$table)){
                        //第一次创建自增ID表
                        $maxuid = db($ds_name)->MAX($ds_field_hash);
                        $sql = "
                          CREATE TABLE `{$hashID_table}` (
                              `id` bigint(20) NOT NULL AUTO_INCREMENT,
                              `stub` char(1) NOT NULL DEFAULT '',
                              PRIMARY KEY (`id`),
                              UNIQUE KEY `unique_stub` (`stub`)
                            ) ENGINE=INNODB AUTO_INCREMENT={$maxuid} DEFAULT CHARSET=utf8;
                            ";
                        $re = Db::execute($sql);
                        $sql = "insert into {$hashID_table}(stub) values('1')";
                        $re1 = Db::execute($sql);
                    }
                    if($child_name != ''){//子表1自增ID表
                        $hashID_child_table = $this->config['database']['prefix'] .$child_name .'_datekeyid';
                        $maxuid1 = db($child_name)->MAX($child_id);
                        if(!in_array($hashID_child_table,$table)) {
                            $sql = "
                      CREATE TABLE `{$hashID_child_table}` (
                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                          `stub` char(1) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `unique_stub` (`stub`)
                        ) ENGINE=INNODB AUTO_INCREMENT={$maxuid1} DEFAULT CHARSET=utf8;
                    ";
                            $re = Db::execute($sql);
                            $sql = "insert into {$hashID_child_table}(stub) values('1')";
                            $re1 = Db::execute($sql);
                        }
                    }
                    if($child_name2 != ''){//子表2自增ID表
                        $hashID_child_table2 = $this->config['database']['prefix'] .$child_name2 .'_datekeyid';
                        $maxuid2 = db($child_name2)->MAX($child_id2);
                        if(!in_array($hashID_child_table2,$table)) {
                            $sql = "
                      CREATE TABLE `{$hashID_child_table2}` (
                          `id` bigint(20) NOT NULL AUTO_INCREMENT,
                          `stub` char(1) NOT NULL DEFAULT '',
                          PRIMARY KEY (`id`),
                          UNIQUE KEY `unique_stub` (`stub`)
                        ) ENGINE=INNODB AUTO_INCREMENT={$maxuid2} DEFAULT CHARSET=utf8;
                    ";
                            $re = Db::execute($sql);
                            $sql = "insert into {$hashID_child_table2}(stub) values('1')";
                            $re1 = Db::execute($sql);
                        }
                    }
                    return 99999;
                } else {
                    return $page;
                }
            }
        }else{//子表分表
            if ($ds_editnum>1){
                //已经分过表，需要调用上次分表的子表数据，填充新分表的子表数据
                //主表分表完毕，【分页根据:page=这次分表数量第N个主表，循环上次全部子表，把上次全部子表里包含本次主表N，所关联ID的数据提取出来，写入这次第N个子表】
                $data_detail1 = db('datasplit_detail')->where(['ds_id' => $data['ds_id'], 'ds_status' => 2])->select();//上次分表数据
                $data_detail2 = db('datasplit_detail')->where(['ds_id' => $data['ds_id'], 'ds_status' => 1])->select();//当前分表数据
                if($page<count($data_detail2)) {
                    $datai = $data_detail2[$page];
                    if ($data['child_name'] != '') {
                        foreach ($data_detail1 as $v1) {
                            $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $v1['child_name'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field'] . "` = b.`" . $data['ds_field_hash'] . "`";
                            $re3 = Db::execute($sql);
                        }
                    }
                    if ($data['child_name2'] != '') {
                        foreach ($data_detail1 as $v1) {
                            $sql = "insert into " . $datai['child_name2'] . " select a.* from `" . $v1['child_name2'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field2'] . "` = b.`" . $data['ds_field_hash'] . "`";
                            $re3 = Db::execute($sql);
                        }
                    }
                    return $page;
                }else{
                    return 99999;
                }
            }else{
                //第一次分表，主表分表结束，从子表调数据，添加到子表分表里
                $data_detail = db('datasplit_detail')->where("ds_id = {$data['ds_id']} and ds_status = 1")->order('ds_d_id')->select();
                if($page<count($data_detail)) {
                    $datai = $data_detail[$page];
                    //主表分表完毕，把子表数据插入对应的子表分表
                    if ($data['child_name'] != '') {
                        $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $this->config['database']['prefix'] . $data['child_name'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field'] . "`=b.`" . $data['ds_field_hash'] . "`";
                        $re3 = Db::execute($sql);
                    }
                    if ($data['child_name2'] != '') {
                        $sql = "insert into " . $datai['child_name'] . " select a.* from `" . $this->config['database']['prefix'] . $data['child_name2'] . "` a inner join `" . $datai['name'] . "` b on a.`" . $data['child_id_field2'] . "`=b.`" . $data['ds_field_hash'] . "`";
                        $re4 = Db::execute($sql);
                    }
                    return $page;
                }else{
                    return 99999;
                }
            }
        }
        die;
    }
    /**
     * 日期分表【第一次】
     * 逻辑：创建自增ID表，此表自增ID=根据id字段【必须是自增字段】判断自增ID最大值，以后新数据自增ID通过此表replace into 1. 如果发现表中已经有此行数据（根据主键或者唯一索引判断）则先删除此行数据，然后插入新的数据。实现自增ID。
     * 新建分表：根据日期设置【日，周，月，年】，新建【表_1~表_N】
     * 从原表获取数据，根据日期字段【unix时间戳】，获取日期，写入对应【表_日期】中
     */
    public function first_split_date_table($data,$page = 0) {
        //Db::startTrans(); //开启事务
        //分表分库ID
        $ds_id = $data['ds_id'];
        //分表分库名称【总表总库名称】
        $ds_name = $data['ds_name'];
        //分表方式:0.否,1.hash
        //$ds_type_hash = $data['ds_type_hash'];
        //分表方式:0.否,1.【日】,2.【周】,3.【月】,4.【年】
        $ds_type_date = $data['ds_type_date'];
        if($ds_type_date == 0 || $ds_type_date == ''){
            $this->response(self::RESPONSE_ERROR,'操作失败：未启用日期分表方式',$data);
        }
        $ds_field_date = $data['ds_field_date'];
        //日期分表/分库数量【一次性循环多少次数据】
        $ds_num = $data['ds_num'];
        //状态:0.启用，1.未启用
        $ds_status = $data['ds_status'];
        //用于分表的字段：ID
        $ds_field_hash = $data['ds_field_hash'];

        $table = get_all_table();

        //hash分表/子表
        $child_name = $data['child_name'];
        //hash分表/子表【关联主表字段】
        $child_id_field = $data['child_id_field'];
        //hash分表/子表【自增ID】
        $child_id = $data['child_id'];
        //hash分表/子表2
        $child_name2 = $data['child_name2'];
        //hash分表/子表字段2
        $child_id_field2 = $data['child_id_field2'];
        //hash分表/子表【自增ID】
        $child_id2 = $data['child_id2'];

        $table_name = $this->config['database']['prefix'] .$ds_name;
        $table_name_child = $this->config['database']['prefix'] .$child_name;
        $table_name_child2 = $this->config['database']['prefix'] .$child_name2;
        if(!in_array($table_name,$table)){
            $this->response(self::RESPONSE_ERROR,'操作失败：数据库里没有该表' .$table_name);
        }
        if($child_name!=''){
            if(!in_array($table_name_child,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1:' .$table_name_child .'不存在');
            }
            if($child_id_field == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1字段没有填写');
            }
            if($child_id == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1自增ID字段没有填写');
            }
        }
        if($child_name2!=''){
            if(!in_array($table_name_child2,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2:' .$table_name_child2 .'不存在');
            }
            if($child_id_field2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2字段没有填写');
            }
            if($child_id2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2自增ID字段没有填写');
            }
        }
        $count = db($ds_name)->count();
        if($count < 10){
            $this->response(self::RESPONSE_ERROR,'操作失败：最少10条记录才能分表，少于10条记录还分啥表。');
        }
        //循环列出日周月年表【按日期建表】
        //先获取最小日期，最大日期，然后判断【日期范围】建表
        $sql = "select min({$ds_field_date}) as min_time,max({$ds_field_date}) as max_time from " .$table_name;
        $datadate = Db::query($sql);
        $min_time = $datadate[0]['min_time'];
        $max_time = $datadate[0]['max_time'];
        $beginTime = strtotime(date('Y-m-d 00:00:00', $min_time));//第一天凌晨
        $endTime = strtotime(date('Y-m-d 23:59:59', $min_time));//第一天最后一秒
        $beginTime1 = strtotime(date('Y-m-d 00:00:00', $max_time));//最后一天凌晨
        $endTime1 = strtotime(date('Y-m-d 23:59:59', $max_time));//最后一天最后一秒

        if($ds_type_date == 1){
            //按日分表
            $timediff = $endTime1-$beginTime;
            $days = intval($timediff/86400);//时间范围内总共多少天
            if($page*$ds_num > $days){//翻页【按照设置的数量，例如数量=30，则每隔30天，翻页】
                return 99999;
            }else{
                for($i = ($page * $ds_num + 1);$i < (($page+1) * $ds_num + 1);$i++){
                    $s_time = $beginTime + 86400 * ($i-1);
                    $e_time = $endTime + 86400 * ($i-1);
                    $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                    //判断有没有数据
                    $datai = Db($ds_name)->where($where)->order($ds_field_hash)->count();
                    if($datai>0){
                        //有数据，则建表写入
                        $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .date('Y_m_d', $s_time);
                        if(!in_array($create_datetable,$table)){
                            $sql="create table {$create_datetable} LIKE {$table_name}";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                            $re = Db::execute($sql);
                            $sql="insert into {$create_datetable} select * from {$table_name} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                            $t=time();
                            $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                            $re = Db::execute($sql);
                            if($child_name != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .date('Y_m_d', $s_time);
                                if(!in_array($create_datetable_child,$table)){
                                    $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                            if($child_name2 != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .date('Y_m_d', $s_time);
                                if(!in_array($create_datetable_child2,$table)){
                                    $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                        }
                    }
                }
                return $page;
            }
        }elseif($ds_type_date == 2){
            //按周分表
            $W = new DateClass();
            $weekdate = $W->getTermWeeks( date('Y-m-d',$beginTime),date('Y-m-d',$beginTime1) );
            //$weekarr = array_column($weekdate,'dates');
            //print_r($weekdate);die;
            $weeks = count($weekdate);//时间范围内总共多少周
            if($page*$ds_num > $weeks){//翻页【按照设置的数量，例如数量=30，则每隔30周，翻页】
                return 99999;
            }else{
                for($i = ($page * $ds_num + 1);$i < (($page+1) * $ds_num + 1);$i++){
                    $s_time = strtotime(date('Y-m-d 00:00:00', strtotime($weekdate[$i]['dates']['start_date']) ));
                    $e_time = strtotime( date('Y-m-d 23:59:59', strtotime($weekdate[$i]['dates']['end_date']) ));//这一周最后一秒
                    $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                    //判断有没有数据
                    $datai = Db($ds_name)->where($where)->order($ds_field_hash)->count();
                    if($datai>0){
                        //有数据，则建表写入
                        $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                        if(!in_array($create_datetable,$table)){
                            $sql="create table {$create_datetable} LIKE {$table_name}";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                            $re = Db::execute($sql);
                            $sql="insert into {$create_datetable} select * from {$table_name} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                            $t=time();
                            $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                            $re = Db::execute($sql);
                            if($child_name != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                                if(!in_array($create_datetable_child,$table)){
                                    $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                            if($child_name2 != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                                if(!in_array($create_datetable_child2,$table)){
                                    $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                        }
                    }
                }
                return $page;
            }
        }elseif($ds_type_date == 3){
            //按月分表
            $W = new DateClass();
            $monthdate = $W->getTermMonth( date('Y-m-d',$beginTime),date('Y-m-d',$beginTime1) );
            //$weekarr = array_column($weekdate,'dates');
            //print_r($monthdate);die;
            $months = count($monthdate);//时间范围内总共多少月
            if($page*$ds_num > $months){//翻页【按照设置的数量，例如数量=3，则每隔3月，翻页】
                return 99999;
            }else{
                for($i = ($page * $ds_num + 1);$i < (($page+1) * $ds_num + 1);$i++){
                    if(!empty($monthdate[$i])){
                        $s_time = $monthdate[$i]['start_time'];
                        $e_time = $monthdate[$i]['end_time'];//这一月最后一秒
                        $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                        //判断有没有数据
                        $datai = Db($ds_name)->where($where)->order($ds_field_hash)->count();
                        if($datai>0){
                            //有数据，则建表写入
                            $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                            if(!in_array($create_datetable,$table)){
                                $sql="create table {$create_datetable} LIKE {$table_name}";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                $re = Db::execute($sql);
                                $sql="insert into {$create_datetable} select * from {$table_name} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                                $t=time();
                                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                                $re = Db::execute($sql);
                                if($child_name != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                    $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                                    if(!in_array($create_datetable_child,$table)){
                                        $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                                if($child_name2 != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                    $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                                    if(!in_array($create_datetable_child2,$table)){
                                        $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                            }
                        }
                    }
                }
                return $page;
            }

        }elseif($ds_type_date == 4){
            //按年分表
            $W = new DateClass();
            $yeardate = $W->getTermYear( date('Y-m-d',$beginTime),date('Y-m-d',$beginTime1) );
            //$weekarr = array_column($weekdate,'dates');
            //print_r($monthdate);die;
            $months = count($yeardate);//时间范围内总共多少月
            if($page*$ds_num > $months){//翻页【按照设置的数量，例如数量=3，则每隔3月，翻页】
                return 99999;
            }else{
                for($i = ($page * $ds_num + 1);$i < (($page+1) * $ds_num + 1);$i++){
                    if(!empty($yeardate[$i])){
                        $s_time = $yeardate[$i]['start_time'];
                        $e_time = $yeardate[$i]['end_time'];//这一月最后一秒
                        $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                        //判断有没有数据
                        $datai = Db($ds_name)->where($where)->order($ds_field_hash)->count();
                        if($datai>0){
                            //有数据，则建表写入
                            $create_datetable = $this->config['database']['prefix'] .$ds_name ."_year" .$yeardate[$i]['year'];
                            if(!in_array($create_datetable,$table)){
                                $sql="create table {$create_datetable} LIKE {$table_name}";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                $re = Db::execute($sql);
                                $sql="insert into {$create_datetable} select * from {$table_name} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                                $t=time();
                                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                                $re = Db::execute($sql);
                                if($child_name != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                    $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_year" .$yeardate[$i]['year'];
                                    if(!in_array($create_datetable_child,$table)){
                                        $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                                if($child_name2 != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                    $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_year" .$yeardate[$i]['year'];
                                    if(!in_array($create_datetable_child2,$table)){
                                        $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                            }
                        }
                    }
                }
                return $page;
            }

        }else{
            $this->response(self::RESPONSE_ERROR,'操作失败：参数错误' .$ds_type_date,array());
        }
        die;
    }
    /**
     * 日期分表【第N次分表】
     * 逻辑：旧分表改表名_tmp，再重新生成新的分表，再把以前分表的数据根据新分表日期导入新分表，重新分表结束后，旧分表保留【或删除】
     */
    public function edit_split_date_table($data,$data_detail,$page = 0) {
        if(empty($data_detail)){
            $this->response(self::RESPONSE_ERROR,'没有原表数据');
        }
        //分表分库ID
        $ds_id = $data['ds_id'];
        //分表分库名称【总表总库名称】
        $ds_name = $data['ds_name'];
        //hash分表/分库数量
        $ds_num = $data['ds_num'];
        //用于hash分表的字段：ID
        $ds_field_hash = $data['ds_field_hash'];
        //分表方式:0.否,1.【日】,2.【周】,3.【月】,4.【年】
        $ds_type_date = $data['ds_type_date'];
        if($ds_type_date == 0 || $ds_type_date == ''){
            $this->response(self::RESPONSE_ERROR,'操作失败：未启用日期分表方式',$data);
        }
        //用于日期分表字段
        $ds_field_date = $data['ds_field_date'];

        $table = get_all_table();

        //hash分表/子表
        $child_name = $data['child_name'];
        //hash分表/子表【关联主表字段】
        $child_id_field = $data['child_id_field'];
        //hash分表/子表【自增ID】
        $child_id = $data['child_id'];
        //hash分表/子表2
        $child_name2 = $data['child_name2'];
        //hash分表/子表字段2
        $child_id_field2 = $data['child_id_field2'];
        //hash分表/子表【自增ID】
        $child_id2 = $data['child_id2'];

        $table_name = $this->config['database']['prefix'] .$ds_name;
        $table_name_child = $this->config['database']['prefix'] .$child_name;
        $table_name_child2 = $this->config['database']['prefix'] .$child_name2;
        if(!in_array($table_name,$table)){
            $this->response(self::RESPONSE_ERROR,'操作失败：数据库里没有该表' .$table_name);
        }
        if($child_name!=''){
            if(!in_array($table_name_child,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1:' .$table_name_child .'不存在');
            }
            if($child_id_field == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1字段没有填写');
            }
            if($child_id == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表1自增ID字段没有填写');
            }
        }
        if($child_name2!=''){
            if(!in_array($table_name_child2,$table)){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2:' .$table_name_child2 .'不存在');
            }
            if($child_id_field2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2字段没有填写');
            }
            if($child_id2 == ''){
                $this->response(self::RESPONSE_ERROR,'操作失败：子表2自增ID字段没有填写');
            }
        }


        $pagecount = count($data_detail);
        if($page<$pagecount){
            //循环写入
            //$sql = 'select * from ' .$data_detail[$page]['name'] .' A order by ' .$ds_field_hash;
            //$datai = Db::query($sql);
            //循环列出日周月年表【按日期建表】
            //先获取最小日期，最大日期，然后判断【日期范围】建表
            $sql = "select min({$ds_field_date}) as min_time,max({$ds_field_date}) as max_time from " .$data_detail[$page]['name'];
            $datadate = Db::query($sql);
            $min_time = $datadate[0]['min_time'];
            $max_time = $datadate[0]['max_time'];
            $beginTime = strtotime(date('Y-m-d 00:00:00', $min_time));//第一天凌晨
            $endTime = strtotime(date('Y-m-d 23:59:59', $min_time));//第一天最后一秒
            $beginTime1 = strtotime(date('Y-m-d 00:00:00', $max_time));//最后一天凌晨
            $endTime1 = strtotime(date('Y-m-d 23:59:59', $max_time));//最后一天最后一秒

            if($ds_type_date == 1){
                //按日分表
                $timediff = $endTime1-$beginTime;
                $days = ceil($timediff/86400);//时间范围内总共多少天
                for($i = 1;$i <= $days;$i++){
                    $s_time = $beginTime + 86400 * ($i-1);
                    $e_time = $endTime + 86400 * ($i-1);
                    $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                    $table_name1 = $data_detail[$page]['name'];//str_replace($this->config['database']['prefix'],'',$data_detail[$page]['name']);
                    //判断有没有数据
                    $sql = "select count({$ds_field_hash}) as c1 from {$table_name1} where {$where} order by {$ds_field_hash}";
                    $datai1 = Db::query($sql);
                    if(count($datai1)>0){
                        $datai = $datai1[0]['c1'];
                    }else{
                        $datai = 0;
                    }
                    if($datai>0){
                        //有数据，则建表写入
                        $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .date('Y_m_d', $s_time);
                        $table = get_all_table();
                        if(!in_array($create_datetable,$table)){
                            $sql="create table {$create_datetable} LIKE {$data_detail[$page]['name']}";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                            $re = Db::execute($sql);
                            $sql="insert into {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                            $t=time();
                            $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                            $re = Db::execute($sql);
                            if($child_name != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .date('Y_m_d', $s_time);
                                if(!in_array($create_datetable_child,$table)){
                                    $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                            if($child_name2 != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .date('Y_m_d', $s_time);
                                if(!in_array($create_datetable_child2,$table)){
                                    $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                        }else{
                            //已经存在表
                            $sql="INSERT INTO {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                        }
                    }
                }
            }elseif($ds_type_date == 2){
                //按周分表
                $W = new DateClass();
                $weekdate = $W->getTermWeeks( date('Y-m-d',$beginTime),date('Y-m-d',$endTime1) );
                //$weekarr = array_column($weekdate,'dates');
                //print_r($weekdate);die;
                $weeks = count($weekdate);//时间范围内总共多少周
                for($i = 1;$i <= $weeks;$i++){
                    $s_time = strtotime(date('Y-m-d 00:00:00', strtotime($weekdate[$i]['dates']['start_date']) ));
                    $e_time = strtotime( date('Y-m-d 23:59:59', strtotime($weekdate[$i]['dates']['end_date']) ));//这一周最后一秒
                    $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                    $table_name1 = $data_detail[$page]['name'];//str_replace($this->config['database']['prefix'],'',$data_detail[$page]['name']);
                    //判断有没有数据
                    $sql = "select count({$ds_field_hash}) as c1 from {$table_name1} where {$where} order by {$ds_field_hash}";
                    $datai1 = Db::query($sql);
                    if(count($datai1)>0){
                        $datai = $datai1[0]['c1'];
                    }else{
                        $datai = 0;
                    }
                    if($datai>0){
                        //有数据，则建表写入
                        $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                        $table = get_all_table();
                        if(!in_array($create_datetable,$table)){
                            $sql="create table {$create_datetable} LIKE {$data_detail[$page]['name']}";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                            $re = Db::execute($sql);
                            $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                            $re = Db::execute($sql);
                            $sql="insert into {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                            $t=time();
                            $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                            $re = Db::execute($sql);
                            if($child_name != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                                if(!in_array($create_datetable_child,$table)){
                                    $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                            if($child_name2 != ''){
                                $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$weekdate[$i]['year'] ."_week" .$weekdate[$i]['year_week_num'];
                                if(!in_array($create_datetable_child2,$table)){
                                    $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                    $re = Db::execute($sql);
                                    $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                    $re = Db::execute($sql);
                                    $t=time();
                                    $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                    $re = Db::execute($sql);
                                }
                            }
                        }else{
                            //已经存在表
                            $sql="INSERT INTO {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                            $re = Db::execute($sql);
                        }
                    }
                }
            }elseif($ds_type_date == 3){
                //按月分表
                $W = new DateClass();
                $monthdate = $W->getTermMonth( date('Y-m-d',$beginTime),date('Y-m-d',$endTime1) );
                //$weekarr = array_column($weekdate,'dates');
                //print_r($monthdate);die;
                $months = count($monthdate);//时间范围内总共多少月
                for($i = 1;$i <= $months;$i++){
                    if(!empty($monthdate[$i])){
                        $s_time = $monthdate[$i]['start_time'];
                        $e_time = $monthdate[$i]['end_time'];//这一月最后一秒
                        $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                        $table_name1 = $data_detail[$page]['name'];//str_replace($this->config['database']['prefix'],'',$data_detail[$page]['name']);
                        //判断有没有数据
                        $sql = "select count({$ds_field_hash}) as c1 from {$table_name1} where {$where} order by {$ds_field_hash}";
                        $datai1 = Db::query($sql);
                        if(count($datai1)>0){
                            $datai = $datai1[0]['c1'];
                        }else{
                            $datai = 0;
                        }
                        if($datai>0){
                            //有数据，则建表写入
                            $create_datetable = $this->config['database']['prefix'] .$ds_name ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                            $table = get_all_table();
                            if(!in_array($create_datetable,$table)){
                                $sql="create table {$create_datetable} LIKE {$data_detail[$page]['name']}";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                $re = Db::execute($sql);
                                $sql="insert into {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                                $t=time();
                                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                                $re = Db::execute($sql);
                                if($child_name != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                    $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                                    if(!in_array($create_datetable_child,$table)){
                                        $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                                if($child_name2 != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                    $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_" .$monthdate[$i]['year'] ."_month" .$monthdate[$i]['month'];
                                    if(!in_array($create_datetable_child2,$table)){
                                        $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                            }else{
                                //已经存在表
                                $sql="INSERT INTO {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                            }
                        }
                    }
                }
            }elseif($ds_type_date == 4){
                //按年分表
                $W = new DateClass();
                $yeardate = $W->getTermYear( date('Y-m-d',$beginTime),date('Y-m-d',$beginTime1) );
                //$weekarr = array_column($weekdate,'dates');
                //print_r($monthdate);die;
                $months = count($yeardate);//时间范围内总共多少月
                for($i = 1;$i <= $months;$i++){
                    if(!empty($yeardate[$i])){
                        $s_time = $yeardate[$i]['start_time'];
                        $e_time = $yeardate[$i]['end_time'];//这一月最后一秒
                        $where = "{$ds_field_date} BETWEEN {$s_time} AND {$e_time}";
                        $table_name1 = $data_detail[$page]['name'];//str_replace($this->config['database']['prefix'],'',$data_detail[$page]['name']);
                        //判断有没有数据
                        $sql = "select count({$ds_field_hash}) as c1 from {$table_name1} where {$where} order by {$ds_field_hash}";
                        $datai1 = Db::query($sql);
                        if(count($datai1)>0){
                            $datai = $datai1[0]['c1'];
                        }else{
                            $datai = 0;
                        }
                        //$datai = Db($table_name1)->where($where)->order($ds_field_hash)->count();
                        if($datai>0){
                            //有数据，则建表写入
                            $create_datetable = $this->config['database']['prefix'] .$ds_name ."_year" .$yeardate[$i]['year'];
                            $table = get_all_table();
                            if(!in_array($create_datetable,$table)){
                                $sql="create table {$create_datetable} LIKE {$data_detail[$page]['name']}";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` CHANGE {$ds_field_hash} {$ds_field_hash} INT(20) UNSIGNED NOT NULL ;";
                                $re = Db::execute($sql);
                                $sql="ALTER TABLE `{$create_datetable}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                $re = Db::execute($sql);
                                $sql="insert into {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                                $t=time();
                                $sql="INSERT INTO {$this->config['database']['prefix']}datasplit_detail(ds_id,`name`,is_hash_date,create_time,ds_status,begin_time,end_time) values ({$ds_id},'{$create_datetable}',1,{$t},0,{$s_time},{$e_time})";
                                $re = Db::execute($sql);
                                if($child_name != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->find();
                                    $create_datetable_child = $this->config['database']['prefix'] .$child_name ."_year" .$yeardate[$i]['year'];
                                    if(!in_array($create_datetable_child,$table)){
                                        $sql="create table {$create_datetable_child} LIKE {$table_name_child}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` CHANGE {$child_id} {$child_id} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name='{$create_datetable_child}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                                if($child_name2 != ''){
                                    $datasplit_detail_data = db('datasplit_detail')->where(['ds_id' => $ds_id,'name' => $create_datetable])->select();
                                    $create_datetable_child2 = $this->config['database']['prefix'] .$child_name2 ."_year" .$yeardate[$i]['year'];
                                    if(!in_array($create_datetable_child2,$table)){
                                        $sql="create table {$create_datetable_child2} LIKE {$table_name_child2}";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` CHANGE {$child_id2} {$child_id2} INT(20) UNSIGNED NOT NULL ;";
                                        $re = Db::execute($sql);
                                        $sql="ALTER TABLE `{$create_datetable_child2}` ENGINE = InnoDB DEFAULT CHARSET utf8;";
                                        $re = Db::execute($sql);
                                        $t=time();
                                        $sql="UPDATE {$this->config['database']['prefix']}datasplit_detail set child_name2='{$create_datetable_child2}' where ds_d_id={$datasplit_detail_data['ds_d_id']}";
                                        $re = Db::execute($sql);
                                    }
                                }
                            }else{
                                //已经存在表
                                $sql="INSERT INTO {$create_datetable} select * from {$data_detail[$page]['name']} where {$where} order by {$ds_field_hash}";
                                $re = Db::execute($sql);
                            }
                        }
                    }
                }
            }else{
                $this->response(self::RESPONSE_ERROR,'操作失败：参数错误' .$ds_type_date,array());
            }
            return $page;
        }else{
            return 99999;
        }
        die;
    }
    /**
     * 分库
     */
    public function split_database() {
        $table = get_all_table();
        print_r($table);die;
    }
}
