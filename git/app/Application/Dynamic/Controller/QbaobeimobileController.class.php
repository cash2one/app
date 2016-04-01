<?php
/**
 * 描述:  亲宝贝手机动态控制器
 * User: 肖书成
 * Date: 2015/6/27
 * Time: 14:39
 */
namespace Dynamic\Controller;

use Admin\Controller\QbaobeiWeixinController;

class QbaobeimobileController extends BaseController
{


    /**
     * API接口初始化
     *
     * @return void
     */
    protected function API_init()
    {
        //结果关闭trace
        C('SHOW_PAGE_TRACE', false);
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if ($referer) {
            $referer = parse_url($referer);
            $host = $referer['host'];
            if (in_array($host, $cors)) {
                header('Access-Control-Allow-Origin:http://' . $host);
            }
        }
    }


    //标签查询
    public function tag()
    {
        $keyword = remove_xss(I('keyword'));

        $tid = M('tags')->field('id,title,meta_title,keywords,description')->where(
            "`name` = '$keyword' AND status = 1"
        )->find();

        if ($tid) {
            $sql = M('TagsMap')->alias('a')->field('b.id,b.title,c.id cid,c.title cate')
                ->join('__DOCUMENT__ b ON a.did = b.id')
                ->join('__CATEGORY__ c ON b.category_id = c.id')
                ->join('__CATEGORY__ d ON c.pid = d.id')
                ->where("a.tid = " . $tid['id'] . " AND a.type = 'document' AND b.status = 1 ")->order(
                    'b.update_time DESC'
                )->buildSql();

            //数据统计
            $count = M()->query("SELECT count(id) count FROM $sql sub ");
            $count = $count[0]['count'];

            //分页参数处理
            $p = I('p');
            if (!is_numeric($p) || $p < 0) {
                $p = 1;
            }
            $row = 10;
            $str = ($p - 1) * $row;

            //列表数据
            $lists = M()->query("$sql limit $str,$row");

            //SEO
            $seo['title'] = $tid['meta_title'] ? $tid['meta_title'] : $tid['title'];
            $seo['keywords'] = $tid['keywords'] ? $tid['keywords'] : $tid['title'];
            $seo['description'] = $tid['description'] ? $tid['description'] : '亲亲宝贝网为大家提供“' . $tid['title'] . '”的相关文章，希望能为大家提供帮助。';

            //标题需要加前后缀
            if (C('SEO_STRING')) {
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seo['title'] = implode('_', $t);
            }

            //分页
            $path = C('MOBILE_STATIC_URL') . '/tag/' . $keyword . '/{page}.html';

            $Page = new \Think\Page($count, $row, '', false, $path); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->rollPage = 5;
            $Page->setConfig('prev', "上一页");
            $Page->setConfig('next', '下一页');
            $Page->setConfig('theme', '%UP_PAGE% %DOWN_PAGE%');

            $show = $Page->show(); // 分页显示输出
            $this->assign('page', $show); // 赋值分页输出

            $this->assign(
                array(
                    'info' => $tid,
                    'SEO' => $seo,
                    'count' => $count,
                    'lists' => $lists,
                )
            );
            $this->display(T('Home@qbaobeimobile/Widget/tag'));
        } else {
            $this->error('404');
        }
    }


    //下拉加载更多新闻内容
    public function loadNews()
    {
        $this->API_init();
        $callback = I('callback');
        $cate = I('cate');
        if (empty($cate)) {
            return;
        }
        $start = I('get.page') * 10;
        $ids = D('Category')->getAllChildrenId($cate);
        $news = M('Document')->alias("a")->join("INNER JOIN __DOCUMENT_BAIKE__ b ON a.id = b.id")->where(
            "category_id IN (" . $ids . ") AND status = 1"
        )->field("a.id,category_id,intro,title,description")->order('update_time desc')->limit("$start,10")->select();
        foreach ($news as $key => $val) {
            $arr[] = array(
                'id' => $val['id'],
                'title' => $val['title'],
                'description' => empty($val['description']) ? $val['intro'] : $val['description'],
                'url' => staticUrlMobile('detail', $val['id'], 'Document'),
            );
        }
        echo $callback . "(" . json_encode($arr) . ")";
    }

    /************************************************手机版2期 首页动态加载*******************************/
    /**
     * 描述：首页动态加载数据
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function loadFirstNews()
    {
        $this->API_init();
        $callback = I('callback');
        $row = 6;
        $start = I('get.page') * $row;
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
        $rs = M('document')->field('id,title,smallimg,previewimg,description')->where($where)->limit(
            $start,
            $row
        )->order('update_time DESC')->select(); //获取首页热点数据
        $c = count($rs);
        unset($where);
        if ($c > 0) {
            foreach ($rs as $key => $val) {
                $rs[$key]['images'] = 0;
                $rs[$key]['url'] = staticUrlMobile('detail', $val['id'], 'Document');
                $rs[$key]['smallimg'] = get_cover($val['smallimg'], 'path', 1, 200, 100);
                if (!empty($val['previewimg'])) {
                    $crs = explode(",", $val['previewimg']);
                    if (count($crs) > 2) {
                        $rs[$key]['images'] = 1;
                        $rs[$key]['smallimg1'] = get_cover($crs[0], 'path', 1, 200, 100);
                        $rs[$key]['smallimg2'] = get_cover($crs[1], 'path', 1, 200, 100);
                        $rs[$key]['smallimg3'] = get_cover($crs[2], 'path', 1, 200, 100);
                    }
                }
            }
        }
        echo $callback . "(" . json_encode($rs) . ")";
    }
    /************************************************手机版2期 首页动态加载END*******************************/

    /************************************************手机版2期  标签页动态加载******************************/
    /**
     * 描述：动态加载标签文章
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function loadTagsNews()
    {
        $this->API_init();
        $callback = I('callback');
        $tid = I('tid');
        if (!is_numeric($tid)) {
            return;
        }
        $flag = I('flag');
        if (empty($flag) || $flag > 3 || $flag < 0) {
            $flag = 'all';
        }
        $page = I('page');
        $rs = $this->getTagListData($tid, $flag, $page);
        echo $callback . "(" . json_encode($rs) . ")";
    }

    /**
     * 描述：获取标签数据
     *
     * @param $tid
     * @param $flag
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getTagListData($tid, $flag, $page)
    {
        if (!is_numeric($tid)) {
            return false;
        }
        $row = 6;
        $start = $page * $row;
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
        } else {
            if ($flag == 2) {
                $cate = D('Category')->getAllChildrenId('1307'); //所有视频分类
                if (!empty($cate)) {
                    $where['a.category_id'] = array('in', $cate);
                }
            } else {
                if ($flag == 3) {
                    $food_cate1 = D('Category')->getAllChildrenId('690'); //食谱分类下的妈妈食谱
                    $food_cate2 = D('Category')->getAllChildrenId('691'); //食谱分类下的宝宝食谱
                    $food_cate1 = empty($food_cate1) ? '0' : $food_cate1;
                    $food_cate2 = empty($food_cate2) ? '0' : $food_cate2;
                    $cate = $food_cate1 . ',' . $food_cate2;
                    if (!empty($cate)) {
                        $where['a.category_id'] = array('in', $cate);
                    }
                }
            }
        }
        $rs = M('document')->alias('a')->join('__TAGS_MAP__ b ON b.did= a.id')->field(
            'a.id as id,a.title as title,a.smallimg as smallimg ,a.previewimg as previewimg,a.description as description'
        )->where($where)->limit($start, $row)->order('a.update_time DESC')->select(); //获取首页热点数据
        $c = count($rs);
        unset($where);
        if ($c > 0) {
            foreach ($rs as $key => $val) {
                $rs[$key]['images'] = 0;
                $rs[$key]['url'] = staticUrlMobile('detail', $val['id'], 'Document');
                $rs[$key]['smallimg'] = get_cover($val['smallimg'], 'path', 1, 200, 100);
                if (!empty($val['previewimg'])) {
                    $crs = explode(",", $val['previewimg']);
                    if (count($crs) > 2) {
                        $rs[$key]['images'] = 1;
                        $rs[$key]['smallimg1'] = get_cover($crs[0], 'path', 1, 200, 100);
                        $rs[$key]['smallimg2'] = get_cover($crs[1], 'path', 1, 200, 100);
                        $rs[$key]['smallimg3'] = get_cover($crs[2], 'path', 1, 200, 100);
                    }
                }
            }
        }
        return $rs;
    }
    /************************************************手机版2期  标签页动态加载END******************************/
    /************************************************手机版2期  图片动态加载**********************************/
    /**
     * 描述：动态获取图片信息
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function loadPicInfos()
    {
        $this->API_init();
        $callback = I('callback');
        $cid = I('cid');
        if (!is_numeric($cid)) {
            return;
        }
        $page = I('page');
        $row = 10;
        $start = $page * $row;
        $cate_name = D('GalleryCategory')->where("id='" . $cid . "'")->getField('title');
        $where = array();
        $where['status'] = 1;
        $rs = D('GalleryCategory')->getAllChildrenId($cid); //获取分类及分类所有id
        if (!empty($rs)) {
            $where['category_id'] = array('in', $rs);
        }
        $list = M('Gallery')->field('id,title,smallimg,update_time,view')->where($where)->limit($start, $row)->select(
        ); //获取图数据
        if (!empty($list)) {
            foreach ($list as $key => $val) {
                $list[$key]['url'] = str_replace(
                    C('STATIC_URL'),
                    C('MOBILE_STATIC_URL'),
                    staticUrlMobile('detail', $val['id'], 'Gallery')
                );
                $list[$key]['smallimg'] = get_cover($val['smallimg'], 'path', 1, 160, 120);
                $list[$key]['time'] = date('m-d', $val['update_time']);
                $list[$key]['cateName'] = $cate_name; //分类名称
            }
        }
        echo $callback . "(" . json_encode($list) . ")";
    }
    /************************************************手机版2期  图片动态加载结束******************************/
    /************************************************手机版2期  详情页相关文章动态加载************************/
    public function relateDoc()
    {
        $this->API_init();
        $callback = I('callback');
        $id = I('id');
        $cid = I('cid');
        $ids = I('ids');
        $tags = I('tags');
        if (!is_numeric($id) || !is_numeric($cid)) {
            return;
        }
        $page = I('page');
        $row = 10;
        $start = $page * $row;
        $where = ' 1=1 ';
        if (!empty($ids)) {
            $where = 'b.id NOT IN(' . $ids . ') ';
        }
        $where .= ' AND b.id != ' . $id . ' AND (b.category_id = ' . $cid . ' ' . (!empty($tags) ? ' OR a.tid IN(' . $tags . ')' : '') . ') AND b.smallimg>0 AND b.status = 1 AND a.type = \'document\'';
        //获取相关文章结果
        $rs = M('TagsMap')
            ->alias('a')->field('b.id,b.title,b.description,b.smallimg,b.previewimg')
            ->join('__DOCUMENT__ b ON a.did = b.id')
            ->where($where)
            ->order('b.update_time desc')
            ->limit($start, $row)
            ->group('b.id')
            ->select();
        if (!empty($rs)) {
            foreach ($rs as $key => $val) {
                $rs[$key]['images'] = 0;
                $rs[$key]['url'] = staticUrlMobile('detail', $val['id'], 'Document');
                $rs[$key]['smallimg'] = get_cover($val['smallimg'], 'path', 1, 200, 100);
                if (!empty($val['previewimg'])) {
                    $crs = explode(",", $val['previewimg']);
                    if (count($crs) > 2) {
                        $rs[$key]['images'] = 1;
                        $rs[$key]['smallimg1'] = get_cover($crs[0], 'path', 1, 200, 100);
                        $rs[$key]['smallimg2'] = get_cover($crs[1], 'path', 1, 200, 100);
                        $rs[$key]['smallimg3'] = get_cover($crs[2], 'path', 1, 200, 100);
                    }
                }
            }
        }
        echo $callback . "(" . json_encode($rs) . ")";
    }
    /************************************************手机版2期  详情页相关文章动态加载END********************/
    /**
     * Description：    视频库
     * Author:         Jeffrey Lau
     * Modify Time:    2016-1-6 09:00:37
     */

    public function loadVideo()
    {
        $this->API_init();
        $callback = I('callback');
        $cate = I('get.cate');
        $order = I('get.order');
        $pcate = M("Category")->where(array("pid" => "1307", "status" => "1"))->select();
        $start = I('get.page') * 8;
        $category = M("Category")->where(array("id" => $cate, "status" => "1"))->find();
        $pid = $category['pid'];
        $orderCondition = $order == "0" ? "view desc" : "create_time desc";
        if ($pid == "1307" && $pid != "0") { //二级分类
            $childcate = M("Category")->where(array("pid" => $cate, "status" => "1"))->select();
            $ids = D('Category')->getAllChildrenId($cate);
            $lists = M('Document')->where("category_id IN(" . $ids . ") AND status=1")->order($orderCondition)->limit(
                "$start,8"
            )->select();

            $isSecond = "1";
            $pid = $cate;
        } else {
            if ($cate == "1307" && $pid == "0") { //一级分类，也就是全部都选择
                $childcate = "";
                $lists = M('Document')->where("category_rootid = '$cate' AND status=1")->order($orderCondition)->limit(
                    "$start,8"
                )->select();
            } else {
                $childcate = M("Category")->where(array("pid" => $pid, "status" => "1"))->select();
                $lists = M('Document')->where("category_id = '$cate' AND status=1")->order($orderCondition)->limit(
                    "$start,8"
                )->select();
            }
        }

        foreach ($lists as $key => $val) {
            $arr[] = array(
                'id' => $val['id'],
                'title' => $val['title'],
                'img' => get_cover($val['smallimg'], 'path'),
                'time' => date("h:i", $val['update_time']),
                'ago' => formatTime($val['create_time']),
                'url' => staticUrlMobile('detail', $val['id'], 'Document'),
            );
        }
        echo $callback . "(" . json_encode($arr) . ")";

    }


    /**
     * 赞
     * author:liupan
     *
     * @param integer $id 数据ID
     * @return void
     */
    public function dig()
    {
        $this->API_init();
        $callback = I('callback');
        $id = I('id');
        if (!is_numeric($id)) {
            return;
        }
        $m = M("Document")->where(array("id" => $id))->setInc('abet');
        echo $callback ? $callback . '(' . json_encode("1") . ');' : json_encode("1");
    }

    /**
     * 描述:文章分类数据下拉加载
     * 作者:肖书成
     * 时间:2016-1-9
     */
    public function cate_list()
    {
        $cate = I('cate');
        $star = I('star');
        $callback = I('callback');


        //缓存
        $name = 'cate' . $cate . '_' . $star;

        $lists = S($name);
        if ($lists) {
            //数据返回
            echo $callback ? $callback . '(' . $lists . ');' : $lists;
            exit;
        }


        //验证参数
        if (!is_numeric($cate) || !is_numeric($star) || $star <= 0) {
            $this->API_false();
        }

        $info = M('Category')->where('status = 1 AND id = ' . $cate)->find();
        if (empty($info)) {
            $this->API_false();
        }

        //判断分类是否含有下级分类
        $cate_child = M('Category')->where('status = 1 AND pid = ' . $cate)->select();

        //数据条件
        $where = 'status = 1';
        if ($cate_child) {
            $where .= ' AND (category_id = ' . $cate . ' OR category_id = ' . implode(
                ' OR category_id = ',
                array_column($cate_child, 'id')
            ) . ')';
        } else {
            $where .= ' AND category_id = ' . $cate;
        }

        //列表数据
        $lists = M('Document')->field('id,title,description,smallimg,previewimg,category_id')->where($where)->limit(
            $star,
            10
        )->order('id DESC')->select();

        if ($lists) {
            foreach ($lists as $k => &$v) {
                $v['url'] = staticUrlMobile('detail', $v['id'], 'Document');
                $v['img'] = get_cover($v['smallimg'], 'path', 1, 240, 200);
                if ($info['rootid'] != '685' && $v['previewimg']) {
                    $imgs = explode(',', $v['previewimg']);
                    if (count($imgs) >= 3) {
                        for ($i = 0; $i < 3; $i++) {
                            $v['imgs'][] = get_cover($imgs[$i], 'path', 1, 240, 200);
                        }
                        $v['three'] = true;
                    }
                }
            }
            unset($v);

            $lists = json_encode($lists);

            S($name, $lists, 120);
        } else {
            $lists = json_encode($lists);
            S($name, $lists, 5);
        }


        //数据返回
        echo $callback ? $callback . '(' . $lists . ');' : $lists;
    }

    /**
     * 作者:肖书成
     * 描述:分类的推荐部分
     * 日期:2016-1-11
     */
    public function cate_position()
    {
        $cate = I('cate');
        $star = I('star');
        $callback = I('callback');

        //缓存
        $name = 'cate_position_' . $cate . '_' . $star;

        $lists = S($name);
        if ($lists) {
            //数据返回
            echo $callback ? $callback . '(' . $lists . ');' : $lists;
            exit;
        }


        //验证参数
        if (!is_numeric($cate) || !is_numeric($star) || $star <= 0) {
            $this->API_false();
        }

        $info = M('Category')->field('id,title')->where('status = 1 AND id = ' . $cate)->find();
        if (empty($info)) {
            $this->API_false();
        }

        //分类查询
        $cate_child = M('Category')->field('id,title')->where('status = 1 AND pid = ' . $info['id'])->select();

        $where = 'position & 8 AND status = 1 ';
        if ($cate_child) {
            $where .= 'AND (category_id = ' . $info['id'] . ' OR category_id = ' . implode(
                ' OR category_id = ',
                array_column($cate_child, 'id')
            ) . ')';
        } else {
            $where .= 'AND category_id = ' . $info['id'];
        }

        $lists = M('Document')->field('id,title,description,smallimg,previewimg')->where($where)->limit(
            $star,
            10
        )->order('update_time DESC')->select();

        if ($lists) {
            foreach ($lists as $k => &$v) {
                $v['url'] = staticUrlMobile('detail', $v['id'], 'Document');
                $v['img'] = get_cover($v['smallimg'], 'path', 1, 200, 100);
                if ($info['rootid'] != '685' && $v['previewimg']) {
                    $imgs = explode(',', $v['previewimg']);
                    if (count($imgs) >= 3) {
                        for ($i = 0; $i < 3; $i++) {
                            $v['imgs'][] = get_cover($imgs[$i], 'path', 1, 200, 100);
                        }
                        $v['three'] = true;
                    }
                }
            }
            unset($v);

            $lists = json_encode($lists);

            S($name, $lists, 120);
        } else {
            $lists = json_encode($lists);
            S($name, $lists, 5);
        }


        //数据返回
        echo $callback ? $callback . '(' . $lists . ');' : $lists;
    }

    /**
     * 描述：获取微信二维码html代码
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function weiXin()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            return;
        }
        $tag_name = I('tag_name');
        $tag_href = I('tag_href');
        $callback = I('callback');
        $this->API_init();
        $ext = '.html';
        $file_name = C('MOBILE_STATIC_ROOT') . '/weixin/' . $id . $ext;
        if (file_exists($file_name)) {
            $html = file_get_contents($file_name);
            $html = str_replace(array('{tag_name}', '{tag_href}'), array($tag_name, $tag_href), $html);
            $data['html'] = $html;
            $lists = json_encode($data);
            //数据返回
            echo $callback ? $callback . '(' . $lists . ');' : $lists;
        }
    }

    /**
     * 描述：亲宝贝手机版添加阅读
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function view()
    {
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $id = I('id');
        $model = ucfirst(I('model'));
        $model = empty($model) ? 'Document' : $model;
        if (!is_numeric($id)) {
            return;
        }
        $session_name = "VIEW_NUMS_" . $id;
        if (S($session_name)) {
            $views = S($session_name) + 1;
            S($session_name, $views);
        } else {
            S($session_name, 1);
        }
        //每3次插入一次数据库
        if (S($session_name) >=3) {
            $m = M($model);
            $rs = $m->where('id=' . $id)->setInc('view', S($session_name));
            if ($rs) {
                S($session_name, null);
            }
            echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
        }
    }
}
