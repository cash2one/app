<?php
// +----------------------------------------------------------------------
// | 描述:qbaobei widget控制器文件
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-5-22 上午10:07    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Home\Widget;

use Home\Controller\BaseController;

/**
 * 描述：qbaobei widget控制器类
 * 主要做一些特殊模块处理
 * Class QbaobeimobileWidget
 *
 * @package Home\Widget
 *          Author:谭坚
 *          Version:1.0.0
 *          Modify Time:
 *          Modify Author:
 */
class QbaobeiWidget extends BaseController
{
    /**
     * 首页头条
     * Author:Liupan
     */
    public function indexHeadline()
    {
        $m                  = M('Document');
        $lists              = $m->where("home_position & 8 AND `status` = '1'")->order("update_time desc")->limit(2)->select();
        $cid1               = $lists[0]['category_id'];
        $cid2               = $lists[1]['category_id'];
        $list1              = $m->where(array(
            'category_id' => $cid1,
            'status'      => '1'
        ))->limit(4)->select();
        $list2              = $m->where(array(
            'category_id' => $cid2,
            'status'      => '1'
        ))->limit(4)->select();
        $headline1['id']    = $lists[0]['id'];
        $headline2['id']    = $lists[1]['id'];
        $headline1['title'] = $lists[0]['title'];
        $headline2['title'] = $lists[1]['title'];
        $this->assign('headline1', $headline1);
        $this->assign('headline2', $headline2);
        $this->assign('lists1', $list1);
        $this->assign('lists2', $list2);
        $this->display(T('Home@qbaobei/Widget/indexHeadline'));
    }

    /**
     * 首页育儿
     * Author:Liupan
     */
    public function indexYuer($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $cate               = D('Category')->getChildrenId($id);
        $map['category_id'] = array(
            'in',
            $cate
        );
        $map['status']      = '1';
        $lists              = D('Document')->order('update_time desc')->where($map)->limit("6")->select();
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobei/Widget/indexYuer'));
    }

    /**
     *
     * Author:Liupan
     */
    public function indexHeadlineMini($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $cate               = D('Category')->getChildrenId($id);
        $map['category_id'] = array(
            'in',
            $cate
        );
        $map['status']      = '1';
        $lists              = D('Document')->order('update_time desc')->where($map)->find();
        $url                = staticUrl('detail', $lists['id'], 'Document');
        $title              = msubstr($lists['title'], 0, 18, 'utf-8', false);
        echo "<a class=\"info\" target=\"_blank\" href=\"" . $url . "\">" . $title . "</a>";
    }

    /**
     * 描述：获取最顶部导航
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getHeadNav()
    {
        //头部导航显示导航名称，先写死
        $title       = array(
            '752' => '备孕',
            '753' => '孕期',
            '754' => '分娩',
            '779' => '0-1岁',
            '780' => '1-3岁',
            '781' => '3-6岁'
        );
        $w           = array();
        $w['id']     = array(
            'in',
            '752,753,754,779,780,781'
        ); //根据分类获取路径和地址
        $list        = M('Category')->field('id,path_lists')->where($w)->order('id asc')->select();
        $static_root = C('STATIC_URL') ? C('STATIC_URL') : 'http://www.qbaobei.com';
        $static_root && $static_root = substr($static_root, - 1) == '/' ? substr($static_root, 0, strlen($static_root) - 1) : $static_root;
        if (is_array($list)) {
            foreach ($list as &$val) {
                $val['url']   = staticUrl('lists', $val['id'], 'Document');
                $key          = $val['id'];
                $val['title'] = $title[$key];
            }
        }
        $this->assign('lists', $list);
        $this->assign('static_url', $static_root);
        $this->assign('home_url', 'http://home.qbaobei.com/');
        $this->assign('bbs_url', 'http://bbs.qbaobei.com/');
    }

    /**
     * 描述：获取频道页相关分类的值
     *
     * @param string $keywords
     *
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getChannelCates($keywords = "jiaoyu/")
    {
        $where               = array();
        $where['path_lists'] = array(
            'like',
            $keywords . '%'
        );
        //  echo M('Category')->where($where)->fetchSql(true)->select();
        $cate_ids = M('Category')->where($where)->getField('id', true);

        return $cate_ids;
    }

    /**
     * 描述：频道首页热门推荐文章（右边的）
     *
     * @param string $keywords
     * @param int $limit
     *
     * @return mixed
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    protected function getHotDocument($keywords = "jiaoyu/", $limit = 16)
    {
        $cate_ids           = $this->getChannelCates($keywords);
        $map                = array();
        $map['category_id'] = array(
            'in',
            $cate_ids
        );
        $map['_string']     = "pc_position & 16";
        $w['map']           = $map;
        $w['limit']         = $limit;
        $w['order']         = 'update_time desc';
        $hot_list           = D('Document')->listsWhere($w, true); //频道首页头条推荐
        return $hot_list;
    }

    /**
     * 描述：首页头条
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getTopDocument($keywords = "jiaoyu/", $limit = "2")
    {

        $cate_ids           = $this->getChannelCates($keywords);
        $map                = array();
        $map['category_id'] = array(
            'in',
            $cate_ids
        );
        $map['_string']     = "pc_position & 8";
        $w['map']           = $map;
        $w['limit']         = $limit;
        $w['order']         = 'update_time desc';
        $list               = D('Document')->listsWhere($w, true); //频道首页头条推荐
        return $list;
    }

    /**
     * 描述：频道首页热点文章推荐
     *
     * @param string $keywords
     * @param int $limit
     *
     * @return array
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    protected function getFocusDocument($keywords = "jiaoyu/", $limit = 2)
    {
        $cate_ids           = $this->getChannelCates($keywords);
        $map                = array();
        $map['category_id'] = array(
            'in',
            $cate_ids
        );
        $map['_string']     = "pc_position & 2";
        //只推荐5个分类，现在写死，以后要改，可以改成参数
        //  echo M('Document')->field('category_id')->where($map)->limit('5')->order('update_time desc')->group('category_id')->fetchSql(true)->select();
        $rs         = M('Document')->field('category_id')->where($map)->limit('5')->order('update_time desc')->group('category_id')->select();
        $limit      = $limit ? $limit : 1;
        $focus_list = array();
        if (is_array($rs)) {
            $where            = array();
            $where['_string'] = "pc_position & 2";
            foreach ($rs as $key => $val) {
                if (is_numeric($val['category_id'])) {
                    $where['category_id'] = array(
                        'eq',
                        $val['category_id']
                    );
                    //获取文章信息
                    $doc                     = M('Document')->where($where)->field('id,title')->limit($limit)->select();
                    $focus_list[$key]['doc'] = $doc;
                    //获取分类信息
                    $catetile = M('Category')->where("id=" . $val['category_id'])->field('id,title')->find();

                    $focus_list[$key]['catetitle'] = $catetile['title'];
                    $focus_list[$key]['cateid']    = $catetile['id'];
                }
            }
        }

        return $focus_list;
    }

    /**
     * 描述：分类编辑推荐
     *
     * @param string $keywords
     * @param int $limit
     *
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getEditDocument($keywords = "jiaoyu/", $limit = 5)
    {
        $cate_ids           = $this->getChannelCates($keywords);
        $map                = array();
        $map['category_id'] = array(
            'in',
            $cate_ids
        );
        $map['_string']     = "pc_position & 4";
        $w['map']           = $map;
        $w['limit']         = $limit;
        $w['order']         = 'update_time desc';
        $list               = D('Document')->listsWhere($w, true); //频道首页头条推荐
        return $list;
    }


    /**
     * 描述：获取seo信息
     *
     * @param      $type
     * @param null $module
     * @param null $id
     *
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
                $seo['title']       = C('WEB_SITE_TITLE');
                $seo['keywords']    = C('WEB_SITE_KEYWORD');
                $seo['description'] = C('WEB_SITE_DESCRIPTION');

                return $seo;
                break;
            case 'moduleindex':
                if (empty($module)) {
                    return;
                }
                $module             = strtoupper($module);
                $seo['title']       = C('' . $module . '_DEFAULT_TITLE');
                $seo['keywords']    = C('' . $module . '_DEFAULT_KEY');
                $seo['description'] = C('' . $module . '_DEFAULT_DESCRIPTION');

                return $seo;
                break;
            case 'special':
                $id                 = empty($id) ? '1' : $id;
                $cate               = D('StaticPage')->where("id='$id'")->find();
                $seo['title']       = empty($cate['title']) ? $cate['title'] : $cate['title'];
                $seo['keywords']    = empty($cate['keywords']) ? '' : $cate['keywords'];
                $seo['description'] = empty($cate['description']) ? '' : $cate['description'];

                return $seo;
                break;
            case 'category':
                $id = empty($id) ? '1' : $id;
                if (empty($module)) {
                    return;
                }
                $module       = strtoupper($module);
                $cate_name    = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE'  => 'PackageCategory',
                    'DOWN'     => 'DownCategory',
                    'GALLERY'  => 'GalleryCategory',
                );
                $cate         = D($cate_name[$module])->where("id='$id'")->find();
                $seo['title'] = empty($cate['meta_title']) ? $cate['title'] : $cate['meta_title'];
                if (C('SEO_STRING')) {
                    $t                                 = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                    $t[(int)C('SEO_PRE_SUF')]          = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode('_', $t);
                }
                $seo['keywords']    = empty($cate['keywords']) ? C('' . $module . '_DEFAULT_KEY') : $cate['keywords'];
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
                    $t                                 = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                    $t[(int)C('SEO_PRE_SUF')]          = C('SEO_STRING');
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
                            $tags            = M('Tags')->where(array(
                                'id' => array(
                                    'in',
                                    $tag_ids
                                )
                            ))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);
                        }
                    } else {
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title']) ? $detail['title'] : $detail['title'] . ',' . $detail['sub_title'];
                    }
                } else {
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if (empty($detail['seo_description'])) {
                    $des = array(
                        'Document' => 'description',
                        'Down'     => 'conductor',
                        'Package'  => 'content'
                    );
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

    /**
     * 描述：感冒频道首页数据获取
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function ganmao()
    {
        $model      = 'Document'; //模块名称
        $cate_model = 'Category'; //模块分类名称
        //频道页头条
        $top_list = $this->getGanmaoList($model, $cate_model, '8', '0', 'id,title,description', '320', '3', 'id DESC');
        //编辑推荐
        $editor_list = $this->getGanmaoList($model, $cate_model, '4', '1', 'id,title,smallimg', '320', '3', 'view DESC');
        //热点推荐
        $hot_list = $this->getGanmaoList($model, $cate_model, '16', '1', 'id,title,smallimg', '320', '8', 'view DESC');
        $hot_pic  = $this->getGanmaoList($model, $cate_model, '16', '1', 'id,title,smallimg', '320', '1', 'view DESC');
        //感冒症状
        $cold_zz = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '321', '11');
        //感冒预防
        $cold_yf = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '323', '11');
        //感冒用药
        $cold_yy = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '322', '11');
        //感冒治疗
        $cold_zl = $this->getGanmaoList($model, $cate_model, '0', '1', 'id,title,smallimg', '324', '6');
        //感冒食疗
        $cold_sl = $this->getGanmaoList($model, $cate_model, '0', '1', 'id,title,smallimg', '1081', '6');
        //孕妇感冒
        $cold_yunfu = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '1078', '11');
        //哺乳期感冒
        $cold_prq = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '1079', '11');
        //小儿感冒
        $cold_xe = $this->getGanmaoList($model, $cate_model, '0', '1', 'id,title,smallimg', '1077', '11');
        //妈妈食谱
        $mother_food = $this->getGanmaoList($model, $cate_model, '0', '1', 'id,title,smallimg', '690', '4');
        //宝宝食谱
        $child_food = $this->getGanmaoList($model, $cate_model, '0', '0', 'id,title,smallimg', '691', '4');
        //育儿百科
        $yuer_baike = $this->getGanmaoList($model, $cate_model, '0', '1', 'id,title,smallimg', '747', '9');
        //精彩贴图
        $pic_list = $this->getGanmaoList('gallery', 'gallery_category', '0', '1', 'id,title,smallimg', '0', '12');
        $this->assign(array(
            'top_list'    => $top_list,
            'editor_list' => $editor_list,
            'hot_pic'     => $hot_pic,
            'hot_list'    => $hot_list,
            'cold_zz'     => $cold_zz,
            'cold_yf'     => $cold_yf,
            'cold_yy'     => $cold_yy,
            'cold_zl'     => $cold_zl,
            'cold_sl'     => $cold_sl,
            'cold_yunfu'  => $cold_yunfu,
            'cold_prq'    => $cold_prq,
            'cold_xe'     => $cold_xe,
            'mother_food' => $mother_food,
            'child_food'  => $child_food,
            'yuer_baike'  => $yuer_baike,
            'pic_list'    => $pic_list,
            'SEO'      => WidgetSEO(array(
                    'special',
                    null,
                    91
                ))
        ));
        $this->display('Widget/ganmao');
    }

    /**
     * 描述：感冒频道页数据获取公共方法
     *
     * @param string $model
     * @param string $cate_model
     * @param int $position
     * @param int $smallimg
     * @param string $field
     * @param string $cate_id
     * @param int $limit
     * @param string $order
     *
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getGanmaoList($model = "Document", $cate_model = 'Category', $position = 0, $smallimg = 0, $field = "*", $cate_id = '320', $limit = 4, $order = 'update_time DESC')
    {
        $where           = array();
        $where['status'] = 1;
        if (!empty($position)) {
            $where['_string'] = "pc_position & " . $position;
        }
        if (!empty($smallimg)) {
            $where['smallimg'] = array(
                'gt',
                0
            );
        }
        $list = $this->getDocList($model, $cate_model, $where, $field, $cate_id, $limit);
        if (empty($list)) {
            $map           = array();
            $map['status'] = 1;
            if (!empty($smallimg)) {
                $map['smallimg'] = array(
                    'gt',
                    0
                );
            }
            $start = rand(1, 20);
            $limit = $start . ',' . $limit;
            $list  = $this->getDocList($model, $cate_model, $map, $field, $cate_id, $limit, $order);
        }

        return $list;
    }

    /**
     * 描述：获取某个分类下所有文章数据
     *
     * @param string $model
     * @param string $cate_model
     * @param string $where
     * @param string $field
     * @param string $cate_id
     * @param string $limit
     * @param string $order
     *
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getDocList($model = 'Document', $cate_model = 'Category', $where = '1=1', $field = '*', $cate_id = '', $limit = '10', $order = 'update_time DESC')
    {
        if (empty($model)) {
            $model = 'Document';
        }
        if (empty($cate_model)) {
            $cate_model = 'Category';
        }
        if (empty($where)) {
            $where = array();
        }
        if (is_numeric($cate_id) && $cate_id > 0) {
            $cid_array = D($cate_model)->getAllChildrenId($cate_id);
            if (!empty($cid_array)) {
                $where['category_id'] = array(
                    'in',
                    $cid_array
                );
            }
        }
        $list = M($model)->field($field)->where($where)->limit($limit)->order($order)->select();

        return $list;
    }

    /**************xiao*********************/
    //健康频道页
    public function health()
    {
        /** 顶部推荐 **/
        $cate = M('Category')->field('id,title,pid')->select();

        //母婴健康的所有子分类
        $mCate = $this->getSubs($cate, 4);

        //育儿导航的所有子分类
        $yCate = $this->getSubs($cate, 145);

        //母婴健康所有子分类 + 育儿导航所有子分类
        $myCate = array_merge($mCate, $yCate);

        $myIds = array_column($myCate, 'id');

        $myStrIds = implode(',', $myIds);

        //头条推荐

        $headline = $this->_getDoucument('description', "category_id IN($myStrIds) AND pc_position & 8", 'update_time DESC', 2);

        //热点推荐
        $hotImg    = $this->_getDoucument('smallimg', "smallimg > 0 AND category_id IN($myStrIds) AND pc_position & 2", 'update_time DESC', false, true);
        $w_hot_img = ($hotImg['id'] ? "a.id != " . $hotImg['id'] : 1);
        $hot       = M('Document')->alias('a')->field('a.id,a.title,b.id cate_id,b.title cate')->join('__CATEGORY__ b ON a.category_id = b.id')->where($w_hot_img . " AND a.status = 1 AND a.category_id IN($myStrIds) AND a.pc_position & 2")->order('a.id DESC')->limit(5)->select();

        //编辑推荐
        $author = $this->_getDoucument('smallimg', "smallimg > 0 AND category_id IN($myStrIds) AND pc_position & 4", 'update_time DESC');

        //宝宝护理
        $hl = $this->_getDoucument('smallimg', "smallimg > 0 AND category_id IN(104,105,106,107) ", 'update_time DESC', 5, false);


        /** 孕育期 **/
        //中间列表 备孕、怀孕、分娩、产后
        $yunyu = $this->_getCate('id IN(19,21,192,22)', 'id ASC', true);
        $yunyu = custom_sort(array(
            19,
            21,
            192,
            22
        ), $yunyu);

        foreach ($yunyu as $k => &$v) {
            $model        = M('Category')->field('id')->where('pid = ' . $v['id'])->buildSql();
            $v['article'] = $this->_getDoucumentFind('category_id IN(' . $model . ')');
            $v['child']   = $this->_getCate('pid = ' . $v['id'], 'id ASC', true, 4);
            foreach ($v['child'] as $k1 => &$v1) {
                $v1['article'] = $this->_getDoucumentFind('id != ' . $v['article']['id'] . ' AND category_id =' . $v1['id']);
            }
        }
        unset($v);
        unset($v1);


        /** 哺育期 **/
        //中间列表 新生儿、0-1岁、1-3岁、3-6岁
        $buyuqi = $this->_getCate('id IN(934,129,130,131)', 'id ASC', true);
        $buyuqi = custom_sort(array(
            934,
            129,
            130,
            131
        ), $buyuqi);

        foreach ($buyuqi as $k => &$v) {
            $model        = M('Category')->field('id')->where('pid = ' . $v['id'])->buildSql();
            $v['article'] = $this->_getDoucumentFind('category_id IN(' . $model . ')');
            $v['child']   = $this->_getCate('pid = ' . $v['id'], 'id ASC', true, 4);
            foreach ($v['child'] as $k1 => &$v1) {
                $v1['article'] = $this->_getDoucumentFind('id != ' . $v['article']['id'] . ' AND category_id =' . $v1['id']);
            }
        }
        unset($v);
        unset($v1);

        /** 生  活 **/
        //中间列表 婚姻家庭、娱乐明星、星座奇缘、百科常识
        $life = $this->_getCate('id IN(116,117,120,118)', 'id ASC', true);
        $life = custom_sort(array(
            116,
            117,
            120,
            118
        ), $life);

        foreach ($life as $k => &$v) {
            $v['article'] = M('Document')->field('id,title,smallimg')->where('status =1 AND smallimg > 0 AND category_id = ' . $v['id'])->limit(6)->select();
        }
        unset($v);

        $this->assign(array(
            'headline' => $headline,
            //头条
            'hotImg'   => $hotImg,
            //热点图片
            'hot'      => $hot,
            //热点
            'hl'       => $hl,
            //宝宝护理
            'author'   => $author,
            //编辑推荐
            'yunyu'    => $yunyu,
            //孕育
            'buyuqi'   => $buyuqi,
            //哺育期
            'life'     => $life,
            //生活
            'SEO'      => WidgetSEO(array(
                'special',
                null,
                60
            ))
        ));

        $this->display('Widget/health');
    }


    //获取某个分类的所有子分类
    public function getSubs($categorys, $catId = 0, $level = 1)
    {
        $subs = array();
        foreach ($categorys as $item) {
            if ($item['pid'] == $catId) {
                $item['level'] = $level;
                $subs[]        = $item;
                $subs          = array_merge($subs, $this->getSubs($categorys, $item['id'], $level + 1));

            }

        }

        return $subs;
    }


    private function _getCate($where, $order = 'id DESC', $isAll = false, $limit = false)
    {
        $model = M('Category')->field('id,title,path_lists')->where($where)->order($order);
        $limit && $model->limit($limit);

        return $isAll ? $model->select() : $model->find();
    }

    private function _getDoucumentFind($where = 1)
    {
        return M('Document')->field('id,title')->where('status = 1 AND ' . $where)->order('id DESC')->find();
    }

    //获取文章数据
    private function _getDoucument($field = false, $where = 1, $order = 'id DESC', $limit = 6, $isFind = false)
    {
        $model = M('Document')->field('id,title' . ($field ? ',' . $field : ''))->where('status = 1 AND ' . $where)->order($order);
        $limit && $model->limit($limit);

        return $isFind ? $model->find() : $model->select();
    }

    public function healthBoxRight()
    {
        $this->display('Widget/health_box_right');
    }

    //健康频道页的子导航
    public function healthNav($id)
    {
        $nav = $this->_getCate('pid = ' . $id, 'id ASC', true);
        if ($nav) {
            $this->assign('nav', $nav);
            $this->display(T('Home@qbaobei/Widget/healthNav'));
        }
    }

    //健康频道的<li><a></a></li>标签的综合 调用方法
    public function healthLi($where = 1, $limit = 9, $order = 'id DESC')
    {
        $li = $this->_getDoucument(false, $where, $order, $limit);
        if ($li) {
            $this->assign('li', $li);
            $this->display('Widget/healthLi');
        }
    }

    /***********************xiao*************************/

    /**********教育频道首页******************************/
    /**
     * 描述：教育首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachIndex()
    {
        //echo T('Home@qbaobei/Widget/Teach/Base/common');
        $id = 61;
        if (is_numeric($id)) {
            $map['id']          = array(
                'eq',
                $id
            );
            $rs                 = M('static_page')->field('keywords,title,description')->where($map)->find();
            $seo                = array();
            $seo['title']       = $rs['title'];
            $seo['keywords']    = $rs['keywords'];
            $seo['description'] = $rs['description'];
            $this->assign('SEO', $seo);
        }
        $this->display(T('Home@qbaobei/Widget/Teach/Index/index'));
    }


    /**
     * 描述：教育频道首页上面中间部分
     *
     * @param string $keywords
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function teachMiddle($keywords = "jiaoyu/")
    {
        $focus_list = $this->getFocusDocument($keywords, 2); //频道首页热点文章
        $list       = $this->getTopDocument($keywords, 6); //频道首页头条推荐
        $first      = $list[0];
        array_shift($list);
        $second = $list[0];
        array_shift($list);
        $hot_list = $this->getHotDocument($keywords); //右侧热门推荐
        $hot_pic  = array_pop($hot_list);
        $cate_ids = $this->getChannelCates($keywords);
        if ($cate_ids) {
            $w['id'] = array(
                'in',
                $cate_ids
            );
        }
        $cate_list = M('Category')->field('id,title')->where($w)->limit(6)->select();
        $this->assign('cate_list', $cate_list);
        $this->assign('focus_list', $focus_list);
        $this->assign('hot_pic', $hot_pic); //右边热门推荐最后一个图片数据
        $this->assign('list', $list);
        $this->assign('first', $first);
        $this->assign('second', $second);
        $this->assign('hot_list', $hot_list); //右侧热门推荐
        $this->display(T('Home@qbaobei/Widget/Teach/teachmiddle'));
    }


    /**
     * 描述：获取教育首页导航nav
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachNav()
    {
        $sort        = array(
            5,
            205,
            134,
            138,
            142,
            197
        );
        $w           = array();
        $w['id']     = array(
            'in',
            $sort
        );
        $list        = M('Category')->field('id,title,path_lists')->where($w)->order('id asc')->select();
        $static_root = C('STATIC_URL') ? C('STATIC_URL') : 'http://www.qbaobei.com';
        $static_root && $static_root = substr($static_root, - 1) == '/' ? substr($static_root, 0, strlen($static_root) - 1) : $static_root;
        $model          = D('Category');
        $child_list_arr = array(); //子类
        if (is_array($list)) {
            foreach ($list as &$val) {
                // $dir = '/' . substr($val['path_list'], 0, strrpos($val['path_list'], '/'));
                $val['url'] = staticUrl('lists', $val['id'], 'Document');
                $child_list = array();
                if (is_numeric($val['id'])) {
                    if ($val['id'] == "5") {
                        $val['title'] = '教育首页';
                        $val['url']   = $static_root . '/jiaoyu/';
                    }
                    $child_ids = $model->getChildrenId($val['id']);

                    $child_ids = explode(',', $child_ids);
                    array_shift($child_ids);
                    $where = array();
                    if (!empty($child_ids)) {
                        $where['id']      = array(
                            'in',
                            $child_ids
                        );
                        $where['_string'] = "(path_lists is not null )  AND  ( path_lists <>'') ";
                        $child_list       = M('Category')->field('id,title,path_lists')->where($where)->order('id asc')->limit('11')->select();
                    }
                }
                $val['child'] = $child_list;
            }
            $nav_list = array();
            foreach ($sort as $key => $value) {
                foreach ($list as $v) {
                    if ($v['id'] == $value) {
                        $nav_list[$key]       = $v;
                        $child_list_arr[$key] = $v['child'];
                        break;
                    }
                }
            }
        }
        $this->assign('lists', $nav_list);
        $this->assign('child_lists', $child_list_arr);
        $this->assign('static_url', $static_root);
        $this->assign('home_url', 'http://home.qbaobei.com/');
        $this->assign('bbs_url', 'http://bbs.qbaobei.com/');
        $this->display(T('Home@qbaobei/Widget/Teach/teachnav'));

    }

    /**
     * 描述：教育频道首页编辑推荐
     *
     * @param string $keywords
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachEdit($keywords = 'jiaoyu/')
    {
        $list  = $this->getEditDocument($keywords);
        $first = array_shift($list);
        $this->assign('first', $first);
        $this->assign('lists', $list);
        $this->display(T('Home@qbaobei/Widget/Teach/teachedit'));
    }

    /**
     * 描述：获取顶部导航
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachHead()
    {
        $this->getHeadNav();
        $this->display(T('Home@qbaobei/Widget/Teach/teachheader'));
    }

    /**
     * 描述：亲宝贝阶段教育模块调用（中间部分）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachStageMiddle()
    {

        //分类名称，先写死
        $title   = array(
            '205' => '胎教',
            '779' => '0-1岁',
            '780' => '1-3岁',
            '781' => '3-6岁'
        );
        $w       = array();
        $w['id'] = array(
            'in',
            '205,779,780,781'
        ); //根据分类获取路径和地址
        $list    = M('Category')->field('id,path_lists')->where($w)->order('id asc')->select();
        //  $static_root = C('STATIC_URL') ? C('STATIC_URL') : 'http://www.qbaobei.com';
        //  $static_root && $static_root = substr($static_root, -1) == '/' ? substr($static_root, 0 , strlen($static_root)-1) : $static_root;
        $cate_model   = D('Category'); //分类模型
        $limit        = 6; //分类文章显示6条
        $cate_ids_tmp = ''; //防止递归获取分类
        if (is_array($list)) {
            foreach ($list as &$val) {
                //  $dir = '/' . substr($val['path_list'], 0, strrpos($val['path_list'], '/'));
                $val['url']   = staticUrl('lists', $val['id'], 'Document');
                $key          = $val['id'];
                $val['title'] = $title[$key];
                if (is_numeric($val['id'])) {
                    $cate_id = $cate_model->getAllChildrenId($val['id']);
                    //同一次请求多次获取分类全部子类（由于获取全部子类用到了递归方法）
                    if ($cate_ids_tmp) {
                        $cate_ids = str_replace($cate_ids_tmp, '', $cate_id);
                    } else {
                        $cate_ids = $cate_id;
                    }
                    $cate_ids_tmp = $cate_id . ',';
                    // dump($cate_ids);
                    $where                = array();
                    $where['category_id'] = array(
                        'in',
                        $cate_ids
                    );
                    $where['status']      = 1;
                    $docinfo              = M('Document')->field('id,title,smallimg')->where($where)->limit($limit)->order('update_time desc')->select(); //获取分类的文章
                    $val['pic_doc']       = $docinfo[0]; //图片推荐
                    array_shift($docinfo);
                    $val['h2_doc'] = $docinfo[0]; //大字推荐
                    array_shift($docinfo);
                    $val['doc'] = $docinfo;
                    //胎教推荐
                    if ($val['id'] == 205) {
                        $cids       = $cate_model->getChildrenId($val['id']);
                        $m          = array();
                        $m['id']    = array(
                            'in',
                            $cids
                        );
                        $other_cate = M('Category')->field('id,title')->where($m)->limit('4')->select();
                        if (is_array($other_cate)) {
                            foreach ($other_cate as &$v) {
                                $v['url'] = staticUrl('lists', $v['id'], 'Document');
                            }
                        }
                        $val['other'] = $other_cate;
                    } else {
                        $cids             = $cate_model->getChildrenId($val['id']);
                        $m                = array();
                        $m['category_id'] = array(
                            'in',
                            $cids
                        );
                        $other_doc        = M('Document')->field('id,title')->where($m)->limit('4')->order('view desc')->select();
                        if (is_array($other_doc)) {
                            foreach ($other_doc as &$v) {
                                $v['url'] = staticUrl('detail', $v['id'], 'Document');
                            }
                        }
                        $val['other'] = $other_doc;
                    }
                }
            }
        }
        $this->assign('lists', $list);
        // dump($list);

        $this->display(T('Home@qbaobei/Widget/Teach/teachStageMiddle'));

    }

    /**
     * 描述：亲宝贝频道页教育资源模块（中间部分）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function teachSourceMiddle()
    {
        //分类名称，先写死
        $title        = array(
            '199' => '书籍连载',
            '200' => '绘本在线',
            '201' => '作文大全',
            '202' => '幼儿手工'
        );
        $w            = array();
        $w['id']      = array(
            'in',
            '199,200,201,202'
        ); //根据分类获取路径和地址
        $list         = M('Category')->field('id,path_lists')->where($w)->order('id asc')->select();
        $limit        = 6; //分类文章显示6条
        $cate_model   = D('Category'); //分类模型
        $cate_ids_tmp = ''; //防止递归获取分类
        if (is_array($list)) {
            foreach ($list as &$val) {
                $val['url']   = staticUrl('lists', $val['id'], 'Document');
                $key          = $val['id'];
                $val['title'] = $title[$key];
                if (is_numeric($val['id'])) {
                    $cate_id = $cate_model->getAllChildrenId($val['id']);
                    //同一次请求多次获取分类全部子类（由于获取全部子类用到了递归方法）
                    if ($cate_ids_tmp) {
                        $cate_ids = str_replace($cate_ids_tmp, '', $cate_id);
                    } else {
                        $cate_ids = $cate_id;
                    }
                    $cate_ids_tmp         = $cate_id . ',';
                    $where                = array();
                    $where['category_id'] = array(
                        'in',
                        $cate_ids
                    );
                    // dump($where);
                    $where['status'] = 1;
                    $docinfo         = M('Document')->field('id,title,smallimg')->where($where)->limit($limit)->order('update_time desc')->select(); //获取分类的文章
                    $val['pic_doc']  = $docinfo[0]; //图片推荐
                    array_shift($docinfo);
                    $val['h2_doc'] = $docinfo[0]; //大字推荐
                    array_shift($docinfo);
                    $val['doc'] = $docinfo;
                }
            }
        }
        $this->assign('lists', $list);
        $this->display(T('Home@qbaobei/Widget/Teach/teachSourceMiddle'));
    }
    /**********教育频道首页END******************************/


    /********************      NEW     ********************/


    /**
     * Description：    获取模块下点击率最高的文章标签
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-19 16:34:26
     */

    public function miniNavi($cate)
    {
        $ids = D('Category')->getAllChildrenId($cate);
        $k   = 0;
        $m   = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("__DOCUMENT.status=1  AND category_id IN(" . $ids . ")")->order("__DOCUMENT.view desc")->limit("10")->field("*")->select();
        foreach ($m as $key => $val) {
            $id        = $val['tid'];
            $_result[] = M("Tags")->where("id =" . $id)->select();
        }
        foreach ($_result as $key => $val) {
            foreach ($val as $key1 => $val1) {

                $result[$k]['title'] = $val1['title'];
                $result[$k]['name']  = $val1['name'];
                $k                   = $k + 1;
            }
        }
        $result = array_unique_fb($result);
        $this->assign('lists', $result);
        $this->display(T('Home@qbaobei/Widget/indexMiniNavi'));
    }

    /**
     * Description：    首页树形TAG列表
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-21 18:07:59
     */

    public function treeTags($name, $cate, $field = "category_id", $ico = null, $url = "")
    {


        $k    = 0;
        $cate = split(",", $cate);
        foreach ($cate as $val) {
            $where .= "id=" . $val . " OR ";
            $cwhere .= "pid=" . $val . " OR ";
            $whereTwo .= $val . ",";
        }
        $len      = strlen($where);
        $len1     = strlen($cwhere);
        $where    = substr($where, 0, $len - 3);
        $cwhere   = substr($cwhere, 0, $len1 - 4);
        $whereTwo = rtrim($whereTwo, ",");


        if ($cate == "209" || $cate == "688") {
            $ids = D("Category")->getAllChildrenId($cate);
        } else {
            $cateList    = M('Category')->where($where)->limit("4")->select();
            $cateListAll = M('Category')->where($cwhere)->select();
            foreach ($cateListAll as $key => $val) {
                $ids .= $val['id'] . ",";
            }
            $ids = rtrim($ids, ",");

        }


        if (empty($ids)) {
            $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("$field IN('$whereTwo')")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
        } else {
            $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("$field IN('$ids')")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
        }

        foreach ($m as $key => $val) {
            $id        = $val['tid'];
            $_result[] = M("Tags")->where("id =" . $id)->select();
        }
        foreach ($_result as $key => $val) {
            foreach ($val as $key1 => $val1) {
                $result[$k]['title'] = $val1['title'];
                $result[$k]['name']  = $val1['name'];
                $k                   = $k + 1;
            }
        }
        $result = array_unique_fb($result);
        $this->assign('cateList', $cateList);
        $this->assign('name', $name);
        $this->assign('lists', $result);
        $this->assign('ico', $ico);
        $this->assign('url', $url);
        $this->display(T('Home@qbaobei/Widget/treeTags'));
    }

    public function treeTags1($name, $field = "category_id")
    {
        $cate = "19,20,21,22";
        $k    = 0;
        $cate = split(",", $cate);
        foreach ($cate as $val) {
            $where .= "id=" . $val . " OR ";
            $cwhere .= "pid=" . $val . " OR ";
            $whereTwo .= $val . ",";
        }
        $len      = strlen($where);
        $len1     = strlen($cwhere);
        $where    = substr($where, 0, $len - 3);
        $cwhere   = substr($cwhere, 0, $len1 - 4);
        $whereTwo = rtrim($whereTwo, ",");


        $cateList  = M('Category')->where($where)->limit("4")->select();
        $cateList1 = M('Category')->where($cwhere)->select();

        foreach ($cateList1 as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids = rtrim($ids, ",");


        $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("$field IN('$ids')")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();

        foreach ($m as $key => $val) {
            $id        = $val['tid'];
            $_result[] = M("Tags")->where("id =" . $id)->select();
        }
        foreach ($_result as $key => $val) {
            foreach ($val as $key1 => $val1) {

                $result[$k]['title'] = $val1['title'];
                $result[$k]['name']  = $val1['name'];
                $k                   = $k + 1;
            }
        }

        var_dump($result);

    }


    public function childCateTag($cate)
    {
        $k        = 0;
        $cateList = M('Category')->where(array( "pid" => $cate ))->select();
        foreach ($cateList as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids = rtrim($ids, ",");
        if (empty($ids)) {
            $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where(array( 'category_id' => $cate ))->order("__DOCUMENT.view desc")->limit("8")->field("*")->select();
        } else {
            $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("status=1  AND category_id IN(" . $ids . ")")->order("__DOCUMENT.view desc")->limit("8")->field("*")->select();
        }


        foreach ($m as $key => $val) {
            $id        = $val['tid'];
            $_result[] = M("Tags")->where("id =" . $id)->select();
        }
        foreach ($_result as $key => $val) {
            foreach ($val as $key1 => $val1) {
                $result[$k]['title'] = $val1['title'];
                $result[$k]['name']  = $val1['name'];
                $k                   = $k + 1;
            }
        }
        $result = array_unique_fb($result);
        $this->assign('lists', $result);
        $this->display(T('Home@qbaobei/Widget/childCateTag'));
    }

    /**
     * Description：    首页树形TAG列表
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-21 18:07:59
     */

    public function topTags($cate)
    {
        if (!is_numeric($cate)) {
            exit();
        }
        $k = 0;
        $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where(array(
            'category_id' => $cate,
            'type'        => 'document'
        ))->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
        foreach ($m as $key => $val) {
            $id        = $val['tid'];
            $_result[] = M("Tags")->where("id =" . $id)->select();
        }
        foreach ($_result as $key => $val) {
            foreach ($val as $key1 => $val1) {
                $k                   = $k + 1;
                $result[$k]['title'] = $val1['title'];
                $result[$k]['name']  = $val1['name'];
            }
        }

        $this->assign('lists', $result);
        $this->display(T('Home@qbaobei/Widget/treeTags'));
    }

    /**
     * Description：    首页内容模块
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-19 16:34:26
     */

    public function indexCon($cate, $curr = '0')
    {
        if (!is_numeric($cate)) {
            exit();
        }
        $cateList = M('Category')->where(array( "pid" => $cate ))->select();
        foreach ($cateList as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids          = rtrim($ids, ",");
        $slider       = M("Document")->alias("__DOCUMENT")->where("status=1 AND home_yuer_position & 1  AND category_id IN(" . $ids . ")")->order("update_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("3")->field("*")->select();
        $hotList      = M("Document")->alias("__DOCUMENT")->where("status=1  AND category_id IN(" . $ids . ")")->order("create_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("18")->field("*")->select();
        $headlineList = M("Document")->alias("__DOCUMENT")->where("status=1 AND category_id IN(" . $ids . ")")->order("view desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("1")->field("*")->select();
        $this->assign('slider', $slider);
        $this->assign('hotList', $hotList);
        $this->assign('headlineList', $headlineList);
        if ($curr == '1') {
            $this->assign('curr', 'current');
        } else {
            $this->assign('curr', '');
        }

        $this->display(T('Home@qbaobei/Widget/indexCon'));
    }


    /**
     * Description：    首页怀孕内容模块
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-19 16:34:26
     */

    public function indexHuaiyun($cate, $curr = '0')
    {
        if ($curr == '1') {
            $this->assign('curr', 'current');
        } else {
            $this->assign('curr', '');
        }
        if (!is_numeric($cate)) {
            exit();
        }
        $cateList = M('Category')->where(array( "pid" => $cate ))->select();
        foreach ($cateList as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids          = rtrim($ids, ",");
        $slider       = M("Document")->alias("__DOCUMENT")->where("status=1 AND home_huaiyun_position & 1  AND category_id IN(" . $ids . ")")->order("update_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("4")->field("*")->select();
        $headlineList = M("Document")->alias("__DOCUMENT")->where("status=1  AND category_id IN(" . $ids . ")")->order("create_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("8")->field("*")->select();
        $today        = M("Document")->alias("__DOCUMENT")->where("status=1  AND category_id IN(" . $ids . ")")->order("update_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("1")->field("*")->select();
        $rank         = M("Document")->alias("__DOCUMENT")->where("status=1  AND category_id IN(" . $ids . ")")->order("view desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("1")->field("*")->select();
        $this->assign('headlineList', $headlineList);
        $this->assign('headlineList', $headlineList);
        $this->assign('slider', $slider);
        $this->assign('today', $today);
        $this->assign('rank', $rank);
        $this->display(T('Home@qbaobei/Widget/indexHuaiyun'));
    }

    /**
     * Description：    首页最新内容模块
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-19 16:34:26
     */

    public function indexLatest($cate, $category = "", $curr = '0')
    {


        if ($curr == '1') {
            $this->assign('curr', 'current');
        } else {
            $this->assign('curr', '');
        }
        $ids   = D("Category")->getAllChildrenId($cate);
        $lists = M("Document")->alias("__DOCUMENT")->where("status=1  AND category_id IN(" . $ids . ")")->order("create_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("15")->field("*")->select();
        $this->assign('category', $category);
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobei/Widget/indexLatest'));
    }

    /**
     * Description：    首页百科内容模块
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-21 09:22:49
     */

    public function indexBaike($cate, $curr = '0')
    {
        if ($curr == '1') {
            $this->assign('curr', 'current');
        } else {
            $this->assign('curr', '');
        }
        $cateList = M('Category')->where(array( "pid" => $cate ))->limit("6")->select();
        $this->assign('cateList', $cateList);

        $this->display(T('Home@qbaobei/Widget/indexBaike'));
    }

    /**
     * 描述：分类内容列表页
     *
     * @param $cate
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function cateContentList($cate)
    {
        if (!is_numeric($cate)) {
            exit();
        }
        $cateList = M('Category')->where(array( "pid" => $cate ))->select();
        if (!empty($cateList)) {
            $ids = '';
            foreach ($cateList as $key => $val) {
                $ids .= $val['id'] . ",";
            }
            $ids  = rtrim($ids, ",");
            $news = M("Document")->where("status=1  AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("12")->field("*")->select();
        }

        $this->assign('news', $news);
        $this->display(T('Home@qbaobei/Widget/baikeBlock'));
    }


    /**
     * Description：    首页百科内容模块
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-21 09:22:49
     */

    public function indexZaojiao()
    {
        $cateList = M('Category')->where(array( "pid" => '5' ))->select();
        foreach ($cateList as $key => $val) {
            $id      = $val['id'];
            $allCate = M('Category')->where(array( "pid" => $id ))->select();
            if (empty($allCate)) {
                $ids .= $id . ",";
            } else {
                foreach ($allCate as $key1 => $val1) {
                    $ids .= $val1['id'] . ",";
                }
            }

        }
        $ids       = rtrim($ids, ",");
        $headline  = M("Document")->where("status=1  AND home_edu_position & 1 AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("11")->field("*")->select();
        $commended = M("Document")->where("status=1 AND home_edu_position & 2 AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("2")->field("*")->select();
        $lists     = M("Document")->where("status=1  AND category_id IN(" . $ids . ")")->order("create_time desc")->limit("10")->field("*")->select();
        $this->assign('headline', $headline);
        $this->assign('commended', $commended);
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobei/Widget/indexZaojiao'));
    }

    /**
     * Description：   早教视频内容
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-21 09:22:49
     */

    public function indexZaojiaoVideo()
    {
        $cateList = M('Category')->where(array( "pid" => '1326' ))->select();
        foreach ($cateList as $key => $val) {
            $id      = $val['id'];
            $allCate = M('Category')->where(array( "pid" => $id ))->select();
            if (empty($allCate)) {
                $ids .= $id . ",";
            } else {
                foreach ($allCate as $key1 => $val1) {
                    $ids .= $val1['id'] . ",";
                }
            }

        }
        $ids   = rtrim($ids, ",");
        $lists = M("Document")->where("status=1 AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("2")->field("*")->select();
        $tpl   = count($lists) >= 2 ? '1' : '2';
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobei/Widget/indexZaojiaoVideo' . $tpl));
    }


    /**
     * Description：     首页营养美食
     * Author:         Jeffrey Lau
     * Modify Time:    2015-9-8 09:06:57
     */

    public function nutritionalFood()
    {
        $w        = array();
        $w['pid'] = array(
            'in',
            array(
                '209',
                '686',
                '688',
                '685',
                '1213'
            )
        );
        $cateList = M('Category')->where($w)->select();
        unset($w);
        foreach ($cateList as $key => $val) {
            $id      = $val['id'];
            $allCate = M('Category')->where(array( "pid" => $id ))->select();
            if (empty($allCate)) {
                $ids .= $id . ",";
            } else {
                foreach ($allCate as $key1 => $val1) {
                    $ids .= $val1['id'] . ",";
                }
            }

        }
        $ids   = rtrim($ids, ",");
        $lists = M("Document")->where("status=1 AND home_food_position & 1 AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("4")->field("*")->select();
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobei/Widget/nutritionalFood'));
    }

    /**
     * 描述：新标签获取路径回调函数
     *
     * @param $params
     *
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function parseModuleUrl($params)
    {
        if (isset($params['category_id'])) {
            $path = $params['path_detail'];
            if (strstr($path, '{create_time|date=Ymd}')) {
                $value = date('Ymd', $params['create_time']);
                // $path = str_replace('{create_time|date=Ymd}', $value, $path);
            }
            //qbaobei路径
            if (strstr($path, '{Year}/{Month}/{Day}/{Time}')) {
                $str_date = date('Y', $params['create_time']) . '/' . date('m', $params['create_time']) . '/' . date('d', $params['create_time']) . '/' . date('His', $params['create_time']);
                // $path = str_replace('{Year}/{Month}/{Day}/{Time}', $str_date, $path);
            }
            //96u路径
            if (strstr($path, '{Y}{M}')) {
                $str_date96u = date('Y', $params['create_time']) . date('m', $params['create_time']);
                // $path = str_replace('{Y}{M}', $str_date96u, $path);
            }

            // 文档
            $url = str_replace(array(
                    '{id}',
                    '{create_time|date=Ymd}',
                    '{Year}/{Month}/{Day}/{Time}',
                    '{Y}{M}'
                ), array(
                    $params['id'],
                    $value,
                    $str_date,
                    $str_date96u
                ), $params['path_detail']) . '.html';

        } else {

            // 列表
            $filename = strtolower($params['path_lists_index']);
            if ($filename == 'index' || !$filename) {
                $url = dirname($params['path_lists']) . '/';
            } else {
                $url = dirname($params['path_lists']) . '/' . $filename . '.html';
            }

        }

        if ($url) {
            return '/' . $url;
        }
    }

    /**
     * 作者:肖书成
     * 描述:作文模块的头部部分
     * 时间:2016-2-22
     */
    public function compositionHeader()
    {
        //参数处理
        $cate = I('cate');
        if (empty($cate)) {
            $cate = "393|394|395|400|399|396|397|398";
        }
        $cate = implode(',', explode('|', $cate));

        //分类查询
        $cate_list = M('Category')->field('id,title')->where("id IN($cate) AND status = 1")->order("FIELD('id',$cate)")->select();
        if (count($cate_list) != 8) {
            $cate_list = M('Category')->field('id,title')->where("id IN(393,394,395,400,399,396,397,398) AND status = 1")->order("FIELD('id',393,394,395,400,399,396,397,398)")->select();
        }

        //页面赋值与调用
        $this->assign(array(
            'cate' => $cate_list
        ));

        $this->display('Public/composition/header');
    }

    /**
     * 作者:肖书成
     * 描述:作文模块的尾部部分
     * 时间:2016-2-22
     */
    public function compositionFoot()
    {
        $this->display('Public/composition/foot');
    }

    /**
     * 作者:肖书成
     * 描述:作文模块的频道页
     * 时间:2016-2-22
     */
    public function compositionIndex()
    {
        //分类
        $cate = M('Category')->field('id,pid,title')->where('rootid = 392 AND status = 1')->select();
        $cate = list_to_tree($cate, 'id', 'pid', 'child', 392);

        //最新作文
        $new_composition = M('Document')->field('id,title')->where('category_rootid = 392 AND status = 1')->order('update_time DESC')->limit(10)->select();

        //幼儿教育（调取浏览量最多的文章）
        $education = M('Document')->field('id,title')->where('category_id = 138 AND status = 1')->order('view DESC')->limit(10)->select();

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
                'special',
                null,
                $page_id
            ));

        $this->assign(array(
            'SEO'         =>    $SEO,
            'cate'        => $cate,
            'composition' => $new_composition,
            'education'   => $education
        ));

        $this->display('Widget/composition');
    }
}
