<?php
// +----------------------------------------------------------------------
// | 东东动态访问控制类
// +----------------------------------------------------------------------
// | Author: liupan
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class DongdongController extends BaseController {
   
    /**
     * 搜索结果
     * @return void
     */
    public function search(){
        $keyword =  remove_xss(strip_tags(I('keyword')));
        $type =  remove_xss(I('type'));
        $type = ucfirst(strtolower($type));
        $allow = array('Down','Document','Package');         
        if (!$keyword) $this->error('请输入关键词！');
        if(!empty($keyword) && !empty($type) && in_array($type, $allow)){
            //结果页面
            $this->assign('keyword',$keyword);// 赋值关键词
            $this->assign('type',$type);// 赋值类型
            //分页获取数据 
            $where = array(
                'map' => array('title' => array('like','%'. $keyword .'%'))            
                );
            $row = 10; 
            $count  = D($type)->listsWhereCount($where);// 查询满足要求的总记录数           
			
		
            $this->assign('totals',$count);

            $p = intval(I('p'));
            if (!is_numeric($p) || $p<0 ) $p = 1;   
            if ($p > $count ) $p = $count; //容错处理  

            $lists = D($type)->page($p, $row)->listsWhere($where, true);
			
            // 赋值数据集
            $this->assign('lists',$lists);

            $path = '/search.html?keyword='.$keyword.'&type='.$type.'&p={page}';
            $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('first','首页');
            $Page->setConfig('end','尾页');
            $Page->setConfig('prev',"上一页");
            $Page->setConfig('next','下一页');

            $show       = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出      
               
            //SEO
            $seo['title'] =C('WEB_SITE_TITLE') .'-'. $keyword . '搜索结果';
            $seo['keywords'] = C('WEB_SITE_KEYWORD') .','. $keyword;
            $seo['description'] =C('WEB_SITE_DESCRIPTION').' '. $keyword. '搜索结果';
            $this->assign("SEO",$seo);
            //模板选择
            $this->display(T('Home@dongdong/Search/index'));
        }else{
            //SEO
            $this->assign("SEO",WidgetSEO(array('index'), 'dongdong'));
            //初始页面
            $this->display(T('Home@dongdong/Search/index'));
        }

    }
	
    /**
    * 下载分类首页
    * @date: 2015-4-20
    * @author: liujun
    */
    public function category(){
    	$cid = I('cid',1);//分类id
    	$page = I('p',1);//当前第几页
    	$pageSize = I('row',40);//分页大小
    	$order = I('order',array('update_time DESC'));//排序大小
    	$where = array();
    	$parameter = array();//参数
    	$parameter['cid'] = $cid;
    	
    	$tree = D('DownCategory')->getTree();//获取分类树

    	if($cid <=0 && !empty($tree)){//设置默认rootid
    		$pid = $tree[0]['pid'];
    		$rootid = $tree[0]['rootid'];
    		$cateTitle = $tree[0]['title'];
    	}else{
    		$categoryInfo = D('DownCategory')->field(array('pid','rootid','title'))->where(array('id'=>$cid,'status'=>1))->find();
    		$pid = $categoryInfo['pid'];
    		$rootid = $categoryInfo['rootid'];
    		$cateTitle = $categoryInfo['title'];
    		
    		//获取子类Id
    		$categoryIds = D('DownCategory')->where(array('rootid'=>$cid,'status'=>1))->field('id')->select();
    		$childrenArr = array();
    		foreach ($categoryIds as $key => $value) {
    			$childrenArr[] = $value['id'];
    		}
    		$childrenIds = implode(',', $childrenArr);
    	}
    	
    	$where['status'] = 1;//正常
    	if(!empty($childrenIds)){
    		$where['category_id']  = array('in',$childrenIds);
    	}else{
    		$where['category_id'] = $cid;
    	}
    	$count = D('Down')->where($where)->count();// 查询满足要求的总记录数
    	$list = D('Down')->where($where)->order($order)->page($page,$pageSize)->select();
    	
    	$Page = new \Think\Page($count,$pageSize,$parameter);// 实例化分页类 传入总记录数和每页显示的记录数
    	$Page->setConfig('prev','上一页');
    	$Page->setConfig('next','下一页');
    	$Page->setConfig('first','首页');
    	$Page->setConfig('last','尾页');
    	$show = $Page->show();// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	
    	//SEO
    	$seo = array();
    	$seo['title'] = $cateTitle.'|'.C('WEB_SITE_TITLE');
        $seo['keywords'] = $cateTitle.','.C('WEB_SITE_KEYWORD');
        $seo['description'] = $cateTitle.' '.C('WEB_SITE_DESCRIPTION');
        $this->assign('SEO',$seo);
        
        //导航栏选中状态
        if($rootid == '1' || $pid == '1'){
        	$navid = 3;
        }else if($rootid == '116' || $pid == '116'){
        	$navid = 4;
        }else{
        	$navid = 1;
        }
        $this->assign('navid',$navid);
        
        $this->assign('tree',$tree);
        $this->assign('pid',$pid);
        $this->assign('rootid',$rootid);
        $this->assign('cid',$cid);
        $this->assign('downList',$list);
    	$this->display(T('Down@dongdong/Category/index'));
    }
    
    /**
    * 最新首发软件
    * @date: 2015-4-20
    * @author: liujun
    */
    public function newest(){
    	$cid = I('cid',0);//标签分类Id
    	$page = I('p',1);//当前第几页
    	$pageSize = I('row',18);//分页大小
    	$order = I('order',array('update_time DESC'));//排序大小
    	$where = array();
    	$parameter = array();//参数
    	$parameter['cid'] = $cid;
    	
    	$where['status'] = 1;
    	$where['id'] = $cid;
    	$tagInfo = D('Tags')->where($where)->find();
    	if(empty($tagInfo)){
    		$this->error('无效Id！');
    	}
    	unset($where);
    	
    	//游戏首发调'游戏首发'标签内容;软件首发调'软件首发'标签内容
    	$downIds = $this->getDownIdsByTagId($cid);
    	
    	$downList = array();
    	if(!empty($downIds)){
    		$where['status'] = 1;
    		$where['id'] = array('in',$downIds);
    		$count = D('Down')->where($where)->count();// 查询满足要求的总记录数
    		$downList = D('Down')->where($where)->order($order)->page($page,$pageSize)->select();
    	}
    	unset($where);
    	
    	$Page = new \Think\Page($count,$pageSize,$parameter);// 实例化分页类 传入总记录数和每页显示的记录数
    	$Page->setConfig('prev','上一页');
    	$Page->setConfig('next','下一页');
    	$Page->setConfig('first','首页');
    	$Page->setConfig('last','尾页');
    	$show = $Page->show();// 分页显示输出
    	$this->assign('page',$show);// 赋值分页输出
    	
    	//相关推荐：首发页调首发内容，按下载量排序
    	$field = array(C(DB_PREFIX).'down.*','address.hits as downhits');
    	$where['status'] = 1;//正常
    	$order = 'downhits DESC';
    	$recommendList = D('Down')->field($field)->join(C(DB_PREFIX).'down_address as address ON '.C(DB_PREFIX).'down.id = address.did')->where($where)->order($order)->limit(10)->select();
    	unset($where);
    	$this->assign('recommendList',$recommendList);
    	
    	$seo = array();
    	$title = '最新首发';
    	$seo['title'] = $title.'|'.C('WEB_SITE_TITLE');
        $seo['keywords'] = $title.','.C('WEB_SITE_KEYWORD');
        $seo['description'] = $title.' '.C('WEB_SITE_DESCRIPTION');
        $this->assign('SEO',$seo);
    	
    	$this->assign('navid',2);//导航Id
    	$this->assign('cid',$cid);
    	$this->assign('downList',$downList);
    	$this->display(T('Down@dongdong/Index/newest'));
    }
    
    /**
     * 根据标签Id获取所有下载Id
     * @date: 2015-4-23
     * @author: liujun
     */
    private function getDownIdsByTagId($tagId = 0){
    	$downIds = array();
    	$where = array();
    	$where['status'] = 1;
    	$where['tid'] = $tagId;
    	$where['type'] = 'down';
    	$tagMapList = D('TagsMap')->field(array('did'))->where($where)->select();
    	foreach($tagMapList as $key=>$value){
    		$downIds[] = $value['did'];
    	}
    	$downIds = join(',', $downIds);
    	unset($where);
    	return $downIds;
    }
	
	 /**
     * API接口初始化
     * @return void
     */
    protected function API_init(){
        //结果关闭trace
        C('SHOW_PAGE_TRACE', false);      
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if($referer){
            $referer = parse_url($referer);
            $host = $referer['host'];
            if(in_array($host, $cors)){
                header('Access-Control-Allow-Origin:http://'. $host);
            }        
        }
    }
	
	    /**
     * 评论列表接口
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_comment($id, $model){
        $this->API_init();
        $callback = I('callback');

        if(!is_numeric($id) || empty($model)) return;
        $this->API_View($id,$model); //浏览次数+1
        $m = M('Comment');
        $map =array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);
        $rs = $m->where($map)->field('id,uname,message,add_time')->order('add_time desc')->limit(0,10)->select();
        if($fuc){
            return $rs;
        }else{
            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
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
        if(!is_numeric($id) || empty($model)) return;
        $model = ucfirst($model);
        $field = array('Document'=>'view','Package'=>'view','Down'=>'view');
        if(!array_key_exists($model, $field)) return;
        $m = M($model);
        $m->where('id='. $id)->setInc($field[$model]);
    }
    /**
     * 赞
	   author:liupan
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_Praise($id = 0, $model = ''){
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
		$cate = I('cate');
        if(!is_numeric($id) || empty($model)) return;
        $m = M($model);
		if($cate=='1'){
			  $m->where('id='. $id)->setInc('abet');
			}else{
			  $m->where('id='. $id)->setInc('argue');	
	    }
        $rs = $this->API_GetPraise($id, $model, true);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 获取赞数
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @param boolean $fuc 是否是函数调用
     * @return void
     */
    public function API_GetPraise($id = 0, $model = '', $fuc = false){
        if(!$fuc){
            $this->API_init();
            $callback = I('callback');            
        }
        if(!is_numeric($id) || empty($model)) return;
        $m = M($model);
        $rs = $m->field("abet,argue")->where('id='. $id)->find();
        if($fuc){
            return $rs;
        }else{
            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
        }
    }
    /**
     * 提交评论
     * @return void
     */
    public function API_commentSub(){
        $this->API_init();
        $callback = I('callback');

        $id = I('id');
        $model = I('model');        
        if(!is_numeric($id) || empty($model)) return;

        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
    }
	
	
	
	public function statDown(){
	    $this->API_init(); //JSONP或其他src方式的回调函数名
	    $id = I('id');
        $callback = I('callback');
        if(!is_numeric($id)) return;
		$hits=M('Down')->where("id='$id'")->getField('hits_today');
		$data['hits_today']=$hits+1;
		$res=M('Down')->where("id='$id'")->save($data);
        echo $callback ? $callback.'('.json_encode($hits).');' : json_encode($hits);
		
		}

    /**
     * 测试
     */
    public function categorylist(){
        //接收参数并判断参数
        $param = I('param');
        if(empty($param)){
            $cate = 1;
            $p=1;
        }else{
            $param = explode('_',$param);
            if(count($param)!=2) return false;
            foreach($param as $k=>$v){
                if(!is_numeric($v)) return false;
            }
            list($cate,$p) = $param;
        }
        $row = 40;
        $_GET['p'] = $p;

        //获取所有分类
        $tree = M('DownCategory')->field('id,title,pid,meta_title,keywords,description')->where('status = 1')->select();

        //判断参数是否是分类的ID
        if(!is_numeric($key = array_search($cate,array_column($tree,'id')))) return false;

        //判断该分类是否是顶级分类(pid = 0)
        $parent    =   $tree[$key]['pid']<1;
        $cate      =   $tree[$key];

        //SEO
        $SEO['title']       =   $tree[$key]['meta_title'];
        $SEO['keywords']    =   $tree[$key]['keywords'];
        $SEO['description']    =   $tree[$key]['description'];

        //给分类分配地址
        foreach($tree as $k=>$v){
            $tree[$k]['url'] = '/index.php?s=/Dynamic/Dongdong/categorylist/param/'.$v['id'].'_1.html';
        }

        //让分类变成分类树
        $tree   =   list_to_tree($tree, $pk = 'id', $pid = 'pid', $child = '_', $root = 0);

        //查询数据
        $where  =   'status = 1 AND ' . ($parent?'category_rootid = '.$cate['id']:'category_id = '.$cate['id']);
        $lists   =   M('Down')->field('id,title,smallimg')->where($where)->order('id DESC')->page($p,$row)->select();
        $count      = M('Down')->where($where)->count();// 查询满足要求的总记录数

        //分页
        $path = '/index.php?s=/Dynamic/Dongdong/categorylist/param/'.$cate['id'].'_{page}.html';
        $Page       = new \Think\Page($count,$row,'',false,$path);// 实例化分页类 传入总记录数和每页显示的记录数
        $show       = $Page->show();// 分页显示输出

        // 赋值分页输出
        $this->assign(array(
            'cate'  =>  $cate,        //当前分类
            'page'  =>  $show,          //分页
            'tree'  =>  $tree,          //分类树
            'lists' =>  $lists,         //数据值
        ));

        $this->display(T('Down@dongdong/Category/index2')); // 输出模板
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	


   

}