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
class QbaobeimobileWidget extends BaseController
{

    /**
     * 描述：获取顶级分类信息
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCategoryInfo()
    {
        $category = M('Category');
        $map['pid'] = array('eq', 0); //查询顶级分类条件
        $map['status'] = array('gt', 0);
        $map['display'] = array('eq', 1);
        $list = $category->field('id,title')->where($map)->select();
        $count = count($list) > 0 ? 1 : 0;
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->display(T('Home@qbaobeimobile/Widget/cate'));
    }

    /**
     * 描述：获取热门推荐信息
     *
     * @param array  $where
     * @param string $limit 默认推荐 5个
     * @param string $order 默认按最新时间推荐
     *                      Author:谭坚
     *                      Version:1.0.0
     *                      Modify Time:
     *                      Modify Author:
     */
    public function getPositionInfo($where = array(), $limit = '20', $order = 'a.update_time DESC')
    {
        if (empty($where)) {
            $where['a.status'] = array('eq', 1);
            $where['b.status'] = array('eq', 1);
            $where['_string'] = "a.position & 1";
        }
        $count = M('Document')->alias('a')->join('__CATEGORY__ b ON b.id = a.category_id')->field('a.id as id')->where($where)->count(); // 查询满足要求的总记录数
        $document_info = M('Document')->alias('a')->join('__CATEGORY__ b ON b.id = a.category_id')->field('a.smallimg as smallimg,a.title as title,a.id as id,a.category_id as category_id,b.title as cate_title')->limit($limit)->order($order)->where($where)->select();
        $c = $count > $limit ? 1 : 0;
        $count = count($document_info) > 0 ? 1 : 0;
        $this->assign('count', $count);
        $this->assign('list', $document_info);
        $this->assign('isMore', $c);
        $this->display(T('Home@qbaobeimobile/Widget/recommend'));
    }

    /**
     * 描述：获取顶级分类信息
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCategoryInfoToList()
    {
        $category = M('Category');
        $map['pid'] = array('eq', 0); //查询顶级分类条件
        $map['status'] = array('gt', 0);
        $map['display'] = array('eq', 1);
        $list = $category->field('id,title')->where($map)->select();
        $count = count($list) > 0 ? 1 : 0;
        $this->assign('count', $count);
        $this->assign('list', $list);
        $this->display(T('Home@qbaobeimobile/Widget/catedetail'));
    }

    /**
     * 描述：获取热门推荐列表页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getPositionListInfo($row = '5')
    {
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
        $where['a.status'] = array('eq', 1);
        $where['b.status'] = array('eq', 1);
        $where['_string'] = "a.position & 1";
        //分页获取数据
        $row = $row ? $row : 5;
        $count = M('Document')->alias('a')->join('__CATEGORY__ b ON b.id = a.category_id')->where($where)->count(); // 查询满足要求的总记录数
        //是否返回总页数
        if (I('gettotal')) {
            echo ceil($count / $row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理
        $lists = M('Document')->alias('a')->join('__CATEGORY__ b ON b.id = a.category_id')->field('a.smallimg as smallimg,a.title as title,a.id as id,a.category_id as category_id,b.title as cate_title')->where($where)->order('a.update_time DESC')->page($p, $row)->select();
        $this->assign('lists', $lists); // 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path'] . getStaticExt()); // 实例化分页类 指定路径规则

        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        //  $Page->setConfig('first','首页');
        // $Page->setConfig('last','尾页');
        $Page->setConfig('theme', '%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, $page_id)));
        $this->display(T('Home@qbaobeimobile/Widget/recomlist'));
    }

    /**
     * 描述：获取首页推荐轮播图
     * 默认4张图，按最新更新时间来推荐
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCarouselInfo($where = array(), $limit = '4', $order = 'update_time DESC')
    {
        if (empty($where)) {
            $where['status'] = array('eq', 1);
            $where['_string'] = "position & 2";
        }
        $document_info = M('Document')->field('smallimg,title,id')->limit($limit)->order($order)->where($where)->select();
        $count = count($document_info) > 0 ? 1 : 0;
        $this->assign('count', $count);
        $this->assign('list', $document_info);
        $this->display(T('Home@qbaobeimobile/Widget/carousel'));
    }

    /**
     * 描述：获取seo信息
     *
     * @param      $type
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

                return $seo;
                break;
        }
    }


    /**
     * 描述：导航模块页面
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getNav()
    {
        //        $nav_array = array(4,209,685,26);
        //        $model = M('Category');
        //        $where['id'] = array('in',$nav_array);
        //        $rs = $model->field('id,name')->where($where)->select();
        //        if(is_array($rs))
        //        {
        //            foreach($rs as $val)
        //            {
        //                $this->assign($val['name'],$val['id']);
        //            }
        //        }
        $this->display(T('Home@qbaobeimobile/Widget/nav'));
    }
    /**********************************************************亲宝贝手机版2期改版 START****************************************/
    /**
     * 描述：搜索推荐推荐逻辑
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getSearchTag()
    {
        $where = array();
        $where['status'] = array('eq', 1);
        $where['_string'] = "position & 1";
        $list = M('tags')->field('id,name,title')->where($where)->limit(27)->select();
        $count = count($list);
        $this->assign('list', $list);
        $this->assign('count', $count);
        $this->assign('maxkey', $count - 1);
        $this->display(T('Home@qbaobeimobile/Widget/searchtag'));
    }

    /**
     * 描述：获取轮播图推荐数据
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getPicPostion()
    {
        $cateid = I('cateid');
        //首页轮播图
        if (!$cateid) {
            $where = array();
            $where['status'] = array('eq', 1);
            $where['smallimg'] = array('gt', 0);
            $w = $where;
            $where['_string'] = "position & 2";
            $list = M('document')->field('id,title,smallimg')->where($where)->limit(5)->order('update_time DESC')->select();
            if (count($list) < 1) {
                $list = M('document')->field('id,title,smallimg')->where($w)->limit(5)->order('view DESC')->select();
            } //没有轮播图情况下，按照最热排序补全数据
            unset($where, $w);

        } else //分类轮播图
        {
            $where = array();
            $where['status'] = array('eq', 1);
            $where['smallimg'] = array('gt', 0);
            $where['category_id'] = $cateid;
            $w = $where;
            $where['_string'] = "position & 32";
            $list = M('document')->field('id,title,smallimg')->where($where)->limit(5)->order('update_time DESC')->select();
            if (count($list) < 1) {
                $list = M('document')->field('id,title,smallimg')->where($w)->limit(5)->order('view DESC')->select();
            } //没有轮播图情况下，按照分类下最热排序补全数据
            unset($where, $w);
        }
        $this->assign('list', $list);
        $this->display(T('Home@qbaobeimobile/Widget/pic'));
    }

    /**
     * 描述：获取首页推荐数据
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getPositionData()
    {
        $where = array();
        $where['status'] = 1;
        $where['smallimg'] = array('gt', 0);
        $where['_string'] = 'position & 2';
        $list = M('document')->where($where)->limit(5)->order('update_time DESC')->getField('id', true); //获取首页轮播图数据
        unset($where);
        $where['status'] = 1;
        $where['smallimg'] = array('gt', 0);
        $where['_string'] = 'position & 1 AND description IS NOT NULL'; //热点并且描述不为空
        if (!empty($list)) {
            $where['id'] = array('not in', $list);
        } //去除首页轮播图数据
        $rs = M('document')->field('id,title,smallimg,previewimg,description')->where($where)->limit(6)->order('update_time DESC')->select(); //获取首页热点数据
        $c = count($rs);
        unset($where);
        if ($c > 0) {
            foreach ($rs as $key => $val) {
                $list[] = $val['id'];
                $rs[$key]['images'] = 0;
                if (!empty($val['previewimg'])) {
                    $crs = explode(",", $val['previewimg']);
                    if (count($crs) > 2) {
                        $rs[$key]['images'] = 1;
                        $rs[$key]['smallimg1'] = $crs[0];
                        $rs[$key]['smallimg2'] = $crs[1];
                        $rs[$key]['smallimg3'] = $crs[2];
                    }
                }
            }
        }
        //数据补全规则
        if ($c < 6) {
            $limit = 6 - $c;
            $where['status'] = 1;
            $where['smallimg'] = array('gt', 0);
            $where['_string'] = 'position & 1 AND description IS NOT NULL'; //热点并且描述不为空
            if (!empty($list)) {
                $where['id'] = array('not in', $list);
            } //去除首页轮播图数据
            $other_rs = M('document')->field('id,title,smallimg,previewimg,description')->where($where)->limit($limit)->order('view DESC')->select(); //获取首页热点数据(数据补全)
            unset($where);
            foreach ($other_rs as $key => $val) {
                $other_rs[$key]['images'] = 0;
                if (!empty($val['previewimg'])) {
                    $ors = explode(",", $val['previewimg']);
                    if (count($ors) > 2) {
                        $other_rs[$key]['images'] = 1;
                        $other_rs[$key]['smallimg1'] = $ors[0];
                        $other_rs[$key]['smallimg2'] = $ors[1];
                        $other_rs[$key]['smallimg3'] = $ors[2];
                    }
                }
            }

        }
        if (!empty($rs) && !empty($other_rs)) {
            $lists = array_merge($rs, $other_rs);
        } else if (empty($rs)) {
            $lists = $other_rs;
        } else {
            $lists = $rs;
        }
        $this->assign('lists', $lists);
        $this->display(T('Home@qbaobeimobile/Widget/position_data'));
    }

    /**
     * 描述：终极标签页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagList()
    {
        $name = I('name');
        if (empty($name)) {
            return false;
        }
        $where = array();
        $where['name'] = $name;
        $where['status'] = 1;
        $rs = M('Tags')->field('id,title,meta_title,keywords,description')->where($where)->find();
        unset($where);
        $tid = $rs['id'];
        if (!is_numeric($tid)) {
            return false;
        }
        $flag = I('flag');
        if (empty($flag) || $flag > 3 || $flag < 0) {
            $flag = 'all';
        }
        $lists = $this->getTagListData($tid, $flag);
        $allcount = $this->getTagListData($tid, 'all', true);
        $doccount = $this->getTagListData($tid, '1', true);
        $videocount = $this->getTagListData($tid, '2', true);
        $foodcount = $this->getTagListData($tid, '3', true);
        $seo = array(
            'title' => $rs['meta_title'] ? $rs['meta_title'] : $rs['title'],
            'keywords' => $rs['keywords'],
            'description' => $rs['description']
        );
        $isshow = 1;
        if ($doccount > 0 && empty($videocount) && empty($foodcount)) {
            $isshow = 0;
        } else if (empty($doccount) && $videocount > 0 && empty($foodcount)) {
            $isshow = 0;
        } else if (empty($doccount) && empty($videocount) && $foodcount > 0) {
            $isshow = 0;
        } else if ($allcount == 0) {
            $isshow = 0;
        }
        $this->assign('lists', $lists);
        $this->assign('tid', $tid);
        $this->assign('count', count($lists));
        $this->assign('allcount', $allcount);
        $this->assign('doccount', $doccount);
        $this->assign('videocount', $videocount);
        $this->assign('foodcount', $foodcount);
        $this->assign('isshow', $isshow);
        $this->assign('title', $rs['title']);
        $this->assign('name', $name);
        $this->assign('flag', $flag);
        $this->assign("SEO", $seo);
        $this->display(T('Home@qbaobeimobile/Widget/tag2'));
    }

    /**
     * 描述：获取标签数据
     *
     * @param      $tid
     * @param      $flag
     * @param bool $bool
     * @return bool|int
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getTagListData($tid, $flag, $bool = false)
    {
        if (!is_numeric($tid)) {
            return false;
        }
        $where['b.tid'] = $tid;
        $where['b.type'] = 'document';
        $where['a.status'] = 1;
        //用join的效率会比in和not in 更高  以后有时间可以修改。
        if ($flag == 1) {
            $video_cate = D('Category')->getAllChildrenId('1307'); //所有视频分类
            $food_cate1 = D('Category')->getAllChildrenId('690'); //食谱分类下的妈妈食谱
            $food_cate2 = D('Category')->getAllChildrenId('691'); //食谱分类下的宝宝食谱
            $video_cate = empty($video_cate) ? '0' : $video_cate;
            $food_cate1 = empty($food_cate1) ? '0' : $food_cate1;
            $food_cate2 = empty($food_cate2) ? '0' : $food_cate2;
            $cate = $video_cate . ',' . $food_cate1 . ',' . $food_cate2;
            if (!empty($cate)) {
                $where['a.category_id'] = array('not in', $cate);
            }
        } else if ($flag == 2) {
            $cate = D('Category')->getAllChildrenId('1307'); //所有视频分类
            if (!empty($cate)) {
                $where['a.category_id'] = array('in', $cate);
            }
        } else if ($flag == 3) {
            $food_cate1 = D('Category')->getAllChildrenId('690'); //食谱分类下的妈妈食谱
            $food_cate2 = D('Category')->getAllChildrenId('691'); //食谱分类下的宝宝食谱
            $food_cate1 = empty($food_cate1) ? '0' : $food_cate1;
            $food_cate2 = empty($food_cate2) ? '0' : $food_cate2;
            $cate = $food_cate1 . ',' . $food_cate2;
            if (!empty($cate)) {
                $where['a.category_id'] = array('in', $cate);
            }
        }
        $rs = M('document')->alias('a')->join('__TAGS_MAP__ b ON b.did= a.id')->field('a.id as id,a.title as title,a.smallimg as smallimg ,a.previewimg as previewimg,a.description as description')->where($where)->limit(6)->order('a.update_time DESC')->select(); //获取首页热点数据
        $c = count($rs);
        if ($bool) {
            return $c;
        }
        unset($where);
        if ($c > 0) {
            foreach ($rs as $key => $val) {
                $rs[$key]['images'] = 0;
                if (!empty($val['previewimg'])) {
                    $crs = explode(",", $val['previewimg']);
                    if (count($crs) > 2) {
                        $rs[$key]['images'] = 1;
                        $rs[$key]['smallimg1'] = $crs[0];
                        $rs[$key]['smallimg2'] = $crs[1];
                        $rs[$key]['smallimg3'] = $crs[2];
                    }
                }
            }
        }

        return $rs;
    }

    /**
     * 描述：工具列表页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function toolList()
    {
        $page_id = I('page_id'); //特殊页的id
        $this->assign("SEO", WidgetSEO(array('special', null, $page_id))); //获取特殊页seo
        $this->display(T('Home@qbaobeimobile/Widget/tool_list'));
    }

    /**
     * 描述：工具详情页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tool()
    {
        $tool_id = I('tool_id'); //工具页id,范围为1-10
        $page_id = I('page_id'); //特殊页的id
        if (!is_numeric($tool_id) || $tool_id < 1 || $tool_id > 10) {
            return;
        }
        $this->assign("SEO", WidgetSEO(array('special', null, $page_id))); //获取特殊页seo
        $url = 'Home@qbaobeimobile/Widget/tool' . $tool_id;
        $this->display(T($url));
    }

    /**
     * 描述：亲宝贝怀孕日期功能页面
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function calender()
    {
        $this->display(T('Home@qbaobeimobile/Widget/calender'));
    }

    /**********************************************************亲宝贝手机版2期改版 END****************************************/

    /**
     * content字段图片和内置插入标签处理
     *
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
     * 描述：标签页面
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagsPage()
    {
        $cookies = $_COOKIE['qbaobeiCategory'];
        $cookiesList = array();
        $cookiesList = explode('|', $cookies);
        $cookiesList = array_filter($cookiesList);
        if (empty($cookiesList)) { //没有cookies
            redirect(C('MOBILE_STATIC_URL') . '/hottag/', 0, '页面跳转中...');
        }
        $lists = array();
        foreach ($cookiesList as $key => $val) {
            $tmp = explode(',', $val);
            $lists[$key]['id'] = $tmp[1];
            $lists[$key]['title'] = $tmp[0];
        }
        $SEO['title'] = "常用分类";
        $SEO['keywords'] = "常用分类";
        $SEO['description'] = "常用分类";
        $this->assign("ishot", "2");
        $this->assign("lists", $lists);
        $this->assign("SEO", $SEO);
        $this->display("Tag/index");
    }

    /**
     * 描述：标签分类页面
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagscate()
    {
        $cid = I('cid');
        if (empty($cid)) {
            return;
        }
        $tag = M('TagsCategory')->where(array("status" => "1", "id" => $cid))->find();
        $tagName = $tag['title'];
        $tagID = $tag['id'];
        $tagsCate = M('Tags')->where(array("status" => "1", "pid" => "0", "category" => $cid))->select();
        $seo['title'] = "\"" . $tagName . "\"" . "标签页 - " . C('WEB_SITE_TITLE');
        $seo['keywords'] = $tagName;
        $seo['description'] = $tagName;
        $alias = rand(1000, 100000);
        $this->assign("alias", $alias);
        $this->assign("SEO", $seo);
        $this->assign("tagName", $tagName);
        $this->assign("tagsCate", $tagsCate);
        $this->display("Tag/cate");
    }

    /**
     * 描述：标签列表页
     *
     * @param $cate
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagslist($cate)
    {
        if (empty($cate)) {
            return;
        }
        $tags = S("tagCache" . $cate);
        if (empty($tags)) {
            $tags = M('Tags')->where(array("status" => "1", "pid" => $cate))->select();
            S("tagCache" . $cate, $tags, 3600); //缓存数据
        }
        $this->assign("lists", $tags);
        $this->display("Tag/tagslist");
    }

    /**
     * 描述：视频详情页面包屑
     *
     * @param $id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function wbreadCrumb($id)
    {
        if (empty($id)) {
            return;
        }
        $siteUrl = C('MOBILE_STATIC_URL');
        $doc = M("Document")->alias("__DOCUMENT")->join("INNER JOIN __CATEGORY__ c ON __DOCUMENT.category_id = c.id")->where("__DOCUMENT.status = '1' AND __DOCUMENT.id = '$id'  ")->field("c.id,c.title,__DOCUMENT.category_rootid as rootid,__DOCUMENT.title as ttitle")->find();
        $cateUrl = staticUrlMobile("lists", $doc['id'], 'Document');
        $parentTpl = "";
        if (!empty($doc['rootid'])) {
            $ParentCate = M('Category')->where(array("id" => $doc['rootid']))->find();
            $parentTpl = " > <a href=\"" . staticUrlMobile("lists", $ParentCate['id'], 'Document') . "\">" . $ParentCate['title'] . "</a>";
        }
        $tpl = "<a href=\"" . $siteUrl . "\">首页</a>" . $parentTpl . " > <a href=\"" . $cateUrl . "\">" . $doc['title'] . "</a> > 正文";
        echo $tpl;

    }

    /**
     * 描述：标签页面
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagscates()
    {
        $cid = I('cid');
        $cookies = $_COOKIE['qbaobeiCategory'];
        $cookiesList = array();
        $cookiesList = explode('|', $cookies);
        $cookiesList = array_filter($cookiesList);
        if (empty($cookiesList)) {
            $this->assign("hasCookie", "0");
        } else {
            $this->assign("hasCookie", "1");
        }
        $tags = M('TagsCategory')->where(array("status" => "1"))->order('sort asc')->select();
        $this->assign("cid", $cid);
        $this->assign("tags", $tags);
        $this->display("Tag/tagscate");
    }

    /**
     * 描述：热门标签
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function hotTags()
    {
        $tags = M('Tags')->where(array("status" => "1", "position" => "1"))->select();
        $seo['title'] = "热门标签 -" . C('SITE_NAME');
        $seo['keywords'] = "热门标签";
        $seo['description'] = "热门标签";
        $alias = rand(1000, 100000);
        $this->assign("alias", $alias);
        $this->assign("SEO", $seo);
        $this->assign("ishot", "1");
        $this->assign("lists", $tags);
        $this->display("Tag/hotTags");
    }

    /**
     * 作者:肖书成
     * 描述:食谱的今日推荐
     * 时间:2016-1-6
     */
    public function today()
    {
        //母婴食谱下的所有分类
        $cate = M('Category')->field('id,pid,title')->where('status = 1 AND id IN(694,695,696,698,699)')->order('field(694,695,696,698,699)')->select();

        //幻灯片
        $where = ' status = 1 AND (category_id = ' . implode(' OR category_id = ', array_column($cate, 'id')) . ')';
        $slide = M('Document')->field('id,title,smallimg')->where('position & 32 AND smallimg > 0 AND ' . $where)->order('update_time DESC')->limit(6)->select();
        $count = (int)count($slide);
        if ($count < 6) {
            $where1 = '';
            if ($count > 0) {
                $where1 = ' AND id NOT IN(' . implode(',', array_column($slide, 'id')) . ')';
            }
            $rel = M('Document')->field('id,title,smallimg')->where($where . $where1)->limit(6 - $count)->order('abet DESC,view DESC')->select();
            $slide = array_filter(array_merge((array)$slide, (array)$rel));
        }


        //ID收集，避免出现重复数据
        $ids = implode(',', array_column($slide, 'id'));


        //编辑推荐
        if ($ids) {
            $where1 = ' AND id NOT IN(' . $ids . ')';
        }
        $bjtj = M('Document')->field('id,title,description,smallimg,previewimg')->where('position & 16 AND ' . $where . $where1)->order('update_time DESC')->limit(6)->select();
        $count = (int)count($bjtj);
        if ($count < 6) {
            if ($count > 0) {
                $where1 = ' AND id NOT IN(' . $ids . ',' . implode(',', array_column($bjtj, 'id')) . ')';
            }
            $rel = M('Document')->field('id,title,description,smallimg,previewimg')->where($where . $where1)->limit(6 - $count)->order('update_time DESC')->select();
            $bjtj = array_filter(array_merge((array)$bjtj, (array)$rel));
        }
        foreach ($bjtj as $k => &$v) {
            if (!empty($v['previewimg'])) {
                $v['imgs'] = explode(',', $v['previewimg']);
                $v['count_img'] = count($v['imgs']);
            } else {
                $v['count_img'] = 0;
            }
        }
        unset($v);
        //ID收集
        $ids .= ',' . implode(',', array_column($bjtj, 'id'));

        //分类列表数据
        $lists = array();
        foreach ($cate as $k => $v) {

            switch ($v['id']) {
                case '694':
                    $v['title'] = '备孕食谱';
                    break;
                case '695':
                    $v['title'] = '孕期食谱';
                    break;
                case '696':
                    $v['title'] = '产后食谱';
                    break;
                case '698':
                    $v['title'] = '4-12月';
                    break;
                case '699':
                    $v['title'] = '1-3岁';
                    break;
            }

            $v['data'] = M('Document')->field('id,title,description,smallimg,previewimg')->where('position & 4 AND status = 1 AND category_id = ' . $v['id'] . ' AND id NOT IN(' . $ids . ')')->limit(2)->order('update_time DESC')->select();

            $count = (int)count($v['data']);
            if ($count < 2) {
                $where1 = '';
                if ($count == 1) {
                    $where1 = ' AND id != ' . $v['data'][0]['id'];
                }

                $rel = M('Document')->field('id,title,description,smallimg,previewimg')->where('status = 1 AND category_id = ' . $v['id'] . ' AND id NOT IN(' . $ids . ')' . $where1)->limit(2 - $count)->order('update_time DESC')->select();
                $v['data'] = array_filter(array_merge((array)$v['data'], (array)$rel));
            }
            $lists[$k] = $v;
        }

        //SEO
        $page_id = I('page_id');
        if ($page_id) {
            $SEO = WidgetSEO(array('special', null, $page_id));
        }
        //数据赋值
        $this->assign(
            array(
                'slide' => $slide,
                'bjtj' => $bjtj,
                'lists' => $lists,
                'SEO' => $SEO
            )
        );
        $this->display('Widget/today');
    }
}
