<?php
// +----------------------------------------------------------------------
// | afs动态访问控制类
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

use Think\Exception;
use Think\Page;

class AfsController extends BaseController
{
    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 下载地址容错处理（此方法仅供testServer方法调用）
     * @param $cate_id  礼包分类ID
     * @param $where    查询数据的条件
     * @return $url
     */
    public function ajaxHisTestServerData()
    {
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $cate_id = I('cate_id');
        $sta = I('sta');
        if ($sta < 0) {
            $sta = 0;
        }
        if (!in_array($cate_id, array(4, 5))) {
            return false;
        }

        $historyData = $this->getTestServer($cate_id, 'b.start_time < UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL 0 DAY))', $sta);

        if ($historyData) {
            foreach ($historyData as $k => &$v) {
                $v['img'] = get_cover($v['cover_id'], 'path');
                $v['month'] = date('m-d', $v['start_time']);
                $v['time'] = date('h:i', $v['start_time']);
                $v['url'] = staticUrl('detail', $v['game'], 'Down');
                $v['company'] = $v['company'] ? $v['company'] : '佚名';
            }
            unset($v);
        } else {
            echo false;
        }

        echo $callback ? $callback . '(' . json_encode($historyData) . ');' : json_encode($historyData);

    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 获取开测开服的数据（此方法仅供testServer方法调用）
     * @param $cate_id //礼包分类ID
     * @param $where //查询数据的条件
     * @return $date array
     */

    private function getTestServer($cate_id, $where, $sta = 0, $len = 10)
    {
        //获取数据
        $date = M('Package')->alias('a')->join('__PACKAGE_PARTICLE__ b ON a.id = b.id')->where("status = 1 AND category_id = $cate_id AND $where")->limit($sta, $len)->order('b.start_time DESC')->select();

        //获取每条数据的产品标签
        foreach ($date as $k => $v) {
            $tid = get_tags($v['id'], 'package', 'product')[0]['id'];
            if ($tid) {
                $games = M('productTagsMap')->alias('a')->field('b.*,c.system,d.name company,e.url,e.site_id')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')->join('__COMPANY__ d ON c.company_id = d.id', 'left')->join('__DOWN_ADDRESS__ e ON b.id = e.did')->order('b.id DESC')->where("a.tid = $tid AND a.type='down'")->select();
                if (empty($games)) {
                    continue;
                }

                foreach ($games as $k1 => $v1) {
                    if ($v1['system'] == 1) {
                        if ($date[$k]['androidUrl']) {
                            continue;
                        }
                        $date[$k]['androidUrl'] = $this->formatAddress($v1['url'], $v1['site_id']);
                        $date[$k]['androidCode'] = builtQrcode($date[$k]['androidUrl']);
                    } elseif ($v1['system'] == 2) {
                        if ($date[$k]['iosUrl']) {
                            continue;
                        }
                        $date[$k]['iosUrl'] = $this->formatAddress($v1['url'], $v1['site_id']);
                        $date[$k]['iosCode'] = builtQrcode($date[$k]['iosUrl']);
                        if (!$date[$k]['company']) {
                            $date[$k]['company'] = $v1['company'] ? $v1['company'] : '';
                        }
                    }
                }
                $date[$k]['game'] = $games[0]['id'];
                $date[$k]['description'] = empty($date[$k]['description']) ? $games[0]['description'] : $date[$k]['description'];
            }
        }

        return $date;
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 下载地址容错处理（此方法仅供getTestServer方法调用）
     * @param $url      url 地址
     * @param $sit_id //公共下载地址ID
     * @return $url
     */
    private function formatAddress($url, $sit_id)
    {
        if (substr($url, 0, 8) != "https://" && substr($url, 0, 7) != "http://") {

            $down = M('presetSite')->where('id =' . $sit_id)->find()['site_url'];

            if (substr($url, 0, 1) == '/') {
                $url = substr($url, 1);
            }
            if (substr($down, 0, 7) != "http://" && substr($down, 0, 8) != "https://") {
                $down = "http://" . $down;
            }
            if (substr($down, -1, 1) != '/') {
                $down = $down . '/';
            }
            $url = $down . $url;
        }

        return $url;
    }


    public function packageList()
    {
        //参数
        $params = I('params');

        if (empty($params)) {
            $p = 1;
        } else {
            $params = explode('_', $params);
            $p = array_pop($params);
        }
        $row = 24;
        $_GET['p'] = $p;

        //礼包类型
        $filter['cate'] = array(
            'title' => '类型',
            'config' => C('FIELD_PACKAGE_CARD_TYPE'),
        );

        //音序
        $filter['pinyin'] = array(
            'title' => '音序',
            'config' => array('A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F', 'G' => 'G', 'H' => 'H', 'I' => 'I', 'J' => 'J', 'K' => 'K', 'L' => 'L', 'M' => 'M', 'N' => 'N', 'O' => 'O', 'P' => 'P', 'Q' => 'Q', 'R' => 'R', 'S' => 'S', 'T' => 'T', 'U' => 'U', 'V' => 'V', 'W' => 'W', 'X' => 'X', 'Y' => 'Y', 'Z' => 'Z'),
        );

        //参数为空的时候全为0
        if (empty($params)) {
            foreach ($filter as $key => $value) {
                $params[] = '0';
            }
        }

        $params && list($cate, $pinyin) = $params;

        $html = '';
        $i = 0;
        //生成筛选
        foreach ($filter as $k => $v) {
            $uparams = $params;

            //URL拼接
            $uparams[$i] = 0;
            $href = C('PACKAGE_SLD') . '/li/' . implode('_', $uparams) . '_1.html';

            $html .= '<dl><dt>' . $v['title'] . '</dt><dd><a ' . (empty(${$k}) ? 'class="cur"' : '') . 'href="' . $href . '" title="全部">全部</a>';

            foreach ($v['config'] as $k1 => $v1) {
                $cur = '';
                if ((${$k} == $k1)) {
                    $cur = 'class="cur"';
                    //seo相关词组
                    $seo_array[$k] = $v;
                }
                //URL拼接
                $uparams[$i] = $k1;
                $href = C('PACKAGE_SLD') . '/li/' . implode('_', $uparams) . '_1.html';

                $html .= '<a ' . $cur . ' href="' . $href . '" title="' . $v1 . '">' . $v1 . '</a>';
            }
            $html .= '</dd></dl>';
            $i++;
        }

        //数据调取
        $where = 'category_id = 1 ';
        if ($cate != '0') {
            $where .= 'AND card_type = "' . $cate . '"';
        }

        if ($pinyin != '0') {
            $where .= ' AND title_pinyin = "' . $pinyin . '"';
        }


        //总记录数
        $count = M('Package')->alias('a')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where($where)->count();
        $lists = M('Package')->alias('a')->join('__PACKAGE_PMAIN__ b ON a.id = b.id')->where($where)->page($p, $row)->select();

        //分页
        if (!$params) {
            $f = '';
            for ($i = 0; $i < count($filter); $i++) {
                $f .= '0_';
            }
            $path = C('PACKAGE_SLD') . '/li/' . $f . '{page}.html';
        } else {
            $path = C('PACKAGE_SLD') . '/li/' . implode('_', $params) . '_{page}.html';
        }

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        $SEO['title'] = '手大礼包_游戏礼包_礼包领取中心 - 安粉丝礼包中心 - 第' . $p . '页';
        $SEO['keywords'] = '手游礼包,大礼包,礼包领取中心,礼包大全';
        $SEO['description'] = '最新手游礼包激活码礼包大全由安粉丝礼包中心提供';

        //数据赋值
        $this->assign(array(
            'SEO' => $SEO,
            'filter' => $html,
            'lists' => $lists,
        ));

        //模板选择
        $this->display(T('Package@afs/Category/index'));
    }

    public function downList()
    {
        //参数
        $params = I('params') ;
        if (empty($params)) {
            $best = $p = 1;
        } else {
            $params = explode('_', $params);

            if (count($params) != 5) {
                return false;
            }

            if (!is_numeric($params[0])) {
                return;
            }
            if (!is_numeric($params[1])) {
                return;
            }
            if ($params[2] != 0) {
                if ($params[0] != 0) {
                    return;
                }
                if ($params[1] != 0) {
                    return;
                }
            }
            //后来扩展的（肖书成）
            /*******************************/
            if ($params[2] != '0') {
                $softTag = M('tags')->field('id,title,meta_title,keywords,description,icon')->where('status = 1 AND category = 6 AND name = "' . $params[2] . '"')->find();
                if (!empty($softTag)) {
                    $this->softTag($softTag);
                    exit();
                }
            }

            /*******************************/

            $p = (int)array_pop($params);
            $best = (int)array_pop($params);

            if ($p < 1) {
                return;
            }
            if ($best < 1) {
                return;
            }
        }

        $_GET['p'] = $p;

        //游戏联网
        $filter['network'] = array(
            'title' => '联网',
            'class' => 'tz',
            'config' => array(1 => '单机', 2 => '网游'),
        );

        //游戏分类
        if (empty($params)) {
            $where = 'pid IN(1,2)';
        } else {
            if (in_array($params[0], array(1, 2))) {
                $where = 'pid = ' . $params[0];
            } else {
                $where = 'pid IN(1,2)';
            }
        }

        $filter['category'] = array(
            'title' => '分类',
            'class' => 'lx',
            'config' => M('downCategory')->where($where)->getField('id,title'),
        );

        //参数为空的时候全为0
        if (empty($params)) {
            foreach ($filter as $key => $value) {
                $params[] = '0';
            }
            $params[] = '0';
        }

        $params && list($network, $category, $tags) = $params;

        $html = '';
        $i = 0;

        if (!in_array($params[1], array_keys($filter['category']['config']))) {
            $category = 0;
            $params[1] = 0;
        }
        foreach ($filter as $k => $v) {
            $uparams = $params;
            $uparams[2] = 0;
            //URL拼接
            $uparams[$i] = 0;
            $href = C('STATIC_URL') . '/game/' . implode('_', $uparams) . '_' . $best . '_1.html';

            $html .= '<p class="' . $v['class'] . '"><em>' . $v['title'] . '</em><a href="' . $href . '" ' . (empty(${$k}) ? 'class="cur"' : '') . '>不限</a>';

            foreach ($v['config'] as $k1 => $v1) {
                $cur = '';
                if ((${$k} == $k1)) {
                    $cur = 'class="cur"';
                    //seo相关词组
                    $seo_array[$k] = $v;
                }
                //URL拼接
                $uparams[$i] = $k1;
                $href = C('STATIC_URL') . '/game/' . implode('_', $uparams) . '_' . $best . '_1.html';
                $html .= '<a href="' . $href . '" ' . $cur . '>' . $v1 . '</a>';
            }

            $html .= '</p>';
            $i++;
        }

        /****标签****/
        //标签url参数
        $tparams = array();
        foreach ($params as $k => $v) {
            $tparams[$k] = 0;
        }

        //获取标签
        $tagslist = M('tags')->field('id,name,title')->where('category = 1 AND status = 1 AND display = 1')->select();
        $html .= '<div class="bq"><div class="icobq">标签</div><div class="ta">';

        //设计标签链接
        foreach ($tagslist as $k => $v) {
            $tparams[2] = $v['name'];
            $href = C('STATIC_URL') . '/tag/' . $v['name'] . '/';

            if ($tags == $v['name']) {
                $cur = 'class="cur"';
            } else {
                $cur = '';
            }

            $html .= '<a ' . $cur . ' href="' . $href . '" target="_blank">' . $v['title'] . '</a>';
        }
        $html .= '</div><div style="clear: both;"></div></div >';
        //查询数据的条件处理
        if ($category == '0') {
            if ($network == '0') {
                if ($tags == '0') {
                    $wheres = 'a.category_rootid IN(1,2)';
                } else {

                    foreach ($tagslist as $k => $v) {
                        if ($v['name'] == $tags) {
                            $tid = $v['id'];
                        }
                    }
                    if (empty($tid)) {
                        header("HTTP/1.1 404 Not Found");
                        header("Status: 404 Not Found");
                        return false;
                    }
                    $wheres = "d.tid = $tid AND d.type='down'";
                }
            } elseif ($network == '1') {
                $wheres = 'a.category_rootid = 1';
            } elseif ($network == '2') {
                $wheres = 'a.category_rootid = 2';
            } else {
                return false;
            }
        } else {
            $wheres = "a.category_id = $category";
        }

        $wheres .= ' AND a.status = 1';

        //排序
        if ($best == 1) {
            $order = 'a.update_time DESC';
        } elseif ($best == 2) {
            $order = 'a.view DESC';
        } else {
            return false;
        }
        $row = 16;

        //判断是否是标签页
        $istag = (is_numeric($tags) && $tags == 0);

        //总记录数
        if ($istag) {
            $count = M('down')->alias('a')->where($wheres)->count('id');
            $lists = M('down')->alias('a')->field('a.id,a.title,a.description,a.smallimg,b.size,b.system,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id', 'left')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where($wheres)->order($order)->page($p, $row)->select();
        } else {
            $count = M('down')->alias('a')->join('__TAGS_MAP__ d ON d.did = a.id', 'left')->where($wheres)->count('a.id');
            $lists = M('down')->alias('a')->field('a.id,a.title,a.description,a.smallimg,b.size,b.system,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id', 'left')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->join('__TAGS_MAP__ d ON d.did = a.id', 'left')->where($wheres)->order('d.sort ASC,' . $order)->page($p, $row)->select();
        }

        //分页
        $path = C('STATIC_URL') . ($istag ? '/game/' : '/tag/') . ($istag ? implode('_', $params) . '_' . $best : $tags . '/' . $best) . '_{page}.html';

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        if ($istag) {
            if ($network == 0 && $category == 0) {
                $Page->firstPath = $new = C('STATIC_URL') . '/game/';
            } else {
                $new = C('STATIC_URL') . ($istag ? '/game/' : '/tag/') . ($istag ? implode('_', $params) . '_' : $tags . '/') . '1_1.html';
            }
        } else {
            $Page->firstPath = C('STATIC_URL') . '/tag/' . $tags . '/';
        }

        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        if ((int)$params[0] == 0 && (int)$params[1] == 0 && is_numeric($params[2]) && (int)$params[2] == 0) {
            $SEO['title'] = C('DOWN_DEFAULT_TITLE') . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = C('DOWN_DEFAULT_KEY');
            $SEO['description'] = C('DOWN_DEFAULT_DESCRIPTION');
        } elseif ($params[1] != 0) {
            $cateSEO = M('downCategory')->where("id = '" . $params[1] . "'")->find();
            $SEO['title'] = $cateSEO['meta_title'] . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = $cateSEO['keywords'];
            $SEO['description'] = $cateSEO['description'];
        } elseif ($params[0] != 0) {
            $cateSEO = M('downCategory')->where("id = '" . $params[0] . "'")->find();
            $SEO['title'] = $cateSEO['meta_title'] . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = $cateSEO['keywords'];
            $SEO['description'] = $cateSEO['description'];
        } elseif ($params[2] != '0') {
            $cateSEO = M('tags')->where("name = '" . $params[2] . "'")->find();
            $SEO['title'] = $cateSEO['meta_title'] . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = $cateSEO['keywords'];
            $SEO['description'] = $cateSEO['description'];
        }

        //页面赋值
        $this->assign(array(
            'istag' => $istag,
            'SEO' => $SEO,
            'filter' => $html,
            'lists' => $lists,
            'count' => $count,
            'new' => $new,
            'hot' => C('STATIC_URL') . ($istag ? '/game/' : '/tag/') . ($istag ? implode('_', $params) . '_' : $tags . '/') . '2_1.html',
            'best' => $best
        ));
        //模板选择
        $this->display(T('Down@afs/Category/index'));
    }

    //标签页
    private function softTag($tag)
    {
        $where = "a.status = 1 AND d.tid = " . $tag['id'] . " AND d.type = 'down'";

        $lists = M('down')->alias('a')->field('a.id,a.title,a.create_time,a.description,a.smallimg,d.tid,b.soft_socre,b.size,b.system,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->join('__TAGS_MAP__ d ON d.did = a.id', 'left')->where($where)->order('d.sort ASC,b.soft_socre DESC,a.view DESC')->select();
        //echo M()->getLastSql();
        if (empty($lists)) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            exit;
        }

        //SEO
        $seo['title'] = $tag['meta_title'];
        $seo['keywords'] = $tag['keywords'];
        $seo['description'] = $tag['description'];

        $this->assign(array(
            'tag' => $tag,
            'SEO' => $seo,
            'lists' => $lists,

        ));

        $this->display(T('Down@afs/Category/softTag'));
    }


    //软件搜索页面
    public function softList()
    {
        //参数
        $params = I('params');
        if (empty($params)) {
            $best = $p = 1;
        } else {
            $params = explode('_', $params);

            if (count($params) != 3) {
                return false;
            }
            $p = (int)array_pop($params);
            $best = (int)array_pop($params);

            if (!in_array($best, array(1, 2))) {
                $best = 1;
            }
            if ($p < 1) {
                return;
            }
        }
        $location = C('STATIC_URL');
        $_GET['p'] = $p;

        $filter['category'] = array(
            'title' => '分类',
            'class' => 'lx',
            'config' => M('downCategory')->where('pid = 48')->getField('id,title'),
        );

        //参数为空的时候全为0
        if (empty($params)) {
            $params = array(0);
        }

        $params && $category = (int)$params[0];

        $html = '';
        $i = 0;

        if (!in_array($params[0], array_keys($filter['category']['config']))) {
            $category = 0;
        }

        //防止以后需要扩展，所以用foreach
        foreach ($filter as $k => $v) {
            //URL拼接
            $href = $location . '/soft/0_1_1.html';
            $html .= '<p class="' . $v['class'] . '"><em>' . $v['title'] . '</em><a href="' . $href . '" ' . (empty(${$k}) ? 'class="cur"' : '') . '>不限</a>';

            foreach ($v['config'] as $k1 => $v1) {
                $cur = '';
                if ((${$k} == $k1)) {
                    $cur = 'class="cur"';
                    //seo相关词组
                    $seo_array[$k] = $v;
                }
                //URL拼接
                $href = $location . '/soft/' . $k1 . '_' . $best . '_1.html';
                $html .= '<a href="' . $href . '" ' . $cur . '>' . $v1 . '</a>';
            }

            $html .= '</p>';
            $i++;
        }

        /****标签****/
        //获取标签
        $tagslist = M('tags')->field('id,name,title')->where('category = 6')->select();
        $html .= '<div class="bq"><div class="icobq">标签</div><div class="ta">';

        //设计标签链接
        foreach ($tagslist as $k => $v) {
            $tparams[2] = $v['name'];
            $href = $location . '/tag/' . $v['name'] . '/';
            $html .= '<a href="' . $href . '" target="_blank">' . $v['title'] . '</a>';
        }
        $html .= '</div><div style="clear: both;"></div></div >';

        //查询数据的条件处理

        if ($category == 0) {
            $wheres = 'a.category_rootid = 48';
        } else {
            $wheres = "a.category_id = $category";
        }

        $wheres .= ' AND a.status = 1';

        //排序
        $order = ($best == 1 ? 'a.update_time DESC' : 'a.abet DESC');

        $row = 16;

        //总记录数
        $count = M('down')->alias('a')->where($wheres)->count('id');
        $lists = M('down')->alias('a')->field('a.id,a.title,a.description,a.smallimg,b.size,b.system,c.title cate')->join('__DOWN_DSOFT__ b ON a.id = b.id', 'left')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where($wheres)->order($order)->page($p, $row)->select();


        //分页
        $path = $location . '/soft/' . $category . '_' . $best . '_{page}.html';

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        ($category == 0) && $Page->firstPath = $location . '/soft/';
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出

        //SEO
        if ((int)$params[0] == 0 && (int)$params[1] == 0) {
            $SEO['title'] = C('DOWN_SOFT_TITLE') . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = C('DOWN_SOFT_KEY');
            $SEO['description'] = C('DOWN_SOFT_DESCRIPTION');
        } elseif ($params[0] != 0) {
            $cateSEO = M('downCategory')->field('meta_title,keywords,description')->where("id = '" . $category . "'")->find();
            $SEO['title'] = $cateSEO['meta_title'] . ' (第' . $p . '页) - 安粉丝手游网';
            $SEO['keywords'] = $cateSEO['keywords'];
            $SEO['description'] = $cateSEO['description'];
        }

        //页面赋值
        $this->assign(array(
            'SEO' => $SEO,
            'filter' => $html,
            'new' => $category == 0 ? ($location . '/soft/') : ($location . '/soft/' . $category . '_1_1.html'),
            'hot' => $location . '/soft/' . $category . '_2_1.html',
            'best' => $best,
            'lists' => $lists,
            'count' => $count,
        ));

        //模板选择
        $this->display(T('Down@afs/Category/soft'));
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/18
     * @Comments 单个礼包
     */
    public function onePackage()
    {
        $params = I('params');

        if (empty($params)) {
            return false;
        }

        $params = explode('_', $params);
        if (count($params) > 2) {
            return false;
        }

        if (count($params) == 1) {
            $id = array_shift($params);
            $_GET['p'] = $p = 1;

        } else {
            $id = array_shift($params);
            $p = array_shift($params);

            if (is_numeric($params) || $p < 1) {
                return false;
            }

            $_GET['p'] = $p;
        }

        $row = 24;

        //判断是否是数字
        if (!is_numeric($id) || $id < 1) {
            return false;
        }

        //获取产品标签id
        $tid = get_tags($id, 'Down', 'product')[0]['id'];
        if (!$tid) {
            return false;
        }

        //查找礼包
        $packges = M('ProductTagsMap')->alias('a')->field('b.id,b.title,c.content')->join('__PACKAGE__ b ON a.did = b.id')->join('__PACKAGE_PMAIN__ c ON a.did = c.id')->where("a.tid = $tid AND a.type = 'package' AND b.category_id = 1")->order('b.id DESC')->page($p, $row)->select();
        if (!$packges) {
            return false;
        }

        //礼包数量
        $count = M('ProductTagsMap')->alias('a')->join('__PACKAGE__ b ON a.did = b.id')->join('__PACKAGE_PMAIN__ c ON a.did = c.id')->where("a.tid = $tid AND a.type = 'package' AND b.category_id = 1")->count('b.id');

        //查找游戏
        $games = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.size,c.system')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->where("a.tid = $tid AND a.type = 'down'")->order('b.id DESC')->select();
        if (!$games) {
            return false;
        }

        $zq = C('FEATURE_ZQ_DIR');

        //查找游戏专区
        $batch = M('ProductTagsMap')->alias('a')->field('b.id,b.url_token')->join('__BATCH__ b ON a.did = b.id')->where("a.tid = $tid AND a.type='batch' AND b.pid = 0 AND b.interface = 0")->order('b.id DESC')->find();
        if ($batch) {
            $batch['url_token'] = C('STATIC_URL') . ($zq ? '/' . $zq . '/' : '/') . $batch['url_token'];
        }

        $path = C('PACKAGE_SLD') . '/oneli/' . $id . '_{page}.html';

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show();// 分页显示输出
        $this->assign('page', $show);// 赋值分页输出


        $game = $games[0];
        foreach ($games as $k => $v) {
            if ($v['system'] == '1' && empty($game['androidUrl'])) {
                $address = M('DownAddress')->field('url,site_id')->where('did = ' . $v['id'])->select();
                if ($address) {
                    $game['androidUrl'] = $this->formatAddress($address[0]['url'], $address[0]['site_id']);
                    $game['androidCode'] = builtQrcode($game['androidUrl']);
                }
            } elseif ($v['system'] == '2' && empty($game['IOSUrl'])) {
                $address = M('DownAddress')->field('url,site_id')->where('did = ' . $v['id'])->select();
                if ($address) {
                    $game['IOSUrl'] = $this->formatAddress($address[0]['url'], $address[0]['site_id']);
                    $game['IOSCode'] = builtQrcode($game['IOSUrl']);
                }
            }
        }

        //SEO
        $SEO['title'] = $game['title'] . '礼包_' . $game['title'] . '礼包领取_' . $game['title'] . '礼包大全 - 安粉丝礼包中心';
        $SEO['keywords'] = $game['title'] . '礼包,' . $game['title'] . '礼包领取,' . $game['title'] . '礼包大全';
        $SEO['description'] = '最新最全的' . $game['title'] . '礼包，' . $game['title'] . '激活码，' . $game['title'] . '礼包大全及礼包如何领取的方法，好礼包尽在安粉丝礼包中心。';

        //页面赋值
        $this->assign(array(
            'SEO' => $SEO,
            'package' => $packges,
            'game' => $game,
            'count' => $count,
            'batch' => $batch ? $batch : false,
        ));

        $this->display(T('Package@afs/Widget/onePackage'));
    }

    /**
     * API接口初始化
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

    /**
     * 评论列表接口
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_comment($id, $model)
    {
        $this->API_init();
        $callback = I('callback');

        if (!is_numeric($id) || empty($model)) {
            return;
        }
        //$this->API_View($id,$model); //浏览次数+1
        $m = M('Comment');
        $map = array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);
        $rs = $m->where($map)->field('id,uname,message,add_time')->order('add_time desc')->limit(0, 10)->select();
        if ($fuc) {
            return $rs;
        } else {
            echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
        }
    }

    /**
     * 描述：浏览次数
     * @param int $id
     * @param string $model
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function API_View($id = 0, $model = '')
    {
        $this->API_init();
        if (!is_numeric($id) || empty($model)) {
            return;
        }
        $model = ucfirst($model);
        $field = array('Document' => 'view', 'Package' => 'view', 'Down' => 'view');
        if (!array_key_exists($model, $field)) {
            return;
        }
        $m = M($model);
        $m->where('id=' . $id)->setInc($field[$model]);
    }

    /**
     * 提交评论
     * @return void
     */
    public function API_commentSub()
    {
        $this->API_init();
        $callback = I('callback');

        $id = I('id');
        $model = I('model');
        if (!is_numeric($id) || empty($model)) {
            return;
        }

        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
    }

    public function ajaxhandBook()
    {
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        //接收参数
        $id = remove_xss(I('params'));
        if (!is_numeric($id)) {
            return false;
        }

        $title = remove_xss(I('name'));

        if (empty($title)) {
            return false;
        }

        //查询本条基础数据
        $tag = $this->get_tags('id = ' . $id);
        if (empty($tag)) {
            return false;
        }

        //判断是否是图鉴二级分类
        $ptag = $this->get_tags('id = ' . $tag['pid']);
        if ($ptag['title'] == '图鉴') {
            $home = $tag;
        } else {
            return false;
        }

        //查询导航
        $nav = $this->get_tags('pid =' . $home['id'], false);

        //图鉴数据
        if ($nav) {
            $where = "a.type='document' AND a.tid IN (" . implode(',', array_column($nav, 'id')) . ") AND c.sub_title like '%" . $title . "%'";
        } else {
            $where = "a.type='document' AND a.tid = $id AND c.sub_title like '%" . $title . "%'";
        }
        $data = M('tagsMap')->alias('a')->field('b.id,b.cover_id,b.smallimg,c.sub_title')->join('__DOCUMENT__ b ON a.did = b.id')->join('__DOCUMENT_ARTICLE__ c ON a.did = c.id')->where($where)->select();

        foreach ($data as $k => $v) {
            $data[$k]['url'] = staticUrlMobile('detail', $v['id'], 'Document');
            $data[$k]['imgUrl'] = get_cover($v['smallimg'] > 0 ? $v['smallimg'] : $v['cover_id'], 'path');
        }

        echo $callback ? $callback . '(' . json_encode($data) . ');' : json_encode($data);
    }

    /**
     * @author 肖书成
     * @create_time 2015/3/31
     * @comment 手机专区游戏图鉴
     * @params int $params
     *
     */
    public function handBook()
    {
        //接收参数
        $id = I('params');
        if (!is_numeric($id)) {
            return false;
        }

        //查询本条基础数据
        $tag = $this->get_tags('id = ' . $id);
        if (empty($tag)) {
            return false;
        }

        //判断是否是图鉴二级分类
        $ptag = $this->get_tags('id = ' . $tag['pid']);
        if ($ptag['title'] == '图鉴') {
            $home = $tag;
        } else {
            return false;
        }

        //查询导航
        $nav = $this->get_tags('pid =' . $home['id'], false);

        //图鉴数据
        if ($nav) {
            $data = M('tagsMap')->alias('a')->field('a.tid,b.id,b.cover_id,b.smallimg,c.sub_title')->join('__DOCUMENT__ b ON a.did = b.id')->join('__DOCUMENT_ARTICLE__ c ON a.did = c.id')->where("a.type='document' AND a.tid IN (" . implode(',', array_column($nav, 'id')) . ")")->select();
            foreach ($nav as $k => $v) {
                foreach ($data as $s => $j) {
                    if ($j['tid'] == $v['id']) {
                        $nav[$k]['data'][] = $j;
                    }
                }
            }
        } else {
            $data = M('tagsMap')->alias('a')->field('b.id,b.cover_id,b.smallimg,c.sub_title')->join('__DOCUMENT__ b ON a.did = b.id')->join('__DOCUMENT_ARTICLE__ c ON a.did = c.id')->where("a.type='document' AND a.tid = $id")->select();
        }

        //SEO
        $rootTag = $this->get_tags('id = ' . $ptag['pid']);
        $tTitle = str_replace('图鉴', '', $tag['title']);

        $SEO['title'] = $rootTag['title'] . $tTitle . '搭配_' . $rootTag['title'] . $tTitle . '选择_排行 - 安粉丝手游网';
        $SEO['keywords'] = $rootTag['title'] . $tTitle . '搭配,' . $rootTag['title'] . $tTitle . '选择,' . $rootTag['title'] . $tTitle . '排行';
        $SEO['description'] = $rootTag['title'] . $tTitle . '大全为玩家提供了' . $tTitle . '搭配，' . $tTitle . '选择及' . $tTitle . '排行的对比数据，帮助玩家更好的了解' . $tTitle . '属性。';

        $this->assign(array(
            'home' => $home,
            'nav' => $nav,
            'data' => $data,
            'SEO' => $SEO,
        ));

        $this->display(T('Home@afs/Batch/dynamic/swtj'));
    }

    //用于查询图鉴
    private function get_tags($where, $isfind = true)
    {
        if ($isfind) {
            return M('tags')->field('id,title,pid,rootid')->where($where)->find();
        } else {
            return M('tags')->field('id,title,pid,rootid')->where($where)->select();
        }
    }

    /**
     * @author 肖书成
     * @create_time 2015/4/1
     * @comment 手机专区攻略标签
     * @params int $params
     */
    public function ajaxBatchTag()
    {
        //接收并处理参数
        $params = I('params');
        $params = explode('_', $params);

        //JSONP或其他src方式的回调函数名
        $callback = I('callback');

        //判断参数
        if (count($params) != 3) {
            return false;
        }
        foreach ($params as $k => $v) {
            if (!is_numeric($v) || (int)$v < 1) {
                return false;
            }
        }

        $params && list($tid, $tag, $p) = $params;
        $count = $list = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->join('__TAGS_MAP__ c ON b.id = c.did')->where("a.tid = $tid AND c.tid = $tag")->count();
        $list = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->join('__TAGS_MAP__ c ON b.id = c.did')->where("a.tid = $tid AND c.tid = $tag")->page($p, 20)->select();


        if ((int)$p * 20 < $count) {

        }
        $list['prv'] = $tid . '_' . $tag . '_' . ((int)$p - 1);
        $list['next'] = $tid . '_' . $tag . '_' . ((int)$p + 1);
        echo $callback ? $callback . '(' . json_encode($list) . ');' : json_encode($list);
    }


    /**
     * @author 肖书成
     * @create_time 2015/4/1
     * @comment 手机专区攻略
     * @params int $params
     */
    public function mBatchGuide()
    {
        //接收并处理参数
        $params = I('params');
        $params = explode('_', $params);

        //判断参数
        if (count($params) != 3) {
            return false;
        }
        foreach ($params as $k => $v) {
            if (!is_numeric($v) || (int)$v < 1) {
                return false;
            }
        }

        //参数赋值
        $params && list($id, $tag, $p) = $params;
        $row = 20;
        $index = C('MOBILE_STATIC_URL');
        $batch = M('batch')->field('title,url_token')->where('id = ' . $id)->find();
        if (!$batch) {
            return false;
        }
        $batch['url_token'] = $index . '/' . $batch['url_token'];
        $product = get_tags($id, 'batch', 'product')[0];
        if (empty($product)) {
            return false;
        }

        //统计并查询数据
        $where = "a.tid = " . $product['id'] . " AND a.type='document' AND b.category_id = 181 AND c.tid = $tag AND c.type='document'";
        $count = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->join('__TAGS_MAP__ c ON b.id = c.did')->where($where)->count();
        if ($count < 1) {
            return false;
        }
        $list = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->join('__TAGS_MAP__ c ON b.id = c.did')->where($where)->order('id DESC')->page($p, $row)->select();
        if ($list < 1) {
            return false;
        }

        $tags = M('tags')->field('id,title')->where('id IN(' . M('ProductTagsMap')->alias('a')->field('distinct c.tid')->join('__DOCUMENT__ b ON a.did = b.id')->join('__TAGS_MAP__ c ON b.id = c.did')->where("a.tid = " . $product['id'] . " AND a.type='document' AND b.category_id = 181 AND c.type='document'")->buildSql() . ') AND category = 1')->select();
        foreach ($tags as $k => &$v) {
            $v['url'] = $index . '/zqguide/' . $product['name'] . '/' . $product['id'] . '_' . $v['id'] . '_1.html';
        }
        unset($v);

        $this->assign('list', $list);// 赋值数据集
        $path = $index . '/zqguide/' . $product['name'] . '/' . $id . '_' . $tag . '_{page}.html';
        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数

        //友情链接
        $link = M('link')->where('`group` = 3')->select();

        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $Page->setConfig('theme', '%UP_PAGE% %DOWN_PAGE%');

        $show = $Page->show();// 分页显示输出
        $this->assign(array(
            'page' => $show,   //分页
            'list' => $list,   //数据
            'link' => $link,   //友情链接
            'tags' => $tags,   //标签
            'tag' => $tag,    //标签标识
            'product' => $product,//产品标签
            'batch' => $batch,  //专区基础数据
        ));// 赋值分页输出
        $this->display(T('Home@afs/Batch/dynamic/swguide')); // 输出模板
    }

    public function phTop()
    {
        /*   set_time_limit(200);
           //控制层页面静态化
           $path = "./Static/phtop.html";
           $file_time = fileatime($path);
           $time_now = time();
           if ((intval($file_time) + 3600) > $time_now) {
               include($path);
               //$this->show('Static/phTop.html');
           } else {
               @unlink($path);
               ob_start();
               $this->display(T('Down@afs/Detail/phtop'));
               $result = ob_get_contents();
               ob_end_flush();
               try {
                   //file_put_contents($path, $result, LOCK_EX);
                   $file_handel = fopen($path, 'w');
                   if (!$file_handel) {
                       throw new \Exception("write file false!");
                   } else {
                       fwrite($file_handel, $result);
                       fclose($file_handel);
                   }
               } catch (\Exception $e) {
                   echo $e->getMessage();
                   exit;
               }
           }*/
        $this->display(T('Down@afs/Detail/phtop'));
    }
}
