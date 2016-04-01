<?php
namespace I\Model;
use Think\Model\RelationModel;
/**
 * 会员模型
 */
class UcenterMemberModel extends RelationModel{

	/* 用户模型自动验证 */
	protected $_validate = array(
		/* 验证用户名 */
		array('username', '1,30', -1, self::EXISTS_VALIDATE, 'length'), //用户名长度不合法
		array('username', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户名被占用

		/* 验证密码 */
		array('password', '6,30', -4, self::EXISTS_VALIDATE, 'length'), //密码长度不合法

		/* 验证邮箱 */
		array('email', 'email', -5, self::EXISTS_VALIDATE), //邮箱格式不正确
		array('email', '1,32', -6, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
		array('email', '', -8, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

		/* 验证手机号码 */
		array('mobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
		array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('password', 'think_ucenter_md5', self::MODEL_BOTH, 'function', UC_AUTH_KEY),
		array('reg_time', NOW_TIME, self::MODEL_INSERT),
		array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
		array('update_time', NOW_TIME),
		array('status', 'getStatus', self::MODEL_BOTH, 'callback'),
	);

    protected $_link=array(
	   'Member'=>array(
	   'mapping_type'      => self::HAS_ONE,
	   'foreign_key'   => 'uid',
	   'as_fields' => 'qq,score,type,company_name,company_address,contacts,nickname',
	   ),
	
	);


	/**
	 * 根据配置指定用户状态
	 * @return integer 用户状态
	 */
	protected function getStatus(){
		return true; //TODO: 暂不限制，下一个版本完善
	}

	/**
	 * 注册一个新用户
	 * @param  string $username 用户名
	 * @param  string $password 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */	protected $patchValidate = true;
	public function register($username, $password, $email, $mobile){
	
		$data = array(
			'username' => $username,
			'password' => think_ucenter_md5($password,UC_AUTH_KEY),
			'email'    => $email,
			'mobile'   => $mobile,
			'reg_time'   => time(),
			'reg_ip'   => get_client_ip(),
			'status'   => '1',
		  	'Member'    => array(
              'nickname'    =>'',
			  'username'    =>$username,
			  'type'    =>'2',
             )
		);
		
		//验证手机
		if(empty($data['mobile'])) unset($data['mobile']);
		/* 添加用户 */
		$uid=$this->relation(true)->add($data);
	    return $uid ? $uid : 0; //0-未知错误，大于0-注册成功
		
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function login($username, $password, $type = 1){
		$map = array();
		switch ($type) {
			case 1:
				$map['username'] = $username;
				break;
			case 2:
				$map['email'] = $username;
				break;
			case 3:
				$map['mobile'] = $username;
				break;
			case 4:
				$map['id'] = $username;
				break;
			default:
				return 0; //参数错误
		}
		/* 获取用户数据 */
		$user = $this->relation(true)->where($map)->find();
		if(is_array($user) && $user['status']&& $user['type']=="2"){
			/* 验证用户密码 */
			if(md5($password) === $user['password']){
				$this->updateLogin($user['id']); //更新用户登录信息
				$this->updatePassWord($user['id'],$password);   //此处需要临时修改用户的密码规则
				return $user['id']; //登录成功，返回用户ID
			}else if(think_ucenter_md5($password, UC_AUTH_KEY) === $user['password']){
				$this->updateLogin($user['id']); //更新用户登录信息
				return $user['id']; //登录成功，返回用户ID
			}else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 获取用户信息
	 * @param  string  $uid         用户ID或用户名
	 * @param  boolean $is_username 是否使用用户名查询
	 * @return array                用户信息
	 */
	public function info($uid, $is_username = false){
		$map = array();
		if($is_username){ //通过用户名获取
			$map['username'] = $uid;
		} else {
			$map['id'] = $uid;
		}

		$user = $this->where($map)->field('id,username,email,mobile,status')->find();
		if(is_array($user) && $user['status'] = 1){
			return array($user['id'], $user['username'], $user['email'], $user['mobile']);
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 检测用户信息
	 * @param  string  $field  用户名
	 * @param  integer $type   用户名类型 1-用户名，2-用户邮箱，3-用户电话
	 * @return integer         错误编号
	 */
	public function checkField($field, $type = 1){
		$data = array();
		switch ($type) {
			case 1:
				$data['username'] = $field;
				break;
			case 2:
				$data['email'] = $field;
				break;
			case 3:
				$data['mobile'] = $field;
				break;
			default:
				return 0; //参数错误
		}

		return $this->create($data) ? 'true' : $this->getError();
	}
	/**
	 * 检测用户是否存在
	 * @param  string  $field  用户名
	 * @return integer         错误编号
	 */
	public function checkEmailExists($field){
		$data = array();
		$data['email'] = $field;
		return $this->create($data) ? 'false' : 'true';
	}
	/**
	 * 更新用户登录信息
	 * @param  integer $uid 用户ID
	 */
	protected function updateLogin($uid){
		$data = array(
			'id'              => $uid,
			'last_login_time' => NOW_TIME,
			'last_login_ip'   => get_client_ip(1),
		);
		$this->save($data);
	}
     /**
	 * 更换旧密码
	 * @param  integer $uid 用户ID
	 */
	public function updatePassWord($uid,$password){
		$data = array(
			'id'              => $uid,
			'password' => think_ucenter_md5($password, UC_AUTH_KEY),
		);
		$this->save($data);
	}
	
	 /**
	 * 验证Token
	 * @param  integer $uid 用户ID
	 */
	public function validateToken($token,$type){
		$data = array(
			'id'              => $uid,
			'password' => think_ucenter_md5($password, UC_AUTH_KEY),
		);
		$this->save($data);
	}
	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @param string $encrypted 传递过来的密码是否已经加密
	 * @return true 修改成功，false 修改失败
	 * @author 刘盼
	 */
	public function updateUserFields($uid,$password,$data,$encrypted=true){
		
		if(empty($uid) || empty($password) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}
		//更新前检查用户密码
		if(!$this->verifyUser($uid, $password,$encrypted)){
			$this->error = '原密码不正确！';
			return false;
		}
		if($data){
			return $this->where(array('id'=>$uid))->save($data);	
		}
		return false;
	
	}
	
	 /**
	 * 更新用户资料
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @param string $encrypted 传递过来的密码是否已经加密
	 * @return true 修改成功，false 修改失败
	 * @author 刘盼
	 */
	public function updateUserData($uid,$data,$encrypted=true){
		
		if(empty($uid) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}
		//更新前检查用户密码
		if(empty(session("i_auth"))){
			$this->error = '您未登录,请先登录';
			return false;
		}
		if($data){
			$arr = array(
			'email' => $data['email'],
			'mobile' => $data['telephone'],
		  	'Member'    => array(
              'nickname'    =>$data['nickname'],
			  'contacts'    =>$data['contact'],
			  'company_name'    =>$data['company'],
			  'company_address'    =>$data['address'],
             )
		);
		 return $this->where(array('id'=>$uid))->relation(true)->save($arr);	
			
	}
		return false;
	
	}
	
	
	
	 /**
	 * 显示用户资料
	 * @param int $uid 用户id
	 * @return array
	 * @author 刘盼
	 */
	
   public function getInfo($uid){
		if(empty($uid)){
			$this->error = '参数错误！';
			return false;
		}
		//更新前检查用户密码
		if(empty(session("i_auth"))){
			$this->error = '您未登录,请先登录';
			return false;
		}
		$data['uid']=$uid;
        return $this->where(array('id'=>$uid))->relation(true)->find();	
	
	}
	
	/**
	 * 更新第三方登录Token
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @param string $encrypted 传递过来的密码是否已经加密
	 * @return true 修改成功，false 修改失败
	 * @author 刘盼
	 */
	public function updateUserToken($username,$password,$data){
		
		if(empty($username) || empty($password) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}
		//更新前检查用户密码
        $map['username']=$username;
        $user= $this->where($map)->find();
		
		
		if(think_ucenter_md5($password, UC_AUTH_KEY) != $user['password']){
			$this->error = '原密码不正确！';
			return false;
		}
		if($data){
			$arr = array(
			'status'   => '1',
		  	'Member'    => array(
			    $data['type'].'_token' => $data['token'],
                )
			 );
			
			return $this->where(array('id'=>$user['id']))->relation(true)->save($arr);	
		}
		return false;
	
	}
	/**
	 * 验证用户密码
	 * @param int $uid 用户id
	 * @param string $password_in 密码
	 * @param string $encrypt     是否是加密后的密码
	 * @return true 验证成功，false 验证失败
	 * @author 刘盼
	 */
	protected function verifyUser($uid, $password_in,$encrypt=false){
		$password = $this->getFieldById($uid, 'password');
		if($encrypt){
			return $password==$password_in ? true : false;
		}else{
			return $password==think_ucenter_md5($password_in, UC_AUTH_KEY) ? true : false;
		}
		return false;
	}





}
