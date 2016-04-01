<?php
namespace Down\Widget;

use Down\Controller\BaseController;
use Script\Model\Dede96uModel;

class Jf96umobileWidget extends BaseController
{
    /**
     * 作者:ganweili
     * 时间:2015/11/17
     * 描述:M.下载栏目页也就是下载内容页里面的礼包和相关下载
     */
    public function downpack($id)
    {
        $aid = "";
        $tid = M('tags_map')->where("did='" . $id ."' AND type='down'")->limit(6)->getField('tid', true);
        foreach ($tid as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }

        $aid = substr($aid, 0, -1);
        $ztid = empty($aid) ? null : "tid in (" . $aid . ") and ";

        //礼包
        $did = M('tags_map')->where($ztid . "type= 'package'  ")->limit(2)->getField('did', true);
        foreach ($did as $k => $v) {
            $laid .= "'" . "$v" . "',";
        }

        $laid = substr($laid, 0, -1);
        $laid = empty($laid) ? null : "id in (" . $laid . ") and ";

        if (!empty($laid)) {
            $pdata = M('package')->where($laid . " status=1 and category_id=1 ")->limit(2)
                ->select();

        }
        //相关下载
        if (!empty($tid)) {
            $where['t.tid'] = array('in',$tid);
            $where['t.did'] = array('neq', $id);
            $where['a.status'] = 1;
            $where['t.type'] = 'down';
            $ddata = M('down')->alias('a')->join('__TAGS_MAP__ t on t.did=a.id')->where($where)
                ->field('a.id,a.title')->limit(5)
                ->select();
        }
        $this->assign('ddata', $ddata);
        $this->assign('pdata', $pdata);
        $this->display('Widget/downpack');
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/20
     * 描述:根据下载id查找数据  猜你喜欢
     */
    public function downtag($aid)
    {
        $ptag = M('product_tags_map')->alias('a')->join('__PRODUCT_TAGS__ b')->where("a.did='$aid' and a.tid=b.id and b.status=1 and a.type='down'")->field('b.id as id,b.title as title')->select();
        foreach ($ptag as $k => $v) {
            $key = $v['id'];
            $data[$key] = M('product_tags_map')->alias('a')->join('__DOWN__  b ')->where("a.tid=$key " . " and  a.did=b.id  and b.status=1 and b.id <>$aid and a.type='down'")->limit(8)->field('b.id as id,b.title as title,b.smallimg as smallimg')->select();

            $wdata[$key] = M('product_tags_map')->alias('a')->join('__FEATURE__  b ')->where("a.tid=$key " . " and  a.did=b.id  and  b.interface=1 and b.pid=0 and b.enabled=1 and a.type='feature' and b.pid='0'")->limit(1)->field('b.id as id,b.title as title,b.icon as icon,b.description as description')->select();
            if (empty($data[$key]) && empty($wdata[$key])) {
                unset($data[$key], $wdata[$key], $ptag[$k]);
            }
        }
        $this->assign('count', count($ptag));
        $this->assign('ptag', $ptag);
        $this->assign('wdata', $wdata);
        $this->assign('data', $data);
        $this->display('Widget/downtag');
    }

    /**
     *
     * 作者:ganweili
     * 时间:2015/11/20
     * 描述:根据下载id查找数据  猜你喜欢
     */
    public function downtags($aid)
    {
        $ptag = M('product_tags_map')->alias('a')->join('__PRODUCT_TAGS__ b')->where("a.did='$aid' and a.tid=b.id and b.status=1 and a.type='down' and b.category = '4'")->field('b.id as id,b.title as title')->select();
        foreach ($ptag as $k => $v) {
            $key = $v['id'];
            $data[$key] = M('product_tags_map')->alias('a')->join('__DOWN__  b ')->where("a.tid=$key " . " and  a.did=b.id  and b.status=1 and b.id <>$aid and a.type='down'")->limit(8)->field('b.id as id,b.title as title,b.smallimg as smallimg')->select();

            $wdata[$key] = M('product_tags_map')->alias('a')->join('__FEATURE__  b ')->where("a.tid=$key " . " and  a.did=b.id  and  b.interface=1 and b.pid=0 and b.enabled=1 and a.type='feature' and b.pid='0'")->limit(1)->field('b.id as id,b.title as title,b.icon as icon,b.description as description')->select();
            if (empty($data[$key]) && empty($wdata[$key])) {
                unset($data[$key], $wdata[$key], $ptag[$k]);
            }
        }
        $this->assign('count', count($ptag));
        $this->assign('ptag', $ptag);
        $this->assign('wdata', $wdata);
        $this->assign('data', $data);
        $this->display('Widget/downtag');
    }

    /**
     * 作者:ganweili
     * 时间:2015/12/22
     * 描述:相关厂商
     */

    public function downcs($id)
    {
        $csid = M('down')->alias('a')->join('__DOWN_DMAIN__ b')->where("a.id='$id' and  a.id=b.id and  a.status=1")->getField('company_id');

        if (empty($csid)) {
            $csid = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(" a.id='$id' and    a.id=b.id and  a.status=1")->getField('company_id');

        }

        $csdata = M('down')->alias('a')->join('__DOWN_DMAIN__ b')->where("a.id=b.id and a.status=1 and b.company_id='$csid'")->limit(8)->select();
        if (empty($csdata)) {
            $csdata = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where("a.id=b.id and a.status=1 and b.company_id='$csid'")->limit(8)->select();
        }

        if (empty($csid)) {
            $csdata = "";
        }
        $this->assign('csdata', $csdata);
        $this->display('Widget/downcs');
    }

    public function downcss($id)
    {

        $csid = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(" a.id='$id' and    a.id=b.id and  a.status=1")->getField('company_id');
        $csdata = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where("a.id=b.id AND a.category_rootid='1857' and a.status=1 and b.company_id='$csid'")->limit(8)->select();


        if (empty($csid)) {
            $csdata = "";
        }
        $this->assign('csdata', $csdata);
        $this->display('Widget/downcs');
    }


    /**
     * 作者:ganweili
     * 时间:2015/11/20
     * 描述:攻略
     */

    public function downgl($id)
    {
        $tid = M('tags_map')->where("did='$id'")->getField('tid', true);
        foreach ($tid as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        $ztid = empty($aid) ? null : "tid in (" . $aid . ") and ";
        $did = M('tags_map')->where($ztid . "type='document' ")->limit(20)->getField('did', true);
        //$data=M('tags_map')->where("type='document' and did ")
        foreach ($did as $k => $v) {
            $adid .= "'" . "$v" . "',";
        }
        $adid = substr($adid, 0, -1);
        $adid = empty($adid) ? null : " id in(" . $adid . ") and";

        $data = M('document')->where($adid . " status=1 and category_id=1588")->order('create_time DESC')->limit(6)->select();

        $this->assign('data', $data);
        $this->display('Widget/downgl');
    }


    /**
     * 作者:ganweili
     * 时间:2015/11/20
     * 描述:攻略
     */

    public function downgls($id)
    {
        $tid = M('tags_map')->where("did='$id'")->getField('tid', true);
        foreach ($tid as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        $ztid = empty($aid) ? null : "tid in (" . $aid . ") and ";
        $did = M('tags_map')->where($ztid . "type='document' ")->limit(20)->getField('did', true);
        //$data=M('tags_map')->where("type='document' and did ")
        foreach ($did as $k => $v) {
            $adid .= "'" . "$v" . "',";
        }
        $adid = substr($adid, 0, -1);
        $adid = empty($adid) ? null : " id in(" . $adid . ") and";

        $gdata = M('document')->where($adid . "  category_id= 1853  and status=1")->order('create_time DESC')->limit(6)->select();
        if (empty($gdata)) {
            $gdata = M('document')->where(array("category_id" => "1853", "status" => "1"))->order('create_time DESC')->limit(6)->select();
        }
        $this->assign('data', $gdata);
        $this->display('Widget/downgl');
    }


    /**
     * 作者:ganweili
     * 时间:2015/11/23
     * 描述:M排行榜
     */
    public function ph()
    {
        $id = I('get.id');
        $gaid = I('get.id');
        if (empty($id)) {
            $id = M('down_paihang')->getField('id');
        }
        $phid = M('down_paihang')->where('id=' . $id)->getField('soft_id');
        $phid = explode(',', $phid);
        foreach ($phid as $k => $v) {
            $data[$v] = M('down')->alias('a')->join('__DOWN_DMAIN__  b')->field('a.id,a.title,a.smallimg,b.size,a.category_id')->where("a.id = $v and a.id=b.id  and   a.status=1  ")->select();

            if (empty($data[$v])) {
                $data[$v] = M('down')->alias('a')->join('__DOWN_DSOFT__  b')->field('a.id,a.title,a.smallimg,b.size,a.category_id')->where("a.id = $v and a.id=b.id  and   a.status=1  ")->select();
            }

        }

        //更多的排行榜

        $gid = M('down')->where("category_id=1856 and status=1   and id !=  $id")->getField('id', true);

        if (empty($gd)) {
            $where = array();
            $where['category_id'] = 1856;
            $where['status'] = 1;
            $where['id'] = array('in', $gid);
            $gdata = M('down')->where($where)->order('level DESC')->select();
            unset($where);
        }


        $seo = M('down')->where("id=$id and status=1")->field('title as ctitle,seo_title as title,seo_keywords as keywords,seo_description as description')->select();
        $seo = $seo['0'];
        if (empty($gaid)) {
            $seo['title'] = "手游排行榜2015前十名_热门手游排行榜_96U手游排行榜";
            $seo['keywords'] = "手游排行榜,手机游戏排行榜,手机网游排行榜,2015手游排行榜,手游排行榜2015前十名,2015手机游戏排行,热门手机游戏排行";
            $seo['description'] = "96U手游排行榜为您提供2015最新的好玩的手机游戏排行信息，
手游排行榜2015前十名，手游排行榜包括热门手游、3D手游、2D手游、
安卓手游、IOS手游，单机手游等和手机游戏相关的手机游戏排行榜信息，
更方便玩家选择自己想玩的手机游戏。请记住手游排行榜TOP.96u.com!";
        }


        $this->assign("SEO", $seo);
        $this->assign('gdata', $gdata);
        $this->assign('data', $data);
        $this->display('Widget/ph');
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/23
     * 描述:M下载栏目页面ajax
     */
    public function downajax()
    {
        $get = I('get.');
        $row = 10;
        $page = $get['page'];
        $cateid = '(' . $get['category_id'] . ')';
        if ($get['type'] == 1) {
            if (empty($get['order'])) {

                $data = M('down')->alias('a')->join('__DOWN_DMAIN__   b')->where("a.category_id in $cateid and a.status=1  and a.id=b.id and a.position & 4   ")->limit($page, $row)->order('a.id')->select();


            } else {
                $data = M('down')->alias('a')->join('__DOWN_DMAIN__   b')->where("a.category_id in $cateid and a.status=1  and a.id=b.id    ")->limit($page, $row)->order('a.create_time')->select();
            }


            foreach ($data as &$v) {
                $v['smallimg'] = get_cover($v['smallimg'], 'path');
                $v['url'] = str_replace('index.html', '', staticUrlMobile('detail', $v['id'], 'Down'));
                $v['size'] = format_size($v['size']);
                if (empty($v['size'])) {
                    $v['size'] = '未知';
                }
                $v['game_type'] = get_game_type($v['game_type']);
            }
            unset($v);

        } elseif ($get['type'] == 2) {
            if (empty($get['order'])) {
                $data = M('down')->alias('a')->join('__DOWN_DSOFT__   b')->where("a.category_id in $cateid and a.status=1  and a.id=b.id and a.position & 4    ")->order('a.id')->limit($page, $row)->order('a.id')->select();
            } else {
                $data = M('down')->alias('a')->join('__DOWN_DSOFT__   b')->where("a.category_id in $cateid and a.status=1  and a.id=b.id  ")->order('a.create_time')->limit($page, $row)->order('a.id')->select();
            }


            foreach ($data as &$v) {

                $v['smallimg'] = get_cover($v['smallimg'], 'path');
                $v['url'] = str_replace('index.html', '', staticUrlMobile('detail', $v['id'], 'Down'));
                $v['size'] = format_size($v['size']);
                if (empty($v['size'])) {
                    $v['size'] = '未知';
                }
                $v['game_type'] = get_game_typedown($v['category_id']);
            }
            unset($v);
        }
        $data = json_encode($data);
        $callback = $get['callback'];
        echo $callback ? $callback . '(' . $data . ');' : $data;


    }

    /**
     * 作者:ganweili
     * 时间:2015/11/30
     * 描述:下载内容页的攻略
     */
    public function gonglue()
    {
        $id = I('get.id');
        $aid = "";
        $tid = M('tags_map')->where("did='$id'")->getField('tid', true);
        foreach ($tid as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        $ztid = empty($aid) ? null : "a.tid in (" . $aid . ") and ";
        $data = M('tags_map')->alias('a')->join("__DOCUMENT__  b ")->limit(10)->field('b.*')->where($ztid . " a.did=b.id and b.category_id=1588   ")->order('b.create_time DESC')->select();
        $rs = M('down')->where("id=$id and status=1")->field('title')->find();
        $seo['title'] = $rs['title'] . '攻略_96u手游网';
        $seo['keywords'] = $rs['title'] . '攻略';
        $seo['description'] = $rs['title'] . '攻略';
        $this->assign('title', $rs['title'] . '攻略');
        $this->assign("SEO", $seo);
        $this->assign('aid', $aid);
        $this->assign('data', $data);
        $this->display('Widget/taggl');


    }

    /**
     * 作者:ganweili
     * 时间:2015/11/30
     * 描述:下载页面攻略,资讯 ajax
     */
    public function tajax()
    {
        $row = 10;
        $page = I('get.page');
        $tid = I('get.tid');
        $tid = empty($tid) ? null : "a.tid  in (" . $tid . ") and";

        //  $cate = I('get.cate');
        if (!empty($tid)) {
            $data = M('tags_map')->alias('a')->join('__DOCUMENT__ b')->where($tid . " b.category_id='1588' and a.did=b.id ")->field('b.id,b.smallimg,b.description,b.title')->limit($page, $row)->select();
        }

        foreach ($data as &$v) {

            $v['smallimg'] = get_cover($v['smallimg'], 'path');
            $v['url'] = str_replace('index.html', '', staticUrlMobile('detail', $v['id'], 'Document'));


        }
        unset($v);
        $data = json_encode($data);
        $callback = I('get.callback');
        echo $callback ? $callback . '(' . $data . ');' : $data;

    }

    /**
     * 作者:ganweili
     * 时间:2015/12/7
     * 描述:下载内容页的资讯
     */
    public function zixun()
    {
        $id = I('get.id');
        $tid = M('tags_map')->where("did='$id'")->getField('tid', true);
        foreach ($tid as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        $ztid = empty($aid) ? null : "a.tid in (" . $aid . ") and ";
        if (!empty($ztid)) {
            $data = M('tags_map')->alias('a')->join("__DOCUMENT__  b ")->limit(10)->field('b.*')->where($ztid . " a.did=b.id and b.category_id=1622   ")->order('b.create_time DESC')->select();
        }
        //var_dump($data);
        $this->assign('aid', $aid);
        $rs = M('down')->where("id=$id and status=1")->field('title')->find();
        $seo['title'] = $rs['title'] . '资讯_96u手游网';
        $seo['keywords'] = $rs['title'] . '资讯';
        $seo['description'] = $rs['title'] . '资讯';
        $this->assign('title', $rs['title'] . '资讯');
        $this->assign("SEO", $seo);
        $this->assign('data', $data);
        $this->display('Widget/zixun');


    }


    public function commendedSoft($cate)
    {
        if (empty($cate)) {
            return;
        }
        $ids = D('DownCategory')->getAllChildrenId($cate);
        $rs = M("Down")->alias("__DOWN")->where("category_id IN(" . $ids . ") AND home_position & 64")->join("INNER JOIN __DOWN_DSOFT__ ON __DOWN.id = __DOWN_DSOFT__.id")->order("update_time desc")->limit("0,10")->field("*")->select();
        $this->assign('lists', $rs);
        $this->display('Widget/commendedSoft');

    }
}
