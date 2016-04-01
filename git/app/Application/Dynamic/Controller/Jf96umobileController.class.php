<?php
// +----------------------------------------------------------------------
// | 描述:96u手机版动态数据获取类
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-12-2 下午3:37    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Dynamic\Controller;


class Jf96umobileController extends BaseController{

    /**
     * 描述：产品标签列表页动态获取逻辑
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function API_productTagsList()
    {
        $this->API_init();
        $callback = I('callback');
        $tag = I('tag');
        if (!is_numeric($tag)) $this->error('页面不存在！');
        //获取标签相关信息
        $info = M('ProductTags')->where('id = '.$tag)->field(true)->find();
        if (!$info) $this->error('页面不存在！');
        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $where['c.status'] = 1;
        $where['b.tid'] = $tag;
        $where['b.type'] = 'down';
        $fields = 'a.id as id';
        //同标签下载数量多少
        $count  = M('down_dsoft')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->count();
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理
        $fields = 'a.id as id,c.title as title,a.size as size,c.smallimg as smallimg';
        $lists =M('down_dsoft')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did = a.id')->join('__DOWN__ c on c.id = a.id')->field($fields)->where($where)->page($p, $row)->select();
        if(!empty($lists))
        {
            foreach($lists as &$val)
            {
                $val['url'] = staticUrlMobile('detail', $val['id'],'Down');
                $val['path'] = get_cover($val['smallimg'],'path');
            }
        }
        echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
    }
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
    /**
     * 描述：礼包首页加载更多
     * Author:JeffreyLau
     */
    public function loadPackage(){
        $this->API_init();
        $callback = I('callback');
		$start=I('get.page')*6;
        if(empty($start)) return;
        $rs = M('Package')->where(array("status"=>"1"))->order('create_time desc')->limit("$start,6")->select();
	    $rs =  M("Package")->alias("__PACKAGE")->where(array("status"=>"1"))->order("create_time desc")->join("INNER JOIN __PACKAGE_PMAIN__ ON __PACKAGE.id = __PACKAGE_PMAIN__.id")->limit("$start,12")->field("*")->select();
		foreach($rs as $key=>$val){
		$arr[] = array( 
           'id'=>$val['id'], 
           'title'=>$val['title'], 
		   'description'=>strip_tags($val['content']), 
		   'images'=>get_cover($val['cover_id'],'path'), 
		   'detail'=>staticUrlMobile('detail', $val['id'],'Package'), 
		  ); 
		}      
        echo $callback ? $callback.'('.json_encode($arr).');' : json_encode($arr);
    }
	
	
	    /**
     * 描述：礼包分页加载更多
     * Author:JeffreyLau
     */
    public function loadCatePackage(){
        $this->API_init();
	  
        $callback = I('callback');
	    $cate = I('cate');
		$start=I('get.page')*10;
        if(empty($start)||empty($cate)) return;
	    $rs =  M("Package")->alias("__PACKAGE")->where(array("status"=>"1","category_id"=>$cate))->order("create_time desc")->join("INNER JOIN __PACKAGE_PMAIN__ ON __PACKAGE.id = __PACKAGE_PMAIN__.id")->limit("$start,12")->field("*")->select();
		foreach($rs as $key=>$val){
		$arr[] = array( 
           'id'=>$val['id'], 
           'title'=>$val['title'], 
		   'description'=>strip_tags($val['content']), 
		   'images'=>get_cover($val['cover_id'],'path'), 
		   'surplus'=> R('Card/getCardsCount',array($val['id'])), 
		   'detail'=>staticUrlMobile('detail', $val['id'],'Package'), 
		  ); 
		}      
        echo $callback ? $callback.'('.json_encode($arr).');' : json_encode($arr);
    }
	
	     /**
     * 描述：软件列表(最新)加载更多
     * Author:JeffreyLau
     */
    public function loadCateSoftCommend(){
        $this->API_init();
        $callback = I('callback');
	    $cate = I('cate');
		$start=I('get.page')*10;
        if(empty($start)||empty($cate)) return;
	   		$ids = D('DownCategory')->getAllChildrenId($cate);
	    $rs =  M("Down")->alias("__DOWN")->where("category_id IN(".$ids.") AND home_position & 64")->join("INNER JOIN __DOWN_DSOFT__ ON __DOWN.id = __DOWN_DSOFT__.id")->order("update_time desc")->limit("$start,10")->field("*")->select();
		foreach($rs as $key=>$val){
		$arr[] = array( 
           'id'=>$val['id'], 
           'title'=>$val['title'], 
		   'description'=>strip_tags($val['content']), 
		   'images'=>get_cover($val['smallimg'],'path'), 
		   'game_type'=>  $this->getCateName($val['category_id']), 
		   'size'=>empty($val['size']) ? '未知' : $val['size'], 
		   'url'=>staticUrlMobile('detail', $val['id'],'Down'), 
		  ); 
		}      
        echo $callback ? $callback.'('.json_encode($arr).');' : json_encode($arr);
    }
      /**
     * 描述：软件列表(最新)加载更多
     * Author:JeffreyLau
     */
    public function loadCateSoftNew(){
        $this->API_init();
        $callback = I('callback');
	    $cate = I('cate');
		$start=I('get.page')*10;
        if(empty($start)||empty($cate)) return;
		$ids = D('DownCategory')->getAllChildrenId($cate);
	    $rs =  M("Down")->alias("__DOWN")->where("category_id IN(".$ids.")")->join("INNER JOIN __DOWN_DSOFT__ ON __DOWN.id = __DOWN_DSOFT__.id")->order("create_time desc")->limit("$start,10")->field("*")->select();
		foreach($rs as $key=>$val){
		$arr[] = array( 
           'id'=>$val['id'], 
           'title'=>$val['title'], 
		   'description'=>strip_tags($val['content']), 
		   'images'=>get_cover($val['smallimg'],'path'), 
		   'game_type'=> $this->getCateName($val['category_id']), 
		   'size'=>empty($val['size']) ? '未知' : $val['size'], 
		   'url'=>staticUrlMobile('detail', $val['id'],'Down'), 
		  ); 
		}      
        echo $callback ? $callback.'('.json_encode($arr).');' : json_encode($arr);
    }
	
	private function getCateName($id){
		$name =  D('DownCategory')->where(array("id"=>$id))->getField('title');
		return $name;
	}
	 /**
     * 描述：文章分页加载更多
     * Author:JeffreyLau
     */
    public function loadCateNews(){
        $this->API_init();
        $callback = I('callback');
	    $cate = I('cate');
		$start=I('get.page')*10;
        if(empty($start)||empty($cate)) return;
		$ids = D('Category')->getAllChildrenId($cate);
	    $rs =  M("Document")->alias("__DOCUMENT")->where("status = '1' AND `category_id` IN(".$ids.")")->order("create_time desc")->join("INNER JOIN __DOCUMENT_ARTICLE__ ON __DOCUMENT.id = __DOCUMENT_ARTICLE__.id")->limit("$start,12")->field("*")->select();
		foreach($rs as $key=>$val){
		$arr[] = array( 
           'id'=>$val['id'], 
           'title'=>$val['title'], 
		   'description'=>strip_tags($val['description']), 
		   'images'=>get_cover($val['smallimg'],'path'), 
		   'detail'=>staticUrlMobile('detail', $val['id'],'Document'), 
		  ); 
		}      
        echo $callback ? $callback.'('.json_encode($arr).');' : json_encode($arr);
    }

    /**
     * 作者:肖书成
     * 描述:标签页获取数据（专门为 手机专题、专区而写的）
     * 时间:2015-12-14
     */
    public function API_tags(){
        //接受参数
        $key        =       I('key');
        $star       =       I('star');


        //验证参数
        if(!is_numeric($key) || !is_numeric($star) || (int)$key<=0 || (int)$star<0){
            $this->API_false();
        }


        //查找标签数据
        $data   =   M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.model_id,b.category_id')->join('__DOWN__ b ON a.did = b.id')
                    ->where("a.tid = ".$key." AND a.type = 'down'")->order('b.id DESC')->limit($star,10)->select();
        if(empty($data)){
            $this->API_result(null);exit;
        }

        //根据标签数据查找相应附属模型
        $model_id   =   implode(',',array_unique(array_column($data,'model_id')));
        $model      =   M('Model')->where("id IN($model_id) AND name != 'paihang'")->getField('id,name');

        $data_id    =   array();
        $arr        =   array();
        foreach($data as $k=>$v){
            foreach($model as $k1=>$v1){
                if($k1 == $v['model_id']){
                    $data_id[$v1][] =   $v['id'];
                    $arr[$v1][] =   $v;
                    continue;
                }
            }
        }

        //根据标签数据查找相应附属模型数据
        $arr_data   =   array();
        foreach($data_id as $k=>$v){
            $arr_data[$k]  =   M('Down'.ucfirst($k))->where('id IN('.implode(',',$v).')')->getField('id,version,size');
        }

        //查找下载分类
        $cate   =   S('96uDownCate');
        if(!$cate){
            $cate = M('DownCategory')->where('status = 1')->getField('id,title');
            if($cate){
                S('96uDownCate',serialize($cate),3600);
            }
        }else{
            $cate   =   unserialize($cate);
        }


        //合并附属模型的数据
        $data   =   array();
        foreach($arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($arr_data[$k][$v1['id']]){
                    $v1['cate'] =   $cate[$v1['category_id']];
                    $v1['url']  =   staticUrlMobile('detail',$v1['id'],'Down');
                    $v1['img']  =   get_cover($v1['smallimg'],'path');
                    $arr_data[$k][$v1['id']]['size']    =   format_size($arr_data[$k][$v1['id']]['size']);
                    $data[] =   array_merge($v1,$arr_data[$k][$v1['id']]);
                }
            }
        }

        //结果处理
        $this->API_result($data);
    }
} 