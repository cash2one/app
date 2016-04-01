<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------



//判断用户是否登录
function user_is_login()
{
	$uid = (int)session("uid");
	return $uid ?
		array(
			'uid' => session("uid"),
			'username' => session("username"),
			'i_auth' => session("i_auth"),
		)
		:
		false;
}
//获取用户头像
function getAvatar($uid){
	$uid = (int)$uid;
	$user=M('Member')->where("uid='$uid'")->getField('head_pic');
	$config = api('Config/lists');
	C($config); //添加配置
	if(!empty($user)){
		$avatar=M('Picture')->where("id='$user'")->getField('path');
	}
	
	$pic=empty($avatar) ? "/Public/Home/gfw/images/head.jpg" : $avatar;
	$picUrl= C('I_URL').$pic;
	return $picUrl;
}

//产生Token
function generateToken(){
	$token=md5(date("YmdHis",time()).rand(10,10000));
	cookie("reg_token",$token);
	return $token;
}

function check_cur(array $string)
{
	$status = false;
	$str = strtolower(str_replace('/i.php/', '', __ACTION__));

	foreach ($string as $v)
		(strtolower($v) == $str) && $status = true;

	return $status ? 'class="cur"' : '';
}