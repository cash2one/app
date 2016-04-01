<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Document\Widget;

use Think\Controller;

/**
 * 页面widget
 *
 */
class AfsWidget extends Controller
{


    /**
     * 方法不存在时调用
     * @return void
     */
    public function __call($method, $args)
    {
        //斜杠会被解析，所以用下划线代替
        $method = str_replace('_', '/', $method);
        $this->display(T($method));
    }


    public function zixunList()
    {
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
        $where = array('category_rootid' => '80');
        $whereMap = array('map' => array('category_rootid' => '80'));
        //分页获取数据
        $row = 20;
        $count = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if (I('gettotal')) {
            echo ceil($count / $row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        //容错处理
        if ($p > $count) {
            $p = $count;
        }

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists', $lists);// 赋值数据集

        $Page = new \Think\Page($count, $row, '', false, $page_info['path'] . getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('first', '首页');
        $Page->setConfig('end', '尾页');
        $Page->setConfig('prev', "上一页");
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, '6')));
        $this->display(T('Document@afs/Category/index'));//模板选择

    }

    public function gonglveList()
    {
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
        $where = array('category_rootid' => '181');
        $whereMap = array('map' => array('category_rootid' => '181'));
        //分页获取数据
        $row = 20;
        $count = D('Document')->where($whereMap)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if (I('gettotal')) {
            echo ceil($count / $row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        //容错处理
        if ($p > $count) {
            $p = $count;
        }

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists', $lists);// 赋值数据集

        $Page = new \Think\Page($count, $row, '', false, $page_info['path'] . getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('first', '首页');
        $Page->setConfig('end', '尾页');
        $Page->setConfig('prev', "上一页");
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, '40')));
        $this->display(T('Document@afs/Category/index'));//模板选择

    }

    /**
     * 文章栏目相关推荐
     */
    public function cateCommended($id)
    {
        $map['category_id'] = $id;
        $map['position'] = '16';
        $cateNews = M('Document')->where($map)->order('create_time desc')->limit(10)->select();
        $this->assign("cateNews", $cateNews);
        $this->display('Widget/cateCommended');
    }

    /**
     * 文章详情相关礼包
     */
    public function relatePackage($id)
    {
        $p = get_base_by_tag($id, 'Document', 'Package', 'product', true);
        $this->assign("p", $p);
        $this->display('Widget/relatePackage');
    }

    /**
     * 文章详情下载框
     */
    public function relateDown($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $tid = M('ProductTagsMap')->where(array("type" => "document", "did" => $id))->getField("tid");
        $t = M('ProductTagsMap')->where(array("type" => "down", "tid" => $tid))->select();

        foreach ($t as $k => $val) {
            $did = $val['did'];
            $down[] = M("Down")->alias("__DOWN")->where("__DOWN.id = '$did' AND status=1")->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->field("__DOWN.id,title,size,smallimg,data_type,update_time")->find();
            $down[] = M("Down")->alias("__DOWN")->where("__DOWN.id = '$did' AND status=1")->order("update_time desc")->join("INNER JOIN __DOWN_DSOFT__ ON __DOWN.id = __DOWN_DSOFT__.id")->field("__DOWN.id,title,size,smallimg,update_time")->find();
        }
        $down = array_filter($down);
        foreach ($down as $k => $val) {
            if ($val['data_type'] == '1' || $val['data_type'] == '') {
                $c[] = $val;
            }
        }
        $c = multi_array_sort($c, 'update_time', SORT_DESC);
        $this->assign("down", $c[0]);
        $b = get_base_by_tag($id, 'Document', 'Batch', 'product', true);
        $batchUrl = getCPath($b['id'], 'batch');
        $this->assign('qrcode', builtQrcode($down[0]['down']));
        $this->assign("batchUrl", $batchUrl);
        $this->display('Widget/relateDown');
    }

    /**
     * 问答详情相关游戏
     */
    public function relateYouxi($id)
    {
        $p = get_base_by_tag($id, 'Document', 'Down', 'product', false);
        $this->assign("p", $p);
        $this->display('Widget/wdRelateGame');
    }

    /**
     * 问答详情相关攻略
     */

    public function relateWenda($id)
    {
        $p = get_base_by_tag($id, 'Document', 'Document', 'product', false);
        $this->assign("p", $p);
        $this->display('Widget/relateWenda');
    }

    /**
     * 文章详情相同风格
     */
    public function sameStyle($id)
    {
        $array = array();
        $pcount = 0;//产品标签匹配出的相关文章数目
        $count = 0;//标签匹配出的相关文章数目
        $p = get_base_by_tag($id, 'Document', 'Down', 'product', false);
        $pcount = count($p);
        if ($pcount < 10) {
            $t = get_base_by_tag($id, 'Document', 'Down', 'tags', false);
            $count = count($t);
        } else {
            $result = $p;
        }


        if ($pcount + $count < 10) {
            $cate = $p[0]['category_id'];
            $cateNews = M('Down')->where(array('category_id' => $cate, 'status' => '1'))->order('create_time desc')->limit(10)->select();
        }
        foreach ($p as $v) {
            $array[] = $v;
        }
        foreach ($t as $v) {
            $array[] = $v;
        }
        foreach ($cateNews as $v) {
            $array[] = $v;
        }
        $this->assign("result", $array);
        $this->display('Widget/sameStyle');
    }

    /**
     * @user lcz
     * @time 2016年2月27日16:42:35
     * @op  文章右边栏目-最新软件
     * @param int $id
     * @Param int $system
     * @Return array
     */
    public function newSoft()
    {
        //查找最新的安卓软件
        $w['pid'] = 48;  //48是安卓软件id
        $soft_child = M('down_category')->where($w)->field('id,title')->select();
      /*  //加上大分类
        $soft_child[] = array('id' => '48');*/
        //查出所有下载类游戏
        $temp = array(); //id
        foreach ($soft_child as $v) {
            $temp[] = $v['id'];
        }
        $where['a.category_id'] = array('in', $temp);
        $where['a.status']  = '1';
        $soft_list = M('down')->alias('a')->join('LEFT JOIN ' . C('DB_PREFIX') . 'down_dsoft as b ON a.id = b.id ')->where($where)->field('a.id,a.category_id,a.title,a.smallimg')->order('a.create_time desc')->limit('0,9')->select();
        //加上所属分类
        foreach ($soft_list as $k => $v) {
            foreach ($soft_child as $kk => $vv) {
                if ($v['category_id'] == $vv['id']) {
                    $soft_list[$k]['category_title'] = $vv['title'];
                }
            }
        }
        $this->assign('result', $soft_list);
        $this->display('Widget/newSoft');
    }

    /**
     * @description 内容处理
     * @author JeffreyLau
     * @date 2016-1-23 14:33:57
     */
    public function contentHandle($content)
    {
        if ($content == "") {
            return;
        }

        preg_match_all('/<author>(.*?)<\/author>/', $content, $author);
        preg_match_all('/<content>(.*)<\/content>/iUs', $content, $con);
        preg_match_all('/<time>(.*?)<\/time>/', $content, $time);
        if (!empty($con[1])) {
            $lists = array();
            foreach ($time[1] as $key => $val) {
                $lists[$key]['time'] = $val;
            }
            foreach ($author[1] as $key => $val) {
                $lists[$key]['author'] = $val;
            }
            foreach ($con[1] as $key => $val) {
                $lists[$key]['content'] = $val;
                $lists[$key]['avatar'] = rand(0, 5);
            }
            $this->assign("lists", $lists);
            $this->display('Widget/answers');

        } else {
            echo $content;
        }

    }

    /**
     * 文章详情相关文章
     */
    public function relateArticle($id)
    {
        $array = array();
        $pcount = 0;//产品标签匹配出的相关文章数目
        $count = 0;//标签匹配出的相关文章数目
        $p = get_base_by_tag($id, 'Document', 'Document', 'product', false);
        $pcount = count($p);
        if ($pcount < 10) {
            $t = get_base_by_tag($id, 'Document', 'Document', 'tags', false);
            $count = count($t);
        } else {
            $result = $p;
        }
        if ($pcount + $count < 10) {
            $cate = M('Document')->where("id='$id'")->getField('category_id');
            $cateNews = M('Document')->where("category_id='$cate'")->order('create_time desc')->limit(10)->select();
        }
        foreach ($p as $v) {
            $array[] = $v;
        }
        foreach ($t as $v) {
            $array[] = $v;
        }
        foreach ($cateNews as $v) {
            $array[] = $v;
        }
        $array = array_unique_fb($array);
        $this->assign("result", $array);
        $this->display('Widget/relateArticle');
    }
}
