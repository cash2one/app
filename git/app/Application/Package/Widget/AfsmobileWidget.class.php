<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/3
 * Time: 16:00
 */

namespace Package\Widget;
use Package\Controller\BaseController;

class AfsmobileWidget extends BaseController{
	
	public function relatePackage($id){
	   $d=get_base_by_tag($id,'Package','Package','product',false);
	   $this->assign("package",$d);
	   $this->display(T('Package@afsmobile/Widget/relatePackage'));
		}
	
	public function relateDown($id){
	   $d=get_base_by_tag($id,'Package','Down','product',true);
	   if(!empty($d)){
		     echo " <a href=\"".staticUrlMobile('detail', $d['id'],'Down')."\">下载该游戏</a>";
		 }
	 
	}
	
	
	public function packageIndex(){
		  //SEO
        $this->assign("SEO",WidgetSEO(array('special',null,'41')));
		$this->display(T('Package@afsmobile/Index/index'));
	}
	
    public function packageList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_rootid'=>'1');
		$whereMap = array('map' => array('category_rootid' => '1'));
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
        //模板选择
        $this->display(T('Package@afsmobile/Category/index'));
		
	}
     public function showDown($id){
		 if(empty($id)){return;}
		 $d=get_base_by_tag($id,'Package','Down','product',true);
		 $url=staticUrlMobile('detail', $d['id'],'Down');
		 echo empty($url)?'javascript:void(0);':$url;;
	}
	 public function showCompany($id){
		 if(empty($id)){return;}
		 $d=get_base_by_tag($id,'Package','Down','product',true);
		 $id=empty($d['company_id'])?'0':$d['company_id'];
         $c=M('Company')->where("id=$id")->getField('name');
		 $c=empty($c)?'未知':$c;
		 echo $c;
	}
	  public function kaifuList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_rootid'=>'1');
		$whereMap = array('map' => array('category_rootid' => '4'));
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
        $this->assign("SEO",WidgetSEO(array('special',null,'45')));
        //模板选择
        $this->display(T('Package@afsmobile/Category/kaifu'));
		
	}
	
	
	   public function kaiceList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$whereMap = array('map' => array('category_rootid' => '5'));
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
       $this->assign("SEO",WidgetSEO(array('special',null,'46')));
        //模板选择
        $this->display(T('Package@afsmobile/Category/kaice'));
		
	}
	
	
	
	
	
	
	
	

}