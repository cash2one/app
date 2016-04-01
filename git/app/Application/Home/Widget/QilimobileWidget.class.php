<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Widget;
use Common\Controller\WidgetController;
use Think\Controller;

/**
 * 页面widget
 * 
 */

class QilimobileWidget extends WidgetController{

   /**
    * 404页面
    * @return void
    */
	function notexists(){
		$this->assign('SEO', $this->SEO('special', null, 78));
		$this->display('Widget/404');
	}

	/**
	 * SEO
	 * @param  string $type 类型
	 * @param  string $module 模块名
	 * @param  integer $cid or $id
	 *@return array
	*/
    public function SEO($type, $module = null, $id = null,$p = 0){
        $seo = array();
        switch ($type) {
            case 'index':
                $seo['title'] = C('MOBILE_WEB_SITE_TITLE');
                $seo['keywords'] = C('MOBILE_WEB_SITE_KEYWORD');
                $seo['description'] =C('MOBILE_WEB_SITE_DESCRIPTION');
                return $seo;
                break;
            case 'moduleindex':
                if(empty($module)) return;
                $module = strtoupper($module);
                $seo['title'] =C(''.$module.'_DEFAULT_TITLE');
                $seo['keywords'] = C(''.$module.'_DEFAULT_KEY');
                $seo['description'] =C(''.$module.'_DEFAULT_DESCRIPTION');
                return $seo;
                break;
	       case 'special':
                $id=empty($id)?'1':$id;
	   $cate=D('StaticPage')->where("id='$id'")->find();
                $title=empty($cate['title'])?$cate['title']:$cate['title'];
				$seo['title'] =$title." - ".C('SITE_NAME');
                $seo['keywords'] = empty($cate['keywords'])?'':$cate['keywords'];
                $seo['description'] =empty($cate['description'])?'':$cate['description'];
                return $seo;
                break;
            case 'category':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory',
					'GALLERY'	=> 'GalleryCategory',
                );
		
                $cate=D($cate_name[$module])->where("id='$id'")->find();
                $title =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
                $seo['title'] =$title." - ".C('SITE_NAME');
                if($p > 1) $seo['title'] =$title."(第" .$p ."页) - ".C('SITE_NAME');
                $seo['keywords'] = empty($cate['keywords'])?C(''.$module.'_DEFAULT_KEY'):$cate['keywords'];
                $seo['description'] =empty($cate['description'])?C(''.$module.'_DEFAULT_KEY'):$cate['description'];
                return $seo;
                break;
            case 'detail':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = ucfirst(strtolower($module));                
                $detail = D($module)->detail($id);

                //标题
                if($module=='Down'){
                    //下载的规则 
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title'];
                    }else{
                        $title = $detail['sub_title'] .'|'. $detail['title'] . '下载' . $detail['version']; 
                    }
                }else{
                    //其他的规则 
                    //1、seo title
                    //2、主标题
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title']; 
                    }else{
                        $title = $detail['title']; 
                    }
                }
                //标题需要加前后缀
                if(C('SEO_STRING')){
                    if($module == 'Package' && C('PACKAGE_SEO_LAST')){
                        $seo['title'] = $title . ' - ' . C('PACKAGE_SEO_LAST');
                    }else{
                        $t = array();
                        $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                        $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                        ksort($t);
                        $seo['title'] = implode(' - ', $t);
                    }
                }else{
                    $seo['title'] = $title; 
                }

                //关键词
                if(empty($detail['seo_keywords'])){
                    if($module=='Document'){
                        //文章 标签
                        $tag_ids = M('TagsMap')->where('did='.$id.' AND type="document"')->getField('tid', true);
                        if(empty($tag_ids)){
                            $seo['keywords'] = $detail['title']; 
                        }else{
                            $tags = M('Tags')->where(array('id'=>array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);                            
                        }
                    }else{
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title']) 
                                                        ? $detail['title'] 
                                                        : $detail['title'] . ',' . $detail['sub_title'];
                    }
                }else{
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if(empty($detail['seo_description'])){
                    $des = array('Document'=>'description','Down'=>'conductor','Package'=>'content');
                    if(empty($detail[$des[$module]])){
                        $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))),0,500);
                    }else{
                        $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                    }
                }else{
                    $seo['description'] = strip_tags($detail['seo_description']);
                }

                return $seo;
                break;
        }
    }

	/**
	* 文章内容页大家都在看
	* @param  string $typeid 栏目id
	* @author zhudesheng
	* @return void
	*/
	function newslook($typeid){
		$category = M('category');
		$details  = $category->find($typeid);
		$look	  = $category->field('id,title,template_index,path_index,template_lists,path_lists,path_detail,path_lists_index,rootid,recommend_view_name')->where("status = 1 AND rootid = $details[rootid] AND recommend_view_name != ''")->limit('10')->select();
		
		if($look){
			foreach($look as $k => $v){
				$look[$k]['url'] = parseModuleUrl($v);
			}
		}
	
		$this->loop = $look;

		$this->display('Widget/newslook');
	}

	/**
	* 文章内容页相关主题
	* @param  string $typeid 栏目id
	* @author zhudesheng
	* @return void
	*/
	function newstopic($typeid){
		$category = M('category');

		$field = 'id,poly_name,rootid,pid';

		$details  = $category->field($field)->find($typeid);
		
		if($details){
			$offset  = $category->field($field)->where("status = 1 AND pid = $details[pid] AND poly_name != ''")->group('poly_name')->order('id DESC')->limit(4)->select();
			
			$count   = count($offset);
			
			if($count < 4){
				$limit = 4 - $count;
				$ids = implode(array_column($offset, 'id'), ',');
				
				$offsetRight = $category->field($field)->where("status = 1 AND rootid = $details[rootid] AND id NOT IN($ids) AND poly_name != ''")->group('poly_name')->order('id ASC')->limit($limit)->select();

				if(is_array($offsetRight)){
					$offset = array_merge($offset, $offsetRight);
				}
			}

			if($offset){
				$result = array();

				foreach($offset as $k => $params){
					$result[$k] = $params;
					$result[$k]['theme'] = $category->where("status = 1 AND poly_name = '$params[poly_name]' AND poly_name != ''")->limit('12')->select();

					if($result[$k]['theme']){
						foreach($result[$k]['theme'] as $e => $val){
							$result[$k]['theme'][$e]['url'] = parseModuleUrl($val);
						}
					}
				}
			}
		}
		
		$this->theme = $result;
		$this->display('Widget/newstopic');
	}

	/**
	* 图库首页
	* @author zhudesheng
	* @return void
	*/
	function pichome(){
		$this->assign('SEO', $this->SEO('special', null, 61));
		$this->display('Widget/pichome');
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

	/**
	 * 副分类
	 * @param  array $data 数据源
	 * @return array
	*/
	function classify($data){
		$details  = current($data);
		$map	  = M('CategoryMap');
		$category = M($details['module'][0]);

		$type	  = strtolower($details['module'][1]);
		$ids	  = array();

		foreach($data as $k => $v){
			$node = $map->where(array('type' => $type, 'did' => $v['id']))->select();
			
			if($node){
				$ids = implode(',', array_column($node, 'cid'));

				$data[$k]['classify'] = $category->field('id,title,name')->limit(2)->select($ids);
				
				if($data[$k]['classify']){
					foreach($data[$k]['classify'] as $i => $val){
						$data[$k]['classify'][$i]['url'] = '/'.$val['name'].'/';
					}
				}
			}else{
				$data[$k]['classify'] = array();
			}
		}

		return $data;
	}

	/**
	 * 文章副分类数据聚合
	 * @param  array $data 数据源
	 * @return array
	*/
	function aggregation($typeid){
		$map  = M('CategoryMap');
		$maps = $map->where(array('type'=>'document', 'cid' => $typeid))->limit(10)->order('update_time DESC')->select();

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
				$document[$k]['url']     = parseModuleUrl($v);
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

			$this->data = $document;
		}

		$this->display('Widget/aggregation');
	}

	/**
	* 手机栏目列表
	* @author zhudesheng
	* @return void
	*/
	function listCategoryGet(){
		// 栏目列表
		$category = $this->getCategory();
		
		$lists = array();
		foreach($category as $k => $value){
			$category[$k]['title'] = msubstr($value['title'],0,4,'utf-8',false);
			$category[$k]['icon'] = get_cover($value['icon']);
		}
		
		$category = list_to_tree($category, 'id', 'pid', '_', 0);

		foreach($category as $value){
			$lists[$value['id']] = $value;
		}
		
		// 图库列表
		$gallery = $this->getCategory('model', 'GalleryCategory');
		foreach($gallery as $k => $value){
			$gallery[$k]['title'] = msubstr($value['title'],0,4,'utf-8',false);
			$gallery[$k]['icon'] = get_cover($value['icon']);
		}
		
		$lists[0] = array(
			'id'	=>	0,
			'title'	=>	'图库',
			'_'		=>	$gallery
		);

		$this->lists = $lists;
		$this->display('Widget/category');
	}

	/**
	* 栏目调用标签
	* @author zhudesheng
	* @return array
	*/
	function getCategory(){
		$params  = array();
		$tmp	= func_get_args();
		$count  = count($tmp);
		for($i = 0; $i < $count; $i++) {
			if(isset($tmp[$i+1])){
				$params[$tmp[$i]] = $tmp[++$i];
			}
		}
		
		if($params['model']){
			$mod = M($params['model']);
		}else{
			$mod = M('category');
		}
		
		if($params['limit']){
			$mod->limit($params['limit']);
		}

		if($params['order']){
			$mod->order($params['order']);
		}
		
		$condition = array();
		if($params['id']){
			$condition[] = "id IN($params[id])";
		}

		if($params['notid']){
			$condition[] = "id NOT IN($params[notid])";
		}

		if(isset($params['pid'])){
			$condition[] = 'pid = '.$params['pid'];
		}

		return $mod->field('id,name,title,pid,icon')->where($condition)->select();
	}
}
?>