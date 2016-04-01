<?php
// +----------------------------------------------------------------------
// | 文章API，用于ajax请求和其他请求
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Think\Controller;

class ApiController extends Controller {

    /**
     * 初始化
     * @return void
     */
    protected function _initialize(){
        //API结果关闭trace
        C('SHOW_PAGE_TRACE', false); 
    }

    /**
     * 默认文档列表方法
     * @param boolean $is_json 是否返回json 默认是
     * @return string 结果集
     */
    public function getList($is_json = true){
        /* 查询条件初始化 */
        $map = array();
        if(I('title')){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }else{
            echo 'null';
            return;
        }

        if(I('status')){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = 1;
        }
        if ( I('time-start') ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( I('time-end') ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }

        if(I('category_id')){
            $map['category_id'] =   I('category_id');
        }

        $field =  I('field') ? I('field') : 'id,title';

        // 构建列表数据
        $Document = M('Document');
        $list = $Document->where($map)->field($field)->select();

        echo $is_json ? json_encode($list) : $list;
    }

    /**
     * 默认下载列表方法
     * @param boolean $is_json 是否返回json 默认是
     * @return string 结果集
     */
    public function getListDown($is_json = true){
        /* 查询条件初始化 */
        $map = array();
        if(I('title')){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }else{
            echo 'null';
            return;
        }

        if(I('status')){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = 1;
        }
        if ( I('time-start') ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( I('time-end') ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }

        if(I('category_id')){
            $map['category_id'] =   I('category_id');
        }

        $field =  I('field') ? I('field') : 'id,title';

        // 构建列表数据
        $Document = M('Down');
        $list = $Document->where($map)->field($field)->select();

        echo $is_json ? json_encode($list) : $list;
    }

    /**
     * 默认下载列表方法
     * @param boolean $is_json 是否返回json 默认是
     * @return string 结果集
     */
    public function getListPackage($is_json = true){
        /* 查询条件初始化 */
        $map = array();
        if(I('title')){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }else{
            echo 'null';
            return;
        }

        if(I('status')){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = 1;
        }
        if ( I('time-start') ) {
            $map['update_time'][] = array('egt',strtotime(I('time-start')));
        }
        if ( I('time-end') ) {
            $map['update_time'][] = array('elt',24*60*60 + strtotime(I('time-end')));
        }

        if(I('category_id')){
            $map['category_id'] =   I('category_id');
        }

        $field =  I('field') ? I('field') : 'id,title';

        // 构建列表数据
        $Document = M('Package');
        $list = $Document->where($map)->field($field)->select();

        echo $is_json ? json_encode($list) : $list;
    }
   /**
     * 获取模板
     * @param boolean $is_json 是否返回json 默认是
     * @return string 结果集
     */
    public function getTemplate($is_json = true){
        //查找缓存
        $cache_name = 'Template_list';
        $cache = S($cache_name);
        $data = I();
        natcasesort($data);
        $param = I() ? md5(json_encode($data)) : 'all';

        if (isset($cache[$param])){
            $list = $cache[$param];
        }else{
            /* 查询条件初始化 */
            $map = array();
            if(I('status')){
                $map['status'] = I('status');
                $status = $map['status'];
            }else{
                $status = null;
                $map['status'] = 1;
            }

            $field =  I('field') ? I('field') : 'id,title';

            I('type') && $map['type'] = I('type');

             // 构建列表数据
            $list = M('Template')->where($map)->field($field)->select();

            !$list && $list = 'null';
            $cache[$param] = $list;
            S($cache_name,$cache);
        }
        $list = ($is_json && $list != 'null') ? json_encode($list) : $list;
        echo $list;
    }

    /**
      * 获取内链
      * @param boolean $is_json 是否返回json 默认是
      * @return string 结果集
      */
    public function getInternalLink($is_json = true){
        /* 查询条件初始化 */
        $map = array();
        if(I('title')){
            $map['title']  = array('like', '%'.(string)I('title').'%');
        }else{
            echo 'null';
            return;
        }

        if(I('status')){
            $map['status'] = I('status');
            $status = $map['status'];
        }else{
            $status = null;
            $map['status'] = 1;
        }

        $field =  I('field') ? I('field') : 'id,title';

        // 构建列表数据
        $Document = M('InternalLink');
        $list = $Document->where($map)->field($field)->select();

        echo $is_json ? json_encode($list) : $list;
    }

     /**
     * 标签或产品标签模块,返回json结构的标签树，用于easyui的combotree插件使用
     * @param  string $type    类型，标签-Tags，产品标签-ProductTags
     * @param  string $category   标签分类
     * @param  integer $did    文档ID，如果传入参数则返回此文档选取标签的ID，否则返回空
     * @param  string $maptype   表中的type字段值
     * @param  array $field 需要查询的字段
     * @return string json结构数据 json之前的数组结构为array('json'=>json_arr, 'select_tags'=>selectid_arr)
     */
    public function getTagsJson($type = 'Tags', $category = '', $did = 0, $maptype = '', $field = array('id','title'=>'text','pid')){
        //获取数据
        $model = D($type);
        //组合条件
        $map = array();
        $map['status'] = 1;

        $category &&  $map['category'] = array('in', $category);
        $list = $model->field($field)->where($map)->order('id DESC')->limit(100)->select(); //暂时限制10000个，以后要缓存到js数组里面，这二次就不用查数据库了

        //combotree数据源格式化
        $json = array();
        if(is_array($list)) {
            //创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data['id']] =& $list[$key];
            }
            //格式化数据
            foreach ($list as $key => $data) {
            	$list[$key]['name'] = $data['text'];//定义name字段
                // 判断是否存在parent
                $parentId =  $data['pid'];
                if (0 == $parentId) {
                    $json[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent['children'][] =& $list[$key];
                    }
                    else
                    {
                        $json[] =& $list[$key];
                    }
                }
            }
        }

        //获取当前did已经选取的tags
        $select_tags = array();
        $default_tags = array();

        //作者xiao 兼容 副分类
        if(substr($type,-8)=='Category'){
            $map    =   'Category';
            $typeId =   'cid';
        }else{
            $map    =   $type;
            $typeId =   'tid';
        }

        if ($did) {
            $rs = M($map.'Map')->field($typeId.', type')->order('id ASC')->where(array('did'=>$did,'type'=>strtolower($maptype)))->select();

            foreach ($rs as $v) {
                $select_tags[] = $v[$typeId];
                $default_tags[] = $this->getTagsDefaultData($v[$typeId],$type,$category);
            }
        }

        $last = array('json'=>$json,'select_tags'=>$select_tags,'default_tags'=>$default_tags);  //主要解决异步加载，变量数据污染问题
        echo json_encode($last);
    }
    
    /**
     * 获取默认数据
     */
    public function getTagsDefaultData($id = 0,$type = 'Tags', $category = '', $field = array('id','title'=>'name','pid')){
    	$info = array();
    	$model = D($type);
    	$map = array();
    	$map['status'] = 1;
    	$category &&  $map['category'] = array('in', $category);
    	$map['id'] = $id;
    	if (intval($id) > 0){
    		$info = $model->field($field)->where($map)->order('sort')->find();
    	}
    	return $info;
    }

     /**
     * 标签或产品标签搜索
     * @param  string $type    类型，标签-Tags，产品标签-ProductTags
     * @param  string $category   标签分类
     * @param  string $keywords   搜索关键词
     * @param  array $field 需要查询的字段
     * @return string json结构数据
     */
    public function getTagsSearch($type = 'Tags', $category = '', $keywords = '', $field = array('id','title','pid'))
    {
        //必须要有关键词
        if(empty($keywords)) return '';
        $map = array();
        $map['title'] = array('like', "%$keywords%");
        //获取数据
        $model = M($type);
        //组合条件
        $map['status'] = 1;
        $category &&  $map['category'] = array('in', $category);
        $pid = $model->field($field)->where($map)->getField('id',true); //获取搜索相关的子集和父级
        if(is_array($pid))
        {
            $w['pid'] = array('in',$pid);
            $w['id'] = array('in',$pid);
            $w['_logic'] = 'or';
            $list = $model->field($field)->where($w)->select();
            $rs = array();
            foreach($list as $key=>$value){
                $value['name'] = $value['title'];
                $rs[$key] = $value;
            }
            if(is_array($rs))
            echo json_encode($rs);
        }

    }

/*
 * 获取挂件
 */
	public function getWidgets(){
		$Document = M('Widgets_instance');
		$lists = $Document->select();
		echo json_encode($lists);
	}
	
	public function getWidget(){
		$files=glob(APP_PATH.'Home/View/'.C('THEME').'/'.I('fun').'/widget/*');
		foreach($files as $k=>$v){
			$lists[]=array('widgets_type'=>strtr($v,array('./Application/Home/View/'.C('THEME').'/'.I('fun').'/widget/'=>'','.htm'=>''))."\r\n",'core_file'=>$v);
		}
		//var_dump($lists);
		echo json_encode($lists);
	}

	/*
	* 获取挂件内容
	*/	
	public function getHtml(){
		$fun=I('fun');
		//echo('{"content":"'.file_get_contents($filename).'"}');
		echo file_get_contents($fun);
	}
	
	public function getLayout(){
			$fun=I('fun');
			$filename=$fun;
			$temps=C('TMPL_PARSE_STRING');
			if(file_exists($filename.'/index.php')){
				include($filename.'/index.php');
			}else{
				$content=file_get_contents($filename);
                $content = $this->formatInclude($content);
				echo(
					json_encode(array('content'=>str_replace('__PUBLIC__',$temps['__PUBLIC__'],$content)
				)));
			}
	}

    /*
     * @Author （原作）周良才 肖书成（修改）
     * 处理代码中的<include 引用 文件
     */
    private function formatInclude($content){
        $regular='/<include file="([^"]+)"\s*\/>/is';
        if(preg_match_all($regular,$content,$results)){
            foreach($results[0] as $k=>&$v){
                $file=str_replace('@','/View/',$results[1][$k]);
                $html = file_get_contents('./Application/'.$file.'.html');
                if(strpos($content,'<include')){
                    $html = $this->formatInclude($html);
                }
                $content=str_replace($results[0][$k],$html,$content);
            }
            unset($v);
        }
        return $content;
    }
	
	/*
	*获取专题分类
	*/
	public function getCat(){
		$pid=intval(I('fun'));
		$Document = M('FeatureCategory');
		//die(var_dump($Document));
		$lists = $Document->where(array('pid'=>$pid))->select();
		echo json_encode($lists);
	}
	
	/*
	*获取下载分类
	*/
	public function getCategory(){
		$pid=intval(I('fun'));
		$Document = M('DownCategory');
		//die(var_dump($Document));
		$lists = $Document->where(array('pid'=>$pid))->select();
		echo json_encode($lists);
	}
	
	/*
	*获取标签下的分类
	*/
	public function getNode(){
		$pid=intval(I('fun'));

		$maps=M('product_tags_map')->field('did')->where(array('type'=>'down','tid'=>$pid))->limit('1000')->select();
		foreach($maps as $k=>$v){
			if($k) $id.=',\''.$v['did'].'\'';
			else $id.='\''.$v['did'].'\'';
		}
		if(!$id) die();

		$results=M('down_category')->where('id in (select category_id from onethink_down where id in ('.$id.'))')->select();

		die(json_encode($results));
	}
	
	/*
	*获取分类下的产品标签
	*/
	public function getTag(){
		$pid=intval(I('fun'));

		$maps=M('down')->field('id')->where(array('category_id'=>$pid))->limit('1000')->select();
		foreach($maps as $k=>$v){
			if($k) $id.=',\''.$v['id'].'\'';
			else $id.='\''.$v['id'].'\'';
		}
		if(!$id) die();

		$results=M('tags')->where('id in (select distinct(tid) from onethink_tags_map where did in ('.$id.'))')->select();

		die(json_encode($results));
	}
	
	public function getTopic(){
		$pid=intval(I('fun'));
		$tags=M('product_tags')->field('id,title')->where('status=1')->select();
		foreach($tags as $k=>$v){
			$details[$v['id']]=$v;
		}
		
		if(isset($pid)){
			$condition='status=1 and id in(select did from onethink_product_tags_map where tid='.$pid.')';
			$condition='status=1 and id in(select did from onethink_product_tags_map where type=\'down\')';
			$ids=M('down')->field('id,title')->where($condition)->select();
			$results['id']=$ids;
		}
		 
		$maps=M('product_tags_map')->field('tid,did')->where(array('type'=>'down'))->select();

		foreach($maps as $k=>$v){
			$results['tag'][$v['tid']]=$details[$v['tid']]['title'];
		}
		die(json_encode($results));
	}
	
	public function lists(){
		$table=$_GET['table'];
		$tables=explode('__',$table);
		
		$title=$_GET['fun'];
		$condition="title like '%{$title}%'";
		//var_dump($condition);
		
		$results=M($tables[1])->field('id,concat(\''.$table.' → \',title) as title')->where($condition)->order('id desc')->limit('100')->select();
		
		if(is_array($results)){
			$ids=M($tables[0])->field('id,concat(\''.$tables[0].' → ID \',title) as title')->where($condition)->order('id desc')->limit('100')->select();
			foreach($ids as $k=>&$v){
				$results[]=$v;
			}
		}else{
			$results=M($tables[0])->field('id,concat(\''.$tables[0].' → ID \',title) as title')->where($condition)->order('id desc')->limit('100')->select();
		}
		
		echo json_encode($results);
	}
	
	/*
	*获取下载分类
	*/
	public function cat(){
		$pid=intval(I('fun'));
		$Document = M('DownCategory');
		//die(var_dump($Document));
		$cats=$Document->where(array('pid'=>$pid,'status'=>'1'))->select();
		$results['cat']=$cats;
		if($pid){
			$condition='status=1 and category_id in(\''.$pid.'\')';
		}else{
			$condition='status=1';
		}
		$ids=M('down')->field('id,title')->where($condition)->order('id desc')->limit('100')->select();
		$results['id']=$ids;
		echo json_encode($results);
	}	
	
	public function __call($method,$args){
		//var_dump($method,$args,$_GET);
		$tid=intval(I('fun'));
		
		$tags=M($method)->field('id,title')->where('status=1 and id in (select tid from onethink_'.$method.'_map where type=\'down\')')->select();
		$results['tag']=$tags;
		
		if($tid){
			$condition='status=1 and id in(select did from onethink_'.$method.'_map where type=\'down\' and tid='.$tid.')';
		}else{
			$condition='status=1 and id in(select did from onethink_'.$method.'_map where type=\'down\')';
		}
		$ids=M('down')->field('id,title')->where($condition)->order('id desc')->limit('200')->select();
		$results['id']=$ids;
			
		die(json_encode($results));	
	}
	
	/*
	 *取得当前节点下的所有子节点
	 */
	protected function cate($id,$pid=0){
		$key=__FUNCTION__.$id;//缓存key
		//if(!$result=app::cache()->get($key)){
		//	$DB=app::db('leha');
		$results=M('DownCategory')->where('status=1')->select();
	
		foreach($results as $k=>&$v){
			$tags[$v['pid']][$v['id']]=$v;
		}
	
		$this->tree(array('hashmaps'=>$tags,'pid'=>0),$tags);
		//print_r($tags[0][$pid]);
		$keys=array();
	
		self::filt($tags[$pid][$id],$keys);
		//	$result='(\''.implode('\',\'',$keys).'\')';
		//	app::cache()->set($key,$result,get_setting('cache_level_low'));
		//}
	
		return $keys;
	}
	
	//深度遍历树
	protected function tree($ids,&$childs){
		$pid=$ids['pid'];
		foreach($childs[$pid] as $k=>&$v){
			if(isset($ids['hashmaps'][$v['id']])){
				$id=$v['id'];
				$childs[$pid][$k]=$ids['hashmaps'][$v['id']];
				$ids['pid']=$id;
				self::tree($ids,$childs[$pid]);
			}
		}
	}
	
	protected function filt($arrays,&$keys){
		foreach($arrays as $k=>$v) {
			if(is_array($v)){
				$keys[$k]=$k;
				self::filt($v,$keys);
			}
		}
	}
    /**
     * 描述：获取城市key-value
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCity()
    {
        $province_id = I('province_id');
        if(empty($province_id)) return false;
        $w['b.id'] = array('eq',$province_id);
        $list = M('City')->alias('a')->join('__PROVINCE__ b on a.provincecode=b.code')->field('a.id as id,a.name as name')->where($w)->select();
        echo json_encode($list);
    }
    /**
     * 描述：获取地区key-value
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getArea()
    {
        $city_id = I('city_id');
        if(empty($city_id)) return false;
        $w['b.id'] = array('eq',$city_id);
        $list = M('Area')->alias('a')->join('__CITY__ b on a.citycode=b.code')->field('a.id as id,a.name as name')->where($w)->select();
        echo json_encode($list);
    }

    /**
     * 描述：根据关键字搜索公司(礼包)名称
     * @param string $keywords
     * @param array $field
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getPackageTitle( $keywords = '', $field = array('id','title'))
    {
        //必须要有关键词
        if(empty($keywords)) return '';
        $keywords = urldecode($keywords);
        $map = array();
        $map['title'] = array('like', "%$keywords%");
        //获取数据
        $model = M('package');
        //组合条件
        //$map['status'] = 1;  //取消禁用厂商不能搜索
        $list = $model->field($field)->where($map)->select();
        echo json_encode($list);
    }
	
	
	/**
     * 检测标题是否重复
     * @param string $title
     * @param array $field
     * @return json
     * Author:Jeffrey Lau
     */
	
	public function checkTitle(){
		$title=I('title');
		$module = I('module');
		if(empty($title)){return;}
		if(empty($module)){return;}
		$data = M($module)->where(array("title"=>$title))->find();
		$json=array();
		if($data){
			$json['result']= "0";
		}else{
			$json['result']= "1";
		}
		
		echo json_encode($json);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}