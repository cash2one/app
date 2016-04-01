<?php
// +----------------------------------------------------------------------
// | 卡号相关信息
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Package\Widget;
use Think\Controller;

class Pc6mobileWidget extends Controller{
	
	
	
   public function xsLibaoList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_id'=>'4');
		$whereMap = array('map' => array('category_id' => '4'));
        //分页获取数据
        $row = 10;
        $count  = D('Package')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Package')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
		$this->assign('pid','4');
        //模板选择
        $this->display(T('Package@pc6mobile/Category/index'));
		
	}
	    public function packageList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_id'=>'1');
		$whereMap = array('map' => array('category_id' => '1'));
        //分页获取数据
        $row = 10;
        $count  = D('Package')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Package')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则

        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
	    $this->assign('pid','1');
        $this->assign('SEO',$seo);
        //模板选择
        $this->display(T('Package@pc6mobile/Category/index'));
		
	}
	    /**
     * 相关礼包
     * @return void
     */
     public function relatePackage($id){
	   $d=get_base_by_tag($id,'Package','Package','product',false);
	   $this->assign("package",$d);
	   $this->display(T('Package@9htmobile/Widget/relatePackage'));
		}
    /**
     * 最新发号信息
     * @return void
     */
    public function packageNewList(){
        $list = M('Card')
                    ->alias('c')
                    ->where('c.draw_status=1 ')
                    ->join('__PACKAGE__ as p ON c.did = p.id')
                    ->field('p.title as title, p.id as id')
                    ->group('p.id')
                    ->limit(5)
                    ->select();
        $this->assign('list', $list);
        $this->display('Widget/cardNewList');
    }

    /**
     * 热门激活码
     * @return void
     */
    public function packageHotList(){
        $list = M('Package')
                    ->alias('p')
                    ->where('p.category_id=2 AND p.status=1 AND c.draw_status=1')
                    ->join('LEFT JOIN __CARD__ as c ON c.did = p.id')
                    ->group('p.id')
                    ->order('numSur')
                    ->field('p.title as title,p.id as id,count(c.id) as numsur')
                    ->limit(5)
                    ->select();
        //echo D('Package')->getLastSql();
        $this->assign('list', $list);
        $this->display('Widget/cardHotList');
    }

	
}
