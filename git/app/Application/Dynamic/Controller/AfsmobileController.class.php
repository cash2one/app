<?php
// +----------------------------------------------------------------------
// | afs动态访问控制类
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class AfsmobileController extends BaseController
{

    /***
     * 排行榜详情页面
     *
     * @param $name 排行榜名字
     */
    public function rankDetail($name)
    {
        $name = I('name');
        if (!$name) {
            $this->error('排行榜名字不能为空！');
        }
        $flag = "top/" . $name;
        $flag = rtrim($flag, "/") . "/index";
        $rank = M('Down')->where("path_detail='" . $flag . "'")->find();
        if (empty($rank)) {
            header("HTTP/1.0 404 Not Found");
            exit();
        }

        $id = empty($rank['id']) ? '102394' : $rank['id'];
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $ids = explode(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[] = M('Down')->where("status=1 AND `id`=$id")->select();
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }
        if ($arrT) {
            $this->assign(ranks, array_slice($arrT, 0, 10));
        }
        $this->assign("info", $rank);
        //SEO
        $this->assign("SEO", WidgetSEO(array('detail', 'Down', $id)));
        //模板选择
        $this->display(T('Down@afsmobile/Detail/paihang'));

    }

    /**
     * 加载排行榜数据
     *
     */
    public function loadRankData()
    {
        $id = I('id');
        $page = I('page');
        $page = empty($page) ? '1' : $page;
        $callback = I('callback');
        if (empty($id)) {
            return;
        }
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where(array("id" => $id))->getField('soft_id');
        $ids = explode(",", $softID);
        $offset = ($page - 1) * 10;
        $ids = array_slice($ids, $offset, 10);
        foreach ($ids as $id) {
            $_LISTS_[] = M('Down')->alias("a")->join("INNER JOIN __DOWN_DMAIN__ b ON a.id = b.id ")->field("a.id,a.title,a.description,a.smallimg,a.view,b.size")->where(array(
                "a.id" => $id,
                "a.status" => "1"
            ))->find();
        }
        $i = ($page - 1) * 10;
        $arr = array();
        foreach ($_LISTS_ as $key => $val) {
            $i++;
            $arr[] = array(
                'id' => $val['id'],
                'title' => $val['title'],
                'key' => $i,
                'class' => $this->viewTrend($val['view']),
                'view' => $this->viewHot($val['view']) . "万",
                'description' => $val['description'],
                'images' => get_cover($val['smallimg'], 'path'),
                'url' => staticUrlMobile('detail', $val['id'], 'Down'),
                'size' => $val['size'],
            );
        }
        echo $callback . "(" . json_encode($arr) . ")";
    }

    /***
     * 格式化浏览次数
     *
     * @param $view 浏览次数
     * @return string
     */
    private function viewHot($view)
    {
        $count = ($view * 10) / 10000;
        return round($count);
    }

    /***
     * 浏览趋势
     *
     * @param $view 浏览次数
     * @return string
     */
    private function viewTrend($view)
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
        return $className;
    }

    /**
     * 神马Json数据获取
     *
     */
    public function getSMJson()
    {
        $key = I('key');
        $url = I('url');
        $title = I('title');
        $callback = I('callback');//http://rec.m.sm.cn/?app=related_query&type=json&query=".$key."&url=".$url."&title=".$title."&from=wh10030
        $key = urlencode($key);
        $url = urlencode($url);
        $title = urlencode($title);
        $requestUrl = "http://rec.m.sm.cn/?app=related_query&type=json&query=" . $key . "&url=" . $url . "&title=" . $title . "&from=wa000098";
        $json = file_get_contents($requestUrl);
        echo $callback ? $callback . '(' . $json . ');' : $json;
    }

    /*
     *AJAX加载更多游戏
     *
     */
    public function ajaxNewGame()
    {
        $callback = I('callback');
        $start = I('get.page') * 20;
        $type = I('get.type');//单机1，网游2
        if ($type == "1") {
            $game = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=1 AND status = 1')->order('update_time desc')->limit("$start,20")->select();
        } else {
            $game = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=2 AND status = 1')->order('update_time desc')->limit("$start,20")->select();
        }

        foreach ($game as $key => $val) {
            $arr[] = array(
                'id' => $val['id'],
                'title' => $val['title'],
                'category' => getCateName($val['id'], 'Down'),
                'language' => getLanguage($val['id']),
                'images' => get_cover($val['smallimg'], 'path'),
                'detail' => staticUrlMobile('detail', $val['id'], 'Down'),
                'size' => $val['size'],
            );
        }
        echo $callback . "(" . json_encode($arr) . ")";
    }

    /*
       *AJAX加载更多推荐游戏
       *
       */
    public function ajaxCommendedGame()
    {
        $callback = I('callback');
        $start = I('get.page') * 20;
        $type = I('get.type');//单机1，网游2
        if ($type == "1") {
            $game = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=1 AND position & 128 AND status=1')->order('view desc')->limit("$start,20")->select();
        } else {
            $game = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field()->where('tb1.id=tb2.id AND category_rootid=2 AND position & 256 AND status=1')->order('view desc')->limit("$start,20")->select();
        }
        foreach ($game as $key => $val) {
            $arr[] = array(
                'id' => $val['id'],
                'title' => $val['title'],
                'category' => getCateName($val['id'], 'Down'),
                'language' => getLanguage($val['id']),
                'images' => get_cover($val['smallimg'], 'path'),
                'detail' => staticUrlMobile('detail', $val['id'], 'Down'),
                'size' => $val['size'],
            );
        }
        echo $callback . "(" . json_encode($arr) . ")";
    }


    /**
     * 标签页面
     *
     * 作者:未知（可能是刘盼）
     * 描述:标签页
     * 修改者:肖书成、谭坚
     * @return void
     */
    public function tags()
    {
        $name = strip_tags(I('name'));
        $info = M('Tags')->where('status=1 AND name="%s"', $name)->field('id,name,title,sub_title,category,meta_title,keywords,description,icon,img')->find();
        //软件标签扩展 作者：肖书成
        /************************/
        if ((int)$info['category'] == 6) {
            $this->softTags($info);
            exit();
        }
        /************************/
        $id = $info['id'];
        if (!is_numeric($id)) {
            $this->error('页面不存在！');
        }

//        $p= $_GET['p'] ; //设置P参数让分页类获取

        //排序
        //计算总数 modify by 谭坚   2015-7-27（优化sql语句）
        $count = M('Down')->alias('a')->join('INNER JOIN __TAGS_MAP__ b on a.id=b.did ')->where("b.type='down' AND b.tid='$id'")->count('a.id');
        //分页获取数据
        $row = 20;

//        if (!is_numeric($p) || $p<0 ) $p = 1;
//        if ($p > $count ) $p = $count; //容错处理
//        $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;

        //modify by 谭坚   2015-7-27（优化sql语句） 肖书成 修改于2015-9-22 (优化SQL语句)
        $lists = M('Down')->alias('d')->field('d.id,d.title,d.smallimg,d.abet,d.view,d.description,d.category_id,d.update_time,m.version,m.licence,m.size,c.title cate')->join('__DOWN_DMAIN__ m on m.id=d.id')->join('__DOWN_CATEGORY__ c ON d.category_id = c.id')->join('__TAGS_MAP__ b on d.id=b.did')->where("b.type='down' AND b.tid='$id'")->order('b.sort ASC')->limit($row)->select();
        $this->assign('lists', $lists);

//        //分页路径
//        $path = '/tag/'.$name.'/'.'{page}.html';
//        //分页
//        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
//        $Page->setConfig('prev','上一页');
//        $Page->setConfig('next','下一页');
//        $Page->setConfig('first','首页');
//        $Page->setConfig('last','尾页');
//        $Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
//        $show       = $Page->show();// 分页显示输出
//        $this->assign('page',$show);// 赋值分页输出

        //SEO 按照指定规则
        $title = $info['title'];
        $seo = array(
            'title' => $info['meta_title'],
            'keywords' => $title . '手游,安卓' . $title . '游戏',
            'description' => '安粉丝手游网为您提供' . $title . '相关游戏下载,最好玩的' . $title . '类安卓游戏',
        );

        $this->assign(array(
            "SEO" => $seo,
            "info" => $info,
            "count" => $count
        ));

        //模板选择
        $this->display(T('Down@afsmobile/Widget/tagList'));
    }

    /**
     * 软件标签
     *
     * 作者：肖书成
     * 描述：软件分类 标签页
     * 日期：2015、6、29
     * @param array $info
     */
    private function softTags($info)
    {
        $count = M('TagsMap')->alias('a')->join('__DOWN__ b ON a.did = b.id')->where('a.tid = ' . $info['id'] . ' AND a.type = "down" AND b.status = 1')->order('a.sort')->count('b.id');

        $list = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.update_time,c.title cate,d.version,d.size,d.language')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_CATEGORY__ c ON b.category_id = c.id')->join('__DOWN_DSOFT__ d ON a.did = d.id')->where('a.tid = ' . $info['id'] . ' AND a.type = "down" AND b.status = 1')->order('a.sort')->limit(20)->select();

//        //分页获取数据
//        $p= $_GET['p'] ; //设置P参数让分页类获取
//        $row = 10;
//        if (!is_numeric($p) || $p<0 ) $p = 1;
//        if ($p > $count ) $p = $count; //容错处理
//        $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
//
//        $list   = M()->query("$sql LIMIT $lr,$row");
//
//        //分页路径
//        $path = '/tag/'.$info['name'].'/'.'{page}.html';
//
//        //分页
//        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
//        $Page->setConfig('prev','上一页');
//        $Page->setConfig('next','下一页');
//        $Page->setConfig('first','首页');
//        $Page->setConfig('last','尾页');
//        $Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
//        $show       = $Page->show();// 分页显示输出
//        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $info['meta_title'];
        $seo['keywords'] = $info['keywords'];
        $seo['description'] = $info['description'];

        //标题需要加前后缀
        if (C('SEO_STRING')) {
            $t = array();
            $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['seo_title'];
            $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
            ksort($t);
            $seo['seo_title'] = implode(' - ', $t);
        }

        $this->assign(array(
            'SEO' => $seo,
            'count' => $count,
            'info' => $info,
            'lists' => $list
        ));

        //模板选择
        $this->display(T('Down@afsmobile/Widget/softTag'));
    }

    /**
     * 作者:肖书成
     * 描述:标签下拉加载更多 （以前是 软件标签 点击加载更多）
     */
    public function API_softTags()
    {
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $key = I('key');
        $num = I('num');
        if (!is_numeric($num) || $num < 1) {
            return false;
        }
        if (!is_numeric($key) || $key < 1) {
            return false;
        }
        $info = M('Tags')->where("id = $key AND status = 1")->find();
        if (empty($info)) {
            return false;
        }

        $info['category'] == 6 ? $dTable = '__DOWN_DSOFT__' : $dTable = '__DOWN_DMAIN__';

        $data = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.update_time,c.title cate,d.version,d.size,d.language')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_CATEGORY__ c ON b.category_id = c.id')->join("$dTable d ON a.did = d.id")->where('a.tid = ' . $key . ' AND a.type = "down" AND b.status = 1')->order('a.sort ASC')->limit($num, 10)->select();

        if ($data) {
            foreach ($data as $k => &$v) {
                $v['url'] = staticUrlMobile('detail', $v['id'], 'Down');
                $v['img'] = get_cover($v['smallimg'], 'path');
                $v['language'] = showText($v['language'], 'language', false, 'down');
                $v['update_time'] = date('Y-m-d', $v['update_time']);
            }
            unset($v);
        }

        echo $callback ? $callback . '(' . json_encode($data) . ')' : json_encode($data);
    }


    /**
     * 搜索结果
     *
     * @return void
     */
    public function search()
    {
        $keyword = I('keyword');
        $type = ucfirst(strtolower($type));
        //if (!$keyword) $this->error('请输入关键词！');
        if (!empty($keyword)) {
            //结果页面
            $this->assign('keyword', $keyword);// 赋值关键词
            $where = array(            //分页获取数据
                'map' => array('title' => array('like', '%' . $keyword . '%'))
            );
            $row = 10;
            $count = D('Down')->where($where)->count();// 查询满足要求的总记录数
            $this->assign('count', $count);

            $p = intval(I('p'));
            if (!is_numeric($p) || $p < 0) {
                $p = 1;
            }
            if ($p > $count) {
                $p = $count;
            } //容错处理

            $lists = D('Down')->page($p, $row)->listsWhere($where, true);


            // 赋值数据集
            $this->assign('lists', $lists);

            $path = '/search.html?keyword=' . $keyword . '&p={page}';
            $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('first', '首页');
            $Page->setConfig('end', '尾页');
            $Page->setConfig('prev', "上一页");
            $Page->setConfig('next', '下一页');
            $Page->setConfig('theme', '%UP_PAGE% %DOWN_PAGE%');
            $show = $Page->show();// 分页显示输出
            $this->assign('page', $show);// 赋值分页输出

            //SEO
            $seo['title'] = C('WEB_SITE_TITLE') . '-' . $keyword . '搜索结果';
            $seo['keywords'] = C('WEB_SITE_KEYWORD') . ',' . $keyword;
            $seo['description'] = C('WEB_SITE_DESCRIPTION') . ' ' . $keyword . '搜索结果';
            $this->assign("SEO", $seo);
            //模板选择
            $this->display(T('Home@afsmobile/Search/search_list'));
        } else {
            //SEO
            $this->assign("SEO", WidgetSEO(array('index'), 'afsmobile'));
            //初始页面
            $this->display(T('Home@afsmobile/Search/search'));
        }


    }

    /*
     * 软件频道的点击加载更多
     *
     */
    public function API_soft()
    {
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $num = (int)I('num');
        $key = (int)I('key');
        if (!in_array($key, array(1, 2))) {
            return false;
        }
        if ($num < 0) {
            return false;
        }
        if ($key == 1) {
            $data = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.language,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.status = 1 AND a.position & 512 AND a.category_rootid = 48')->order('a.view DESC')->limit($num, 10)->select();
        } else {
            $data = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.language,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.status = 1 AND a.category_rootid = 48')->order('a.update_time DESC')->limit($num, 10)->select();
        }

        if ($data) {
            foreach ($data as $k => &$v) {
                $v['url'] = staticUrlMobile('detail', $v['id'], 'Down');
                $v['img'] = get_cover($v['smallimg'], 'path');
                $v['language'] = showText($v['language'], 'language', false, 'down');
            }
            unset($v);
        }

        echo $callback ? $callback . '(' . json_encode($data) . ')' : json_encode($data);
    }
}
