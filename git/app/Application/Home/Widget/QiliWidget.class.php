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

class QiliWidget extends WidgetController{

   /**
    * 404页面
    * @return void
    */
	function notexists(){
		$this->assign('SEO', $this->SEO('special', null, 77));
		$this->display('Widget/404');
	}


	function listsGraphic($typeid, $limit, $notin){
		$notid = $this->listTop($typeid, false, false, true);
		if($notin){
			$notid .= ','.$notin;
		}

		$this->id = $this->listTop($typeid, $limit, $notid, true);
		$this->display('Widget/listsGraphic');
	}

   /**
    * 猜你喜欢
	* @param $typeid string  栏目ID
	* @param $hot    numeric 热门天数
    * @return void
    */
	function guess($typeid, $hot){
		if($typeid){
			$mod  = M('document');
			$time = date('Y-m-d 00:00:00', strtotime('-'.($hot-1).' day'));
		
			$time = strtotime($time);
			
			$document = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid)")->limit(9)->order('create_time DESC,view DESC')->select();
			$count = count($document);
			if($count < 9){
				$ids = '';
				if($document){
					$ids = implode(array_column($document, 'id'), ',');
				}

				for($i = 1; $i < 30; $i++){
					$limit = 9 - $count;
					$time  = strtotime(date('Y-m-d 00:00:00', strtotime('-'.($hot*$i).' day')));
					
					if($ids){
						$data = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid) AND id NOT IN($ids)")->limit($limit)->select();
					}else{
						$document = array();
						$data = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid)")->limit($limit)->select();
					}

					if($data){
						$count += count($data);
						$document = array_merge($document, $data);
						$ids = implode(array_column($document, 'id'), ',');
					}

					if($count >= 9){
						break;
					}
				}
			}

			// 自动补全
			$count = count($document);
			if($count <= 4){
				$limit = 4 - $count;
				if($ids){
					$tmp = $mod->field('id')->where("id NOT IN($ids) AND status = 1")->order('id DESC')->limit($limit)->select();
					if($tmp){
						$document = array_merge($document, $tmp);
					}
				}else{
					$document = $mod->field('id')->where('status = 1')->order('id DESC')->limit($limit)->select();
				}
			}

			if($document){
				$this->id = implode(array_column($document, 'id'), ',');
				$this->display('Widget/guess');
			}
		}
	}

   /**
    * 热门排行
	* @param $typeid string  栏目ID
	* @param $hot    numeric 热门天数
    * @return void
    */
	function hot($typeid, $hot){
		if($typeid){
			$mod  = M('document');
			$time = date('Y-m-d 00:00:00', strtotime('-'.($hot-1).' day'));
		
			$time = strtotime($time);
			
			$document = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid)")->limit(10)->order('create_time DESC,view DESC')->select();
			$count = count($document);
			if($count < 10){
				$ids = '';
				if($document){
					$ids = implode(array_column($document, 'id'), ',');
				}

				for($i = 1; $i < 30; $i++){
					$limit = 10 - $count;
					$time  = strtotime(date('Y-m-d 00:00:00', strtotime('-'.($hot*$i).' day')));
					
					if($ids){
						$data = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid) AND id NOT IN($ids)")->limit($limit)->select();
					}else{
						$document = array();
						$data = $mod->field('id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid)")->limit($limit)->select();
					}

					if($data){
						$count += count($data);
						$document = array_merge($document, $data);
						$ids = implode(array_column($document, 'id'), ',');
					}

					if($count >= 10){
						break;
					}
				}
			}
			
			// 自动补全
			$count = count($document);
			if($count < 10){
				$limit = 10 - $count;
				if($ids){
					$tmp = $mod->field('id')->where("id NOT IN($ids) AND status = 1")->order('id DESC')->limit($limit)->select();
					if($tmp){
						$document = array_merge($document, $tmp);
					}
				}else{
					$document = $mod->field('id')->where('status = 1')->order('id DESC')->limit($limit)->select();
				}
			}
			
			if($document){
				$this->id = implode(array_column($document, 'id'), ',');
				$this->display('Widget/hot');
			}
		}
	}

    /**
     * SSI生成头部
    */
    public function header(){
      $this->display(T('Home@qili/Public/header'));
  }

    /**
	*SSI生成底部
    */
    public function footer(){
      $this->display(T('Home@qili/Public/footer'));
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
                $seo['title'] =C('WEB_SITE_TITLE');
                $seo['keywords'] = C('WEB_SITE_KEYWORD');
                $seo['description'] =C('WEB_SITE_DESCRIPTION');
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
					'GALLERY'	=>	'GalleryCategory'
                );
				if(is_string($id)){
					$cate=D($cate_name[$module])->where("name='$id'")->find();
				}else{
					$cate=D($cate_name[$module])->where("id='$id'")->find();
				}

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
	 * 页脚内容
	 * @return void
	*/
	public function about(){
		$template = I('get.template');
		if($template){
			$params = array('about' => 69, 'culture' => 70, 'join' => 71);
			$this->assign('SEO', $this->SEO('special', null, $params[$template]));
			$this->display('Widget/'.$template);
		}
	}

	/**
	* 打印详情的标签
	* @author Jeffrey Lau
	* @date   2015-6-29 10:26:13
	* @param  integer $cid or $id
	* @return string
	*/
   public function showTags($id){
	  if(!is_numeric($id)){return;}
	  $result = '' ;
	  $tags =  M('TagsMap')->alias("__TAG")->where(array('did'=>$id))->join("INNER JOIN __TAGS__ ON __TAG.tid = __TAGS__.id")->limit("10")->field("*")->select();
	  if(is_array($tags)){
		  foreach($tags as $key=>$val){
		          $result.=$val['title']."&nbsp;";
		  }
		  
	   }
	 echo rtrim($result,"&nbsp;");
  }

	/**
	 * 版块首页
	 * @return void
	*/
	function section($name){

		$this->display('Widget/'.$name);
	}

    /**
     * 描述：娱乐频道首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function yule()
    {
		
		$this->assign('SEO', $this->SEO('special', null, 67));
        $this->display('Widget/yule');
    }

    /**
     * 描述：生活频道首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function shenghuo()
    {
        $this->display('Widget/shenghuo');
    }
	/**
	 * 电脑版Url处理
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
	 * 图库首页
	 * @return void
	*/
	function pichome(){
		$this->assign('SEO', $this->SEO('special', null, 65));
		$this->display('Widget/pichome');
	}

	/**
	 * 图库首页
	 * @return void
	*/
	function lists(){
		$name = I('get.name');

		$this->assign('SEO', $this->SEO('category', 'DOCUMENT', $name));
		$this->assign('board', $name);
		$this->display('Widget/lists');
	}

	/**
	 * 博主首页
	 * @return void
	*/
	public function bozhulist(){
		$this->display('Widget/bozhulist');
	}

	/**
	 * 解析博文中文章图片
	 * @param  array $data 数据源
	 * @return array
	*/
	function parseDocumentImg($data){
		$details  = current($data);
		$map	  = M('CategoryMap');
		$category = M($details['module'][0]);

		$type	  = strtolower($details['module'][1]);
		$ids	  = array();

		foreach($data as $k => $v){
			if(substr_count($v['content'], '[page][/page]') >= 1){
				list($imgpack, $text) = explode('[page][/page]', $v['content']);
				
				if($imgpack){
					preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/',$imgpack,$match);
					
					if(!empty($match[1][0])){
						$data[$k]['image'] = $match[1][0];
					}
				}
			}

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
	* 列表头部文章推荐
	* @param category_id string 栏目id
	* @author zhudesheng
	* @date   2015.10.22
	*/
	public function listTop($category_id, $limit = 7, $notin='', $return = false){
		if($category_id){
			$attribute = M('attribute')->where('model_id = 1')->select();
			
			$attr = array();
			foreach($attribute as $val){
				if(substr($val['name'], '-6') == '_recom'){
					$attr[] = $val['name'];
				}
			}
			
			$where = '';
			if($attr){
				$where = ' AND (';
				foreach($attr as $name){
					$where .= "(`$name` != '' AND `$name` != 0) OR ";
				}
				$where = rtrim($where, 'OR ').')';
			}
			
			if($notin){
				$document = M('Document')->field('id')->where("(status = 1 AND category_id IN($category_id)) AND id NOT IN($notin) $where")->order('update_time DESC')->limit($limit)->select();
			}else{
				$document = M('Document')->field('id')->where("(status = 1 AND category_id IN($category_id)) $where")->order('update_time DESC')->limit($limit)->select();
			}
			
		
			$count = count($document);

			if($count < $limit){
				$limit = $limit - $count;
				if($document){
					$ids   = implode(array_column($document, 'id'), ',');
				}
				if($ids){
					$array    = M('Document')->field('id')->where('status = 1 AND category_id IN('.$category_id.') AND id NOT IN('.$ids.')')->order('view DESC')->limit($limit)->select();
					$document = array_merge($document, $array);
				}else{
					$document = M('Document')->field('id')->where('status = 1 AND category_id IN('.$category_id.')')->order('view DESC')->limit($limit)->select();
				}
			}
			
			if($document){
				$ids = implode(array_column($document, 'id'), ',');
			}

			if($return){
				return $ids;
			}else{
				$this->ids = $ids;
				$this->display('Widget/listTop');
			}
		}
	}

	/**
	 * 列表大家都在搜
	 * @param $typeid string 栏目ID
	 * @return void
	*/
	function listsSearch($typeid){
		$category = M('category');
		
		$list  = $category->where("id IN($typeid) AND status = 1 AND poly_name != ''")->limit(4)->group('poly_name')->select();

		if($list){
			$ids = implode(array_column($list, 'id'), ',');
			$result = array();
			foreach($list as $k => $value){
				$result[$k]['root'] = $value;
				
				if($ids){
					$son = $category->where("status = 1 AND poly_name = '$value[poly_name]' AND id NOT IN($ids)")->order('id DESC')->limit(8)->select();
				}

				if($son){
					$_ids = implode(array_column($son, 'id'), ',');
					if($ids){
						$_ids = ','.$_ids;
					}
					$ids .= $_ids;

					foreach($son as $e => $val){
						$son[$e]['module'] = array('Category', 'Document', 'DocumentArticle');
						$son[$e]['url']	   = parseModuleUrl($son[$e]);
					}
					$result[$k]['son'] = $son;
				}
			}

			if($result){
				foreach($result as $k => $value){
					if(empty($value['son'])){
						unset($result[$k]['root'], $result[$k]['son']);
					}
				}
				$result = array_filter($result);
				$this->theme = $result;
				$this->display('Widget/search');
			}
			
		}
	}

	/**
	 * 标签大家都在搜
	 * @param $typeid string 栏目ID
	 * @return void
	*/
	function tagsSearch($typeid){
		$category = M('category');
		
		$details  = $category->where("id = '$typeid' AND status = 1")->find();
		
		if($details){
			$list  = $category->where("rootid = '$details[rootid]' AND status = 1 AND poly_name != ''")->limit(4)->select();
			
			if($list){
				$ids = implode(array_column($list, 'id'), ',');
				$result = array();
				foreach($list as $k => $value){
					$result[$k]['root'] = $value;
					$son = $category->where("status = 1 AND poly_name = '$value[poly_name]' AND id NOT IN($ids)")->order('id DESC')->limit(8)->select();
					
					if($son){
						$_ids = implode(array_column($son, 'id'), ',');
						if($ids){
							$_ids = ','.$_ids;
						}
						$ids .= $_ids;

						foreach($son as $e => $val){
							$son[$e]['module'] = array('Category', 'Document', 'DocumentArticle');
							$son[$e]['url']	   = parseModuleUrl($son[$e]);
						}
						$result[$k]['son'] = $son;
					}
				}

				if($result){
					foreach($result as $k => $value){
						if(empty($value['son'])){
							unset($result[$k]['root'], $result[$k]['son']);
						}
					}
					$result = array_filter($result);
					$this->theme = $result;
					$this->display('Widget/tagsSearch');
				}
			}
		}
	}

	/**
	 * Tags内容聚合列表
	 * @param $typeid string 栏目ID
	 * @return void
	*/
	function tagsList($typeid){
		$map  = M('CategoryMap');
		$count = $map->where(array('type'=>'document', 'cid' => $typeid))->count();
		
		if(strrpos($_SERVER['REQUEST_URI'], '.html')){
			$url = dirname($_SERVER['REQUEST_URI']).'/';
		}else{
			$url = $_SERVER['REQUEST_URI'];
		}

		$page  = new \Think\Page($count,7,array(),false, $url.'index_{page}.html');
		$page->setConfig('first','首页');
		$page->setConfig('end','尾页');
		$page->setConfig('prev',"上一页");
		$page->setConfig('next','下一页');
		$page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
		$pages = $page->show();
		$limit = $page->firstRow.',7';

		$maps = $map->where(array('type'=>'document', 'cid' => $typeid))->order('id DESC')->limit($limit)->select();

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
				$document[$k]['atlas']= get_thumb($v['atlas_a'], $v['atlas'], '285x271');
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

			$this->assign('tagslist', $document);
		}

		$this->display('Widget/tagslist');
	}

	/**
	 * 首页生活推荐
	 * @return void
	*/
	function liferecom(){
		$typeid = M('category')->field('id')->where('status = 1 AND rootid IN(951,918,917,5,915,303,277,281)')->select();

		if($typeid){
			$typeid = implode(array_column($typeid, 'id'), ',');
			$mod    = M('document');
			$result = $accumulate = array();
			$ids    = '';

			for($i = 0; $i < 30; $i++){
				$time  = strtotime(date('Y-m-d 00:00:00', strtotime('-'.(1*$i).' day')));

				if($ids){
					$document = $mod->field('id,category_id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid) AND id NOT IN('.$ids.') AND home_recom & 128")->limit(8)->order('create_time DESC,view DESC')->select();
				}else{
					$document = $mod->field('id,category_id')->where("status = 1 AND create_time >= '$time' AND category_id IN($typeid) AND home_recom & 128")->limit(8)->order('create_time DESC,view DESC')->select();
				}

				if($document){
					foreach($document as $value){
						if(isset($accumulate[$value['category_id']])){
							if($accumulate[$value['category_id']] >= 2){
								continue;
							}

							$accumulate[$value['category_id']] += 1;
							$result[] = $value;
						}else{
							$accumulate[$value['category_id']] = 1;
							$result[] = $value;
						}
					}

					$tmp = implode(array_column($document, 'id'), ',');

					if($ids){
						$ids .= ',';
					}

					$ids .= $tmp;
				}
				
				if(count($result) >= 8){
					break;
				}
			}
			
			if($result){
				$this->liferecomid = implode(array_column($result, 'id'), ',');
				$this->display('Widget/liferecom');
			}
		}
	}
}
?>