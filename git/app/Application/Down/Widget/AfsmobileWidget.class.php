<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/2
 * Time: 10:16
 */

namespace Down\Widget;

use Down\Controller\BaseController;

class AfsmobileWidget extends BaseController
{


    /**
     * 排行榜页面
     *
     */
    public function paihang()
    {
        $this->display('Widget/phb');
    }

    /**
     * 游戏子分类
     *
     */
    public function cateList()
    {
        $page_id = I('page_id');
        $cate = I('cate');
        $page_info = get_staticpage($page_id);

        $where = array('category_id' => $cate);
        $whereMap = array('map' => array('category_id' => $cate));
        //分页获取数据
        $row = 10;
        $count = D('Down')->where($where)->count();// 查询满足要求的总记录数
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
        $lists = D('Down')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists', $lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path'] . getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出
        $this->assign("info", $newGame);
        $this->assign("newGame", $newGame);
        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, $page_id)));
        $this->display('Widget/cateList');
    }

    /**
     * 相关下载
     *
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

        $tags = get_tags($c[0]['id'], 'down');
        $this->assign('tags', $tags);
        $this->assign("d", $c);
        $this->display('Widget/floatBox');

    }

    /**
     * 相关文章
     *
     */
    public function relateArticle($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $p = get_base_by_tag($id, 'Down', 'Document', 'product', false);
        $this->assign("lists", $p);
        $this->display('Widget/relateArticle');

    }

    /**
     * 相关版本
     *
     */
    public function relateVersion($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $p = get_base_by_tag($id, 'Down', 'Down', 'product', false);
        foreach ($p as $key => $val) {
            if ($val['id'] == $id) {
                unset($val);
            }
            $lists[] = $val;
        }
        $lists = array_filter($lists);
        $this->assign("lists", $lists);
        $this->display('Widget/relateVersion');

    }

    /**
     * 厂商
     *
     */
    public function downFactory($company_id)
    {
        if (!is_numeric($company_id) || $company_id == "0") {
            return;
        }
        $company = M("Company")->where(array("id" => $company_id))->find();
        $lists = M("Down")->alias("__DOWN")->where(array(
            "company_id" => $company_id,
            'data_type' => '1',
            'status' => '1'
        ))->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->limit("10")->field("*")->select();
        $this->assign("lists", $lists);
        $this->assign("company", $company);
        $this->display('Widget/downFactory');

    }

    /**
     * 猜你喜欢
     *
     * @param $tid
     * @param $tag 标签
     * @param $id id
     * @param $category 分类
     */
    public function guessLike($tid, $tag, $id, $category)
    {
        //$d  =   $this->get_product_data(implode(',',array_column($tag,'id')),'down','b.id !='.$id,'b.smallimg',10,false,'TagsMap','b.abet DESC');

        $tagsId = array_column($tag, 'id');
        $tagGame = array();
        if (!empty($tagsId)) {
            $where = '(a.tid = ' . implode(' OR a.tid = ', $tagsId) . ') AND ';
            $tagGame = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__PRODUCT_TAGS_MAP__ c ON b.id = c.did')->where($where . 'a.type="down" AND b.id !=' . $id . ' AND b.status = 1 AND b.category_id = ' . $category . ' AND c.type = "down"')->order('b.abet DESC')->group('c.tid')->limit(8)->select();

            $tagGameCount = count($tagGame);
            if ($tagGameCount < 6) {
                $tagGame1 = M('tags_map')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__PRODUCT_TAGS_MAP__ c ON b.id = c.did')->where($where . 'a.type="down" AND b.id !=' . $id . ' AND b.status = 1 AND c.type = "down"')->order('b.abet DESC')->group('c.tid')->limit(8)->select();
                $tagGame = array_filter(array_merge((array)$tagGame, (array)$tagGame1));
            }
        }

        $tagGameCount = count($tagGame);
        if ($tagGameCount < 6) {
            $tagGame1 = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->where('a.type = "down" AND b.id != ' . $id . ' AND b.status = 1 AND b.category_id = ' . $category)->order('b.abet DESC')->group('a.tid')->limit(8)->select();

            $tagGame = array_filter(array_merge((array)$tagGame, (array)$tagGame1));
        }

        $this->assign("lists", $tagGame);
        $this->display('Widget/guessLike');
    }

    /***
     * 格式化浏览次数
     *
     * @param $view 浏览次数
     */
    public function viewHot($view)
    {
        $count = ($view * 10) / 10000;
        echo round($count);
    }

    /***
     * 浏览趋势
     *
     * @param $view 浏览次数
     */
    public function viewTrend($view)
    {
        $count = ($view * 10) / 10000;
        $num = round($count);
        $className = "";
        if ($num <= 30 && $num > 20) {
            $className = "normal";
        } else {
            if ($num <= 20) {
                $className = "down";
            } else {
                $className = "up";
            }
        }
        echo $className;
    }

    /*
     * 排行榜
     *
     */
    public function rankList()
    {
        $id = '105641';
        $_LISTS_ = array();
        $rank = M('Down')->where("id='" . $id . "'")->find();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $rankID = $id;
        $ids = split(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[] = M('Down')->alias("a")->join("INNER JOIN __DOWN_DMAIN__ b ON a.id = b.id ")->field("a.id,a.title,a.description,a.smallimg,a.view,b.size")->where(array(
                "a.id" => $id,
                "a.status" => "1"
            ))->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }
        $this->assign("SEO", WidgetSEO(array('special', null, '14')));
        $this->assign("info", $rank);
        $this->assign("rankID", $rankID);
        $this->assign("ranks", array_slice($arrT, 0, 10));
        $this->display(T('Down@afsmobile/Detail/rank'));

    }

    /*
     * 单机游戏
     *
     */
    public function danjiList()
    {
        $commendedGame = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=1 AND position & 128 AND status=1')->order('view desc')->limit(20)->select();

        $newGame = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=1 AND status=1')->order('update_time desc')->limit(20)->select();
        $this->assign("commendedGame", $commendedGame);

        $this->assign("info", $newGame);
        $this->assign("newGame", $newGame);
        $this->assign("SEO", WidgetSEO(array('special', null, '16')));
        $this->display('Widget/danjiList');
    }

    /*
     * 网络游戏
     *
     */
    public function wangyouList()
    {
        $commendedGame = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=2 AND position & 256 AND status=1')->order('view desc')->limit(20)->select();

        $newGame = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=2 AND status=1')->order('update_time desc')->limit(20)->select();
        $this->assign("commendedGame", $commendedGame);
        $this->assign("info", $newGame);
        $this->assign("newGame", $newGame);
        $this->assign("SEO", WidgetSEO(array('special', null, '17')));
        $this->display('Widget/wangyouList');
    }

    /*
     * 排行榜
     *
     */
    public function rankConList($id)
    {
        $id = empty($id) ? '102394' : $id;
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = explode(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[] = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND tb1.id=' . $id)->order('create_time desc')->limit(5)->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }

        $this->assign("ranks", array_slice($arrT, 0, 10));
        $this->display('Widget/rankDetail');


    }

    /*
     * //修改 ：xiao 日期：2015/6/29/
     *
     */
    public function detailRelate($tid, $tag, $id)
    {
        $a = $this->get_product_data($tid, 'document', 1, 'b.update_time', 5);
        $tag && $d = $this->get_product_data(implode(',', array_column($tag, 'id')), 'down', 'b.id !=' . $id, 'b.smallimg', 5, false, 'TagsMap');
//		  $a=get_base_by_tag($id,'Down','Document','product',false);
//		  $d=get_base_by_tag($id,'Down','Down','product',false);
//		  $d_count=count($d);
//		  if($d_count<5){//如果匹配出的相关结果小于十个
//			  $need_count=5-$d_count;
//			  $d_t=get_base_by_tag($id,'Down','Down','tags',false);
//		  }
//		  $newDownArray=array_merge((array)$d,(array)$d_t);

        $this->assign("a", $a);
        $this->assign("d", $d);

        $this->display('Widget/detailRelate');
    }

    public function specialText($content)
    {
        $content = str_replace("|", "\n", $content);
        if (!empty($content)) {
            $arr = explode("\n", $content);
        }
        $array = array();
        foreach ($arr as $val) {
            $array[]['title'] = $val;
        }
        $this->assign("d", $array);
        $this->display('Widget/specialText');

    }

    /*
     * 下载详情TAG
     *
     */
    public function detailTags($id)
    {
        $tags = get_tags($id, 'down');
        $this->assign('tags', $tags);
        $this->display('Widget/detailTag');

    }

    /*
     * 下载相关礼包
     *
     */
    public function detailRelatePackage($id)
    {
        $a = get_base_by_tag($id, 'Down', 'Package', 'product', false);
        $this->assign("package", $a);
        $this->display('Widget/relatePackage');
    }

    /*
     * 下载相关专区
     *
     *
     */
    public function detailRelateBatch($id)
    {
        $a = get_base_by_tag($id, 'Down', 'Batch', 'product', true);
        $url = C('MOBILE_STATIC_URL') . getCPath($a['id'], 'batch');
        if ($a) {
            echo "<a class=\"m_btn\" href=\"" . $url . "\">进入专区</a>";
        }
    }

    /*
     * 根据(产品)标签获取数据的方法(秒杀90%以上根据(产品)标签获取的数据) Author : xiao
     *
     */
    public function get_product_data(
        $tid,
        $type,
        $where = 1,
        $field = 'b.create_time',
        $row = 8,
        $join = false,
        $table = 'ProductTagsMap',
        $order = 'b.id DESC'
    ) {
        if (empty($tid)) {
            return false;
        }
        $b = '__' . strtoupper($type) . '__';

        $field = $field ? 'b.id,b.title,' . $field : 'max(b.id) max,min(b.id) min,count(b.id) count';

        $model = M($table)->alias('a')->field($field)->join("$b b ON a.did = b.id");

        if ($join) {
            $model->join($join);
        }

        $tWhere = is_numeric($tid) ? "a.tid = $tid" : "a.tid IN($tid)";

        $list = $model->where("$tWhere AND a.type = '$type' AND b.status = 1 AND $where")->order($order)->group('b.id')->limit($row)->select();

        return $list;
    }

    /*
     * 软件频道页 作者：肖书成
     *
     */
    public function soft()
    {
        $position = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.language,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.status = 1 AND a.position & 512 AND a.category_rootid = 48')->order('a.view DESC')->limit(10)->select();
        $new = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.language,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.status = 1 AND a.category_rootid = 48')->order('a.update_time DESC')->limit(10)->select();
        $cate = M('DownCategory')->field('id,title,icon')->where('status = 1 AND pid = 48')->select();

        $this->assign(array(
            'position' => $position,
            'new' => $new,
            'cate' => $cate,
            'SEO' => WidgetSEO(array('special', null, '53'))
        ));

        $this->display('Widget/soft');
    }


    /*
     * 软件下载列表页 作者：肖书成
     *
     */
    public function softList()
    {
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
        $cate = I('cate');
        $sid = I('sid');

        //分页获取数据
        $row = 2;
        $sql = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.language,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.status = 1 AND a.category_id = ' . $cate)->order('a.id DESC')->buildSql();
        $count = M()->query("SELECT count('id') count FROM $sql str");// 查询满足要求的总记录数
        $count = $count[0]['count'];
        //是否返回总页数
        if (I('gettotal')) {
            if (empty($count)) {
                echo 1;
            } else {
                echo ceil($count / $row);
            }
            exit();
        }

        //分页数据处理
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理
        $str = ($p - 1) * $row > 0 ? ($p - 1) * $row : 0;


        $lists = M()->query("$sql limit $str,$row");
        $this->assign('lists', $lists);// 赋值数据集

        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        $this->assign("SEO", WidgetSEO(array('special', null, $sid)));

        $this->display('Widget/softList');
    }

    /*
     * 软件下载内容页面精品推荐
     */
    public function recommend($category_id){
        echo $category_id;
    }
}
