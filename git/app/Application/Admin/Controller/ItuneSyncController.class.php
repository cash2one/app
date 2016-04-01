<?php
// +----------------------------------------------------------------------
// | 描述:苹果数据导入文件（其实这些导入可以整理成一个基出的导入类，再继承）
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-4-8 上午10:20    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;

use Think\Controller;

/**
 * 描述：苹果数据导入控制器类
 * Class ItuneSyncController
 * @package Admin\Controller
 * Author:谭坚
 * Version:1.0.0
 * Modify Time:
 * Modify Author:
 */
class ItuneSyncController extends Controller
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
    protected function getPresetSite($site_name = '苹果官方下载', $site_url = 'http://www.apple.com/cn/itunes/download/')
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
    protected function getCategory($cate_name = '', $parent_cate_name = 'itunes游戏', $name = 'itunes_game')
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
     * 描述：抓取数据插入数据库中
     * @param $list
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function insertDate($list)
    {
        if (!is_array($list) || empty($list['title'])) {
            return false;
        }
        $mytables = $this->tables;
        //实例化模型
        foreach ($mytables as $t) {
            ${$t . 'Model'} = M($t);
        }
        //取出主表名，并排除
        $main = array_shift($mytables);
        //循环数据
        $v = $list;
        $flag = 0; //标志位，用于标识更新还是新增（0标识新增，1标识更新)
        $data = array();
        $data['title'] = trim($v['title']);
        $data['b.system'] = 2;
        $down_id = M('down_dmain')->alias('b')->join(' __DOWN__ a ON b.id=a.id')->where($data)->field('a.id')->find();
        $down_id = $down_id['id'];
        $game_data = array(
            'uid' => '', //用户id
            'name' => '', //标识
            'title' => trim($v['title']), //卡号名称
            'category_id' => $this->getCategory('苹果游戏'), //所属分类
            'description' => $v['description'], //简介
            'root' => '', //根节点
            'pid' => '', //所属id
            'model_id' => 13, //内容模型id
            'type' => 2, //内容类型,主题类型
            'position' => '', //推荐位
            'link' => '', //外链
            'cover_id' => '', //封面横图
            'display' => 1, //可见性
            'deadline' => time(), //截止时间
            'attach' => '', //附件数量
            'view' => '', //点击总数
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
            'smallimg' => $this->pic(str_replace('"', '/', $v['logo'])), //logo图
            'seo_title' => $v['seo_title'], //seo标题
            'seo_keywords' => $v['seo_keywords'], //seo关键字
            'seo_description' => $v['seo_description'], //seo描述
            'hits_month' => '', //月点击数
            'hits_week' => '', //周点击数
            'hits_today' => '', //天点击数
            'date_month' => time(), //月点击数开始时间
            'date_week' => time(), //周点击数开始时间
            'date_today' => time(), //天点击数开始时间
            'audit' => 0, //审核 0,未审核。 1,审核
            'old_id' => '', //老数据id
            'category_rootid' => '', //根目录id
            'home_position' => '', //全站推荐位
            'vertical_pic' => '', //封面竖图
        );

        //处理多图字段
        if (!empty($v['pics'])) {
            $picid = array();
            foreach ($v['pics'] as $value) {
                $value = str_replace('"', '/', $value);
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
       // $this->setTagsId($v['tags'], $down_id); //设置标签
        unset($game_data);
        //循环处理其他表
        foreach ($mytables as $t) {
            if ($t == 'down_dmain') {
                $downdmain_data = array(
                    'id' => $down_id,
                    'content' => $v['introduce'], //介绍
                    'version' => $v['version'], //包版本。例如：v1.2.3
                    'package_name' => '', //apk源包的包名
                    'font' => '', //标题字体
                    'font_color' => '', //标题颜色
                    'size' => $v['size'], //文件大小
                    'sub_title' => '', //副标题
                    'conductor' => '', //导读
                    'system' => '2', //平台
                    'device' => $v['device'],
                    'score' => '', //用户评分
                    'keytext' => $v['special'],
                    'language' => '4',
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
                    'name' => $v['title'], //下载名称
                    'url' => $v['file'], //下载地址
                    'hits' => '', //点击数
                    'site_id' => strstr($v['file'], '.ipa') ? $this->getPresetSite('苹果本地下载', 'http://dl.anfensi.com/ipa/') : $this->getPresetSite(), //预定义站点id
                    'special' => '', //链接类型id
                    'update_time' => time(), //更新时间
                    'old_id' => '' //老数据id
                );
                $data = array();
                $data['did'] = $down_id;
                $data['url'] = $v['file'];
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
                        $import_data['content'] = '【苹果】 原有游戏[' . trim($v['title']) . ']新增下载扩展链接：' . $v['file'];
                    } else {
                        $import_data['content'] = '【苹果】 新增：[' . trim($v['title']) . ']游戏';
                    }
                    M('api_import_log')->add($import_data);
                    $data['update_time'] = time();
                    M('down')->save($data);
                } else {
                    //$downaddress_data['id'] = $exist['id'];
                    //M('down_address')->save($downaddress_data);
                }
            }
            unset($data, $downaddress_data);
        }

    }

    /**
     * 描述：主运行方法
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function index()
    {
        //修改内存
        ini_set('memory_limit', '512M');
        //设置超时时间
        ini_set('max_execution_time', '0');
        //$this->deleteLocationAds(); //删除本地错误下载链接（此方法只适用于afs）
       // $this->deleteAddress(); //删除苹果加入其它链接地址（此方法只适用于afs）
        //获取数据
        $this->getData();
        echo '添加成功！';
    }

    /**
     * 描述：获取数据并入库(此数据由刘盼用火车头采集提供，用POST方式)
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getData()
    {

        $data = array();
        $data['title'] = I('title'); //获取标题
        $data['tags'] = I('tags'); //获取标签
        $data['version'] = I('version'); //版本
        $data['size'] =I('size'); //文件大小
        $data['description'] = trim(I('description')); //简介
        $data['device'] = I('device'); //支持设备
        $data['introduce'] = trim(I('introduce')); //介绍
        $data['seo_title'] = I('seo_title'); //seo标题
        $data['seo_keywords'] = I('seo_keywords'); //seo关键字
        $data['seo_description'] = I('seo_description'); //seo描述
        $data['logo'] = $this->getPicSrc(I('logo')); //logo图片
        $data['pics'] = $this->getPicSrc(I('pics'), 0); //预览多图
        $data['file'] = I('file'); //下载地址
        $data['special'] = I('special');
        /* 测试数据
        $data['title'] = "保卫萝卜2：天天向上"; //获取标题
        $data['tags'] = "塔防"; //获取标签
        $data['version'] = "1.2.3"; //版本
        $data['size'] = "80.81M"; //文件大小
        $data['description'] ="提供最新保卫萝卜2：天天向上免费下载, 百兆光纤下载iPhone版保卫萝卜2：天天向上和iPad版保卫萝卜2：天天向上"; //简介
        $data['device'] = "iPhone、iPod touch、iPad"; //支持设备
        $data['introduce'] = "<p>下载即送500宝石红包！！！！！！！！！！2015开年
惊喜有木有！！！！！！！！！

保卫萝卜2塔防新年大戏重量级更新，全新冒险“天天向上”正式开启，300个免费关卡随机挑战，300个
，真的是300个，您没看错，是300个免费关卡随机挑战！！！99层摩天大树邀千万玩家试比天高，为您
的新年添喜，愿您羊年更上一层楼！！！

惊喜，惊险，刺激，多变，小伙伴们天天向上，斗智斗勇，其乐无穷，您还等什么？

同时为喜迎新年，所有萝卜英雄全部特价登场，等候您的召唤！

切记，爬高有风险，挑战须谨慎，如发现运气不佳，请及时调整状态多喝水，多活动，切勿与手机较真
！大过年的，别玩大了哈~~

欲知更新详情，没二话，下载走起！！！~~~

—————————————————————————————————

以下内容来自2014年：

发布当日6小时登上中国区总榜TOP1，持续榜首21天！保卫萝卜1+2总下载已接近2亿，平均每2个智能手
机用户就有1个人玩过保卫萝卜！

9个主题，155个关卡，奇趣刺激的冒险之旅等待您的启程！
20个炮塔陆续登场，等候您的召唤！
465个特殊任务，全新的紫水晶任务奖杯，丰厚的宝石奖励等您来拿！
萝卜家族又添神秘新成员，他们将与您并肩作战！
“全体减速”、“全体冰冻”、“必杀炸弹”等魔法特技悉数登场，助您收获金萝卜！
全新模式“双出口”、“怪物堡垒”、“随机炮塔”、“终极BOSS战”开启，更多惊奇等您探索！
接入社交圈子，和好友一起拼进度、比排行！
同步ICloud保存进度，接入GameCenter比拼成就，还等什么？快快下载吧！

《保卫萝卜》游戏历程

2012年8月，《保卫萝卜》1代正式登录AppStore开放下载。
2012年9月，中国区TOP1，亚洲各市场TOP3，被超过120个国家推荐为热门游戏！
2012年12月，荣获苹果官方AppStore年度最佳游戏提名！
2013年2月，荣获2012年度最佳手机单机游戏大奖！
2013年5月，《保卫萝卜》1代IOS平台下载突破5000万。
2013年7月，荣获“手游奥斯卡”星耀最好评年度大奖！
2013年11月，《保卫萝卜2：极地冒险》完成开发提交AppStore审核。
2013年11月22日，《保卫萝卜2：极地冒险》全球免费开放下载！6小时后登顶！持续21一天TOP1，持平
萝卜1创下的榜首记录！
2014年9月，保卫萝卜系列走过整整2个年头，感谢所有支持萝卜的玩家和粉丝们，并期待新的主题给您
带来更多的快乐！
2014年11月，开发全新主题“地下庄园”！
2014年12月5日，保卫萝卜开发商“飞鱼科技”成功在香港主板挂牌上市（HK.01022）

新浪微博：weibo.com/cairot
客服QQ：1811100060
客服邮箱：kefu@luobo.cn
微信公众：CairotTeam</p>

【特别】：

					<p>苹果玩家出现关卡与炮塔不匹配、频繁闪退等异常情况
，我们感到非常抱歉，这500宝石作为补偿仅仅是我们的一份心意，感谢您的一路陪伴与包容，我们继续
努力！

修复新浪微博客户端分享不送宝石的bug
生命星增加恢复购买功能，可立刻找回无限生命星、萝卜角色
修复部分内存过低的苹果设备造成载入天天向上关卡崩溃的bug
保护气球楼层可以使用icloud功能同步数据</p>
"; //介绍
        $data['seo_title'] = "保卫萝卜2：天天向上_保卫萝卜2：天天向上iphone版免费下载"; //seo标题
        $data['seo_keywords'] = "保卫萝卜2：天天向上_保卫萝卜2：天天向上iphone版免费下载"; //seo关键字
        $data['seo_description'] = "保卫萝卜2：天天向上_保卫萝卜2：天天向上iphone版免费下载"; //seo描述
        $data['logo'] = '<img width=\'140\' height=\'140\' xsrc="http://img.itools.hk/js/img/icon_default_7878.jpg" src="Uploads/Picture/C/Logo/689888397-175lgqy12wlo0c57.jpg"><'; //logo图片
        $data['pics'] = 'ref="Uploads/Picture/C/5725fv1qahpe2zh.jpg"><img src="Uploads/Picture/C/5725fv1qahpe2zh.jpg"><span></span></a>
											<a href="Uploads/Picture/C/5825w5tnb1rnqah.jpg"><img src="Uploads/Picture/C/5825w5tnb1rnqah.jpg"><span></span></a>
											<a href="Uploads/Picture/C/5825a3bft10hjvy.jpg"><img src="Uploads/Picture/C/5825a3bft10hjvy.jpg"><span></span></a>
											<a href="Uploads/Picture/C/58250wsshptxa5k.jpg"><img src="Uploads/Picture/C/58250wsshptxa5k.jpg"><span></span></a>
											<a href="Uploads/Picture/C/582524vmehjlqdv.jpg"><img src="Uploads/Picture/C/582524vmehjlqdf.jpg"><span></span></a>'; //预览多图
        $data['file'] = "https://itunes.apple.com/cn/app/bao-wei-luo-bo2-tian-tian/id689888397?
mt=8&uo=4&VersionID=811964375&ChannelID=repos_details"; //下载地址
        $data['size'] = intval( $data['size']) * 1024; //文件大小

        $data['logo'] = $this->getPicSrc( $data['logo']); //logo图片
        $data['pics'] = $this->getPicSrc( $data['pics'],0); //预览多图
        dump($data);
        die();
        */
        $this->insertDate($data);
    }

    /**
     * 描述：获取图片地址（主要截取$str中的img标签中的src地址）
     * @param $str
     * @param int $only
     * @return null
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function  getPicSrc($str = '', $only = 1)
    {
        if (empty($str)) return null;
        if ($only)
            preg_match('/<img.+\s+src\s*=\s*"?(.+.(jpg|gif|bmp|bnp|png|svg))"?.+>/iU', $str, $match); //获取第一个匹配的src(用于匹配logo)
        else
            preg_match_all('/<img.+src\s*=\s*"?(.+.(jpg|gif|bmp|bnp|png|svg))"?.+>/iU', $str, $match); //获取所有匹配的scr(用于匹配全部图片)
        return $match[1];
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
    protected function getCompanyId($company_name = '苹果')
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
     * 描述：设置标签
     * @param string $tags
     * @param string $parent_tags
     * @param string $down_id
     * @param string $type 默认为下载类型
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function setTagsId($tags = '', $down_id = '', $type = 'down', $parent_tags = '苹果游戏')
    {
        $tags_key = 'ITUNESTAGS';
        if (!empty($parent_tags) && !S($tags_key)) {
            $data = array();
            $data['name'] = $parent_tags;
            $tag_cateid = M('tags_category')->where($data)->field('id')->find(); //判断分类标签是否存在
            $tag_cateid = $tag_cateid['id']; //获取分类标签id
            if (!is_numeric($tag_cateid)) {
                $data['title'] = $parent_tags;
                $data['create_time'] = time();
                $data['update_time'] = time();
                $data['status'] = 1;
                $tag_cateid = M('tags_category')->add($data); //获取分类标签id
            }
           S($tags_key,$tag_cateid);
        }
        $tag_cateid = S($tags_key);
        unset($data);
        /*
        if($tag_cateid){
            $data['name'] = 'dmain'; //模型名称
            $rs =  M('Model')->where($data)->field('id,tags_category')->find(); //获取模型分类
            $rs['tags_category'] = empty($rs['tags_category']) ? $tag_cateid : $rs['tags_category'] . ',' . $tag_cateid; //获取标签标识
            if(is_numeric($rs['id'])) M('Model')->save($rs);  //为模型挂载标签标识
        }
        */
        if (!empty($tags) && !empty($down_id)) {
            $tags_arr = explode(',', $tags); //标签切分为数组
            foreach ($tags_arr as $val) //循环里面写sql效率需要考虑（为了导入暂时不考虑，以后整合）
            {
                unset($data); //释放数据
                $data['name'] = $val;
                $data['category'] = $tag_cateid;
                $tagid = M('tags')->where($data)->field('id')->find(); //判断标签是否存在
                $tagid = $tagid['id'];
                if (!is_numeric($tagid)) {
                    $data['title'] = $val;
                    $data['depth'] = 1;
                    $data['list_row'] = 10;
                    $data['display'] = 1;
                    $data['create_time'] = time();
                    $data['update_time'] = time();
                    $data['status'] = 1;
                    $tagid = M('tags')->add($data); //获取标签id
                }
                unset($data); //释放数据
                $data['tid'] = $tagid;
                $data['did'] = $down_id;
                $data['type'] = trim($type);
                $tag_mapid = M('tags_map')->where($data)->field('id')->find(); //判断映射关系是否存在
                $tag_mapid = $tag_mapid['$tag_mapid'];
                if (!is_numeric($tag_mapid)) {
                    $data['create_time'] = time();
                    $data['update_time'] = time();
                    M('tags_map')->add($data); //插入映射关系
                }
            }
        }
    }

    /**
     * 描述：此方法只针对afs
     * @param string $site_id 苹果预定义站点
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function deleteAddress($site_id = '5,6')
    {
        $rs = M('down_address')->field('did')->group('did')->having('count(did)>1')->select();
        if(is_array($rs) && !empty($rs))
        {
            foreach($rs as $val)
            {
                $did[] = $val['did'];
            }
            $data['did'] = array('in',$did);
            $data['site_id'] = array('in',$site_id);
            var_dump($data);
            M('down_address')->where($data)->delete();
        }
    }

    /**
     * 描述：删除非本地地址（只适用于anfs）
     * @param string $site_id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function deleteLocationAds($site_id = '1',$url = 'dl.anfensi.com')
    {
        $data = array();
        $data['site_id'] = $site_id;
        $data['url'] = array('notlike','%' .$url .'%');
        M('down_address')->where($data)->delete();
    }

} 