<?php
require 'db.php';

// 数据库设定
$config = array(
    'host' => 'localhost',
    'dbname' => 'our',
    'user' => 'root',
    'password' => 'zk888',
    'pconnect' => 0,
    'charset' => 'utf8'
);
    // 创建数据连接
    $dbconn = DB::get_conn($config);

    // 判断连接是否有效
    $status = pdo_ping($dbconn);

    if($status){
        echo 'connect ok'.PHP_EOL;
    }else{
        echo 'connect failure'.PHP_EOL;

        // 重置连接
        DB::reset_connect();
        $dbconn = DB::get_conn($config);
    }
$sqlstr='';
if(isset($_POST['q'])) {
    $sqlstr = strtolower($_POST['q']);
    if(strpos($sqlstr,'select ') !==false) {
        // 查询select
        $condparam = array(mt_rand(1, 3));
        $data = DB::query($dbconn, $sqlstr, $condparam);
    }elseif(strpos($sqlstr,'insert ') !==false){
        //新增
        $condparam = array(mt_rand(1, 3));
        $add = DB::exec($dbconn, $sqlstr, $condparam);
        if($add)
        {
            echo '新增成功'.'<br>';
        }
        else {
            echo '新增失败' . '<br>';
        }
    }elseif(strpos($sqlstr,'update ') !==false){
        //修改
        $condparam = array(mt_rand(1, 3));
        $upd = DB::exec($dbconn, $sqlstr, $condparam);
        if($upd)
        {
            echo '修改成功'.'<br>';
        }
        else {
            echo '修改失败' . '<br>';
        }
    }elseif(strpos($sqlstr,'delete ') !==false){
        //删除
        $condparam = array(mt_rand(1, 3));
        $del = DB::exec($dbconn, $sqlstr, $condparam);
        if($del)
        {
            echo '删除成功'.'<br>';
        }
        else {
            echo '删除失败' . '<br>';
        }
    }else{
        echo '不支持语句'.'<br>';
    }
}
/**
 * 检查连接是否可用
 * @param  Link $dbconn 数据库连接
 * @return Boolean
 */
function pdo_ping($dbconn){
    try{
        $dbconn->getAttribute(PDO::ATTR_SERVER_INFO);
    } catch (PDOException $e) {
        if(strpos($e->getMessage(), 'MySQL server has gone away')!==false){
            return false;
        }
    }
    return true;
}
?>
<div style="padding:10px 0 10px 20px">
    <form method="post" name="DF" action="" enctype="multipart/form-data">
        <textarea id="q" name="q" cols="70" rows="10" style="width:98%;margin:10px 0;"><?php echo $sqlstr; ?></textarea>
        <input type="submit" name="GoSQL" value="执行" style="width:100px">&nbsp;&nbsp;
        <input type="button" name="Clear" value=" 清除 " onclick="document.DF.q.value=''" style="width:100px">
    </form>
</div>

    <div style="padding:10px 0 10px 20px">
        <table>
<?php
if(isset($data) && count($data)>0) {
    // 显示数组
    $i = 0;
    foreach($data as $key => $val) {
        if($i==0){
            echo '<tr>';
            foreach($val as $k => $v) {
                echo '<th>'. $k .'</th>';
            }
            echo '</tr>';
            echo '<tr>';
            foreach($val as $k => $v) {
                echo '<th>'. $v .'</th>';
            }
            echo '</tr>';
        }else{
            echo '<tr>';
            foreach($val as $k => $v) {
                echo '<th>'. $v .'</th>';
            }
            echo '</tr>';
        }
        $i++;
        //var_dump('下标是'.$key.' 值是'.$val);
    }
}
?>
        </table>
    </div>