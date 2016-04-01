<?php
// +----------------------------------------------------------------------
// | 描述:首页模块widget类，对一些特殊逻辑处理
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-12 上午9:39    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Home\Widget;

use Common\Controller\WidgetController;

class Jf96umobileWidget extends WidgetController
{

    /**
     * 描述：新标签获取路径回调函数
     * @param $params
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function parseModuleUrl($params)
    {
        if (isset($params['category_id'])) {
            $model = $params['module']['1'];
            $where['id'] = $params['id'];
            $rs = M($model)->field('path_detail')->where($where)->find();
            $path = $rs['path_detail'];
            if (empty($path)) {
                $path = $params['path_detail'];
            }
            $DIR = C(strtoupper($model) . '_DIR');
            if ($DIR) {
                $DIR = substr($DIR, -1) != '/' ? $DIR . '/' : $DIR;
                $path = $DIR . $path;
            }
            $path = str_replace('//', '/', $path);
            if (strstr($path, '{create_time|date=Ymd}')) {
                $value = date('Ymd', $params['create_time']);
                // $path = str_replace('{create_time|date=Ymd}', $value, $path);
            }
            if (strstr($path, '{Year}/{Month}/{Day}/{Time}')) {// qbaobei路径
                $str_date = date('Y', $params['create_time']) . '/' . date('m', $params['create_time']) . '/' . date('d', $params['create_time']) . '/' . date('His', $params['create_time']);
                // $path = str_replace('{Year}/{Month}/{Day}/{Time}', $str_date, $path);
            }
            if (strstr($path, '{Y}{M}')) {//96u路径
                $str_date96u = date('Y', $params['create_time']) . date('m', $params['create_time']);
                // $path = str_replace('{Y}{M}', $str_date96u, $path);
            }

            // 文档
            $url = str_replace(array('{id}', '{create_time|date=Ymd}', '{Year}/{Month}/{Day}/{Time}', '{Y}{M}'), array($params['id'], $value, $str_date, $str_date96u), $path) . '.html';
            $url = str_replace('index.html', '', $url);
        } else {
            $model = $params['module']['1'];
            // 列表
            $filename = strtolower($params['path_lists_index']);
            $path = dirname($params['path_lists']) . '/';
            $DIR = C(strtoupper($model) . '_DIR');
            if ($DIR) {
                $DIR = substr($DIR, -1) != '/' ? $DIR . '/' : $DIR;
                $path = $DIR . $path;
            }
            $path = str_replace('//', '/', $path);
            if ($filename == 'index' || !$filename) {
                $url = $path;
            } else {
                $url = $path . $filename . '.html';
            }

        }

        if ($url) {
            return '/' . $url;
        }
    }

    /**
     * 描述：获取分类信息
     * @param string $model
     * @param string $where
     * @param string $field
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCateInfo($model = 'Category', $where = '1=1', $field = '*')
    {
        return M($model)->where($where)->field($field)->select();
    }

    /**
     * 描述：获取seo信息
     * @param $type
     * @param null $module
     * @param null $id
     * @return array
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function SEO($type, $module = null, $id = null)
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
                $seo['title'] = empty($cate['title']) ? $cate['title'] : $cate['title'];
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
                    'DOWN' => 'DownCategory',
                    'GALLERY' => 'GalleryCategory',
                );
                $cate = D($cate_name[$module])->where("id='$id'")->find();
                $seo['title'] = empty($cate['meta_title']) ? $cate['title'] : $cate['meta_title'];
                if (C('SEO_STRING')) {
                    $t = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode('_', $t);
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
                    //下载的规则
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if (!empty($detail['seo_title'])) {
                        $title = $detail['seo_title'];
                    } else {
                        $title = $detail['sub_title'] . '|' . $detail['title'] . '下载';
                    }
                } else {
                    //其他的规则
                    //1、seo title
                    //2、主标题
                    //$cate= M('Category')->where(array('id'=>$detail['category_id']))->getField("title");

                    if (!empty($detail['seo_title'])) {
                        $title = $detail['seo_title'];
                    } else {

                        $title = $detail['title'];
                    }
                }
                //标题需要加前后缀
                if (C('SEO_STRING')) {
                    $t = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode('_', $t);
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
                $seo['description'] = ltrim($seo['description']);
                $seo['description'] = rtrim($seo['description']);
                return $seo;
                break;
        }
    }
    /**--------------------------------------------------------谭坚Widget start---------------------------------------------*/
    /**
     * 描述：弹出导航逻辑
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function popNav()
    {

        $where['status'] = 1; //判断分类状态
        //获取游戏新闻(资讯)子类
        $pid = 1586;
        $where['pid'] = $pid;
        $field = 'id,title';
        $news_cate = $this->getCateInfo('Category', $where, $field);
        $this->assign('news_name', '资讯');
        $this->assign('news_id', $pid);
        $this->assign('news_cate', $news_cate);
        //游戏库导航
        $this->gameNav();
        //下载导航逻辑
        $this->downNav();
        //软件导航逻辑
        //获取软件下载安卓子类
        $android_cate = D('DownCategory')->where(array("pid" => "1858", "status" => "1"))->field('id,title')->select();
        $this->assign('android_name', '安卓软件');
        $this->assign('android_id', "1858");
        $this->assign('android_cate', $android_cate);

        //获取IOS软件
        $ios_cate = D('DownCategory')->where(array("pid" => "1859", "status" => "1"))->field('id,title')->select();
        $this->assign('ios_name', 'IOS软件');
        $this->assign('ios_id', "1859");
        $this->assign('ios_cate', $ios_cate);
        $this->display(T('Home@jf96umobile/Widget/popnav'));

    }

    /**
     * 描述：游戏库分类逻辑
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function gameNav()
    {
        //获取手游专区单机游戏子类
        $pid = 1665;
        $where['pid'] = $pid;
        $field = 'id,title';
        $danji_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('danji_name', '单机游戏');
        $this->assign('danji_id', $pid);
        $this->assign('danji_cate', $danji_cate);
        //获取手游专区网络游戏子类
        $pid = 1666;
        $where['pid'] = $pid;
        $field = 'id,title';
        $network_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('network_name', '网络游戏');
        $this->assign('network_id', $pid);
        $this->assign('network_cate', $network_cate);
    }

    /**
     * 描述：获取下载导航逻辑
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function downNav()
    {
        $where['status'] = 1; //判断分类状态
        //获取手游下载安卓子类
        $pid = 1831;
        $where['pid'] = $pid;
        $field = 'id,title';
        $network_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('android_name', '安卓游戏');
        $this->assign('android_id', $pid);
        $this->assign('android_cate', $network_cate);
        //获取手游下载苹果子类
        $pid = 1830;
        $where['pid'] = $pid;
        $field = 'id,title';
        $network_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('ios_name', 'IOS游戏');
        $this->assign('ios_id', $pid);
        $this->assign('ios_cate', $network_cate);
        //获取手游下载电脑版子类
        $pid = 1832;
        $where['pid'] = $pid;
        $field = 'id,title';
        $network_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('pc_name', 'PC游戏');
        $this->assign('pc_id', $pid);
        $this->assign('pc_cate', $network_cate);
        //获取手游下载其它子类
        $pid = 1687;
        $where['pid'] = $pid;
        $field = 'id,title';
        $network_cate = $this->getCateInfo('DownCategory', $where, $field);
        $this->assign('other_name', '其它游戏');
        $this->assign('other_id', $pid);
        $this->assign('other_cate', $network_cate);
    }

    /**
     * 描述：下载分类
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function downCate()
    {
        $this->downNav();
        $this->display(T('Home@jf96umobile/Widget/downpopnav'));
    }

    /**
     * 描述：游戏库分类显示
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function gameCate()
    {
        $this->gameNav();
        $this->display(T('Home@jf96umobile/Widget/gamepopnav'));
    }

    /**
     * 描述：软件分类显示
     * Author:刘盼
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function softCate()
    {
        //获取软件下载安卓子类
        $android_cate = D('DownCategory')->where(array("pid" => "1858", "status" => "1"))->field('id,title')->select();
        $this->assign('android_name', '安卓软件');
        $this->assign('android_id', "1858");
        $this->assign('android_cate', $android_cate);

        //获取IOS软件
        $ios_cate = D('DownCategory')->where(array("pid" => "1859", "status" => "1"))->field('id,title')->select();
        $this->assign('ios_name', 'IOS软件');
        $this->assign('ios_id', "1859");
        $this->assign('ios_cate', $ios_cate);

        $this->display(T('Home@jf96umobile/Widget/softCate'));
    }

    /**
     * 产品标签列表
     *
     */
    public function ptagList()
    {
        $tag = I('tag');
        if (!$tag) {
            $this->error('页面不存在！');
        }
        //获取标签相关信息
        $info = M('ProductTags')->where("name = '" . $tag . "'")->field(true)->find();
        if (!$info) {
            $this->error('页面不存在！');
        }
        $this->assign('info', $info);
        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $where['c.status'] = 1;
        $where['b.tid'] = $info['id'];
        $where['b.type'] = 'down';
        $fields = 'a.id as id';
        //同标签下载数量多少
        $count = M('down_dsoft')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->count();
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理
        $fields = 'a.id as id,c.title as title,a.size as size,c.smallimg as smallimg';
        $lists = M('down_dsoft')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->page($p, $row)->select();
        if ($count > $p * $row) {//判断是否还有数值
            $is_more = 1;
        }
        $seo['title'] = $info['title'] . '下载_96u手游网';
        $seo['keywords'] = $info['title'] . '下载';
        $seo['description'] = $info['title'] . '下载';
        $this->assign('is_more', $is_more);
        $this->assign('lists', $lists);// 赋值数据集
        $this->assign('tid', $tag);// 标签id
        $this->assign('p', $p);
        //SEO
        $this->assign("SEO", $seo);
        // 模板选择
        $this->display(T('Home@jf96umobile/Widget/taglist'));
    }


    /**
     * 描述：产品标签列表（只取手机下载分类）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagList()
    {
        $tag = I('tag');
        if (!$tag) {
            $this->error('页面不存在！');
        }
        //获取标签相关信息
        $info = M('Tags')->where("name = '" . $tag . "'")->field(true)->find();
        if (!$info) {
            $this->error('页面不存在！');
        }
        $this->assign('info', $info);
        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $where['c.status'] = 1;
        $where['b.tid'] = $info['id'];
        $where['b.type'] = 'down';
        $fields = 'a.id as id';
        //同标签下载数量多少
        $count = M('down_dsoft')->alias('a')->join('__TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->count();
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理
        $fields = 'a.id as id,c.title as title,a.size as size,c.smallimg as smallimg';
        $lists = M('down_dsoft')->alias('a')->join('__TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->page($p, $row)->select();
        if ($count > $p * $row) {// 判断是否还有数值
            $is_more = 1;
        }
        $seo['title'] = $info['title'] . '下载_96u手游网';
        $seo['keywords'] = $info['title'] . '下载';
        $seo['description'] = $info['title'] . '下载';
        $this->assign('is_more', $is_more);
        $this->assign('lists', $lists);// 赋值数据集
        $this->assign('tid', $tag);// 标签id
        $this->assign('p', $p);
        //SEO
        $this->assign("SEO", $seo);
        // 模板选择
        $this->display(T('Home@jf96umobile/Widget/taglist'));
    }

    /**
     * 产品标签SEO
     * @param array $params SEO数组
     * @return void
     */
    public function pTagsSeo($tag = '')
    {
        if (!empty($tag)) {
            $rs = M('Tags')->where("name = '" . $tag . "'")->field(true)->find();
            $seo['title'] = $rs['meta_title'] ? $rs['meta_title'] : C('TAGS_DEFAULT_TITLE');
            $seo['keywords'] = $rs['keywords'] ? $rs['keywords'] : C('TAGS_DEFAULT_KEY');
            $seo['description'] = $rs['description'] ? $rs['description'] : C('TAGS_DEFAULT_DESCRIPTION');
        }
        return $seo;
    }
    /**--------------------------------------------------------谭坚Widget end-----------------------------------------------*/
    /**
     * 作者:肖书成
     * 描述:专题，专区，K页面合集，当然也可以用做厂商，这里没做设计
     * 时间:2015-12-03
     */
    public function hj()
    {
        $key = I('key');
        if (!in_array($key, array('f', 'b', 's'))) {
            return false;
        }

        $table = array('f' => 'Feature', 'b' => 'Batch', 's' => 'Special');
        $where = array(
            'f' => 'pid = 0 AND interface = 1 AND enabled = 1',
            'b' => 'pid = 0 AND interface = 1 AND enabled = 1',
            's' => ''
        );

        $baseWidget = new \Common\Controller\WidgetController();
        $baseWidget->hj('Home@jf96umobile/Widget/hj', $table[$key], $where[$key], 'id DESC', 12, true);
    }

    /**
     * 作者肖书成
     * 描述:厂商的Widget
     * 功能:随机调取厂商页
     * @param $id
     */
    public function cs($id)
    {
        $count = M('Company')->where('status = 1 AND id != ' . $id)->count('id');
        $count = (int)$count;

        if ($count > 4) {
            $rand = mt_rand(0, $count - 4);
            $lists = M('Company')->where('status = 1 AND id != ' . $id)->limit($rand, 4)->select();
        } elseif ($count > 0) {
            $lists = M('Company')->where('status = 1 AND id != ' . $id)->select();
        } else {
            return;
        }

        $url = C('MOBILE_STATIC_URL');

        if (empty($lists)) {
            return;
        }

        foreach ($lists as $k => &$v) {
            $v['url'] = $url . '/' . (substr($v['path'], 0, 1) == '/' ? substr($v['path'], 1) : $v['path']);
            $v['url'] = substr($url, -1) == '/' ? $v['url'] : $v['url'] . '/';
        }
        unset($v);

        $this->assign('lists', $lists);
        $this->display('Widget/cs');

    }


    public function indexSlider($pos)
    {
        $lists = $this->docAndDown($pos, 3, 3, 3);
        $this->assign("lists", $lists);
        $this->display('Widget/slider');
    }

    private function docAndDown($position, $model = '3', $docnum = '6', $downnum = '6')
    {
        if (!empty($position)) {
            $position_str = " AND position & $position";
        }
        if ($model == 3 || $model == 2) {
            $down = M("Down")->where("status=1" . $position_str)->field("id,title,description,update_time,smallimg,cover_id,vertical_pic")->limit($downnum)->order("update_time desc")->select();
            $downa = array();
            foreach ($down as $key => $val) {
                $downa[] = array(
                    'title' => $val['title'],
                    'url' => str_replace('index.html', '', staticUrl("detail", $val['id'], "Down")),
                    'description' => $val['description'],
                    'update_time' => $val['update_time'],
                    'smallimg' => $val['smallimg'],
                    'cover_id' => $val['cover_id'],
                    'vertical_pic' => $val['vertical_pic']
                );
            }
        }
        if ($model == 3 || $model == 1) {
            $doc = M("Document")->where("status=1" . $position_str)->field("id,title,description,update_time,smallimg,cover_id,vertical_pic")->limit($docnum)->order("update_time desc")->select();
            $doca = array();
            foreach ($doc as $key => $val) {
                $doca[] = array(
                    'title' => $val['title'],
                    'url' => staticUrl("detail", $val['id'], "Document"),
                    'description' => $val['description'],
                    'update_time' => $val['update_time'],
                    'smallimg' => $val['smallimg'],
                    'cover_id' => $val['cover_id'],
                    'vertical_pic' => $val['vertical_pic']
                );
            }
        }
        if (empty($doca)) {
            $lists = $downa;
        } else {
            if (empty($downa)) {
                $lists = $doca;
            } else {
                $lists = array_merge($downa, $doca);
            }
        }
        $lists = array_sort($lists, 'update_time', 'DESC');
        return $lists;
    }
}
