<?php
namespace Package\Widget;
use Package\Controller\BaseController;

class GfwmobileWidget extends BaseController{
	
	/**
	* 网站大全首页
	* @date: 2015-6-12
	* @author: liujun
	*/
	public function website(){
		$seo = I('seo');
		$this->assign('SEO',WidgetSEO(array('special',null,$seo)));
		$this->display(T('Package@gfwmobile/Widget/website'));
	}
	
	/**
	 * 手机版网站频道页面
	 * @date: 2015-5-29
	 * @author: liujun
	 */
	public function categoryInfo(){
		$category_id = I('category_id');
		$depth = 2;//层级
		$seo = I('seo');
		
		//分类信息查询
		$info = D('PackageCategory')->info($category_id);
		if (!$info || $info['pid'] > 0) $this->error('您访问的频道页面不存在！');
		
		$cateList = array();
		$cateTree = D('PackageCategory')->getTree($category_id);//获取分类树
		
		$result = array(
			'category_id' => $category_id,
			'SEO' => WidgetSEO(array('special',null,$seo)),
			'info' => $info,
			'cateTree' => $cateTree
		);
		$this->assign($result);
		$this->display(T('Package@gfwmobile/Widget/category_info'));
	}
	
	/**
	 * 描述：获取分页地址
	 * Author:谭坚
	 * Version:1.0.0
	 * Modify Time:
	 * Modify Author:
	 */
	public function categoryPage($row,$count,$cate)
	{
	
		$count = $count ? $count : 0;
		$row = $row ? $row : 10;
		$cate = $cate ? $cate : 1;
		$path = staticUrlMobile('lists',$cate,'Package',2);
		$Page       = new \Think\Page($count, $row, '', $cate,$path);// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id
		$Page->setConfig('prev',"上一页");
		$Page->setConfig('next','下一页');
		$Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE%');
		$show   = $Page->show();// 分页显示输出
		$this->assign('page',$show);// 赋值分页输出
		$this->display(T('Package@gfwmobile/Widget/catepage'));
	}
	
	/**
	 * 获取热门网站
	 * @date: 2015-6-13
	 * @author: liujun
	 */
	public function websiteHot($limit = 5){
		$webSiteList = M('Package')->field('id,title,path_detail')->where('status = 1 and position & 1')->order('update_time DESC')->limit($limit)->select();
		$this->assign('webSiteList',$webSiteList);
		$this->display(T('Package@gfwmobile/Widget/website_hot'));
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
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        $Page->setConfig('theme',' %UP_PAGE% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

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
        $idArr = array(5,1,2,3,6,4);
		$where = array('status'=>1);
		$order = '';
		if(!empty($idArr)){
            $ids = join(',', $idArr);
            $where['id'] = array('in',$ids);
            $order = 'FIELD(id,'.$ids.')';
        }
		$cateList = M('PackageCategory')->field(array('id','title'))->where($where)->order($order)->select();
		$this->assign('cateList',$cateList);
        $this->display(T('Package@gfwmobile/Widget/website_place'));
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
}
