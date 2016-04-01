<?php

namespace Document\Widget;
use Home\Controller\BaseController;

class P7230mobileWidget extends BaseController{
    public function newsList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
		$where = array('category_rootid'=>'80');
		$whereMap = array('map' => array('category_rootid' => '80'));
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
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
    	 $this->display(T('Document@7230mobile/Category/index'));
		
	}
	
	
	
	 public function cateList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
		$cate= I('cate');
        $page_info = get_staticpage($page_id);
		 $where = array('category_id'=>$cate);
		 $whereMap = array('map' => array('category_id' => $cate));
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cate',$cate);
        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
        //模板选择
    	$this->display(T('Document@7230mobile/Category/cateList'));
		
	}
	
	
	    public function gonglveList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
         $where = array('category_rootid'=>'85');
		 $whereMap = array('map' => array('category_rootid' =>'85'));
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
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
    	 $this->display(T('Document@7230mobile/Category/gonglve'));
		
	}
	
	
	public function gonglveCate(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
		$cate= I('cate');
        $page_info = get_staticpage($page_id);
        $where = array('category_id'=>$cate);
		$whereMap = array('map' => array('category_id' => $cate));
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cate',$cate);
        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
        //模板选择
    	$this->display(T('Document@7230mobile/Category/gonglveCate'));
		
	}
	
	
	public function pingceList(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
         $where = array('category_rootid'=>'81');
		 $whereMap = array('map' => array('category_rootid' => '81'));
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
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
    	 $this->display(T('Document@7230mobile/Category/pingc'));
		
	}
	
	public function pingceCate(){
	    //根据传入static_page表ID查找数据
        $page_id = I('page_id');
		$cate= I('cate');
        $page_info = get_staticpage($page_id);
        $where = array('category_id'=>$cate);
		$whereMap = array('map' => array('category_id' => $cate));
		
        //分页获取数据
        $row = 20;
        $count  = D('Document')->where($where)->count();// 查询满足要求的总记录数
        //是否返回总页数
		
		
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Document')->page($p, $row)->listsWhere($whereMap, true);
        $this->assign('lists',$lists);// 赋值数据集
        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
		$Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
        $show = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('cate',$cate);
        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
        //模板选择
    	$this->display(T('Document@7230mobile/Category/pingceCate'));
		
	}

    /**
     * 作者:肖书成
     * 时间:2015/10/29
     * 描述:文章详情页的 “相关手游推荐”
     */
    public function relateDown($tid){
        if($tid){
            $info   =   M('ProductTagsMap')->field('b.id,b.title,b.category_id')->alias('a')->join('__DOWN__ b ON a.did = b.id')
                        ->where("a.type = 'down' AND a.tid = $tid AND b.status = 1 AND b.pid = 0")->find();

            if($info){
                $count  =   M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                    ->where('a.category_id = '.$info['category_id'].' AND a.status = 1 AND a.pid = 0 AND b.size !="0"')->count('a.id');

                if($count<=4){
                    $star   =   0;
                }else{
                    $star   =   rand(0,(int)$count - 4);
                }

                $lists  =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                    ->where('a.category_id = '.$info['category_id'].' AND a.status = 1 AND a.pid = 0 AND b.size != "0"')
                    ->order('a.view DESC,a.update_time DESC')->limit($star,4)->select();
            }else{
                $count  =   M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                    ->where('a.category_rootid = 1 AND a.status = 1 AND a.pid = 0 AND b.size !="0"')->count('a.id');

                if($count<=4){
                    $star   =   0;
                }else{
                    $star   =   rand(0,(int)$count - 4);
                }

//            $lists   =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')
//                ->where('a.category_id = 1 AND a.status = 1 AND a.pid = 0 AND c.type="down"')->group('c.tid')->limit($star,4)->select();

                $lists  =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                    ->where('a.category_rootid = 1 AND a.status = 1 AND a.pid = 0 AND b.size != "0"')->order('a.view DESC,a.update_time DESC')->limit($star,4)->select();
            }
        }else{
            $count  =   M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                ->where('a.category_rootid = 1 AND a.status = 1 AND a.pid = 0 AND b.size !="0"')->count('a.id');

            if($count<=4){
                $star   =   0;
            }else{
                $star   =   rand(0,(int)$count - 4);
            }

//            $lists   =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')
//                ->where('a.category_id = 1 AND a.status = 1 AND a.pid = 0 AND c.type="down"')->group('c.tid')->limit($star,4)->select();

            $lists  =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')
                ->where('a.category_rootid = 1 AND a.status = 1 AND a.pid = 0 AND b.size != "0"')->order('a.view DESC,a.update_time DESC')->limit($star,4)->select();
        }

        $this->assign('lists',$lists);
        $this->display('Widget/relateDown');
    }

//* 作者:肖书成
//* 描述:文章猜你喜欢
//* 时间:2015/11/14

    public function cLike($id){
        //检验参数
        if(empty($id)){return false;}

        $tags = M('TagsMap')->alias('a')->field('a.tid,c.name,c.title')->join('__PRODUCT_TAGS_MAP__ b ON a.did = b.did')->join('__TAGS__ c ON a.tid = c.id')
            ->where("a.type = 'down' AND b.type = 'down' AND b.tid = $id AND c.pid != 0 AND (c.category = 1 or c.category = 2)")->group('a.tid')->limit(3)->select();

        if(empty($tags)){return false;}

        foreach($tags as $k=>$v){
            $tagDown[]  =   M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.size')->join('__DOWN__ b ON a.did = b.id')
                ->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did')
                ->where('a.tid = '.$v['tid'].' AND b.status = 1 AND b.pid = 0 AND c.size != 0 AND d.type="down"')
                ->order('b.update_time DESC')->group('d.tid')->limit(8)->select();
        }
        if(empty($tagDown)){return false;}

        $this->assign(array(
            'tags'      =>  $tags,
            'tagDown'  =>  $tagDown
        ));

        $this->display('Widget/cLike');
    }


}