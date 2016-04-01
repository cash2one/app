<?php
namespace Admin\Controller;
use Think\Controller;
use Common\PinYin;
use User\Api\UserApi;

/**
 * 数据导入控制器
 * @author wwei <wangwei@163.com>
 */
class AnfensiController extends Controller{

    //插入的表名
    public $tables = array(
       'Down'   => array('down','down_dmain', 'down_address'),
       'Document'   => array('document','document_article')
    );

    /**
     * 初始化 设定数据库配置
     * @return void
     */
    public function __construct(){
        set_time_limit(100000);
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
       ini_set("memory_limit","512M");
    }

    /**
     * 专题导入
     * @link    admin.php?s=/Anfensi/feature.html
     * @return bool
     */
    public function feature(){
        $data = array();
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonspec ad ON ar.id= ad.aid')
            ->field('*')->where('')->select();
        foreach ($lists as $value) {
            $data['id'] =  $value['id'];
            $data['pid'] = '';//父页面ID
            $data['interface'] =  '';//手机或触屏
            $data['category_id'] =  $value['typeid'];//分类ID
            $data['title'] =  $value['title'];//专题标题
            $data['seo_title'] =  $value['title'];//seo标题
            $data['keywords'] =  $value['keywords'];//关键字
            $data['description'] =  $value['description'];//专题描述
            $data['layout'] =  $value['templet'];//模版地址
            $data['widget'] =  '';//挂件
            $data['content'] =  $this->getContent($value['id']);//专题描述
            $data['content'] = iconv('GBK', 'UTF-8', $data['content']);
            $data['url_token'] =  $value['redirecturl'];//链接地址
            $data['icon'] = $this->pic($value['litpic']);//专题图标
            $data['topic_count'] =  $value['click'];//话题计数
            $data['label'] =  '';//
            $data['sort'] =  $value['sortrank'];//顺序
            $data['enabled'] =  1;//开启
            $data['update_time'] =  $value['pubdate'];//更新时间
            //$data[''] = $value['writer']; //责任编辑
            //$data[''] =  $value['filename'];//自定义文件名
            $exist = M('feature')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                //M('feature')->save($data);
            }else{
                M('feature')->add($data);
            }
        }
    }

    /**
     * 标签导入
     * @link   admin.php?s=/Anfensi/tags.html
     * @return bool
     */
    public function tags(){
        $data = array();
        $pys = array();
        $lists = M()->db(1)->table("dede_tagindex")->field('*')->select();
        foreach ($lists as $value) {
            $data['id'] =  $value['id'];//标签ID
            $data['category'] =  1;//标签分类
            //拼音处理 同名拼音随机构造为不同的
            $py = strtolower(trim(get_pinyin_first($value['regcode'], true)));
            $py  = empty($py) ? (string)rand(1,99999999) : $py;
            while (in_array($py, $pys)) {
                $py .= '-';   
            }
            $pys[] = $py;
            $data['name'] =  $value['tag_py'] . $py;//标识
            $data['title'] = $value['tag'];//标题
            $data['pid'] = '';//上级标签ID
            $data['rootid'] = '';//根节点ID
            $data['depth'] = '';//层级
            $data['sort'] = $value['sorts'];//排序（同级有效）
            $data['list_row'] = '';//列表每页行数
            $data['meta_title'] = $value['title_seo'];//SEO的网页标题
            $data['keywords'] = $value['keywords'];//关键字
            $data['description'] = $value['descript'];//描述
            $data['link_id'] = $value['tagurl'];//外链
            $data['display'] = 1;//可见性
            $data['extend'] = '';//扩展设置
            $data['create_time'] = $value['addtime'];//创建时间
            $data['update_time'] = $value['addtime'];//更新时间
            $data['status'] = 1;//数据状态（-1-删除，0-禁用，1-正常，2-待审核）
            $data['icon'] = '';//标签图标
            $data['old_id'] = '';//原表ID
            $exist = M('tags')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('Tags')->save($data);
            }else{
                M('Tags')->add($data);
            }
        }
    }

    /**
     * 标签关系数据导入
     * @link  admin.php?s=/Anfensi/tagsMap.html
     * id   1,文档  3，下载   -1，专题
     * @return void
     */
    public function tagsMap($mid=-1){
        if(!empty($mid)){
            $map['channel'] = $mid;
        }
        $data = array();
        $lists = M()->db(1)->table("dede_taglist")->alias('tl')->join('dede_tagindex ti ON tl.tid= ti.id')
            ->join('dede_archives ar ON tl.aid = ar.id')
            ->field('*')->where('ar.channel =' -1)->select();
        foreach ($lists as $value) {
            $data['id'] =  '';//主键ID
            $data['tid'] =  $value['tid'];//tags表ID
            $data['did'] = $value['aid'];//数据表ID
            if($mid == 1){
                $data['type'] = 'document';//类型
            }else if($mid == 3){
                $data['type'] = 'down';//类型
            }else if($mid == -1){
                $data['type'] = 'feature';//类型
            }else{}

            $data['create_time'] = $value['addtime'];//创建时间
            $data['update_time'] = $value['addtime'];//更新时间
            $exist = M('tags_map')->where('id='.$value['tid'].' and did='.$value['aid'])->find();
            if(!empty($exist)) continue;
            $rs = M('tags_map')->add($data);
            if($rs){
                echo('成功！！！！' . PHP_EOL);
            }else{
                echo('失败！！！！' . PHP_EOL);
            }
        }
    }

    //----------------------下载相关----------------------

    /**
     * 下载模型基础表
     * @return void
     * @link   /admin.php?s=/Anfensi/down.html
     * @分类id  12,动作游戏  34,益智休闲  35，手机游戏   36,40竞速游戏  37,角色扮演   38,射击飞行    39,策略塔防
     * 41,手机网游
     */
    public function down()
    {
        //删除
        /* $down_id = M("Down")->field('id')->where('old_id=0')->select();
         if($down_id){
             $down_id_item = array();
             foreach($down_id as $row){
                 $down_id_item[] = $row['id'];
             }
         }

         if($down_id_item){
             $down_id_map['id'] = array('in', $down_id_item);
             M('down_dmain')->where($down_id_map)->delete();
         }*/
         //M('Down')->where('old_id=0')->delete();
        //exit;
        $data = array();
        $typeid_arr = array(12, 34, 35, 36, 40, 37, 38, 39,41);
        $map['ar.typeid'] = array('in', $typeid_arr);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonsoft aso ON ar.id= aso.aid')
            ->field('*')->where($map)->select();
        $cd = array(
            12 => 3, //动作游戏
            34  =>4, //益智休闲
            35 =>1, //单机游戏
            36  =>5, //竞速游戏
            40  =>5, //竞速游戏
            37  =>6, //角色扮演
            38  =>7, //射击飞行
            39  =>8, //策略塔防
            41  =>2 //网络游戏
        );
        foreach ($lists as $value) {
            $data = array(
                'id'    =>$value['id'],//下载id
                'uid'   =>'',//用户id
                'name'  =>'',//标识
                'title' =>$value['shorttitle'],//标题
                'category_id'   =>$cd[$value['typeid']],//所属分类
                'description'   =>$value['description'],//简介
                'root'  =>'',//根节点
                'pid'   =>'',//所属id
                'model_id'    =>13,//内容模型id
                'type'   =>2,//内容类型
                'position'  =>'',//推荐位
                'link' =>$value['officialUrl'],//外链
                'cover_id'   =>'',//封面横图
                'display'   =>1,//可见性
                'deadline'  =>time(),//截止时间
                'attach'   =>'',//附件数量
                'view'   =>$value['click'],//点击总数
                'comment'   =>'',//评论数
                'extend'   =>'',//扩展统计字段
                'level'   =>$value['weight'],//优先级
                'create_time'   =>$value['senddate'],//创建时间
                'update_time'  =>$value['pubdate'],//更新时间
                'status'   =>1,//数据状态  0，禁止。 1,正常
                'title_pinyin'   =>'',//标题首字母
                'path_detail'   =>'',//静态文件路径
                'abet'   =>'',//好
                'argue'   =>'',//差
                'smallimg'   => $this->pic($value['litpic']),//logo图
                'seo_title'   =>$value['title'],//seo标题
                'seo_keywords'   =>$value['keywords'],//seo关键字
                'seo_description'   =>$value['description'],//seo描述
                'hits_month'   =>'',//月点击数
                'hits_week'   =>'',//周点击数
                'hits_today'   =>'',//天点击数
                'date_month'   =>time(),//月点击数开始时间
                'date_week'   =>time(),//周点击数开始时间
                'date_today'   =>time(),//天点击数开始时间
                'audit'   =>1,//审核
                'old_id'   =>1,//老数据id
                'home_position'   =>'',//全站推荐位
                'vertical_pic'   => ''//封面竖图
            );

            //获取软件截图链接
            $data['previewimg'] = ''; //预览多图
            $map_morepic['arcid'] = $value['id'];
            $map_morepic['mediatype'] = 0;
            $lists_morepic = M()->db(1)->table("dede_uploads")
                ->field('url, title')->where($map_morepic)->select();
            if($lists_morepic){
                $picid = array();
                foreach($lists_morepic as $v_morepic){
                    $picid[] = $this->pic($v_morepic['url'], $v_morepic['title']);
                }
                $data['previewimg'] = implode(',', $picid); //预览多图
            }
            if(in_array($value['typeid'], array(12, 34, 36, 37, 38, 39, 40))){
                $data['category_rootid']   = 1; //栏目根id
            }

            $exist = M('down')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('down')->add($data);
            }else{
                //M('down')->save($data);
            }

        }
    }


    /**
     * 软件下载导入
     * link  /admin.php?s=/Anfensi/downAddress.html
     * @return bool
     */
    public function downAddress(){
        //删除
        M('down_address')->where('old_id=0')->delete();
        $data = array();
        $typeid_arr = array(12, 34, 35, 36, 40, 37, 38, 39, 41);
        $map['ar.typeid'] = array('in', $typeid_arr);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonsoft aso ON ar.id= aso.aid')
            ->field('*')->where($map)->select();
        foreach ($lists as $value) {
            $data = array(
                    'did'   =>$value['id'],//下载id
                    'name'  =>$value['title'],//下载名称
                    'hits'   =>$value['click'],//点击数
                    'site_id'   =>'',//预定义站点id
                    'special'  =>'',//链接类型id
                    'update_time'   =>$value['pubdate'],//更新时间
                    'old_id'    =>1//老数据id
            );

            //$search = '/http:\/\/(.*)\.apk/';
            $search = '/http:\/\/(.*)/';
            preg_match($search, $value['softlinks'], $matches);//下载地址
            //结尾替换
            $url = str_replace('{/dede:link}', '', $matches[0]);
           // dump($matches);
            $data['url'] = rtrim($url);
            $exist = M('down_address')->where('did='.$value['id'])->find();
            if(!empty($exist)){
                //M('down_address')->save($data);
            }else{
                M('down_address')->add($data);
            }
        }
    }

 /**
 * 软件下载附属表导入
  * link    /admin.php?s=/Anfensi/downDmain.html
 * @return void
 */
    public function downDmain(){
        $data = array();
        $typeid_arr = array(12, 34, 35, 36, 40, 37, 38, 39, 41);
        $map['ar.typeid'] = array('in', $typeid_arr);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonsoft aso ON ar.id= aso.aid')
            ->field('*')->where($map)->select();
        foreach ($lists as $value){
            $data = array(
                    'id'    => $value['id'],//
                    'content'   => $value['introduce'],//介绍
                    'version'  => $value['banben'],//版本
                    'font' => '',//标题字体
                    'font_color'   => $value['color'],//标题颜色
                    'sub_title'  => $value['title'],//副标题
                    'conductor'   => '',//导读
                    'rank'   => $value['daccess'],//等级
                    'licence' => $value['accredit'],//授权
                    'author_url'    => $value['officialUrl'],//官网
                    'keytext'  => $value['baotext'],//特别
                    'system_version'   =>'',//平台版本
                    'network'   => ($value['typeid'] == 41) ? 2 : 1,//联网
                    'company_id'   => ''//厂商
                /*新版去掉字段*/
                    //'picture_score'   => '',//画面分
                    //'music_score'   => '',//音乐分
                    //'feature_score'   => '',//特色分
                    //'run_score'  => ''//运行分

            );
            //文件大小
            if($value['softsize'] == '未知'){
                $data['size'] = '';
            }else{
                $data['size'] = $value['softsize'];
            }

            if($value['pingtai'] == '安卓'){
                $data['system'] = 1;
            }else if($value['pingtai'] == '苹果'){
                $data['system'] = 2;
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

            /*修改增加字段*/

            //游戏状态
            if($value['yxroot'] == 0){
                $data['yxroot'] = 1;
            }else if($value['yxroot'] == 1){
                $data['yxroot'] = 2;
            }else if($value['yxroot'] == 2){
                $data['yxroot'] = 3;
            }
            //前端状态
            $array_soft = array(
                'g' => 1,
                'w' => 2,
                's' => 4
            );
            $count_softneed = 0;
            $arr_softneed = explode(',', $value['softneed']);
            foreach($arr_softneed as $v){
                $count_softneed += $array_soft[$v];
            }
            $data['softneed'] = $count_softneed;
            //前端角标
            if($value['jbroot'] == ''){
                $data['data_type'] = 1;
            }else if($value['jbroot'] == 'p'){
                $data['data_type'] = 2;
            }else if($value['jbroot'] == 'y'){
                $data['data_type'] = 3;
            }else if($value['jbroot'] == 'g'){
                $data['data_type'] = 4;
            }
            $exist = M('down_dmain')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                //M('down_dmain')->save($data);
            }else{
                M('down_dmain')->add($data);
            }
        }
    }


    /**
     * 文档分类导入
     * @link    /admin.php?s=/Anfensi/category.html
     * @return bool
     */
    public function category(){
        $data = array();
        $typeid = array(80, 184, 191, 192, 182, 81, 83, 181, 168, 147, 156, 166, 169, 170, 171, 157, 160, 152, 193, 204, 208, 205, 206, 207, 194, 196, 197, 198, 209, 210, 211, 212, 213, 15, 21, 23, 149, 150, 151);
        $map['id'] = array('in', $typeid);
        $lists = M()->db(1)->table("dede_arctype")->field('*')->where($map)->select();
        foreach ($lists as $value) {
            $data = array(
                'id'    =>$value['id'],//
                'title'  =>$value['typename'],//标题
                'rootid'   =>'',//
                'depth'   =>$value['softsize'],//
                'sort'  => $value['sortrank'],//排序
                'path_index'   =>'',//首页生成路劲规则
                'path_lists'    =>'',//列表生成路劲规则
                'path_lists_index'   =>'',//列表首页名称（如果填写会同时生成填写名称的第一页）
                'path_detail'  =>'',//详情生成路劲规则
                'list_row'   => 10,//列表每页行数
                'meta_title'  =>$value['seotitle'],//seo的网页标题
                'keywords' =>$value['keywords'],//关键字
                'description'   =>$value['description'],//描述
                'template_index'   =>$value['tempindex'],//频道页模板
                'template_lists'  => $value['templist'],//列表页模板
                'template_detail'   =>$value['temparticle'],//详情页模板
                'template_edit'    =>'',//编辑页模板
                'model'   =>2,//列表绑定模型
                'model_sub'  =>'',//子文档绑定模型
                'type'   =>2,//允许发布的内容类型
                'link_id'  =>$value['siteurl'],//外链
                'allow_publish' =>2,//是否允许发布内容
                'display'   =>$value['ishidden'],//可见性
                'reply'   =>'',//是否允许回复
                'check'  => '',//发布的文章是否需要审核
                'reply_model'   =>'',//
                'extend'    =>'',//扩展设置
                'create_time'   =>time(),//创建时间
                'update_time'  =>time(),//更新时间
                'status'  => 1,//数据状态
                'icon'   =>'',//分类图标
                'old_id'    =>'' //原有分类id
            );

            if(in_array($value['id'], array(184, 191, 192, 182, 81, 83))){
                $data['pid']  = 80; //上级分类id
            }else if(in_array($value['id'], array(147, 156, 166, 169, 170, 171))){
                $data['pid']  = 168; //上级分类id
            }else if($value['id']==160){
                $data['pid']  = 157; //上级分类id
            }else if(in_array($value['id'], array(204, 194, 209))){
                $data['pid']  = 193; //上级分类id
            }else if(in_array($value['id'], array(208, 205, 206, 207))){
                $data['pid']  = 204; //上级分类id
            }else if(in_array($value['id'], array(196, 197, 198))){
                $data['pid']  = 194; //上级分类id
            }else if(in_array($value['id'], array(210, 211, 212, 213))){
                $data['pid']  = 209; //上级分类id
            }else if(in_array($value['id'], array(23, 149, 150, 151))){
                $data['pid']  = 21; //上级分类id
            }
            //拼音处理 同名拼音随机构造为不同的
            $py = strtolower(trim(get_pinyin_first($value['regcode'], true)));
            $py  = empty($py) ? (string)rand(1,99999999) : $py;
            while (in_array($py, $pys)) {
                $py .= '-';
            }
            $pys[] = $py;
            $data['name'] =  $py;//标识
            $exist = M('category')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('category')->save($data);
            }else{
                M('category')->add($data);
            }
        }
    }


    /*-----文档相关----*/
    /**
     * 文档基础表导入
     * @link  /admin.php?s=/Anfensi/document.html
     * @cid 分类id
     */
    public function document(){
        $data = array();
        $typeid = array(80, 184, 191, 192, 182, 81, 181);
        $map['ar.typeid'] = array('in', $typeid);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonarticle ad ON ar.id= ad.aid')
            ->field('*')->where($map)->select();
        foreach ($lists as $value){
            $data = array(
                'id'    => $value['id'],//文档id
                'uid'   => '',//用户id
                'name'  => '',//标识
                'title' => $value['title'],//标题
                'category_id'   => $value['typeid'],//所属分类
                'description'   => $value['description'],//描述
                'root'  => '',//根节点
                'pid'   => '',//所属id
                'type'  => 2,//内容类型
                'position'  => '',//推荐位
                'link'  => $value['redirecturl'],//外链
                'cover_id'  => $this->pic($value['litpic']),//封面
                'display'   => 1,//可见性
                'deadline'  => 0,//截至时间
                'attach'    => '',//附件数量
                'view'  => $value['click'],//浏览量
                'comment'   => '',//评论数
                'extend'    => '',//扩展统计字段
                'level' => $value['weight'],//优先级
                'create_time'   => $value['senddate'],//创建时间
                'update_time'   => $value['pubdate'],//更新时间
                'status'    => 1,//数据状态
                'title_pinyin'  => '',//标题首字母
                'path_detail'   => '',//静态文件路径
                'seo_title' => $value['title'],//seo标题
                'seo_keywords'  => $value['keywords'],//seo关键字
                'seo_description'   => $value['description'],//seo描述
                'video' => '',//是否包含视频
                'pagination_type'   => '',//分页类型
                'files' => '',//附件
                'ding'  => '',//顶
                'cai'   => '',//踩
                'old_id'    => 1,//老数据id
                'home_position' => '',//全站推荐位
                'vertical_pic'  => '',//首页推荐竖图
                'smallimg'  => ''//图鉴
            );

            if($value['typeid'] == 191){
                $data['model_id']   = 19; //内容模型id
            }else{
                $data['model_id']   = 2; //内容模型id
            }
            if(in_array($value['typeid'], array(184, 191, 192, 182, 81))){
                $data['category_rootid']   = 80; //栏目根id
            }else{
                $data['category_rootid']   = 181; //栏目根id
            }

            $exist = M('document')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('document')->add($data);
            }else{
                //M('document')->save($data);
            }

        }
    }

    /**
     * 文档模型文章表导入
     * link    /admin.php?s=/Anfensi/documentArticle.html
     * @return void
     */
    public function documentArticle(){
        $data = array();
        //$typeid = array(80, 184, 191, 192, 182, 81, 83, 181, 168, 147, 156, 166, 169, 170, 171, 157, 160, 152, 193, 204, 208, 205, 206, 207, 194, 196, 197, 198, 209, 210, 211, 212, 213, 15, 21, 23, 149, 150, 151);
        $typeid = array(80, 184, 191, 192, 182, 81, 181);
        $map['ar.typeid'] = array('in', $typeid);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonarticle ad ON ar.id= ad.aid')
            ->field('*')->where($map)->select();
        foreach ($lists as $value) {
            $data = array(
                    'id'    =>$value['id'],//文档id
                    'parse'   =>$value['introduce'],//内容解析内容
                    'content'  =>$value['body'],//文章内容
                    'template' =>$value['templet'],//详情页显示模板
                    'bookmark'   =>'',//收藏数
                    'sub_title'   =>$value['shorttitle'],//副标题
                    'font'  => '',//标题字体
                    'font_color'   =>$value['color'],//标题颜色
                    'author'    =>$value['writer'],//作者
                    'source'   =>$value['source'],//出处
                    'source_url'  =>''//出处网址
            );
            $exist = M('document_article')->where('id='.$value['id'])->find();

            if(empty($exist)){
                M('document_article')->add($data);
            }else{
                //M('document')->save($data);
            }
			
        }
    }

    /**
     * 问答表导入
     * @link  /admin.php?s=/Anfensi/documentWenda.html
     * @cid 分类id
     */
    public function documentWenda(){
        $data = array();
        $typeid = array(191);
        $map['ar.typeid'] = array('in', $typeid);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonarticle ad ON ar.id= ad.aid')
            ->field('*')->where($map)->select();
        foreach ($lists as $value){
            $data = array(
                'id'    => $value['id'],//文档id
                'answer_user'   => $value['writer'],//
                'content'  => $value['body']//
            );

            $exist = M('document_wenda')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('document_wenda')->add($data);
            }else{
                //M('document_wenda')->save($data);
            }

        }
    }

    /**
     * 产品下载标签数据导入
     * link    /admin.php?s=/Anfensi/productTags.html
     * @return void
     */
    public function productTags(){
        //获取软件id
        $data = array();
        //$typeid = array(12, 34, 35, 36, 40, 37, 38, 39, 41);
        //$map['ar.typeid'] = array('in', $typeid);
        $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonsoft aso ON ar.id= aso.aid')
            ->field('aid, shorttitle, softrank, title, keywords, description, senddate, pubdate')->select();
        foreach ($lists as $value) {
            $data = array(
                'id'    =>$value['aid'],//标签id
                'category'   =>1,//分类id
                'name'  => (string)rand(1,99999999),//标识
                'title' =>$value['shorttitle'],//标题
                'pid'   =>'',//上级标签id
                'rootid'   =>'',//根节点id
                'depth'  => '',//层级
                'sort'   =>$value['softrank'],//排序
                'list_row'    =>'',//列表每页行数
                'meta_title'   =>$value['title'],//seo的网页标题
                'keywords'  =>$value['keywords'],//关键字
                'description'  =>$value['description'],//描述
                'link_id'  =>'',//外链
                'display'  =>1,//可见性
                'extend'  =>'',//扩展设置
                'create_time'  =>$value['senddate'],//创建时间
                'update_time'  =>$value['pubdate'],//更新时间
                'status'  =>1,//数据状态（-1-删除，0-禁用，1-正常，2-待审核）
                'icon'  =>'',//标签图标
                'old_id'    =>1//原表id
            );
            $exist = M('product_tags')->where('id='.$value['aid'])->find();
            if(!empty($exist)){
                //M('product_tags')->save($data);
            }else{
                M('product_tags')->add($data);
            }
        }
    }

    /**
     * 产品标签关系表数据导入
     * link    /admin.php?s=/Anfensi/productTagsMap.html
     * id   1,文档  3，下载
     * @return void
     */
    public function productTagsMap($id){
        if(empty($id)){
            return false;
        }
        $data = array();
        if($id == 1){
            $typeid = array(80, 184, 191, 192, 182, 81, 83, 181, 168, 147, 156, 166, 169, 170, 171, 157, 160, 152, 193, 204, 208, 205, 206, 207, 194, 196, 197, 198, 209, 210, 211, 212, 213, 15, 21, 23, 149, 150, 151);
            $map['ar.typeid'] = array('in', $typeid);
            $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonarticle ad ON ar.id= ad.aid')
                ->field('id, softid, senddate, pubdate')->where($map)->select();
            $i = 1;
            foreach ($lists as $value) {
                if(empty($value['softid'])){
                    continue;
                }
                $data = array(
                    'id'    =>$i,//主键id
                    'tid'   =>$value['softid'],//tags表id
                    'did'  => $value['id'],//数据表id
                    'type' =>'document',//类型
                    'create_time'   =>$value['senddate'],//创建时间
                    'update_time'   =>$value['pubdate']//更新时间
                );

                // $exist = M('product_tags_map')->where('id='.$value['id'])->find();
                //if(!empty($exist)){
                //  M('product_tags_map')->save($data);
                //}else{
                    M('product_tags_map')->add($data);
                //}
                $i++;
            }
        }else if($id == 3){//下载产品标签关联数据
            $typeid_arr = array(12, 34, 35, 36, 40, 37, 38, 39,41);
            $map['ar.typeid'] = array('in', $typeid_arr);
            $lists = M()->db(1)->table("dede_archives")->alias('ar')->join('dede_addonsoft aso ON ar.id= aso.aid')
                ->field('*')->where($map)->select();
            foreach ($lists as $value) {
                if(empty($value['shorttitle'])){
                    continue;
                }
                $data = array(
                    'id'    =>'',//主键id
                    'tid'   =>$value['aid'],//tags表id
                    'did'  => $value['aid'],//数据表id 自己关联自己
                    'type' =>'down',//类型
                    'create_time'   =>$value['senddate'],//创建时间
                    'update_time'   =>$value['pubdate']//更新时间
                );
                M('product_tags_map')->add($data);
            }
        }
    }



    /**
     * 下载模型基础表
     * @return void
     * @link   /admin.php?s=/Anfensi/downInsertPreviewing.html
     * @return bool
     */
    public function downInsertPreviewing()
    {
        $down_id = array(9478,82799,11136,11605,12562,12954,22559,23175,23171,23173,23816,23824,23920,24081,24083,24193,24406,25120,25230,26011,26143,26369,26535,27259,27362,27364,27379,27476,27685,27688,27690,27902,27905,27909,28014,28015,28328,28840,28841,28844,28845,28947,28948,28952,28954,29127,29128,29206,29214,29355,29430,29507,29581,29654,29721,29735,29933,30364,30367,30369,30370,30371,30444,30591,30735,30830,31026,31028,31029,31104,31179,31327,31473,31474,31787,31789,82587,32067,32146,32218,32296,32741,32743,32745,32818,32964,32965,32966,32968,32969,33042,33114,33115,82610,33175,33410,33411,33412,33488,34075,34296,34373,34375,34378,34529,34683,34689,34686,34767,34891,34904,34936,34941,34943,82950,36567,36753,36790,36854,36856,36857,37039,75400,37110,37145,37170,37241,37265,37377,37378,37379,37447,38091,38329,38330,38573,38665,38844,38893,39232,39233,13783,13789,13793,13795,13956,14122,14126,14465,14625,14779,14781,14785,14787,15004,15189,15192,15216,37946,82620,21620,21928,21931,39489,39490,39638,39641,40810,40824,42638,46896,67158,61757,62369,62627,64815,64816,64817,64818,64933,82566,66795,66797,66799,66800,66802,66803,68130,69925,70023,70631,70735,70866,71493,71495,71497,71601,71653,73511,73533,73838,74803,74828,74854,74856,74878,74903,75213,75245,75393,76152,78021,78022,78024,78026,78027,78028,78030,78034,78035,78036,78037,78041,78042,78043,78044,78046,78047,78048,78179,78182,82581,82188,82190,82187,82185,79871,82815,81339,81864,81866,81867,81958,82045,82124,82154,82180,82181,82494,82582,82499,82529,82573,82557,82560,82561,82562,82728,82729,82730,82731,82732,82733,82734,82735,82737,82756,82757,82758,82760,82781,82825,82899,82939,83070,83282,83665,83723,84081,84180,84573,84629,84668,85598,86805,87018,89818,90974,92854,95407,97830,100752,101178,101179,101499,101737,103873,103101,103365,103544,105637,105638,105639,105640,105641,105642,105643,105644,105645,105646,105647,105648,105649,105650,105651,105652,105653,105654,105655,105656,105657,105658,105659,105660,105661,105662,105663,105664,105937,107334,107402);
        $map['id'] = array('in', $down_id);
        $lists = M('down')->field('id')->where($map)->select();
        $data = array();
        foreach ($lists as $value) {
            //获取软件截图链接
            $data['previewimg'] = ''; //预览多图
            $map_morepic['arcid'] = $value['id'];
            $lists_morepic = M('uploads')->field('url, title')->where($map_morepic)->select();
            if($lists_morepic){
                $picid = array();
                foreach($lists_morepic as $v_morepic){
                    $picid[] = $this->pic($v_morepic['url'], $v_morepic['title']);
                }
                $data['previewimg'] = implode(',', $picid); //预览多图
            }
            $exist = M('down')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('down')->add($data);
            }else{
                M('down')->where('id='.$value['id'])->save($data);
            }

        }
    }

    /**
     * @param $id
     * @link   /admin.php?s=/Anfensi/addonsoftImgurls.html
     * @return bool
     */
    public function addonsoftImgurls(){

        //新老地址替换规则
        $pattern = array(
            '/\{dede:img(.*)\} (.*) \{\/dede\:img\}/isU',
        );
        $replacement = array(
            '$2,',
        );

        $soft_id = array(9478,11136,11605,12562,12954,22559,23175,23171,23173,23816,23824,23920,24081,24083,24193,24406,25120,25230,26011,26143,27259,27362,27364,27379,27685,27688,27690,27902,27905,27909,28014,28015,28328,28840,28841,28844,28845,28947,28948,28952,28954,29127,29128,29206,29214,29355,29430,29507,29581,29654,29721,29735,29933,30364,30367,30369,30370,30371,30444,30591,30735,30830,31026,31028,31029,31104,31179,31327,31473,31474,31787,31789,32067,32146,32218,32296,32741,32743,32745,32818,32964,32965,32966,32968,32969,33042,33114,33115,33175,33411,33412,33488,34075,34296,34373,34375,34378,34529,34683,34689,34686,34767,34891,34904,34936,34941,34943,36567,36753,36790,36854,36856,36857,37039,37110,37145,37170,37241,37265,37377,37378,37379,37447,38091,38329,38330,38573,38665,38844,38893,39232,39233,13783,13789,13793,13795,13956,14122,14126,14465,14625,14779,14781,14785,14787,15004,15189,15192,15216,37946,82620,21928,21931,70866,81339,83665,84180,84573,84629,84668,85598,86805,90974,95407,101499,101737,103873,103544,105637,105648,105649,105650,105651,105652,105653,105654,105655,105656,105657,105658,105659,105660,105661,105662,105663,105664,105937,107334,107402);
        $map['aso.aid'] = array('in', $soft_id);
        $lists = M()->table("onethink_archives")->alias('ar')->join('onethink_addonsoft aso ON ar.id= aso.aid')
            ->field('id, imgurls, title')->where($map)->select();
        $data = array();
        foreach ($lists as $value) {

            //获取软件截图链接
            $data['previewimg'] = ''; //预览多图
            $url = str_replace("{dede:pagestyle maxwidth='' pagepicnum='' ddmaxwidth='' row='' col='' value=''/}", '', $value['imgurls']);

            $lists_morepic =  preg_replace($pattern, $replacement, $url);
            $lists_morepic = explode(',', $lists_morepic);
            $picid = array();
            $urlId='';
            foreach($lists_morepic as $k=>$v){
                $urlId = trim($v);
                if(!empty($urlId)){
                    $picid[] = $this->pic($urlId, $value['title']);
                }
            }
            $data['previewimg'] = implode(',', $picid); //预览多图
            $exist = M('down')->where('id='.$value['id'])->find();
            if(empty($exist)){
                M('down')->add($data);
            }else{
                M('down')->where('id='.$value['id'])->save($data);
            }
        }
    }

    /*
     * @抓取目标数据
     * @id 专题id
     * */
    public function getContent( $id ){
        if(empty($id)){
            return false;
        }
        //构造链接
        $url = 'http://www.anfensi.com/special/arc-'.$id.'.html';
        //抓取页面内容
        $content = file_get_contents($url);
        //正则匹配之间的html
        $pattern="/<ul class=\"m-h15 t-l clearfix\">(.*?)<\/ul>/si";
        preg_match($pattern, $content, $m);
        //正则替换远程地址为本地地址
        //$p=preg_replace('/\/weather\/(\w+)\/index.htm/', 'tq.php/$1.html', $m[0]);
        return $m[0];
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
     * 厂商图片导入处理
     * @param string $v 软件作者
     * @return void
     */
    private function company($v){
        if(empty($v)) return 0;
        //查找
        $picid = M('company')->where('name="'.trim($v).'"')->field('id')->find();
        $picid = $picid['id'];
        if(is_numeric($picid)) return $picid;
        //插入
        $picid = M('company')->add(array(
            'id'    =>'',//标签id
            'uid'   =>1,//用户id
            'name'  => $v,//公司名
            'name_e'    => '',//英文名
            'path' =>'',//生成路劲
            'pinyin'   =>'',//拼音首字母
            'keywords'   =>'',//关键字
            'title'  => '',//title
            'description'   =>'',//厂商详细说明 描述
            'homepage'    =>'',//主页
            'img'   =>'',//图片id
            'position_img'  =>'',//推荐图片id
            'scontent'  =>'',//厂商简单说明
            'create_time'  =>time(),//创建时间
            'update_time'  =>time()//更新时间
        ));
        if(is_numeric($picid)){
            return $picid;
        }else{
            return 0;
        }
    }



    /**
     * 下载文档数据更新
     * link    /admin.php?s=/anfensi/doAfsUpdate/tablename/Down.html
     * @param string $tablename
     * @param wwei $v 软件作者
     * @return bool
     */
    public function doAfsUpdate($tablename = ''){
        $Model = M($tablename);
        $mytables = $this->tables;
        if($mytables){
            $t = $mytables[$tablename]; //循环的表
        }

        if($tablename == 'Down'){
            $map['d.old_id'] = 0;
            $filename = 'down.txt';
            $result = $Model->alias('d')->join('LEFT JOIN __DOWN_DMAIN__ dd ON d.id = dd.id')->join('LEFT JOIN __DOWN_ADDRESS__ dw ON d.id = dw.id')->where($map)->select();
        }else if($tablename == 'Document'){
            $map['d.old_id'] = 0;
            $filename = 'document.txt';
            $result = $Model->alias('d')->join('LEFT JOIN __DOCUMENT_ARTICLE__ da ON d.id = da.id')->join('LEFT JOIN __DOCUMENT_WENDA__ as dw ON d.id = dw.id')->where($map)->select();
        }else{
            return false;
        }

        //写入文件
        file_put_contents($filename, json_encode($result));
        //读取文件
        $data_arr = json_decode(file_get_contents($filename));

        exit;
        $data = array();
        foreach ($data_arr as $row){
            if($tablename == 'Down'){
                foreach($t as $item){
                    switch($item){
                        case 'down':
                            $data = array(
                                'id'    =>'',//下载id
                                'uid'   =>$row['uid'],//用户id
                                'name'  =>'',//标识
                                'title' =>$row['title'],//标题
                                'category_id'   =>$row['category_id'],//所属分类
                                'description'   =>$row['description'],//简介
                                'root'  =>$row['root'],//根节点
                                'pid'   =>$row['pid'],//所属id
                                'model_id'    =>$row['model_id'],//内容模型id
                                'type'   =>$row['type'],//内容类型
                                'position'  =>$row['position'],//推荐位
                                'link' =>$row['link'],//外链
                                'cover_id'   =>'',//封面横图
                                'display'   =>$row['display'],//可见性
                                'deadline'  =>$row['deadline'],//截止时间
                                'attach'   =>$row['attach'],//附件数量
                                'view'   =>$row['view'],//点击总数
                                'comment'   =>$row['comment'],//评论数
                                'extend'   =>$row['extend'],//扩展统计字段
                                'level'   =>$row['level'],//优先级
                                'create_time'   =>$row['create_time'],//创建时间
                                'update_time'  =>$row['update_time'],//更新时间
                                'status'   =>$row['status'],//数据状态  0，禁止。 1,正常
                                'title_pinyin'   =>$row['title_pinyin'],//标题首字母
                                'path_detail'   =>$row['path_detail'],//静态文件路径
                                'abet'   =>$row['abet'],//好
                                'argue'   =>$row['argue'],//差
                                'smallimg'   => $this->pic($row['litpic']),//logo图
                                'seo_title'   =>$row['seo_title'],//seo标题
                                'seo_keywords'   =>$row['seo_keywords'],//seo关键字
                                'seo_description'   =>$row['seo_description'],//seo描述
                                'hits_month'   =>$row['hits_month'],//月点击数
                                'hits_week'   =>$row['hits_week'],//周点击数
                                'hits_today'   =>$row['hits_today'],//天点击数
                                'date_month'   =>$row['date_week'],//月点击数开始时间
                                'date_week'   =>$row['title'],//周点击数开始时间
                                'date_today'   =>$row['date_today'],//天点击数开始时间
                                'audit'   =>1,//审核
                                'old_id'   =>0,//老数据id
                                'category_rootid'   => $row['category_rootid'],//分类根目录id
                                'home_position'   =>$row['home_position'],//全站推荐位
                                'vertical_pic'   => ''//封面竖图
                            );
                            //获取软件截图链接
                            $data['previewimg'] = ''; //预览多图
							
                            if($row['previewimg']){
                                $picid = array();
                                foreach($row['previewimg'] as $v_morepic){
                                    $picid[] = $this->pic($v_morepic['url'], $row['title']);
                                }
                            $data['previewimg'] = implode(',', $picid); //预览多图
                            }
                            M('dowm')->add($data);
                            break;
                    case 'down_dmain':
                        $data = array(
                            'id'    => '',//
                            'content'   => $row['content'],//介绍
                            'version'  => $row['version'],//版本
                            'font' => $row['font'],//标题字体
                            'font_color'   => $row['font_color'],//标题颜色
                            'size'  => $row['size'], //文件大小
                            'sub_title'  => $row['sub_title'],//副标题
                            'conductor'   => $row['conductor'],//导读
                            'system'    => $row['system'],
                            'rank'   => $row['rank'],//等级
                            'data_type' => $row['data_type'], //前端角标
                            'licence' => $row['licence'],//授权
                            'language'  => $row['language'], //语言
                            'author_url'    => $row['author_url'],//官网
                            'keytext'  => $row['keytext'],//特别
                            'system_version'   =>$row['system_version'],//平台版本
                            'network'   => $row['network'],//联网
                            'company_id'    => $row['company_id'],//厂商
                            'yxroot'    => $row['yxroot'], //游戏状态
                            'softneed'  => $row['softneed'], //前端状态
                            'ordernum'  => $row['ordernum']
                        );
                        M('down_dmain')->add($data);
                        break;
                    case 'down_address':
                        $data = array(
                            'id'    =>'',
                            'did'   =>$row['did'],//下载id
                            'name'  =>$row['name'],//下载名称
                            'url'   => $row['url'], //下载地址
                            'hits'   =>$row['hits'],//点击数
                            'site_id'   =>'',//预定义站点id
                            'special'  =>'',//链接类型id
                            'update_time'   =>$row['update_time'],//更新时间
                            'old_id'    =>0//老数据id
                        );
                        $exist = M('down_address')->where('did='.$row['id'])->find();
                        if(!empty($exist)){
                            //M('down_address')->save($data);
                        }else{
                            M('down_address')->add($data);
                        }
                        break;
                        default:
                        echo '更新完成！';

                    }
                }
            }else if($tablename == 'Document'){
                    switch($row){
                        case 'document':
                            $data = array(
                                'id'    => '',//文档id
                                'uid'   => $row['uid'],//用户id
                                'name'  => '',//标识
                                'title' => $row['title'],//标题
                                'category_id'   => $row['category_id'],//所属分类
                                'description'   => $row['description'],//描述
                                'root'  => $row['root'],//根节点
                                'pid'   => $row['pid'],//所属id
                                'model_id'  => $row['model_id'],//模型id
                                'type'  => 2,//内容类型
                                'position'  => $row['position'],//推荐位
                                'link'  => $row['link'],//外链
                                'cover_id'  => $this->pic($row['litpic']),//封面
                                'display'   => 1,//可见性
                                'deadline'  => 0,//截至时间
                                'attach'    => 0,//附件数量
                                'view'  => $row['view'],//浏览量
                                'comment'   => '',//评论数
                                'extend'    => '',//扩展统计字段
                                'level' => $row['level'],//优先级
                                'create_time'   => $row['create_time'],//创建时间
                                'update_time'   => $row['update_time'],//更新时间
                                'status'    => 1,//数据状态
                                'title_pinyin'  => '',//标题首字母
                                'path_detail'   => '',//静态文件路径
                                'seo_title' => $row['seo_title'],//seo标题
                                'seo_keywords'  => $row['seo_keywords'],//seo关键字
                                'seo_description'   => $row['seo_description'],//seo描述
                                'video' => '',//是否包含视频
                                'category_rootid'   => $row['category_rootid'],//分类根目录id
                                'pagination_type'   => '',//分页类型
                                'files' => '',//附件
                                'ding'  => $row['ding'],//顶
                                'cai'   => $row['cai'],//踩
                                'old_id'    => 0,//老数据id
                                'home_position'     => $row['home_position'],//全站推荐位
                                'vertical_pic'  => '',//首页推荐竖图
                                'smallimg'  => ''//图鉴
                            );

                            $exist = M('document')->where('id='.$row['id'])->find();
                            if(empty($exist)){
                                M('document')->add($data);
                            }else{
                                //M('document')->save($data);
                            }
                            break;
                        case 'document_article':
                            $data = array(
                                'id'    =>'',//文档id
                                'parse'   =>$row['parse'],//内容解析内容
                                'content'  =>$row['content'],//文章内容
                                'template' =>$row['templet'],//详情页显示模板
                                'bookmark'   =>$row['bookmark'],//收藏数
                                'sub_title'   =>$row['sub_title'],//副标题
                                'font'  => $row['font'],//标题字体
                                'font_color'   =>$row['font_color'],//标题颜色
                                'author'    =>$row['author'],//作者
                                'source'   =>$row['source'],//出处
                                'source_url'  =>$row['source_url']//出处网址
                            );
                            M('document_article')->add($data);
                            break;
                            default:
                            echo '更新完成！';
                    }
            }
        }
    }
/**
 * @增加问答数据
 * @link /admin.php?s=/anfensi/addWenda.html
 * @return bool
 * */
    public function addWenda(){
        $map['id'] = array('in', '95875, 95748, 95718, 95710, 95709, 95708, 95707, 95705, 95703, 95702, 95700, 95679, 95678, 96597, 96350, 96349, 96348, 96347, 96346, 96345, 96343, 96342, 96341, 96340, 96339, 96338, 96337, 96308, 96306, 96305, 96304, 96300, 96299, 96297, 96240, 95928, 95918, 95917, 95916, 95863, 95857, 95856, 95854, 95851, 95840, 95839, 95838, 95814, 95811, 95809, 95804, 95801, 95787, 95786, 95785, 95782, 95777, 95774, 95766, 95763, 95761, 95760, 95757, 95754, 95753, 95717, 95716, 95715, 95714, 95459, 95390, 95372, 95033, 94965, 97393, 97298, 97170, 96994, 96658, 96655, 96199, 96082, 96080, 96079, 95398, 95075');
        $res = M('document_article')->field('id, content, author')->where($map)->select();
        if($res){
            $data = array();
            foreach($res as $item){
                $data = array(
                    'id'    => $item['id'],
                    'answer_user'   => $item['author'],
                    'content'   => $item['content']
                );
                M('document_wenda')->add($data);
            }
        }
    }

    /**
     * 下载文档数据更新
     * link    ?s=/anfensi/documentArticleReplace.html
     * @return bool
     */
    public function documentArticleReplace(){
        //新老地址替换规则
        $pattern = array(
            '/http:\/\/www.anfensi.com\/[\d]{4}(\d+)\//i',
            '/\/Androidgame\/[a-zA-Z_]+[\d]{4}(\d+)\.html/i',
            '/\/gonglue\/[\d]{4}(\d+)\.html/i',
            '/gonglue\/[\d]{4}(\d+)\.html/i',
            '/\/Androidgame_[\d]{4}(\d+)\.html/i',
            '/http:\/\/www.anfensi.com\/Androidgame\//i',
            '/[^a-zA-Z\/]news\/(\d+)\.html/i',
            '/[^a-zA-Z\/]down\/(\d+)\.html/i',
            '/href=\//i',
        );
        $replacement = array(
            'http://www.anfensi.com/down/$1.html',
            '/down/$1.html',
            '/news/$1.html',
            '/news/$1.html',
            '/down/$1.html',
            '/http://www.anfensi.com/danji/',
            '/news/$1.html',
            '/down/$1.html',
            'href="/',
        );
        $result = M('document_article')->field('id, content')->select();
            foreach ($result as $value) {
                $value['content'] =  preg_replace($pattern, $replacement, $value['content']);
                //echo $value['content'].PHP_EOL;
                M('document_article')->where('id='.$value['id'])->save($value);
                echo '内链-'.$value['id'].PHP_EOL;
            }
    }


    /**
 * 问答模块数据替换
 * link    /admin.php?s=/anfensi/wenDaReplace.html
 * @return bool
 */
    public function wenDaReplace(){
        //新老地址替换规则
        $pattern = array(
            '/http:\/\/www.anfensi.com\/[\d]{4}(\d+)\//i',
            '/\/Androidgame\/[a-zA-Z_]+[\d]{4}(\d+)\.html/i',
            '/\/gonglue\/[\d]{4}(\d+)\.html/i',
            '/gonglue\/[\d]{4}(\d+)\.html/i',
            '/\/Androidgame_[\d]{4}(\d+)\.html/i',
            '/http:\/\/www.anfensi.com\/Androidgame\//i',
            '/[^a-zA-Z\/]news\/(\d+)\.html/i',
            '/[^a-zA-Z\/]down\/(\d+)\.html/i',
            '/href=\//i',
        );
        $replacement = array(
            'http://www.anfensi.com/down/$1.html',
            '/down/$1.html',
            '/news/$1.html',
            '/news/$1.html',
            '/down/$1.html',
            '/http://www.anfensi.com/danji/',
            '/news/$1.html',
            '/down/$1.html',
            'href="/',
        );
        $result = M('document_wenda')->field('id, content')->select();
        foreach ($result as  $value) {
            //preg_match('/\/Androidgame\/[a-zA-Z_]+[\d]{4}(\d+)\.html/i', $value['content'], $m);
            $value['content'] =  preg_replace($pattern, $replacement, $value['content']);
            //echo $value['content'].PHP_EOL;
            M('document_wenda')->where('id ='.$value['id'])->save($value);
            echo '内链-'.$value['id'].PHP_EOL;
        }
    }

    /**
     * 专区public替换
     * link    /admin.php?s=/anfensi/batchReplace.html
     * @return bool
     */
    public function batchReplace(){
        //新老地址替换规则
        $pattern = array(
            '/[^a-zA-Z.]public\//is',
        );
        $replacement = array(
            '/public/',
        );
        $result = M('batch')->field('id, content')->select();
        foreach ($result as  $value) {
            $value['content'] =  preg_replace($pattern, $replacement, $value['content']);
            //echo $value['content'].PHP_EOL;
            M('batch')->where('id ='.$value['id'])->save($value);
            echo '内链-'.$value['id'].PHP_EOL;
        }
    }

    /*
     * ALTER TABLE `onethink_down_dmain`
MODIFY COLUMN `size`  varchar(10) NOT NULL COMMENT '文件大小' AFTER `font_color`


    ALTER TABLE `onethink_picture` ADD `title` VARCHAR(60) NOT NULL COMMENT '注释' AFTER `url`;

     * */

}
