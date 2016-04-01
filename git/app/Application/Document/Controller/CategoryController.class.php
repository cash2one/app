<?php
namespace Document\Controller;
use Think\Controller;
class CategoryController extends BaseController {
    public function index(){
     //  $id=I('get.id');
//	  if (!is_numeric($id)) $this->error('栏目不存在！');
//      $count= D('Document')->where('category_id='.$id)->count();
//      $Page= new \Think\Page($count,10);
//      $Page->setConfig('prev','上一页');
//      $Page->setConfig('next','下一页');
//      $Page->setConfig('first','第一页');
//      $Page->setConfig('last','尾页');
//      $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
//      $show= $Page->show();
//      $list=D('Document')->order('id desc')->where('category_id='.$id)->limit($Page->firstRow.','.$Page->listRows)->select();
//      $this->page=$show;
//      $this->list=$list;
//	  $this->assign('count',$count);
//	  $this->display();

        $cate = I('cate');


		$hot = I('hot');  //用来判断是否生成最热列表页
		 if(is_numeric($cate)){
			$info = D('Category')->info($cate);

		}elseif(is_string($cate)){
			 //分类信息查询
			$cate = trim($_SERVER['REQUEST_URI'], '/');

			if(strrpos($cate, '.html')){
				$cate =  dirname($cate);
			}
			
			$info = D('Category')->where(array('name' => $cate))->find();
			$cate = $_GET['cate'] = $info['id'];
		}

		if (!$info) $this->error('页面不存在！');

		// 标签支持变量 zhudesheng 2015-9-16
        if($info['model'][0])
		$_GET['MODELID'] = $info['model'][0];

        $this->assign('info', $info);
        //分页获取数据 
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $rs = D('Category')->getAllChildrenId($cate); //获取分类及分类所有id
        $where = array(
            'map' => array('category_id' => array('in',$rs))
            );

        $count  = D('Document')->listsWhereCount($where);// 查询满足要求的总记录数
	    $countToday = D('Document')->where('category_id='.$cate)->where('create_time>='.mktime(0,0,0,date('m'),date('d'),date('Y')) AND 'create_time<='.mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1)->count();// 查询今日更新的总记录数
		
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;   
        
        if ($p > $count ) $p = $count; //容错处理
        if($hot) $where['order'] = 'view DESC';  //最热排序

        $lists = D('Document')->page($p, $row)->listsWhere($where, true);

        $this->assign('lists',$lists);// 赋值数据集
        $Page       = new \Think\Page($count, $row, '', $cate);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
		$Page->setConfig('first','首页');
		$Page->setConfig('end','尾页');
		$Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');

        if(substr(I('t'),-6) == 'mobile'){//Author: 肖书成 为了兼容手机版列表
            $Page->isMobile  = true;
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }else{
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        }
        
        $show   = $Page->show($hot);// 分页显示输出  修改人：谭坚   添加最热列表参数
	    $this->assign('count',$count);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cid',$cate);// 输出栏目ID
        $cateName=D('Category')->where('id='.$cate)->getField('title');
		$this->assign("categoryName",$cateName);

        //SEO
        $this->assign("SEO",WidgetSEO(array('category','Document', $cate,$p)));
        $mobile_url = staticUrlMobile('lists',$cate,'Document', $p); //获取手机版地址
        $this->assign('MOBLIE_URL',$mobile_url);

       // 模板选择
        $temp = $info['template_index'] ? $info['template_index'] : ($info['template_lists'] ? $info['template_lists'] : 'index');
        //新增最热列表模板  如果配有最热列表生成字段，用最热模板 谭坚  2015-8-28
        if($hot) $temp = $info['template_lists_hot'] ? $info['template_lists_hot'] : 'index_hot';
        //亲宝贝列表用自己的模板 谭坚 2015-6-26
        // 亲宝贝手机版没有用到模板  谭坚 2015-6-26
        if(I('t') == 'qbaobeimobile')
        {
            $templates = array('jiankang','tuku','yingyang','baike','food','video','chengzhang');
            if(!in_array($temp,$templates)) $temp='index';
        }
//       if(I('t') == 'qbaobeimobile') $temp = 'index';  //注释人：肖书成
       unset($hot);
       $this->display($temp);
    }
	
	/*
	 *  user: lcz
	 *  time: 2016年2月29日16:59:16
	 *  op: 新增文章分类专题页面
	 */

    protected function specialIndex($arr){

    }
	
	
	
	
	
	
	
	
	
	
}