<?php
namespace Admin\Controller;
use Think\Controller;
use Common\PinYin;
use User\Api\UserApi;
class DatasyncController extends Controller {

    //API表名
    public $tables = array('down','down_dmain', 'down_address');

    /**
     * 初始化 设定数据库配置
     * @return void
     */
    public function __construct(){
        set_time_limit(100000);
    }

    /**
     * 插入分类
     * @link    /admin.php?s=/Datasync/category.html
     * @return bool
     */
    public function category(){
        exit;
        //删除
        M('down_category')->where('1=1')->delete();

        $lists = array(
            0   => array('id' => 1, 'name'=>'wy', 'cateName' => '网游', 'pid'=>0, 'rootid'=>1, 'depth'=>1, 'status'=>1),
            1   => array('id' => 2, 'name'=>'scjs', 'cateName' => '赛车竞速', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            2   => array('id' => 3, 'name'=>'xxyz', 'cateName' => '休闲益智', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            3   => array('id' => 4, 'name'=>'jsmx', 'cateName' => '角色冒险', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            4   => array('id' => 5, 'name'=>'cltf', 'cateName' => '策略塔防', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            5   => array('id' => 6, 'name'=>'dzkp', 'cateName' => '动作酷跑', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            6   => array('id' => 7, 'name'=>'tyjj', 'cateName' => '体育竞技', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            7   => array('id' => 8, 'name'=>'fxsj', 'cateName' => '飞行射击', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            8   => array('id' => 9, 'name'=>'kpqp', 'cateName' => '卡牌棋牌', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            9   => array('id' => 10, 'name'=>'yyyx', 'cateName' => '音乐游戏', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            10  => array('id' => 11, 'name'=>'qt', 'cateName' => '其他', 'pid'=>1, 'rootid'=>1, 'depth'=>2, 'status'=>1),
            11  => array('id' => 12, 'name'=>'rj', 'cateName' => '软件', 'pid'=>0, 'rootid'=>12, 'depth'=>1, 'status'=>1),
            12  => array('id' => 13, 'name'=>'txsj', 'cateName' => '通讯社交', 'pid'=>12, 'rootid'=>12, 'depth'=>2, 'status'=>1),
            13  => array('id' => 14, 'name'=>'temp', 'cateName' => 'TEMP', 'pid'=>0, 'rootid'=>14, 'depth'=>1, 'status'=>0)
        );
        foreach ($lists as $value) {
            $data = array(
                'id'    =>$value['id'],//
                'name'  =>$value['name'],//标志
                'title'  =>$value['cateName'],//标题
                'pid'   =>$value['pid'],//上级分类id
                'rootid'   =>$value['rootid'],//根分类id
                'depth'   =>$value['depth'],//层级
                'sort'  => '',//排序
                'path_index'   =>'',//首页生成路劲规则
                'path_lists'    =>'',//列表生成路劲规则
                'path_lists_index'   =>'',//列表首页名称（如果填写会同时生成填写名称的第一页）
                'path_detail'  =>'',//内容生成路劲规则
                'list_row'   => '',//列表每页行数
                'meta_title'  =>$value['cateName'],//seo的网页标题
                'keywords' => '',//关键字
                'description'   => '',//描述
                'template_index'   => '',//频道页模板
                'template_lists'  => '',//列表页模板
                'template_detail'   => '',//详情页模板
                'template_edit'    => '',//编辑页模板
                'model'   => 13,//列表绑定模型
                'model_sub'  => '',//子文档绑定模型
                'type'   => 2,//允许发布的内容类型  2,代表主题
                'link_id'  => '',//外链
                'allow_publish' => 2,//是否允许发布内容
                'display'   => 1,//可见性
                'reply'   => '',//是否允许回复
                'check'  => '',//发布的文章是否需要审核
                'reply_model'   => '',//
                'extend'    => '',//扩展设置
                'create_time'   => time(),//创建时间
                'update_time'  => time(),//更新时间
                'status'  => $value['status'],//数据状态
                'icon'   => ''//分类图标
            );

            $exist = M('down_category')->where('id='.$value['id'])->find();
            if(!empty($exist)){
                M('down_category')->save($data);
            }else{
                M('down_category')->add($data);
            }
        }
    }

    /**
     * 主方法
     * @access public
     * @param string $type 更新类型，默认 all-到现在为止全部，add-增量更新
     * @param string $url API地址类型，默认 pro-实际生产地址，test-测试地址
     * @return void
     * @link    ./admin.php?s=/Datasync/index/type/all.html
     */
        public function index($type='all',$urlType='pro'){
        //更新类型
        switch ($type) {
            case 'add':
                $dateTo = date('YmdHis');
                $dateFrom = S('ucDataToCache');
                break;
            case 'all':       
            default:
               $dateTo = date('YmdHis');
               $dateFrom = '20000101000000';
                break;
        }

        //API地址类型,获取参数
        $params = array();
        switch ($urlType) {
            case 'test':
                $params['url'] = "http://interface.test7.9game.cn:8039/datasync/getdata";
                $params['caller'] = 'pc6';
                $params['signKey'] = '3ba620bfbf84974d3f01fa3e017c5e5b';
                break;
            case 'pro':
            default:
                $params['url'] = "http://interface.9game.cn/datasync/getdata";
                $params['caller'] = '9game_pc_pc6';
                $params['signKey'] = 'a35aab75f1c8b49d6e125d0ce62865c3';
                break;
        }      
        
        //缓存截止时间
        S('ucDataToCache',$dateTo);

        //修改内存
        ini_set('memory_limit','512M');

        //设置超时时间
        ini_set('max_execution_time', '0'); 

        $this->getData($dateFrom,$dateTo,$params);
    }

   /**
     * 获取数据
     * @access public
     * @param string $dateFrom 获取数据开始时间  
     * @param string $dateFrom 获取数据结束时间  
     * @param array  $params 参数array('url'=>'***','caller'=>'***','signKey'=>'***'), 
     * @param string $url API地址
     * @return void
     */
    public function getData($dateFrom,$dateTo,$params){

        //参数变量化
        extract($params);

        //data参数
        $data = array();
        $data['dateFrom'] = $dateFrom;
        $data['dateTo'] = $dateTo;
        $data['syncEntity'] = 'game,platform,package,event';
        $data['syncField'] = 'game.id, game.cpId, game.cpName, game.name, game.categoryId, game.typeId, game.firstChar, game.pinyinFull, game.nickName, game.pinyinFirstLetter, game.pinyinFullNick, game.pinyinFirstLetterNick, game.shortName, game.platforms, game.packages, game.events, game.createTime, game.modifyTime, game.deleted, platform.id ,platform.gameId ,platform.platformId ,platform.active ,platform.score ,platform.logoImageUrl ,platform.frontImageUrl ,platform.screenshotImageUrls ,platform.version ,platform.versionUpdateDesc ,platform.dataPkgInstallDesc ,platform.operationTypeId ,platform.instruction ,platform.operationStatusId ,platform.uploadTime ,platform.size ,platform.description ,platform.modifyTime ,platform.createTime ,platform.deleted ,package.id ,package.gameId ,package.platformId ,package.downUrl ,package.name ,package.packageName ,package.fileSize ,package.active ,package.type ,package.dataPackageIds ,package.version ,package.upgradeDescription ,package.md5 ,package.chId ,package.datapkgInstallPath ,package.govVersion ,package.ad ,package.secureLevel ,package.secureInstruction ,package.extendInfo ,package.deleted ,package.createTime ,package.modifyTime ,event.id ,event.typeIds ,event.platformIds ,event.beginTime ,event.displayTime ,event.publishTime ,event.active ,event.description ,event.specialSource ,event.title ,event.newServer ,event.expansion ,event.mergeServer ,event.newVersion ,event.deleted ,event.createTime ,event.modifyTime';
        $data['syncType'] = '1';
        $data['pageSize'] = '100';
        $client = array("caller"=>$caller,"ex"=>'');
        $post_data = array("id" => time(),"client" => $client,"encrypt"=>''); //encrypt推荐Base64加密

        $pageNum = 1;
        $count = 0;
        echo '最开始的内存占用：'.memory_get_usage().PHP_EOL.'********************'.PHP_EOL;
        //while ($pageNum <= 30) {
        while (true) {
            $data['pageNum'] = $pageNum;
            ksort($data);

            $sign = $caller;
            foreach($data as $k => $v){
                $sign .= $k . '=' . $v;
            }
            $sign .= $signKey;
            $sign = md5($sign);

            $post_data['sign'] = $sign;
            $post_data['data'] = $data;

            $output = $this->curl($url,json_encode($post_data));

            if ($output) {
                //获取list数据，不存在则停止
                $output_data = json_decode($output->data,true);
                $list = $output_data['list'];
                if (empty($list)) break;
                $this->_insertDate($list); //数据插入
                //var_dump($list);
                $count += count($list);
                echo 'down表插入 '.$count.' 条'.PHP_EOL;
                //echo '~~~~~~~~~~~~~~'.PHP_EOL.'插入结束占用：'.memory_get_usage().PHP_EOL;
            }else{
                break;
            }

            $pageNum++;
        }
        echo '********************'.PHP_EOL.'结束的内存占用：'.memory_get_usage().PHP_EOL;
  
    }


    /**
     * 数据插入
     * ID为主键，插入同一主键数据无效
     * @access protected
     * @param array $list API获得的数据
     * @return void
     */
    protected function _insertDate($list){
        if (!is_array($list)){
            return false;
        }

        $mytables = $this->tables;

        //实例化模型
        foreach ($mytables as $t) {
             ${$t.'Model'} =M($t);
        }

        //取出主表名，并排除
        $main = $mytables[0];
        array_shift($mytables);

        //循环数据
        foreach ($list as $v) {
            //主键为0舍弃
            if ($v['id'] == 0){
                continue;
            }

            if ($v['deleted'] == 1){
                continue;
            }
            if(!$v['packages']){
                continue;
            }

            if($v['platforms'][0]['platformId'] == 2){
                //分类对应关系
                $cid = array(
                    1 => 2, //休闲益智（网游）
                    2  =>3, //赛车竞速（网游）
                    3 =>4, //角色冒险（网游）
                    4  =>5, //策略塔防（网游）
                    5  =>4, //角色冒险（网游）
                    6  =>6, //动作酷跑（网游）
                    7  =>11, //其它（网游）
                    8  =>7, //体育竞技（网游）
                    9  =>8, //飞行射击（网游）
                    10  =>9, //卡牌棋牌（网游）
                    11  =>6, //动作酷跑（网游）
                    12  =>2, //休闲益智（网游）
                    13  =>11, //其它（网游）
                    14  =>11, //其它（网游）
                    18  =>3, //赛车竞速（网游）
                    19  =>11, //其它（网游）
                    25  =>11, //其它（网游）
                    35  =>11, //其它（网游）
                    36  =>11, //其它（网游）
                    37  =>11, //其它（网游）
                    38  =>11, //其它（网游）
                    39  =>14, //TEMP
                    40  =>14, //TEMP
                    41  =>13, //通讯社交（软件）
                    48  =>14, //TEMP
                    49  =>14, //TEMP
                    50  =>14, //TEMP
                    51  =>14, //TEMP
                    53  =>5, //策略塔防（网游）
                    54  =>2, //休闲益智（软件）
                    55  =>14, //TEMP
                    56  =>12, //软件
                    57  =>10//音乐游戏（网游）
                );

            //处理down表
            $game_data = array(
                 'id'    => '',//下载id
                 'uid'   =>'',//用户id
                 'name'  =>$v['pinyinFull'],//标识
                 'title' =>$v['name'],//卡号名称
                 'category_id'   => $cid[$v['categoryId']],//所属分类
                 'description'   =>$v['description'],//简介
                 'root'  =>'',//根节点
                 'pid'   =>'',//所属id
                 'model_id'    =>13,//内容模型id
                 'type'   =>$v['typeIds'],//内容类型
                 'position'  =>'',//推荐位
                 'link' =>'',//外链
                 'cover_id'   =>$v['frontImageUrl'],//封面横图
                 'display'   =>1,//可见性
                 'deadline'  =>time(),//截止时间
                 'attach'   =>'',//附件数量
                 'view'   =>'',//点击总数
                 'comment'   =>'',//评论数
                 'extend'   =>'',//扩展统计字段
                 'level'   =>'',//优先级
                 'create_time'   =>$v['createTime'],//创建时间
                 'update_time'  =>time(),//更新时间
                 'status'   =>2,//数据状态  -1:删除,0:禁用,1:正常,2:待审核,3:草稿
                 'title_pinyin'   =>$v['firstChar'],//标题首字母
                 'path_detail'   =>'',//静态文件路径
                 'abet'   =>'',//好
                 'argue'   =>'',//差
                 'smallimg'   => $this->pic($v['platforms'][0]['logoImageUrl']),//logo图
                 'seo_title'   =>$v['title'],//seo标题
                 'seo_keywords'   =>'',//seo关键字
                 'seo_description'   =>'',//seo描述
                 'hits_month'   =>'',//月点击数
                 'hits_week'   =>'',//周点击数
                 'hits_today'   =>'',//天点击数
                 'date_month'   =>time(),//月点击数开始时间
                 'date_week'   =>time(),//周点击数开始时间
                 'date_today'   =>time(),//天点击数开始时间
                 'old_id'   =>'',//老数据id
                 'home_position'   =>'',//全站推荐位
                 'vertical_pic'   => $this->pic($v['platforms'][0]['frontImageUrl']), //封面竖图
                 'game_id'   => $v['id']
            );
                //根目录id
                if(in_array($v['categoryId'], array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,18,19,25,35,36,37,38,53,57))){
                    $game_data['category_rootid']   = 1;//网游
                }else if(in_array($v['categoryId'], array(41,12))){
                    $game_data['category_rootid']   = 12;//软件
                }else{
                    $game_data['category_rootid']   = 14;//TEMP
                }

            //处理多图字段
            if(!empty($v['platforms'][0]['screenshotImageUrls'])){
                 $picid = array();
                 foreach($v['platforms'][0]['screenshotImageUrls'] as $value){
                     $picid[] = $this->pic($value);//预览多图
                 }
                     $game_data['previewimg'] = implode(',', $picid); //预览多图
            }
            //不插入id重复的数据
            $exist = M('down_dmain')->where('package_name='.$v['packages'][0]['extendInfo']['packageName'])->find();
            if(!$exist){
                ${$main.'Model'}->add($game_data);
            }else{
                continue;
            }


            //循环处理其他表
            foreach ($mytables as $t) {
                if ($t == 'down_dmain') {
                     //构造平台数据
                     $downdmain_data = array(
                         'id'    => '',//
                         'content'   => $v['platforms'][0]['description'],//介绍
                         'version'   => $v['platforms'][0]['version'],//包版本。例如：v1.2.3
                         'package_name'  => $v['packages'][0]['extendInfo']['packageName'],//apk源包的包名
                         'font' => '',//标题字体
                         'font_color'   => '',//标题颜色
                         'size'   => $v['packages'][0]['fileSize'],//文件大小
                         'sub_title'  => $v['shortName'],//副标题
                         'conductor'   => $v['platforms'][0]['instruction'],//导读
                         'system'    => 1,//平台
                         'rank'   => $v['packages'][0]['secureLevel'],//等级
                         'data_type' => '',//前端角标
                         'licence' => '',//授权
                         'language'  =>'',//语言
                         'author_url'    => '',//官网
                         'keytext'  => $v['platforms'][0]['versionUpdateDesc'],//特别
                         'package_version'   =>$v['packages'][0]['extendInfo']['minSdkVersion'],//apk运行需要的android sdk最低版本,
                         'network'   => $v['typeId'],//联网
                         'company_id'   => 2,//厂商
                         'yxroot'    => $v['platforms'][0]['operationStatusId'], //游戏状态
                         'softneed'  => '',//前端状态
                         'score' => $v['platforms'][0]['score'] //用户评分平均分
                     );
                     //插入数据
                     M('down_dmain')->add($downdmain_data);
                 }
                    $exist_id = M('down')->field('id')->where('game_id='.$v['id'])->find();
                   //down_address表插入前的特殊处理
                   if ($t == 'down_address') {
                     $downaddress_data = array(
                         'id'    => '',
                         'did'   => $exist_id['id'],//下载id
                         'name'  =>$v['packages'][0]['name'],//下载名称
                         'url'   =>$v['packages'][0]['downUrl'],//下载地址
                         'hits'   =>'',//点击数
                         'site_id'   =>'',//预定义站点id
                         'special'  =>'',//链接类型id
                         'update_time'   =>time(),//更新时间
                         'old_id'    =>''//老数据id
                     );
                     //插入数据
                     M('down_address')->add($downaddress_data);
                }
            }
        }
        }
 }


    /**
     * 单图片导入处理
     * @param string $v 图片地址
     * @return void
     */
    private function pic($v){
        if(empty($v)) return 0;
        //查找
        $picid = M('picture')->where('path="'.trim($v).'"')->field('id')->find();
        $picid = $picid['id'];
        if(is_numeric($picid)) return $picid;
        //插入
        $picid = M('Picture')->add(array(
            'path'=>trim($v),
            'title'=>'',
            'status'=>1,
            'old'=>'',
            'create_time'=>time()
        ));
        if(is_numeric($picid)){
            return $picid;
        }else{
            return 0;
        }
    }


    /**
     * HTTP请求
     * @access public
     * @param string $url 请求地址
     * @param array $post_data 请求数据
     * @return mixed
     */
    public function curl($url,$post_data){

        $headers = array("Accept:application/json",'Expect:','Transfer-Encoding:');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        unset($headers,$post_data,$url,$ch);

        return json_decode($output);
    }    




}
