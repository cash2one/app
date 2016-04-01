<?php
// +----------------------------------------------------------------------
// | 描述：久游数据导入（其实这些导入可以整理成一个基本的导入类，再继承）
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-3-31 下午1:56    Version:1.0.0 
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Think\Controller;

/**
 * 描述：久游数据导入类
 * Class GameSyncController
 * @package Admin\Controller
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
class GameSyncController extends Controller
{

    //API表名
    public $tables = array('down', 'down_dmain', 'down_address');

    /**
     * 初始化 设定数据库配置
     * @return void
     */
    public function __construct()
    {
        set_time_limit(100000);
    }

    /**
     * 描述：获取预设站点id
     * @param string $site_name
     * @param string $site_url
     * @return int|mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getPresetSite($site_name = '九游下载', $site_url = 'http://downum.game.uc.cn/download/package/')
    {
        //预设站点名称和url地址有一个为空时，返回0
        if (empty($site_name) || empty($site_url)) return 0;
        $data = array();
        // $data['site_name'] = trim($site_name);
        $data['site_url'] = trim($site_url);
        //查询是否存在预设站点
        $site_id = M('PresetSite')->where($data)->field('id')->find();
        $site_id = $site_id['id'];
        //存在预设站点,返回预设站点主键id
        if (is_numeric($site_id)) return $site_id;
        //不存在就插入
        $data['site_name'] = trim($site_name);
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        $site_id = M('PresetSite')->add($data);
        unset($data);
        if (is_numeric($site_id)) {
            return $site_id;
        }
        return 0;
    }

    /**
     * 描述：获取分类id
     * @return array
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getCategory()
    {
        //分类id
        $cate_id = array();
        $game_sub = array(
            2 => array('id' => 2, 'name' => '9game_scjs', 'cateName' => '赛车竞速', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            3 => array('id' => 3, 'name' => '9game_xxyz', 'cateName' => '休闲益智', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            4 => array('id' => 4, 'name' => '9game_jsmx', 'cateName' => '角色冒险', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            5 => array('id' => 5, 'name' => '9game_cltf', 'cateName' => '策略塔防', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            6 => array('id' => 6, 'name' => '9game_dzkp', 'cateName' => '动作酷跑', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            7 => array('id' => 7, 'name' => '9game_tyjj', 'cateName' => '体育竞技', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            8 => array('id' => 8, 'name' => '9game_fxsj', 'cateName' => '飞行射击', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            9 => array('id' => 9, 'name' => '9game_kpqp', 'cateName' => '卡牌棋牌', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            10 => array('id' => 10, 'name' => '9game_yyyx', 'cateName' => '音乐游戏', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            11 => array('id' => 11, 'name' => '9game_qt', 'cateName' => '其他', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
            12 => array('id' => 12, 'name' => '9game_rj', 'cateName' => '软件', 'pid' => 1, 'rootid' => 1, 'depth' => 2, 'status' => 1),
        );
        $lists = array(
            0 => array('id' => 1, 'name' => '9game_wy', 'cateName' => '九游网游', 'pid' => 0, 'rootid' => 1, 'depth' => 1, 'status' => 1, 'sub' => $game_sub)
        );
        foreach ($lists as $value) {
            $data = array(
                'name' => $value['name'], //标志
                'title' => $value['cateName'], //标题
                'pid' => $value['pid'], //上级分类id
                'rootid' => '', //根分类id
                'depth' => $value['depth'], //层级
                'sort' => '', //排序
                'list_row' => '10', //列表每页行数
                'meta_title' => $value['cateName'], //seo的网页标题
                'keywords' => '', //关键字
                'description' => '', //描述
                'model' => 13, //列表绑定模型
                'model_sub' => '13', //子文档绑定模型
                'type' => 2, //允许发布的内容类型  2,代表主题
                'link_id' => '', //外链
                'allow_publish' => 2, //是否允许发布内容
                'display' => 1, //可见性
                'reply' => '', //是否允许回复
                'check' => '', //发布的文章是否需要审核
                'reply_model' => '', //
                'extend' => '', //扩展设置
                'create_time' => time(), //创建时间
                'update_time' => time(), //更新时间
                'status' => $value['status'], //数据状态
                'icon' => '' //分类图标
            );
            $game_data = array();
            $game_data['name'] = $value['name'];
            //$game_data['title'] = $value['cateName'];
            $exist = M('down_category')->where($game_data)->field('id')->find();
            $cid = $exist['id'];
            if (!is_numeric($cid)) {
                $cid = M('down_category')->add($data);
                $data['rootid'] = $cid;
                $data['id'] = $cid;
                M('down_category')->save($data);
            }

            if (is_array($value['sub'])) {
                foreach ($value['sub'] as $key => $val) {
                    unset($data, $game_data);
                    $data = array(
                        'name' => $val['name'], //标志
                        'title' => $val['cateName'], //标题
                        'pid' => $cid, //上级分类id
                        'rootid' => $cid, //根分类id
                        'depth' => $val['depth'], //层级
                        'sort' => '', //排序
                        'list_row' => '10', //列表每页行数
                        'meta_title' => $val['cateName'], //seo的网页标题
                        'keywords' => '', //关键字
                        'description' => '', //描述
                        'model' => 13, //列表绑定模型
                        'model_sub' => '', //子文档绑定模型
                        'type' => 2, //允许发布的内容类型  2,代表主题
                        'link_id' => '', //外链
                        'allow_publish' => 2, //是否允许发布内容
                        'display' => 1, //可见性
                        'reply' => '', //是否允许回复
                        'check' => '', //发布的文章是否需要审核
                        'reply_model' => '', //
                        'extend' => '', //扩展设置
                        'create_time' => time(), //创建时间
                        'update_time' => time(), //更新时间
                        'status' => $val['status'], //数据状态
                        'icon' => '' //分类图标
                    );
                    $game_data['name'] = $val['name'];
                    $game_data['title'] = $val['cateName'];
                    $exist = M('down_category')->where($game_data)->field('id')->find();
                    $sub_cid = $exist['id'];
                    if (!is_numeric($sub_cid)) {
                        $sub_cid = M('down_category')->add($data);
                    }
                    $cate_id[$key] = $sub_cid;
                }
            }
        }
        return $cate_id;
    }

    /**
     * 久游数据导入方法
     * @access public
     * @param string $type 更新类型，默认 all-到现在为止全部，add-增量更新
     * @param string $url API地址类型，默认 pro-实际生产地址，test-测试地址
     * @return void
     * @link    ./admin.php?s=/GameSync/index/type/all.html
     */
    public function index($type = 'all', $urlType = 'pro', $temp = 'idongdong')
    {
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
        //添加内站链接
        // $this->getPresetSite();
        //获取分类
        if ($temp == 'afs')
        {
            $params['caller'] = '9game_7730';
            $params['signKey'] = 'bff8f1c3f3489a9a7ce45bfc1d364bae';  //这些都是测试的
            $cate_id = $this->getCategory();
        }else if($temp == '7230')
        {
            $params['caller'] = '9game_7730';
            $params['signKey'] = 'bff8f1c3f3489a9a7ce45bfc1d364bae';  //这是用的afs的，以后要改
            $cate_id = $this->getCategory();
        }

        //缓存截止时间
        S('ucDataToCache', $dateTo);
        //清空上一次修改数据内容
        //修改内存
        ini_set('memory_limit', '512M');

        //设置超时时间
        ini_set('max_execution_time', '0');

        $this->getData($dateFrom, $dateTo, $params, $cate_id, $temp);
    }

    /**
     * 获取数据
     * @access public
     * @param string $dateFrom 获取数据开始时间
     * @param string $dateFrom 获取数据结束时间
     * @param array $params 参数array('url'=>'***','caller'=>'***','signKey'=>'***'),
     * @param string $url API地址
     * @return void
     */
    public function getData($dateFrom, $dateTo, $params, $cate_id, $temp)
    {

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
        $client = array("caller" => $caller, "ex" => '');
        $post_data = array("id" => time(), "client" => $client, "encrypt" => ''); //encrypt推荐Base64加密

        $pageNum = 1;
        $count = 0;
        echo '最开始的内存占用：' . memory_get_usage() . PHP_EOL . '********************' . PHP_EOL;
        //while ($pageNum <= 30) {
        while (true) {
            $data['pageNum'] = $pageNum;
            ksort($data);

            $sign = $caller;
            foreach ($data as $k => $v) {
                $sign .= $k . '=' . $v;
            }
            $sign .= $signKey;
            $sign = md5($sign);

            $post_data['sign'] = $sign;
            $post_data['data'] = $data;

            $output = $this->curl($url, json_encode($post_data));

            if ($output) {
                //获取list数据，不存在则停止
                $output_data = json_decode($output->data, true);
                $list = $output_data['list'];
                if (empty($list)) break;
                $this->_insertDate($list, $cate_id, $temp); //数据插入
                //var_dump($list);
                $count += count($list);
                echo 'down表插入 ' . $count . ' 条' . PHP_EOL;
                //echo '~~~~~~~~~~~~~~'.PHP_EOL.'插入结束占用：'.memory_get_usage().PHP_EOL;
            } else {
                break;
            }

            $pageNum++;
        }
        echo '********************' . PHP_EOL . '结束的内存占用：' . memory_get_usage() . PHP_EOL;
    }

    /**
     * 描述：获取文件路径（这个函数写的不是很严谨）
     * @param $url
     * @param string $dir
     * @return null|string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getPicPath($url,$dir="")
    {
        if(empty($url)) return null;
        $file = time()."_".rand().rand().pathinfo($url,PATHINFO_BASENAME);
        return $dir.$file;
    }
    /**
     * 数据插入
     * ID为主键，插入同一主键数据无效
     * @access protected
     * @param array $list API获得的数据
     * @return void
     */
    protected function _insertDate($list, $cate_id, $temp)
    {
        if (!is_array($list)) {
            return false;
        }
        $mytables = $this->tables;
        //实例化模型
        foreach ($mytables as $t) {
            ${$t . 'Model'} = M($t);
        }

        //取出主表名，并排除
        $main = $mytables[0];
        array_shift($mytables);

        //循环数据
        foreach ($list as $v) {
            //主键为0舍弃
            if ($v['id'] == 0) {
                continue;
            }

            if ($v['deleted'] == 1) {
                continue;
            }
            if (!$v['packages']) {
                continue;
            }

            if ($v['platforms'][0]['platformId'] == 2) {
                $uninsert_cid = array('39', '40', '41', '48', '49', '50', '51', '55', '56');
                //分类对应关系
                if ($temp == 'afs' || $temp == '7230') {
                    $cid = array(
                        1 => $cate_id['2'], //休闲益智（网游）
                        2 => $cate_id['3'], //赛车竞速（网游）
                        3 => $cate_id['4'], //角色冒险（网游）
                        4 => $cate_id['5'], //策略塔防（网游）
                        5 => $cate_id['4'], //角色冒险（网游）
                        6 => $cate_id['6'], //动作酷跑（网游）
                        7 => $cate_id['11'], //其它（网游）
                        8 => $cate_id['7'], //体育竞技（网游）
                        9 => $cate_id['8'], //飞行射击（网游）
                        10 => $cate_id['9'], //卡牌棋牌（网游）
                        11 => $cate_id['6'], //动作酷跑（网游）
                        12 => $cate_id['2'], //休闲益智（网游）
                        13 => $cate_id['11'], //其它（网游）
                        14 => $cate_id['11'], //其它（网游）
                        18 => $cate_id['3'], //赛车竞速（网游）
                        19 => $cate_id['11'], //其它（网游）
                        25 => $cate_id['11'], //其它（网游）
                        35 => $cate_id['11'], //其它（网游）
                        36 => $cate_id['11'], //其它（网游）
                        37 => $cate_id['11'], //其它（网游）
                        38 => $cate_id['11'], //其它（网游）
                        53 => $cate_id['5'], //策略塔防（网游）
                        54 => $cate_id['2'], //休闲益智（软件）
                        57 => $cate_id['10'], //音乐游戏（网游）
                        39 => $cate_id['12'], //软件
                        40 => $cate_id['12'],
                        41 => $cate_id['12'],
                        48 => $cate_id['12'],
                        49 => $cate_id['12'],
                        50 => $cate_id['12'],
                        51 => $cate_id['12'],
                        55 => $cate_id['12'],
                        56 => $cate_id['12']
                    );
                    if (in_array($v['categoryId'], $uninsert_cid) && $temp == "afs") {
                        continue;
                    }
                } else if($temp == 'idongdong'){
                    $cid = array(
                        1 => 2, //休闲益智（网游）
                        2 => 3, //赛车竞速（网游）
                        3 => 4, //角色冒险（网游）
                        4 => 5, //策略塔防（网游）
                        5 => 4, //角色冒险（网游）
                        6 => 6, //动作酷跑（网游）
                        7 => 11, //其它（网游）
                        8 => 7, //体育竞技（网游）
                        9 => 8, //飞行射击（网游）
                        10 => 9, //卡牌棋牌（网游）
                        11 => 6, //动作酷跑（网游）
                        12 => 2, //休闲益智（网游）
                        13 => 11, //其它（网游）
                        14 => 11, //其它（网游）
                        18 => 3, //赛车竞速（网游）
                        19 => 11, //其它（网游）
                        25 => 11, //其它（网游）
                        35 => 11, //其它（网游）
                        36 => 11, //其它（网游）
                        37 => 11, //其它（网游）
                        38 => 11, //其它（网游）
                        39 => 14, //TEMP
                        40 => 14, //TEMP
                        41 => 13, //通讯社交（软件）
                        48 => 14, //TEMP
                        49 => 14, //TEMP
                        50 => 14, //TEMP
                        51 => 14, //TEMP
                        53 => 5, //策略塔防（网游）
                        54 => 2, //休闲益智（软件）
                        55 => 14, //TEMP
                        56 => 12, //软件
                        57 => 10 //音乐游戏（网游）
                    );
                }
                $flag = 0; //标志位，用于标识更新还是新增（0标识新增，1标识更新）
                //处理down表
                $data = array();
                if($temp == 'idongdong')
                {
                    $data['b.package_name'] = $v['packages'][0]['extendInfo']['packageName'];  //dongdong 数据获取方式改变，只判断包名
                    $data['b.company_id'] =  2; //dongdong 厂商名为2
                }
                else if($temp == 'afs')
                {
                    $data['title'] = trim($v['name']);
                }
                else if($temp == '7230')
                {
                   if(in_array($v['categoryId'], $uninsert_cid))
                   {
                       $data['title'] = trim($v['name']); //软件
                   }
                   else
                   {

                       $v['name'] = trim($v['name']).'九游版';
                       $data['b.package_name'] = $v['packages'][0]['extendInfo']['packageName']; //根据包名判断
                       //$data['title'] = trim($v['name']);
                   }
                    $pic_dir = "/ApiUploads/JiuYou/"; //设置图片下载目录
                    $file = $this->getPicPath($v['platforms'][0]['logoImageUrl'],$pic_dir); //logo图
                    if(down_pic($v['platforms'][0]['logoImageUrl'],$file)) $v['platforms'][0]['logoImageUrl'] = $file;
                    $file = $this->getPicPath($v['frontImageUrl'],$pic_dir);  //封面横图
                    if(down_pic($v['frontImageUrl'],$file)) $v['frontImageUrl'] = $file;
                    $file = $this->getPicPath($v['platforms'][0]['frontImageUrl'],$pic_dir);//封面竖图
                    if(down_pic($v['platforms'][0]['frontImageUrl'],$file)) $v['platforms'][0]['frontImageUrl'] = $file;
                    if (!empty($v['platforms'][0]['screenshotImageUrls'])) {  //预览多图
                        foreach ($v['platforms'][0]['screenshotImageUrls'] as &$value) {
                            $file = $this->getPicPath($value,$pic_dir);
                            if(down_pic($value,$file)) $value = $file;
                        }
                    }
                }
                //$data['name'] =trim($v['package_name']);
                // $data['category_id'] = $this->getCategory($v['type']);
                $down_id = M('down_dmain')->alias('b')->join(' __DOWN__ a ON b.id=a.id')->where($data)->field('a.id')->find();
                $down_id = $down_id['id'];
                $cate_id1 = $cid[$v['categoryId']]; //所属分
                $game_data = array(
                    'id' => '', //下载id
                    'uid' => '', //用户id
                    'name' => $v['pinyinFull'], //标识
                    'title' => trim($v['name']), //卡号名称
                    'category_id' => $cid[$v['categoryId']], //所属分类
                    'description' => $v['description'], //简介
                    'root' => '', //根节点
                    'pid' => '', //所属id
                    'model_id' => 13, //内容模型id
                    'type' => $v['typeIds'], //内容类型
                    'position' => '', //推荐位
                    'link' => '', //外链
                    'cover_id' => $v['frontImageUrl'], //封面横图
                    'display' => 1, //可见性
                    'deadline' => time(), //截止时间
                    'attach' => '', //附件数量
                    'view' => '', //点击总数
                    'comment' => '', //评论数
                    'extend' => '', //扩展统计字段
                    'level' => '', //优先级
                    'create_time' => $v['createTime'], //创建时间
                    'update_time' => time(), //更新时间
                    'status' => 2, //数据状态  -1:删除,0:禁用,1:正常,2:待审核,3:草稿
                    'title_pinyin' => $v['firstChar'], //标题首字母
                    'path_detail' => '', //静态文件路径
                    'abet' => '', //好
                    'argue' => '', //差
                    'smallimg' => $this->pic($v['platforms'][0]['logoImageUrl']), //logo图
                    'seo_title' => $v['title'], //seo标题
                    'seo_keywords' => '', //seo关键字
                    'seo_description' => '', //seo描述
                    'hits_month' => '', //月点击数
                    'hits_week' => '', //周点击数
                    'hits_today' => '', //天点击数
                    'date_month' => time(), //月点击数开始时间
                    'date_week' => time(), //周点击数开始时间
                    'date_today' => time(), //天点击数开始时间
                    'old_id' => '', //老数据id
                    'category_rootid' => get_category_by_model($cate_id1, 'rootid', 'down_category'), //根目录id
                    'home_position' => '', //全站推荐位
                    'vertical_pic' => $this->pic($v['platforms'][0]['frontImageUrl']), //封面竖图
                );
                //处理多图字段
                if (!empty($v['platforms'][0]['screenshotImageUrls'])) {
                    $picid = array();
                    foreach ($v['platforms'][0]['screenshotImageUrls'] as $value) {
                        $picid[] = $this->pic($value); //预览多图
                    }
                    $game_data['previewimg'] = implode(',', $picid); //预览多图
                }
                //不存在，添加
                if (!is_numeric($down_id)) {
                    //处理down表
                    $down_id = ${$main . 'Model'}->add($game_data);
                } else {
                    $flag = 1;
                    //$game_data['id'] = $down_id;
                    //${$main . 'Model'}->save($game_data);
                }
                //循环处理其他表
                foreach ($mytables as $t) {
                    if ($t == 'down_dmain') {
                        if($temp == 'afs')
                        {
                            $company_id = $this->getCompanyId();
                        }
                        if($temp == 'idongdong') $company_id = 2;
                        //构造平台数据
                        $downdmain_data = array(
                            'id' => $down_id, //
                            'content' => $v['platforms'][0]['description'], //介绍
                            'version' => $v['platforms'][0]['version'], //包版本。例如：v1.2.3
                            'package_name' => $v['packages'][0]['extendInfo']['packageName'], //apk源包的包名
                            'font' => '', //标题字体
                            'font_color' => '', //标题颜色
                            'size' => $v['packages'][0]['fileSize'] / 1024, //文件大小
                            'sub_title' => $v['shortName'], //副标题
                            'conductor' => $v['platforms'][0]['instruction'], //导读
                            'system' => '1', //平台
                            'rank' => $v['packages'][0]['secureLevel'], //等级
                            'data_type' => '', //前端角标
                            'licence' => '', //授权
                            'language' => '', //语言
                            'author_url' => '', //官网
                            'keytext' => $v['platforms'][0]['versionUpdateDesc'], //特别
                            'package_version' => $v['packages'][0]['extendInfo']['minSdkVersion'], //apk运行需要的android sdk最低版本,
                            'network' => $v['typeId'], //联网
                            'company_id' => $company_id, //厂商
                            'yxroot' => $v['platforms'][0]['operationStatusId'], //游戏状态
                            'softneed' => '', //前端状态
                            'score' => $v['platforms'][0]['score'] //用户评分平均分
                        );
                        //插入数据
                        $exist = M('down_dmain')->field(true)->where('id=' . $down_id)->find();
                        if (empty($exist)) {
                            //构造平台数据
                            M('down_dmain')->add($downdmain_data);
                        } else {
                            /*
                            if($exist['version'] !=$v['version'])
                            {
                            //更新信息
                            $downdmain_data['id'] = $down_id;
                            M('down_dmain')->save($downdmain_data);
                            }
                            */
                        }
                    }
                    if ($t == 'down_address') {
                        $downaddress_data = array(
                            'id' => '',
                            'did' => $down_id, //下载id
                            'name' => $v['packages'][0]['name'], //下载名称
                            'url' => $v['packages'][0]['downUrl'], //下载地址
                            'hits' => '', //点击数
                            'site_id' => $this->getPresetSite(), //预定义站点id
                            'special' => '', //链接类型id
                            'update_time' => time(), //更新时间
                            'old_id' => '' //老数据id
                        );
                        $data = array();
                        $data['did'] = $down_id;
                        if($temp == 'idongdong')
                        {
                           // $site_id = $this->getPresetSite();
                            $data['site_id'] = array('in',array('1','2','3'));
                        }
                        else
                        {
                            $data['url'] = $v['packages'][0]['downUrl'];
                        }
                        //判断下载地址是否存在
                        $exist = M('down_address')->where($data)->field('id')->find();
                        if (!is_numeric($exist['id'])) {
                            M('down_address')->add($downaddress_data);
                            unset($data);
                            $data['id'] = $down_id;
                            $import_data['did'] = $down_id;
                            $import_data['update_type'] = $flag;
                            $import_data['update_time'] = time();
                            if ($flag) {
                                $import_data['content'] = '【九游】 原有游戏/软件[' . trim($v['name']) . ']新增下载扩展链接：' . $v['packages'][0]['downUrl'];
                            } else {
                                $import_data['content'] = '【九游】 新增：[' . trim($v['name']) . ']游戏/软件';
                            }
                            M('api_import_log')->add($import_data);
                            $data['update_time'] = time();
                            M('down')->save($data);
                        } else {
                            if($temp == 'idongdong')
                            {
                                $data['id'] = $exist['id'];
                                $data['url'] = $v['packages'][0]['downUrl'];
                                $data['site_id'] = $this->getPresetSite();
                                M('down_address')->save($data);
                                $import_data['did'] =  $down_id;
                                $import_data['update_type'] = 1;
                                $import_data['update_time'] = time();
                                $import_data['content'] = '【九游】 原有游戏/软件['.trim($v['name']).']修改下载扩展链接：'.$v['packages'][0]['downUrl'];
                                M('api_import_log')->add($import_data);
                            }
                            //$downaddress_data['id'] = $exist['id'];
                            //M('down_address')->save($downaddress_data);
                        }
                    }
                }
                unset($data, $downaddress_data);
            }
        }
    }


    /**
     * 单图片导入处理
     * @param string $v 图片地址
     * @return void
     */
    private function pic($v)
    {
        if (empty($v)) return 0;
        //查找
        $picid = M('picture')->where('path="' . trim($v) . '"')->field('id')->find();
        $picid = $picid['id'];
        if (is_numeric($picid)) return $picid;
        //插入
        $picid = M('Picture')->add(array(
            'path' => trim($v),
            'title' => '',
            'status' => 1,
            'old' => '',
            'create_time' => time()
        ));
        if (is_numeric($picid)) {
            return $picid;
        } else {
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
    public function curl($url, $post_data)
    {

        $headers = array("Accept:application/json", 'Expect:', 'Transfer-Encoding:');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        unset($headers, $post_data, $url, $ch);
        return json_decode($output);
    }

    /**
     * 描述：获取厂商id
     * @param string $company_name
     * @return int|mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getCompanyId($company_name = '九游')
    {
        if (empty($company_name)) return 0;
        $key = base64_encode($company_name);
        //测试 (测试的时候可以删除缓存，实际使用环境中，还是使用缓存)
        S($key,null);
        //判断缓存中是否存在，存在就直接返回
        if (S($key)) return S($key);
        $data = array();
        $data['name'] = $company_name;
        //查询厂商是否存在
        $company_id = M('Company')->where($data)->field('id')->find();
        $company_id = $company_id['id'];
        //存在就返回厂商id
        if (is_numeric($company_id)) {
            //缓存厂商id，下次直接查询缓存
            S($key, $company_id);
            return $company_id;
        }
        //设置用户id
        //$data['uid'] = 1;
        //设置关键字
        $data['keywords'] = $company_name;
        $data['create_time'] = time();
        $data['update_time'] = $data['create_time'];
        $company_id = M('Company')->add($data);
        unset($data);
        if (is_numeric($company_id)) {
            //缓存厂商id，下次直接查询缓存
            S($key, $company_id);
            return $company_id;
        }
        return 0;
    }


} 