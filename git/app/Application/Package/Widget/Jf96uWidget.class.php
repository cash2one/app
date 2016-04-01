<?php


namespace Package\Widget;

use Package\Controller\BaseController;

class Jf96uWidget extends BaseController
{

    //相关游戏
    public function relateGame($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $game = get_base_by_tag($id, 'Package', 'Down', '', true);
        $batch = get_base_by_tag($id, 'Package', 'Batch', '', true);
        $this->assign("batch", $batch);
        $this->assign("game", $game);
        $this->display("Widget/relateGame");
    }

    //相关链接
    public function relateLink($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $doc = get_base_by_tag($id, 'Package', 'Document', '', false);
        foreach ($doc as $key => $val) {
            if ($val['category_id'] == "1590") {
                $gl[$key]['id'] = $val['id'];
                $gl[$key]['update_time'] = $val['update_time'];
            }
            if ($val['category_id'] == "1589") {
                $pc[$key]['id'] = $val['id'];
                $pc[$key]['update_time'] = $val['update_time'];
            }
        }
        $gl = multi_array_sort($gl, 'update_time', SORT_DESC);
        $pc = multi_array_sort($pc, 'update_time', SORT_DESC);
        $this->assign("gl", $gl);
        $this->assign("pc", $pc);
        $this->display("Widget/relateLink");
    }

    //相关礼包
    public function relatePackage($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $package = get_base_by_tag($id, 'Package', 'Package', '', false);
        $this->assign("lists", $package);
        $this->display("Widget/relatePackage");
    }

    //相关新闻
    public function relateNews($id)
    {
        if (!is_numeric($id)) {
            return;
        }
        $news = get_base_by_tag($id, 'Package', 'Document', '', false);
        $length = count($news);
        $limit = 10 - $length;
        if ($limit > 0) {
            $doc = M('Document')->where(array('category_id' => '1587'))->order('create_time desc')->limit(
                $limit
            )->select();
        }
        $lists = array();
        $lists1 = array();
        $lists2 = array();
        if (!empty($news)) {
            foreach ($news as $key => $val) {
                if ($key > 9) {
                    break;
                }
                $lists1[$key]['id'] = $val['id'];
                $lists1[$key]['title'] = $val['title'];
                $lists1[$key]['create_time'] = $val['create_time'];
                $lists1[$key]['update_time'] = $val['update_time'];
            }
        }
        if (!empty($doc)) {
            foreach ($doc as $key1 => $val1) {
                $lists2[$key1]['id'] = $val1['id'];
                $lists2[$key1]['title'] = $val1['title'];
                $lists2[$key1]['create_time'] = $val1['create_time'];
                $lists2[$key1]['update_time'] = $val1['update_time'];
            }
        }

        $lists = array_merge($lists1, $lists2);
        $lists = multi_array_sort($lists, 'update_time', SORT_DESC);
        $lists = array_unique_fb($lists);
        $this->assign("lists", $lists);
        $this->display("Widget/relateNews");
    }

    //开服开测
    public function kaifu()
    {

        $this->display("Widget/kaifu");
    }


    public function packageUrl($letter, $cid)
    {
        $letter = strtolower($letter);
        echo $cid . "_" . $letter . ".html";
    }


    public function packageList()
    {
        $cid = I('cate');
        $le = I('letter');
        $cate = empty($cid) ? '' : " AND category_id= '$cid' ";
        $letter = empty($le) ? '' : " AND title_pinyin= '$le' ";
        $condition = "status =1 AND category_id IN(6,7,8)" . $cate . $letter;

        $count = D('Package')->where($condition)->count();
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理

        $path = C('STATIC_URL') . '/fahao/' . $cid . "_" . $le . '_{page}.html';
        $Page = new \Think\Page($count, 10, '', false, $path);
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '第一页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show = $Page->show();
        $link = $cid . "_" . $le . ".html";
        $letters = array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
            'e' => 'E',
            'f' => 'F',
            'g' => 'G',
            'h' => 'H',
            'i' => 'I',
            'j' => 'J',
            'k' => 'K',
            'l' => 'L',
            'm' => 'M',
            'n' => 'N',
            'o' => 'O',
            'p' => 'P',
            'q' => 'Q',
            'r' => 'R',
            's' => 'S',
            't' => 'T',
            'u' => 'U',
            'v' => 'V',
            'w' => 'W',
            'x' => 'X',
            'y' => 'Y',
            'z' => 'Z'
        );

        $package = M("Package")->alias("__PACKAGE")->where($condition)->order("update_time desc")->join(
            "LEFT JOIN __PACKAGE_PMAIN__ ON __PACKAGE.id = __PACKAGE_PMAIN__.id"
        )->limit($Page->firstRow . ',' . $Page->listRows)->field("*")->select();
        $this->page = $show;
        //SEO
        $zimuseo = strtoupper($le) == "0" ? "手机游戏礼包|手机游戏新手卡|手机游戏激活码|96u手机游戏发号" : strtoupper(
            $le
        ) . '字母开头的礼包大全|手机游戏礼包|手机游戏新手卡|手机游戏激活码|96u手机游戏发号';
        $seo['title'] = $zimuseo;
        $seo['keywords'] = "积分抢号,积分礼包,积分换实物,手机游戏新手卡,手机游戏激活码,手机游戏大礼包,手机游戏道具卡,手机游戏内测号,96u手机游戏发号";
        $seo['description'] = "96u手机游戏发号中心提供手机游戏礼包,手机游戏激活码,手机游戏新手卡,手机游戏限量礼包等手机游戏相关资源自动发放的平台,玩家最贴心的新手卡及激活码领取基地就住96u手机游戏发号中心！";
        $this->assign('link', $link);
        $this->assign('class', 'package'); //用户导航选中效果  add by tanjian  2015/12/21 17:07
        $this->assign('letters', $letters);
        $this->assign('le', strtoupper($le));
        $this->assign('cate', $cid);
        $this->assign('SEO', $seo);
        $this->assign("lists", $package);
        $this->display("Category/index");
    }
}
