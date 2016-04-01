<?php
namespace Down\Widget;

use Down\Controller\BaseController;

class Jf96uWidget extends BaseController
{

    /**
     * 描述：排行榜
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function paihang()
    {
        $this->assign("SEO", WidgetSEO(array('special', null, '75')));
        $this->assign('cid', '75');
        $this->display('Widget/rank');
    }

    /**
     * 描述：排行榜
     *
     * @param $id
     * @param $type
     * @param $title
     * @param $href
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function rankCon($id, $type, $title, $href)
    {
        $id = empty($id) ? '246744' : $id;
        $_LISTS_ = array();
        $softID = M('DownPaihang')->where("id='$id'")->getField('soft_id');
        $softID = rtrim($softID, ",");
        $softID = rtrim($softID, " ");
        $ids = split(",", $softID);
        foreach ($ids as $id) {
            $_LISTS_[$id] = M()->table('onethink_down tb1,onethink_down_dmain tb2')->field('*')->where(
                'tb1.id=tb2.id AND tb1.id=' . $id .' AND tb1.status=1'
            )->order('create_time desc')->limit(50)->select();
            if (empty($_LISTS_[$id])) {
                $_LISTS_[$id] = M()->table('onethink_down tb1,onethink_down_dsoft tb2')->field('*')->where(
                    'tb1.id=tb2.id AND tb1.id=' . $id .' AND tb1.status=1'
                )->order('create_time desc')->limit(50)->select();
            }
        }
        $arrT = array();
        foreach ($_LISTS_ as $value) {
            foreach ($value as $value) {
                $arrT[] = $value;
            }
        }
        switch ($type) {
            case '0':
                $this->assign('ranks', array_slice($arrT, 0, 50));
                $this->display('Widget/rankDetailBlock');
                return;
            case '1':
                $this->assign('title', $title);
                $this->assign('href', $href);
                $this->assign('ranks', array_slice($arrT, 0, 10));
                $this->display('Widget/rankBlock');
                return;
            case '2':
                $this->assign('ranks', array_slice($arrT, 0, 10));
                $this->display('Widget/rankIndex');
                return;
        }
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/16
     * 描述:二级栏目用于游戏库下载首页的二级分类
     */

    public function downcate($cate)
    {

        $data = M('down_category')->where('pid=' . $cate)->select();

        $tid = empty($data) ? 1 : 2;
        if (!$data) {
            $data = M('down_category')->where('id=' . $cate)->getField('pid');
            $data = M('down_category')->where('pid=' . $data)->select();

        }
        if ($cate == '1666' or $cate == '1665') {
            $rootid = M('down_category')->where('pid=' . $cate)->getField('rootid');
            $data = M('down_category')->where("pid=$rootid")->select();

        }

        $this->assign('tid', $tid);
        $this->assign('data', $data);
        $this->assign('cate', $cate);
        if ($cate != 1687 and $cate != 1854) {
            $this->display('Widget/downcate');
        } else {


        }
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/16
     * 描述:二级栏目用于游戏库下载首页的二级分类
     */

    public function softcate($cate)
    {

        $data = M('down_category')->where('pid=' . $cate)->select();

        $tid = empty($data) ? 1 : 2;
        if (!$data) {
            $data = M('down_category')->where('id=' . $cate)->getField('pid');
            $data = M('down_category')->where('pid=' . $data)->select();

        }
        if ($cate == '1666' or $cate == '1665') {
            $rootid = M('down_category')->where('pid=' . $cate)->getField('rootid');
            $data = M('down_category')->where("pid=$rootid")->select();

        }

        $this->assign('tid', $tid);
        $this->assign('data', $data);
        $this->assign('cate', $cate);
        if ($cate != 1687 and $cate != 1854) {
            $this->display('Widget/softcate');
        } else {


        }
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/16
     * 描述:游戏库下载首页的一级分类
     *   <a href="{:staticUrl('lists',1676, 'down')}" class="active">安卓游戏</a>
     * <a href="">PC游戏</a>
     * <a href="">苹果游戏</a>
     */
    public function downtop($cid)
    {
        $data = M('down_category')->where('pid=1676')->select();
        $this->assign('cid', $cid);
        $this->assign('data', $data);
        $this->display('Widget/downtop');

    }

    /**
     * 作者:ganweili
     * 时间:2015/11/17
     * 描述:安卓游戏推荐
     */
    public function antj($pid)
    {
        $pid = M('down_category')->where("pid=$pid ")->getField('id', true);
        foreach ($pid as $k => $v) {
            $pd .= "'" . "$v" . " ', ";
        }
        $pd = substr($pd, 0, -2);

        $pd = empty($pd) ? null : "a.category_id in (" . $pd . ")  and  ";

        $data = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
            $pd . " a.id=b.id and a.home_position & 64 and a.status=1  "
        )->limit(8)->select();

        if (count($data) < 8) {
            $mi = 8 - count($data);
            $datab = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(" a.id=b.id and a.status=1")
                ->limit($mi)
                ->select();
            if (empty($data)) {
                $data = $datab;
            } else {
                $data = array_merge($data, $datab);
            }
        }
        $this->assign('data', $data);
        $this->display('Widget/hotph');

    }


    /**
     * 作者:ganweili
     * 时间:2015/11/17
     * 描述:M.下载栏目页也就是下载内容页里面的其他版本版本  猜你喜欢 以及下面的游戏版本
     * $id=当前页面的id
     * $system=当前页面的系统类型
     */
    public function downbanben($id, $system, $model)
    {
        if (!is_numeric($id) || empty($model) || empty($system)) {
            return;
        }
        $model = strtoupper($model);
        $tid = M('tags_map')->where("did=" . $id . " and type='down'")->getField('tid');
        $tid = empty($tid) ? null : "tid=" . $tid . " and";
        if (!empty($tid)) {
            $did = M('tags_map')->where($tid . "  did NOT IN ('" . "$id" . "')  and type='down' ")
            ->getField('did', true);
        }
        $aid = '';
        foreach ($did as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        //其他平台版本
        //为空时防止sql报错
        if (empty($aid)) {
            $aid = "";
        } else {

            $aid = " and a.id   IN (" . "$aid" . ")";
        }
        if (!empty($aid)) {
            $otherdown = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
                "a.id=b.id " . $aid . " and a.status=1  "
            )
                ->field('a.id,a.title,b.system')
                ->select();
        }

        if (empty($aid)) {
            $otherdown = null;
        }
        //所有的产品标签
        $ptag = M('product_tags_map')->alias('a')
            ->join('__PRODUCT_TAGS__ b')
            ->where("a.did='$id' and a.tid=b.id and b.status=1 and a.type='down'")
            ->field('b.id as id,b.title as title')->select();
        foreach ($ptag as $k => $v) {
            $key = $v['id'];

            $data[$key] = M('product_tags_map')->alias('a')->join('__DOWN__  b on a.did=b.id')->join(
                '__' . $model . '__ c on c.id=b.id'
            )
                ->where("a.tid=$key " . " and  a.did=b.id  and b.status=1 and b.id <>$id and a.type='down'")->limit(6)
                ->field(
                    'b.id as id,b.title as title,b.smallimg as smallimg,c.version as version,c.size as size,b.create_time as create_time,b.category_id as category_id'
                )->select();
            $wdata[$key] = M('product_tags_map')->alias('a')->join('__FEATURE__  b ')
                ->where(
                    "a.tid=$key " . " and  a.did=b.id  and   b.interface=0 and b.pid=0 and b.enabled=1 and a.type='feature' and b.pid='0'"
                )->limit(1)
                ->field('b.id as id,b.title as title,b.icon as icon,b.description as description')->select();
            if (empty($data[$key]) && empty($wdata[$key])) {
                unset($data[$key], $wdata[$key], $ptag[$k]);
            }
        }
        $this->assign('count', count($ptag));
        $this->assign('ptag', $ptag);
        $this->assign('wdata', $wdata);
        $this->assign('data', $data);
        $this->assign('otherdowncount', count($otherdown));
        $this->assign('otherdown', $otherdown);
        $this->display('Widget/downbanben');

    }

    /**
     * 作者:ganweili
     * 时间:2015/11/18
     * 描述:下载栏目页也就是下载内容页里面的资讯 攻略 视频 礼包 同类游戏
     */
    public function downtag($id)
    {
//游戏资讯

        $ztid = M('tags_map')->where('did=' . $id)->getField('tid', true);

        $ztid = implode("','", $ztid);

        $ztid = empty($ztid) ? null : "tid in ('" . $ztid . "') and";

        $zdid = M('tags_map')->where($ztid . " type='document'")
            ->getField('did', true);
        foreach ($zdid as $k => $v) {
            $zaid .= "'" . "$v" . "',";
        }
        $aid = substr($zaid, 0, -1);

        $aid = empty($aid) ? null : "id in (" . $aid . ") and ";

        $zdata = M('document')->where($aid . "  category_id= 1622  and status=1 ")->order('create_time DESC')->limit(
            10
        )->select();
        if (empty($zdata)) {
            $zdata = M('document')->where("  category_id= 1622  and status=1 ")->order('create_time DESC')->limit(
                10
            )->select();
        }
        //攻略

        $gdata = M('document')->where($aid . "  category_id= 1588  and status=1")->order('create_time DESC')->limit(
            10
        )->select();
        if (empty($gdata)) {
            $gdata = M('document')->where("  category_id= 1588  and status=1")->order('create_time DESC')->limit(
                10
            )->select();
        }
        //礼包
        $lbdid = M('tags_map')->where($ztid . " type='package'")

            ->getField('did', true);


        foreach ($lbdid as $k => $v) {
            $lbaid .= "'" . "$v" . "',";
        }
        $lbaid = substr($lbaid, 0, -1);

        $lbaid = empty($lbaid) ? null : "id in (" . $lbaid . ") and ";

        $ldata = M('package')->where($lbaid . " status=1  and category_id=6  ")->limit(10)->select();

        if (empty($ldata)) {
            $ldata = M('package')->where(" status=1 and category_id=6")->limit(10)->select();

        }
        //游戏视频
        $pdata = M('document')->where($aid . " category_id= 1591   and status=1 ")->order('view')
            ->limit(4)->select();
        if (empty($pdata)) {
            $pdata = M('document')->where(" category_id= 1591   and status=1 ")->order('view')
                ->limit(4)->select();
        }

        //相同厂商


        $csid = M('down')->alias('a')->join('__DOWN_DMAIN__ b')->where("a.id='$id' and  a.id=b.id and  a.status=1")
            ->getField('company_id');

        if (empty($csid)) {
            $csid = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
                " a.id='$id' and    a.id=b.id and  a.status=1"
            )
                ->getField('company_id');

        }

        $csdata = M('down')->alias('a')->join('__DOWN_DMAIN__ b')->where(
            "a.id=b.id and a.status=1 and b.company_id='$csid'"
        )
            ->limit(18)
            ->select();
        if (empty($csdata)) {
            $csdata = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
                "a.id=b.id and a.status=1 and b.company_id='$csid'"
            )
                ->limit(18)
                ->select();
        }

        if (empty($csid)) {
            $csdata = "";
        }

        $this->assign('csdata', $csdata);
        $this->assign('pdata', $pdata);
        $this->assign('ldata', $ldata);
        $this->assign('gdata', $gdata);
        $this->assign('zdata', $zdata);
        $this->display('Widget/downtag');

    }

    /**
     * 作者:ganweili
     * 时间:2015/11/17
     * 描述:M.下载栏目页也就是下载内容页里面的其他版本版本  猜你喜欢 以及下面的游戏版本
     * $id=当前页面的id
     * $system=当前页面的系统类型
     */
    public function downbanbens($id, $system, $model)
    {
        if (!is_numeric($id) || empty($model) || empty($system)) {
            return;
        }
        $model = strtoupper($model);
        $tid = M('tags_map')->where("did=" . $id . " and type='down'")->getField('tid');
        $tid = empty($tid) ? null : "tid=" . $tid . " and";
        if (!empty($tid)) {
            $did = M('tags_map')->where($tid . "  did NOT IN ('" . "$id" . "')  and type='down' ")
                ->getField('did', true);
        }
        $aid = '';
        foreach ($did as $k => $v) {
            $aid .= "'" . "$v" . "',";
        }
        $aid = substr($aid, 0, -1);
        //其他平台版本
        //为空时防止sql报错
        if (empty($aid)) {
            $aid = "";
        } else {

            $aid = " and a.id   IN (" . "$aid" . ")";
        }
        if (!empty($aid)) {
            $otherdown = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
                "a.id=b.id " . $aid . " and a.status=1  "
            )
                ->field('a.id,a.title,b.system')
                ->select();
        }

        if (empty($aid)) {
            $otherdown = null;
        }

        //所有的产品标签
        $ptag = M('product_tags_map')->alias('a')
            ->join('__PRODUCT_TAGS__ b')
            ->where("a.did='$id' and a.tid=b.id and b.status=1 and a.type='down'  and b.category = '4'")
            ->field('b.id as id,b.title as title')->select();
        foreach ($ptag as $k => $v) {
            $key = $v['id'];

            $data[$key] = M('product_tags_map')->alias('a')->join('__DOWN__  b on a.did=b.id')->join(
                '__' . $model . '__ c on c.id=b.id'
            )
                ->where("a.tid=$key " . " and  a.did=b.id  and b.status=1 and b.id <>$id and a.type='down'")->limit(6)
                ->field(
                    'b.id as id,b.title as title,b.smallimg as smallimg,c.version as version,c.size as size,b.create_time as create_time,b.category_id as category_id'
                )->select();
            $wdata[$key] = M('product_tags_map')->alias('a')->join('__FEATURE__  b ')
                ->where(
                    "a.tid=$key " . " and  a.did=b.id  and   b.interface=0 and b.pid=0 and b.enabled=1 and a.type='feature' and b.pid='0'"
                )->limit(1)
                ->field('b.id as id,b.title as title,b.icon as icon,b.description as description')->select();
            if (empty($data[$key]) && empty($wdata[$key])) {
                unset($data[$key], $wdata[$key], $ptag[$k]);
            }
        }
        $this->assign('count', count($ptag));
        $this->assign('ptag', $ptag);
        $this->assign('wdata', $wdata);
        $this->assign('data', $data);
        $this->assign('otherdowncount', count($otherdown));
        $this->assign('otherdown', $otherdown);
        $this->display('Widget/downbanbens');

    }

    /**
     * 作者:ganweili
     * 时间:2015/11/18
     * 描述:下载栏目页也就是下载内容页里面的资讯 攻略 视频 礼包 同类游戏(针对软件进行调整2016-2-1 16:11:47)
     */
    public function downtags($id)
    {
//游戏资讯

        $ztid = M('tags_map')->where('did=' . $id)->getField('tid', true);

        $ztid = implode("','", $ztid);

        $ztid = empty($ztid) ? null : "tid in ('" . $ztid . "') and";

        $zdid = M('tags_map')->where($ztid . " type='document'")

            ->getField('did', true);


        foreach ($zdid as $k => $v) {
            $zaid .= "'" . "$v" . "',";
        }
        $aid = substr($zaid, 0, -1);

        $aid = empty($aid)?null:"id in (" . $aid . ") and ";

        $zdata = M('document')->where($aid . "  category_id= 1622  and status=1 ")->order('create_time DESC')->limit(
            10
        )->select();
        if (empty($zdata)) {
            $zdata = M('document')->where("  category_id= 1622  and status=1 ")->order('create_time DESC')->limit(
                10
            )->select();
        }
        //攻略

        $gdata = M('document')->where($aid . "  category_id= 1853  and status=1")->order('create_time DESC')->limit(
            10
        )->select();
        if (empty($gdata)) {
            $gdata = M('document')->where("  category_id= 1853  and status=1")->order('create_time DESC')->limit(
                10
            )->select();
        }

        //相同厂商


        $csid = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(" a.id='$id' and    a.id=b.id and  a.status=1")
            ->getField('company_id');

        $csdata = M('down')->alias('a')->join('__DOWN_DSOFT__ b')->where(
            "a.id=b.id AND a.category_rootid='1857' and a.status=1 and b.company_id='$csid'"
        )
            ->limit(18)
            ->select();


        if (empty($csid)) {
            $csdata = "";
        }

        $this->assign('csdata', $csdata);
        $this->assign('gdata', $gdata);
        $this->assign('zdata', $zdata);
        $this->display('Widget/downtags');

    }
}
