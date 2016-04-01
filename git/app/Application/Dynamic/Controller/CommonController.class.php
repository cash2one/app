<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/4/24 上午10:54
 */

namespace Dynamic\Controller;


class CommonController extends BaseController
{
	/**
	 * 提交评论
	 * @return void
	 */
	public function API_commentSub(){
		$this->API_init();
		$callback = I('callback');

		$id = I('id');
		$model = I('model');
		if(!is_numeric($id) || empty($model)) return;

		$m = M('Comment');
		$data['message'] = strip_tags(I('message'));
		$data['document_id'] = intval($id);
		$data['type'] = strip_tags($model);
		$data['uid'] = (int)I('uid');
		$data['at_uid'] = (int)I('at_uid');
		$data['uname'] = strip_tags(I('uname'));
		$data['add_time'] = time();
		$data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
		$rs = $m->add($data);
		echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
	}
	
	/**
	* 获取验证码
	* @date: 2015-6-29
	* @author: liujun
	*/
	public function API_verify(){
		$verify = new \Think\Verify();
		$verify->useCurve = false;
		$verify->entry();
	}
	
	/**
	* 检测码验证
	* @date: 2015-6-29
	* @author: liujun
	*/
	public function API_checkVerify(){
		$rs = array('status'=>-1,'msg'=>'验证码输入错误！');
		$code = I('code');
		$callback = I('callback');
		
		$verify = new \Think\Verify();
		if($verify->check($code)){
			$rs['status'] = 1;
			$rs['msg'] = 'success';
		}
		echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
	}
	
	/**
	* 获取二维码
	* @date: 2015-7-1
	* @author: liujun
	*/
	public function qrCode(){
		$str = I('str');
		$callback = I('callback');
		$qrCode = builtQrcode($str);
		$rs = array('status'=>1,'qrCode'=>$qrCode);
		echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
	}
}