<?php
// +----------------------------------------------------------------------
// |动态验证接口
// +----------------------------------------------------------------------
// | Author: liupan
// +----------------------------------------------------------------------

namespace I\Controller;

use Think\Controller;
use ORG\ThinkSDK\ThinkOauth;

/**
 * 动态逻辑处理控制器
 */
class ApiController extends Controller
{
    protected function _initialize()
    {
        $config = api('Config/lists');
        C($config); //添加配置
    }

    public function __construct()
    {
        parent::__construct();

        //操作成功与失败模板定义
        $this->error_page = 'common/operate_error';
        $this->success_page = 'common/operate_success';
    }

    public function jsLogin()
    {
        $username = user_is_login() ? session("username") : "游客";
        $tpl = "<div class=\"main-r-header\">"
            . "<a class=\"head-pic cfl\"><img src=\"" . C('I_URL') . "/Public/Home/gfw/images/Loginpic.gif\" width=\"50\" height=\"50\" /></a>"
            . "<div class=\"cfl\">"
            . "<span class=\"hello\">Hi," . $username . "</span> <span class=\"last-login\">欢迎来到官方网！</span>"
            . "</div></div>" . "";
        echo "document.writeln('" . $tpl . "')";
    }

    public function headLogin()
    {
        $words = "<li class=\"login\"><a href=\"i.php?a=login\">登录</a></li>"
            . "<li><a href=\"i.php?a=register\">免费注册</a></li>"
            . "";
        $tpl = user_is_login() ? "<li>欢迎您:" . session("username") . "</li>" : $words;
        echo "document.writeln('" . $tpl . "')";
    }

    //判断是否登录
    public function judgeLogin()
    {
        $callback = I('callback');
        $tpl['status'] = user_is_login() ? "1" : "0";
        $tpl['user'] = session("username");
        $tpl['avatar'] = getAvatar(session("uid"));
        echo $callback ? $callback . '(' . json_encode($tpl) . ');' : json_encode($tpl);
    }


    //验证邮件,适合注册表单验证
    public function validateMail()
    {
        $mail = remove_xss(I('username'));
        $user = M('Member')->where(array("username" => $mail))->find();
        echo empty($user) ? "true" : "false";
    }

    //验证邮件,适合找回密码表单验证
    public function validateMail2()
    {
        $mail = remove_xss(I('username'));
        $user = M('Member')->where(array("username" => $mail))->find();
        echo empty($user) ? "false" : "true";
    }


    //验证邮件
    public function checkMailExist()
    {
        $mail = remove_xss(I('email'));
        if (A('I/User', 'Api')->checkEmail($mail) == "true") {
            echo "true";
        } else {
            echo "false";
        }
    }

    //验证邮件是否存在
    public function checkMail()
    {
        $mail = remove_xss(I('username'));
        echo A('I/User', 'Api')->checkEmailExist($mail);
    }

    //验证码
    public function captcha()
    {
        $config = array(
            'fontSize' => 30,    // 验证码字体大小
            'length' => 4,     // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }

    //验证验证码
    public function checkCaptcha()
    {
        $code = I('captcha');
        $verify = new \Think\Verify();
        if ($verify->check($code, $id)) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    //提交注册
    public function submitReg()
    {
        $username = remove_xss(I('username'));
        $password = remove_xss(I('password'));
        $token = remove_xss(I('__token__'));
        if ($token != cookie("reg_token")) {
            $this->error('请勿非法提交...');
        }
        $res = A('I/User', 'Api')->register($username, $password, $username, '');
        if ($res == '0') {
            $this->error('(⊙o⊙)…注册失败,请稍后再试');
        } else {
            $uid = A('I/User', 'Api')->login($username, $password);
            cookie('reg_user', $username);
            cookie('reg_uid', $uid);
            // $token=md5(rand(1,30).UC_AUTH_KEY);//产生Token
            // $url=C('site_url').U('Api/verifyMail')."&token=".$token."&user=".$username;
            //    R('Addons://Email/Email/sendRegister',array($username,$url));
            $this->redirect('Api/regSucc');

        }

    }

    public function regSucc()
    {
        $username = cookie('reg_user');
        $uid = cookie('reg_uid');

        $emailUrl = "http://" . gotoMail($username);


        session("uid", $uid);
        session("username", $username);
        session("i_auth", md5($username . UC_AUTH_KEY));

        $this->assign("email", $username);
        $this->assign("emailUrl", $emailUrl);
        $this->display('Index/regSuccess');
    }

    //会员登录
    public function submitLogin()
    {
        $username = remove_xss(I('username'));
        $password = remove_xss(I('password'));
        $res = A('I/User', 'Api')->login($username, $password);
        if ($res == '-1') {
            $this->error("登录失败,用户名不存在或被禁用");
        } else {
            if ($res == '-2') {
                $this->error("登录失败,密码错误");
            } else {
                $this->success('登陆成功');
                session("uid", $res);
                session("username", $username);
                session("i_auth", md5($username . UC_AUTH_KEY));
                $this->redirect('Ucenter/index');
            }
        }
    }

    //退出登录
    public function logout()
    {
        session("uid", null);
        session("username", null);
        session("i_auth", null);
        $this->success('退出登录成功...', U('Index/login'));
    }

    //第三方登录
    public function api_login($type = null)
    {
        empty($type) && $this->error('参数错误');
        import("ORG.ThinkSDK.ThinkOauth");
        $sns = \ThinkOauth::getInstance($type);
        redirect($sns->getRequestCodeURL());////跳转到授权页面
    }


    //登录成功，获取新浪微博用户信息
    public function sina($token)
    {
        $sina = ThinkOauth::getInstance('sina', $token);
        $data = $sina->call('users/show', "uid={$sina->openid()}");

        if ($data['error_code'] == 0) {
            $userInfo['type'] = 'SINA';
            $userInfo['name'] = $data['name'];
            $userInfo['nick'] = $data['screen_name'];
            $userInfo['head'] = $data['avatar_large'];
            return $userInfo;
        } else {

        }
    }

    //授权回调地址
    public function callback($type = null, $code = null)
    {
        (empty($type) || empty($code)) && $this->error('参数错误');
        import("ORG.ThinkSDK.ThinkOauth");//加载ThinkOauth类并实例化一个对象
        $sns = \ThinkOauth::getInstance($type);
        $token = $sns->getAccessToken($code, '');
        $tokenID = $token['openid'];
        $user_info = $sns->call('user/get_user_info');
        $nickname = empty($user_info['nickname']) ? 'QQ用户' : $user_info['nickname'];
        $this->assign('nickname', $nickname);//显示用户昵称
        $field = $type . "_token";
        $Info = M('Member')->where("$field='$tokenID'")->find();
        cookie("bind_type", $type);
        cookie("bind_token", $tokenID);
        if ($Info) {
            session("uid", $Info['uid']);
            session("username", $Info['username']);
            session("i_auth", md5($Info['username'] . UC_AUTH_KEY));
            $this->success('登陆成功', U('Ucenter/index'), 3);
        } else {
            $this->assign('type', $type);
            $this->assign('token', $tokenID);
            $this->display('Index/bindRegister');
        }

        //	exit();


        //	$info   = \ThinkOauth::getInstance($type, $token);
//		if($type=='qq'){
//			$data = $info->call('user/get_user_info');
//		}else if($type=='sina'){
//			$data = $sina->call('users/show', "uid={$info->openid()}");
//		}else{
//			throw_exception("获取用户信息失败");
//		}
        //判断


    }


    //绑定第三方登录
    public function bindLogin()
    {
        $type = I('type');
        $token = I('token');
        $username = remove_xss(I('username'));
        $password = remove_xss(I('password'));
        $data['type'] = $type;
        $data['token'] = $token;
        $info = A('I/User', 'Api')->updateUserToken($username, $password, $data);

        if ($info['status']) {
            $this->success('恭喜,绑定登陆成功！', U('Index/login'), 3);
        } else {
            $this->error($info['info']);
        }


    }

    //绑定第三方注册
    public function bindReg()
    {
        $username = remove_xss(I('username'));
        $password = remove_xss(I('password'));
        $type = I('type');
        $token = I('token');
        $res = A('I/User', 'Api')->register($username, $password, $username, '');
        $data['type'] = $type;
        $data['token'] = $token;
        $info = A('I/User', 'Api')->updateUserToken($username, $password, $data);
        if ($info['status']) {
            $uid = A('I/User', 'Api')->login($username, $password);
            cookie('reg_user', $username);
            cookie('reg_uid', $uid);
            $this->success('恭喜,绑定成功！', U('Index/login'), 3);
        } else {
            $this->error('(⊙o⊙)…注册失败,请稍后再试');
        }

    }

    //第一步验证基本信息,发送找回密码邮件
    public function step1()
    {
        $username = remove_xss(I('username'));
        $token = md5(rand(1, 30) . UC_AUTH_KEY);//产生Token
        $url = C('site_url') . U('Api/resetPass') . "&token=" . $token . "&user=" . $username;
        $data['token'] = $token;
        $i = M('Member')->where("username='$username'")->save($data);//修改Token
        if (!empty($username) && $i) {//发送邮件
            R('Addons://Email/Email/renPassword', array($username, $url));
        } else {
            $this->error("邮箱地址不能为空！");
        }
        session('find_username', $username);
        $this->redirect('Api/step2');
    }

    //第二步骤,展示邮件登陆地址
    public function step2()
    {
        $username = session('find_username');
        $emailUrl = "http://" . gotoMail($username);
        $this->assign("email", $username);
        $this->assign("emailUrl", $emailUrl);
        $SEO['title'] = "官方网找回密码第二步";
        $SEO['keywords'] = "官方网找回密码第二步";
        $SEO['description'] = "官方网找回密码第二步";
        $this->assign("SEO", $SEO);
        $this->display('Index/findpass2');

    }

    /*
     * 重置密码
     *
     */
    public function resetPass()
    {
        $user = I('user');
        $token = I('token');
        if (empty($user)) {
            $this->error("参数错误！");
            return;
        }
        $UC = M('UcenterMember')->where("username='$user'")->find();
        $this->assign("uid", $UC['id']);
        $this->assign("old_pass", $UC['password']);
        $rightToken = M('Member')->where("username='$user'")->getField('token');
        if ($token == $rightToken && !empty($token)) {
            $SEO['title'] = "重置密码";
            $SEO['keywords'] = "重置密码";
            $SEO['description'] = "重置密码";
            $this->assign("SEO", $SEO);
            $this->display('Index/findpass3');
        } else {
            $this->error("该验证地址已失效,请重试...");
        }
    }

    /*
     * 重置密码
     *
     */
    public function resetPassWord()
    {
        $user = remove_xss(I('username'));
        $newPass = remove_xss(I('password'));
        $uid = remove_xss(I('uid'));
        $password = remove_xss(I('oldPass'));
        $data['password'] = think_ucenter_md5($newPass, UC_AUTH_KEY);
        $info = A('I/User', 'Api')->updateInfo($uid, $password, $data, true);
        if ($info['status']) {//密码修改成功
            $this->redirect('Api/step4');
        } else {
            $this->error("密码修改失败,请联系管理员！");
        }
    }


    public function step4()
    {
        $SEO['title'] = "恭喜,修改成功";
        $SEO['keywords'] = "恭喜,修改成功";
        $SEO['description'] = "恭喜,修改成功";
        $this->assign("SEO", $SEO);
        $this->display('Index/findpass4');
    }

    /*
     * 修改密码
     *
     */
    public function editPassWord()
    {
        $uid = remove_xss(I('uid'));
        $newPass = remove_xss(I('password'));
        $password = remove_xss(I('oldPwd'));
        $data['password'] = think_ucenter_md5($newPass, UC_AUTH_KEY);
        $info = A('I/User', 'Api')->updateInfo($uid, $password, $data, false);
        if ($info['status']) {
            $this->success('恭喜,密码修改成功！');
        } else {
            $this->error($info['info']);
        }
    }

    /*
     * 修改资料
     *
     */
    public function updateInfo()
    {
        $uid = remove_xss(I('uid'));
        $nickname = remove_xss(I('nick'));
        $contact = remove_xss(I('contact'));
        $telephone = remove_xss(I('telephone'));
        $email = remove_xss(I('email'));
        $company = remove_xss(I('company'));
        $address = remove_xss(I('address'));
        $data['nickname'] = $nickname;
        $data['contact'] = $contact;
        $data['telephone'] = $telephone;
        $data['email'] = $email;
        $data['company'] = $company;
        $data['address'] = $address;
        $info = A('I/User', 'Api')->updateUserInfo($uid, $data, false);
        if ($info['status']) {
            $rand = rand(100, 99999);
            $this->success('恭喜,资料修改成功！', U('Ucenter/updateInfo') . "?rand=" . $rand);
            // echo "<script>alert('恭喜,资料修改成功！');window.location.reload();window.location = '" . U('Ucenter/updateInfo') . "?rand=$rand';</script>";
        } else {
            $this->error($info['info']);
        }
    }

    /*
     * 校验网站名
     *
     */
    public function validateSiteName()
    {
        $sitename = remove_xss(I('title'));
        $site = M('Package')->where(array('title' => $sitename))->find();
        if ($site) {
            echo "false";
        } else {
            echo "true";
        }

    }

    public function uploadAvatar()
    {
        if (!empty($_FILES)) {
            //图片上传设置
            $fileName = date('YmdHis', time()) . rand(100, 999);
            $config = array(
                'maxSize' => 3145728,
                'savePath' => '',
                'rootPath' => './Uploads/Avatar/', // 设置附件上传根目录
                'saveName' => $fileName,
                'exts' => array('jpg', 'gif', 'png', 'jpeg'),
                'autoSub' => true,
                'subName' => '',
            );
            $thumb = new \Think\Image();
            $upload = new \Think\Upload($config);// 实例化上传类
            $images = $upload->upload();
            //判断是否有图
            if ($images) {
                $file = "/Uploads/Avatar/" . $images['Filedata']['savepath'] . $images['Filedata']['savename'];
                $thumbFilePath = "." . $file;
                $thumbSavePath = "./Uploads/Avatar/" . date('YmdHis', time()) . rand(100, 999) . ".jpg";
                $thumb->open($thumbFilePath);
                $thumb->thumb(354, 244)->save($thumbSavePath);//按照原图的比例生成一个缩略图
                @unlink($thumbFilePath);
                echo substr($thumbSavePath, 1, strlen($thumbSavePath) - 1);


            } else {
                $this->error($upload->getError());//获取失败信息
            }

        }
    }


    public function cropAvatar()
    {
        $x = I('x');
        $y = I('y');
        $w = I('w');
        $h = I('h');
        if (empty(I('path')) || empty($w)) {
            $this->error('您未上传图片！');
            return;
        }
        $username = session("username");
        $fileName = date('YmdHis', time()) . rand(100, 999);
        $imgPath = I('path');


        $image = new \Think\Image();
        $real_path = "./Uploads/Avatar/" . $fileName . ".jpg";
        $image->open($imgPath)->crop($w, $h, $x, $y)->save($real_path);//裁剪原图得到选中区

        $picData['path'] = substr($real_path, 1, strlen($real_path) - 1);
        $picData['url'] = '';
        $picData['md5'] = '';
        $picData['sha1'] = '';
        $picData['status'] = '1';
        $picData['create_time'] = time();
        $picData['old'] = '0';
        $pic = M('Picture')->add($picData);
        $data['head_pic'] = $pic;
        $i = M('Member')->where("username='$username'")->save($data);//修改头像
        @unlink($imgPath); //删除原图
        $this->success('头像修改成功');

    }
}
