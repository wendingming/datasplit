<?php

/**
 *  登陆页
 * @file   Login.php  
 * @date   2016-8-23 19:52:46 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

use think\Controller;
use think\Loader;
use think\Cache;

class Login extends Controller {

    /**
     * 登入
     */
    public function index() {
        //dump(request()->ip());exit;


        if (isset($_POST['dosubmit'])) {
            $username = input('post.username');
            $password = input('post.password');

            if (!$username) {
                $this->error('用户名不能为空');
            }
            if (!$password) {
                $this->error('密码不能为空');
            }

            $info = db('admin')->field('id,username,password')->where('username', $username)->find();

            if (!$info) {
                $this->error('用户不存在');
            }

            if (md5($password) != $info['password']) {
                $this->error('密码不正确');
            } else {
                session('user_name', $info['username']);
                session('user_id', $info['id']);
                if (input('post.islogin')) {
                    cookie('user_name', encry_code($info['username']));
                    cookie('user_id', encry_code($info['id']));
                }

                //记录登录信息
                Loader::model('Admin')->editInfo(1, $info['id']);
                $this->success('登入成功', 'index/index');
            }
        } else {
            if (session('user_name')) {
                $this->success('您已登入', 'index/index');
            }

            if (cookie('user_name')) {
                $username = encry_code(cookie('user_name'),'DECODE');
                $info = db('admin')->field('id,username,password')->where('username', $username)->find();
                if ($info) {
                    //记录
                    session('user_name', $info['username']);
                    session('user_id', $info['id']);
                    Loader::model('Admin')->editInfo(1, $info['id']);
                    $this->success('登入成功', 'index/index');
                }
            }

            $this->view->engine->layout(false);
            return $this->fetch('login');
        }
    }

    /**
     * 登出
     */
    public function logout() {
        session('user_name', null);
        session('user_id', null);
        cookie('user_name', null);
        cookie('user_id', null);
        $this->success('退出成功', 'login/index');
    }

    /**
     * 清除缓存文件夹runtime
     * @param LogAdmin $logAdmin
     * @return mixed
     */
    /*public function clear() {
        if (delete_dir_file(CACHE_PATH) && delete_dir_file(TEMP_PATH)) {
            //$logAdmin->writeLog('清除缓存数据');
            return json_encode(2);
        } else {
            return json_encode(1);
        }
    }*/
    public function clearCache(){
        Cache::clear();
        array_map( 'unlink', glob( TEMP_PATH.DS.'.php' ) );
        /*$path = glob( LOG_PATH.'/' );
        foreach ($path as $item) {
            array_map( 'unlink', glob( $item.DS.'.' ) );
            rmdir( $item );
        }*/
        array_map('unlink', glob(TEMP_PATH . '/*.php'));
        if(Cache(NUll)){
            $this->success('缓存清除成功！');
        }else{
            $this->error('缓存清除失败！');
        }
    }

    /**
     * 清除模版缓存 不删除cache目录
     */
    public function clear_sys_cache() {
        Cache::clear();
        $this->success( '清除成功', 'index/index' );
    }
    /**
     * 清除模版缓存 不删除 temp目录
     */
    public function clear_temp_ahce() {
        array_map( 'unlink', glob( TEMP_PATH.DS.'.php' ) );
        $this->success( '清除成功', 'index/index' );
    }
    /**
     * 清除日志缓存 不删出log目录
     */
    public function clear_log_chache() {
        $path = glob( LOG_PATH.'/' );
        foreach ($path as $item) {
            array_map( 'unlink', glob( $item.DS.'.' ) );
            rmdir( $item );
        }
        $this->success( '清除成功', 'index/index' );
    }
    /**
     * 清除glob
     */
    function clert_temp_cache()
    {
        array_map('unlink', glob(TEMP_PATH . '/*.php'));
        rmdir(TEMP_PATH);
    }
}
