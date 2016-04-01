<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Widget;

use Common\Controller\WidgetController;
use Think\Controller;

/**
 * 页面widget
 *
 */
class AfsWidget extends WidgetController
{


    /**
     * 生成头部
     * @param string $content 内容
     * @return string 处理过后的内容
     */
    public function head()
    {
        $this->display(T('Home@afs/Public/header'));
    }

    /**
     * 生成尾部
     * @param string $content 内容
     * @return string 处理过后的内容
     */
    public function foot()
    {
        $this->display(T('Home@afs/Public/foot'));
    }

    public function kaifu()
    {
        $this->display(T('Home@afs/Widget/kaifu'));
    }

    public function danji()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '1')));
        $this->display("Widget/danji");
    }

    public function wangyou()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '5')));
        $this->display("Widget/wangyou");
    }

    public function yxph()
    {

        $this->display("Widget/yxph");
    }

    /**
     * 公共专区部分
     */
    public function zhuanqu()
    {
        $this->display(T('Home@afs/Widget/zhuanqu'));
    }


    /*
     * 首页自定义专区排序
     * Author:刘盼
    */

    public function selfZhuanqu()
    {
        $id = "109343";//排行对应专区ID109343
        $zoneID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = split(",", $zoneID);
        foreach ($ids as $id) {
            $_LISTS_[] = M('Batch')->where("id='$id' AND interface=0")->order('update_time desc')->limit(50)->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }
        $this->assign("lists", array_slice($arrT, 0, 7));
        $this->display(T('Home@afs/Widget/selfZhuanqu'));
    }


    /**
     * 网站地图
     */
    public function sitemap()
    {
        $news = D('Document')->where(array("status" => "1"))->order('update_time desc')->limit('50')->select();// 查询满足要求的总记录数
        $this->assign("news", $news);
        $down = D('Down')->where(array("status" => "1"))->order('update_time desc')->limit('50')->select();// 查询满足要求的总记录数
        $this->assign("down", $down);
        $this->assign("SEO", WidgetSEO(array('special', null, '34')));
        $this->display(T('Home@afs/Widget/sitemap'));
    }

    /**
     * 关于我们
     */
    public function aboutus()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '35')));
        $this->display(T('Home@afs/Widget/aboutus'));
    }

    /**
     * 联系我们
     */
    public function contact()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '36')));
        $this->display(T('Home@afs/Widget/contact'));
    }

    //首页下载部分
    //Jeffrey Lau
    public function indexDown()
    {
        $down = M('Down')->order("update_time desc")->where("status=1")->field("id,title,create_time,update_time")->limit(39)->select();
        $this->assign("lists", $down);
        $this->display('Widget/indexDown');
    }

    //首页厂商部分
    //Jeffrey Lau
    public function indexCompany()
    {
        $company = M('Company')->order("update_time desc")->where("status=1")->field("id,name,img,path")->limit(12)->select();
        $this->assign("lists", $company);
        $this->display('Widget/indexCompany');
    }
    //首页标签
    //Jeffrey Lau
    public function indexTag($type)
    {
        if (empty($type)) {
            return;
        }
        switch ($type) {
            case '1':
                $column = 'id,id as tagid,name,title,sort,pid,status,category,(select count(*) from onethink_tags_map where tid = tagid and did > 0) as total';
                $map['total'] = array('neq', '0');
                $map['category'] = "1";
                $map['status'] = "1";
                $tags = M('Tags')->field($column)->where($map)->order("update_time desc")->limit(35)->select();
                break;
            case '2'://单机
                $column = 'id,id as tagid,name,title,sort,pid,status,category,(select count(*) from onethink_tags_map where tid = tagid and did > 0) as total';
                $tags = M('Tags')->field($column)->order("total desc")->where(array("category" => "1", "status" => "1"))->limit(36, 71)->select();
                //$tags=M('Tags')->order("update_time desc")->where(array("status"=>"1"))->limit(35)->select();
                break;
            case '3'://软件
                $column = 'id,id as tagid,name,title,sort,pid,status,category,(select count(*) from onethink_tags_map where tid = tagid and did > 0) as total';
                $tags = M('Tags')->field($column)->order("total desc")->where(array("category" => "6", "status" => "1"))->limit(35)->select();
                break;

        }

        $this->assign("lists", $tags);
        $this->display('Widget/indexTag');
    }

    /**
     * 文章栏目相关推荐
     */
    public function indexZq($id)
    {
        $game = get_base_by_tag($id, 'Batch', 'Down', 'product', false);
        foreach ($game as $k => $val) {
            if ($val['data_type'] == '1') {
                $c[] = $val;

            }
        }


        $news = get_base_by_tag($id, 'Batch', 'Document', 'product', false);
        $subNavi = M('Batch')->where("pid='$id'")->select();
        $this->assign("zid", $id);
        $this->assign("subnavi", $subNavi);
        $this->assign("news", $news);
        $this->assign("game", $c);
        $this->display('Widget/indexZq');
    }


    public function wbreadCrumb($id, $module, $type)
    {
        if (empty($id)) {
            return;
        }
        $module = ucfirst($module);
        $type = strtolower($type);
        $siteUrl = C('STATIC_URL');
        $tpl = "";
        switch ($type) {
            case 'detail'://详情页面
                if (empty($type)) {
                    return;
                }
                $info = M($module)->where("id='$id'")->find();
                $cateAlias = $module == 'Document' ? 'Category' : $module . 'Category';
                $cid = $info['category_id'];
                $cateName = M($cateAlias)->where("id='$cid'")->find();
                if ($module == 'Down') {//下载面包屑特殊处理
                    $SURL = C('STATIC_URL');
                    $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strrpos($SURL, '/')) : $SURL;
                    $cateUrl = $SURL . "/game/" . $cateName['rootid'] . "_" . $cateName['id'] . "_0_1_1.html";
                } else {
                    $cateUrl = staticUrl('lists', $cateName['id'], $module);
                }

                $tpl = "<a href=\"" . $siteUrl . "\">首页</a>><a href=\"" . $cateUrl . "\">" . $cateName['title'] . "</a>><a href=\"" . staticUrl('detail', $info['id'], $module) . "\">" . $info['title'] . "</a>";

                echo $tpl;
                break;

            case 'cate'://栏目页面
                if (empty($type)) {
                    return;
                }
                $cateAlias = $module == 'Document' ? 'Category' : $module . 'Category';
                $cateName = M($cateAlias)->where("id='$id'")->find();
                $tpl = "<a href=\"" . $siteUrl . "\">首页</a>><a href=\"" . staticUrl('lists', $cateName['id'], $module) . "\">" . $cateName['title'] . "</a>";
                echo $tpl;
                break;
        }
    }

    /**
     * SEO
     * @param  string $type 类型
     * @param  string $module 模块名
     * @param  integer $cid or $id
     * @return array
     */
    public function SEO($type, $module = null, $id = null, $p = 0)
    {
        $seo = array();
        switch ($type) {
            case 'index':
                $seo['title'] = C('WEB_SITE_TITLE');
                $seo['keywords'] = C('WEB_SITE_KEYWORD');
                $seo['description'] = C('WEB_SITE_DESCRIPTION');
                return $seo;
                break;
            case 'moduleindex':
                if (empty($module)) {
                    return;
                }
                $module = strtoupper($module);
                $seo['title'] = C('' . $module . '_DEFAULT_TITLE');
                $seo['keywords'] = C('' . $module . '_DEFAULT_KEY');
                $seo['description'] = C('' . $module . '_DEFAULT_DESCRIPTION');
                return $seo;
                break;
            case 'special':
                $id = empty($id) ? '1' : $id;
                $cate = D('StaticPage')->where("id='$id'")->find();
                $title = empty($cate['title']) ? $cate['title'] : $cate['title'];
                $seo['title'] = $title . " - " . C('SITE_NAME');
                $seo['keywords'] = empty($cate['keywords']) ? '' : $cate['keywords'];
                $seo['description'] = empty($cate['description']) ? '' : $cate['description'];
                return $seo;
                break;
            case 'category':
                $id = empty($id) ? '1' : $id;
                if (empty($module)) {
                    return;
                }
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory'
                );
                $cate = D($cate_name[$module])->where("id='$id'")->find();
                $title = empty($cate['meta_title']) ? $cate['title'] : $cate['meta_title'];
                $seo['title'] = $title . " - " . C('SITE_NAME');
                if ($p > 1) {
                    $seo['title'] = $title . "(第" . $p . "页) - " . C('SITE_NAME');
                }
                $seo['keywords'] = empty($cate['keywords']) ? C('' . $module . '_DEFAULT_KEY') : $cate['keywords'];
                $seo['description'] = empty($cate['description']) ? C('' . $module . '_DEFAULT_KEY') : $cate['description'];
                return $seo;
                break;
            case 'detail':
                $id = empty($id) ? '1' : $id;
                if (empty($module)) {
                    return;
                }
                $module = ucfirst(strtolower($module));
                $detail = D($module)->detail($id);

                //标题
                if ($module == 'Down') {
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if (!empty($detail['seo_title'])) {
                        $title = $detail['seo_title'];
                    } else {
                        $title = $detail['sub_title'] . '|' . $detail['title'] . '下载' . $detail['version'];
                    }
                } else {
                    //1、seo title
                    //2、主标题
                    if (!empty($detail['seo_title'])) {
                        $title = $detail['seo_title'];
                    } else {
                        $title = $detail['title'];
                    }
                }
                //标题需要加前后缀
                if (C('SEO_STRING')) {
                    if ($module == 'Package' && C('PACKAGE_SEO_LAST')) {
                        $seo['title'] = $title . ' - ' . C('PACKAGE_SEO_LAST');
                    } else {
                        $t = array();
                        $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                        $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                        ksort($t);
                        $seo['title'] = implode(' - ', $t);
                    }
                } else {
                    $seo['title'] = $title;
                }

                //关键词
                if (empty($detail['seo_keywords'])) {
                    if ($module == 'Document') {
                        //文章 标签
                        $tag_ids = M('TagsMap')->where('did=' . $id . ' AND type="document"')->getField('tid', true);
                        if (empty($tag_ids)) {
                            $seo['keywords'] = $detail['title'];
                        } else {
                            $tags = M('Tags')->where(array('id' => array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);
                        }
                    } else {
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title'])
                            ? $detail['title']
                            : $detail['title'] . ',' . $detail['sub_title'];
                    }
                } else {
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if (empty($detail['seo_description'])) {
                    $des = array('Document' => 'description', 'Down' => 'conductor', 'Package' => 'content');
                    if (empty($detail[$des[$module]])) {
                        $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))), 0, 500);
                    } else {
                        $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                    }
                } else {
                    $seo['description'] = strip_tags($detail['seo_description']);
                }

                return $seo;
                break;
        }
    }


    /**
     * content字段图片和内置插入标签处理
     * @param string $content 内容
     * @return string 处理过后的内容
     */
    public function contentProcess($content)
    {
        //图片暂时兼容处理
        $content = preg_replace('/src\=\"(\/up\/.+?)/i', 'src="' . C('PIC_HOST') . '$1', $content);
        $content = preg_replace('/src\=\"(up\/.+?)/i', 'src="' . C('PIC_HOST') . '/$1', $content);
        $content = preg_replace('/src\=\"(\/Uploads\/.+?)/i', 'src="' . C('PIC_HOST') . '$1', $content);
        $content = preg_replace('/src\=\"(Uploads\/.+?)/i', 'src="' . C('PIC_HOST') . '/$1', $content);
        $content = preg_replace('/src\=\"http:\/\/(www.)??7230.com\/(up\/.+?)/i', 'src="' . C('PIC_HOST') . '/$2', $content);
        //内置标签处理
        echo $content;
    }


    /**
     * content 安粉丝详情
     * @param string $id 礼包ID
     * @return string 返回地址
     */
    public function packageUrl($id)
    {
        if (empty($id)) {
            return;
        }
        $game = get_base_by_tag($id, 'Package', 'Down', 'product', true);

        $url = staticUrl('detail', $game['id'], 'Down');
        if (!empty($url)) {
            echo $url;
        }
    }


    /**
     * content Tag链接地址处理
     * @param string $name name
     * @return string 返回地址
     */
    public function tagsUrl($name)
    {
        echo "/tag/" . $name;

    }

    /**
     * 排行页
     */
    public function rank()
    {

        $yearDownList = M('Down')->alias('a')->field('a.id,a.smallimg,a.title,a.abet')->join('__DOWN_ADDRESS__ b ON a.id = b.did ')->limit(30)->order('hits desc')->select();
        $this->assign('lists', $yearDownList);

        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, '9')));

        $this->display('Widget/phpd');
    }

    /**
     * 媒体库
     */
    public function media()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '10')));
        $this->display('Widget/media');
    }

    /**
     * Banner
     */
    public function banner()
    {
        $arrH = array();//首页横图
        $arrW = array();//首页竖图
        $docH = M('Document')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
        foreach ($docH as $k => $v) {
            $docH[$k]['type'] = 'document';
        }
        $docW = M('Document')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
        foreach ($docW as $k => $v) {
            $docW[$k]['type'] = 'document';
        }


        $downH = M('Down')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
        foreach ($downH as $k => $v) {
            $downH[$k]['type'] = 'down';
        }

        $downW = M('Down')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
        foreach ($downW as $k => $v) {
            $downW[$k]['type'] = 'down';
        }

        $packageH = M('Package')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
        foreach ($packageH as $k => $v) {
            $packageH[$k]['type'] = 'package';
        }
        $packageW = M('Package')->field('id,title,description,vertical_pic,level,update_time')->where("home_position='2' OR home_position='3'")->order('update_time desc')->limit(2)->select();
        foreach ($packageW as $k => $v) {
            $packageW[$k]['type'] = 'package';
        }

        $arrH = array_merge((array)$docH, (array)$downH, (array)$packageH);
        $arrW = array_merge((array)$docW, (array)$downW, (array)$packageW);

        $arrH = array_sort($arrH, 'update_time', SORT_DESC);//首页横图
        $arrW = array_sort($arrW, 'update_time', SORT_DESC);//首页竖图
        $this->assign('bannerH', $arrH);//首页横图
        $this->assign('bannerW', $arrW);//首页竖图
        $this->display('Widget/banner');
    }


    /***
     * 面包屑导航
     */
    public function Breadcrumb($type, $id, $model)
    {
        if (!is_numeric($id) || $id < 1) {
            return false;
        }
        $model = ucfirst(strtolower($model));

        $category = "__CATEGORY__";

        switch ($model) {
            case 'Document':
                break;
            case 'Down':
                $category = '__DOWN_CATEGORY__';
                break;
            case 'Package':
                $category = '__PACKAGE_CATEGORY__';
                break;
            default:
                return false;
        }

        $result = "<a href=" . staticUrl('index') . ">首页</a>->";

        switch ($type) {
            case 'lists':
                switch ($model) {
                    case 'Document':
                        $category = "downCategory";
                        break;
                    case 'Package':
                        $category = 'packageCategory';
                        break;
                    default:
                        return false;
                }
                $result .= M($category)->where('id = ' . $id)->getField('title');
                break;

            case 'detail':
                if ($model == 'Down') {
                    $arr = M($model)->field('title,category_rootid')->where('id = ' . $id)->find();
                    $arr['cateName'] = M('downCategory')->where('id = ' . $arr['category_rootid'])->getField('title');

                    if ($arr['cateName'] == "游戏") {
                        return $result . '<a href="' . C('STATIC_URL') . '/yx/" >游戏</a>->' . $arr['title'];
                    } elseif ($arr['cateName'] == "软件") {
                        return $result . '<a href="' . C('STATIC_URL') . '/rj/" >软件</a>->' . $arr['title'];
                    } elseif ($arr['cateName'] == "壁纸") {
                        return $result . '<a href="' . C('STATIC_URL') . '/pic/" >图片库</a>->' . $arr['title'];
                    } elseif ($arr['cateName'] == "铃声") {
                        return $result . '<a href="' . getWidgetPath('10') . '" >媒体库</a>->' . $arr['title'];
                    }
                }

                $arr = M($model)->alias('a')->field('a.title,b.title name,b.id')->join($category . ' b ON a.category_id = b.id')->where('a.id = ' . $id)->find();
                $result .= '<a href=' . staticUrl('lists', $arr['id'], $model) . '>' . $arr['name'] . '</a>->' . $arr['title'];
                break;
            default:
                return false;
        }
        return $result;
    }


    /**
     * @author 肖书成
     * @crate_time 2015/3/20
     * @comments 游戏厂商页面合集
     * @param int GET sid
     */
    public function companyHj()
    {

    }

    /**
     * @author 肖书成
     * @crate_time 2015/3/20
     * @comments 游戏专题页面合集
     * @param int GET sid
     */
    public function featureHj()
    {

    }

    /**
     * @author 肖书成
     * @crate_time 2015/3/20
     * @comments 游戏专区页面合集
     * @param int GET sid
     */
    public function batchHj()
    {
        $this->hj('batch');
    }

    /**
     * @author 肖书成
     * @crate_time 2015/3/20
     * @comments 游戏K页面合集
     * @param int GET sid
     */
    public function kHj()
    {
        $this->hj('special');
    }

    /**
     * @author 肖书成
     * @crate_time 2015/3/20
     * @comments 专门为厂商、专题、专区、K页面的合集页所用
     * @param int GET sid
     * @param string $table
     */
    public function hj($table)
    {
        parent::hj('Home@afs/Widget/hj', $table, 'pid = 0 AND enabled = 1 AND interface = 0');
    }

    //处理操作
    protected function newDayGame($pid)
    {
        $w['pid'] = $pid;
        $game_child = M('down_category')->where($w)->field('id,title')->select();
        //查出所有下载类游戏
        $temp = array(); //id
        foreach ($game_child as $v) {
            $temp[] = $v['id'];
        }
        $where_game['a.category_id'] = array('in', $temp);
        $where_game['b.system'] = '1';  //1是安卓
        $where_game['b.data_type'] = '1'; //1是官方
        $where_game['a.status'] = '1';
        $game_list = M('down')->alias('a')->join('LEFT JOIN ' . C('DB_PREFIX') . 'down_dmain as b ON a.id = b.id ')
            ->where($where_game)->field('a.id,a.category_id,a.title,b.size,a.smallimg')->order('a.update_time desc')->limit('0,8')->select();
        //加上所属分类
        foreach ($game_list as $k => $v) {
            foreach ($game_child as $kk => $vv) {
                if ($v['category_id'] == $vv['id']) {
                    $game_list[$k]['category_title'] = $vv['title'];
                }
            }
        }

        return $game_list;
    }

    //首页-今日更新
    public function newDay()
    {
        //1是安卓单机id
        $game_list = $this->newDayGame(1);
        //12是安卓网游id
        $game_list_net = $this->newDayGame(2);
        //软件
        $w['pid'] = 48;  //48是安卓软件id
        $soft_child = M('down_category')->where($w)->field('id,title')->select();
        //查出所有下载类游戏
        $temp = array(); //id
        foreach ($soft_child as $v) {
            $temp[] = $v['id'];
        }
        $where_soft['a.category_id'] = array('in', $temp);
        $where_soft['a.status'] = '1';
        $soft_list = M('down')->alias('a')->join('LEFT JOIN ' . C('DB_PREFIX') . 'down_dsoft as b ON a.id = b.id ')->where($where_soft)->field('a.id,a.category_id,a.title,a.smallimg')->order('a.update_time desc')->limit('0,8')->select();
        //加上所属分类

        foreach ($soft_list as $k => $v) {
            foreach ($soft_child as $kk => $vv) {
                if ($v['category_id'] == $vv['id']) {
                    $soft_list[$k]['category_title'] = $vv['title'];
                }
            }
        }

        $this->assign(array(
            'soft' => $soft_list,
            'game' => $game_list,
            'game_net' => $game_list_net
        ));
        $this->display(T('Home@afs/Widget/newDay'));
    }

    /*
     * 标签列表页-相关文章
     */
    public function relationArt($tags)
    {
        $product_tags = array();
        foreach ($tags as $k => $v) {
            $product_tags[] = get_tags($tags[$k], 'down', 'product');
        }
        $product_tags = array_filter($product_tags);
        $product_tag_ids = array();
        foreach ($product_tags as $k => $v) {
            foreach ($v as $kk => $vv) {
                $product_tag_ids[] = $vv['id'];
            }
        }
        if (!empty($product_tag_ids)) {
            //根据产品标签id查找相关文章
            //$arr = array();
             $where['b.id'] = array('in', $product_tag_ids);
             $where['a.type'] = 'document';
             $tags = M('product_tags_map')->field('a.did,b.id,b.category,b.name,b.title,a.id map_id,c.title,c.description,c.smallimg')->alias('a')->join('__PRODUCT_TAGS__ b ON a.tid = b.id', 'left')->join('__DOCUMENT__ c ON c.id = a.did', 'left')->where($where)->order('c.view DESC')->limit(18)->select();

   /*         $n = 0;
            foreach ($product_tag_ids as $k => $v) {
                if($n > 18){
                    break;
                }
                $where['b.id'] = $product_tag_ids[$k];
                $where['a.type'] = 'document';
                $arr[] = M('product_tags_map')->field('a.did,b.id,b.category,b.name,b.title,a.id map_id,c.title,c.description,c.smallimg')->alias('a')->join('__PRODUCT_TAGS__ b ON a.tid = b.id', 'left')->join('__DOCUMENT__ c ON c.id = a.did', 'left')->where($where)->order('c.view DESC')->limit(18)->select();
                $n++;
            }
            $tags = array_filter($arr);
          */
            $this->assign('relation_art', $tags);
        }

        $this->display(T('Home@afs/Widget/relationArt'));
    }
}
