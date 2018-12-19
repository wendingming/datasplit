<?php

/**
 * 后台公共文件 
 * @file   Common.php  
 * @date   2016-8-24 18:28:34 
 * @author Zhenxun Du<5552123@qq.com>  
 * @version    SVN:$Id:$ 
 */

namespace app\admin\controller;

use think\Controller;

class Common extends Controller {
    // 处理成功响应码
    const RESPONSE_SUCCES = 0;
    //请求错误码
    const RESPONSE_ERROR = 1;

    protected $user_id;
    protected $user_name;

    public function __construct(\think\Request $request = null) {

        parent::__construct($request);

        if (!session('user_id')) {

            $this->error('请登陆', 'login/index', '', 0);
        }

        $this->user_id = session('user_id');
        $this->user_name = session('user_name');

        //权限检查
        if (!$this->_checkAuthor($this->user_id)) {
            $this->error('你无权限操作');
        }

        //记录日志
        $this->_addLog();
    }

    /**
     * 权限检查
     */
    private function _checkAuthor($user_id) {
        
        if (!$user_id) {
            return false;
        }
        if($user_id==1){
            return true;
        }
        $c = strtolower(request()->controller());
        $a = strtolower(request()->action());

        if (preg_match('/^public_/', $a)) {
            return true;
        }
        if ($c == 'index' && $a == 'index') {
            return true;
        }
        $menu = model('Menu')->getMyMenu($user_id);
        foreach ($menu as $k => $v) {
            if (strtolower($v['c']) == $c && strtolower($v['a']) == $a) {
                return true;
            }
        }
        return false;
    }

    /**
     * 记录日志
     */
    private function _addLog() {

        $data = array();
        $data['querystring'] = request()->query()?'?'.request()->query():'';
        $data['m'] = request()->module();
        $data['c'] = request()->controller();
        $data['a'] = request()->action();
        $data['userid'] = $this->user_id;
        $data['username'] = $this->user_name;
        $data['ip'] = ip2long(request()->ip());
	    $data['time'] = time();
        $arr = array('Index/index','Log/index','Menu/index');
        if (!in_array($data['c'].'/'.$data['a'], $arr)) {
            db('admin_log')->insert($data);
        } 
    }
    /**
     * 相应接口请求 返回json数据
     * @param $code = 0 默认成功 0
     * @param $data = array()  数据
     * @param $msg = ''  返回信息
     */
    public function response($code = self::RESPONSE_SUCCES, $msg='', $data = array(), $count=0){
        $json = array(
            'code'  =>  $code,
            'data'  =>  $data,
            'msg'   =>  $msg,
            'count' =>  $count
        );
        exit(json_encode($json ,JSON_UNESCAPED_UNICODE ));
    }

}
