<?php
// +----------------------------------------------------------------------
// | 搜索控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Package\Controller;

class SearchController extends BaseController {

    /**
     * 搜索结果
     * @return void
     */
    public function index(){
        $keyword = I('keyword');
        if (!$keyword) $this->error('请输入关键词！');
        $this->assign('keyword',$keyword);// 赋值关键词


        //分页获取数据 
        $row = 10; 
        $count  = M('Package')
                            ->alias('p')
                            ->where('p.title like "%'. $keyword .'%" AND p.status=1')
                            ->join('RIGHT JOIN __PACKAGE_PMAIN__ as m ON m.id = p.id')
                            ->count();// 查询满足要求的总记录数

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<0 ) $p = 1;   
        if ($p > $count ) $p = $count; //容错处理  

        $lists = M('Package')
                        ->alias('p')
                        ->where('p.title like "%'. $keyword .'%" AND p.status=1')
                        ->join('RIGHT JOIN __PACKAGE_PMAIN__ as m ON m.id = p.id')
                        ->order('p.id')
                        ->field('*')
                        ->page($p, $row)
                        ->select();
        $this->assign('lists',$lists);// 赋值数据集
        $Page       = new \Think\Page($count, $row);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出      

        //SEO
        $params['title'] = C('PACKAGE_DEFAULT_TITLE') .'-'. $text . '搜索结果';
        $params['key'] = C('PACKAGE_DEFAULT_KEY') .'，'. $text;
        $params['description'] = C('PACKAGE_DEFAULT_DESCRIPTION') .'，'. $text. '搜索结果';
        $this->SEO($params);
        //模板选择
        $this->display();
    }

}