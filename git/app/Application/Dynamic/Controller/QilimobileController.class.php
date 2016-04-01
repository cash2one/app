<?php
// +----------------------------------------------------------------------
// | 七丽手机动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class QilimobileController extends BaseController {

    /**
     * 图库投票
     * @return void
     */
	function addView($modelid, $id){
		$relati = F('tagRelati');

		if(isset($relati[$modelid])){
			$document = M($relati[$modelid][1]);
			$details  = $document->where(array('id'=>$id))->find();

			if($details){
				if($document->where(array('id'=>$id))->setInc('view', 1)){
					$this->ajaxReturn($details['view']+1);
				}
			}
		}
	}

	/**
	 * 文章副分类数据聚合
	 * Author:朱德胜
	*/
	function aggregation(){
		$typeid = I('get.typeid');
		$page   = I('get.page');
		$limit  = 10 * $page;
		
		if($typeid && $page){
			$map  = M('CategoryMap');
			$maps = $map->where(array('type'=>'document', 'cid' => $typeid))->order("id DESC")->limit($limit.',10')->order('update_time DESC')->select();

			if($maps){
				$ids		= implode(',', array_column($maps, 'did'));
				$document	= M('Document')->where("status = 1 AND id IN($ids)")->select();
				$ids		= implode(',', array_column($document, 'category_id'));
				$category	= M('Category');

				$menu = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid')->where('id IN('.$ids.')')->select();
					
				$details = array();
				foreach($menu as $value){
					$details[$value['id']] = $value;
				}
				
				foreach($document as $k => $v){
					$v['module'] = array('Category', 'Document', 'DocumentArticle');

					$v['path_detail']		 = $details[$v['category_id']]['path_detail'];
					$v['rootid']			 = $details[$v['category_id']]['rootid'];
					$document[$k]['url']     = $this->parseModuleUrl($v);
					$document[$k]['create_time']     = date('Y/m/d H:i:s', $v['create_time']);
					$document[$k]['atlas_a']= get_thumb($v['atlas_a'], $v['atlas'], '88x82');
					$node = $map->where(array('type' => 'document', 'did' => $v['id']))->select();
			
					if($node){
						$ids = implode(',', array_column($node, 'cid'));

						$document[$k]['classify'] = $category->field('id,title,name')->limit(2)->select($ids);
						
						if($document[$k]['classify']){
							foreach($document[$k]['classify'] as $i => $val){
								$document[$k]['classify'][$i]['url'] = '/'.$val['name'].'/';
							}
						}
					}else{
						$document[$k]['classify'] = array();
					}
				}

				$result = array('status' => 1, 'data' => $document);
			}else{
				$result = array('status' => 0);
			}

			$this->ajaxReturn($result);
		}
	}

    /**
     * 文章频道页面数据加载
     * Author:朱德胜
     */
    public function channel(){
		$typeid = I('get.typeid');
		$page   = I('get.page');
		$limit  = 10 * $page;
		
		if($typeid && $page){
			$map	  = M('CategoryMap');
			$document = M('Document')->where(array("status"=>1,"category_rootid"=>$typeid))->order("id DESC")->limit($limit.',10')->select();
			$category = M('Category');

			if($document){
				$ids  = implode(array_column($document, 'category_id'), ',');
				$menu = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid')->where('id IN('.$ids.')')->select();
				
				$details = array();
				foreach($menu as $value){
					$details[$value['id']] = $value;
				}
				
				foreach($document as $k => $v){
					$v['module'] = array('Category', 'Document', 'DocumentArticle');

					$v['path_detail']		 = $details[$v['category_id']]['path_detail'];
					$v['rootid']			 = $details[$v['category_id']]['rootid'];
					$document[$k]['url']     = $this->parseModuleUrl($v);
					$document[$k]['create_time'] = date('Y/m/d H:i:s', $v['create_time']);
					$document[$k]['atlas_a']= get_thumb($v['atlas_a'], $v['atlas'], '88x82');
					$node = $map->where(array('type' => 'document', 'did' => $v['id']))->select();
			
					if($node){
						$ids = implode(',', array_column($node, 'cid'));

						$document[$k]['classify'] = $category->field('id,title,name')->limit(2)->select($ids);
						
						if($document[$k]['classify']){
							foreach($document[$k]['classify'] as $i => $val){
								$document[$k]['classify'][$i]['url'] = '/'.$val['name'].'/';
							}
						}
					}else{
						$document[$k]['classify'] = array();
					}
				}
				
				$result = array('status' => 1, 'data' => $document);
			}else{
				$result = array('status' => 0);
			}
			$this->ajaxReturn($result);
		}
    }

    /**
     * 图库频道页面数据加载
     * Author:朱德胜
     */
    public function picchannel(){
		$page   = I('get.page');
		$limit  = 10 * $page;
		
		if($page){
			$map	  = M('CategoryMap');
			$document = M('Gallery')->where(array("status"=>1))->order("id DESC")->limit($limit.',10')->select();
			$category = M('GalleryCategory');

			if($document){
				$ids  = implode(array_column($document, 'category_id'), ',');
				$menu = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid')->where('id IN('.$ids.')')->select();
				
				$details = array();
				foreach($menu as $value){
					$details[$value['id']] = $value;
				}
				
				foreach($document as $k => $v){
					$v['module'] = array('Category', 'Document', 'DocumentArticle');

					$v['path_detail']		 = $details[$v['category_id']]['path_detail'];
					$v['rootid']			 = $details[$v['category_id']]['rootid'];
					$document[$k]['url']     = $this->parseModuleUrl($v);
					$document[$k]['atlas_a']= get_thumb($v['atlas_a'], $v['atlas'], '88x82');
					$document[$k]['create_time']     = date('Y/m/d H:i:s', $v['create_time']);
					$node = $map->where(array('type' => 'document', 'did' => $v['id']))->select();
			
					if($node){
						$ids = implode(',', array_column($node, 'cid'));

						$document[$k]['classify'] = $category->field('id,title,name')->limit(2)->select($ids);
						
						if($document[$k]['classify']){
							foreach($document[$k]['classify'] as $i => $val){
								$document[$k]['classify'][$i]['url'] = '/'.$val['name'].'/';
							}
						}
					}else{
						$document[$k]['classify'] = array();
					}
				}
				
				$result = array('status' => 1, 'data' => $document);
			}else{
				$result = array('status' => 0);
			}
			$this->ajaxReturn($result);
		}
    }

	/**
	 * 手机版Url处理
	 * @param  string $params 栏目信息
	 * @return string
	*/
	function parseModuleUrl($params){
		if(isset($params['category_id'])){
			// 文档
			$category = M($params['module'][0])->where(array('id' => $params['rootid']))->find();
			
			if($category){
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