<?php
// +----------------------------------------------------------------------
// | 描述:JF采集器功能,主要用于用户登录判断和跳转以及登录栏目获取
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-22 上午10:12    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;


use Think\Controller;

class CollectorController extends Controller{
    /**
     * 后台控制器初始化
     */
    protected function is_login($url,$siteid){
        // 获取当前用户ID
        if(defined('UID')) return ;
        define('UID',is_login());
        if( !UID ){// 还没登录 跳转到登录页面
            //$url = base64_encode($url);
            $this->redirect('Public/login/&redirecturl='.$url.'&siteid='.$siteid);
        }
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        /*
        // 是否是超级管理员
        define('IS_ROOT',   is_administrator());
        if(!IS_ROOT && C('ADMIN_ALLOW_IP')){
            // 检查IP地址访问
            if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))){
                $this->error('403:禁止访问');
            }
        }
        // 检测访问权限
        $access =   $this->accessControl();
        if ( $access === false ) {
            $this->error('403:禁止访问');
        }elseif( $access === null ){
            $dynamic        =   $this->checkDynamic();//检测分类栏目有关的各项动态权限
            if( $dynamic === null ){
                //检测非动态权限
                $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
                if ( !$this->checkRule($rule,array('in','1,2')) ){
                    $this->error('未授权访问!');
                }
            }elseif( $dynamic === false ){
                $this->error('未授权访问!');
            }
        }
        */
        if(method_exists($this, 'initialize')){
            $this->initialize();
        }
    }

    /**
     * 描述：跳转到选择栏目页面
     * @param $url
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function selectCate($url,$siteid)
    {
        //这个地方判断url是否有效，状态是否为200,应该加个check函数
        $siteid = str_replace('.'.C('URL_HTML_SUFFIX'),'',$siteid);
        if($url&&$siteid)
        {
            $this->is_login($url,$siteid);
            session('SESSION_REDIRECTURL',$url);
            session('SESSION_SITEID',$siteid);
            $session_name = $this->getSessionName($siteid);
            if(S($session_name))
            {
                $value = S($session_name);
                $model_id = $value['model_id'];
                $cate_id = $value['cate_id'];
                $model_name = $this->getModel($model_id);
                $method = 'add';
                $redirecturl = $model_name.'/'.$method.'/cate_id/'.$cate_id.'/model_id/'.$model_id;
                $this->redirect($redirecturl);

            }
            else
            {
                $this->assign('url',$url);
                $this->assign('siteid',$siteid);
                $this->display('cate'); //跳转到选择分类页面
            }
        }
    }

    /**
     * 描述：获取session名字
     * @param $siteid
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    function getSessionName($siteid)
    {
       return  "SESSION_CATE_".$siteid;
    }

    /**
     * 描述：插入采集数据
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function insert()
    {
        $this->is_login(session('SESSION_REDIRECTURL'), session('SESSION_SITEID'));
        $model_name = I('model_name');
        $cate_id = I('cate_id');
        if($model_name && $cate_id)
        {
            $siteid = I('site_id');
            $is_session = I('is_session');
            $session_time = I('session_time');
            if($model_name == 'document')
            {
                $where['id'] = $cate_id;
                $model_id = M('category')->where($where)->field('model')->find();
                $model_id = $model_id['model'];
            }
            else
            {
                $where['id'] = $cate_id;
                $model_id = M($model_name.'_category')->where($where)->field('model')->find();
                $model_id = $model_id['model'];
            }
            if($is_session)
            {
                $session_name = $this->getSessionName($siteid);
                $value = array();
                $value['model_id'] = $model_id;
                $value['cate_id'] = $cate_id;
                S($session_name,$value,$session_time);
            }
            $model_name = ucfirst($model_name);
            $method = 'add';
            $url = $model_name.'/'.$method.'/cate_id/'.$cate_id.'/model_id/'.$model_id;
            $this->redirect($url);
        }
        $this->error('数据错误！');
    }
    /**
     * 描述：获取模块名称
     * @param $model_id
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    function getModel($model_id)
    {
        $where['id'] = $model_id;
        $rs = M('model')->field('name,extend')->where($where)->find();
        if($rs['extend'] == '0')
        {
            $model = $rs['name'];
        }
        else
        {
            $where['id'] = $rs['extend'];
            $rs =  M('model')->field('name')->where($where)->find();
            $model = $rs['name'];
        }
        return ucfirst($model);
    }

    /**
     * 权限检测
     * @param string  $rule    检测的规则
     * @param string  $mode    check模式
     * @return boolean
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function checkRule($rule, $type=AuthRuleModel::RULE_URL, $mode='url'){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        static $Auth    =   null;
        if (!$Auth) {
            $Auth       =   new \Think\Auth();
        }
        if(!$Auth->check($rule,UID,$type,$mode)){
            return false;
        }
        return true;
    }

    /**
     * 检测是否是需要动态判断的权限
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return null;//不明,需checkRule
    }


    /**
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     *
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问,无需执行节点权限检测
     *   返回 **null**, 需要继续执行节点权限检测决定是否允许访问
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
    final protected function accessControl(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        $allow = C('ALLOW_VISIT');
        $deny  = C('DENY_VISIT');
        $check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);
        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }
} 