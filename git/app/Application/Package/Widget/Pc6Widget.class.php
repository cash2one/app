<?php
// +----------------------------------------------------------------------
// | 卡号相关信息
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Package\Widget;
use Think\Controller;

class Pc6Widget extends Controller{

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

    /**
    * 获取礼包产品标签
    * @date: 2015-7-2
    * @author: liujun
    */
	public function packageProductTags($did = 0,$limit = 1){
		$where = array('m.type'=>'package','m.did'=>$did);
		$tags = M('ProductTagsMap')->alias('m')->join('right join '.C(DB_PREFIX).'product_tags as t ON m.tid = t.id')->field('t.id,t.name,t.title')->where($where)->order('t.id ASC')->limit($limit)->select();
		$this->assign('tags',$tags);
		$this->display(T('Package@pc6/Widget/packageProductTags'));
	}
	
	/**
	 * 获取礼包
	 * @date: 2015-7-2
	 * @author: liujun
	 */
	public function package($limit = 6,$order = 'update_time DESC'){
		$packageList = M('Package')->where(array('status'=>1,'category_id'=>array('in',array(1,2,4))))->order($order)->limit($limit)->select();
		$this->assign('packageList',$packageList);
		$this->display(T('Package@pc6/Widget/packageList'));
	}
	
	/**
	 * 获取产品标签
	 * @date: 2015-7-2
	 * @author: liujun
	 */
	public function productTags($limit = 10,$order = 'sort ASC,update_time DESC'){
		$productTags = M('ProductTags')->where(array('status'=>1))->order($order)->limit($limit)->select();
		$this->assign('productTags',$productTags);
		$this->display(T('Package@pc6/Widget/productTags'));
	}
}
