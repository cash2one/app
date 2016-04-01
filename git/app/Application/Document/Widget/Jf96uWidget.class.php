<?php
// +----------------------------------------------------------------------
// | 描述:文章模块widget文件
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-11 下午2:54    Version:1.0.0 
// +----------------------------------------------------------------------
namespace Document\Widget;
use Think\Controller;
class Jf96uWidget extends Controller{
    /**
     * 描述：我的世界首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function wdsj()
    {
        $game_id = 186720;  //我的世界游戏id
        $taglist = M('tags_map')->where("did='".$game_id."' and type='down'")->getField('tid',true);
        if(!empty($taglist))
        {
            $where = array();
            $where['t.tid'] = array('in',$taglist);
            $where['t.type'] = array('eq','package');
            $packlist = M('package')->alias('p')->join('__TAGS_MAP__ t on p.id=t.tid')->field('p.id as id,p.title as title')->where($where)->select();
        }
        $count = count($packlist);
        $this->assign('pcount',$count);
        $this->assign('packlist',$packlist);
        $this->assign('cid',65); //widget的id
        $seo = WidgetSEO(array('special',null,65));
        $this->assign('SEO',$seo);
        $this->display(T('Document@jf96u/Widget/wdsj'));
    }
	
	
	
	//相关下载
	public function relateDown($id){
		if(!is_numeric($id)){return;}
		$down = get_base_by_tag($id,'Document','Down','',true);
		$package=get_base_by_tag($down['id'],'Down','Package','',false);
		$this->assign("packageCount",count($package));
		$this->assign("down",$down);
		$this->assign("package",$package);
		$this->display("Widget/relateDown");
		
		
	}
	
	//手游排行榜
	public function shouyouRank($id){
		$this->display("Widget/relateDown");	
	}
	
	
	//热门礼包
	public function hotPackage(){
		$rs =  M("Package")->alias("__PACKAGE")->where(array("status"=>"1"))->order("update_time desc")->join("INNER JOIN __PACKAGE_PMAIN__ ON __PACKAGE.id = __PACKAGE_PMAIN__.id")->limit("5")->field("*")->select();
		$this->assign("lists",$rs);
		$this->display("Widget/hotPackage");	
	}
	
	//游戏视频
	public function gameVideo(){
		$this->display("Widget/gameVideo");	
	}
		//手游排行
	public function gameRank(){
		$this->display("Widget/gameRank");	
	}
	
	//游戏评测
	public function gamePingce(){
		$this->display("Widget/gamePingce");	
	}
	
	//下载框的相关链接
	public function getRelateLink($id){
		$links=array(
		     'shipin'=>$this->getRelateArticle($id,"1591"),
			 'gonglve'=>$this->getRelateArticle($id,"1590"),
			 'pingce'=>$this->getRelateArticle($id,"1589"),
			 'jietu'=>$this->getRelateArticle($id,"1602")
		
		);
		$this->assign("lists",$links);
		$this->display("Widget/getRelateLink");	
	}
	
   private function getRelateArticle($ID,$filterID){
	       $relate=get_base_by_tag($ID,'Down','Document','',false);
		   foreach($relate as $key=>$val){
			    if(!in_array($val['category_id'],$filterID)){
				   unset($relate[$key]);
				}
				
		   }
		   $relate= multi_array_sort($relate,'update_time',SORT_DESC);
		   if(empty($relate)){
			   return;
		   } 
		 
	       return staticUrl('detail',$relate[0]['id'],'Document');
  }
  
  
  
	
} 