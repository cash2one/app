<?php
// +----------------------------------------------------------------------
// | PC6动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class PC6Controller extends BaseController {

    /**
     * API接口初始化
     * @return void
     */
    protected function API_init(){
        //结果关闭trace
        C('SHOW_PAGE_TRACE', false);      
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if($referer){
            $referer = parse_url($referer);
            $host = $referer['host'];
            if(in_array($host, $cors)){
                header('Access-Control-Allow-Origin:http://'. $host);
            }        
        }
    }


     /**
     * 礼包API接口 根据产品标签获取礼包数据
     * @return void
     */
    public function API_PackgeForPTags(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $ptag = I('ptag');
            if(empty($ptag)){
                echo 'null';
                return;
            }
            $ptag_id = M('ProductTags')->where('status = 1 AND title="'.$ptag.'"')->getField('id');
            if(empty($ptag_id)){
                echo 'null';
                return;
            }           
            //根据产品标签查找数据

            $map['t.tid'] = $ptag_id;
            $map['t.type'] = "package";
            $map['d.status'] = 1;
            //$map['d.status']  = array('in','1,-1');
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PMAIN__ as m ON m.id = d.id')
                            ->group('d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->join('INNER JOIN __PRODUCT_TAGS_MAP__ as t ON t.did = d.id')
                            ->where($map)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    // 添加移动版地址 crohn 2015-6-19
                    $value['m_url'] = staticUrlMobile('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                    //获取卡号数
                    $value['card_sur'] = $Card->getCardsCount($value['id']);
                    $value['card_all'] =  $Card->getAllCardsCount($value['id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }

    }

     /**
     * 礼包API接口 获取最新礼包数据
     * @return void
     */
    public function API_PackgeLists(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $num = I('num');
            if(!is_numeric($num)) $num = 10;
  
            //根据产品标签查找数据
            $map = array();
            $map['d.status'] = 1;
            //$map['d.status'] = array('in','1,-1');
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PMAIN__ as m ON m.id = d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->where($map)->order('id DESC')->limit($num)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    // 添加移动版地址 crohn 2015-6-19
                    $value['m_url'] = staticUrlMobile('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                    //获取卡号数
                    $value['card_sur'] = $Card->getCardsCount($value['id']);
                    $value['card_all'] =  $Card->getAllCardsCount($value['id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }
    }

     /**
     * 礼包API接口 获取最新开服开测数据
     * @return void
     */
    public function API_PackgeListsK(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $num = I('num');
            if(!is_numeric($num)) $num = 10;
            //cate
            $cate = I('cate');
            $cate = $cate==3 ? 3 : 6;
  
            //根据产品标签查找数据
            $map = array();
            $map['d.status'] = 1;
            //$map['d.status'] = array('in','1,-1');
            $map['d.category_id'] = $cate;
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PARTICLE__ as m ON m.id = d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->where($map)->order('id DESC')->limit($num)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    // 添加移动版地址 crohn 2015-6-19
                    $value['m_url'] = staticUrlMobile('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }
    }
    
    /**
     * 产品标签列表
     * @date: 2015-7-1
     * @author: liujun
     */
    public function productTagsIndex(){
    	$where = array('status'=>1);
    	$order = 'sort ASC,update_time DESC';
    	$count = M("ProductTags")->field('id')->where($where)->order($order)->count();
    	$list = M("ProductTags")->field('id,category,name,title')->where($where)->order($order)->limit(10,$count)->select();
    	$seo = array(
    			'title' => ' 手游礼包全部游戏_热门手游礼包_最新兑换码领取_' .C('SITE_NAME').'礼包中心',
    			'keywords' => '热门手机游戏,手游礼包,礼包大全,兑换码领取,手机游戏,'.C('SITE_NAME').'礼包',
    			'description' => '手机游戏礼包大全尽在'.C('SITE_NAME').'礼包中心。礼包中心为您提供最新热门手机游戏礼包、兑换码。',
    	);
    	$this->assign('SEO',$seo);
    	$this->assign('list',$list);
    	
    	//生成静态地址参数
    	$static_url_params = array('model'=>'Package' , 'module'=>'Package' , 'category'=>'PackageCategory');
    	$this->assign('static_url_params',$static_url_params);
    	
    	$this->display(T('Package@pc6/ProductTags/index'));
    }
    
    /**
     * 产品标签详情页面：获取礼包列表
     * @date: 2015-7-1
     * @author: liujun
     */
    public function productTagsDetail(){
    	$name = I('name');//产品标签标识名
		$limit = !empty(I('limit'))?I('limit'):15;//记录条数
		$p = intval(I('p'));//第几页
		$count = 0;
		
        //获取游戏产品标签信息
        $info = M('ProductTags')->where(array('status'=>1,'name'=>$name))->find();
        if (!$info) $this->error('页面不存在！');
        
        //获取所有礼包
        $field = 'p.id,p.title,m.end_time,m.content,m.activation';
        $join = 'right join __PACKAGE__ as p ON t.did = p.id left join __PACKAGE_PMAIN__ as m on p.id = m.id';
        $where = array('status'=>1);
        $where['t.type'] = 'package';//类型：礼包
        $where['t.tid'] = $info['id'];
        
        $count = M('ProductTagsMap')->alias('t')->join($join)->field($field)->where($where)->count();//总记录数
        
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
        
        $list = M('ProductTagsMap')->alias('t')->join($join)->field($field)->where($where)->page($p,$limit)->order('t.update_time DESC')->select();
        //echo M('ProductTagsMap')->getLastSql();exit;
        
        $path = '/tag/'.$info['name'].'/{page}'.getStaticExt();
        $Page = new \Think\Page($count, $limit, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show = $Page->show();// 分页显示输出
        
        $seoTitle = $info['title'];
        if(!strstr($seoTitle, '礼包')){
        	$seoTitle = $seoTitle.'礼包';
        }
        $seo = array(
        	'title' => $seoTitle.'兑换码大全_'.$seoTitle.'兑换码领取_'.C('SITE_NAME').'礼包中心',
        	'keywords' => $seoTitle.'，'.$info['title'].'激活码，'.$seoTitle.'兑换码领取',
        	'description' => C('SITE_NAME').'礼包中心'.'为玩家们免费提供'.$seoTitle.'兑换码大全，'.$seoTitle.'兑换码大全主要汇总了'.$seoTitle.'、'.$seoTitle.'兑换码、'.$seoTitle.'独家礼包等，欢迎玩家前来领取。',
        );
        $result = array(
			'SEO' => $seo,
        	'listCount' => $count,
        	'page' => $show,
        	'info' => $info,//产品标签详情
        	'list' => $list,//礼包记录
        );
        
        //生成静态地址参数
        $static_url_params = array('model'=>'Package' , 'module'=>'Package' , 'category'=>'PackageCategory');
        $this->assign('static_url_params',$static_url_params);
        
        $this->assign($result);
    	$this->display(T('Package@pc6/ProductTags/detail'));
    }
}