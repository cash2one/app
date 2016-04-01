<?php
/**
 * User: lcz
 * Date: 2016/3/28
 * Time: 16:32
 * op  : 用于采集数据更新，插入，对比操作
 */
namespace Admin\Controller;

use Think\Controller;

class AfsUpdateController extends Controller
{
    /*
     * 360cps游戏列表获取
     */
    public function index()
    {
        $game360Obj = A('Game360Sync');
        $page = 1;
         if (!S('page_'.$page)) {
            echo 11;
            $list = $game360Obj->index($type = 'all', $temp = 'afs', $page);
            $list_items = $list['items'];
            S('page_'.$page, $list_items, 3600);
         } else {
             echo 22;
           $list_items = S('page_'.$page);
         }
        foreach ($list_items as $k => $v) {
            $res = $this->checkGame($list_items[$k]);
            $list_items[$k]['similars'] = $res;
        }
        //游戏所有分类
        $game_category = $this->gameCategory();
        $this->assign('game_category', $game_category);
        $this->assign('list_items', $list_items);

        $this->display();
    }
    
    /*
     *
     */
    public function redirectOp()
    {
        if (I('post.')) {
            $category_id = I('game_category');
            $old = I('old_id');
        }
    }

    //更新操作
    public function gameUpdate()
    {
        $new_id = I('get.new_id');
        $old_id = I('get.old_id');
        //$new_arr = $this->id2arr($new_id);
        $this->redirect('Down/edit',array('id'=>$old_id,'new_id'=>$new_id,'afs_update'=>1,'page'=>1));
    }

    //根据id得到数组
    public function id2arr($id, $page = 1)
    {
        $list_items = S('page_'.$page);
        $res_arr = array();
        foreach($list_items as $k=>$v){
            if($v['id'] == $id){
                $res_arr = $v;
            }
        }
        return $res_arr;
    }

    //数组转化操作
    public function arr2arr()
    {

    }

    /*
     * 游戏所有分类
     */
    public function gameCategory()
    {
        $where['pid'] = array('in', '1,2');
        return M('down_category')->field('id,title,name')->where($where)->select();
    }

    /*
     *  数据插入操作
     */
    protected function gameInsert($arr = array(), $type = 'game')
    {
        dump($arr);
        //获取分类信息id
        $category_id = $this->categoryFrom($arr['categoryName'], $arr['tag']);
        $pic_arr = explode(',', $arr['screenshotsUrl']);
        $game_data = array(
            'uid' => '', //用户id
            'name' => '',
            'title' => trim($arr['name']), //卡号名称
            'category_id' => $category_id, //所属分类
            'description' => $arr['brief'], //简介
            'root' => '', //根节点
            'pid' => '', //所属id
            'model_id' => 13, //内容模型id
            'type' => 2, //内容类型,主题类型
            'position' => '', //推荐位
            'link' => '', //外链
            'cover_id' => $this->pic($pic_arr['screenshotsUrl'][0]), //封面横图
            'display' => 1, //可见性
            'deadline' => time(), //截止时间
            'attach' => '', //附件数量
            'view' => $arr['downloadTimes'], //点击总数
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
            'smallimg' => $this->pic($arr['iconUrl']), //logo图
            'seo_title' => $arr['name'], //seo标题
            'seo_keywords' => $arr['tag'], //seo关键字
            'seo_description' => '', //seo描述
            'hits_month' => '', //月点击数
            'hits_week' => '', //周点击数
            'hits_today' => '', //天点击数
            'date_month' => time(), //月点击数开始时间
            'date_week' => time(), //周点击数开始时间
            'date_today' => time(), //天点击数开始时间
            'audit' => 0, //审核 0,未审核。 1,审核
            'old_id' => '', //老数据id
            'category_rootid' => get_category_by_model($category_id, 'rootid', 'down_category'), //根目录id
            'home_position' => '', //全站推荐位
            'vertical_pic' => $this->pic($pic_arr['screenshotsUrl'][0]), //封面竖图
        );
        $down_id = M('down')->add($game_data);
        if (!$down_id) {
            return false;
        }
        if ($type == 'game') {
            $downdmain_data = array(
                'id' => $down_id,
                'content' => $arr['description'], //介绍
                'version' => $arr['versionName'], //包版本。例如：v1.2.3
                'system_version' => $arr['minVersion'], //平台版本。例如：v1.2.3


                'font' => '', //标题字体
                'font_color' => '', //标题颜色
                'size' => intval($arr['apkSize'] / 1024), //文件大小
                'sub_title' => $arr['name'], //副标题
                'conductor' => '', //导读
                'system' => '1', //平台
                'score' => round($arr['rating'] / 2), //用户评分
                'company_id' => $this->getCompanyId($arr['developer']) //厂商
            );
            $down_dmain_id = M('down_dmain')->add($downdmain_data);
        } else {
            //soft
        }
        $downaddress_data = array(
            'id' => '',
            'did' => $down_id, //下载id
            'name' => $arr['name'], //下载名称
            'url' => $arr['downloadUrl'], //下载地址
            'hits' => '', //点击数
            'site_id' => $this->getPresetSite(), //预定义站点id
            'special' => '', //链接类型id
            'update_time' => time(), //更新时间
            'old_id' => '' //老数据id
        );
        M('down_address')->add($downaddress_data);
    }

    /*
     * 检测相同相近类型名字的游戏
     */
    protected function checkGame($arr = array())
    {
        if (!is_array($arr) || empty($arr)) {
            return false;
        }
        //dump($arr);
        if (mb_strlen($arr['name'], 'utf-8') <= 3) {
            $name = mb_substr($arr['name'], 0, 2, 'utf-8');
        } else {
            $name = mb_substr($arr['name'], 0, 3, 'utf-8');
        }
        $where['a.title'] = array('like', '%' . $name . '%');
        $result = M('down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id ', 'left')->where($where)->field('a.id,a.title,a.update_time,a.smallimg,a.category_id,a.edit_id,b.version,b.size,b.system,b.data_type,b.update_info,b.bag')->select();
        //dump($result);
        if (!empty($result)) {
            return $result;
        } else {
            return false;
        }
    }

    /*
     * 返回所属分类id所属分类
     * @parmars: $category_name : 分类名城 ; $tag : 标签
     * return : category_id;
     */
    public function categoryFrom($category_name, $tag)
    {
        preg_match('/[:||-]/', $category_name, $flags);
        if ($flags[0] === ':') {
            $category_arr = explode(':', $category_name);
        } else {
            $category_arr = explode('-', $category_name);
        }
        $tag_flag = (stripos($tag, '网游') !== false || stripos($tag, 'RPG网游') !== false) ? true : false;  //根据标签判断是网游还是单机

        /* $category_dj = M('down_category')->field('id,name,title')->where(array('pid' => 1))->select();
         $category_wy = M('down_category')->field('id,name,title')->where(array('pid' => 2))->select();
         dump($category_dj);
         dump($category_wy);*/
        if ($category_arr[1] == '网络游戏') {
            return 2;
        } else if ($tag_flag === true) {
            switch ($category_arr[1]) {
                case '角色扮演':
                    return 15;
                    break;
                case '策略':
                    return 12;
                    break;
                case '经营策略':
                    return 12;
                    break;
                case '休闲益智':
                    return 12;
                    break;
                case '竞技游戏':
                    return 14;
                    break;
                case '体育竞速':
                    return 14;
                    break;
                case '体育':
                    return 14;
                    break;
                case '格斗':
                    return 14;
                    break;
                case '动作冒险':
                    return 14;
                    break;
                case '动作':
                    return 14;
                    break;
                case '飞行射击':
                    return 14;
                    break;
                case '其他':
                    return 15;
                    break;
                case '塔防':
                    return 13;
                    break;
                case '棋牌天地':
                    return 11;
                    break;
                case '养成':
                    return 15;
                    break;
            }
        } else {
            switch ($category_arr[1]) {
                case '角色扮演':
                    return 6;
                    break;
                case '策略':
                    return 4;
                    break;
                case '经营策略':
                    return 4;
                    break;
                case '休闲益智':
                    return 4;
                    break;
                case '竞技游戏':
                    return 5;
                    break;
                case '体育竞速':
                    return 5;
                    break;
                case '体育':
                    return 5;
                    break;
                case '格斗':
                    return 3;
                    break;
                case '动作冒险':
                    return 3;
                    break;
                case '动作':
                    return 3;
                    break;
                case '飞行射击':
                    return 7;
                    break;
                case '其他':
                    return 6;
                    break;
                case '塔防':
                    return 8;
                    break;
                case '棋牌天地':
                    return 9;
                    break;
                case '养成':
                    return 6;
                    break;
            }
        }

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
    public function pic($v)
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
}
