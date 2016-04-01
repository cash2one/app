<?php
// +----------------------------------------------------------------------
// | 官方网动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <wwei@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class GfwController extends BaseController
{

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
     * 更新推荐
     * @param integer $id 数据id
     * @param integer $typeid 推荐类型 1.好  2.差
     * @param string $model 模型名
     * @return void
     */
    public function API_RecPraise($id = 0, $model = '')
    {
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $typeid = I('typeid');
        if (!is_numeric($id) || empty($model)) {
            return;
        }
        $m = M($model);
        if ($typeid == 1) {
            $m->where('id=' . $id)->setInc('abet');
        } else {
            if ($typeid == 2) {
                $m->where('id=' . $id)->setInc('argue');
            }
        }
        $rs = $this->API_GetRecPraise($id, $model, true);
        echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
    }

    /**
     * 获取推荐数据
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @param boolean $fuc 是否是函数调用
     * @return void
     */
    public function API_GetRecPraise($id = 0, $model = '', $fuc = false)
    {
        if (!$fuc) {
            $this->API_init();
            $callback = I('callback');
        }
        if (!is_numeric($id) || empty($model)) {
            return;
        }
        $m = M($model);
        $rs = $m->field("abet,argue")->where('id=' . $id)->find();
        if ($fuc) {
            return $rs;
        } else {
            echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
        }
    }

    /**
     * 产品：换一批
     * @param integer $id 分类ID
     * @return void
     */
    public function API_GetProbatch($cid)
    {
        $this->API_init();
        $callback = I('callback');
        $p = I('request.p');
        $cid = I('request.cid');
        //获取指定分类数据
        $where = array(
            'map' => array('category_id' => $cid)
        );
        $productlist = D('Down')->page($p, 5)->listsWhere($where, true);
        if ($productlist) {
            $rs = array();
            foreach ($productlist as $k => $v) {
                $v['previewimg'] = explode(',', $v['previewimg']);
                $rs[$k]['url'] = staticUrl('detail', $v['id'], 'Down');
                $rs[$k]['img'] = get_cover($v['previewimg'][0], 'path');
                $rs[$k]['title'] = msubstr($v['title'], 0, 8, 'utf-8', false);
            }
        }

        echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);

    }

    /**
     * @Author 肖书成
     * @comments 联系我们
     */
    public function lxwm()
    {
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');

        //接收参数
        $rel['userName'] = remove_xss(I('userName'));
        $rel['contact'] = remove_xss(I('contact'));
        $rel['tm'] = remove_xss(I('tm'));
        $rel['content'] = remove_xss(I('content'));
        if (empty($rel['tm']) || empty($rel['content'])) {
            return false;
        }
        $data['uname'] = $rel['userName'] ? $rel['userName'] : '游客';
        $data['message'] = '联系方式:' . ($rel['contact'] ? $rel['contact'] : '没有联系方式  ') . '主题:' . ($rel['tm'] ? $rel['tm'] : '没有主题  ') . '内容:' . $rel['content'];
        $data['enabled'] = 1;

        $val = M('Comment')->data($data)->add() ? true : false;

        echo $callback ? $callback . '(' . json_encode($val) . ');' : json_encode($val);
    }

    /**
     * 获取公司相关产品
     * @param integer $id 公司ID
     * @return array
     */
    public function websiteProduct()
    {
        $callback = I('callback');
        //传入公司id
        $id = I('request.id');
        if (!is_numeric($id) || $id < 1) {
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            return false;
        }
        $DownCategory = D('DownCategory');
        $Down = D('Down');
        $p = intval(I('p'));
        if (!is_numeric($p) || $p <= 0) {
            $p = 0;
        }

        $info = D('Package')->detail($id);//获取公司详情

        $here = getParentCategory($info['category_id'], 'package_category');

        $info_list = $DownCategory->info($id);//获取每页数据显示个数
        $row = $info_list['list_row'] ? $info_list['list_row'] : 12;//默认显示12条

        //获取指定分类数据
        $map['package_id'] = $id;
        $map['status'] = 1;
        $count = $Down->where($map)->count();//统计数据
        $prodata = $Down->where($map)->page($p, $row)->order('create_time DESC')->select();
        if (empty($prodata)) {//为空跳到404
            header("HTTP/1.1 404 Not Found");
            header("Status: 404 Not Found");
            return false;
        }
        $Page = new \Think\Page($count, $row);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('first', '首页');
        $Page->setConfig('end', '尾页');
        $Page->setConfig('prev', "上一页");
        $Page->setConfig('next', '下一页');
        $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        $pattern = array(
            '/dynamic\.php\?s=\/gfw\/websiteProduct\/id\/([0-9]+)\/p\/([0-9]+)\.html/i',
        );
        $replacement = array(
            'company_products/$1_$2/',
        );
        $show = preg_replace($pattern, $replacement, $Page->show());// 分页显示输出
        $this->assign('count', $count);
        $this->assign('show', $show); //分页
        $this->assign('info', $info);
        $this->assign('here', $here);//当前位置
        $this->assign('id', $id);//数据id
        $this->assign('p', $p);//翻页
        $SEO = array(
            'title' => $info['title'] . '产品大全-官方网',
            'keywords' => $info['title'] . '产品大全-官方网',
            'description' => $info['title'] . '产品大全-官方网'
        );
        $this->assign('SEO', $SEO);//seo
        $this->assign('prodata', $prodata);//产品列表

        $this->display(T('Down@gfw/Widget/websiteProduct'));
    }
}
