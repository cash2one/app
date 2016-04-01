<?php
// +----------------------------------------------------------------------
// | 描述：亲！宝贝wiget文件
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-5-22 下午3:36    Version:1.0.0 
// +----------------------------------------------------------------------
namespace Document\Widget;

use Think\Controller;

class QbaobeimobileWidget extends Controller
{

    /**
     * 描述：获取文章标签
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getTags($id, $limit = "3")
    {
        $detail_id = $id;
        if (is_numeric($detail_id)) {
            $where['b.did'] = $detail_id;
            $where['b.type'] = 'document';
            $where['a.status'] = 1;
            $list = M('Tags')->alias('a')->join('__TAGS_MAP__ b ON b.tid = a.id')->field(
                'a.title as title,a.name,a.id as id'
            )->where($where)->limit($limit)->select();
            $this->assign('list', $list);
        }
        $this->display(T('Document@qbaobeimobile/Widget/tag'));
    }

    /**
     * 描述：获取分页地址
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function categoryPage($row, $count, $cate)
    {

        $count = $count ? $count : 0;
        $row = $row ? $row : 10;
        $cate = $cate ? $cate : 1;
        $path = staticUrlMobile('lists', $cate, 'Document', 2);
        $Page = new \Think\Page($count, $row, '', $cate, $path); // 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('prev', "上一页");
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', ' %UP_PAGE% %DOWN_PAGE%');
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', $show); // 赋值分页输出
        $this->display(T('Document@qbaobeimobile/Widget/catepage'));
    }

    /**
     * 描述：相关推荐和热门推荐
     *
     * @param     $category_id
     * @param int $limit
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getHot($category_id, $limit = 8)
    {
        if (is_numeric($category_id)) {
            $hot_list = M('document')->field('id,title')->where('position&1')->limit('0,' . $limit)->select();
            //  $new_list = M('document')->field('id,title')->where('category_id='.$category_id)->order('update_time desc')->limit('0,'.$limit)->select();
        }
        if (count($hot_list) > 0) {
            $hot = 1;
        }
        // if(count($new_list) > 0) $new = 1;
        $this->assign('hot', $hot);
        //  $this->assign('new',$new);
        $this->assign('hot_list', $hot_list);
        //  $this->assign('new_list',$new_list);
        $this->display(T('Document@qbaobeimobile/Widget/list'));
    }

    /**
     * 描述：获取最新
     *
     * @param     $category_id
     * @param int $limit
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function getNew($category_id, $limit = 8)
    {
        if (is_numeric($category_id)) {
            // $hot_list = M('document')->field('id,title')->where('position&1')->limit('0,'.$limit)->select();
            $new_list = M('document')->field('id,title')->where('category_id=' . $category_id)->order(
                'update_time desc'
            )->limit('0,' . $limit)->select();
        }
        //if(count($hot_list) > 0) $hot =1;
        if (count($new_list) > 0) {
            $new = 1;
        }
        //$this->assign('hot',$hot);
        $this->assign('new', $new);
        //$this->assign('hot_list',$hot_list);
        $this->assign('new_list', $new_list);
        $this->display(T('Document@qbaobeimobile/Widget/newlist'));
    }

    /**
     * 描述：获取管理文章
     *
     * @param       $id
     * @param       $cateid
     * @param array $ids
     * @param array $tags
     * @return bool
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function getRelateDoc($id, $cateid, $ids = array(), $tags = array())
    {
        if (!is_numeric($id) || !is_numeric($cateid)) {
            return false;
        }
        $tid_str = false; //设定标签字符串为false
        $ids_str = false; //去重id字符串初始化为false
        if (!empty($tags) && is_array($tags)) {
            $tid_str = implode(',', array_column($tags, 'id'));
        }
        $where = ' 1=1 ';
        if (!empty($ids) && is_array($ids)) {
            $ids_str = implode(',', $ids);
            $where = 'b.id NOT IN(' . $ids_str . ') ';
        }
        $where .= ' AND b.id != ' . $id . ' AND (b.category_id = ' . $cateid . ' ' . (!empty($tid_str) ? ' OR a.tid IN(' . $tid_str . ')' : '') . ') AND b.smallimg>0 AND b.status = 1 AND a.type = \'document\'';
        //获取相关文章结果
        $rs = M('TagsMap')
            ->alias('a')
            ->field('b.id,b.title,b.description,b.smallimg,b.previewimg')
            ->join('RIGHT JOIN __DOCUMENT__ b ON a.did = b.id')
            ->where($where)
            ->order('b.update_time desc')
            ->limit(5)
            ->group('b.id')
            ->select();
        //页面赋值
        $this->assign('relatedoclist', $rs);
        $this->assign(
            array(
                'relatedoclist' => $rs,
                'ids_str' => $ids_str,
                'tid_str' => $tid_str
            )
        );
        $this->display(T('Document@qbaobeimobile/Widget/relatedoc'));
    }

    /************肖书成区域，其他人的方法请写外面谢谢合作！***************/

    /**
     * 描述：分类备孕的文章详情页 相关文章、热门推荐
     *
     * @param $info
     */
    public function by_related2($info, $tags)
    {
        //相关文章(同分类下的五条，个人添加了随机方法)
        $where = 'id != ' . $info['id'] . ' AND status = 1 AND category_id = ' . $info['category_id'];
        $count = M('Document')->where($where)->count('id');
        $count = (int)$count;
        $ids = array();
        //热门推荐
        if ($count > 5) {
            // $ids        =   implode(',',array_column($related,'id'));
            $hot = M('Document')->field('id,title')->where('position & 16 AND ' . $where)->order(
                'update_time DESC'
            )->limit(5)->select();
            $count = (int)count($hot);
            if ($count < 5) {
                if ($count != 0) {
                    $ids = implode(array_column($hot, 'id'));
                    $where .= " AND id NOT IN($ids)";
                }
                $hot1 = M('Document')->field('id,title')->where($where)->order('view DESC')->limit(5 - $count)->select(
                );
                $hot = array_filter(array_merge((array)$hot, (array)$hot1));
            }
        }
        //为避免数据重复把所有的数据ID总结到了一起
        array_push($ids, $info['id']);

        //页面赋值
        $this->assign(
            array(
                'info' => $info,
                'hot' => $hot,
                'ids' => $ids,
                'tags' => $tags
            )
        );
        //模板调用
        $this->display('Widget/by_related2');
    }

    /**
     * 作者:肖书成
     * 描述:标签数据
     *
     * @param $tags
     * @param $ids
     */
    public function tags_data($tags, $ids)
    {
        if (empty($ids) || empty($tags)) {
            return false;
        }

        foreach ($tags as $k => &$v) {
            $v['lists_data'] = M('TagsMap')->alias('a')->field(
                'b.id,b.title,b.description,b.smallimg,b.previewimg'
            )->join('__DOCUMENT__ b ON a.did = b.id')->where(
                "a.tid = " . $v['id'] . " AND a.type = 'document' AND b.status = 1 AND b.id NOT IN(" . implode(
                    ',',
                    $ids
                ) . ")"
            )->limit(5)->select();

            //去重
            if ($v['lists_data']) {
                $id1 = array_column($v['lists_data'], 'id');
                $ids = array_merge($ids, $id1);
                $ids = array_unique($ids);
            }

        }
        unset($v);


        $this->assign('lists', $tags);
        $this->display('Widget/tags_data');

    }

    /**
     * 描述：通用分类的处理逻辑分类
     * 日期：2016-1-5
     *
     * @param $info
     * @param $SEO
     * @return mixed
     */
    public function by_cate2($info, $SEO, $lists)
    {
        //顶级分类信息
        $top_cate = $info;
        $key = 0;
//        $mbx        =   "<a href='".C('MOBILE_STATIC_URL')."'>首页 > </a>";


        while ($top_cate['pid'] !== '0') {
            $title = M('Category')->field('id,pid,title,template_lists,status')->where(
                'id = ' . $top_cate['pid']
            )->find();

            if ($title['id'] == "5") {
                $title['title'] = '早教';
            }


            if ($title['pid'] !== '0') {
                $key++;
                $top_cate = $title;
            } else {
                $top_cate['pid'] = '0';
            }
        }

        if ($top_cate['id'] == '5') {
            $top_cate['title'] = '早教';
        }

        //面包屑导航
        $mbx = " > " . $top_cate['title'];
        if (($top_cate['template_lists'] == $title['template_lists']) && ($title['status'] == 1)) {
            $mbx = " > <a href='" . staticUrlMobile(
                'lists',
                $title['id'],
                'Document'
            ) . "'>" . $title['title'] . "</a>" . $mbx;
        }
        $mbx = "<a href='" . C('MOBILE_STATIC_URL') . "'>首页</a>" . $mbx;


        //判断是否有子分类
        $isTop = ($top_cate['id'] == $info['id']);
        if ($isTop) {
            $child_num = M('Category')->where('status = 1 AND pid = ' . $info['id'])->count('id');
            if ((int)$child_num < 1) {
                $top_cate = $title;
                $key = 1;
                $isTop = false;
            }
        }


        //导航
        $where = '';
        if ($top_cate['id'] == '80') {
            $where = ' OR id = 320';
        }


        $nav = M('Category')->field('id,title')->where(
            'status = 1 AND (pid = ' . $top_cate['id'] . $where . ')'
        )->select();

        if ($nav) {
            $nav_id = implode(',', array_column($nav, 'id'));
            $nav_child_count = M()->query(
                "SELECT COUNT(pid) AS tp_count ,pid FROM `onethink_category` WHERE ( `status` = 1 AND pid IN($nav_id) ) GROUP BY pid"
            );
            $nav1 = array();
            $nav2 = array();
            $nav_child_count_id = array_column($nav_child_count, 'pid');
            foreach ($nav as $k => $v) {
                if (in_array($v['id'], $nav_child_count_id)) {
                    $nav1[] = $v;
                } else {
                    $nav2[] = $v;
                }
            }

            $nav = array_filter(array_merge((array)$nav1, (array)$nav2));
        }


        //奶粉 和早期教育 排序
        if (in_array($top_cate['id'], array('209', '5'))) {
            $soft_cate = array('209' => array(210, 218, 214, 213, 215), '5' => array(205, 741, 1144, 197));
            $nav1 = array();
            $nav2 = array();

            foreach ($nav as $k => $v) {
                if (in_array($v['id'], $soft_cate[$top_cate['id']])) {
                    $nav1[] = $v;
                } else {
                    $nav2[] = $v;
                }
            }

            $nav1 = custom_sort($soft_cate[$top_cate['id']], $nav1, 'id');
            $nav = array_filter(array_merge($nav1, $nav2));
        }


        //幻灯片
        $where = 'status = 1 ';

        if ($nav) {
            $where .= 'AND (category_id = ' . $top_cate['id'] . ' OR category_id = ' . implode(
                ' OR category_id = ',
                array_column($nav, 'id')
            ) . ')';
        } else {
            $where .= 'AND category_id = ' . $top_cate['id'];
        }

        $slide = M('Document')->field('id,title,smallimg')->where(
            'position & 32 AND smallimg > 0 AND ' . $where
        )->limit(5)->order('update_time DESC')->select();

        $count = (int)count($slide);
        if ($count < 5) {
            $where1 = ' smallimg > 0 AND ' . $where;

            if ($count != 0) {
                $where1 = $where1 . ' AND id NOT IN(' . implode(',', array_column($slide, 'id')) . ')';
            }

            $slide1 = M('Document')->field('id,title,smallimg ')->where($where1)->limit(5 - $count)->order(
                'abet DESC'
            )->select();
            $slide = array_filter(array_merge((array)$slide, (array)$slide1));
        }

        //列表数据
        if ($key == 0) {
            $method = 'cate_position';
            if ($isTop && $slide) {
                $where1 = 'position & 8 AND ' . $where . ' AND id NOT IN(' . implode(
                    ',',
                    array_column($slide, 'id')
                ) . ')';
            } else {
                $where1 = 'status = 1 AND category_id = ' . $info['id'];
            }

            $lists = M('Document')->field('id,title,description,smallimg,previewimg')->where($where1)->limit(6)->order(
                'update_time DESC'
            )->select();

            $count = (int)count($lists);
            if ($count < 6 && $isTop) {
                $where1 = $where;
                if ($slide) {
                    $where1 = $where . ' AND id NOT IN(' . implode(',', array_column($slide, 'id')) . ')';
                }
                $lists1 = M('Document')->field('id,title,description,smallimg,previewimg')->where($where1)->limit(
                    6 - $count
                )->order('abet DESC')->select();
                $lists = array_filter(array_merge((array)$lists, (array)$lists1));
            }
        } else {
            $method = 'cate_list';
        }

        foreach ($lists as $k => &$v) {
            if (!empty($v['previewimg'])) {
                $v['imgs'] = explode(',', $v['previewimg']);
                $v['count_img'] = count($v['imgs']);
            } else {
                $v['count_img'] = 0;
            }

        }
        unset($v);

        //去重
        $slide_ids = array_column($slide, 'id');
        foreach ($lists as $k => $v) {
            if (in_array($v['id'], $slide_ids)) {
                unset($lists[$k]);
            }
        }

        //热门标签
        if ($isTop && $nav) {
            $where2 = ' AND (b.category_id = ' . $top_cate['id'] . ' OR b.category_id = ' . implode(
                ' OR b.category_id = ',
                array_column($nav, 'id')
            ) . ')';
        } else {
            $where2 = ' AND b.category_id = ' . $info['id'];
        }

        $tags = M('TagsMap')->alias('a')->field('c.id,c.name,c.title,b.abet,b.view')->join(
            '__DOCUMENT__ b ON a.did = b.id'
        )->join('__TAGS__ c ON a.tid = c.id')
            ->where('a.type = "document" AND b.status = 1 AND c.status = 1' . $where2)->order(
                'b.abet DESC,b.view DESC'
            )->group('a.tid')->limit(9)->select();

        //数据赋值
        $this->assign(
            array(
                'SEO' => $SEO,
                'info' => $info,
                'isTop' => $isTop,
                'slide' => $slide,
                'nav' => $nav,
                'top_cate' => $top_cate,
                'lists' => $lists,
                'tags' => $tags,
                'method' => $method,
                'key' => $key,
                'mbx' => $mbx
            )
        );


        //模板调用
        $this->display('Category/by_cate2');
    }

    /**
     * 作者:肖书成
     * 描述:通用详情页面包屑
     * 时间:2016-1-23
     *
     * @param $id
     */
    public function ty_xq_mbx($id)
    {
        $info = M('Category')->field('id,title,pid,status,template_lists')->where('id = ' . $id)->find();
        if (empty($info)) {
            echo "<a href='" . C('MOBILE_STATIC_URL') . "'>首页</a> > 正文";
            exit;
        }

        while ($info['status'] !== '1') {
            $info = M('Category')->field('id,title,pid,status,template_lists')->where('id = ' . $info['pid'])->find();
            if ($info['pid'] == '0') {
                break;
            }
        }

        if ($info['id'] == '5') {
            $info['title'] = '早教';
        }

        if ($info['id'] == '699') {
            $info['title'] = '1-3岁';
        }

        $key = 0;
        $top_cate = $info;


        $mbx = "<a href='" . staticUrlMobile('lists', $info['id'], 'Document') . "'>" . $info['title'] . "</a>";
        while ($top_cate['pid'] !== '0') {
            $title = M('Category')->field('id,pid,title,template_lists,status')->where(
                'id = ' . $top_cate['pid']
            )->find();

            if ($title['id'] == '5') {
                $title['title'] = '早教';
            }

            if ($title['pid'] !== '0') {
                $key++;
                $top_cate = $title;
            } else {
                $top_cate['pid'] = '0';
            }
        }

        //面包屑导航
        if (($info['id'] !== $top_cate['id']) && ($title['status'] == 1)) {
            $mbx = "<a href='" . staticUrlMobile(
                'lists',
                $title['id'],
                'Document'
            ) . "'>" . $title['title'] . " > </a>" . $mbx;
        }

        $mbx = "<a href='" . C('MOBILE_STATIC_URL') . "'>首页 > </a>" . $mbx;
        echo $mbx;
    }


    /**
     * 作者:肖书成
     * 描述:食谱分类
     * 时间:2016-1-10
     *
     * @param $info
     * @param $SEO
     * @param $lists
     * @param $page
     */
    public function food_cate2($info, $SEO, $lists, $page)
    {
        $cate = array(
            array('id' => 694, 'title' => '备孕食谱'),
            array('id' => 695, 'title' => '孕期食谱'),
            array('id' => 696, 'title' => '产后食谱'),
            array('id' => 698, 'title' => '4-12月'),
            array('id' => 699, 'title' => '1-3岁'),
        );


        if (in_array($info['id'], array('694', '695', '696', '698', '699'))) {
            $top_cate['title'] = '食谱';

        } elseif ($info['pid'] != '0') {
            $top_cate['title'] = M('Category')->where('status = 1 AND id = ' . $info['pid'])->getField('title');
//            $cate   =   M('Category')->field('id,title')->where('status = 1 AND pid = '.$info['pid'])->limit(5)->select();
        } else {
            $top_cate['title'] = $info['title'];
//            $cate   =   M('Category')->field('id,title')->where('status = 1 AND pid = '.$info['id'])->limit(5)->select();
        }

        $tags = M('TagsMap')->alias('a')->field('b.id,b.title,b.name')->join('__TAGS__ b ON a.tid = b.id')->join(
            '__DOCUMENT__ c ON a.did = c.id'
        )
            ->where(
                'a.type = "document" AND b.status = 1 AND c.category_id = ' . $info['id'] . ' AND c.status = 1'
            )->order('c.abet DESC,c.view DESC')->group('a.tid')->limit(8)->select();

        $this->assign(
            array(
                'info' => $info,
                'cate' => $cate,
                'SEO' => $SEO,
                'lists' => $lists,
                'page' => $page,
                'tags' => $tags,
                'top_cate' => $top_cate,
            )
        );

        $this->display('Category/food_cate2');
    }

    /**
     * 作者:肖书成
     * 描述:食谱详情页的描述
     * 时间:2016-1-10
     *
     * @param $info
     * @param $tags
     */
    public function d_food2($info, $tags)
    {
        $time_key = false;
        if ($info['step_read'] == '1' && (int)$info['create_time'] > 1452787200) {
            $time_key = true;
            preg_match_all("/<h2>([\s\S]*?)<\/h2>[\s\S]*?<img.*?src=(?<t>['\"])(.*?)\g{t}/i", $info['content'], $array);
            $info['stepList'] = array();
            foreach ($array[1] as $k => $v) {
                $rel['number'] = $k + 1;
                $rel['title'] = $v;
                $rel['img'] = $array[3][$k];

                $info['stepList'][] = $rel;
            }
        }

        $ids = '';

        //标签数据
        if (!empty($tags)) {
            $t_ids = implode(',', array_column($tags, 'id'));

            //相关食谱
            $count = M('TagsMap')->alias('a')->field('b.id')->join('__DOCUMENT__ b ON a.did = b.id')->where(
                'a.type = "document" AND b.status = 1 AND b.category_id IN(694,695,696,698,699) AND b.id != ' . $info['id'] . ' AND a.tid IN(' . $t_ids . ')'
            )
                ->group('b.id')->select();
            $count = count($count);

            if ($count > 0) {
                if ($count < 4) {
                    $limt = 0;
                } else {
                    $limt = rand(0, $count - 4) . ',4';
                }

                $tag_list = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join(
                    '__DOCUMENT__ b ON a.did = b.id'
                )->where(
                    'a.type = "document" AND b.status = 1 AND b.category_id IN(694,695,696,698,699) AND b.id != ' . $info['id'] . ' AND a.tid IN(' . $t_ids . ')'
                )
                    ->order('b.view DESC')->group('b.id')->limit($limt)->select();
            }

            //相关知识
            $count = M('TagsMap')->alias('a')->field('b.id')->join('__DOCUMENT__ b ON a.did = b.id')->join(
                '__CATEGORY__ c ON b.category_id = c.id'
            )
                ->where(
                    'a.type = "document" AND b.status = 1 AND b.category_id NOT IN(694,695,696,698,699) AND a.tid IN(' . $t_ids . ') AND c.status = 1'
                )->group('b.id')->select();

            $count = count($count);

            if ($count > 0) {
                if ($count < 5) {
                    $limt = 0;
                } else {
                    $limt = rand(0, $count - 5) . ',5';
                }

                $tag_zs = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join(
                    '__DOCUMENT__ b ON a.did = b.id'
                )->join('__CATEGORY__ c ON b.category_id = c.id')
                    ->where(
                        'a.type = "document" AND b.status = 1 AND b.category_id NOT IN(694,695,696,698,699) AND a.tid IN(' . $t_ids . ') AND c.status = 1'
                    )
                    ->order('b.view DESC')->group('b.id')->limit($limt)->select();
            }


            $this->assign(
                array(
                    'tag_list' => $tag_list,
                    'tag_zs' => $tag_zs
                )
            );
            if ($tag_list) {
                $ids = implode(',', array_column($tag_list, 'id'));
            }
        }

        //功效
        if ($info['efficacy']) {
            $info['efficacy'] = explode('|', $info['efficacy']);
        }

        //主料
        if ($info['foodstuff']) {
            $info['foodstuff'] = str_replace('，', ',', $info['foodstuff']);
            $foodstuff = explode(',', $info['foodstuff']);
            foreach ($foodstuff as $k => &$v) {
                $v = explode('|', $v);
            }
            unset($v);

            $info['foodstuff'] = $foodstuff;
        }

        //辅料
        if ($info['accessories']) {
            $info['accessories'] = str_replace('，', ',', $info['accessories']);
            $foodstuff = explode(',', $info['accessories']);
            foreach ($foodstuff as $k => &$v) {
                $v = explode('|', $v);
            }
            unset($v);

            $info['accessories'] = $foodstuff;
        }

        //小贴士
        if ($info['tips']) {
            $info['tips'] = explode("\n", $info['tips']);
        }


        //特别推荐
        $where = '';
        if ($ids) {
            $where = ' AND id NOT IN(' . $ids . ')';
        }

        $tj = M('Document')->field('id,title,smallimg')->where(
            'status = 1 AND position & 8 AND category_id = ' . $info['category_id'] . ' AND id != ' . $info['id'] . $where
        )->order('update_time DESC')->limit(4)->select();
        $count = (int)count($tj);
        if ($count < 4) {
            if ($count > 0) {
                if ($ids) {
                    $where = ' AND id NOT IN(' . $ids . ',' . implode(',', array_column($tj, 'id')) . ')';
                } else {
                    $where = ' AND id NOT IN(' . implode(',', array_column($tj, 'id')) . ')';
                }
            }
            $tj1 = M('Document')->field('id,title,smallimg')->where(
                'status = 1 AND category_id = ' . $info['category_id'] . ' AND id != ' . $info['id'] . $where
            )->order('update_time DESC')->limit(4 - $count)->select();
            $tj = array_filter(array_merge((array)$tj, (array)$tj1));
        }
        //为避免数据重复把所有的数据ID总结到了一起
        $ids = array_unique(
            array_filter(
                array_merge(
                    (array)array_column($tag_list, 'id'),
                    (array)array_column($tag_zs, 'id'),
                    (array)array_column($tj, 'id')
                )
            )
        );
        array_push($ids, $info['id']);
        $this->assign(
            array(
                'info' => $info,
                'time_key' => $time_key,
                'tj' => $tj,
                'ids' => $ids,
                'tags' => $tags
            )
        );


        $this->display('Widget/d_food2');
    }


    /**************************************************END***************************************************/


    /**
     * Description：    百科首页
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */
    public function baike()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '71')));
        $this->display('Widget/baike');
    }

    /**
     * Description：    百科首页父分类
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */
    public function baikePCate($cate)
    {
        if (empty($cate)) {
            return;
        }
        $ids = D('Category')->getChildrenId($cate);
        if (empty($ids)) {
            return;
        }
        $cids = explode(",", $ids);
        if (is_array($cids)) {
            array_shift($cids);
        }
        $pcids = implode(",", $cids);
        $pcate = M('Category')->where("id IN(" . $pcids . ")")->select();
        $this->assign('pcate', $pcate);
        $this->display('Widget/baikePcate');
    }

    /**
     * Description：    百科首页
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */
    public function baikeCateBlock($cate)
    {
        if (empty($cate)) {
            return;
        }
        $rs = D('Category')->getAllChildrenId($cate);
        if (empty($rs)) {
            return;
        }
        $cids = explode(",", $rs);
        if (is_array($cids)) {
            array_shift($cids);
        }
        $pcids = implode(",", $cids);
        if (empty($pcids)) {
            return;
        }
        $lists = M('Category')->where("id IN(" . $pcids . ")")->select();
        $this->assign('lists', $lists);
        $this->display('Widget/baikeCateBlock');
    }

    //百科内容处理
    public function baikeContent($content)
    {
        $i = 0;
        $pattern = "/<a name=\"p(\d+?)\">/";
        $m = preg_split($pattern, $content);
        foreach ($m as $val) {
            $i = $i + 1;
            preg_match("/<strong>([^\/]+?)<\/strong>/", $val, $m);
            $lists[$i]['title'] = $m[1];
            $a_content = preg_replace("/<span style=\"([^\/]+?)\"><strong>([^\/]+?)<\/strong><\/span><\/a>/", "", $val);
            $lists[$i]['content'] = $a_content;
        }
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobeimobile/Widget/baikeContent'));

    }

    /**
     * Description：    相关百科
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function relateBaike($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $p = get_base_by_tag($id, 'Document', 'Document', '', false);

        foreach ($p as $key => $val) {
            if ($val['id'] == $id) {
                unset($val);
            }
            $lists[] = $val;
        }
        $lists = array_filter($lists);
        $this->assign("lists", $lists);
        $this->display(T('Document@qbaobei/Widget/relateBaike'));

    }

    /**
     * Description：    视频库
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function videoKu()
    {
        $cate = I('get.cate');
        $order = I('get.order');
        $cateCache = S('tagcate');
        if (empty($cateCache)) { //缓存分类
            $pcate = M("Category")->where(array("pid" => "1307", "status" => "1"))->select();
            S('tagcate', $pcate, 86400);
        } else {
            $pcate = $cateCache;
        }

        $category = M("Category")->where(array("id" => $cate, "status" => "1"))->find();
        $pid = $category['pid'];
        $cateName = $category['title'];
        $orderCondition = $order == "0" ? "view desc" : "create_time desc";
        if ($pid == "1307" && $pid != "0") { //二级分类
            $childcate = M("Category")->where(array("pid" => $cate, "status" => "1"))->select();
            $ids = D('Category')->getAllChildrenId($cate);
            $lists = M('Document')->where("category_id IN(" . $ids . ") AND status=1")->order($orderCondition)->limit(
                '8'
            )->select();

            $isSecond = "1";
            $pid = $cate;
        } else {
            if ($cate == "1307" && $pid == "0") { //一级分类，也就是全部都选择
                $childcate = "";
                $lists = M('Document')->where("category_rootid = '$cate' AND status=1")->order($orderCondition)->limit(
                    '8'
                )->select();
                $pid = "1307";
            } else {
                $childcate = M("Category")->where(array("pid" => $pid, "status" => "1"))->select();
                $lists = M('Document')->where("category_id = '$cate' AND status=1")->order($orderCondition)->limit(
                    '8'
                )->select();
            }
        }
        $seo['title'] = $category['meta_title'];
        $seo['keywords'] = $category['keywords'];
        $seo['description'] = $category['description'];
        $this->assign("pid", $pid);
        $this->assign("cate", $cate);
        $this->assign("order", $order);
        $this->assign("pcate", $pcate);
        $this->assign("childcate", $childcate);
        $this->assign("isSecond", $isSecond);
        $this->assign("cateName", $cateName);
        $this->assign('SEO', $seo);
        $this->assign("lists", $lists);
        $this->display('Widget/videoKu');
    }

    public function orderUrl()
    {
        $order = I('get.order');
        $url = $order == "0" ? '0/' : '';
        echo $url;
    }

    public function orderUrlDir()
    {
        $order = I('get.order');
        $url = $order == "0" ? 'video/' : '';
        echo $url;
    }

    public function videoListNew($cate, $seo)
    {
        $order = I('get.order');
        $cateCache = S('tagcate');
        if (empty($cateCache)) { //缓存分类
            $pcate = M("Category")->where(array("pid" => "1307", "status" => "1"))->select();
            S('tagcate', $pcate, 86400);
        } else {
            $pcate = $cateCache;
        }
        $category = M("Category")->where(array("id" => $cate, "status" => "1"))->find();
        $pid = $category['pid'];
        $cateName = $category['title'];
        $orderCondition = $order == "0" ? "view desc" : "create_time desc";
        if ($pid == "1307" && $pid != "0") { //二级分类
            $childcate = M("Category")->where(array("pid" => $cate, "status" => "1"))->select();
            $ids = D('Category')->getAllChildrenId($cate);
            $lists = M('Document')->where("category_id IN(" . $ids . ") AND status=1")->order($orderCondition)->limit(
                '8'
            )->select();

            $isSecond = "1";
            $pid = $cate;
        } else {
            if ($cate == "1307" && $pid == "0") { //一级分类，也就是全部都选择
                $childcate = "";
                $lists = M('Document')->where("category_rootid = '$cate' AND status=1")->order($orderCondition)->limit(
                    '8'
                )->select();
                $pid = "1307";
            } else {
                $childcate = M("Category")->where(array("pid" => $pid, "status" => "1"))->select();
                $lists = M('Document')->where("category_id = '$cate' AND status=1")->order($orderCondition)->limit(
                    '8'
                )->select();
            }
        }
        $this->assign("pid", $pid);
        $this->assign("cate", $cate);
        $this->assign("order", $order);
        $this->assign("pcate", $pcate);
        $this->assign("childcate", $childcate);
        $this->assign("isSecond", $isSecond);
        $this->assign("cateName", $cateName);
        $this->assign('SEO', $seo);
        $this->assign("lists", $lists);
        $this->display('Widget/videoKu');

    }

    /**
     * Description：    视频详情同类
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-7 15:38:35
     */

    public function videoDetailSame($cate, $id)
    {
        if (empty($cate)) {
            return;
        }
        if (empty($id)) {
            return;
        }
        $video = M('Document')->where("category_id = '$cate' AND status=1 AND id!='" . $id . "'")->order(
            "create_time desc"
        )->limit('8')->select();
        $lists = array_filter($video);
        $this->assign("lists", $lists);
        $this->display('Widget/videoDetailSame');
    }

    /**
     * Description：    视频详情推荐
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-7 15:38:35
     */

    public function videoDetailCommended($cate, $id)
    {
        if (empty($cate)) {
            return;
        }
        if (empty($id)) {
            return;
        }
        $video = M('Document')->where("category_id = '$cate' AND id!='$id' AND position & 8 AND status=1")->order(
            "update_time desc"
        )->limit('8')->select();
        $neednum = 8 - count($video);
        if ($neednum != 0) {
            $where = array();
            if (!empty($video)) {
                $video_ids = array();
                foreach ($video as $val) {
                    $video_ids[] = $val['id'];
                }
                $video_ids[] = $id;
                if (!empty($video_ids)) {
                    $where['id'] = array('not in', $video_ids);
                }
            }
            $where['category_id'] = $cate;
            $where['status'] = 1;
            $news = M('Document')->where($where)->order("create_time desc")->limit($neednum)->select();
            unset($where, $video_ids);
        }
        $lists = array_merge((array)$video, (array)$news);
        $this->assign("lists", $lists);
        $this->display('Widget/videoDetailSame');
    }

    /**
     * Description：    视频详情热播
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-7 15:38:35
     */

    public function videoDetailHot()
    {
        $ids = D('Category')->getAllChildrenId("1307");
        $video = M('Document')->where("category_id IN(" . $ids . ") AND status=1")->order("view desc")->limit(
            '6'
        )->select();
        $this->assign("lists", $video);
        $this->display('Widget/videoDetailSame');
    }


    /**
     * Description：   百科详情相关
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function baikeDetailRelate($id, $cate)
    {
        if (empty($id)) {
            return;
        }
        if (empty($cate)) {
            return;
        }
        $baike = get_base_by_tag($id, 'Document', 'Document', 'product', false);
        foreach ($baike as $key => $val) {
            $ids .= $val['id'];
        }
        $ids = rtrim($ids, ",");
        $neednum = 15 - count($baike);
        if ($neednum != 0) {
            if ($ids != "" || $ids != ",") {
                $where['id'] = array('not in', array($ids));
                $where['category_id'] = $cate;
                $where['status'] = "1";
                $news = M('Document')->where($where)->order("view desc")->limit($neednum)->select();
            } else {
                $news = M('Document')->where("category_id = '$cate' AND status=1")->order("view desc")->limit(
                    $neednum
                )->select();
            }

        }
        $lists = array_merge((array)$baike, (array)$news);
        $lists = array_filter($lists);
        $this->assign("lists", $lists);
        $this->display('Widget/baikeDetailCommended');
    }

    /**
     * Description：   百科详情热门推荐页面
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function baikeDetailCommended($cate, $id)
    {
        if (empty($id)) {
            return;
        }
        if (empty($cate)) {
            return;
        }
        $baike = M('Document')->where("category_id = '$cate' AND position & 16 AND id!='$id' AND status=1")->order(
            "update_time desc"
        )->limit('15')->select();
        $neednum = 15 - count($baike);
        if ($neednum != 0) {
            $news = M('Document')->where("category_id = '$cate' AND id!='$id' AND status=1")->order("view desc")->limit(
                $neednum
            )->select();
        }
        $lists = array_merge((array)$baike, (array)$news);
        $lists = array_filter($lists);
        $lists = array_unique_fb($lists);
        $this->assign("lists", $lists);
        $this->display('Widget/baikeDetailCommended');
    }


    /**
     * Description：    视频首页
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function video()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '70')));
        $this->display('Widget/video');
    }

    /**
     * Description：    视频首页幻灯
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function shipinSlider()
    {
        $childIds = $this->getChildIds('1307');
        if (empty($childIds)) {
            return;
        }
        foreach ($childIds as $key => $val) {
            $ids .= $val . ",";
        }
        $ids = rtrim($ids, ",");
        $video = M('Document')->where("category_id IN(" . $ids . ") AND position & 32 AND status=1")->order(
            "update_time desc"
        )->limit('5')->select();
        $this->assign("lists", $video);
        $this->display('Widget/shipinSlider');
    }

    /**
     * Description：    视频首页推荐
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function shipinCommended()
    {
        $childIds = $this->getChildIds('1307');
        if (empty($childIds)) {
            return;
        }
        foreach ($childIds as $key => $val) {
            $ids .= $val . ",";
        }
        $ids = rtrim($ids, ",");
        if (!empty($ids)) {
            $video = M('Document')->where("category_id IN(" . $ids . ") AND position & 16 AND status=1")->order(
                "update_time desc"
            )->limit('4')->select();
        }

        $this->assign("lists", $video);
        $this->display('Widget/shipinCommended');
    }

    /**
     * Description：    视频首页内容块
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function videoBlock($rootid, $limit, $t = '')
    {
        if (empty($rootid)) {
            return;
        }
        $limit = empty($limit) ? '2' : $limit;
        $t = empty($t) ? '' : '1';
        $childIds = $this->getChildIds($rootid);
        $childIds = array_merge(array($cateId), $childIds);
        $where = array('status' => 1, 'category_id' => array('in', $childIds));
        $video = M('Document')->where($where)->limit($limit)->select();
        $this->assign("lists", $video);
        $this->display('Widget/videoBlock' . $t);
    }

    private function getChildIds($pid = 0, &$lists = array())
    {
        $field = array('id,title,pid');
        $cateList = M('Category')->field($field)->where(array('status' => 1, 'pid' => $pid))->select();
        foreach ($cateList as $key => $value) {
            $lists[] = $value;
            $this->getChildIds($value['id'], $lists);
        }
        $childIds = array_column($lists, 'id');
        return $childIds;
    }

    /**
     * 描述：qbaobei手机版404
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function m404()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '86')));
        $this->display('Widget/404');
    }

    /**
     * 描述：获取mp3块
     *
     * @param int $file_id
     * @return bool
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function mp3($file_id = 0)
    {
        if (!is_numeric($file_id) || empty($file_id)) {
            return false;
        }
        $where = array();
        $where['id'] = $file_id;
        $where['ext'] = 'mp3';
        $infos = M('file')->field('name,savepath,url')->where($where)->find();
        if (!empty($infos['url'])) {
            $url = $infos['url'];
        } else if (!empty($infos['name']) && !empty($infos['savepath'])) {
            $url = C('MOBILE_STATIC_URL').$infos['savepath'].$infos['name'];
        }
        $this->assign('url', $url);
        $this->display('Widget/mp3');
    }
}
