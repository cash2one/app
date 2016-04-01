<?php
// +----------------------------------------------------------------------
// | 七丽PC动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class QiliController extends BaseController {

    /**
     * 图库列表数据加载
     * Author:朱德胜
     */
    public function galleryList(){
		$typeid = I('get.typeid');
		$page	= I('get.page');
		$limit  = 12 * $page;

		if($page){
			$document = M('Gallery')->where(array("status"=>1, 'category_id' => $typeid))->order("id DESC")->limit($limit.',12')->select();
			
			if($document){
				$category = M('GalleryCategory');
				$ids  = implode(array_column($document, 'category_id'), ',');
				$menu = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid')->where('id IN('.$ids.')')->select();
				
				$details = array();
				foreach($menu as $value){
					$details[$value['id']] = $value;
				}
				
				foreach($document as $k => $v){
					$v['module'] = array('GalleryCategory', 'Gallery', 'GalleryAlbum');
					$v['path_detail']		 = $details[$v['category_id']]['path_detail'];
					$v['rootid']			 = $details[$v['category_id']]['rootid'];
					$v['category_id']		 = $v['category_id'];
					
					$document[$k]['url']     = $this->parseModuleUrl($v);
					$document[$k]['atlas_c'] = get_thumb($v['atlas_c'], $v['atlas'], '285x317');
				}
				
				$result = array('status' => 1, 'data' => $document);
			}else{
				$result = array('status' => 0);
			}

			$this->ajaxReturn($result);
		}
    }

	/**
	 * Url处理
	 * @param  string $params 栏目信息
	 * @return string
	*/
	function parseModuleUrl($params){
		if(isset($params['category_id'])){
			// 文档
			$category = M($params['module'][0])->where(array('id' => $params['rootid']))->find();
			
			if($category){
				if(substr($category['name'], 0,4) == 'pic/'){
					$category['name'] = 'pic';
				}

				$url = $category['name'].'/'.substr($params['id'], -2).'/'.$params['id'].'.html';
			}
		}else{
			if($params['template_index']){
				// 频道
				$filename = strtolower(basename($params['path_index']));

				if($filename == 'index'){
					$params['path_index'] = dirname($params['path_index']).'/';
				}

				$url = $params['path_index'];
			}else{
				// 列表
				$filename = strtolower($params['path_lists_index']);
				
				if($filename == 'index' || !$filename){
					$url = dirname($params['path_lists']).'/';
				}else{
					$url = dirname($params['path_lists']).'/'.$filename.'.html';
				}
			}
		}
		
		if($url){
			return '/'.ltrim($url, '/');
		}
	}
}
?>