<?php
// +----------------------------------------------------------------------
// | 列表控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Package\Controller;

class CategoryController extends BaseController {

    /**
     * 列表展示
     * @return void
     */
    public function index(){
        $cate = I('cate');
		
        //分类信息查询
		if(is_numeric($cate)){
			$info = D('PackageCategory')->info($cate);
		}elseif(is_string($cate)){
			 //分类信息查询
			$cate = trim($_SERVER['REQUEST_URI'], '/');
			$info = D('PackageCategory')->where(array('name' => $cate))->find();
			$cate = $_GET['cate'] = $info['id'];
		}

        if (!$info) $this->error('页面不存在！');
		// 标签支持变量 zhudesheng 2015-9-16
		$_GET['MODELID'] = array_shift($info['model']);
        $this->assign('info', $info);

        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $rs = D('PackageCategory')->getAllChildrenId($cate); //获取分类及分类所有id
        $where = array(
            'map' => array('category_id' => array('in',$rs))
        );
        $count  = D('Package')->listsWhereCount($where);// 查询满足要求的总记录数
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<0 ) $p = 1;   
        if ($p > $count ) $p = $count; //容错处理  

        $lists = D('Package')->page($p, $row)->listsWhere($where, true);
        $this->assign('lists',$lists);// 赋值数据集

        $Page       = new \Think\Page($count, $row, '', $cate);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
        /******************修改人:肖书成 时间:2015/10/30********************/
        $Page->setConfig('first','首页');
        $Page->setConfig('end','尾页');
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        if(substr(I('t'),-6) == 'mobile'){//为了兼容手机版列表
            $Page->isMobile  = true;
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }else{
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        }
        /**************************************************/
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cid',$cate);// 输出栏目ID
        $this->assign('count',$count);//总记录数

        //SEO
        $this->assign("SEO",WidgetSEO(array('category','Package', $cate,$p)));

        //模板选择
        $temp = $info['template_index'] 
                        ? $info['template_index'] 
                        : ($info['template_lists'] ? $info['template_lists'] : 'index');            
        $this->display($temp);
    }

}