<?php
/**
 * ThinkSNS API接口抽象类
 * @author lenghaoran
 * @version TS3.0
 */
abstract class Api {
    
    var $mid; //当前登陆的用户ID
    var $since_id;
    var $max_id;
    var $page;
    var $count;
    var $user_id;
    var $user_name;
    var $id;
    var $data;
    
    private $_module_white_list = null; // 白名单模块

    /**
     * 架构函数
     * @param boolean $location 是否本机调用，本机调用不需要认证
     * @return void
     */
    public function __construct($location=false) {
        $this->_module_white_list = array('Oauth', 'Sitelist');
        //$this->mid = $_SESSION['mid'];
        //外部接口调用
        if ($location == false) {
            if (!$this->mid && !in_array(MODULE_NAME, $this->_module_white_list)){              
                $this->verifyUser();
            }
        //本机调用
        } else {
            $this->mid = $_SESSION['mid'];
        }
        
        $GLOBALS['ts']['mid'] = $this->mid;
        
        //默认参数处理
        $this->since_id   = $_REQUEST['since_id']   ? intval($_REQUEST['since_id']) : '';
        $this->max_id     = $_REQUEST['max_id']     ? intval($_REQUEST['max_id'])   : '';
        $this->page       = $_REQUEST['page']       ? intval($_REQUEST['page'])     : 1;
        $this->count      = $_REQUEST['count']      ? intval($_REQUEST['count'])    : 20;
        $this->user_id    = $_REQUEST['user_id']    ? intval($_REQUEST['user_id'])  : 0;
        $this->user_name  = $_REQUEST['user_name']  ? h($_REQUEST['user_name'])     : '';
        $this->id         = $_REQUEST['id']         ? intval($_REQUEST['id'])       : 0;
        $this->data       = $_REQUEST;

        // findPage
        $_REQUEST[C('VAR_PAGE')] = $this->page;

        //接口初始化钩子
        Addons::hook('core_filter_init_api');
        
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

    /**
     * 用户身份认证
     * @return void
     */
    private function verifyUser() {
        $verifycode['oauth_token'] = h($_REQUEST['oauth_token']);
        $verifycode['oauth_token_secret'] = h($_REQUEST['oauth_token_secret']);
        $verifycode['type'] = 'location';
        if($login = D('Login')->where($verifycode)->field('uid,oauth_token,oauth_token_secret')->find() ){
            $this->mid = $login['uid'];
            $_SESSION['mid'] = $this->mid;
        }else{
            $this->verifyError();
        }
    }

    /**
     * 输出API认证失败信息
     * @return  object|json
     */
    protected function verifyError() {
        $message['message'] = '认证失败';
        $message['code']    = '00001';
        exit( json_encode( $message ) );
    }

    /**
     * 通过api方法调用API时的赋值
     * api('WeiboStatuses')->data($data)->public_timeline();
     * @param array $data 方法调用时的参数
     * @return void
     */
    public function data($data){
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }
        $this->since_id   = $data['since_id']   ? intval( $data['since_id'] ) : '';
        $this->max_id     = $data['max_id']     ? intval( $data['max_id'] )   : '';
        $this->page       = $data['page']       ? intval( $data['page'] )     : 1;
        $this->count      = $data['count']      ? intval( $data['count'] )    : 20;
        $this->user_id    = $data['user_id']    ? intval( $data['user_id'])   : $this->mid;
        $this->user_name  = $data['user_name']  ? h( $data['user_name'])      : '';
        $this->id         = $data['id']         ? intval( $data['id'])        : 0;
        $this->data = $data;
        return $this;
    }
}
?>