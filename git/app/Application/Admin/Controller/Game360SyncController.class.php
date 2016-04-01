<?php
// +----------------------------------------------------------------------
// | 描述:360手机平台数据获取文件（其实这些导入可以整理成一个基本的导入类，再继承）
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-4-3 下午5:12    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Think\Controller;

/**
 * 描述：
 * Class Game360SyncController
 * @package Admin\Controller
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
class Game360SyncController extends Controller
{
    //需要同步数据表名
    protected $tables = array('down', 'down_dmain', 'down_address');

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
    protected function getPresetSite($site_name = '360下载', $site_url = 'http://api.np.mobilem.360.cn/redirect/down/')
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
     * 描述：获取分类信息id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getCategory($cate_name = '', $parent_cate_name = '360游戏', $name = '360_game')
    {
        //分类id缓存key
        $cate_key = base64_encode($cate_name);
        $key = base64_encode($parent_cate_name . $name);
        //测试 (测试的时候可以删除缓存，实际使用环境中，还是使用缓存)
        S($cate_key, null);
        S($key, null);
        //缓存存在，直接返回分类id缓存
        if (S($cate_key)) return S($cate_key);
        //上级分类名称为空，上级id赋值为0
        if (empty($parent_cate_name)) {
            $pid = 0;
        } else {
            //上级分类id缓存key
            if (S($key)) {
                //直接读取缓存
                $pid = S($key);
            } else {
                $data = array();
                $data['title'] = $parent_cate_name;
                $data['name'] = $name;
                $pid = M('down_category')->where($data)->field('id')->find();
                $pid = $pid['id'];
                if (!is_numeric($pid)) {
                    $pdata = array(
                        'name' => $name, //标志
                        'title' => $parent_cate_name, //标题
                        'pid' => 0, //上级分类id
                        'rootid' => '', //根分类id
                        'depth' => '', //层级
                        'sort' => '', //排序
                        'list_row' => '10', //列表每页行数（默认10条）
                        'meta_title' => $parent_cate_name, //seo的网页标题
                        'keywords' => '', //关键字
                        'description' => '', //描述
                        'model' => 13, //列表绑定模型
                        'model_sub' => 13, //子文档绑定模型
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
                        'status' => 1, //数据状态
                        'icon' => '' //分类图标
                    );
                    $pid = M('down_category')->add($pdata);
                }
                //缓存上级分类id
                S($key, $pid);
            }
        }
        //释放
        unset($data);
        //不是数字，赋值为0
        if (!is_numeric($pid)) $pid = 0;
        if (empty($cate_name)) return 0;
        $data['title'] = $cate_name;
        $data['pid'] = $pid;

        $cate_id = M('down_category')->where($data)->field('id')->find();
        $cate_id = $cate_id['id'];
        if (is_numeric($cate_id)) {
            unset($data);
            S($cate_key, $cate_id);
            return $cate_id;
        } else {
            $data = array(
                'name' => (string)rand(1, 99999999), //标志
                'title' => $cate_name, //标题
                'pid' => $pid, //上级分类id
                'rootid' => $pid, //根分类id
                'depth' => '', //层级
                'sort' => '', //排序
                'list_row' => '', //列表每页行数
                'meta_title' => $cate_name, //seo的网页标题
                'keywords' => '', //关键字
                'description' => '', //描述
                'model' => 13, //列表绑定模型
                'model_sub' => 13, //子文档绑定模型
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
                'status' => 1, //数据状态
                'icon' => '' //分类图标
            );
            $cate_id = M('down_category')->add($data);
            unset($data);
            if (is_numeric($cate_id)) {
                S($cate_key, $cate_id);
                return $cate_id;
            }
        }
        return 0;
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
    protected function getPicPath($url, $dir = "")
    {
        if (empty($url)) return null;
        $file = time() . "_" . rand() . rand() . pathinfo($url, PATHINFO_BASENAME);
        return $dir . $file;
    }

    /**
     * 描述：抓取数据插入数据库中
     * @param $list
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function insertDate($list, $temp, $type)
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
        $main = array_shift($mytables);
        $parent_cate_name = '360游戏'; //父类型
        $flag_name = '360_game'; //父类型标识
        //循环数据
        foreach ($list as $v) {
            //主键为0舍弃
            if ($v['id'] == 0) {
                continue;
            }
            if (!$v['packageName']) {
                continue;
            }
            $flag = 0;//标志位，用于标识更新还是新增（0标识新增，1标识更新)
            $v['package_name_flag'] = str_replace('.', '_', $v['packageName']);
            //不插入id重复的数据(实际生产环节可以考虑缓存)
            $data = array();

            if ($temp == 'idongdong') {
                $data['b.package_name'] = $v['packageName'];
                $data['b.company_id'] = $this->getCompanyId();
            } else if ($temp == 'afs') {
                $data['title'] = trim($v['name']);
            } else if ($temp == '7230') {
                if ($type == "1") {
                    $data['title'] = trim($v['name']);
                    $parent_cate_name = "360软件";
                    $flag_name = "360_software";
                } else {
                    $v['name'] = trim($v['name']) . '360版';
                    // $data['title'] = trim($v['name']);  //7230分维度插入数据
                    $data['b.package_name'] = $v['packageName']; //根据包名判断

                }
                $pic_dir = "/ApiUploads/360/"; //设置图片下载目录
                $file = $this->getPicPath($v['iconUrl'], $pic_dir);
                if (down_pic($v['iconUrl'], $file)) $v['iconUrl'] = $file;
                if (!empty($v['screenshotsUrl'])) { //多图预览
                    foreach ($v['screenshotsUrl'] as &$value) {
                        $file = $this->getPicPath($value, $pic_dir);
                        if (down_pic($value, $file)) $value = $file;
                    }
                }
            }

            $category_id = explode(':', $v['categoryName']); //获取分类
            $v['screenshotsUrl'] = explode(',', $v['screenshotsUrl']); //分割成数组形式
            //$data['name'] =trim($v['package_name']);
            // $data['category_id'] = $this->getCategory($v['type']);
            $down_id = M('down_dmain')->alias('b')->join(' __DOWN__ a ON b.id=a.id')->where($data)->field('a.id')->find();
            $down_id = $down_id['id'];
            $cate_id1 = $this->getCategory($category_id[1], $parent_cate_name, $flag_name);
            $game_data = array(
                'uid' => '', //用户id
                'name' => $v['package_name_flag'], //标识
                'title' => trim($v['name']), //卡号名称
                'category_id' => $this->getCategory($category_id[1], $parent_cate_name, $flag_name), //所属分类
                'description' => $v['brief'], //简介
                'root' => '', //根节点
                'pid' => '', //所属id
                'model_id' => 13, //内容模型id
                'type' => 2, //内容类型,主题类型
                'position' => '', //推荐位
                'link' => '', //外链
                'cover_id' => $this->pic($v['screenshotsUrl'][0]), //封面横图
                'display' => 1, //可见性
                'deadline' => time(), //截止时间
                'attach' => '', //附件数量
                'view' => $v['downloadTimes'], //点击总数
                'comment' => '', //评论数
                'extend' => '', //扩展统计字段
                'level' => '', //优先级
                'create_time' => time(), //创建时间
                'update_time' => time(), //更新时间
                'status' => 2, //数据状态  0，禁止。 1,正常
                'title_pinyin' => '', //标题首字母
                'path_detail' => '', //静态文件路径
                'abet' => '', //好
                'argue' => '', //差
                'smallimg' => $this->pic($v['iconUrl']), //logo图
                'seo_title' => $v['name'], //seo标题
                'seo_keywords' => $v['tag'], //seo关键字
                'seo_description' => '', //seo描述
                'hits_month' => '', //月点击数
                'hits_week' => '', //周点击数
                'hits_today' => '', //天点击数
                'date_month' => time(), //月点击数开始时间
                'date_week' => time(), //周点击数开始时间
                'date_today' => time(), //天点击数开始时间
                'audit' => 0, //审核 0,未审核。 1,审核
                'old_id' => '', //老数据id
                'category_rootid' => get_category_by_model($cate_id1, 'rootid', 'down_category'), //根目录id
                'home_position' => '', //全站推荐位
                'vertical_pic' => $this->pic($v['screenshotsUrl'][0]), //封面竖图
            );

            //处理多图字段
            if (!empty($v['screenshotsUrl'])) {
                $picid = array();
                foreach ($v['screenshotsUrl'] as $value) {
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
            unset($game_data);
            //循环处理其他表
            foreach ($mytables as $t) {
                if ($t == 'down_dmain') {
                    $downdmain_data = array(
                        'id' => $down_id,
                        'content' => $v['description'], //介绍
                        'version' => $v['versionName'], //包版本。例如：v1.2.3
                        'system_version' => $v['minVersion'], //平台版本。例如：v1.2.3
                        'package_name' => $v['packageName'], //apk源包的包名
                        'font' => '', //标题字体
                        'font_color' => '', //标题颜色
                        'size' => intval($v['apkSize'] / 1024), //文件大小
                        'sub_title' => $v['name'], //副标题
                        'conductor' => '', //导读
                        'system' => '1', //平台
                        'score' => $v['rating'], //用户评分
                        'company_id' => $this->getCompanyId() //厂商
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
                unset($downdmain_data);
                //down_address表插入前的特殊处理
                if ($t == 'down_address') {
                    $downaddress_data = array(
                        'id' => '',
                        'did' => $down_id, //下载id
                        'name' => $v['name'], //下载名称
                        'url' => $v['downloadUrl'], //下载地址
                        'hits' => '', //点击数
                        'site_id' => $this->getPresetSite(), //预定义站点id
                        'special' => '', //链接类型id
                        'update_time' => time(), //更新时间
                        'old_id' => '' //老数据id
                    );
                    $data = array();
                    $data['did'] = $down_id;
                    if ($temp == 'idongdong') {
                        $site_id = $this->getPresetSite();
                        $data['site_id'] = array('in', array('1', '2', '7', $site_id));
                    } else {
                        $data['url'] = $v['downloadUrl'];
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
                            $import_data['content'] = '【360】 原有游戏/软件[' . trim($v['name']) . ']新增下载扩展链接：' . $v['downloadUrl'];
                        } else {
                            $import_data['content'] = '【360】 新增：[' . trim($v['name']) . ']游戏/软件';
                        }
                        M('api_import_log')->add($import_data);
                        $data['update_time'] = time();
                        M('down')->save($data);
                    } else {
                        if ($temp == 'idongdong') {
                            $data['id'] = $exist['id'];
                            $data['url'] = $v['downloadUrl'];
                            $data['site_id'] = $this->getPresetSite();
                            M('down_address')->save($data);
                            $import_data['did'] = $down_id;
                            $import_data['update_type'] = 1;
                            $import_data['update_time'] = time();
                            $import_data['content'] = '【360】 原有游戏/软件[' . trim($v['name']) . ']修改下载扩展链接：' . $v['downloadUrl'];
                            M('api_import_log')->add($import_data);
                        }
                        //$downaddress_data['id'] = $exist['id'];
                        //M('down_address')->save($downaddress_data);
                    }
                }
                unset($data, $downaddress_data);
            }
        }
    }

    /**
     * 描述：对外开放的主方法(由于多酷那边程序有BUG，时间段抓取也为全部抓取)
     * @param string $type **all为全部抓取数据**add为上次抓取时间到当前时间段数据抓取
     * admin.php?s=/DuokuSync/index/type/all.html
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function index($type = 'all', $temp = 'idongdong', $page = '')
    {
        //缓存key值（数据获取开始时间）
        $key = '360DataToCache';
        //更新类型
        switch ($type) {
            case 'add':
                $dateFrom = S($key) ? S($key) : strtotime('1972-1-1');
                break;
            case 'all':

            default:
                $dateFrom = strtotime('1972-1-1'); //默认开始时间
                break;
        }
        //API地址类型,获取参数
        $params = array();
        //开始时间
        $params['starttime'] = $dateFrom;
        //缓存截止时间
        S($key, time());
        //修改内存
        ini_set('memory_limit', '512M');
        //设置超时时间
        ini_set('max_execution_time', '0');
        if ($temp == "afs") {
            //接口地址
            //$params['url'] = "http://api.np.mobilem.360.cn/app/list";
            $params['url'] = "http://api.np.mobilem.360.cn/app/cpsgames";
            //渠道号
            $params['from'] = 'lm_115054';
            //类型 2表示游戏
            $params['type'] = '2';
            //获取数据
            $data =  $this->getDatas($params, 'afs', $page);
            return $data;
        } else if ($temp == "7230") {
            //接口地址
            $params['url'] = "http://api.np.mobilem.360.cn/app/list";
            //渠道号
            $params['from'] = 'lm_124383';
            //类型 2表示游戏
            $params['type'] = '2';
            //获取数据
            $this->getData($params, $temp); //获取游戏数据
            $params['type'] = '1';
            //获取数据
            $this->getData($params, $temp); //获取软件数据
        } else if ($temp == "idongdong") {
            //接口地址
            $params['url'] = "http://api.np.mobilem.360.cn/app/cpsgames";
            //渠道号
            $params['from'] = 'lm_130820';
            //类型 2表示游戏
            $params['type'] = '2';
            //获取数据
            $this->getData($params, $temp);
        }
        //添加内站链接
        //  $this->getPresetSite();
        //释放
        unset($params);
    }

    /*
     * 远程获取数据不插入直接放回数据
     */
    protected function getDatas($params = array(), $temp = 'afs', $page = '')
    {

        //参数为空或者不是数据，直接返回
        if (empty($params) || !is_array($params)) return '';
        //初始化
        $post_data = array();
        // 这里需要改,给post_data赋值
        $post_data['from'] = $params['from']; //渠道号
        $post_data['type'] = $params['type']; //类型（1为软件，2为游戏  由360api给的数据）
        //$post_data['starttime'] = $params['starttime']; //开始时间
        $post_data['starttime'] = time() - 3600 * 24;  //开始时间
        if ($temp == "afs") {
            $secret_key = 'da374e1be19b3f5a2b010cfccfc43c17';
        }
        $pageNum = intval($page) > 0 ? intval($page) - 1 : 0;
        /*$list = array();
        while (true) {
            $post_data['start'] = $pageNum * 100;
            $post_data['num'] = 100;
            unset($post_data['sign']);
            $post_data['sign'] = $this->getSign($post_data, $secret_key);
            $output = $this->curl($params['url'], $post_data);
            dump($output);
            if (empty($output['items'])) {
                break;
            }
            $list[$pageNum] = $output['items'];
            $pageNum++;
        }
        return $list;*/
        $post_data['start'] = $pageNum * 20;
        $post_data['num'] = 20;
        unset($post_data['sign']);
        $post_data['sign'] = $this->getSign($post_data, $secret_key);
        $output = $this->curl($params['url'], $post_data);
        if (!empty($output['items'])) {
            return $output;
        }else{
            return -1;
        }
    }

    /**
     * 描述：获取远程数据
     * @param array $params
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getData($params = array(), $temp)
    {
        //参数为空或者不是数据，直接返回
        if (empty($params) || !is_array($params)) return '';
        //初始化
        $post_data = array();
        // 这里需要改,给post_data赋值
        $post_data['from'] = $params['from']; //渠道号
        $post_data['type'] = $params['type']; //类型（1为软件，2为游戏  由360api给的数据）
        $post_data['starttime'] = $params['starttime']; //开始时间
        if ($temp == "afs") $secret_key = 'da374e1be19b3f5a2b010cfccfc43c17';
        if ($temp == "7230") $secret_key = '4111a6c478a1812b9ba92e5035ad18ea';
        if ($temp == "idongdong") $secret_key = '09b7a3a5d0fe9303d2a60eb76002c923';
        $pageNum = 0;
        $count = 0;
        echo '最开始的内存占用：' . memory_get_usage() . PHP_EOL . '********************' . PHP_EOL;
        //测试用
        //while ($pageNum <= 10) {
        while (true) {
            if ($temp == 'afs') {
                $post_data['start'] = $pageNum * 3;
                $post_data['num'] = 3;
            }
            if ($temp == 'idongdong') {
                $post_data['start'] = $pageNum * 300;
                $post_data['num'] = 300;
            }
            if ($temp == '7230') {
                $post_data['pagesize'] = 300;
                $post_data['page'] = $pageNum + 1;
            }
            unset($post_data['sign']);
            $post_data['sign'] = $this->getSign($post_data, $secret_key);
            $output = $this->curl($params['url'], $post_data);
            if ($output) {
                //获取list数据，不存在则停止
                $list = $output['items'];
                if (empty($list)) break;
                //数据插入
                $this->getPresetSiteDate($list, $temp, $post_data['type']);
                $count += count($list);
                echo 'down表插入 ' . $count . ' 条' . PHP_EOL;
            } else {
                break;
            }
            $pageNum++;
            if ($temp == '7230') sleep(5);
            if ($temp == 'afs') sleep(2);
            if ($temp == 'idongdong') sleep(2);
        }
        echo '********************' . PHP_EOL . '结束的内存占用：' . memory_get_usage() . PHP_EOL;
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
    protected function getCompanyId($company_name = '360')
    {
        if (empty($company_name)) return 0;
        $key = base64_encode($company_name);
        //测试 (测试的时候可以删除缓存，实际使用环境中，还是使用缓存)
        S($key, null);
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

    /**
     *描述：单图片导入处理（直接拷贝王维久游的函数）
     * @param $v
     * @return int|mixed
     * Author:王为
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
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
     * 描述：HTTP请求（代码直接拷贝下来，王维）
     * @param $url
     * @param $post_data
     * @return mixed
     * Author:王为
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function curl($url, $post_data)
    {

        //$headers = array("Accept:application/json",'Expect:','Transfer-Encoding:');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        unset($headers, $post_data, $url, $ch);
        return json_decode($output, true);
    }

    /**
     * 描述：获取360的sign值
     * @param string $url
     * @param string $secret_key
     * @return null
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getSign($data = array(), $secret_key = 'da374e1be19b3f5a2b010cfccfc43c17')
    {
        if (!is_array($data)) return null;
        ksort($data); //key值升序排序（重要，360接口提供获取sign值方法)
        $str = http_build_query($data); //生成http url格式
        $sign = md5($str . $secret_key); // 获取sign值（获取方法有360手游平台提出）
        return $sign;
    }
}
