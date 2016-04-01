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
use User\Model\UcenterMemberModel;
use Admin\Model\MemberModel;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class UserController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $nickname       =   I('nickname');
        $map['status']  =   array('egt',0);
        $map['type'] = array('eq',1);
        if(is_numeric($nickname)){
            $map['uid|nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
        }

        $list   = $this->lists('Member', $map);
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display();
    }

    /**
     * 描述：前台用户管理（逻辑写在控制器里面，需要改变）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function frontIndex()
    {
        $nickname       =   I('nickname');
        $map['a.status']  =   array('egt',0);
        $map['b.type'] = array('eq',2);
        $p=$_GET['p']?$_GET['p']:'1';
        $page   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;

        if(is_numeric($nickname)){
            $map['b.uid|b.nickname']=   array(intval($nickname),array('like','%'.$nickname.'%'),'_multi'=>true);
        }else{
            $map['b.nickname']    =   array('like', '%'.(string)$nickname.'%');
        }
        $list   =  M('UcenterMember')->alias('a')->join('__MEMBER__ b on a.id = b.uid')->field('a.status as status,a.mobile as mobile,a.email as email,a.username as username,a.last_login_time as last_login_time,a.last_login_ip as last_login_ip,b.uid as uid,b.company_name as company_name,b.nickname as nickname,b.contacts as contacts,b.score as score,b.login as login')->where($map)->page($p.','.$page)->select();
        $count=M('UcenterMember')->alias('a')->join('__MEMBER__ b on a.id = b.uid')->where($map)->count();// 查询满足要求的总记录数
        int_to_string($list);
        $Page = new \Think\Page($count,$page);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->parameter['nickname']   =   I('nickname');  //用于分页带参数
        $pagination = $Page->show();// 分页显示输出
        $this->assign('_page',$pagination);
        $this->assign('_list', $list);
        $this->meta_title = '前台用户信息';
        $this->display();
    }

    /**
     * 描述：修改用户信息
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function editFrontUser()
    {
        if(IS_POST){
            if(empty($_POST['password'])) unset($_POST['password']);
            $ucenterMemberMoble = new UcenterMemberModel();
            $memberMoble = new MemberModel();
            // 获取模型的字段信息
            if(!$ucenterMemberMoble->create())
                $this->error($this->showRegError($ucenterMemberMoble->getError()));
            if(!$memberMoble->create())
                $this->error($memberMoble->getError());

            if(I('id') && I('uid')){
                $flag = $ucenterMemberMoble->save();
                $f = $memberMoble->save();
                if($flag !== false &&  $f !== false)
                    $this->success("编辑成功！", U('User/frontIndex'));
                else
                {
                    echo $ucenterMemberMoble->getError();
                    echo $this->showRegError($ucenterMemberMoble->getError());
                    $this->error($this->showRegError($ucenterMemberMoble->getError()).'<br/>'.$memberMoble->getError());
                }

            }
        } else {
            if(is_numeric(I('id')))
            {
                $map[id] = I('id');
                $rs   =  M('UcenterMember')->alias('a')->join('__MEMBER__ b on a.id = b.uid')->field('a.mobile as mobile,a.email as email,a.username as username,a.id as id,b.uid as uid,b.company_name as company_name,b.nickname as nickname,b.contacts as contacts,b.company_address as company_address')->where($map)->find();
                $this->assign('rs',$rs);
                $this->display('edit');
            }
            else
            {
                $this->error("用户不存在",  U('User/frontIndex'));
            }

        }
    }


    /**
     * 修改昵称初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updateNickname(){
        $nickname = M('Member')->getFieldByUid(UID, 'nickname');
        $this->assign('nickname', $nickname);
        $this->meta_title = '修改昵称';
        $this->display('updatenickname');
    }

    /**
     * 修改昵称提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitNickname(){
        //获取参数
        $nickname = I('post.nickname');
        $password = I('post.password');
        empty($nickname) && $this->error('请输入昵称');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data   =   $Member->create(array('nickname'=>$nickname));
        if(!$data){
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改昵称成功！');
        }else{
            $this->error('修改昵称失败！');
        }
    }

    /**
     * 修改昵称初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updateUsername(){
        $username = M('Member')->getFieldByUid(UID, 'username');
        $this->assign('username', $username);
        $this->meta_title = '修改姓名';
        $this->display('updateusername');
    }

    /**
     * 修改昵称提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitUsername(){
        //获取参数
        $username = I('post.username');
        $password = I('post.password');
        empty($username) && $this->error('请输入姓名');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data   =   $Member->create(array('username'=>$username));
        if(!$data){
            $this->error($Member->getError());
        }

        $res = $Member->where(array('uid'=>$uid))->save($data);

        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['username'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改姓名成功！');
        }else{
            $this->error('修改姓名失败！');
        }
    }

    /**
     * 修改密码初始化
     * @author huajie <banhuajie@163.com>
     */
    public function updatePassword(){
        $this->meta_title = '修改密码';
        $this->display('updatepassword');
    }

    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function submitPassword(){
        //获取参数
        $password   =   I('post.old');
        empty($password) && $this->error('请输入原密码');
        $data['password'] = I('post.password');
        empty($data['password']) && $this->error('请输入新密码');
        $repassword = I('post.repassword');
        empty($repassword) && $this->error('请输入确认密码');

        if($data['password'] !== $repassword){
            $this->error('您输入的新密码与确认密码不一致');
        }

        $Api    =   new UserApi();
        $res    =   $Api->updateInfo(UID, $password, $data);
        if($res['status']){
            $this->success('修改密码成功！');
        }else{
            $this->error($this->showRegError($res['info']));
        }
    }

    /**
     * 描述：修改头像
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function updateHeadPic(){
        $this->meta_title = '修改头像';
        $icon = M('Member')->getFieldByUid(UID, 'icon');
        $this->assign('icon', $icon);
        $this->display('updateheadpic');
    }

    /**
     * 描述：修改头像提交
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function submitHeadPic(){
        //获取参数
        $icon = I('post.icon');
        $password = I('post.password');
        empty($icon) && $this->error('请上传头像');
        empty($password) && $this->error('请输入密码');

        //密码验证
        $User   =   new UserApi();
        $uid    =   $User->login(UID, $password, 4);
        ($uid == -2) && $this->error('密码不正确');

        $Member =   D('Member');
        $data['icon'] = $icon;  //图片id
        $res = $Member->where(array('uid'=>$uid))->save($data);
        if($res){
            $user               =   session('user_auth');
            $user['username']   =   $data['nickname'];
            session('user_auth', $user);
            session('user_auth_sign', data_auth_sign($user));
            $this->success('修改头像成功！');
        }else{
            $this->error('修改头像失败！');
        }
    }
    /**
     * 用户行为列表
     * @author huajie <banhuajie@163.com>
     */
    public function action(){
        //获取列表数据
        $Action =   M('Action')->where(array('status'=>array('gt',-1)));
        $list   =   $this->lists($Action);
        int_to_string($list);
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->assign('_list', $list);
        $this->meta_title = '用户行为';
        $this->display();
    }

    /**
     * 新增行为
     * @author huajie <banhuajie@163.com>
     */
    public function addAction(){
        $this->meta_title = '新增行为';
        $this->assign('data',null);
        $this->display('editaction');
    }

    /**
     * 编辑行为
     * @author huajie <banhuajie@163.com>
     */
    public function editAction(){
        $id = I('get.id');
        empty($id) && $this->error('参数不能为空！');
        $data = M('Action')->field(true)->find($id);

        $this->assign('data',$data);
        $this->meta_title = '编辑行为';
        $this->display('editaction');
    }

    /**
     * 更新行为
     * @author huajie <banhuajie@163.com>
     */
    public function saveAction(){
        $res = D('Action')->update();
        if(!$res){
            $this->error(D('Action')->getError());
        }else{
            $this->success($res['id']?'更新成功！':'新增成功！', Cookie('__forward__'));
        }
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        $ucenter_model = M('UcenterMember');
        switch ( strtolower($method) ){
            case 'forbiduser':
                $ucenter_model->where("id in($id)")->setField('status',0);
                $this->forbid('Member', $map );
                break;
            case 'resumeuser':
                $ucenter_model->where("id in($id)")->setField('status',1);
                $this->resume('Member', $map );
                break;
            case 'deleteuser':
                $ucenter_model->where("id in($id)")->setField('status',-1);
                $this->delete('Member', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    public function add($username = '', $uname = '', $password = '', $repassword = '', $email = '',$icon = ''){
        if(IS_POST){
            /* 检测密码 */
            if($password != $repassword){
                $this->error('密码和重复密码不一致！');
            }

            /* 调用注册接口注册用户 */
            $User   =   new UserApi;
            $uid    =   $User->register($username, $password, $email);
            if(0 < $uid){ //注册成功
                $user = array('uid' => $uid, 'nickname' => $username, 'username' => $uname, 'reg_time' => time(), 'status' => 1,'icon' => $icon);
                if(!M('Member')->add($user)){
                    $this->error('用户添加失败！');
                } else {
                    $this->success('用户添加成功！',U('index'));
                }
            } else { //注册失败，显示错误信息
                $this->error($this->showRegError($uid));
            }
        } else {
            $this->meta_title = '新增用户';
            $this->display();
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }

}