<?php
namespace Admin\Controller;
use Think\Controller;
use Common\PinYin;
use User\Api\UserApi;

/**
 * 官方网数据导入控制器
 * @author wwei <wangwei@163.com>
 */
class FangController extends Controller{

    /**
     * 初始化 设定数据库配置
     * @return void
     */
    public function __construct(){
        set_time_limit(1000000);
        M()->db(1, C('DB_CONFIG1'));
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
        //关闭调试
       C('SHOW_PAGE_TRACE', false);

       //设置允许内存
       ini_set("memory_limit","1024M");
    }

    /**
     * 管理公司分类(class表)对应礼包分类
     * @link    admin.php?s=/Fang/packagecategory.html
     * @return bool
     */
    public function packagecategory(){
        $data = array();
        $pys = array();
        $lists = M()->db(1)->table("class")->field('*')->select();
        foreach ($lists as $value) {
            $data['id'] =  intval($value['number']);//分类id
            $py = in_array($value['filepath'], $pys) ? '-' : '';
            $pys[] = $value['filepath'];
            $data['name'] =  $value['filepath'] . $py;//标识
            $data['title'] =  $value['title'];//标题
            $data['pid'] = intval($value['root']);//上级分类id
            //根目录处理
            if(intval($value['root']) == 0){
                $data['rootid'] = intval($value['number']);
            }else{
                $data['rootid'] = intval($value['root']);
            }
            //层级处理
            $data['depth'] =  strlen($value['number'])/3;//层级
            $data['sort'] =  $value['corder'];//排序
            $data['path_index'] =  '';//分类首页生成路劲规则
            $data['path_lists'] =  'class/'.$value['filepath'].$py.'/index_{page}';//列表生成路劲规则
            $data['path_lists_index'] =  'index';//列表首页名称
            $data['path_detail'] =  '';//内容生成路劲规则
            $data['list_row'] = 10;//列表每页行数
            $data['meta_title'] = $value['title'].'网站大全_'.$value['title'].'网站排名_'.$value['title'].'网站有哪些_官方网';//seo网页标题
            $data['keywords'] = $value['title'].','.$value['title'].'网站大全,'.$value['title'].'网站排名,'.$value['title'].'网站有哪些';//关键字
            $data['description'] = $value['title'].'网站大全,'.$value['title'].'网站排名,'.$value['title'].'网站有哪些。官方网为您找到了'.$value['title'].'网站的相关信息。查看'.$value['title'].'最新网站信息就上官方网。';//描述
            $data['template_index'] = '';//频道页模版
            $data['template_lists'] = '';//列表页模板
            $data['template_detail'] = '';//详情页模版
            $data['template_edit'] = '';//编辑页模版
            $data['model'] = 14;//列表绑定模型
            $data['model_sub'] = 14;//子文档绑定模型
            $data['type'] = 2;//允许发布的内容类型
            $data['link_id'] = '';//外链
            $data['allow_publish'] =  1;//是否允许发布内容
            $data['display'] =  1;//可见性
            $data['reply'] =  1;//是否允许回复
            $data['check'] = '';//发布的文章是否需要审核
            $data['reply_model'] = '';
            $data['extend'] =  '';//扩展设置
            $data['create_time'] =  time();//创建时间
            $data['update_time'] =  time();//更新时间
            $data['status'] =  1;//数据状态
            $data['icon'] =  '';//分类图标
            $exist = M('package_category')->where('id='.intval($value['number']))->find();
            //if(!empty($exist)){
              //  M('package_category')->save($data);
            //}else{
                M('package_category')->add($data);
            //}
        }
    }

    /**
     * 公司对应到礼包
     * @link    admin.php?s=/Fang/companypackage.html
     * @return bool
     */
    public function companypackage()
    {
        $data = array();
        $pys = array();
        $lists = M()->db(1)->table("company")->field('*')->select();
        foreach ($lists as $value) {

            $retitle = $value['retitle'];
            $title = $value['title'];

            $data['id'] =  $value['id'];//文档id
            $data['uid'] = 1;//用户ID

            $py = in_array($value['filepath'], $pys) ? '-' : '';
            $pys[] = $value['filepath'];
            $data['name'] =  $value['filepath'] . $py;//标识
            $data['title'] =  $value['title'];//网站名称
            $data['category_id'] =  intval($value['cid']);//所属分类
            $data['description'] =  $value['summary'];//网站描述
            $data['root'] =  '';//根节点
            $data['pid'] =  '';//所属id
            $data['model_id'] = 14;//内容模型id
            $data['type'] =  2;//内容类型
            $data['position'] =  '';//推荐位
            $data['link'] = '';//外链
            $data['cover_id'] =  '';//logo图
            $data['display'] = 1;//可见性
            $data['deadline'] = time();//截止时间
            $data['attach'] =  '';//附件数量
            $data['view'] =  $value['hits'];//浏览量
            $data['comment'] =  '';//评论数
            $data['extend'] =  '';//扩展统计字段
            $data['level'] = '';//优先级
            $data['create_time'] =  strtotime($value['jointime']);//创建时间
            $data['update_time'] = strtotime($value['datatime']);//更新时间
            $data['status'] =  1;//数据状态.1,正常
            $data['title_pinyin'] =  $value['letter'];//标题首字母
            $data['path_detail'] =  'website/'.$value['filepath'];//静态文件路径
            $data['seo_title'] = !empty($retitle)?$retitle.' - www.guanfang123.com官方网':'官网_'.$title.'官方网站 - www.guanfang123.com官方网';//seo标题
            $data['seo_key'] =  $title.'官网,'.$title.'官方网站,'.$title.'官方产品'.$title.'官方介绍';//seo关键字
            $data['seo_description'] =  'guanfang123官网为您整理出'.$title.'官方网站的'.$title.'官网介绍、访问地址、官方产品等内容';//seo描述
            $data['brecom_id'] =  '';//推荐大图
            $data['srecom_id'] =  '';//推荐小图
            $data['home_position'] = '';//全站推荐位
            $data['vertical_pic'] =  '';//封面竖图
            $data['abet'] =  $value['hits'];//赞
            $data['category_rootid'] =  '';//栏目根id
            $data['url'] =  $value['website'];//网站地址
            $data['user_id'] =  '';//前台用户id
            $exist = M('package')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('package')->save($data);
            }else{
                M('package')->add($data);
            }
        }
    }

    /**
     * 管理公司对应礼包内容
     * @link    admin.php?s=/Fang/packageparticle.html
     * @return bool
     */
    public function packageparticle(){
        $data = array();
        $lists = M()->db(1)->table("company") ->field('*')->select();
        foreach ($lists as $value) {
            $data['id'] =  $value['id'];//公司id
            $data['start_time'] = time();//开测时间
            $data['sevver'] =  '';//测试类型
            $data['game_type'] = '';//游戏类型
            $data['server_type'] =  '';//当前情况
            $data['conditions'] =  '';//运行环境
            $data['content'] =  $value['content'];//网站简介
            $data['province'] =  '';//省
            $data['country'] =  '';//国家
            $data['city'] =  $value['city'];//市
            $data['county'] =  '';//县/区
            $data['address'] = '';//详细地址
            $data['city_id'] =  $value['city'];//所属城市id
            $data['nature'] = '';//公司性质
            $data['contacts'] =  '';//联系人
            $data['telphone'] = '';//手机
            $data['phone'] = '';//电话号码

            $exist = M('package_particle')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('package_particle')->save($data);
            }else{
                M('package_particle')->add($data);
            }
        }
    }

    //----------------------下载相关----------------------

    /**
     * 产品对应下载分类
     * @link    /admin.php?s=/Fang/productCate.html
     * @return bool
     */
    /*public function productCate(){
        $data = array();
        $lists = M()->db(1)->table("categony")->field('*')->select();
        foreach ($lists as $value) {
            $data = array(
                'id'    =>intval($value['number']),//分类id
                'title'  =>$value['title'],//标题
                'pid'   => intval($value['root']),//根分类id
                'sort'  => $value['corder'],//排序
                'path_index'   =>'',//分类首页生成路劲规则
                'path_lists'    => 'product_class/'.$value['filepath'].'/index_{page}',//列表生成路劲规则
                'path_lists_index'   =>'index',//列表首页名称（如果填写会同时生成填写名称的第一页）
                'path_detail'  =>'',//内容生成路劲规则
                'list_row'   => 10,//列表每页行数
                'meta_title'  => $value['title'].'价格_'.$value['title'].'厂商_'.$value['title'].'批发_官方网',//seo的网页标题
                'keywords' => $value['title'].','.$value['title'].'价格,'.$value['title'].'厂商,'.$value['title'].'批发',//关键字
                'description'   => '官方网为您提供'.$value['title'].'的相关信息，包括：'.$value['title'].'价格，'.$value['title'].'批发，'.$value['title'].'厂商相关信息、品牌等内容。查看'.$value['title'].'最新信息就上官方网',//描述
                'template_index'   => '',//频道页模板
                'template_lists'  => '',//列表页模板
                'template_detail'   => '',//详情页模板
                'template_edit'    => '',//编辑页模板
                'type'   =>2,//允许发布的内容类型
                'link_id'  => '',//外链
                'allow_publish' => 2,//是否允许发布内容
                'display'   => 1,//可见性
                'reply'   => 1,//是否允许回复
                'position'  => '', //推荐位
                'check'  => '',//发布的文章是否需要审核
                'reply_model'   => '',//
                'extend'    =>'',//扩展设置
                'create_time'   =>time(),//创建时间
                'update_time'  =>time(),//更新时间
                'status'  => 1,//数据状态
                'icon'   =>'',//分类图标
                'vertical_pic'  => '',//分类竖图
            );

            //拼音处理 同名拼音随机构造为不同的
            $py = strtolower(trim(get_pinyin_first($value['filepath'], true)));
            $py  = empty($py) ? (string)rand(1,99999999) : $py;
            while (in_array($py, $pys)) {
                $py .= '-';
            }
            $pys[] = $py;
            $data['name'] =  $value['id'] . $py;//标识
            $data['depth'] =  strlen($value['number'])/3;//层级
            //之后处理产品类型
            //14001002
            //array(14003006,14003002,14003007,14006002,14006003,14002011,14002003,14008003,16003005,14001005,14001004,14005011,17002005,17002013,17002018,14007002,14001003);
            $number = intval($value['number']);
            if($number=='14001002'){
                $data['model']   = 13; //列表绑定模型
                $data['model_sub']  = 13; //子文档绑定模型
            }elseif(in_array($number, array(14003006,14003002,14003007,14006002,14006003,14002011,14002003,14008003,16003005,14001005,14001004,14005011,17002005,17002013,17002018,14007002,14001003))){
                $data['model']   = 20; //列表绑定模型
                $data['model_sub']  = 20; //子文档绑定模型
            }else{
                $data['model']   = ''; //列表绑定模型
                $data['model_sub']  = ''; //子文档绑定模型
            }

            //根目录处理
            if(intval($value['root']) == 0){
                $data['rootid'] = intval($value['number']);
            }else{
                $data['rootid'] = intval($value['root']);
            }

            $exist = M('down_category')->where('id='.intval($value['number']))->find();
            if(!empty($exist)){
                M('down_category')->save($data);
            }else{
                M('down_category')->add($data);
            }
        }
    }*/

    /**
     * 产品分类seo相关更新
     * @link    /admin.php?s=/Fang/proCateSeo.html
     */
    public function proCateSeo(){
        $data = array();
        $lists = M('down_category')->field('*')->select();
        foreach($lists as $value){
            $data = array(
                'meta_title'  => $value['title'].'价格_'.$value['title'].'厂商_'.$value['title'].'批发_官方网',//seo的网页标题
                'keywords' => $value['title'].','.$value['title'].'价格,'.$value['title'].'厂商,'.$value['title'].'批发',//关键字
                'description'   => '官方网为您提供'.$value['title'].'的相关信息，包括：'.$value['title'].'价格，'.$value['title'].'批发，'.$value['title'].'厂商相关信息、品牌等内容。查看'.$value['title'].'最新信息就上官方网。',//描述
            );
            M('down_category')->where('id='.$value['id'])->save($data);
        }
        echo '更新seo标题、关键字、描述成功！';
    }

    /**
     * 产品对于下载基础表
     * @return void
     * @link   /admin.php?s=/Fang/productdown.html
     * @return bool
     */
    public function productdown()
    {
        $data = array();
        $pys = array();
        $lists = M()->db(1)->table("product")->field('*')->select();//protype过滤灌水
        foreach ($lists as $value) {
            $retitle = $value['retitle'];
            $productname = $value['productname'];
            $web = M()->db(1)->table("company")->where('id='.$value['webid'])->getField('title');

            $data = array(
                'id'    =>$value['id'],//产品id
                'uid'   =>'',//用户id
                'title' =>$value['productname'],//产品名称
                'category_rootid'   => '',//根栏目id
                'description'   =>$value['description'],//简介
                'root'  =>'',//根节点
                'pid'   =>'',//所属id
                'type'  => 2,//类别2,主题
                'position'  => '',//产品推荐位
                'link' => '',//外链
                'cover_id'   =>'',//封面横图
                'display'   =>1,//可见性
                'deadline'  =>time(),//截止时间
                'attach'   =>'',//附件数量
                'view'   =>$value['hits'],//点击总数
                'comment'   =>'',//评论数
                'extend'   =>'',//扩展统计字段
                'level'   => '',//优先级
                'create_time'   => strtotime($value['jointime']),//创建时间
                'update_time'  =>strtotime($value['datatime']),//更新时间
                'status'   =>1,//数据状态  0，禁止。 1,正常
                'title_pinyin'   => $value['letter'],//标题首字母
                'path_detail'   => 'product/'.$value['filepath'],//静态文件路径
                'abet'   =>'',//好
                'argue'   =>'',//差
                'seo_title'   => $productname.' 价格 批发 厂家_官方网',//seo标题
                'seo_keywords'   =>$productname.'价格，'.$productname.'批发，'.$productname.'厂家',//seo关键字
                'seo_description'   => '官方网为您提供最新的'.$productname.'价格，'.$productname.'批发，'.$productname.'厂家。想了解更加全面的'.$productname.'就上官方网。',//seo描述
                'hits_month'   =>'',//月点击数
                'hits_week'   =>'',//周点击数
                'hits_today'   =>'',//天点击数
                'date_month'   =>time(),//月点击数开始时间
                'date_week'   =>time(),//周点击数开始时间
                'date_today'   =>time(),//天点击数开始时间
                'audit'   =>1,//审核
                'old_id'   =>1,//老数据id
                'home_position'   =>'',//全站推荐位
                'vertical_pic'   => '',//封面竖图
                'user_id'   => '',//前台用户id
                'edit_id'   => '',//编辑者id
                'package_id'   => $value['webid']//所属公司（网站）
            );

            $py = in_array($value['filepath'], $pys) ? '-' : '';
            $pys[] = $value['filepath'];
            $data['name'] =  $value['filepath'] . $py;//标识

            //实体产品图片
            if($value['protype'] == 1){
                $productpic= str_replace('/up/', '/Uploads/Picture/', $value['productpic']);
                $data['previewimg'] = $this->pic($productpic);//多图
            }

            $data['model_id'] = !empty($value['protype']) ? 20 : 13;//内容模型id

            //所属分类
            $classarr=explode(",",$value['classid']);
            $data['category_id'] = $classarr[0];//导入对应的分类id

            $exist = M('down')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('down')->save($data);
            }else{
                M('down')->add($data);
            }

        }
    }

    /**
 * 网络产品
  * link    /admin.php?s=/Fang/downDmain.html
 * @return void
 */
    public function downDmain(){
        $data = array();
        $lists = M()->db(1)->table("product")->field('*')->where('protype =0')->select();//protype过滤灌水
        foreach ($lists as $value){
            $data = array(
                    'id'    => $value['id'],//产品id
                    'content'   => $value['content'],//产品介绍
                    'version'  => '',//版本
                    'font' => '',//标题字体
                    'font_color'   => '',//标题颜色
                    'size'  =>'',//文件大小
                    'sub_title'  => $value['retitle'],//副标题
                    'parameter' =>'',//其他产品参数（格式：    游戏画面：好|音乐特性：一般）
                    'licence' => '',//授权
                    'network'   => '',//联网
                    'rank'  =>'',//等级
                    'supplier'  => ''//供应商
            );

            //通过值获取id  //兼容平台
            if(in_array($value['forsys'], array('Andriod','andriod系统','Android 4.3以下','android1.0.2','android1.5以上','android1.6以上','android1.6以下','android2.2','Android4.1','android手机','Android版','Android版手机'))){
                $data['system'] = 1;
            }else if(in_array($value['forsys'], array('ios','iOS 4.3 或更高','iOS5','iOS5.0','ios7以上','iOS8','iPad','IPAD版','IPAD，IOS3.2以上','Iphone','Iphone,IPAD','iPhone/iPad','iPhone版','iphone版手机','iso3.0','iso3.2','iso4.0'))){
                $data['system'] = 2;
            }else if(in_array($value['forsys'], array('Windows7/Vista/XP','Windows','XP/2003/Vista/Win7','Win2000/xp','Windows 2000/xp','winXP, Vista, 7','Win 7/Vista/XP','支持winXP/Vista/7','pc windows版','win2000/XP/Vista','PC版','pc','Win2000/2003/XP/WIN7','Vista Winows7','Windows XP','WIN7','2003/XP/WIN7/VISTA','xp','Win7/xp','indows','WinXP/Win7/Win2003','xp win7','xp,Windows7','XP;','XP[','Win','XP`','windows7'))){
                $data['system'] = 3;
            }else{
                $data['system'] = 6;
            }

            //语言
            if($value['language'] == '简体中文'){
                $data['language'] = 1;
            }else if($value['language'] == '英文软件'){
                $data['language'] = 3;
            }else if($value['language'] == '繁体中文'){
                $data['language'] = 2;
            }else{
                $data['language'] = 6;
            }

            $exist = M('down_dmain')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('down_dmain')->save($data);
            }else{
                M('down_dmain')->add($data);
            }
        }
    }

    /**
     * 实体产品表
     * link    /admin.php?s=/Fang/downproduct.html
     * @return void
     */
    public function downproduct(){
        $data = array();
        $lists = M()->db(1)->table("product")->field('*')->where('protype=1')->select();
        foreach ($lists as $value){
            $data = array(
                'id'    => $value['id'],
                'price'   => '',//价格
                'limit'  => '',//最小购买数
                'content' => $value['content'],//产品简介
                'parameter'  =>'',//其他产品参数（格式样例：    适合人群：中年|尺寸：170）
                'stock'  => '',//库存量
                'brand' =>'',//品牌
                'model'    =>'',//型号
                'material'    =>'',//材质
                'market_price'  => '',//市场价
                'supplier'  => ''//供应商
            );

            $exist = M('down_product')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('down_product')->save($data);
            }else{
                M('down_product')->add($data);
            }
        }
    }

    /**
     * 产品对应下载地址表
     * link  /admin.php?s=/Fang/downAddress.html
     * @return bool
     */
    public function downAddress(){

        $data = array();
        $lists = M()->db(1)->table("product")->field('*')->where('protype =0')->select();//protype产品类型0，网络产品

        foreach ($lists as $value) {
            //下载地址(老网站取第一个)
            $value["productpic"] = str_replace(array('.exe@','.rar@','.msi@','.zip@','.cab@','.apk@','.ipa@','.EXE@','FyWlo=@','3a00@','70df83@','2271719@','blazer.msihttp:'), array('.exe@,','.rar@,','.msi@,','.zip@,','.cab@,','.apk@,','.jpa@,','.EXE@,','FyWlo=@,','3a00@,','70df83@,','2271719@,','blazer.msi@,http:'), $value["productpic"]);
            $addarr=explode('@,', $value["productpic"]);
            if($addarr){//多下载地址
                foreach($addarr as $u){
                    $data = array(
                        'id'    => '',
                        'did'   =>$value['id'],//下载id
                        'name'  =>$value['productname'],//下载名称
                        'url'   => $u,//下载地址
                        'hits'   =>$value['hits'],//点击数
                        'site_id'   =>10,//预定义站点id
                        'special'  =>'',//链接类型id
                        'update_time'   =>strtotime($value['datatime']),//更新时间
                        'old_id'    =>1//老数据id
                    );

                    //$exist = M('down_address')->where('did='.$value['id'])->find();
                    //if(!empty($exist)){
                      //M('down_address')->save($data);
                    //}else{
                      M('down_address')->add($data);
                    //}

                }
            }

        }
    }

    /*-----会员相关----*/
    /**
     * 会员对应会员
     * @link  /admin.php?s=/Fang/memberuser.html
     * 后台管理 1  前台用户2
     */
    public function memberuser(){
        $lists = M()->db(1)->table("gwuser")->field('*')->select();
        foreach ($lists as $value) {
            $data = array(
                'uid'    =>$value['id'],//用户id
                'nickname'  =>$value['username'],//昵称
                'username'   =>$value['realname'],//姓名
                'sex'   =>$value['sex'],//性别
                'birthday'  => $value['bron'],//生日
                'company_address'   =>$value['address'],//公司地址
                'company_name'    =>'',//公司名称
                'contacts'   =>$value['realname'],//联系人
                'token'  =>'',//token用于找回密码
                'type'   => 2,//1，后台用户。2，前台用户。
                'qq' =>'',//qq号
                'score'   =>$value['score'],//用户积分
                'login'  =>'',//登录次数
                'reg_id'  => '',//注册ip
                'reg_time'   =>strtotime($value['jointime']),//注册时间
                'last_login_id' => '',//最后登录ip
                'last_login_time'   =>time(),//最后登录时间
                'status'    =>1//状态。1，正常
            );
            //上传图图像处理
            if($value['userpic']=='/user/images/default.gif'){
                $data['head_pic'] = $this->pic($value['userpic']);//头像图片id
            }else{
                $value['userpic'] = preg_replace('/\/up\/userpic\//is', '/Uploads/Picture/', $value['userpic']);

                $data['head_pic'] = $this->pic($value['userpic']);//头像图片id
            }

            $exist = M('member')->where('uid='.$value['id'])->find();
            if(empty($exist)){
                M('member')->add($data);
            }else{
                M('member')->save($data);
            }
        }
    }

    /**
     * 后台用户导入
     * @link  /admin.php?s=/Fang/adminuser.html
     * 后台管理 1  前台用户2
     */
    public function adminuser(){
        $lists = M()->db(1)->table("employee")->field('*')->select();
        foreach ($lists as $value) {
            $data = array(
                'uid'    =>'',//用户id
                'nickname'  =>$value['employee_id'],//昵称
                'username'   =>$value['realname'],//姓名
                'sex'   => '',//性别
                'birthday'  => '',//生日
                'company_address'   => '',//公司地址
                'company_name'    =>'',//公司名称
                'contacts'   =>$value['realname'],//联系人
                'token'  =>'',//token用于找回密码
                'type'   => 1,//1，后台用户。2，前台用户。
                'qq' =>'',//qq号
                'score'   =>'',//用户积分
                'login'  => $value['logins'],//登录次数
                'reg_id'  => '',//注册ip
                'reg_time'   => '',//注册时间
                'last_login_id' => $value['lastip'],//最后登录ip
                'last_login_time'   => strtotime($value['lastlogin']),//最后登录时间
                'status'    =>1//状态。1，正常
            );

            $exist = M('member')->where('uid='.$value['id'])->find();
            if(empty($exist)){
                M('member')->add($data);
            }else{
                M('member')->save($data);
            }
        }
    }

    /**
     * 会员密码对应member - ucenter
     * @link  /admin.php?s=/Fang/ucmember.html
     * @cid 分类id
     */
    public function ucmember(){

        $lists = M()->db(1)->table("gwuser")->field('*')->select();
        foreach ($lists as $value) {
            $data = array(
                'id'    =>$value['id'],//用户id
                'username'  =>$value['username'],//用户名
                'password'   =>$value['userpass'],//密码
                'email'   =>$value['email'],//用户邮箱
                'mobile'  => '',//用户手机
                'reg_time'   =>strtotime($value['jointime']),//注册时间
                'reg_ip'    => '',//注册ip
                'last_login_time'   => time(),//最后登录时间
                'last_login_ip'  => '',//最后登录ip
                'update_time'   => strtotime($value['datatime']),//更新时间
                'status'  =>1//用户状态 1,正常
            );
            $exist = M('ucenter_member')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('ucenter_member')->add($data);
            }else{
                M('ucenter_member')->save($data);
            }
        }
    }

    /**
     * 后台用户密码对应member - ucenter
     * @link  /admin.php?s=/Fang/ucadminmember.html
     * @cid 分类id
     */
    public function ucadminmember(){

        $lists = M()->db(1)->table("employee")->field('*')->select();
        foreach ($lists as $value) {
            $data = array(
                'id'    =>'',//用户id
                'username'  =>$value['employee_id'],//用户名
                'password'   =>$value['employee_password'],//密码
                'email'   => '',//用户邮箱
                'mobile'  => '',//用户手机
                'reg_time'   => strtotime($value['lastlogin']),//注册时间
                'reg_ip'    => '',//注册ip
                'last_login_time'   => strtotime($value['lastlogin']),//最后登录时间
                'last_login_ip'  => $value['lastip'],//最后登录ip
                'update_time'   => strtotime($value['lastlogin']),//更新时间
                'status'  =>1//用户状态 1,正常
            );
            M('ucenter_member')->add($data);
        }
    }

    /**
     * 旧标签导入
     * @link   admin.php?s=/Fang/tags.html
     * @return bool
     */
    public function tags(){
        $data = array();
        $pys = array();
        $lists = M()->db(1)->table("gwtag")->field('*')->select();
        foreach ($lists as $value) {
            $data['id'] =  $value['id'];//标签ID
            $data['category'] =  1;//标签分类
            $data['name'] =  rand(1,99999999);//标识
            $data['title'] = $value['title'];//标题
            $data['pid'] = '';//上级标签ID
            $data['rootid'] = '';//根节点ID
            $data['depth'] = '';//层级
            $data['sort'] = '';//排序（同级有效）
            $data['list_row'] = 10;//列表每页行数
            $data['meta_title'] = '';//SEO的网页标题
            $data['keywords'] = '';//关键字
            $data['description'] = '';//描述
            $data['link_id'] = '';//外链
            $data['display'] = 1;//可见性
            $data['extend'] = '';//扩展设置
            $data['create_time'] = time();//创建时间
            $data['update_time'] = time();//更新时间
            $data['status'] = 1;//数据状态（-1-删除，0-禁用，1-正常，2-待审核）
            $data['icon'] = '';//标签图标
            $data['old_id'] = 1;//原表ID

            $exist = M('tags')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('Tags')->save($data);
            }else{
                M('Tags')->add($data);
            }
        }
    }

    /**
     * 公司标签导入
     * @link   admin.php?s=/Fang/ctags.html
     * @return bool
     */
    public function ctags(){
        $data = array();
        $pys = array();
        $lists = $this->comTags();
        foreach ($lists as $value) {
            $data['id'] =  '';//标签ID
            $data['category'] =  1;//标签分类
            $data['name'] =  rand(1,99999999);//标识
            $data['title'] = $value;//标题
            $data['pid'] = 1;//上级标签ID
            $data['rootid'] = 1;//根节点ID
            $data['depth'] = '';//层级
            $data['sort'] = '';//排序（同级有效）
            $data['list_row'] = 10;//列表每页行数
            $data['meta_title'] = '';//SEO的网页标题
            $data['keywords'] = '';//关键字
            $data['description'] = '';//描述
            $data['link_id'] = '';//外链
            $data['display'] = 1;//可见性
            $data['extend'] = '';//扩展设置
            $data['create_time'] = time();//创建时间
            $data['update_time'] = time();//更新时间
            $data['status'] = 1;//数据状态（-1-删除，0-禁用，1-正常，2-待审核）
            $data['icon'] = '';//标签图标
            $data['old_id'] = 1;//原表ID

            $exist = M('tags')->where('title='.$value)->find();
            if(!empty($exist)){
                M('Tags')->save($data);
            }else{
                M('Tags')->add($data);
            }
        }
    }

    /**
     * 根据产品表标签字段写标签关系数据
     * @link  admin.php?s=/Fang/tagsMap.html
     * @return void
     */
    public function tagsMap(){
        $data = array();
        $lists = M()->db(1)->table("company")->field('*')->select();

        foreach ($lists as $value) {
            $value['tag'] = str_replace(array('，', '、', ' ','|','_'), ',', trim($value['tag']));
            $tag = explode(',', $value['tag']);
            //构造查询标签id条件
            $map_tags['title'] = array('in', $tag);
            //获取标签id
            $tags_id = M('tags')->field('id')->where($map_tags)->select();
            foreach($tags_id as $tid){
                if(empty($tid)){
                    continue;
                }
                $data['id'] =  '';//主键ID
                $data['tid'] =  $tid['id'];//tags表ID
                $data['did'] = $value['id'];//数据表ID
                $data['type'] = 'package';//类型
                $data['create_time'] = strtotime($value['jointime']);//创建时间
                $data['update_time'] = strtotime($value['datatime']);//更新时间

                $exist = M('tags_map')->where('id='.$tid['id'].' and did='.$value['id'])->find();
                if(!empty($exist)){
                    M('tags_map')->save($data);
                }else{
                    M('tags_map')->add($data);
                }
            }


        }
    }

    /**
     * 获得公司标签去重
     * @link   admin.php?s=/Fang/comTags.html
     */
    public function comTags(){
        $tag = array();
        $data = array();
        $lists = M()->db(1)->table("company")->field('distinct tag')->select();
        foreach($lists as $key=>$value){
            if(empty($value['tag'])){
                continue;
            }
            $value['tag'] = str_replace(array('，', '、', ' ','|','_'), ',', trim($value['tag']));
           // $tag = preg_split("/[\s，、|,]+/", trim($value['tag']));
            $tag = explode(',', $value['tag']);
            $data = array_merge($data, $tag);

        }
        //一维数组去重
        $res = array_unique($data);
        return array_filter($res);
    }

    /**
     * 公司刷用户id
     * @link    admin.php?s=/Fang/packageU.html
     * @return bool
     */
    public function packageU()
    {
        $data = array();
        $lists = M()->db(1)->table("company")->alias('c')->join('gwuser g ON c.lasteditor= g.UserName')
            ->field('c.id,g.id as uid')->select();
        foreach ($lists as $value) {
            $data['user_id'] =  $value['uid'];//文档id
            M('package')->where('id='.$value['id'])->save($data);
        }
    }

    /**
     * 产品刷刷用户id
     * @link    admin.php?s=/Fang/productU.html
     * @return bool
     */
    public function productU()
    {
        $data = M('package')->field('id,user_id')->select();
       // p(count($data));
        foreach ($data as $val)
        {
            echo('update onethink_package set user_id='.$val['user_id'].' where id='.$val['id'].';<br/>');
        }
        exit;
        /*
        $data = array();
        $lists = M()->db(1)->table("product")->alias('p')->join('gwuser g ON p.username= g.UserName')
            ->field('p.id,g.id as uid')->select();
        foreach ($lists as $value) {
            $data['user_id'] =  $value['uid'];//文档id
            M('down')->where('id='.$value['id'])->save($data);
        }
        //*/
    }


    /**
     * 单图片导入处理
     * @param string $v 图片地址
     * @param string $title 图片标题
     * @return void
     */
    private function pic($v, $title='', $old = 1){
        if(empty($v)) return 0;
        //查找
        $picid = M('Picture')->where('old='.$old.' AND path="'.trim($v).'"')->field('id')->find();
        $picid = $picid['id'];
        if(is_numeric($picid)) return $picid;
        //插入
        $picid = M('Picture')->add(array(
            'path'=>trim($v),
            'title'=>$title,
            'status'=>1,
            'old'=>$old,
            'create_time'=>time()
        ));
        if(is_numeric($picid)){
            return $picid;
        }else{
            return 0;
        }
    }

    /**
     * 产品内容替换
     * link    admin.php?s=/Fang/productDataR.html
     * array(
    ../../up/day_141028/201410281454087067.jpg
    ../../up/day_141028/201410281454087067.gif
     * )
     * @return bool
     */
    public function productDataR(){
        //新老地址替换规则
        $pattern = array(
            '/\.\.\/\.\.\/up/i',
        );
        $replacement = array(
            '/Uploads/Picture',
        );
        $lists = M()->db(1)->table("product")->field('id, content, protype')->select();
        //$lists = M("down_dmain")->field('id, content')->select();
        //dump($lists);exit;
        foreach ($lists as  $value) {
            //preg_match('/\/Androidgame\/[a-zA-Z_]+[\d]{4}(\d+)\.html/i', $value['content'], $m);
            $data['content'] =  preg_replace($pattern, $replacement, $value['content']);

            if($value['protype']==1){
                M('down_product')->where('id ='.$value['id'])->save($data);
            }else{
                M('down_dmain')->where('id ='.$value['id'])->save($data);
            }

        }
        echo '更新成功！';
    }

    /**
     * 文章内容替换
     * link    admin.php?s=/Fang/websiteDataR.html
     * array(
    ../../up/day_141028/201410281454087067.jpg
    ../../up/day_141028/201410281454087067.gif
     * )
     * @return bool
     */
    public function websiteDataR(){
        //新老地址替换规则
        $pattern = array(
            '/\.\.\/\.\.\/up/i',
            '/\.\.\/up/i',
        );
        $replacement = array(
            '/Uploads/Picture',
            '/Uploads/Picture',
        );
        $lists = M("package_particle")->field('id, content')->select();
        //dump($lists);exit;
        foreach ($lists as  $value) {
            //preg_match('/\/Androidgame\/[a-zA-Z_]+[\d]{4}(\d+)\.html/i', $value['content'], $m);
            $data['content'] =  preg_replace($pattern, $replacement, $value['content']);


            M('package_particle')->where('id ='.$value['id'])->save($data);

        }
        echo '更新成功！';
    }


}
