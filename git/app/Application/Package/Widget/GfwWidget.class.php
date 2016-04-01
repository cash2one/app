<?php
namespace Package\Widget;
use Common\Controller\WidgetController;

class GfwWidget extends WidgetController{
	
	/**
	* 网站大全
	* @date: 2015-5-6
	* @author: liujun
	*/
	public function website(){
		$seo = I('seo');
		
		//热门点击:热门点击推荐位排
		$where = array('status'=>1);
		$where['_string'] = "position & 1";
		$hotList = $this->getWebSiteList($where,'update_time DESC',10);
		
		$result = array(
				'SEO' => WidgetSEO(array('special',null,$seo)),//seo
				'hotList' => $hotList,//热门点击
			);
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/website_index'));
	}
	
	/**
	* 导航栏(父级分类)详情页面(频道页面)
	* @date: 2015-5-6
	* @author: liujun
	*/
	public function categoryInfo(){
		$category_id = I('category_id');
		$limit = !empty(I('limit'))?I('limit'):7;//记录条数
		$depth = 2;//层级
		$seo = I('seo');
		
		//分类信息查询
		$info = D('PackageCategory')->info($category_id);
		if (!$info || $info['pid'] > 0) $this->error('您访问的频道页面不存在！');
		//热门分类推荐 
		$hotCateList = $this->getCateHot($category_id,1,20);
		//二级分类及其网站记录
		$cateList = array();
		$cateTree = $this->getCateTree($category_id,$depth);
		if(!empty($cateTree[0]['_'])){
			foreach($cateTree[0]['_'] as $key=>$value){
				$childIds = $this->getChildIds($value['id']);//获取子分类id
				if($childIds){
					$webSiteList = $this->getWebSiteList(array('status'=>1,'category_id'=>array('in',$childIds)),'update_time DESC',$limit);
				}
				$value['webSiteList'] = !empty($webSiteList)?$webSiteList:array();
				$cateList[] = $value;
			}
		}
		
		$result = array(
			'category_id' => $category_id,
			'SEO' => WidgetSEO(array('special',null,$seo)),
			'info' => $info,
			'cateList' => $cateList,
			'hotCateList' => $hotCateList,//热门分类推荐 
		);
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/category_info'));
	}
	
	/**
	 * 当前分类位置
	 * @date: 2015-5-9
	 * @author: liujun
	 * @param int $category_id 分类Id
	 */
	public function categoryPosition($category_id = 0){
		$result = getParentCategory($category_id);
		$this->assign('currentCategory',$result);
		$this->display('Widget/category_position');
	}
	
	/**
	 * 网站相关推荐
	 * @date: 2015-5-20
	 * @author: liujun
	 * @param int $limit 数据条数
	 * @param int $type 类型
	 */
	public function websiteRecommended($limit = 30,$type = 'new',$category_id = 0,$package_id = 0){
		$position = 2;//最新收录
		if($type == 'detail_hot'){//最新热门推荐
			$position = 8;
		}
		
		$where = array('status'=>1);
		$where['_string'] = "position & ".$position;
		
		if(intval($package_id) > 0){
			$where['id'] = array('neq',$package_id);
		}
		//每个频道下的网站记录
		if(intval($category_id) > 0){
			$cateInfo = getParentCategory($category_id);
			if($cateInfo){
				$cid = $cateInfo[0]['id'];
				if($position == '8' && count($cateInfo) > 1){//相关推荐
					$cid = $cateInfo[1]['id'];
				}
				$childIds = $this->getChildIds($cid);//获取子分类id
				if($childIds){
					$where['category_id'] = array('in',$childIds);
				}
			}
			$webSiteList = $this->getWebSiteList($where,'update_time DESC',$limit);
			if($position == 8 && count($webSiteList) < $limit){//热门分类：不足获取同分类下的最新记录
				$newLimit = $limit - count($webSiteList);
				unset($where['_string']);
				
				$notIds = $package_id;
				if($webSiteList){
					$cateIds = array_column($webSiteList, 'id');
					$notIds = $notIds.','.join(',',$cateIds);//排除的ID
				}
				$where['id'] = array('not in',$notIds);
				$newWebSiteList = $this->getWebSiteList($where,'update_time DESC',$newLimit);
				if(empty($webSiteList)){
					$webSiteList = $newWebSiteList;
				}else{
					$webSiteList = array_merge($webSiteList,$newWebSiteList);
				}
			}
		}else{
			$webSiteList = $this->getWebSiteList($where,'update_time DESC',$limit);
		}
		
		$result = array(
			'type' => $type,//类型
			'webSiteList' => $webSiteList,
		);
		$this->assign($result);
		$this->display('Widget/website_recommended');
	}
	
	/**
	 * 网站首页分类极其网站列表
	 * @date: 2015-5-11
	 * @author: liujun
	 * @param int $category_id 分类Id
	 * @param int $limit 数据条数
	 */
	public function websiteList($pid = 0,$depth = 2,$limit = 24,$position = 1){
		$webSiteList = array();
		//获取分类树
		$cateTree = $this->getCateTree($pid,$depth);
		if($position == '1'){
			//获取分类下的所有网站记录
			$childIds = $this->getChildIds($pid);//获取子分类id
			if($childIds){
				$webSiteList = $this->getWebSiteList(array('status'=>1,'category_id'=>array('in',$childIds)),'update_time DESC',$limit);
			}
		}
		$result = array(
			'position' => $position,
			'cateTree' => $cateTree,
			'webSiteList' => $webSiteList,
		);
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/website_list'));
	}
	
	/**
	 * 获取网站分类
	 * @date: 2015-5-11
	 * @author: liujun
	 * @param array $cate 默认值
	 */
	public function categoryTree($cate){
		$category_id = !empty($cate['id'])?$cate['id']:0;
		
		$PackageCategory = D('PackageCategory');
		$info = $PackageCategory->info($category_id);
		if(!$info){
			$this->error('您访问的频道页面不存在！');
		}
		//获取顶级目录id
		$parent_id = 0;
		$parentList = getParentCategory($category_id);
		if($parentList){
			$parent_pid = $parentList['0']['pid'];
			if($parent_pid == 0){
				$parent_id = $parentList['0']['id'];
			}
		}
		
		//找不到上级目录bug 去掉提示消息 add liujun 2015-06-19
		/* if($parent_id <= 0){
			$this->error('您访问的频道页面不存在！');
		} */
		
		$field = array('id,title,pid','path_lists','icon','vertical_pic');
		$cateRs = $PackageCategory->getTree($parent_id,$field);
		$cateList = !empty($cateRs['_'])?$cateRs['_']:array();
		unset($cateRs['_']);
		$result = array(
			'category_id' => $category_id,
			'rootCate' => $cateRs,//顶级目录
			'cateList' => $cateList,//子目录
			'cate' => array($info['id'],$info['pid'])
		);
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/category_tree'));
	}
	
	/**
	* 网站基本信息
	* @date: 2015-5-11
	* @author: liujun
	*/
	public function websiteBasicInfo($info = array()){
		$result = array();
		$province_id = $info['province_id'];//省
		$city_id = $info['city_id'];//市
		$area_id= $info['area_id'];//区域
		$address = $info['address'];//详细地址
		
		$provinceInfo = getProvince($province_id);
		$cityInfo = getCity($city_id);
		$areaInfo = getArea($area_id);
		
		$result['info'] = $info;
		$result['province'] = !empty($provinceInfo['name'])?$provinceInfo['name']:'';
		$result['city'] = !empty($cityInfo['name'])?$cityInfo['name']:'';
		$result['area'] = !empty($areaInfo['name'])?$areaInfo['name']:'';
		$result['address'] = $address;
		
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/website_basic_info'));
	}
	
	/**
	* 网站下的所有产品图片
	* @date: 2015-5-12
	* @author: liujun
	*/
	public function websiteProduct($package = array(),$limit = 8){
		$img_first_id = 0;
		$package_id = $package['id'];
        $status = 1;//只获取正常的数据
		$productList = M('Down')->field(array('id','title','package_id','previewimg'))->where(array('package_id'=>$package_id,'status'=>$status))->order('update_time DESC')->limit($limit)->select();
		foreach($productList as $key=>$value){
			if(!empty($value['previewimg'])){
				$imgIds = array_filter(explode(',', $value['previewimg']));//去除空元素
				if(is_array($imgIds)){
					$imgIds = array_values($imgIds);//重新排列
					$img_first_id = $imgIds[0];
				}
			}
			$productList[$key]['img_id'] = $img_first_id;
		}
		$this->assign('info',$package);
		$this->assign('productList',$productList);
		$this->display(T('Package@gfw/Widget/website_product'));
	}
	
	/**
	 * 获取网站记录
	 * @date: 2015-5-7
	 * @author: liujun
	 * @param array $where 查询条件
	 * @param string $order 排序字段
	 * @param int $limit 记录条数
	 */
	private function getWebSiteList($where = array(),$order = 'update_time DESC',$limit = 24){
		$field = array('id','title','category_id','position','update_time');
		if(empty($where)){
			$where = array('status'=>1);
		}
		if(empty($order)){
			$order = 'update_time DESC';
		}
		$webSiteList = M('Package')->field($field)->where($where)->order($order)->limit($limit)->select();
		return $webSiteList;
	}
	
	/**
	 * 根据分类id获取所有子分类Id
	 * @date: 2015-5-11
	 * @author: liujun
	 */
	private function getChildIds($pid = 0,&$lists = array()){
		$field = array('id,title,pid');
		$cateList = M('PackageCategory')->field($field)->where(array('status'=>1,'pid'=>$pid))->select();
		foreach($cateList as $key=>$value){
			$lists[] = $value;
			$this->getChildIds($value['id'], $lists);
		}
		$childIds = array_column($lists, 'id');
		$childIds = array_merge(array($pid),$childIds);
		return $childIds;
	}
	
	/**
	 * 获取热门分类推荐 
	 * @date: 2015-5-13
	 * @author: liujun
	 */
	private function getCateHot($pid = 0,$position = 0,$limit = 20){
		$field = array('id,title,pid','depth','path_lists','icon','vertical_pic','recommend_view_name');
		$where = array('status'=>1);
		if($position > 0){
			$where['_string'] = 'position & '.$position;
		}
		//获取分类下的所有子分类
		$childIds = $this->getChildIds($pid);//获取子分类id
		if($childIds){
			$where['id'] = array('in',$childIds);
		}else{
			$where['id'] = $pid;
		}
		$cateList = M('PackageCategory')->field($field)->where($where)->order('sort asc')->limit($limit)->select();
		return $cateList;
	}
	
	/**
	 * 获取分类下的网站记录
	 * @date: 2015-5-11
	 * @author: liujun
	 */
	private function getCateTree($pid = 0,$depth = 1){
		$cateList = $this->getCategoryList($pid,$depth);
		if($pid > 0){
			$field = array('id,title,pid','depth','path_lists','icon','vertical_pic');
			$parentInfo = M('PackageCategory')->field($field)->where(array('status'=>1,'id'=>$pid))->order('sort')->select();
			if(!empty($parentInfo)){
				$cateList = array_merge($parentInfo,$cateList);
			}
		}
		//把返回的数据集转换成Tree
		$cateTree = list_to_tree($cateList,'id','pid','_');
		return $cateTree;
	}
	
	/**
	 * 获取子类
	 * @date: 2015-5-11
	 * @author: liujun
	 * @param int $pid 父id
	 * @param int $depth 层级
	 * @param array $lists 数据集
	 * @return array
	 */
	private function getCategoryList($pid = 0,$depth = 1, &$lists = array()) {
		$field = array('id,title,pid','depth','path_lists','icon','vertical_pic');
		$where = array('status'=>1,'pid'=>$pid,'depth'=>array('elt',$depth));
		$cateList = M('PackageCategory')->field($field)->where($where)->order('sort')->select();
		foreach($cateList as $key=>$value){
			$lists[] = $value;
			$this->getCategoryList($value['id'],$depth, $lists);
		}
		return $lists;
	}
	
	/**
	* 地方网站大全
	* @date: 2015-5-14
	* @author: liujun
	*/
	public function websitePlace(){
		$category_id = I('cate');//分类Id
		$province_id = !empty(I('province'))?I('province'):0;//省份Id
		$limit = !empty(I('limit'))?I('limit'):12;//记录条数
		$p = intval(I('p'));//第几页
		$count = 0;
		
		//必须带省份Id
		$provinceInfo = getProvince($province_id);
		if($province_id <= 0 || empty($provinceInfo)){
			return false;
		}
		//检测分类id是否正确
		if($category_id > 0){
			$cateInfo = M('PackageCategory')->where(array('status'=>1,'id'=>$category_id))->select();
			if(empty($cateInfo)){
				return false;
			}
		}
		//获取当前分类位置
		$positionCate = getParentCategory($category_id);
		$selectIds = array_column($positionCate, 'id');
		//获取分类树
		$cateTree = $this->getCateTree(0,2);
		//获取所有省份
		$provinceList = getProvince();
		//获取网站记录
		$where = array('status'=>1);
		$childIds = $this->getChildIds($category_id);//获取子分类id
		if($childIds){
			$where['category_id'] = array('in',$childIds);
		}else{
			$where['category_id'] = $category_id;
		}
		if($province_id){
			$where['province_id'] = $province_id;
		}
		$field = array('package.id','title','category_id','url','update_time','content','contacts','telphone','phone','country','province_id','city_id','area_id','address');
		$join = 'left join '.C(DB_PREFIX).'package_particle as particle ON package.id = particle.id';
		$count = M('Package')->alias('package')->join($join)->field($field)->where($where)->count();//总记录数
		
		//是否返回总页数
		if(I('gettotal')){
			if(empty($count)){
				echo 1;
			}else{
				echo ceil($count/$limit);
			}
			exit();
		}
		
		if (!is_numeric($p) || $p<=0 ) $p = 1;
		if ($p > $count ) $p = $count; //容错处理
		
		$webSiteList = M('Package')->alias('package')->join($join)->field($field)->where($where)->order('update_time DESC')->page($p,$limit)->select();
		
		createdPlacePath($provinceInfo['pinyin'],$category_id);//创建静态文件夹
		
		$path = '';
		$path_all = C('PLACE_CATE_STATIC_PATH');
		$path_province = C('PLACE_PROVINCE_STATIC_PATH');
		$path_cate_url = substr($path_all, 0, strrpos($path_all, '/')).'/';//默认url
		$path_province_url = substr($path_province, 0, strrpos($path_province, '/')).'/';//默认url
		
		if($province_id > 0 && $category_id > 0){//处理所有静态地址
			$path_all = str_replace('{pinyin}',$provinceInfo['pinyin'],$path_all);
			$path_all = str_replace('{category_id}',$category_id,$path_all);
			$path = $path_all.getStaticExt();
			$path_province_url = $path_cate_url;
		}elseif ($province_id > 0){//处理省份静态地址
			$path_province = str_replace('{pinyin}',$provinceInfo['pinyin'],$path_province);
			$path = $path_province.getStaticExt();
		}
		
		$Page = new \Think\Page($count, $limit, '', false, $path);// 实例化分页类 指定路径规则
		$Page->setConfig('prev','上一页');
		$Page->setConfig('next','下一页');
		$Page->setConfig('first','首页');
		$Page->setConfig('last','尾页');
		$show = $Page->show();// 分页显示输出
		
		$seoTitle = $provinceInfo['name'].$cateInfo[0]['title'].'网站大全';
		$seo = array(
			'title' => $seoTitle .' - '.C('SITE_NAME'),
			'keywords' => $seoTitle,
			'description' => $seoTitle,
		);

		$result = array(
			'select_category_id' => $category_id,
			'select_province_id' => $province_id,
			'path_cate_url' => $path_cate_url,
			'path_province_url' => $path_province_url,
			'positionCate' => $positionCate,
			'selectIds' => $selectIds,//选择的分类Id
			'SEO' => $seo,//seo
			'cateTree' => $cateTree,
			'provinceList' => $provinceList,
			'count' => $count,
			'page' => $show,
			'webSiteList' => $webSiteList,
			'provinceInfo' => $provinceInfo //当前选中的省份信息
		);
		$this->assign($result);
		$this->display(T('Package@gfw/Widget/website_place'));
	}
	
	/**
	* 网站大全省份
	* @date: 2015-5-16
	* @author: liujun
	*/
	public function websiteProvince(){
		//获取所有省份
		$provinceList = getProvince();
		//地方网站大全url
		$path_province = C('PLACE_PROVINCE_STATIC_PATH');
		$path_province = substr($path_province, 0, strrpos($path_province, '/')).'/';
		
		$this->assign('path_province',$path_province);
		$this->assign('provinceList',$provinceList);
		
		$this->display(T('Package@gfw/Widget/website_province'));
	}
}
