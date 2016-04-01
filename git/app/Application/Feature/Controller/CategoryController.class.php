<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/13
 * Time: 19:01
 */

namespace Feature\Controller;


class CategoryController extends BaseController{
    /**
     * 主方法
     * @return void
     */
    public function index(){
        $cate = I('cate');
        $hot = I('hot');  //用来判断是否生成最热列表页
        if (!is_numeric($cate)) $this->error('页面不存在！');
        //分类信息查询
        $info = D('FeatureCategory')->info($cate);
        if (!$info) $this->error('页面不存在！');
        $this->assign('info', $info);
        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $rs = D('FeatureCategory')->getAllChildrenId($cate); //获取分类及分类所有id
        $isMobile = (substr(I('t'),-6) === 'mobile');
        $where = array(
            'map' => array(
                'category_id'   =>  array('in',$rs),
                'interface'     =>  $isMobile?1:0,
            )
        );
        $count  = D('Feature')->listsWhereCount($where);// 查询满足要求的总记录数

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理
        if($hot) $where['order'] = 'view DESC';  //最热排序
        $lists = D('Feature')->page($p, $row)->listsWhere($where, false);

        $this->assign('lists',$lists);// 赋值数据集
        $Page       = new \Think\Page($count, $row, '', $cate);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        $Page->setConfig('first','首页');
        $Page->setConfig('end','尾页');
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');

        if($isMobile){
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }else{
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        }
        $show   = $Page->show($hot);// 分页显示输出
        $this->assign('count',$count);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cid',$cate);// 输出栏目ID

        $cateName=D('FeatureCategory')->where('id='.$cate)->getField('title');
        $this->assign("categoryName",$cateName);

        //SEO
        $this->assign("SEO",WidgetSEO(array('category','Feature', $cate,$p)));
        $mobile_url = staticUrlMobile('lists',$cate,'Feature',$p); //获取手机版地址
        $this->assign('MOBLIE_URL',$mobile_url);

        // 模板选择
        $temp = $info['template_index']
            ? $info['template_index']
            : ($info['template_lists'] ? $info['template_lists'] : 'index');
        //新增最热列表模板  如果配有最热列表生成字段，用最热模板 谭坚  2015-8-28
        if($hot) $temp = $info['template_lists_hot'] ? $info['template_lists_hot'] : 'index_hot';
        $this->display($temp);
    }
} 