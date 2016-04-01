<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi;


/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PublicController extends \Think\Controller {

    /**
     * 后台用户登录
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function login($username = null, $password = null, $verify = null){ 
        //添加后台登录IP白名单，免输入验证码 sunjianhua 2015.12.21
        $show_code = 1;
        
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config);
        
        $client_ip = get_client_ip();
        $client_ips = explode('.', $client_ip);
        $ip_list = explode(';', C('NO_CAPTCHA_IP'));
        if(is_array($ip_list) && is_array($client_ips)) {
            foreach ($ip_list as $v) {
                if (strpos($v, '*') !== false) {
                    //处理通配符
                    $ips = explode('.', $v);
                    foreach ($ips as $key => $value) {
                        if ($value === '*') {
                            $client_ips[$key] = '*';
                        }
                    }
                    
                    if ($client_ips === $ips) {
                        $show_code = 0;
                        break;
                    }
                } else {
                    if ($client_ip === $v) {
                        $show_code = 0;
                        break;
                    }
                }
            }
        }
        $this->assign('show_code',$show_code);
        
        if(IS_POST){
            
            /* 检测验证码 TODO: */
            if($show_code && !check_verify($verify)){
                $this->error('验证码输入错误！');
            }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    if(I('redirecturl')&&I('siteid'))
                    {
                        $redirecturl = I('redirecturl');
                        $siteid = I('siteid');
                        $url = 'Collector/selectCate/&url='.$redirecturl.'&siteid='.$siteid;
                        $this->success('登录成功！', U($url));
                    }
                    else
                    {
                        session('SESSION_REDIRECTURL',null);
                        session('SESSION_SITEID',null);
                        $this->success('登录成功！', U('Index/index'));
                    }

                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置
                if(I('redirecturl'))
                {
                    $this->assign('redirecturl',I('redirecturl'));
                }
                if(I('siteid'))
                {
                    $this->assign('siteid',I('siteid'));
                }
                $this->display();
            }
        }
    }


    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
            $this->success('退出成功！', U('login'));
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->useCurve = false;
        //$verify->useNoise = false;
        $verify->entry(1);
    }

}
