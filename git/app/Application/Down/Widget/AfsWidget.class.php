<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/2
 * Time: 10:16
 */

namespace Down\Widget;

use Down\Controller\BaseController;

class AfsWidget extends BaseController
{

    /**
     * @Author 肖书成
     * @time 2015/3/2创建
     * @param $id
     * @Comments 下载详情页右边 游戏评测，游戏礼包，游戏攻略，游戏新闻，相同厂商，相似厂商 数据的调用
     */

    public function detailRight($id, $cid, $category, $soft = false)
    {

        //获取产品标签
        $tid = get_tags($id, 'Down', 'product')[0]['id'];//产品标签
        $tagsId = array_column(get_tags($id, 'Down'), 'id');//标签
        $test = array();
        $package = array();
        $server = array();
        if (!empty($tid)) {
            //游戏评测
            $test = $this->article('b.category_id = 184 AND a.type="document" AND a.tid = ' . $tid);
            //游戏礼包
            $package = array_filter(array_merge($package, (array)M('product_tags_map')->alias('a')->field('b.*')->join('__PACKAGE__ b ON a.did = b.id')->where('b.category_id = 1 AND a.type="package" AND a.tid = ' . $tid)->order('b.id DESC')->select()));
            //游戏开服表
            $server = array_filter(array_merge($server, (array)M('product_tags_map')->alias('a')->field('b.*,c.*')->join('__PACKAGE__ b ON a.did = b.id')->join('__PACKAGE_PARTICLE__ c ON a.did = c.id')->where('b.category_id = 4 AND a.type="package" AND a.tid = ' . $tid)->order('b.id DESC')->select()));
            //游戏攻略
            $strategy = $this->article('b.category_id = 181 AND a.type="document" AND a.tid = ' . $tid, false, 'b.id DESC');
            if (count($strategy) < 10) {
                $strategy = array_filter(array_merge((array)$strategy, (array)$this->article('b.category_id = 81 AND a.type="document" AND a.tid = ' . $tid, false, 'b.id DESC')));
            }

            //游戏新闻
            $news = $this->article('b.category_id = 182 AND a.type="document" AND a.tid = ' . $tid, false, 'b.id DESC', 5);
        }

        if (!$soft) {
            //游戏礼包
            if (count($package) < 5) {
                $package = array_filter(array_merge($package, (array)M('package')->where('category_id = 1')->limit(6)->order('id DESC')->select()));
            }
            $package = array_unique_fb($package);//去重

            //游戏开服
            if (count($server) < 5) {
                $server = array_filter(array_merge($server, (array)M('package')->alias('a')->join('__PACKAGE_PARTICLE__ b ON a.id=b.id')->where('a.category_id = 4')->limit(6)->order('a.id DESC')->select()));
            }

            $server = array_unique_fb($server);
        }

        //相同厂商游戏（相同厂商）
        if (!$soft) {
            if ($cid != '0') {
                $company = M('Company')->field('id,name,path,status')->where('id = ' . $cid)->find();
                if ($company) {
                    $company['url'] = C('STATIC_URL') . '/' . (substr($company['path'], 0, 1) == '/' ? substr($company['path'], 1) : $company['path']);
                }
                $companyGame = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.id != ' . $id . ' AND b.data_type = 1 AND b.rank != 0 AND b.company_id = ' . $cid)->limit(6)->order('a.abet DESC')->select();
                $csNum = count($companyGame);
//                if($csNum<6){
//                    $companyGame = array_filter(array_merge((array)$companyGame,(array)M('Down')->field('id,title,smallimg')->where('status = 1 AND category_rootid IN(1,2) AND id !='.$id)->order('update_time')->limit(6-$csNum)->select()));
//                }
            } else {
//                $companyGame = M('Down')->field('id,title,smallimg')->where('status = 1 AND category_rootid IN(1,2) AND id !='.$id)->order('update_time DESC')->limit(6)->select();
            }

        }


        //相同标签游戏（相似游戏）
        $tagGame = array();
        if (!empty($tagsId)) {
            $where = '(a.tid = ' . implode(' OR a.tid = ', $tagsId) . ') AND ';
            $tagGame = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.abet')->join('__DOWN__ b ON a.did = b.id')->join('__PRODUCT_TAGS_MAP__ c ON b.id = c.did')->where($where . 'a.type="down" AND b.id !=' . $id . ' AND b.status = 1 AND b.category_id = ' . $category . ' AND c.type = "down"')->order('b.abet DESC')->group('c.tid')->limit(6)->select();

            $tagGameCount = count($tagGame);
            if ($tagGameCount < 6) {
                $tagGame1 = M('tags_map')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__PRODUCT_TAGS_MAP__ c ON b.id = c.did')->where($where . 'a.type="down" AND b.id !=' . $id . ' AND b.status = 1 AND c.type = "down"')->order('b.abet DESC')->group('c.tid')->limit(6)->select();
                $tagGame = array_filter(array_merge((array)$tagGame, (array)$tagGame1));
            }

        }

        $tagGameCount = count($tagGame);
        if ($tagGameCount < 6) {
            $tagGame1 = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->where('a.type = "down" AND b.id != ' . $id . ' AND b.status = 1 AND b.category_id = ' . $category)->order('b.abet DESC')->group('a.tid')->limit(6)->select();

            $tagGame = array_filter(array_merge((array)$tagGame, (array)$tagGame1));
        }

        //最新游戏 star
        $lists_ = M('down_category')->field('id,pid')->where(array('id' => $category))->find();
        $lists_c = M('down_category')->field('id,title,pid')->where(array('pid' => $lists_['pid']))->find();
        $title_flag = $lists_c['pid'] == 1 ? '最新单机' : '最新网游';
        $new_where['category_id'] = $category;
        $new_where['status'] = 1;
        $new_where['id'] = array('neq', $id);
        $new_arr = M('Down')->field('id,title,smallimg')->where($new_where)->order('id DESC')->limit('0,6')->select();
        //end

        $this->assign(array(
            'soft' => $soft,
            'test' => $test,
            'package' => $package,
            'server' => $server,
            'strategy' => $strategy,
            'news' => $news,
            'companyGame' => $companyGame,
            'tagGame' => $tagGame,
            'company' => $company,
            'new_arr' => $new_arr,
            'title_flag' => $title_flag
        ));

        $this->display('Widget/detailRight');
    }


    /**
     * 作者:肖书成
     * 描述:软件详情页右边（相关版本下载、标签、相似软件）
     */
    public function softRight($info)
    {
        //相关版本
        if ($info['productTags']) {
            $versions = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.version,c.size,c.soft_socre')->join('__DOWN__ b ON a.did = b.id')
                ->join('__DOWN_DSOFT__ c ON b.id = c.id')->where('b.status = 1 AND a.tid = ' . $info['productTags'][0]['id'] . ' AND a.type="down"')->order('b.update_time DESC')->limit(6)->select();
        }


        if ($info['tags']) {
            //标签（只取第一个）
            $tags = M('Tags')->field('id,name,title,description,icon')->where('id = ' . $info['tags'][0]['id'])->find();
            $tags['data'] = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.size,c.soft_socre,d.title cate')
                ->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DSOFT__ c ON b.id = c.id')
                ->join('__DOWN_CATEGORY__ d ON b.category_id = d.id')->where('a.tid = ' . $info['tags'][0]['id'] . ' AND b.status = 1 AND a.did != ' . $info['id'])
                ->order('a.sort ASC')->limit(8)->select();

            //相似软件
            $tid = array_column($info['tags'], 'id');
            $tid = implode(',', $tid);
            $repeat_ids = $repeat_id = array_column($tags['data'], 'id');
            $repeat_id[] = $info['id'];
            $repeat_id = implode(',', $repeat_id);

            $soft = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')
                ->join('__DOWN__ b ON a.did = b.id')->where('a.tid IN(' . $tid . ') AND b.status = 1 AND a.did NOT IN(' . $repeat_id . ')')
                ->order('a.sort ASC')->group('b.id')->limit(12)->select();
            $soft = (array)$soft;

            $row = (int)count($soft);

            if ($row < 12) {
                $repeat_id = array_column($soft, 'id');
                $repeat_id = $repeat_ids = array_merge($repeat_ids, $repeat_id);
                $repeat_id = implode(',', $repeat_id);
                $soft1 = M('Down')->where('status = 1 AND category_id = ' . $info['category_id'] . ' AND id NOT IN(' . $repeat_id . ')')->limit(12 - $row)->select();
                $soft = array_merge($soft, $soft1);
            }

        } else {
            //相似软件
            $soft = M('Down')->where('status = 1 AND category_id = ' . $info['category_id'] . ' AND id !=' . $info['id'])->limit(8)->select();
        }

        //加入最新软件 star
        $new_where['category_id'] = $info['category_id'];
        $new_where['status'] = 1;
        $new_where['id'] = array('neq', $info['id']);
        $new_soft = M('Down')->field('id,title,smallimg')->where($new_where)->order('id desc')->limit('0,6')->select();

        // end

        $this->assign(array(
            'versions' => $versions,
            'tags' => $tags,
            'soft' => $soft,
            'newsoft' => $new_soft
        ));

        $this->display('Widget/softRight');
    }

    //软件详情页导航 横条
    public function softNav()
    {
        $soft_id = I('soft');
        $jh_id = I('jh');

        if (!empty($soft_id)) {
            $soft_id = str_replace('|', ',', $soft_id);
            $soft = M('Down')->field('id,title')->where('id IN(' . $soft_id . ') and status = 1')->limit(30)->select();
        }

        if (!empty($jh_id)) {
            $jh_id = str_replace('|', '","', $jh_id);
            $jh = M('tags')->field('id,name,title')->where('name IN("' . $jh_id . '") and status = 1')->limit(30)->select();
        }

        $this->assign(array(
            'soft' => $soft,
            'jh' => $jh,
        ));

        $this->display('Widget/softNav');
    }

    //此方法仅供 detailRight 方法调用
    private function article($where = 1, $isfind = true, $order = 'b.id DESC', $row = 10)
    {
        if ($isfind === true) {
            return M('product_tags_map')->alias('a')->field('b.*')->join('__DOCUMENT__ b ON a.did = b.id')->where($where)->order('b.id DESC')->find();
        } else {
            return M('product_tags_map')->alias('a')->field('b.*')->join('__DOCUMENT__ b ON a.did = b.id')->where($where)->order('b.id DESC')->limit($row)->select();
        }
    }

    /**
     * @Author 肖书成
     * @time 2015/3/10创建
     * @Comments 其他平台同款游戏下载
     * @param int $id
     * @Param int $system
     * @Return null
     */

    public function otherDown($id, $system)
    {

        $tid = get_tags($id, 'down', 'product')[0]['id'];
        if (empty($tid) || empty($system)) {
            return;
        }
        $config = C('FIELD_DOWN_SYSTEM');
        unset($config[$system]);

        $value = array();
        foreach ($config as $k => $v) {
            $value[$k]['title'] = $v;
        }

        $data = M('productTagsMap')->alias('a')->join('__DOWN_DMAIN__ b ON a.did= b.id')->where("a.tid = $tid AND a.type='down' AND b.system != $system")->order('b.id DESC')->select();

        foreach ($data as $k => $v) {
            foreach ($value as $k1 => $v1) {
                if (empty($value[$k1]['id'])) {
                    if ($v['system'] == $k1) {
                        $value[$k1]['id'] = $v['id'];
                    }
                }
            }
        }

        $this->assign('other', $value);
        $this->display('Widget/otherDown');

    }

    /**
     * @user lcz
     * @time 2016年2月27日16:42:35
     * @op  文章右边栏目-最新游戏
     * @Return array
     */
    public function newGame()
    {
        //查找最新的，单机和网络游戏 ，必须是安卓平台，官方版；
        //找出所属，安卓单机，和网络游戏的子分类；
        $w['pid'] = array('between', array('1', '2'));
        $game_child = M('down_category')->where($w)->field('id,title')->select();
        /* //加上大分类
         $game_child[] = array('id' => '1');
         $game_child[] = array('id' => '2');*/
        //查出所有下载类游戏
        $temp = array(); //id
        foreach ($game_child as $v) {
            $temp[] = $v['id'];
        }
        $where['a.category_id'] = array('in', $temp);
        $where['b.system'] = '1';  //1是安卓
        $where['b.data_type'] = '1'; //1是官方
        $where['a.status'] = '1';
        $game_list = M('down')->alias('a')->join('LEFT JOIN ' . C('DB_PREFIX') . 'down_dmain as b ON a.id = b.id ')
            ->where($where)->field('a.id,a.category_id,a.title,b.size,a.smallimg')->order('a.id desc')->limit('0,10')->select();
        //加上所属分类
        foreach ($game_list as $k => $v) {
            foreach ($game_child as $kk => $vv) {
                if ($v['category_id'] == $vv['id']) {
                    $game_list[$k]['category_title'] = $vv['title'];
                }
            }
        }
        /* echo M()->getLastSql();
         prt($game_list);*/
        $this->assign(ranks, $game_list);
        $this->display(T('Down@afs/Widget/rankIndex'));
    }

    //排行榜
    public function rankCon($id, $type)
    {
        $id = empty($id) ? '102394' : $id;
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = split(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[] = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND tb1.id=' . $id)->order('create_time desc')->limit(50)->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }

        switch ($type) {
            case '0':
                $this->assign(ranks, array_slice($arrT, 0, 50));
                $this->display('Widget/rankDetail');
                return;
            case '1':
                $this->assign(ranks, array_slice($arrT, 0, 10));
                $this->display('Widget/rankCon1');
                return;
            case '2':
                $this->assign(ranks, array_slice($arrT, 0, 10));
                $this->display(T('Down@afs/Widget/rankIndex'));
                return;
        }

    }

    //排行榜
    public function paihang()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '4')));
        $this->display('Widget/phb');
    }

    public function detailPackage($id)
    {
        //获取该游戏的产品标签
        $tid = get_tags($id, 'down', 'product')[0]['id'];

        //查询该游戏的礼包个数
        if ($tid) {
            $count = M('productTagsMap')->alias('a')->join('__PACKAGE__ b ON a.did = b.id')->where("a.tid = $tid AND a.type= 'package' AND b.category_id = 1")->count();
        }

        //根据礼包个数判断该游戏是否存在礼包，然后页面赋值
        $this->assign('isPackage', $count > 0 ? $id : false);

        //模板调用
        $this->display('Widget/detailPackage');
    }

    /**
     * @user lcz
     * @time 2016年3月7日 14:32:40
     * @op  最新xx分类
     * @Return array
     */

    public function newTop()
    {
        //标题转换数组
        $arr_turn = array(
            '1' => '最新单机',
            '2' => '最新网游',
            '48' => '最新软件'
        );
        $where['id'] = array('in', '1,2,48');
        $arr_id = M('down_category')->field('id,title')->where($where)->select();
        foreach ($arr_id as $k => $v) {
            $arr_id[$k]['title'] = $arr_turn[$v['id']];
        }

        $this->assign('res', $arr_id);
        $this->display(T('Down@afs/Widget/newTop'));
    }

    /*
     * 处理函数top
     */
    protected function topOp($cid = '', $type = 'yx')
    {
        $cid = isset($cid) && $cid > 0 ? $cid : 1;
        $where['pid'] = $cid;
        //当前栏目下的子栏目id数组
        $arr_id = M('down_category')->field('id,title')->where($where)->select();
        $soft_id = '';
        foreach ($arr_id as $k => $v) {
            $soft_id .= $v['id'] . ',';
        }
        $soft_id = substr($soft_id, 0, -1);
        $where_c['a.status'] = 1;
        $where_c['a.category_id'] = array('in', $soft_id);
        if ($type == 'soft') {
            $result = M('down')->alias('a')->field('a.id,a.title,a.update_time,a.category_id,b.language,b.licence,b.size,b.soft_socre')->join('left join ' . C('DB_PREFIX') . 'down_dsoft as b ON a.id = b.id')->where($where_c)->order('a.update_time DESC')->limit(500)->select();
            foreach ($result as $k => $v) {
                foreach ($arr_id as $vv) {
                    if ($vv['id'] == $v['category_id']) {
                        $result[$k]['category_name'] = $vv['title'];
                    }
                }
                //星级评分
                $result[$k]['soft_socre'] = mt_rand(3, 5);
                //转换语言
                $result[$k]['language'] = getLanguage($v['id'], 'soft');
            }
        } else {
            $result = M('down')->alias('a')->field('a.id,a.title,a.update_time,a.category_id,b.language,b.licence,b.size')->join('left join ' . C('DB_PREFIX') . 'down_dmain as b ON a.id = b.id')->where($where_c)->order('a.update_time DESC')->limit(500)->select();
            foreach ($result as $k => $v) {
                foreach ($arr_id as $vv) {
                    if ($vv['id'] == $v['category_id']) {
                        $result[$k]['category_name'] = $vv['title'];
                    }
                }
                //星级评分
                $result[$k]['soft_socre'] = mt_rand(3, 5);
                //转换语言
                $result[$k]['language'] = getLanguage($v['id']);
            }
        }
        return $result;
    }

    /**
     * @user lcz
     * @time 2016年3月7日 14:32:40
     * @op  首页最新top
     * @Return array
     */

    public function rankTop($cid = 1)
    {
        //最新3个。安卓单机 1 ，安卓网游 2 安卓软件 48 ；
        $res1 = $this->topOp(1);
        $res2 = $this->topOp(2);
        $res3 = $this->topOp(48, 'soft');

        $this->assign(array(
            'res1' => $res1,
            'res2' => $res2,
            'res3' => $res3,
        ));
        $this->display(T('Down@afs/Widget/rankTop'));
    }

    /**
     * @user lcz
     * @time 2016年3月4日 11:00:49
     * @op  专题软件右边栏目-相关专题
     * @Param int $category_id
     * @Param int $order
     * @Return array
     */

    public function relationSpecial($category_id, $order = '1')
    {
        //$order参数排序 1为默认的 id降序 2为更新时间降序  3为点击量降序
        $where['category_id'] = $category_id ? $category_id : '75';
        $where['status'] = 1;
        if ($order == 2) {
            $_order = 'update_time DESC ';
        } elseif ($order == 3) {
            $_order = 'view DESC ';
        } else {
            $_order = 'id DESC ';
        }
        $result = M('down')->field('id,title,smallimg')->where($where)->order($_order)->limit(9)->select();

        $this->assign('res', $result);
        $this->display('Widget/relationSpecial');
    }

    /**
     * @user lcz
     * @time 2016年3月4日 11:00:49
     * @op  专题软件右边栏目-其他版本
     * @Param array $productTags
     * @Param int $id 主键id
     * @Param sting $title 下载类型标题
     * @Return array
     */

    public function otherVersion(array $productTags, $id, $title)
    {
        $tags = $productTags[0]['name'];  //标签值
        //选取所有title和$title类似的值
        $where['status'] = 1;
        $where['id'] = array('neq', $id);
        $title = mb_substr($title, 0, 3, 'utf-8');
        $where['title'] = array('like', $title . '%');
        $result = M('down')->field('id,title,smallimg')->where($where)->order('id DESC')->select();
        //从类似的数组中选取符合标签的
        $res = array();    //所以相同标签的数组
        foreach ($result as $k => $v) {
            $result[$k]['tags'] = get_tags($v['id'], 'down', 'product');
        }
        //匹对是否满足相同标签,对于多重标签我们取第一个标签
        foreach ($result as $k => $v) {
            foreach ($v['tags'] as $vv) {
                if ($vv['name'] == $tags) {
                    $res[] = $result[$k];
                }
            }
        }

        $this->assign('res', $res);
        $this->display('Widget/otherVersion');
    }

    /**
     * @user lcz
     * @time 2016年3月6日 11:00:49
     * @op  专题软件下侧栏目-相关阅读
     * @Param array $productTags
     * @Param int $id 主键id
     * @Param sting $title 下载类型标题
     * @Return array
     */

    public function relationRead(array $productTags)
    {
        //获取当前产品的所有标签数组
        foreach ($productTags as $k => $v) {
            $tags_arr[] = $v['id'];
        }
        //根据标签换取相关标签的数据id
        $where_tag['tid'] = array('in', $tags_arr);
        $id_arr = M('product_tags_map')->field('did')->where($where_tag)->select();
        $result = array();
        foreach ($id_arr as $v) {
            $where_document['status'] = '1';
            $where_document['id'] = $v['did'];
            $result[] = M('document')->alias('a')->field('id,title,description,cover_id')->where($where_document)->find();
        }
        $result = array_filter($result);
        foreach ($result as $k => $v) {
            $res = M('picture')->field('path')->where(array('id' => $v['cover_id']))->find();
            $result[$k]['img_path'] = $res['path'];
        }
        //prt($result);
        $this->assign('result', $result);
        $this->display('Widget/relationRead');
    }
}
