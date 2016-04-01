<?php
// +----------------------------------------------------------------------
// | 7230动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class P7230Controller extends BaseController {
    /**
     * 下载首页
     * @return void
     */
    public function downindex(){
        //下载首页还是游戏首页
        $type = I('type');
        $type = $type == 'rj' ? 'rj' : 'yx';
        //参数
        $params = I('params');
        if(empty($params)){
            $p = $sort = 1;
        }else{
            $params = explode('_', $params);
            $p = array_pop($params);
            $sort = array_pop($params);            
        }

        //筛选条件
        $filter = array();
        if($type == 'yx'){
            //传入参数
            $params && list($system, $network, $category) = $params;
            //环境
            $filter['system'] =array(
                'title'=>'平台',
                'config'=>C('FIELD_DOWN_SYSTEM'),
                );
            //联网
            $filter['network'] =array(
                'title'=>'联网',
                'config'=>C('FIELD_DOWN_NETWORK'),
                );
            //游戏栏目分类
            $categorys = M('DownCategory')->where('status=1 AND pid=1')->getField('id,title');
            $filter['category'] =array(
                'title'=>'分类',
                'config'=>$categorys,
                );
            //游戏标签分类
            $tags = M('Tags')->where('status=1 AND category=1')->order('sort DESC')->limit(100)->getField('name,title');
        }elseif($type == 'rj'){
            //传入参数
            $params && list($system, $category) = $params;
            //环境
            $filter['system'] =array(
                'title'=>'平台',
                'config'=>C('FIELD_DOWN_SYSTEM'),
                );
            //软件栏目分类
            $categorys = M('DownCategory')->where('status=1 AND pid=2')->getField('id,title');
            $filter['category'] =array(
                'title'=>'分类',
                'config'=>$categorys,
                );
            //软件标签分类
            $tags = M('Tags')->where('status=1 AND category=7')->order('sort DESC')->limit(100)->getField('name,title');
        }

        //参数为空的时候全为0
        if(!$params){
            foreach ($filter as $key => $value) {
                $params[] = 0;
            }
            $params[] = 0;
        }

        //生成筛选
        $c = array('fl','wf','qin'); //css class
        $html = '';
        $i = 0;
        foreach ( $filter as $t => $f) {    
            $uparams = $params;        
            if(!current($c)) reset($c);
            $html .='<p class="wz '. each($c)['value'].'">';
            $html .='<b>'. $f['title'] .'</b>';
            //URL拼接
            $uparams[$i] = 0;
            $href = '/' .$type . '/' . implode('_', $uparams) .  '_'. $sort .'_1.html';
            $html .= empty(${$t}) ? '<s class="cur"><a target="_self" href="'. $href .'">不限</a></s>' : '<s><a target="_self" href="'. $href .'">不限</a></s>';
            foreach ($f['config'] as $k => $v) {
                if((${$t}==$k)){
                    $cur = 'class="cur"';
                    //seo相关词组
                    $seo_array[$t] = $v;
                }else{
                    $cur = '';
                }
                //URL拼接
                $uparams[$i] = $k;
                $href = '/' .$type . '/' . implode('_', $uparams) .  '_'. $sort .'_1.html';
                $html .='<s data-id="'. $k .'"'. $cur .'><a target="_self" href="'. $href .'">'. $v .'</a></s>';
            }
            $html .='</p>';
            $html .= PHP_EOL;
            $i++;
        }
        //重置的HTML代码
        $uparams = array_map(function($v){return 0;},$params);
        $html .= '<p class="wz ts"><b>重置</b><s><a target="_self" href="/' .$type . '/' .  implode('_', $uparams) .  '_1_1.html">全部重置</a></s></p>' . PHP_EOL;

        //标签
        $html .= '<div class="tags"><div class="tags_head">标签</div><div class="tags_body"><ul>'.PHP_EOL;
        foreach($tags as $k => $v){
            $href    = '/tag/'.$k;
            $html   .= '<li><a target="_blank" href="'.$href.'">'.$v.'</a></li>'.PHP_EOL;
        }
        $html .='<li style="float: right;"><a target="_blank" href="/tags/">更多标签+</a></li></ul></div><div class="clear"></div></div>';

        $this->assign('filter',$html);
        //生成tab排序标签
        $html_sort_arr = array(1=>'最近更新', 2=>'最热下载');
        $html_sort = '';
        foreach ($html_sort_arr as $k => $v) {
            if(!$k) continue;
            //URL拼接
            $href = '/' .$type . '/' .implode('_', $params) .  '_'. $k .'_1.html';
            $cur = ($sort==$k) ? 'class="cur"' : '';
            $html_sort .='<i data-id="'. $k .'"'. $cur .'><a target="_self" href="'. $href .'">'. $v .'</a></i>';
        }
        $this->assign('html_sort',$html_sort);        


        // 右链接条件
        $map['_string'] = 'd.title is not null';

        // 其他条件
        $map['d.category_rootid'] = $type == 'yx' ? 1 : 2;
        if($system){
            $map['m.system'] = $system;
        }
        if($network){
            $map['m.network'] = $network;
        }
        if($category){
            $map['d.category_id'] = $category;
        }

        // 分页获取数据
        M('Down')->alias('d force index(ix_onethink_down_id_view)') ->join('RIGHT JOIN __DOWN_DMAIN__ as m force index(ix_oneth_id_sys) ON m.id = d.id AND d.status=1');
        $count  = M('Down')->where($map)->count('d.id');
        $row = 12; 
        if (!is_numeric($p) || $p<0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理 
        $_GET['p'] = $p; //设置P参数让分页类获取
        $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
        M('Down')->limit($lr, $row);

        // 查询数据 
        M('Down')->alias('d force index(ix_onethink_down_id_view)') ->join('RIGHT JOIN __DOWN_DMAIN__ as m force index(ix_oneth_id_sys) ON m.id = d.id AND d.status=1')->field('d.id,d.title,d.smallimg,d.abet,d.view,d.description');
        if($sort){
            $a_sort = array('', 'update_time', 'view');
            $a_sort[$sort] && M('Down')->order('d.' .$a_sort[$sort]. ' DESC');
            $order =  $a_sort[$sort] ?  ' order by d.' .$a_sort[$sort]. ' DESC' : '';
        }
        $lists = M('Down')->where($map)->select();
        $this->assign('lists',$lists);

        //分页
        if(!$params){
            $f = '';
            for ($i=1; $i <= count($filter) ; $i++) { 
                 $f .='0_';
             } 
            $path = '/'. $type .'/'. $f . $sort .'_{page}.html';
        }else{
            $path = '/' .$type . '/' . implode('_', $params)  . '_'. $sort .'_{page}.html';
        }
        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO 按照指定规则
        $str_seo_arr = array('yx'=>array('好玩的', '游戏', '游戏'), 'rj'=>array('最好用的', '软件', '软件'));
        $str_seo = $str_seo_arr[$type];
        $seo_array['system'] = strtr($seo_array['system'],array('Android'=>'安卓手机', 'IOS'=>'苹果手机', 'PC'=>'电脑', 'TV'=>'电视'));
        $seo_array_str = empty($seo_array['system']) ? '手机'.implode('',$seo_array) : implode('',$seo_array);
        $set_title = array($str_seo[0].$seo_array_str.$str_seo[1].'排行榜', '最新'.$seo_array_str.$str_seo[1].'下载大全');
        if($sort==2){
            $seo['title'] = $set_title[0] . ' - 7230手游网';
            $seo['keywords'] = !empty($seo_array['system']) ? '最热'.$str_seo[0].$seo_array['system'].$str_seo[1].'排行榜,' : '';
            $seo['keywords'] .= !empty($seo_array['network']) ? $seo_array['system'].$seo_array['network'].$str_seo[2].'排行,' : '';
            $seo['keywords'] .= !empty($seo_array['category']) ? $seo_array['system'].$seo_array['category'].$str_seo[2].'排行,' : '';
            $seo['keywords'] .= !empty($seo_array['tag']) ? $seo_array['system'].$seo_array['tag'].$str_seo[2].'排行' : '';
        }else{
            $seo['title'] = $set_title[1] . ' - 7230手游网';
            $seo['keywords'] = !empty($seo_array['system']) ? '最新'.$str_seo[0].$seo_array['system'].$str_seo[1].'下载,' : '';
            $seo['keywords'] .= !empty($seo_array['network']) ? $seo_array['system'].$seo_array['network'].$str_seo[2].'下载,' : '';
            $seo['keywords'] .= !empty($seo_array['category']) ? $seo_array['system'].$seo_array['category'].$str_seo[2].'下载,' : '';
            $seo['keywords'] .= !empty($seo_array['tag']) ? $seo_array['system'].$seo_array['tag'].$str_seo[2].'下载' : '';
        }
        $seo['description'] = '7230手游网提供'. $set_title[0] .','. $set_title[1];
        $this->assign("SEO",$seo);

        //模板选择
        $this->display(T('Down@7230/Index/index'));
    }


    /**
     * 图片库
     * @return void
     */
    public function pic(){
        $type = 'pic';
        //参数
        $params = I('params');
        if(empty($params)){
            $p = $sort = 1;
        }else{
            $params = explode('_', $params);
            $p = array_pop($params);
            $sort = array_pop($params);            
        }

        //筛选条件
        $filter = array();
        //传入参数
        $params && list($tag) = $params;
        //软件标签分类
        $tags = M('Tags')->where('status=1 AND category=10')->getField('id,title');
        $filter['tag'] =array(
            'title'=>'特征',
            'config'=>$tags,
            );

        //参数为空的时候全为0
        if(!$params){
            foreach ($filter as $key => $value) {
                $params[] = 0;
            }
        }

        //生成筛选
        $c = array('fl','wf','ts'); //css class
        $html = '';
        $i = 0;
        foreach ( $filter as $t => $f) {    
            $uparams = $params;        
            if(!current($c)) reset($c);
            $html .='<p class="wz'. each($c)['value'].'">';
            $html .='<b>'. $f['title'] .'：</b>';
            //URL拼接
            $uparams[$i] = 0;
            $href = '/' .$type . '/' . implode('_', $uparams) .  '_'. $sort .'_1.html';
            $html .= empty(${$t}) ? '<s class="cur"><a target="_self" href="'. $href .'">不限</a></s>' : '<s><a target="_self" href="'. $href .'">不限</a></s>';
            foreach ($f['config'] as $k => $v) {
                $cur = (${$t}==$k) ? 'class="cur"' : '';
                //URL拼接
                $uparams[$i] = $k;
                $href = '/' .$type . '/' . implode('_', $uparams) .  '_'. $sort .'_1.html';
                $html .='<s data-id="'. $k .'"'. $cur .'><a target="_self" href="'. $href .'">'. $v .'</a></s>';
            }
            $html .='</p>';
            $html .= PHP_EOL;
            $i++;
        }
        //重置的HTML代码
        $uparams = array_map(function($v){return 0;},$params);
        $html .= '<p class="wz ts"><b>重置：</b><s><a target="_self" href="/' .$type . '/' .  implode('_', $uparams) .  '_0_1.html">全部重置</a></s></p>' . PHP_EOL;
        $this->assign('filter',$html);
        //获取子查询语句
        M('Down')->alias('d')->group('d.id')->field('d.id,d.title,d.cover_id,d.abet,d.view,d.description');
        //M('Down')->join('INNER JOIN __DOWN_DMAIN__ as m ON m.id = d.id');
        //默认条件
         $map['d.status'] = 1;
        //查找壁纸栏目数据
        $map['d.category_rootid'] = 4;
        //有图片数据
        $map['d.cover_id'] = array('gt',0);
        //标签
        if($tag){
            M('Down')->join('INNER JOIN __TAGS_MAP__ as t ON t.did = d.id AND t.type="down"');
            $map['t.tid'] = $tag;
        }
        //排序
        M('Down')->order('d.update_time DESC');

        //获取子查询语句
        $subQuery  = M('Down')->where($map)->buildSql();

        //计算总数
        $count = M()->query("select count(id) as count from $subQuery sub limit 0,1");
        $count = $count[0]['count'];
        //分页获取数据 
        $row = 20; 
        if (!is_numeric($p) || $p<0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理 
        $_GET['p'] = $p; //设置P参数让分页类获取
        $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
        $lists = M()->query("$subQuery limit $lr,$row");
        //var_dump("$subQuery limit ".(($p-1)*$row).",$row");
        $this->assign('lists',$lists);
        $this->assign('count',$count);

        //分页
        if(!$params){
            $f = '';
            for ($i=1; $i <= count($filter) ; $i++) { 
                 $f .='0_';
             } 
            $path = '/'. $type .'/'. $f . $sort .'_{page}.html';
        }else{
            $path = '/'. $type .'/' . implode('_', $params)  . '_'. $sort .'_{page}.html';    
        }
        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出      
        //SEO
        if(empty($tag)){
            $seo = array(
                'title'=>'游戏搞笑图片大全_高清手机壁纸下载 - 7230手游网',
                'keywords'=>'游戏搞笑图片,搞笑图片大全,高清手机壁纸下载',
                'description'=>'7230手游网为您分享搞笑图片,游戏搞笑图片,搞笑图片大全,高清手机壁纸下载',
                );           
        }else{
            $seo = array(
                'title'=>$filter['tag']['config'][$tag].'手机壁纸下载大全 - 7230手游网',
                'keywords'=>$filter['tag']['config'][$tag].'手机壁纸下载,'.$filter['tag']['config'][$tag].'手机壁纸大全',
                'description'=>'7230手游网图片库频道为您提供'.$filter['tag']['config'][$tag].'手机壁纸下载,'.$filter['tag']['config'][$tag].'手机壁纸大全',   
                );         
        }

        $this->assign("SEO",$seo);
        //模板选择
        $this->display(T('Home@7230/Pic/index'));
    }

    /**
     * 搜索结果
     * @return void
     */
    public function search(){

        $keyword =  remove_xss(strip_tags(I('keyword')));
        if (!$keyword) $this->error('请输入关键词！');
        $this->assign('keyword',$keyword);// 赋值关键词

        //下载数据匹配
        $map = array();
        $map['title'] = array('like','%'. $keyword .'%');
        $map['status'] = 1;
        $down = M('Down')->where($map)->limit(0,10)->order('id DESC')->select();  
        //var_dump($down);      
        if($down){
            $this->assign('down', $down);
        }

        //文章数据匹配
        $map = array();
        $map['title'] = array('like','%'. $keyword .'%');
        $map['status'] = 1;
        $document = M('Document')->where($map)->limit(0,5)->order('id DESC')->select(); 
        //var_dump($document);         
        if($document){
            $this->assign('document', $document);
        }


        // //分页获取数据 
        // $row = 10; 
        // $count  = M('Package')
        //                     ->alias('p')
        //                     ->where('p.title like "%'. $keyword .'%" AND p.status=1')
        //                     ->join('RIGHT JOIN __PACKAGE_PMAIN__ as m ON m.id = p.id')
        //                     ->count();// 查询满足要求的总记录数

        // $p = intval(I('p'));
        // if (!is_numeric($p) || $p<0 ) $p = 1;   
        // if ($p > $count ) $p = $count; //容错处理  

        // $lists = M('Package')
        //                 ->alias('p')
        //                 ->where('p.title like "%'. $keyword .'%" AND p.status=1')
        //                 ->join('RIGHT JOIN __PACKAGE_PMAIN__ as m ON m.id = p.id')
        //                 ->order('p.id')
        //                 ->field('*')
        //                 ->page($p, $row)
        //                 ->select();
        // // 赋值数据集
        // $this->assign('lists',$lists);
        // //生成静态地址参数
        // $static_url_params = array('model'=>'Package' , 'module'=>'Package' , 'category'=>'PackageCategory');
        // $this->assign('static_url_params',$static_url_params);

        // $Page       = new \Think\Page($count, $row);// 实例化分页类 传入总记录数和每页显示的记录数
        // $show       = $Page->show();// 分页显示输出
        // $this->assign('page',$show);// 赋值分页输出      

        // //SEO
        $seo['title'] =C('WEB_SITE_TITLE') .'-'. $keyword . '搜索结果';
        $seo['keywords'] = C('WEB_SITE_KEYWORD') .','. $keyword;
        $seo['description'] =C('WEB_SITE_DESCRIPTION').' '. $keyword. '搜索结果';
        $this->assign("SEO",$seo);

        //var_dump($lists);
        //模板选择
        $this->display(T('Home@7230/Search/index'));

    }

 /**
     * Tags
     * @return void
     */
    public function tags(){
	   $infoList=array();
       $name =  strip_tags(I('name'));
       if(!$name) $this->error('Tags不能为空！');
	   $keyword=M('Tags')->where("name='".$name."'")->find();
	  
        if(empty($keyword)){
		 header("HTTP/1.0 404 Not Found");
          exit();
	    }elseif(in_array($keyword['category'],array(1,7))){
            $this->tagList();exit;
        }
        //Tags对应数据匹配
        $info = M('TagsMap')->field('id,did,type')->where("tid='".$keyword['id']."'")->order('id DESC')->select(); 

        if($info){
            $this->assign('info', $info);
        }
	 foreach($info as $k=>$val){
		 $id=$val['did'];
		 $module=ucfirst($val['type']);
		 if($module=='Document'){
			   $doc[] = M('Document')->limit(0,5)->where('id='.$id)->order('id DESC')->find(); 
			 }
		if($module=='Down'){
			   $down[] = M('Down')->limit(0,5)->where('id='.$id)->order('id DESC')->find(); 
			 }
		if($module=='Batch'){
			   $batch[] = M('Batch')->limit(0,5)->where('id='.$id)->order('id DESC')->find(); 
			 }
	   //   $m[] = M($module)->limit(0,5)->where('id='.$id)->order('id DESC')->select(); 
		
		 // $infoList=array_merge($infoList,$m);
		 }

		$this->assign("downList",$down);
	    $this->assign("batchList",$batch);
        $this->assign("docList",$doc);
		  $this->assign('keyword',$keyword['title']);// 获取Tag名
        //SEO
        if(empty($keyword['meta_title'])){
            $seo['title'] =$keyword['title'].'手机游戏下载_软件下载_攻略教程专辑 ' .'-'.C('SEO_STRING');
            $seo['keywords'] =$keyword['title'] . ',手游排行榜,手机软件下载';
            $seo['description'] ='7230手游'. $keyword['title']. '专辑内容提供手机游戏下载，手机软件下载,游戏攻略，软件教程';
        }else{
            $seo['title'] =$keyword['meta_title'];
            $seo['keywords'] =$keyword['keywords'];
            $seo['description'] =$keyword['description'];           
        }
        $this->assign("SEO",$seo);
        //模板选择
        $this->display(T('Home@7230/Tags/index'));

    }

    /**
     * 描述：浏览次数
     * @param int $id
     * @param string $model
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function API_View($id = 0, $model = '')
    {
        $this->API_init();
        if(!is_numeric($id) || empty($model)) return;
        $field = array('Document'=>'view','Package'=>'view','Down'=>'view');
        if(!array_key_exists($model, $field)) return;
        $m = M($model);
        $m->where('id='. $id)->setInc($field[$model]);
    }

    /**
     * 赞
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_Praise($id = 0, $model = ''){
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        if(!is_numeric($id) || empty($model)) return;

        $field = array('Document'=>'ding','Down'=>'abet','Batch'=>'abet','Special'=>'abet','Feature'=>'abet');
        if(!array_key_exists($model, $field)) return; 
        $m = M($model);
        $m->where('id='. $id)->setInc($field[$model]);
        $rs = $this->API_GetPraise($id, $model, true);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 获取赞数
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @param boolean $fuc 是否是函数调用
     * @return void
     */
    public function API_GetPraise($id = 0, $model = '', $fuc = false){
        if(!$fuc){
            $this->API_init();
            $callback = I('callback');            
        }
        if(!is_numeric($id) || empty($model)) return;

        $field = array('Document'=>'ding','Down'=>'abet','Batch'=>'abet','Special'=>'abet','Feature'=>'abet');
        if(!array_key_exists($model, $field)) return; 
        $m = M($model);
        $rs = $m->where('id='. $id)->getField($field[$model]);
        if($fuc){
            return $rs;
        }else{
            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
        }
    }

    /**
     * 批量获取赞数目
     * 由于添加了页面浏览统计功能，此函数前端只能文章、下载、礼包详情页面
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_BatchGetPraise($b = array()){
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        if(empty($b)) return;

        $rs =array();
        foreach ($b as $id => $model) {
           $num = $this->API_GetPraise($id, $model, true);
           $rs[$id] = $num;
           //浏览统计（浏览次数加1，这个问了刘盼、肖书成，说在前端只有文章、下载、礼包详情页面用到过，故浏览次数加1功能可以放这）add by 谭坚
           $this->API_View($id,$model);
        }
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }


    /**
     * 评论列表接口
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_comment($id, $model){
        $this->API_init();
        $callback = I('callback');

        if(!is_numeric($id) || empty($model)) return;

        $m = M('Comment');
        $map =array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);

        $rs = $m->where($map)->field('id,uname,message,add_time')->order('id ASC')->limit(0,10)->select();
        if($fuc){
            return $rs;
        }else{
            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
        }
    }

    /**
     * 显示评论页面
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function commentIndex($id, $model){
        if(!is_numeric($id) || empty($model)) return;
        $m = M('Comment');
        $map =array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);

        $count = $m->where($map)->count();
        $row = 20;  
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;   
        if ($p > $count ) $p = $count; //容错处理
        $lists = $m->where($map)->field('id,uname,message,add_time')->page($p, $row)->order('id ASC')->select();
        $path = C('STATIC_URL') . '/P7230/commentIndex/id/'. $id .'/model/'. $model .'/p/{page}' ;
        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 
        // $Page->setConfig('first','首页');
        // $Page->setConfig('end','尾页');
        // $Page->setConfig('prev',"上一页");
        // $Page->setConfig('next','下一页');
        // $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $this->assign('lists',$lists);// 赋值数据集
        $this->assign('count',$count);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('id',$id);
        $this->assign('model',$model);
        $this->assign('sp',($p-1)*$row);

        //查找数据
        $d = M(ucfirst(strtolower($model)));
        $info = $d->getById($id);
        $this->assign('info',$info);// 赋值分页输出

        //SEO
        $this->assign("SEO",WidgetSEO(array('detail',ucfirst(strtolower($model)), $id), '7230'));
        //模板选择
        $this->display(T('Home@7230/Comment/index'));
    }


    /**
     * 提交评论跨域代理
     * @return void
     */
    public function API_commentSubPro(){
       
    }

    /**
     * 提交评论
     * @return void
     */
    public function API_commentSub(){
        $this->API_init();
        $callback = I('callback');

        $id = I('id');
        $model = I('model');        
        if(!is_numeric($id) || empty($model)) return;

        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
    }

    /**
     * @comments 开测开服下拉加载更多
     * @param $cate
     * @param string $start
     * @return mixed
     */
    public function AjaxTestServer(){
        //JSONP或其他src方式的回调函数名
        $callback   =       I('callback');
        $cate       =       I('cate');
        $start      =       I('start');
        $where      =       I('con');
        if(!in_array($where,array('',1,2))) return false;
        if(!in_array($cate,array(3,5))) return false;
        $where      =       $where?' AND conditions & '.$where:'';
        if((int)$start <10) return false;

        //查询数据
        $listPackage = M('package')->alias('a')->field('a.id,a.title,a.cover_id,b.tid,c.*')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->join('__PACKAGE_PARTICLE__ c ON a.id = c.id')->where('b.type = "package" AND a.category_id = '.$cate.$where)->limit($start,12)->order('a.id DESC')->select();

        //处理附加的游戏数据
        foreach($listPackage as $k=>&$v){
            if(!empty($v['tid'])){
                $down =  M('Down')->alias('a')->field('a.id game_id,a.title game_title,b.system,b.picture_score,b.music_score,b.feature_score,b.run_score,c.name company')->join('__DOWN_DMAIN__ b ON a.id = b.id','LEFT')->join('__COMPANY__ c ON b.company_id = c.id','LEFT')->join('__PRODUCT_TAGS_MAP__ d ON a.id = d.did','LEFT')->where('d.tid = '.$v['tid'].' AND d.type = "down"')->select();
                if(!empty($down)){
                    foreach($down as $k1=>&$v1){
                        $v1['score'] = round(($v1['picture_score'] + $v1['music_score'] + $v1['feature_score'] + $v1['run_score'])*5/10,1);
                        if($v1['system']=="1" && empty($v['androidId'])){
                            $v['androidId']= $v1['game_id'];
                        }

                        if($v1['system']=="2" && empty($v['IOS'])){
                            $v['IOS'] = $v1['game_id'];
                        }
                    }
                    unset($v1);
                    $v = array_merge($v,$down[0]);
                };

                //根据产品标签查找最新的礼包
                $package = M('package')->alias('a')->field('a.id')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('a.category_id IN(1,2,4) AND b.tid = '.$v['tid'].' AND b.type = "package"')->order('a.create_time DESC')->select();
                $v['packageId'] = $package[0]['id'];
            }
            //处理格式
            $v['start_time']    = date('Y-m-d',$v['start_time']);
            $v['game']          = staticUrl('detail',$v['androidId'],'Down');
            $v['IOS']           = staticUrl('detail',$v['IOS'],'Down');
            $v['package']       = staticUrl('detail',$v['packageId'],'Package');
            $v['imgurl']        = get_cover($v['cover_id'],'path');
        }
        unset($v);

        echo $callback ? $callback.'('.json_encode($listPackage).');' : json_encode($listPackage);
    }

    /**
     * Author: x i a o
     * 描  述:7230PC版下载标签页
     * time :2015/07/08
     */
    public function tagList(){
        $name   =   I('name');

        $tid = M('tags')->field('id,category,title,meta_title,keywords,description,icon')->where("`name` = '$name' AND status = 1")->find();

        if($tid){
            $sql    =   M('TagsMap')->alias('a')->field('b.id,b.title,b.description,b.update_time,b.smallimg,c.size,c.rank')
                ->join('__DOWN__ b ON a.did = b.id')
                ->join('__DOWN_DMAIN__ c ON b.category_id = c.id')
                ->where("a.tid = ".$tid['id']." AND a.type = 'down' AND b.status = 1 ")->order('a.sort DESC,b.level DESC,b.update_time DESC')->buildSql();

            //数据统计
            $count  =   M()->query("SELECT count(id) count FROM $sql sub ");
            $count  =   $count[0]['count'];

            //分页参数处理
            $p      =   I('p');
            if (!is_numeric($p) || $p<0 ) $p = 1;
            $row    =   12;
            $str    =   ($p-1)*$row;

            //列表数据
            $lists  =   M()->query("$sql limit $str,$row");

            //SEO
            $seo['title']       =   $tid['meta_title'] . ((int)$p>1?'(第'.$p.'页)':''); //.' (第'.$p.'页) - 7230手游网';
            $seo['keywords']    =   $tid['keywords'];
            $seo['description'] =   $tid['description'];

            //分页
            $path   =   C('STATIC_URL').'/tag/'.$name.'/{page}.html';

            $Page       = new \Think\Page($count,$row,'',false,$path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->firstPath= C('STATIC_URL').'/tag/'.$name;
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last','尾页');
            $show       = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出

            $this->assign(array(
                'info'      =>  $tid,
                'SEO'       =>  $seo,
                'count'     =>  $count,
                'lists'     =>  $lists,
            ));
            $this->display(T('Down@7230/Widget/tagList'));
        }
    }

}
