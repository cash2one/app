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

/**
 * 页面widget
 *
 */
class AfsmobileWidget extends WidgetController
{

    public function tags()
    {
        $map['category'] = array('EQ', 1);
        $tags = M('Tags')->order('create_time desc')->where($map)->limit(30)->select();
        $this->assign('tags', $tags);
        $this->display(T('Home@afsmobile/Widget/tags'));
    }

    public function errorPage()
    {
        $this->display(T('Home@afsmobile/Widget/404'));
    }

    /**
     * SEO
     * @param  string $type 类型
     * @param  string $module 模块名
     * @param  integer $cid or $id
     * @return array
     */
    public function SEO($type, $module = null, $id = null, $p = 1)
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
                if (empty($module)) return;
                $module = strtoupper($module);
                $seo['title'] = C('' . $module . '_DEFAULT_TITLE');
                $seo['keywords'] = C('' . $module . '_DEFAULT_KEY');
                $seo['description'] = C('' . $module . '_DEFAULT_DESCRIPTION');
                return $seo;
                break;
            case 'special':
                $id = empty($id) ? '1' : $id;
                $cate = D('StaticPage')->where("id='$id'")->find();
                $seo['title'] = empty($cate['title']) ? $cate['title'] : $cate['title'];
                $seo['keywords'] = empty($cate['keywords']) ? '' : $cate['keywords'];
                $seo['description'] = empty($cate['description']) ? '' : $cate['description'];
                return $seo;
                break;
            case 'category':
                $id = empty($id) ? '1' : $id;
                if (empty($module)) return;
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory'
                );
                $cate = D($cate_name[$module])->where("id='$id'")->find();
                $seo['title'] = empty($cate['meta_title']) ? $cate['title'] : $cate['meta_title'];
                $seo['title'] = $seo['title'] . "(第" . $p . "页) - " . C('SITE_NAME') . '手机版';
                $seo['keywords'] = empty($cate['keywords']) ? C('' . $module . '_DEFAULT_KEY') : $cate['keywords'];
                $seo['description'] = empty($cate['description']) ? C('' . $module . '_DEFAULT_KEY') : $cate['description'];
                return $seo;
                break;
            case 'detail':
                $id = empty($id) ? '1' : $id;
                if (empty($module)) return;
                $module = ucfirst(strtolower($module));
                $detail = D($module)->detail($id);

                //标题
                if ($module == 'Down') {
                    //下载的规则 
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号


                    //////////////////////////////////手机版单独处理  Author : Jeffrey Lau   Date:2015-6-25//////////////////////////////////
                    $m_title = empty($detail['m_seo_title']) ? $detail['title'] : $detail['m_seo_title'];
                    if (!empty($detail['m_seo_title']) && empty($detail['seo_title'])) {//手机版SEO标题不为空，电脑版SEO标题为空 就用手机版SEO标题
                        $title = $detail['m_seo_title'];
                    } else if (!empty($detail['m_seo_title']) && !empty($detail['seo_title'])) {//手机版和pc SEO标题都不为空   用手机版SEO规则
                        $title = $detail['m_seo_title'];
                    } else if (empty($detail['m_seo_title']) && !empty($detail['seo_title'])) {//手机版SEO标题为空,那就用PC
                        $title = $detail['seo_title'];
                    } else {
                        $title = $detail['sub_title'] . '|' . $detail['title'] . '下载';

                    }
                    //(旧代码注释   Author : Jeffrey Lau   Date:2015-6-25)
                    //    if(!empty($detail['seo_title'])){
//                        $title = $detail['seo_title']; 
//                    }else{
//                        $title = $detail['sub_title'] .'|'. $detail['title'] . '下载'; 
//                    }
                } else {
                    //////////////////////////////////手机版单独处理  Author : Jeffrey Lau     Date:2015-6-25//////////////////////////////////
                    if (!empty($detail['m_seo_title']) && empty($detail['seo_title'])) {//手机版SEO标题不为空，电脑版SEO标题为空 就用手机版SEO标题
                        $title = $detail['m_seo_title'];
                    } else if (!empty($detail['m_seo_title']) && !empty($detail['seo_title'])) {//手机版和pc SEO标题都不为空   用手机版SEO规则
                        $title = $detail['m_seo_title'];
                    } else if (empty($detail['m_seo_title']) && !empty($detail['seo_title'])) {//手机版SEO标题为空,那就用PC
                        $title = $detail['seo_title'];
                    } else {
                        $title = $detail['title'];

                    }

                    //其他的规则    (旧代码注释   Author : Jeffrey Lau   Date:2015-6-25)
                    //1、seo title
                    //2、主标题
                    // if(!empty($detail['seo_title'])){
//                        $title = $detail['seo_title']; 
//                    }else{
//                        $title = $detail['title']; 
//                    }
                }
                //标题需要加前后缀
                if (C('SEO_STRING')) {
                    $t = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode(' - ', $t);
                } else {
                    $seo['title'] = $title;
                }

                ///////////////////////////////////////////////////////////手机版关键词处理  Author : Jeffrey Lau Date:2015-6-25    ///////////////////////////////////

                if ($module == 'Document') {

                    if (!empty($detail['m_seo_keywords']) && empty($detail['seo_keywords'])) {//手机版SEO关键词为空，电脑版SEO关键词为空 就用手机版SEO关键词
                        $seo['keywords'] = strip_tags($detail['m_seo_keywords']);
                    } else if (!empty($detail['m_seo_keywords']) && !empty($detail['seo_keywords'])) {//手机版和pc SEO关键词都不为空   用手机版SEO规则
                        $seo['keywords'] = strip_tags($detail['m_seo_keywords']);
                    } else if (empty($detail['m_seo_keywords']) && !empty($detail['seo_keywords'])) {//手机版SEO关键词为空,那就用PC
                        $seo['keywords'] = strip_tags($detail['seo_keywords']);
                    } else if (empty($detail['m_seo_keywords']) && empty($detail['seo_keywords'])) {//都为空
                        $tag_ids = M('TagsMap')->where('did=' . $id . ' AND type="document"')->getField('tid', true);
                        if (empty($tag_ids)) {
                            $seo['keywords'] = $detail['title'];
                        } else {
                            $tags = M('Tags')->where(array('id' => array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);
                        }
                    }

                } else if ($module == 'Down') {
                    if (!empty($detail['m_seo_keywords']) && empty($detail['seo_keywords'])) {//手机版SEO关键词为空，电脑版SEO关键词为空 就用手机版SEO关键词
                        $seo['keywords'] = strip_tags($detail['m_seo_keywords']);
                    } else if (!empty($detail['m_seo_keywords']) && !empty($detail['seo_keywords'])) {//手机版和pc SEO关键词都不为空   用手机版SEO规则
                        $seo['keywords'] = strip_tags($detail['m_seo_keywords']);
                    } else if (empty($detail['m_seo_keywords']) && !empty($detail['seo_keywords'])) {//手机版SEO关键词为空,那就用PC
                        $seo['keywords'] = strip_tags($detail['seo_keywords']);
                    } else if (empty($detail['m_seo_keywords']) && empty($detail['seo_keywords'])) {//都为空
                        $seo['keywords'] = empty($detail['sub_title'])
                            ? $detail['title']
                            : $detail['title'] . ',' . $detail['sub_title'];
                    }

                } else if (empty($detail['seo_keywords'])) {
                    $seo['keywords'] = empty($detail['sub_title'])
                        ? $detail['title']
                        : $detail['title'] . ',' . $detail['sub_title'];
                } else {
                    $seo['keywords'] = $detail['seo_keywords'];
                }


                /////////////////////////////////////////////////////手机版描述处理  Author : Jeffrey Lau  Date:2015-6-25 //////////////////////////////////////////////
                if ($module == 'Document' || $module == 'Down') {

                    if (!empty($detail['m_seo_description']) && empty($detail['seo_description'])) {//手机版SEO描述为空，电脑版SEO描述为空 就用手机版SEO描述
                        $seo['description'] = strip_tags($detail['m_seo_description']);
                    } else if (!empty($detail['m_seo_description']) && !empty($detail['seo_description'])) {//手机版和pc SEO描述都不为空   用手机版SEO规则
                        $seo['description'] = strip_tags($detail['m_seo_description']);
                    } else if (empty($detail['m_seo_description']) && !empty($detail['seo_description'])) {//手机版SEO描述为空,那就用PC
                        $seo['description'] = strip_tags($detail['seo_description']);
                    } else if (empty($detail['m_seo_description']) && empty($detail['seo_description'])) {//都为空,截取文章内容
                        $des = array('Document' => 'description', 'Down' => 'conductor', 'Package' => 'content');
                        if (empty($detail[$des[$module]])) {
                            $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))), 0, 500);
                        } else {
                            $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                        }
                    }


                } else if (empty($detail['seo_description'])) {//其他模块 : 礼包

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
     * content Tag链接地址处理
     * @param string $name name
     * @return string 返回地址
     */
    function rankUrl($name)
    {
        $url = "/" . $name;
        echo str_replace("index/", "", rtrim($url, "/") . "/");
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


    public function hotWords()
    {//搜索页面热词
        $id = '105641';
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = split(",", $softID);
        foreach ($ids as $id) {
            //$_LISTS_[]=M('Down')->field()->where("status=1 AND `id`=$id")->select();
            $_LISTS_[] = M()->table('onethink_down tb1,onethink_down_dmain tb2')->where('tb1.id=tb2.id AND tb1.id=' . $id)->order('create_time desc')->limit(15)->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }

        $this->assign(ranks, array_slice($arrT, 0, 15));
        $this->display(T('Home@afsmobile/Widget/hotwords'));
    }

    /**
     * @author 肖书成
     * @crate_time 2015/4/10
     * @comments 专区合集
     */
    public function batchHj()
    {
        $this->hj('Home@afsmobile/Widget/hj', 'batch', 'pid = 0 AND interface = 1 AND enabled = 1', 'id DESC', 12, true);
    }

    /**
     * @author 肖书成
     * @crate_time 2015/4/10
     * @comments K页面合集
     */
    public function specialHj()
    {
        $this->hj('Home@afsmobile/Widget/hj', 'special', 'interface = 1 AND enabled = 1', 'id DESC', 12, true);
    }

    /**
     * 作者:肖书成
     * 描述:随机厂商
     * @param int $id
     */
    public function randCs($id)
    {
        $lists = M('Company')->field('name,path,img')->where(rand_where('Company', 8, 'status = 1 AND id != ' . $id))->limit(8)->select();

        $this->assign('lists', $lists);
        $this->display('Widget/randCs');
    }

    /**
     * 作者:肖书成
     * 描述:随机标签
     * @param int $id
     */
    public function randTag($id, $cate)
    {
        $lists = M('Tags')->field('name,title,img')->where(rand_where('Tags', 8, "status = 1 AND category = $cate AND id != $id"))->limit(8)->select();

        $this->assign('lists', $lists);
        $this->display(T('Home@afsmobile/Widget/randTags'));
    }

    /*
     * M_手机头部banner 获取down推荐
     */
    public function bannerDown()
    {
        $where['home_position'] = 1;
        $where['status'] = 1;
        $res = M('down')->field('id,cover_id,title')->where($where)->order('update_time DESC')->limit(4)->select();
        $this->assign('res',$res);

        $this->display(T('Home@afsmobile/Widget/bannerDown'));
    }

}


