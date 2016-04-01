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
 * Qbaobeiwidget
 */
class QbaobeiWidget extends Controller
{

    /**
     * 健康美食：首页
     * @date: 2015-6-9
     * @author: liujun
     */
    public function jkms()
    {
        $seo = I('seo');
        $this->assign('SEO', WidgetSEO(array(
            'special',
            null,
            $seo
        )));
        $this->display(T('Document@qbaobei/Widget/jkms/index'));
    }

    /**
     * 健康美食：导航栏
     * @date: 2015-6-9
     * @author: liujun
     */
    public function jkmsNav()
    {
        //获取指定导航栏id
        $cateIds = $this->getJkmsIds('navCateId');
        $ids     = join(',', $cateIds);

        $filed = array( 'id,name,title,pid' );
        $order = "FIELD(id," . $ids . ")";

        $where            = array( 'status' => 1 );
        $where['_string'] = "id in (" . $ids . ")";

        $cateList = M('Category')->field($filed)->where($where)->order($order)->select();
        $cateTree = list_to_tree($cateList);//分类树结构

        $this->assign('cateTree', $cateTree);
        $this->display(T('Document@qbaobei/Widget/jkms/nav'));
    }

    /**
     * 健康美食：文章推荐
     * @date: 2015-6-10
     * @author: liujun
     */
    public function jkmsPosition($pc_position = 0, $length = 1, $offset = 0, $type = 1)
    {
        $order = 'update_time DESC';
        $field = 'd.id,d.title,d.category_id,d.smallimg,d.pc_position,a.content';

        $pids     = $this->getJkmsIds('pid');
        $childIds = $this->getJkmsIds('childIds');
        $cateIds  = array_merge($pids, $childIds);//获取所有子分类

        //查询条件
        $where            = array( 'status' => 1 );
        $where['_string'] = "pc_position & " . $pc_position;
        if ($cateIds) {
            $where['category_id'] = array(
                'in',
                $cateIds
            );
        }

        //获取结果集
        $join   = 'left join ' . C(DB_PREFIX) . 'document_article as a ON d.id = a.id';
        $list   = M('Document')->alias('d')->join($join)->field($field)->where($where)->order($order)->limit($offset, $length)->select();
        $result = array(
            'type' => $type,
            'list' => $list,
        );

        $this->assign($result);
        $this->display(T('Document@qbaobei/Widget/jkms/position'));
    }

    /**
     * 健康美食：分类推荐
     * @date: 2015-6-10
     * @author: liujun
     */
    public function jkmsCatePosition($cateId = 0, $type = 2)
    {
        $order       = 'sort desc';
        $field       = 'id,name,title,vertical_pic';
        $where       = array( 'status' => 1 );
        $where['id'] = $cateId;

        $cateInfo = M('Category')->field($field)->where($where)->find();
        $result   = array(
            'type'     => $type,
            'cateInfo' => $cateInfo,
        );

        $this->assign($result);
        $this->display(T('Document@qbaobei/Widget/jkms/position'));
    }

    /**
     * 健康美食：分类下的文章
     * @date: 2015-6-10
     * @author: liujun
     */
    public function jkmsCategoryList()
    {
        $pids    = $this->getJkmsIds('pid');
        $join_c  = 'left join ' . C(DB_PREFIX) . 'document_article as a ON d.id = a.id';
        $field_c = 'd.id,d.title,d.category_id,d.smallimg,d.pc_position,d.description,a.content';

        $filed       = array( 'id,name,title,pid,position,icon' );
        $order       = "FIELD(id," . join(',', $pids) . ")";
        $where       = array( 'status' => 1 );
        $where['id'] = array(
            'in',
            $pids
        );

        $cateRs = M('Category')->field($filed)->where($where)->order($order)->select();
        unset($where);
        foreach ($cateRs as $key => $value) {
            //获取热门推荐子类
            $childIds  = $this->getChildIds($value['id']);
            $where     = array(
                'status'  => 1,
                'id'      => array(
                    'in',
                    $childIds
                ),
                '_string' => 'position & 1'
            );
            $childList = M('Category')->field($filed)->where($where)->order('sort asc')->limit(4)->select();
            //获取文章
            $documentIds = array();
            foreach ($childList as $ckey => $cvalue) {
                unset($where);
                $childIds2 = $this->getChildIds($cvalue['id']);
                $childIds2 = array_merge(array( $cvalue['id'] ), $childIds2);

                $list                      = M('Document')->alias('d')->join($join_c)->field($field_c)->where(array(
                    'status'      => 1,
                    'category_id' => array(
                        'in',
                        $childIds2
                    )
                ))->order('update_time DESC')->limit(5)->select();
                $childList[$ckey]['_list'] = $list;
                if ($list) {
                    $documentIds = array_merge($documentIds, array_column($list, 'id'));
                }
            }
            //获取食谱热点排行:按点击量排序
            unset($where);
            $where = array( 'status' => 1 );
            //$where['_string'] = 'pc_position & 2';
            $where['category_id'] = array(
                'in',
                array_merge(array( $value['id'] ), $childIds)
            );
            if ($documentIds) {
                $where['d.id'] = array(
                    'not in',
                    $documentIds
                );
            }
            $hotList                  = M('Document')->alias('d')->join($join_c)->field($field_c)->where($where)->order('view DESC')->limit(7)->select();
            $cateRs[$key]['_child']   = $childList;
            $cateRs[$key]['_hotList'] = $hotList;
        }

        $result = array( 'cateRs' => $cateRs, );
        $this->assign($result);
        $this->display(T('Document@qbaobei/Widget/jkms/category_list'));
    }

    /**
     * 健康美食：banner
     * @date: 2015-6-10
     * @author: liujun
     */
    public function jkmsBanner($type = 1)
    {
        $this->assign('type', $type);//显示位置
        $this->display(T('Document@qbaobei/Widget/jkms/banner'));
    }

    /**
     * 健康美食：热门推荐
     * @date: 2015-6-10
     * @author: liujun
     */
    public function jkmsSubject()
    {
        $this->display(T('Document@qbaobei/Widget/jkms/subject'));
    }

    /**
     * 健康美食：当前分类位置
     * @date: 2015-6-11
     * @author: liujun
     */
    public function jkmsCurrentCate($cateId = 0)
    {
        $list = getParentCategory($cateId, 'Category');

        $result = array(
            'type' => 'current_cate',
            'list' => $list,
        );

        $this->assign($result);
        $this->display(T('Document@qbaobei/Widget/jkms/position'));
    }

    /**
     * 健康美食：详情页面右侧
     * @date: 2015-6-11
     * @author: liujun
     */
    public function jkmsDetailRight()
    {
        $this->display(T('Document@qbaobei/Widget/jkms/detail_right'));
    }

    /**
     * 健康美食：每日热点
     * @date: 2015-6-11
     * @author: liujun
     */
    public function jkmsDetailHot($cateId = 0, $notId = '', $limit = 6)
    {
        $field = 'id,title,category_id';
        $order = 'update_time DESC';

        $childIds = $this->getChildIds($cateId);
        $childIds = array_merge(array( $cateId ), $childIds);

        $where = array(
            'status'      => 1,
            'category_id' => array(
                'in',
                $childIds
            )
        );
        if (!empty($notId)) {
            $where['_string'] = 'id not in (' . $notId . ')';
        }
        $list = M('Document')->field($field)->where($where)->order($order)->limit($limit)->select();

        $result = array(
            'type' => 'detail_hot',
            'list' => $list
        );
        $this->assign($result);
        $this->display(T('Document@qbaobei/Widget/jkms/position'));
    }

    /**
     * 健康美食：获取文章标签关键词
     * @date: 2015-6-11
     * @author: liujun
     */
    public function jkmsTags($documentId = 0)
    {
        $tags = getArticleTag($documentId);
        $this->assign('tags', $tags);
        $this->display(T('Document@qbaobei/Widget/jkms/tags'));
    }

    /**
     * 健康美食：详情页相关静态数据
     * @date: 2015-6-16
     * @author: liujun
     */
    public function jkmsData($type = 0)
    {
        $this->assign('type', $type);
        $this->display(T('Document@qbaobei/Widget/jkms/detail_data'));
    }

    /**
     * 健康美食：获取指定模块Id
     * @date: 2015-6-10
     * @author: liujun
     */
    private function getJkmsIds($r_key = 'pid')
    {
        $result = array(
            'pid'       => null,
            'navCateId' => null,
            'childIds'  => null
        );
        //父类Id：母婴食谱,四季养生,饮食健康,美食资讯,饮食文化
        $result['pid'] = array(
            '685',
            '686',
            '687',
            '688',
            '689'
        );
        //导航栏分类id
        $result['navCateId'] = array(
            '685,690,691',
            //母婴食谱分类id
            '686,710,711,712,713',
            //四季养生分类id
            '687,722,723',
            //饮食健康分类id
            '688,730,739',
            //美食资讯分类id
            '689,732,734,733,735',
            //饮食文化分类id
        );
        //获取所有模块子分类
        if ($r_key == 'childIds') {
            $childIds = array();
            foreach ($result['pid'] as $key => $pid) {
                $childIds = array_merge($childIds, $this->getChildIds($pid));
            }
            $result['childIds'] = array_merge($result['pid'], $childIds);
        }

        return $result[$r_key];
    }

    /**
     * 获取所有子分类
     * @date: 2015-6-10
     * @author: liujun
     */
    private function getChildIds($pid = 0, &$lists = array())
    {
        $field    = array( 'id,title,pid' );
        $cateList = M('Category')->field($field)->where(array(
            'status' => 1,
            'pid'    => $pid
        ))->select();
        foreach ($cateList as $key => $value) {
            $lists[] = $value;
            $this->getChildIds($value['id'], $lists);
        }
        $childIds = array_column($lists, 'id');

        return $childIds;
    }


    /**
     * 分类下热门文章
     * @date: 2015-6-12
     * @author: liupan
     */

    public function hotDoc($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $doc = M('Document')->where("`category_id` = '$id' AND position & 16")->limit(10)->select();
        $this->assign('lists', $doc);
        $this->display(T('Document@qbaobei/Widget/hotDoc'));
    }

    /**
     * 资讯详情下热点关注
     * @date: 2015-6-11
     * @author: liupan
     */

    public function hotFocus($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        $num   = 7; // 默认显示7条
        $doc   = M('Document')->where("`category_id` = '$id' AND pc_position & 16 AND status=1")->limit($num)->select();
        $count = count($doc);
        if ($count < $num) {
            $limit  = $num - $count;
            $orther = M('Document')->where("`category_id` = '$id' AND status=1 ")->limit($limit)->select();
            if (is_array($orther)) {
                if (!empty($doc)) {
                    $result = array_merge($doc, $orther);
                } else {
                    $result = $orther;
                }
            }
        } else {
            $result = $doc;
        }
        $this->assign('lists', $result);
        $this->display(T('Document@qbaobei/Widget/hotFocus'));
    }

    /**
     * 重写分页
     * @date: 2015-6-11
     * @author: liupan
     */
    public function categoryPage($row, $count, $cate)
    {

        $count = $count ? $count : 0;
        $row   = $row ? $row : 10;
        $cate  = $cate ? $cate : 1;
        $p     = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        //  $path = staticUrl('lists',$cate,'Document',$p);
        $Page = new \Think\Page($count, $row, '', $cate);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('prev', "<<");
        $Page->setConfig('next', '>>');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE%');
        $this->assign('c', $Page->nowPage);// 当前页数
        $this->assign('t', ceil($count / $row));// 赋值分页输出
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        $this->display(T('Document@qbaobei/Widget/catepage'));
    }


    /****************************************PC2（第二次改版的频道页）*****************************/
    /******************************************肖书成的区域***************************************/
    public function header($cate, $title = '', $nav = true)
    {
        if (empty($cate)) {
            return false;
        }

        if (!is_array($cate)) {
            //获取顶级分类
            $pid = $this->getTopCate($cate);

            //-------------begin 其他指定的分类 add liujun 2015-09-02
            //685营养：1213烘焙,209奶粉,686四季养生,688美食资讯
            if (in_array($cate, array(
                    1213,
                    209,
                    688
                )) || in_array($pid, array(
                    686,
                    1213,
                    209,
                    688
                ))
            ) {
                $pid = 685;//营养
            }
            //-------------end 其他指定的分类

            //分类名称声明
            if (!in_array($pid, array(
                4,
                5,
                145,
                685
            ))
            ) {
                return;
            }
            $arr = array(
                4   => '怀孕',
                5   => '早教',
                145 => '育儿',
                685 => '营养'
            );
            $this->assign('pid', $pid);
            if ($title === false) {
                //                $data = M('Category')->where('id = '.$pid)->getField('title');
                $this->assign('title', $arr[$pid]);
                $this->assign('mbx', 'no');
            } else {
                $this->assign('title', $arr[$pid]);
            }

        } else {
            //            $pid = $cate;
            $this->assign('title', $title);
        }
        $this->assign('nav', $nav);

        //获取所有子分类
        //        $cates = $this->getCate($pid,true);

        //        //相关文章标签      （临时改变规则，现在是写死的所以注释）
        //        $tag    = M('TagsMap')->alias('a')->field('b.id,b.name,b.title,c.title dd')->join('__TAGS__ b ON a.tid = b.id')->join('__DOCUMENT__ c ON a.did = c.id')
        //            ->where("c.status = 1 AND c.category_id IN($cates)")->order('c.view DESC')->group('a.tid')->limit(4)->select();
        //
        //        //页面赋值
        //        $this->assign('tag',$tag);

        //模版调用
        $this->display('Widget/header');
    }

    /**
     * 作者:肖书成
     * 描述:获取文章分类的顶级分类，并缓存（缓存时间:1小时）
     *
     * @param $id
     *
     * @return mixed（顶级分类ID）
     */
    private function getTopCate($id)
    {
        $name = md5($id . __FUNCTION__);
        $rel  = S($name);

        if (!$rel) {
            $rel = $this->topCate($id);
            if ($rel) {
                S($name, $rel, 3600);
            }
        }

        return $rel;
    }

    /**
     * 作者:肖书成
     * 描述:获取文章分类的顶级分类（请使用getTopCate带有缓存，这个没缓存每次都要查数据库）
     *
     * @param $id
     *
     * @return mixed（顶级分类ID）
     */
    private function topCate($id)
    {
        if (empty($id)) {
            return $id;
        }
        $pid = M('Category')->where('id = ' . $id)->getField('pid');
        $pid = (int)$pid;
        if ($pid > 0) {
            return $this->topCate($pid);
        } else {
            return $id;
        }
    }


    /**
     * 作者:肖书成
     * 描述:频道页公共部分
     */
    public function channelPosition($id, $more, $display = true)
    {
        //频道推荐
        $cate = $this->getCate($id, true);

        $position = array();

        //幻灯片
        $position['slide'] = M('Document')->field('id,title,smallimg')->where("status = 1 AND pc_position & 1 AND category_id IN($cate)")->order('update_time DESC')->limit(6)->select();

        //频道头条
        $position['headline'] = M('Document')->field('id,title,description')->where("status = 1 AND pc_position & 8 AND category_id IN($cate)")->order('update_time DESC')->limit(2)->select();

        //频道热点推荐
        $position['hotpot'] = M('Document')->field('id,title')->where("status = 1 AND pc_position & 2 AND category_id IN($cate)")->order('update_time DESC')->limit(4)->select();

        //频道热门推荐
        $position['hot'] = M('Document')->field('id,title')->where("status = 1 AND pc_position & 16 AND category_id IN($cate)")->order('update_time DESC')->limit(5)->select();

        //更多
        $position['more'] = $more;

        if ($display) {
            //页面赋值
            $this->assign('position', $position);

            //模版调用
            $this->display('Widget/channelPosition');
        } else {
            //返回数据
            return $position;
        }

    }

    /**
     * 作者:肖书成
     * 描述:频道页的热词
     *
     * @param $id
     * @param $title
     */
    public function hotWord($id, $title, $limit = 8)
    {
        $cate = $this->getCate($id);

        $cate = list_to_tree($cate, 'id', 'pid', 'child', $id);

        $lists = array();
        foreach ($cate as $k => $v) {

            $cates = $this->getCate($v['id'], true);

            $data = $this->getDocument('id,title', "status = 1 AND category_id IN($cates)", $limit, 'select', 'view DESC');

            if (!empty($data)) {
                $cateInfo          = M('Category')->field('title')->where('id=' . $v['id'])->find();
                $cateInfo['child'] = $data;
                $lists[]           = $cateInfo;
            }
        }

        $header = array(
            'cate'  => $id,
            'title' => $title
        );

        if (empty($lists)) {
            return;
        }

        $number = array(
            'one',
            'two',
            'three',
            'four',
            'five',
            'six',
            'seven',
            'eight',
            'nine',
            'ten',
            'eleven',
            'twelve'
        );

        $this->assign(array(
            'number'  => $number,
            'hotWord' => $lists,
            'header'  => $header
        ));

        $this->display('Widget/hotWords');
    }

    /**
     * 作者:肖书成
     * 描述：频道页的分类 Widget
     */
    public function channelCate($data, $title)
    {
        $data['title'] = $title;
        $this->assign('data', $data);
        $this->display('Widget/channelCate');
    }

    /**
     * 作者:肖书成
     * 描述:育儿频道
     */
    public function kidsChannel()
    {
        //自动推荐
        if (I('key') == 'yes') {
            $arr1 = 145;
            $arr2 = array(
                array(
                    934,
                    4
                ),
                array(
                    129,
                    4
                ),
                array(
                    130,
                    4
                ),
                array(
                    131,
                    4
                ),
            );
            $this->zdtj($arr1, $arr2, false, '育儿频道页');
            die;
        }

        //新生儿
        $xse = $this->kidsCate(934);

        //0-1岁
        $oneYears     = $this->kidsCate(129);
        $cate         = $this->getCate(691, true);
        $xse['right'] = $this->getDocument('id,title,smallimg', "status = 1 AND category_id IN($cate)  AND smallimg > 0", 6, 'select', 'view DESC');

        //1-3岁
        $threeYears = $this->kidsCate(130);

        //3-6岁
        $sixYears = $this->kidsCate(131);
        //3-6岁视频
        $cate          = $this->getCate(1338, true);
        $educationMove = $this->getDocument('id,title,smallimg', "status = 1 AND category_id IN($cate) AND smallimg > 0", 6);

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
            'special',
            null,
            $page_id
        ));

        //页面赋值
        $this->assign(array(
            'xse'           => $xse,
            'oneYears'      => $oneYears,
            'threeYears'    => $threeYears,
            'sixYears'      => $sixYears,
            'educationMove' => $educationMove,
            'SEO'           => $SEO
        ));

        $this->display('Widget/kidsChannel');
    }

    /**
     * 作者:肖书成
     * 描述:怀孕频道
     */
    public function fetationChannel()
    {
        //自动推荐
        if (I('key') == 'yes') {
            $arr1 = 4;
            $arr2 = array(
                array(
                    19,
                    4
                ),
                array(
                    21,
                    4
                ),
                array(
                    192,
                    4
                ),
                array(
                    690,
                    4
                ),
                array(
                    22,
                    4
                ),
            );
            $this->zdtj($arr1, $arr2, false, '怀孕频道页');
            die;
        }

        //备孕
        $one = $this->kidsCate(19);

        //孕中
        $two = $this->kidsCate(21);

        //分娩
        $three = $this->kidsCate(192);

        //妈妈食谱
        $cate           = $this->getCate(690, true);
        $three['right'] = $this->getDocument('id,title,smallimg', "status = 1 AND category_id IN($cate)", 6, 'select', 'view DESC');


        //产后
        $four = $this->kidsCate(22);
        //产后指导
        $ids = array_column($four['img'], 'id');
        $ids = array_merge((array)$ids, array_column($four['new'], 'id'));
        $ids = implode(',', $ids);
        empty($ids) ? $ids = 0 : $ids = $ids;
        $cate = $this->getCate(22, true);//母婴健康->产后

        $four['right'] = $this->getDocument('id,title,smallimg', "status = 1 AND smallimg > 0 AND category_id IN($cate) AND id NOT IN($ids)", 6, 'select', 'view DESC');

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
            'special',
            null,
            $page_id
        ));

        //页面赋值
        $this->assign(array(
            'one'   => $one,
            'two'   => $two,
            'three' => $three,
            'four'  => $four,
            'SEO'   => $SEO,
        ));


        $this->display('Widget/fetationChannel');
    }

    /**
     * 作者:肖书成
     * 描述:早教频道
     */
    public function educationChannel()
    {
        //自动推荐
        if (I('key') == 'yes') {
            $arr1 = 5;
            $arr2 = array(
                array(
                    198,
                    8
                ),
                array(
                    134,
                    4
                ),
                array(
                    138,
                    4
                ),
                array(
                    142,
                    4
                ),
                array(
                    14,
                    1
                ),
                array(
                    16,
                    1
                ),
                array(
                    24,
                    1
                ),
                array(
                    1027,
                    1
                ),
            );
            $this->zdtj($arr1, $arr2, false, '早教频道页');
            die;
        }

        //教育资讯
        $zx = $this->kidsCate(198, 8, false);
        is_array($zx['img']) ? $notIds = ' AND id NOT IN(' . implode(',', array_column($zx['img'], 'id')) . ')' : $notIds = '';
        $cate  = $this->getCate(198, true);
        $newZx = $this->getDocument('id,title,description', "status = 1 AND category_id IN($cate)" . $notIds, 3);

        //早期教育
        //婴儿教育 (育儿导航->0-1岁->婴儿教育)
        $tj         = $this->getImgNew(134, 4, 4);
        $tj['tags'] = $this->getTags(array(
            134,
            138,
            142
        ));

        //幼儿教育 (育儿导航->1-3岁->幼儿教育)
        $ye = $this->getImgNew(138, 4, 4);

        //学龄前教育 (育儿导航->3-6岁->学龄前教育)
        $xl = $this->getImgNew(142, 4, 4);

        //视频
        $cate = $this->getCate(array(
            1330,
            1334,
            1338
        ), true);
        $sp   = $this->getDocument('id,title,smallimg', "status = 1 AND category_id IN($cate)", 6, 'select');

        //特色教育
        $tsjy['data'][] = $this->getImgNew(14, 1, 2);
        $tsjy['data'][] = $this->getImgNew(16, 1, 2);
        $tsjy['data'][] = $this->getImgNew(24, 1, 2);
        $tsjy['data'][] = $this->getImgNew(1027, 1, 2);
        $tsjy['tags']   = $this->getTags(array(
            14,
            16,
            18,
            24,
            1027
        ));

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
            'special',
            null,
            $page_id
        ));

        //页面赋值
        $this->assign(array(
            'zx'    => $zx,
            'newZx' => $newZx,
            'tj'    => $tj,
            'ye'    => $ye,
            'xl'    => $xl,
            'sp'    => $sp,
            'tsjy'  => $tsjy,
            'SEO'   => $SEO,
        ));

        $this->display('Widget/educationChannel');
    }


    /**
     * 作者:肖书成
     * 描述:营养频道
     */
    public function pabulumChannel()
    {
        //自动推荐
        if (I('key') == 'yes') {
            $arr1 = 685;
            $arr2 = array(
                array(
                    688,
                    8
                ),
                array(
                    690,
                    4
                ),
                array(
                    691,
                    4
                ),
                array(
                    209,
                    4
                ),
            );
            $this->zdtj($arr1, $arr2, false, '营养频道页');
            die;
        }


        //美食资讯
        $one        = $this->kidsCate(688, 8, false);
        $cate       = $this->getCate(688, true);
        $one['new'] = $this->getDocument('id,title,description', "status = 1 AND category_id IN($cate)", 3);

        //妈妈食谱
        $two = $this->kidsCate(690);

        //宝宝食谱
        $three       = $this->kidsCate(691);
        $cate        = $this->getCate(688, true);
        $three['ms'] = $this->getDocument('id,title,smallimg,description', "status = 1 AND smallimg > 0 AND category_id IN($cate)", 4);

        //奶粉
        $four = $this->kidsCate(209);
        //奶粉排行版

        $four['ph'] = $this->getDocument('id,title,smallimg', "status = 1 AND smallimg > 0 AND category_id = 213", 6);

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
            'special',
            null,
            $page_id
        ));

        //页面赋值
        $this->assign(array(
            'one'   => $one,
            'two'   => $two,
            'three' => $three,
            'four'  => $four,
            'SEO'   => $SEO,
        ));

        $this->display('Widget/pabulumChannel');
    }


    /**
     * 作者:肖书成
     * 描述:资讯频道页
     */
    public function messageChannel()
    {
        //自动推荐
        if (I('key') == 'yes') {
            $arr1 = array(
                115,
                117,
                688,
                119
            );
            $arr2 = array(
                array(
                    115,
                    8
                ),
                array(
                    117,
                    12
                ),
                array(
                    203,
                    1
                ),
                array(
                    204,
                    1
                ),
                array(
                    305,
                    1
                ),
                array(
                    1115,
                    1
                ),
                array(
                    688,
                    4
                ),
                array(
                    119,
                    10
                )
            );

            $this->zdtj($arr1, $arr2, false, '资讯频道页');
            die;
        }


        //焦点关注(母婴健康->生活->焦点关注)
        $jd = $this->kidsCate(115, 8, true, 3);

        //明星娱乐(母婴健康->生活->娱乐明星)
        $yl = $this->imgTagCate(117, 12);

        //教育资讯(早期教育->教育资讯)
        $jy['tags']   = $this->getTags(198);
        $jy['data'][] = $this->getImgNew(203, 1, 2);//教育热点
        $jy['data'][] = $this->getImgNew(204, 1, 2);//开心娱乐
        $jy['data'][] = $this->getImgNew(305, 1, 2);//节目大全
        $jy['data'][] = $this->getImgNew(1115, 1, 2);//亲子活动
        //早教视频(最新)
        $cate        = $this->getCate(26, 27);
        $jy['video'] = $this->getDocument('id,title,smallimg', "status = 1 AND category_id IN($cate) AND smallimg > 0", 6, 'select', 'view DESC');

        //美食资讯(美食资讯【顶级分类】)
        $ms = $this->kidsCate(688);

        //社会聚焦(母婴健康->生活->社会公益)
        $sh = $this->imgTagCate(119, 10);

        //SEO
        $page_id = I('page_id');
        $page_id && $SEO = WidgetSEO(array(
            'special',
            null,
            $page_id
        ));

        $this->assign(array(
            'jd'  => $jd,
            //焦点关注
            'yl'  => $yl,
            //明星娱乐
            'jy'  => $jy,
            //教育资讯
            'ms'  => $ms,
            //美食资讯
            'sh'  => $sh,
            //社会聚焦
            'SEO' => $SEO
        ));

        $this->display('Widget/messageChannel');
    }

    /**
     * 作者:肖书成
     * 描述:获取分类图文推荐文章和标签
     *
     * @param $id
     * @param int $row
     *
     * @return mixed
     */
    private function imgTagCate($id, $row = 10)
    {
        $cates        = $this->getCate($id, true);
        $data['img']  = $this->getImg($cates, $row);
        $data['tags'] = $this->getTags($id);

        return $data;
    }

    /**
     * 作者:肖书成
     * 描述:获取分类的图文推荐
     *
     * @param $id
     * @param int $row
     *
     * @return mixed
     */
    private function getImg($id, $row = 4)
    {
        is_string($id) ? $cates = $id : $cates = $this->getCate($id, true);
        $img = $this->getDocument('id,title,smallimg,description', "status = 1 AND pc_position & 32 AND category_id IN($cates) AND smallimg > 0", $row);

        return $img;
    }

    private function getImgNew($id, $row1, $row2)
    {
        is_string($id) ? $cates = $id : $cates = $this->getCate($id, true);
        $data['position'] = $this->getImg($id, $row1);
        $data             = (array)$data;
        $data['new']      = $this->getDocument('id,title,description', "status = 1 AND category_id IN($cates)", $row2);

        return $data;
    }

    /**
     * 作者:肖书成
     * 描述:获取分类中点击次数最多的文章标签
     *
     * @param $id
     * @param int $row
     */
    private function getTags($id, $row = 5)
    {
        is_string($id) ? $cates = $id : $cates = $this->getCate($id, true);
        $tags = M('TagsMap')->alias('a')->field('b.id,b.name,b.title,c.title dd')->join('__TAGS__ b ON a.tid = b.id')->join('__DOCUMENT__ c ON a.did = c.id')->where("c.status = 1 AND c.category_id IN($cates)")->order('c.view DESC')->group('a.tid')->limit($row)->select();

        return $tags;
    }


    /**
     * 作者:肖书成
     * 描述:频道分类
     */
    private function kidsCate($cate, $row = 4, $addNew = true, $row2 = false)
    {
        //获取所有分类的字符串
        $cates = $this->getCate($cate, true);

        //查找分类的图文推荐
        $data['img'] = M('Document')->field('id,title,smallimg,description')->where("status = 1 AND pc_position & 32 AND category_id IN($cates)")->order('update_time DESC')->limit($row)->select();

        //查找最新文章的条件初始化
        if (!empty($data['img']) && $addNew) {
            $ids         = implode(',', array_column($data['img'], 'id'));
            $where       = " AND id NOT IN($ids)";
            $data['ids'] = $ids;
        } else {
            $where = '';
        }

        //查找最新文章
        if ($addNew) {
            !is_numeric($row2) && $row2 = $row;
            $data['new'] = M('Document')->field('id,title,description')->where("status = 1 AND category_id IN($cates)" . $where)->order('update_time DESC')->limit($row2)->select();
        }

        //相关文章标签
        $data['tags'] = $this->getTags($cates);

        //返回结果
        return $data;
    }

    /**
     * 作者:肖书成
     * 描述:为百科分类写的一个Widget
     *
     * @param array $info 分了信息
     * @param array $arr 分类列表
     * @param array $SEO 页面SEO
     */
    public function bkLists($info, $arr, $SEO)
    {
        //面包屑
        $pid = $topID = (int)$info['pid'];
        $mbx = '';
        $i   = 0;
        $mb  = array();
        while ($pid > 0) {
            $mb = M('Category')->field('id,title,pid')->where('status = 1 AND id = ' . $pid)->find();
            if (empty($mb)) {//上级分类被禁用，子分类则不生成。
                return false;
            }

            $pid = (int)$mb['pid'];
            $mbx = '<a href="' . staticUrl('lists', $mb['id'], 'Document') . '">' . $mb['title'] . '</a>>' . $mbx;
            $i ++;
        }

        //获取子分类
        $childCate = M('Category')->field('id,title,pid')->where('status = 1 AND pid = ' . $info['id'])->select();

        //判断是否是二级分类
        if ($topID == 0) {
            //页面导航
            $nav = $childCate;

            //列表数据处理
            $lists = $childCate;
            foreach ($lists as $k => &$v) {
                $v['child'] = $this->getCate($v['id']);

                //去除没有子分类的二级分类
                if (empty($v['child'])) {
                    unset($lists[$k]);
                    continue;
                }

                //查找子分类的数据
                foreach ($v['child'] as $k2 => &$v2) {
                    foreach ($arr as $k1 => $v1) {
                        if ($v1['category_id'] == $v2['id']) {
                            $v2['data'][] = $v1;
                            unset($arr[$k1]);
                        }
                    }

                    //去除没有数据的分类
                    if (empty($v2['data'])) {
                        unset($v['child'][$k2]);
                    }

                }
                unset($v2);//防止异常！！

                //去除没有数据的分类
                if (empty($v['child'])) {
                    unset($lists[$k]);
                }

            }
            unset($v);//防止异常！！

        } elseif ($i == 1) {//二级分类的数据

            //页面导航
            $nav = M('Category')->field('id,title,pid')->where('pid = ' . $info['pid'])->select();

            //列表数据处理
            $lists = array();

            $self['id']    = $info['id'];
            $self['title'] = $info['title'];
            $self['pid']   = $info['pid'];
            $self['child'] = $this->getCate($info['id']);
            foreach ($self['child'] as $k => &$v) {
                foreach ($arr as $k1 => $v1) {
                    if ($v1['category_id'] == $v['id']) {
                        $v['data'][] = $v1;
                        unset($arr[$k1]);
                    }
                }

                //去除没有数据的分类
                if (empty($v['data'])) {
                    unset($self['child'][$k]);
                }
            }
            unset($v);

            $lists[] = $self;
        } else {//三级分类,也可以兼容后面还有子分类

            //页面导航
            $nav = M('Category')->field('id,title,pid')->where('pid = ' . $mb['id'])->select();

            //列表数据
            $lists = $arr;
        }

        //页面赋值
        $this->assign(array(
            'level' => $i,
            'SEO'   => $SEO,
            'mbx'   => $mbx,
            'nav'   => $nav,
            'info'  => $info,
            'lists' => $lists,
        ));

        //模板调用
        $this->display('Category/bkLists');
    }

    /**
     * 作者:肖书成
     * 描述：频道页的导航 + 子导航
     *
     * @param $id
     * @param $title
     */
    public function navChild($id, $title)
    {
        if (empty($id) || empty($title) || !is_numeric($id)) {
            return false;
        }

        $cates = M('Category')->field('id,title,icon')->where('status = 1 AND display = 1 AND pid = ' . $id)->select();

        $this->assign(array(
            'id'    => $id,
            'title' => $title,
            'cates' => $cates
        ));

        $this->display('Widget/navChild');
    }

    /**
     * 作者:肖书成
     * 描述:导航标签
     */
    public function navTag($id)
    {
        $tags = $this->getTags($id, 3);
        $this->assign('tags', $tags);
        $this->display('Widget/navTag');
    }



    //----------------------------------私有方法-------------------

    /**
     * 作者:肖书成
     * 描述:分类缓存
     *
     * @param $id
     * @param bool $key
     * @param string $table
     *
     * @return array|mixed|string
     */
    public function getCate($id, $key = false, $table = 'document')
    {
        if (!is_numeric($id) && !is_array($id)) {
            return false;
        }

        $table = ucfirst(strtolower($table));
        if (!in_array($table, array(
            'Document',
            'Gallery'
        ))
        ) {
            return false;
        }
        $table == 'Document' ? $table = 'Category' : $table = 'GalleryCategory';

        is_array($id) ? $arr_id = true : $arr_id = false;

        $arr_id ? $jj = implode(',', $id) : $jj = $id;

        $name = md5($jj . __FUNCTION__ . $table);

        $rel = S($name);

        if (!$rel) {
            $rel = $data = $this->getChileCate($id, $table);

            if (!empty($rel)) {
                S($name, serialize($data), 600);
            }
        } else {
            $rel = unserialize($rel);
        }

        if ($key) {
            if ($arr_id) {
                foreach ($id as $k => $v) {
                    $rel[] = array( 'id' => $v );
                }
            } else {
                $rel[] = array( 'id' => $id );
            }
            $result = implode(',', array_column($rel, 'id'));
        } else {
            $result = $rel;
        }

        return $result;
    }

    /**
     * 作者:肖书成
     * 描述:获取文章数据
     */
    public function getDocument($field, $where, $row, $select = 'select', $order = 'update_time DESC', $swhere = false)
    {
        $model = M('Document')->field($field)->where($where)->order($order);
        $row && $model->limit($row);

        switch ($select) {
            case 'select':
                $data = $model->select();
                break;
            case 'find':
                $data = $model->find();
                break;
            case 'getField':
                $swhere === true ? $data = $model->getField('id', true) : $data = $model->getField('id');
        }

        return $data;
    }


    /**
     * 作者:肖书成
     * 描述：获取一个分类下的所有 子分类
     * 注解:不会使用请勿乱用，此方法是专门为getCate方法写的，用getCate方法会达到一样的效果。
     */
    private function getChileCate($id, $table = 'Category', $lists = array())
    {
        //验证参数
        if (empty($id) && !is_array($id) && !is_numeric($id)) {
            return;
        }
        if (!is_array($lists)) {
            return false;
        }

        //执行SQL操作
        $where = 'status = 1 AND pid ';

        if (is_array($id)) {
            is_array($id[0]) ? $where .= 'IN(' . implode(',', array_column($id, 'id')) . ')' : $where .= 'IN(' . implode(',', $id) . ')';
        } elseif (is_numeric($id)) {
            $where .= '=' . $id;
        } else {
            return $lists;
        }

        $list = M($table)->field('id,title,pid')->where($where)->select();

        //结果处理 （递归）
        if (!empty($list)) {
            $lists = array_filter(array_merge($lists, $list));
            $lists = $this->getChileCate($list, $table, $lists);

        }

        //返回结果
        return $lists;
    }

    /**
     * 作者:肖书成
     * 描述:自动推荐程序，频道页才有用。
     */
    public function zdtj($arr1, $arr2, $arr3, $title)
    {
        header("Content-type:text/html;charset=utf-8");
        $this->techo("正在为您推荐 \"$title\" 数据，请稍候……");

        echo("<h3>频道页头部推荐部分：</h3>");
        //频道推荐
        $data = $this->zdtuPosition($arr1);

        echo("<h3>频道页分类推荐部分&nbsp;&nbsp;&nbsp;(图文推荐)：</h3>");
        //分类推荐
        if (is_array($arr2)) {
            foreach ($arr2 as $k => $v) {
                $data = $this->zdtjCate($v[0], $v[1], $data);
            }
        }

        //热门推荐
        if ($arr3) {
            $this->zdtjHot($arr3, $data);
        }
        $all = count($data['ids']);
        //        初始化了".$data['c']."条数据，
        echo "<h2>本次一共查出了" . $all . "条数据,其中更新了" . $data['s'] . '条数据,其余' . ($all - $data['s']) . '条数据已经被推荐或者更新失败<br/>本次查出来的的所有ID：<br/>' . implode(',', $data['ids']) . '</h2>';


    }

    public function techo($str)
    {
        echo $str . "<br/><br/>" . str_repeat(' ', 256);
        ob_flush();
        flush();
    }


    /**
     * 作者:肖书成
     * 描述:自动推荐程序（频道页总推荐）
     *
     * @param $id
     */
    public function zdtuPosition($id)
    {
        $cate = $this->getCate($id, true);
        //        //初始化数据
        //        $c = $this->saveDocument("category_id IN($cate)");

        //频道页轮播图推荐
        $ids     = $slideId = M('Document')->where("status = 1 AND smallimg > 0 AND category_id IN($cate)")->limit(6)->getField('id', true);
        $slideId = implode(',', $slideId);
        $this->techo("频道页轮播图推荐ID：" . $slideId);
        $data['pc_position'] = 1;
        $s                   = M('Document')->data($data)->where("id IN($slideId)")->save();

        //频道页头条推荐
        $notID               = implode(',', $ids);
        $headlineId          = M('Document')->where("status = 1 AND category_id IN($cate) AND id NOT IN($notID)")->order('view DESC')->limit(2)->getField('id', true);
        $ids                 = array_merge($ids, $headlineId);
        $data['pc_position'] = 8;
        $s += M('Document')->data($data)->where("id IN(" . implode(',', $headlineId) . ")")->save();
        $this->techo("频道页头条推荐ID：" . implode(',', $headlineId));

        //频道页热点文章推荐
        $notID               = implode(',', $ids);
        $hotpotID            = M('Document')->where("status = 1 AND category_id IN($cate) AND id NOT IN($notID)")->order('view DESC')->limit(5)->getField('id', true);
        $ids                 = array_merge($ids, $hotpotID);
        $hotpotID            = implode(',', $hotpotID);
        $data['pc_position'] = 2;
        $s += M('Document')->data($data)->where("id IN($hotpotID)")->save();
        $this->techo("频道页热点文章推荐ID：" . $hotpotID);


        //频道页热门文章推荐
        $notID               = implode(',', $ids);
        $hot                 = M('Document')->where("status = 1 AND category_id IN($cate) AND id NOT IN($notID)")->order('view DESC')->limit(5)->getField('id', true);
        $ids                 = array_merge($ids, $hot);
        $hot                 = implode(',', $hot);
        $data['pc_position'] = 16;
        $s += M('Document')->data($data)->where("id IN($hot)")->save();
        $this->techo("频道页热门文章推荐ID:" . $hot);

        $result['ids'] = $ids;         //推荐的数据ID
        $result['s']   = $s;           //更新成功的条数
        //        $result['c']    = $c;
        return $result;
    }

    //分类推荐
    public function zdtjCate($id, $row, $data)
    {

        if (is_array($id)) {
            $cateId   = implode(',', $id);
            $cateName = M('Category')->where("id IN($cateId)")->getField('title', true);
            $cateName = implode(',', $cateName);
            echo("<h4>“" . $cateName . "&nbsp;&nbsp;&nbsp;”的分类推荐：</h4>");
        } else {
            $cateName = M('Category')->where("id = $id")->getField('title');
            echo("<h4>“" . $cateName . "&nbsp;&nbsp;&nbsp;”的分类推荐：</h4>");
        }

        $cate = $this->getCate($id, true);
        //初始化数据
        //        $data['c'] = (int)$data['c'] + (int)($this->saveDocument("category_id IN($cate) AND position & 32"));

        $notID = implode(',', $data['ids']);
        $imgID = M('Document')->where("status = 1 AND smallimg > 0 AND category_id IN($cate) AND id NOT IN($notID)")->order('view DESC')->limit($row)->getField('id', true);
        if (!empty($imgID)) {
            $data['ids'] = array_merge($data['ids'], $imgID);
            $imgID       = implode(',', $imgID);
            $this->techo("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;本次推荐的ID是：" . $imgID);
            $data['pc_position'] = 32;
            $data['s'] += M('Document')->data($data)->where("id IN($imgID)")->save();

        } else {
            $this->techo('本次推荐失败，原因：没有数据！');
        }

        return $data;
    }

    //热门推荐
    public function zdtjHot($id, $data, $row = 5)
    {
        if (!is_numeric($id)) {
            $this->techo('热门推荐失败，原因:参数不正确');

            return $data;
        }

        $cateStr2 = $this->getCate($id, true);
        $notID    = implode(',', $data['ids']);
        $imgID    = M('Document')->where("status = 1 AND smallimg > 0 AND category_id IN($cateStr2) AND id NOT IN($notID)")->order('view DESC')->limit($row)->getField('id', true);

        if (!empty($imgID)) {
            $data['ids']         = array_merge($data['ids'], $imgID);
            $imgID               = implode(',', $imgID);
            $data['pc_position'] = 16;
            $this->techo("本次推荐的ID是：" . $imgID);
            $data['s'] += M('Document')->data($data)->where("id IN($imgID)")->save();
        } else {
            $this->techo('热门推荐失败，原因:没有图片数据或者没数据');
        }

        return $data;
    }

    public function getSQL()
    {
        $cate = I('cate');
        if (empty($cate)) {
            echo "参数错误";
            exit;
        }
        $cate = $this->getCate($cate, true);
        $sql  = M('Document')->field('`id`,`title`,`category_id`')->where("status = 1 AND category_id IN($cate) AND smallimg > 0")->limit(20)->buildSql();
        dump($sql);
        die;
    }

    /**
     * 作者:肖书成
     * 描述:视频列表导航
     *
     * @param $cate
     */
    public function videoNav($cate)
    {
        if (!is_numeric($cate)) {
            return false;
        }
        $topCate = M('Category')->field('id,title')->where('status = 1 AND pid = 1307')->select();

        $topCateID = array_column($topCate, 'id');
        //        dump($topCateID);die;
        if (in_array($cate, $topCateID)) {
            $topId     = $cate;
            $childCate = M('Category')->field('id,title')->where("pid = $cate")->select();
        } else {
            $topId = M('Category')->where("status = 1 AND id = $cate")->getField('pid');
            if ($topId == '0') {
                return false;
            }
            $childCate = M('Category')->field('id,title')->where("pid = $topId")->select();
            $this->assign('childId', $cate);
        }

        $this->assign(array(
            'topId'     => $topId,
            'topCate'   => $topCate,
            'childCate' => $childCate,
        ));

        $this->display('Widget/videoNav');
    }

    //修改视频列表分类列表模版
    public function setVideo()
    {
        $cate                       = $this->getCate(1307, true);
        $data['template_index']     = 'video';
        $data['template_lists_hot'] = '';
        $num                        = M('Category')->data($data)->where("id IN($cate)")->save();
        dump($num);
    }

    public function setPath()
    {
        $pingyin = new \OT\PinYin();
        $cate    = $this->getCate(1307);
        //        $data['path_lists']     = 'sp/{page}';
        //        $data['path_lists_hot'] = 'hot/'.$data['path_lists'];
        //        M('Category')->where('id = 1307')->data($data)->save();


        $cate = list_to_tree($cate, 'id', 'pid', '_', 1307);

        $s = 0;

        foreach ($cate as $k => $v) {
            $name1 = $pingyin->getFirstPY($v['title']);

            $data['path_lists']     = 'sp/' . $name1 . '/{page}';
            $data['path_lists_hot'] = 'hot/' . $data['path_lists'];
            $s += M('Category')->where('id = ' . $v['id'])->data($data)->save();

            foreach ($v['_'] as $k1 => $v1) {
                $name2 = $name1 . '/' . ($pingyin->getFirstPY($v1['title']));

                $data['path_lists']     = 'sp/' . $name2 . '/{page}';
                $data['path_lists_hot'] = 'hot/' . $data['path_lists'];
                $s += M('Category')->where('id = ' . $v1['id'])->data($data)->save();
            }
        }

        echo '成功更新' . $s . '条数据';
    }

    /**************************************肖书成的区域end**************************************/
    /**
     * 获取百科分类
     * @date: 2015-8-24 15:59:08
     * @author: JeffreyLau
     */

    public function getBaikeCates($category, $name = "")
    {
        if (!is_numeric($category)) {
            return;
        }
        $cateList = M('Category')->where(array( "pid" => $category ))->select();
        $this->assign('categoryName', $name);
        $this->assign('cateList', $cateList);
        $this->display(T('Document@qbaobei/Widget/baikeIndexBlock'));
    }

    /**
     * 获取百科分类下的内容列表
     * @date: 2015-8-24 15:59:08
     * @author: JeffreyLau
     */
    public function baikeCon($category)
    {
        if (!is_numeric($category)) {
            return;
        }
        $cateList = M('Category')->where(array( "pid" => $category ))->select();
        $ids      = '';
        foreach ($cateList as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids = rtrim($ids, ",");
        if (!empty($ids)) {
            $lists = M("Document")->where("status=1  AND category_id IN(" . $ids . ")")->order("update_time desc")->limit("10")->field("*")->select();
        }
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/baikeConBlock'));
    }


    /**
     * 获取百科频道页标签
     * @date: 2015-8-24 15:59:08
     * @author: JeffreyLau
     */
    public function baikeChannelTags()
    {
        $cate   = "";
        $k      = 0;
        $rootID = "746,747,748,749,750,751";
        $m      = M('Document')->alias("__DOCUMENT")->where("status=1 AND category_rootid IN(" . $rootID . ")")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
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
        $this->display(T('Document@qbaobei/Widget/baikeIndexTags'));
    }


    /**
     * 获取百科频道页头条
     * @date: 2015-8-24 15:59:08
     * @author: JeffreyLau
     */
    public function baikeIndexHeadline()
    {
        $rootID   = "746,747,748,749,750,751";
        $slider   = M("Document")->alias("__DOCUMENT")->where("status=1 AND pc_position & 1 AND category_rootid IN(" . $rootID . ")")->order("update_time desc")->limit("4")->field("*")->select();//频道首页幻灯推荐
        $headline = M("Document")->alias("a")->join('left join __DOCUMENT_BAIKE__ b ON a.id=b.id')->where("status=1 AND pc_position & 8 AND category_rootid IN(" . $rootID . ")")->order("update_time desc")->limit("2")->field("a.id as id,a.title as title,a.description as description,b.intro as intro")->select();
        $latest   = M("Document")->alias("__DOCUMENT")->where("status=1 AND category_rootid IN(" . $rootID . ")")->order("create_time desc")->limit("4")->field("*")->select();
        $result   = '';
        $this->assign('slider', $slider);
        $this->assign('headline', $headline);
        $this->assign('latest', $latest);
        $this->assign('lists', $result);
        $this->display(T('Document@qbaobei/Widget/baikeIndexHeadline'));
    }


    /**
     * Description：    百科详情目录提取
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-26 16:52:51
     */

    public function baikeCatalog($content)
    {
        $pattern = "/<strong>(.*?)<\/strong><\/span>/";
        preg_match_all($pattern, $content, $m);
        $k = 0;
        foreach ($m[1] as $key => $val) {
            $k                 = $k + 1;
            $list[$k]['title'] = $val;
        }
        $this->assign('lists', $list);
        $this->display(T('Document@qbaobei/Widget/baikeCatalog'));

    }


    /**
     * Description：    百科详情内容格式转换
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-26 17:21:57
     */

    public function baikeContent($content)
    {
        $i       = 0;
        $pattern = "/<a name=\"p(\d+?)\">/";
        $m       = preg_split($pattern, $content);
        foreach ($m as $val) {
            $i = $i + 1;
            preg_match("/<strong>([^\/]+?)<\/strong><\/span>/", $val, $m);
            $lists[$i]['title'] = $m[1];
            $a_content          = preg_replace("/<span style=\"([^\/]+?)\"><strong>([^\/]+?)<\/strong><\/span>/", "", $val);
            // $a_content=str_replace("</a><br />","",$a_content);
            $lists[$i]['content'] = $a_content;
        }
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/baikeContent'));

    }


    /**
     * Description：    相关百科
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 09:12:51
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
     * Description：    推荐百科
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 09:12:51
     */

    public function commendedBaike()
    {
        $rootID = "746,747,748,749,750,751";
        //频道页热点文章推荐
        $lists = M("Document")->alias("a")->join("__DOCUMENT_BAIKE__ b on a.id=b.id")->where("status=1 AND pc_position & 2 AND category_rootid IN(" . $rootID . ")")->order("a.update_time desc")->limit("3")->field("*")->select();
        $this->assign("lists", $lists);
        $this->display(T('Document@qbaobei/Widget/commentBaike'));

    }


    /**
     * Description：    相关视频
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 09:12:51
     */

    public function relateVideo($cate)
    {
        $videoList = M("Document")->alias("__DOCUMENT")->order("view desc")->where("status=1")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("5")->field("*")->select();
        $this->assign("lists", $videoList);
        $this->display(T('Document@qbaobei/Widget/relateVideo'));

    }


    /**
     * 二期详情页：当前分类位置
     * @date: 2015-8-25
     * @author: liujun
     */
    public function currentPosition($cateId = 0)
    {
        $list = getParentCategory($cateId, 'Category');
        $this->assign('list', $list);
        $this->assign('type', 'position');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页：获取文章标签
     * @date: 2015-8-25
     * @author: liujun
     */
    public function tags($id = 0, $type = 'tags')
    {
        $list = get_tags($id, 'Document');
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页底部：获取相关最新文章,排除已推荐的避免和右侧热门推荐文章重复
     * @date: 2015-8-25
     * @author: liujun
     */
    public function newDocument($id = 0, $category_id = 0, $limit = 4)
    {
        $field = 'd.id,d.name,d.title,d.smallimg,a.content';
        $order = 'update_time DESC';
        $join  = 'left join ' . C(DB_PREFIX) . 'document_article as a ON d.id = a.id';
        $where = array(
            'd.status'           => 1,
            'd.id'               => array(
                'neq',
                $id
            ),
            'd.content_position' => 0,
            'd.category_id'      => $category_id
        );
        $list  = M('Document')->alias('d')->join($join)->field($field)->where($where)->order($order)->limit($limit)->select();

        //如果不存在：获取子类下的数据
        if (empty($list) || count($list) < $limit) {
            $childIds               = $this->getChildIds($category_id);//所有子类
            $childIds               = array_merge(array( $category_id ), $childIds);
            $where['d.category_id'] = array(
                'in',
                $childIds
            );
            if ($list) {
                $where['d.id'] = array(
                    'not in',
                    array_merge(array( $id ), array_column($list, 'id'))
                );
            }
            $clist = M('Document')->alias('d')->join($join)->field($field)->where($where)->order($order)->limit($limit - count($list))->select();
            if ($list) {
                $list = array_merge($list, $clist);
            } else {
                $list = $clist;
            }
        }
        //如果不存在：获取顶级目录下的数据
        if (empty($list) || count($list) < $limit) {
            $cateList = getParentCategory($category_id, 'Category');
            if ($cateList) {
                $pid                    = $cateList[0]['id'];
                $childIds               = $this->getChildIds($pid);//所有子类
                $childIds               = array_merge(array( $pid ), $childIds);
                $where['d.category_id'] = array(
                    'in',
                    $childIds
                );
                if ($list) {
                    $where['d.id'] = array(
                        'not in',
                        array_merge(array( $id ), array_column($list, 'id'))
                    );
                }
                $plist = M('Document')->alias('d')->join($join)->field($field)->where($where)->order($order)->limit($limit - count($list))->select();
                if ($list) {
                    $list = array_merge($list, $plist);
                } else {
                    $list = $plist;
                }
            }
        }

        $this->assign('list', $list);
        $this->assign('type', 'newDocument');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页底部：热门关注
     * 调用百科指定的分类
     * @date: 2015-8-26
     * @author: liujun
     */
    public function hotFollow($limit = 8)
    {
        $result = array();
        //怀孕百科、育儿百科、早教百科、营养美食百科、保健养生百科、生活用品百科
        $cateIds  = array(
            '746',
            '747',
            '748',
            '749',
            '750',
            '751'
        );
        $where    = array(
            'status' => 1,
            'id'     => array(
                'in',
                $cateIds
            )
        );
        $cateList = M('Category')->field('id,title')->where($where)->select();
        foreach ($cateList as $key => $cate) {
            $childIds                    = $this->getChildIds($cate['id']);//所有子类
            $childIds                    = array_merge(array( $cate['id'] ), $childIds);
            $where                       = array(
                'status'      => 1,
                'category_id' => array(
                    'in',
                    $childIds
                )
            );
            $document                    = M('Document')->field('id,name,title')->where($where)->order('update_time DESC')->limit($limit)->select();
            $result[$key]['category_id'] = $cate['id'];
            $result[$key]['title']       = $cate['title'];
            $result[$key]['document']    = $document;
        }
        $this->assign('list', $result);
        $this->assign('type', 'hotFollow');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页右侧：获取当前文章二级分类下的热门视频，按点击量排序
     * @date: 2015-8-25
     * @author: liujun
     */
    public function hotVideo($category_id = 0, $limit = 6, $type = 'hotVideo')
    {
        $list         = array();
        $video_cId    = 0;//视频分类ID
        $video_cTitle = '';//视频分类名
        $cateList     = getParentCategory($category_id, 'Category');
        if (!empty($cateList[1]['id'])) {
            $video_cTitle = $cateList[1]['title'];
            //视频所有分类Id
            $videoPId  = 1307;
            $childIds  = $this->getChildIds($videoPId);//所有子类
            $childIds  = array_merge(array( $videoPId ), $childIds);
            $where     = array(
                'status' => 1,
                'title'  => $video_cTitle,
                'id'     => array(
                    'in',
                    $childIds
                )
            );
            $videoCate = M('Category')->field('id')->where($where)->find();
            if ($videoCate) {
                $video_cId = $videoCate['id'];
            }
            //获取热门视频
            if ($video_cId > 0) {
                $field = 'id,name,title,smallimg';
                $where = array( 'status' => 1 );
                unset($childIds);
                $childIds = $this->getChildIds($video_cId);//所有子类
                if (!empty($childIds)) {
                    $childIds             = array_merge(array( $video_cId ), $childIds);
                    $where['category_id'] = array(
                        'in',
                        $childIds
                    );
                } else {
                    $where['category_id'] = $video_cId;
                }
                $list = M('Document')->field($field)->where($where)->order('view DESC')->limit($limit)->select();
            }
        }
        $this->assign('list', $list);
        $this->assign('video_cId', $video_cId);
        $this->assign('video_cTitle', $video_cTitle);
        $this->assign('type', $type);
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页右侧：获取当前文章前两个标签下的文章
     * @date: 2015-8-25
     * @author: liujun
     */
    public function tagsDocument($document_id = 0, $limit = 8)
    {
        $result = array();
        $oldIds = array();
        $total  = 0;//文章总数量
        $tags   = get_tags($document_id, 'Document');
        foreach ($tags as $key => $tag) {
            if ($key + 1 <= 2) {
                $where = array(
                    'd.status' => 1,
                    'm.tid'    => $tag['id'],
                    'd.id'     => array(
                        'neq',
                        $document_id
                    )
                );
                //前一个标签下的文章，避免多个标签相同数据出现。
                if ($oldIds) {
                    $where['d.id'] = array(
                        'not in',
                        array_merge(array( $document_id ), $oldIds)
                    );
                }
                $document        = M('TagsMap')->alias('m')->join('left join ' . C(DB_PREFIX) . 'document as d ON m.did = d.id')->field('d.id,d.name,d.title')->where($where)->order('d.update_time DESC')->limit($limit)->select();
                $total           = $total + count($document);//文章总数量
                $tag['document'] = $document;
                if ($document) {
                    $idArr  = array_merge($oldIds, array_column($document, 'id'));
                    $oldIds = array_unique($idArr);
                }
                $result[] = $tag;
            } else {
                break;
            }
        }
        $this->assign('total', $total);
        $this->assign('list', $result);
        $this->assign('type', 'tagsDocument');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详情页右侧：获取当前顶级分类下的二级【热门分类推荐】下的【热门推荐文章】,文章按照时间先后排序
     * @date: 2015-8-25
     * @author: liujun
     */
    public function hotDocument($document_id = 0, $category_id = 0, $limit = 8)
    {
        $result = array();
        $pId    = 0;
        //获取当前文章分类的顶级目录
        $cateList = getParentCategory($category_id, 'Category');
        if (!empty($cateList)) {
            $pId = $cateList[0]['id'];//顶级目录
            //获取第二级热门分类推荐的分类Ids
            $where     = array(
                'status'   => 1,
                'position' => 1,
                'pid'      => $pId
            );
            $hotCatIds = M('Category')->field('id,title')->where($where)->order('sort ASC')->select();
            foreach ($hotCatIds as $key => $value) {
                $childIds = $this->getChildIds($value['id']);//所有子类
                $childIds = array_merge(array( $value['id'] ), $childIds);
                $where    = array(
                    'status'           => 1,
                    'id'               => array(
                        'neq',
                        $document_id
                    ),
                    'content_position' => 1,
                    'category_id'      => array(
                        'in',
                        $childIds
                    )
                );
                $document = M('Document')->field('id,name,title,description')->where($where)->order('update_time DESC')->limit($limit)->select();

                $result[$key]['category_id'] = $value['id'];
                $result[$key]['title']       = $value['title'];
                $result[$key]['list']        = $document;
            }
        }

        $this->assign('list', $result);
        $this->assign('document_id', $document_id);//文章Id
        $this->assign('category_id', $category_id);//当前文章分类Id
        $this->assign('pid', $pId);//顶级分类Id
        $this->assign('type', 'hotDocument');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详列表右侧：妈妈交流
     * @date: 2015-8-28
     * @author: liujun
     */
    public function mamaTalk()
    {
        $this->assign('type', 'mamaTalk');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }

    /**
     * 二期详列表右侧：热门专题
     * @date: 2015-8-28
     * @author: liujun
     */
    public function hotPlans()
    {
        $this->assign('type', 'hotPlans');
        $this->display(T('Document@qbaobei/Widget/newDetailBlock'));
    }


    /**
     * Description：    图库首页
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function tukuIndex()
    {
        //SEO
        $this->assign("SEO", WidgetSEO(array(
            'special',
            null,
            '67'
        )));
        $this->display(T('Document@qbaobei/Index/pic'));

    }

    /**
     * Description：    图库头条
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function tukuHeadline()
    {

        $slider   = M("Gallery")->where("pc_position & 1")->order('update_time DESC')->limit('4')->select();
        $headline = M("Gallery")->alias("__GALLERY")->where("pc_position & 8")->order("update_time desc")->join("INNER JOIN __GALLERY_ALBUM__ ON __GALLERY.id = __GALLERY_ALBUM__.id")->limit("2")->field("*")->select();
        $commend  = M("Gallery")->where("pc_position & 16")->order('update_time DESC')->limit('3')->select();
        $latest   = M("Gallery")->order('create_time DESC')->limit('4')->select();
        $this->assign('slider', $slider);
        $this->assign('headline', $headline);
        $this->assign('commend', $commend);
        $this->assign('latest', $latest);
        $this->display(T('Document@qbaobei/Widget/tukuHeadline'));

    }


    /**
     * Description：    图库首页内容
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */
    public function tukuIndexList($cate, $type, $title, $ico = "")
    {

        $lists = M("Gallery")->where(array( "category_rootid" => $cate ))->order('update_time DESC')->limit('11')->select();

        $tpl = $type == '1' ? 'tukuIndexList1' : 'tukuIndexList2';
        $this->assign('title', $title);
        $this->assign('ico', $ico);
        $this->assign('cate', $cate);
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/' . $tpl));
    }

    /**
     * Description：    视频首页
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function videoIndex()
    {
        //SEO
        $this->assign("SEO", WidgetSEO(array(
            'special',
            null,
            '63'
        )));
        $this->display(T('Document@qbaobei/Index/video'));

    }

    /**
     * Description：    百科首页
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function baikeIndex()
    {
        //SEO
        $this->assign("SEO", WidgetSEO(array(
            'special',
            null,
            '64'
        )));
        $this->display(T('Document@qbaobei/Index/index'));

    }

    /**
     * Description：    视频首页内容
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function videoIndexZaojiao($cate, $type, $title, $ico = "")
    {
        $tpl = $type == '1' ? 'videoIndexList1' : 'videoIndexList2';
        $this->assign('title', $title);
        $this->assign('ico', $ico);
        // $this->assign('lists',$lists);
        $this->assign('cate', $cate);
        $this->display(T('Document@qbaobei/Widget/videoIndexZaojiao'));
    }

    /**
     * Description：    视频首页内容
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-27 19:30:55
     */

    public function videoIndexList($cate, $type, $title, $ico = "")
    {
        if (!is_numeric($cate)) {
            return;
        }
        $rs                   = D('Category')->getAllChildrenId($cate); //获取分类及分类所有id
        $where                = array();
        $where['category_id'] = array(
            'in',
            $rs
        );
        $where['status']      = array(
            'eq',
            '1'
        );
        $lists                = M("Document")->alias("__DOCUMENT")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->where($where)->order('update_time DESC')->limit('9')->select();
        $tpl                  = $type == '1' ? 'videoIndexList1' : 'videoIndexList2';
        $this->assign('title', $title);
        $this->assign('ico', $ico);
        $this->assign('lists', $lists);
        $this->assign('cate', $cate);
        $this->display(T('Document@qbaobei/Widget/' . $tpl));
    }

    /**
     * Description：    视频首页头条和幻灯
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-28 19:36:45
     */

    public function videoHeadline()
    {
        $headline = M("Document")->alias("__DOCUMENT")->where("status=1 AND pc_position & 8")->order("update_time desc")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("2")->field("*")->select();
        $latest   = M("Document")->alias("__DOCUMENT")->where("status=1")->order("create_time desc")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("4")->field("*")->select();
        $slider   = M("Document")->alias("__DOCUMENT")->where("status=1 AND pc_position & 1")->order("update_time desc")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("4")->field("*")->select();
        $lists    = '';
        $this->assign('lists', $lists);
        $this->assign('headline', $headline);
        $this->assign('slider', $slider);
        $this->assign('latest', $latest);
        $this->display(T('Document@qbaobei/Widget/videoHeadline'));
    }


    /**
     * Description：    详情页相关视频
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-28 19:36:45
     */

    public function videoDetailRelate($id)
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
        $this->display(T('Document@qbaobei/Widget/guessulikeVideo'));
    }


    /**
     * Description：    详情页视频排行
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-28 19:36:45
     */

    public function detailVideoRank()
    {
        $lists = M("Document")->alias("__DOCUMENT")->where("status=1")->order("view desc")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("8")->field("*")->select();
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/detailVideoRank'));
    }

    /**
     * Description：    相关标签
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-28 15:36:22
     */

    public function videoRelateTags($id)
    {
        if (!is_numeric($id)) {
            return;
        }//
        $tags = M("TagsMap")->alias("__TAGSMAP")->join("INNER JOIN __TAGS__ ON __TAGSMAP.tid = __TAGS__.id")->where(array(
            "did"  => $id,
            "type" => "document"
        ))->select();
        $this->assign("lists", $tags);
        $this->display(T('Document@qbaobei/Widget/videoDetailTag'));
    }


    /**
     * Description：    热播排行
     * Author:         Jeffrey Lau
     * Modify Time:    2015-8-28 15:36:22
     */

    public function videoIndexRank()
    {
        $lists = M("Document")->alias("__DOCUMENT")->where("status=1")->order("view desc")->join("INNER JOIN __DOCUMENT_VIDEO__ ON __DOCUMENT.id = __DOCUMENT_VIDEO__.id")->limit("5")->field("*")->select();
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/videoIndexRank'));
    }


    public function videoTags($cate)
    {
        if (!is_numeric($cate)) {
            return;
        }
        $k        = 0;
        $cateList = M('Category')->where(array( "pid" => $cate ))->select();
        $ids      = '';
        foreach ($cateList as $key => $val) {
            $ids .= $val['id'] . ",";
        }
        $ids = rtrim($ids, ",");
        if (empty($ids)) {
            return;
        }
        $m = M('Document')->alias("__DOCUMENT")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->where("status=1  AND category_id IN(" . $ids . ")")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
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
        $this->display(T('Document@qbaobei/Widget/videoTag'));
    }


    //展示二级菜单
    public function showChildMenu($cate)
    {
        if (!is_numeric($cate)) {
            return;
        }
        $lists = M("Category")->where(array(
            "pid"     => $cate,
            "display" => "1"
        ))->select();
        $this->assign('lists', $lists);
        $this->display(T('Document@qbaobei/Widget/showChildMenu'));
    }

    //展示二级菜单
    public function showMenuTag()
    {
        $k      = 0;
        $rootID = "746,747,748,749,750,751";
        $m      = M('Document')->alias("__DOCUMENT")->where("status=1 AND category_rootid IN(" . $rootID . ")")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
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
        $this->display(T('Document@qbaobei/Widget/showMenuTag'));
    }

    public function showMenuTagVideo()
    {
        $k = 0;
        $m = M('Document')->alias("__DOCUMENT")->where("status=1 AND model_id = '60'")->join("__TAGS_MAP__ ON __DOCUMENT.id = __TAGS_MAP__.did")->order("__DOCUMENT.view desc")->limit("20")->field("*")->select();
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
        $this->display(T('Document@qbaobei/Widget/showMenuTag'));
    }

    /**
     * 作者:肖书成
     * 描述:作文详情页
     * 时间:2015-2-24
     *
     * @param $info
     * @param $tags
     */
    public function compositionDetail($info, $tags)
    {
        //热点关注（详情页推荐 热门文章）
        $ids     = array( $info['id'] );
        $ids_str = implode(',', $ids);

        $hot_article = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND content_position & 1 AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('update_time DESC')->limit(5)->select();
        $count       = count($hot_article);
        if ($count < 5) {
            if ($count == 0) {
                $hot_article = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('view DESC')->limit(5)->select();
            } else {
                $ids          = array_merge($ids, array_column($hot_article, 'id'));
                $ids_str      = implode(',', $ids);
                $hot_article1 = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('view DESC')->limit(5 - $count)->select();
                $hot_article  = array_merge($hot_article, $hot_article1);
            }
        }
        $ids = array_filter(array_merge($ids, (array)array_column($hot_article, 'id')));

        //精彩图库
        $photo = M('Gallery')->field('id,title,smallimg')->where('status = 1 AND smallimg != 0')->order('view DESC')->limit('6')->select();

        //标签数据
        $tag_article = array();
        if (!empty($tags)) {
            $tag_article = M('TagsMap')->alias('a')->field('b.id,b.title')->join('__DOCUMENT__ b ON a.did = b.id')->where('a.tid IN(' . implode(',', array_column($tags, 'id')) . ') AND b.status = 1 AND b.id NOT IN(' . implode(',', $ids) . ')')->order('b.view DESC')->group('b.id')->limit(6)->select();
        }
        $count = count($tag_article);
        if ($count < 6) {
            if ($count == 0) {
                $tag_article = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND status = 1 AND id NOT IN(' . implode(',', $ids) . ')')->limit(6)->select();
            } else {
                $tag_article1 = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND status = 1 AND id NOT IN(' . implode(',', $ids) . ')')->limit(6 - $count)->select();
                $tag_article  = array_filter(array_merge($tag_article, (array)$tag_article1));
            }
            if (count($tag_article) < 6) {
                $tag_article = M('Document')->field('id,title')->where('status = 1 AND id NOT IN(' . implode(',', $ids) . ')')->limit(6)->select();
            }
        }

        //文章详情 上 下分页
        $page_list = array();
        $page1     = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND id > ' . $info['id'])->order('id desc')->find();
        $page2     = M('Document')->field('id,title')->where('category_id = ' . $info['category_id'] . ' AND id < ' . $info['id'])->order('id desc')->find();

        if ($page1) {
            $page_list[0]['title'] = '上一篇：';
            $page_list[0]['cont']  = $page1;
        } else {
            $page_list[0]['title'] = '上一篇：没有了！';
        }

        if ($page2) {
            $page_list[1]['title'] = '下一篇：';
            $page_list[1]['cont']  = $page2;
        } else {
            $page_list[1]['title'] = '下一篇：没有了';
        }

        //导航
        $nav  = '';
        $cate = M('Category')->field('id,title,pid,status')->where('id = ' . $info['category_id'])->find();
        if ($cate) {
            if ($cate['status'] == 1) {
                $this->assign('cate', $cate);
                $nav = "<a href=\"" . staticUrl('lists', $cate['id'], 'Document') . "\" target=\"_self\">" . $cate['title'] . "</a>";
            }
            while ($cate && $cate['pid'] > 0) {
                if ($cate['status'] == 1) {
                    $cate = M('Category')->field('id,title,pid,status')->where('id = ' . $cate['pid'])->find();
                    $nav  = "<a href=\"" . staticUrl('lists', $cate['id'], 'Document') . "\" target=\"_self\">" . $cate['title'] . "</a><span> &gt; </span>" . $nav;
                }
            }
        }

        $this->assign(array(
            'info'        => $info,
            'tags'        => $tags,
            'hot_article' => $hot_article,
            'photo'       => $photo,
            'tag_article' => $tag_article,
            'page_list'   => $page_list,
            'nav'         => $nav
        ));

        $this->display('Widget/composition_detail');
    }


    /**
     * 作者:肖书成
     * 描述:作文分类列表页
     * 时间:2015-2-24
     *
     * @param $info
     * @param $lists
     */
    public function compositionLists($info, $lists)
    {
        $ids_str = implode(',', array_column($lists, 'id'));

        //导航
        $cate = M('Category')->field('id,title,pid,status')->where('id = ' . $info['id'])->find();
        $nav  = '';
        while ($cate && $cate['pid'] > 0) {
            if ($cate['status'] == 1) {
                $cate = M('Category')->field('id,title,pid,status')->where('id = ' . $cate['pid'])->find();
                $nav  = "<a href=\"" . staticUrl('lists', $cate['id'], 'Document') . "\" target=\"_self\">" . $cate['title'] . "</a><span> &gt; </span>" . $nav;
            }
        }

        //编辑推荐
        $cate_childs_str   = $this->getCate($info['id'], true);
        $recommend_article = M('Document')->field('id,title,category_id,smallimg')->where('category_id IN(' . $cate_childs_str . ') AND content_position & 1 AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('update_time DESC')->limit(4)->select();
        $count             = count($recommend_article);

        if ($count < 4) {
            if ($count == 0) {
                $recommend_article = M('Document')->field('id,title,smallimg')->where('category_id = ' . $info['id'] . ' AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('view DESC')->limit(4)->select();
            } else {
                $ids_str .= ',' . implode(',', array_column($recommend_article, 'id'));
                $recommend_article1 = M('Document')->field('id,title,smallimg')->where('category_id = ' . $info['id'] . ' AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('view DESC')->limit(4 - $count)->select();
                !empty($recommend_article1) && ($ids_str .= ',' . implode(',', array_column($recommend_article1, 'id')));
                $recommend_article = array_filter(array_merge($recommend_article, (array)$recommend_article1));
            }
        }


        //热门文章
        $hot_article = M('Document')->field('id,title,description')->where('category_id IN(' . $cate_childs_str . ') AND status = 1')->order('view DESC')->limit(17)->select();
        $count       = count($hot_article);
        if ($count < 17) {
            if ($count == 0) {
                $hot_article = M('Document')->field('id,title,description')->where('category_rootid = ' . $info['rootid'] . ' AND status = 1')->order('view DESC')->limit(17)->select();
            } else {
                $ids_str      = implode(',', array_column($hot_article, 'id'));
                $hot_article1 = M('Document')->field('id,title,description')->where('category_rootid = ' . $info['rootid'] . ' AND status = 1 AND id NOT IN(' . $ids_str . ')')->order('view DESC')->limit(17 - $count)->select();
                $hot_article  = array_filter(array_merge($hot_article, (array)$hot_article1));
            }
        }

        //专题精选
        $hot_document = array_chunk($hot_article, 10);
        $hot_article  = $hot_document[0];
        $zt_article   = array_shift($hot_document[1]);
        $zt_lists     = $hot_document[1];

        $this->assign(array(
            'nav'               => $nav,
            'recommend_article' => $recommend_article,
            'hot_article'       => $hot_article,
            'zt_article'        => $zt_article,
            'zt_lists'          => $zt_lists
        ));

        $this->display('Widget/composition_lists');
    }

    /**
     * 描述：食谱最新模板格式数据
     *
     * @param $info
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function food($info)
    {
        if ($info['step_read'] == '1' && (int)$info['create_time'] > 1452787200) {
            preg_match_all("/<h2>([\s\S]*?)<\/h2>[\s\S]*?<img.*?src=(?<t>['\"])(.*?)\g{t}/i", $info['content'], $array);
            $info['stepList'] = array();
            foreach ($array[1] as $k => $v) {
                $rel['number'] = $k + 1;
                $rel['title'] = $v;
                $rel['img'] = $array[3][$k];

                $info['stepList'][] = $rel;
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
        $this->assign('info', $info);
        $this->display('Widget/food');

    }
}
