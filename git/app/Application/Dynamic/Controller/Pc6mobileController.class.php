<?php
// +----------------------------------------------------------------------
// | afs动态访问控制类
// +----------------------------------------------------------------------
// | Author:
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class Pc6mobileController extends BaseController
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
     * 产品标签列表
     * @date: 2015-7-1
     * @author: liujun
     */
    public function tagsIndex()
    {
        $where = array('status' => 1);
        $order = 'sort ASC,update_time DESC';
        $count = M("ProductTags")->field('id')->where($where)->order($order)->count();
        $list = M("ProductTags")->field('id,category,name,title')->where($where)->order($order)->limit(10, $count)->select();
        $seo = array(
            'title' => ' 手游礼包全部游戏_热门手游礼包_最新兑换码领取_' . C('SITE_NAME') . '礼包中心',
            'keywords' => '热门手机游戏,手游礼包,礼包大全,兑换码领取,手机游戏,' . C('SITE_NAME') . '礼包',
            'description' => '手机游戏礼包大全尽在' . C('SITE_NAME') . '礼包中心。礼包中心为您提供最新热门手机游戏礼包、兑换码。',
        );

        $productTags = M('ProductTags')->where(array('status' => 1))->order('sort ASC,update_time DESC')->limit(10)->select();
        $this->assign('hlists', $productTags);
        $this->assign('SEO', $seo);
        $this->assign('lists', $list);

        //生成静态地址参数
        $static_url_params = array('model' => 'Package', 'module' => 'Package', 'category' => 'PackageCategory');
        $this->assign('static_url_params', $static_url_params);

        $this->display(T('Package@pc6mobile/Widget/tagsList'));
    }

    /**
     * 产品标签详情页面：获取礼包列表
     * @date: 2015-7-1
     * @author: liujun 刘盼二次修改
     */
    public function tagsDetail()
    {
        $name = I('name');//产品标签标识名
        $limit = !empty(I('limit')) ? I('limit') : 15;//记录条数
        $p = intval(I('p'));//第几页
        $count = 0;

        //获取游戏产品标签信息
        $info = M('ProductTags')->where(array('status' => 1, 'name' => $name))->find();
        if (!$info) {
            $this->error('页面不存在！');
        }

        //获取所有礼包
        $field = 'p.id,p.cover_id,p.title,m.end_time,m.content,m.activation';
        $join = 'right join __PACKAGE__ as p ON t.did = p.id left join __PACKAGE_PMAIN__ as m on p.id = m.id';
        $where = array('status' => 1);
        $where['t.type'] = 'package';//类型：礼包
        $where['t.tid'] = $info['id'];

        $count = M('ProductTagsMap')->alias('t')->join($join)->field($field)->where($where)->count();//总记录数

        //是否返回总页数
        if (I('gettotal')) {
            if (empty($count)) {
                echo 1;
            } else {
                echo ceil($count / $limit);
            }
            exit();
        }

        if (!is_numeric($p) || $p <= 0) {
            $p = 1;
        }
        if ($p > $count) {
            $p = $count;
        } //容错处理

        $list = M('ProductTagsMap')->alias('t')->join($join)->field($field)->where($where)->page($p, $limit)->order('t.update_time DESC')->select();
        //echo M('ProductTagsMap')->getLastSql();exit;

        $path = '/tag/' . $info['name'] . '/{page}' . getStaticExt();
        $Page = new \Think\Page($count, $limit, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show();// 分页显示输出

        $seoTitle = $info['title'];
        if (!strstr($seoTitle, '礼包')) {
            $seoTitle = $seoTitle . '礼包';
        }
        $seo = array(
            'title' => $seoTitle . '兑换码大全_' . $seoTitle . '兑换码领取_' . C('SITE_NAME') . '礼包中心',
            'keywords' => $seoTitle . '，' . $info['title'] . '激活码，' . $seoTitle . '兑换码领取',
            'description' => C('SITE_NAME') . '礼包中心' . '为玩家们免费提供' . $seoTitle . '兑换码大全，' . $seoTitle . '兑换码大全主要汇总了' . $seoTitle . '、' . $seoTitle . '兑换码、' . $seoTitle . '独家礼包等，欢迎玩家前来领取。',
        );
        $result = array(
            'SEO' => $seo,
            'listCount' => $count,
            'page' => $show,
            'info' => $info,//产品标签详情
            'lists' => $list,//礼包记录
        );

        //生成静态地址参数
        $static_url_params = array('model' => 'Package', 'module' => 'Package', 'category' => 'PackageCategory');
        $this->assign('static_url_params', $static_url_params);

        $this->assign($result);
        $this->display(T('Package@pc6mobile/Widget/tagsDetail'));
    }
}
