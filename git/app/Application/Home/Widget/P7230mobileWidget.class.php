<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-12-18
 * Time: 下午5:38
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Widget;

use Home\Controller\BaseController;

class P7230mobileWidget extends BaseController{

	public function tools(){
      $this->assign("system",get_device_type());//手机系统判断
      $this->display(T('Home@7230mobile/Widget/tools'));
    }
	
    public function navi(){
      $this->display(T('Home@7230mobile/Public/navi'));
    }
	
	public function everyoneLike(){
        $where = array(
            'map' => array('_string' => 'position & 1')
        );
       $dataList = D('Down')->listsWhere($where, true);

        //判断手机操作系统
        $sys = get_device_type(true);
        if(!empty($sys)){
            if($sys == 'Android'){
                $dd_map['system'] = 1;
            }else if($sys == 'iPhone'){
                $dd_map['system'] = 2;
            }
            foreach($dataList as $key=>$value){
                $dd_map['id'] = $value['id'];
                $dd_res = M('down_dmain')->field('id')->where($dd_map)->find();
                if(!empty($dd_res) && $dd_res['id']==$value['id']){
                    $dataList[$key][] = $value;
                }

            }
        }

       $this->assign('lists',$dataList);// 赋值数据集
       $this->display(T('Home@7230mobile/Widget/everyoneLike'));
    }
	
	public function tagCloud(){
	  $map['category'] = array('EQ',1);
	  $tags=M('Tags')->order('create_time desc')->where($map)->limit(20)->select();
	  $this->assign('tags',$tags);
      $this->display(T('Home@7230mobile/Widget/tagcloud'));
    }
	  /**
    * 生成头部
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function head(){
      $this->assign('url',C('MOBILE_STATIC_URL'));
      $this->display(T('Home@7230mobile/Public/mobile2/header'));
  }

   /**
    * 生成友情链接
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function footIndex(){
	  
      $this->display(T('Home@7230mobile/Public/footerIndex'));
    }

    /**
    * 生成尾部
    * @param string $content 内容
    * @return string 处理过后的内容
    */
    function foot(){
      $this->display(T('Home@7230mobile/Public/mobile2/footer'));
    }
	  /**
     * Banner
     */
   public function banner(){
	 $arrH=array();//首页横图
	 $docH= M('Document')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
	 foreach ($docH as $k => $v) {$docH[$k]['type'] = 'document';}

       //判断手机操作系统
       $sys = get_device_type(true);
       if($sys == 'Android'){
           $where = ' And dd.system=1';
       }else if($sys == 'iPhone'){
           $where = ' And dd.system=2';
       }else{
           $where = '';
       }
     $downH = M('Down')->alias('d')->join('LEFT JOIN __DOWN_DMAIN__ dd ON d.id = dd.id ')->field('d.id,d.title,d.description,d.cover_id,d.level,d.update_time')
         ->where("(d.home_position='1' OR d.home_position='3')".$where)->order('d.update_time desc')->limit(4)->select();

	 foreach ($downH as $k => $v) {$downH[$k]['type'] = 'down';}
	 
	 $packageH= M('Package')->field('id,title,description,cover_id,level,update_time')->where("home_position='1' OR home_position='3'")->order('update_time desc')->limit(4)->select();
	 foreach ($packageH as $k => $v) {
         $packageH[$k]['type'] = 'package';
       }
	   
	 $arrH=array_merge((array)$docH,(array)$downH,(array)$packageH);
	 $arrH=array_sort($arrH,'update_time',SORT_DESC);//首页横图
	 $this->assign('bannerH',$arrH);//首页横图
     $this->display(T('Home@7230mobile/Widget/banner'));
  }

        /**
        * SEO
        * @param  string $type 类型
        * @param  string $module 模块名
        * @param  integer $cid or $id
        *@return array
        */
      public function SEO($type, $module = null, $id = null){
          $seo = array();
          switch ($type) {
              case 'index':
                  $seo['title'] =C('MOBILE_WEB_SITE_TITLE');
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
                  $seo['title'] =empty($cate['title'])?$cate['title']:$cate['title'];
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
                      'DOWN' => 'DownCategory'
                  );
                  $cate=D($cate_name[$module])->where("id='$id'")->find();
                  $seo['title'] =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
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
                          $title = $detail['seo_title'] . $detail['version']; 
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
                      $t = array();                
                      $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title; 
                      $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                      ksort($t);
                      $seo['title'] = implode(' - ', $t);                      
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
        * content字段图片和内置插入标签处理
        * @param string $content 内容
        * @return string 处理过后的内容
        */
        function contentProcess($content){
            //图片暂时兼容处理
          $content = preg_replace('/src\=\"(\/up\/.+?)/i', 'src="'. C('PIC_HOST') .'$1', $content);
            //内置标签处理
          echo $content;
      }


    /**
     * @Author 肖书成
     * @createTime 2015/3/12
     * @Comments 手机专题，K页面，专区列表
     */
    public function zt(){
        $key = I('key');
        $id = I('id');
        if(in_array($key,array('zt','k','zq'))){
            $this->ztList($key,$id);
        }
    }
    /**
     * 描述：厂商详细页
     * @param $id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function csDetail($id)
    {
        if(empty($id))
        $id = I('id');
        if(is_numeric($id))
        {
           $where['id'] = array('eq',$id);
           $where['status'] = array('eq',1);
           $info = M('Company')->where($where)->field(true)->find();
           $field = 'a.id as id,a.title as title,a.smallimg as smallimg,c.size as size';
           $limit = 5; //默认5个
           $order = 'a.update_time desc';
           $limit+=1; //获取六个，判断是否要加载更多
           $game_list_info_android = $this->getCsField($id,2,$field,$limit,$order,1); //获取最新个厂商5个android游戏
           $game_list_info_ios = $this->getCsField($id,2,$field,$limit,$order,2); //获取最新厂商下5个ios游戏
           $soft_list_info_android = $this->getCsField($id,1,$field,$limit,$order,1); //获取厂商下最新5个android软件
           $soft_list_info_ios = $this->getCsField($id,1,$field,$limit,$order,2); //获取厂商下最新5个ios软件
           $game_count_android = count($game_list_info_android);
           $game_count_ios = count($game_list_info_ios);
           $soft_count_android = count($soft_list_info_android);
           $soft_count_ios = count($soft_list_info_ios);
           $this->getCompanyInfo();
           //判段android和ios平台游戏是否存在
           if($game_count_android >0 && $game_count_ios > 0)
           {
               $game_exist = 1;
           }
           else if($game_count_android >0 && $game_count_ios <= 0)
           {
               $game_exist = 2;
           }
           else if($game_count_android <=0 && $game_count_ios > 0)
           {
               $game_exist = 3;
           }
           else
           {
               $game_exist = 0;
           }
            //判段android和ios平台游戏软件是否存在
            if($soft_count_android >0 && $soft_count_ios > 0)
            {
                $soft_exist = 1;
            }
            else if($soft_count_android >0 && $soft_count_ios <= 0)
            {
                $soft_exist = 2;
            }
            else if($soft_count_android <=0 && $soft_count_ios > 0)
            {
                $soft_exist = 3;
            }
            else
            {
                $soft_exist = 0;
            }

           if($game_count_android == "6")
           {
               array_pop($game_list_info_android);
               $is_more_android_game = 1;
           }
           if($game_count_ios == "6")
           {
               array_pop($game_list_info_ios);
               $is_more_ios_game = 1;
           }
           if($soft_count_android == "6")
           {
               array_pop($soft_list_info_android);
               $is_more_android_soft = 1;
           }
           if($soft_count_ios == "6")
           {
               array_pop($soft_list_info_ios);
               $is_more_ios_soft = 1;
           }
           $this->getCsCourse($id,1,$limit);
           $this->getCsCourse($id,2,$limit);
           $seo['title'] = $info['title'];
           $seo['keywords'] = $info['keywords'];
           $seo['description'] = $info['description'];
           $this->assign("SEO",$seo);//SEO
           $this->assign('is_more_android_game',$is_more_android_game);
           $this->assign('is_more_ios_game',$is_more_ios_game);
           $this->assign('is_more_android_soft',$is_more_android_soft);
           $this->assign('is_more_ios_soft',$is_more_ios_soft);
           $this->assign('game_list_info_android',$game_list_info_android);
           $this->assign('game_list_info_ios',$game_list_info_ios);
           $this->assign('soft_list_info_android',$soft_list_info_android);
           $this->assign('soft_list_info_ios',$soft_list_info_ios);
           $this->assign('soft_exist',$soft_exist);
           $this->assign('game_exist',$game_exist);
           $this->assign('info',$info);

        }
        $this->display(T('Home@7230mobile/Widget/csmobile'));
    }

    /**
     * 描述：获取相关厂商信息
     * @param string $limit
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCompanyInfo($limit = "6")
    {
        $where['status'] = array('eq',1);
        $count = M('Company')->where($where)->count();
        $start = rand(0,($count-$limit)); //获取一个随机数
        $company_list =  $info = M('Company')->where($where)->field(true)->limit($start,$limit)->select();
        if(!empty($company_list))
        {
            foreach($company_list as $k=>&$v){
                $v['url'] = C('MOBILE_STATIC_URL').'/'.$v['path'].'/';
            }
        }
        $this->assign('company_list',$company_list);
    }
    /**
     * 描述：获取厂商相关游戏或者软件攻略或者教程
     * @param $id
     * @param $type
     * @param string $limit
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getCsCourse($id,$type,$limit='5')
    {
        if(is_numeric($id))
        {
            $field="a.id as id";
            $rs = $this->getCsField($id,$type,$field);
            $ids = array();
            if(is_array($rs) && !empty($rs))
            {
                foreach($rs as $val)
                {
                    $ids[] = $val['id'];
                }
            }
            if(!empty($ids))
            {
               $where['did'] = array('in',$ids);
               $where['type'] = 'down';
               $tids = M('Product_tags_map')->where($where)->getField('tid',true);
               if(!empty($tids))
               {
                   unset($where);
                   $where['b.tid'] = array('in',$tids);
                   $where['b.type'] = 'document';
                   $where['a.status'] = '1';
                   $count = M('Document')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did=a.id')->field('a.id as id,a.title as title')->where($where)->count();
                   $list =  M('Document')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did=a.id')->field('a.id as id,a.title as title')->where($where)->order('a.update_time desc')->limit($limit)->select();
               }
            }
        }
        $is_more = ($count > $limit) ? 1 : 0;
        $is_exist = $count > 0 ? 1 : 0;
        if($type == "1")
        {
            $this->assign('is_more_soft',$is_more);
            $this->assign('soft_list_course',$list);
            $this->assign('is_exist_soft',$is_exist);
        }
        else
        {
            $this->assign('is_more_game',$is_more);
            $this->assign('game_list_course',$list);
            $this->assign('is_exist_game',$is_exist);
        }
    }
    /**
     * 描述：获取厂商游戏的相关字段（由于以前没有区分软件和游戏类型，所以这个函数以分类id为准）
     * @id 厂商id
     * @type 1表示网络游戏，2表示网络游戏
     * @field厂商游戏相关字段
     * @limit 获取数据条数
     * @order排序
     * @system系统版本（用于搜索条件）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected  function getCsField($id,$type,$field="a.id as id",$limit = "",$order="a.id desc",$system="")
    {
       if(is_numeric($id))
       {
           if($type =="1")
           {
               $map['b.rootid'] = array('in','2,70');   //由于以前缺陷，所以这一只有写死 5表示铃声，不知道铃声是否算软件
           }
           else
           {
               $map['b.rootid'] = array('not in','38,2,5,70'); //由于以前缺陷，所以这一只有写死
           }
           $map['a.status'] = 1;
           $map['b.status'] = 1;
           $map['c.company_id'] = $id;
           if($system) $map['c.system'] = $system;
           //获取相关厂商的游戏字段
           if($limit)
           $rs =  M('Down')->alias('a')->join('__DOWN_DMAIN__ c ON c.id=a.id')->join('__DOWN_CATEGORY__ b on b.id=a.category_id')->field($field)->where($map)->limit($limit)->order($order)->select();
           else
           $rs =  M('Down')->alias('a')->join('__DOWN_DMAIN__ c ON c.id=a.id')->join('__DOWN_CATEGORY__ b on b.id=a.category_id')->field($field)->where($map)->order($order)->select();
       }
        return $rs;
    }
    /**
     * 描述：厂商列表
     * @param $key
     * @param $id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public  function csList(){
        $table = 'company';
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);
        //分页获取数据
        $count  = M($table)->where('status > 0')->count();// 查询满足要求的总记录数
        $row = 8;
        //是否返回总页数
        if(I('gettotal')){
            if(empty($count)){
                echo 1;
            }else{
                echo ceil($count/$row);
            }
            exit();
        }
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = M($table)->where('status > 0')->order('id DESC')->page($p, $row)->select();
        foreach($lists as $k=>&$v){
            $v['url'] = C('MOBILE_STATIC_URL').'/'.$v['path'].'/';
        }
        //seo关键字
        $seo['title'] = $lists[0]['title'];
        $seo['keywords'] = $lists[0]['keywords'];
        $seo['description'] = $lists[0]['description'];
        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        /*
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        */
        $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE% ');
        $show = $Page->show();// 分页显示输出
        $this->assign(array(
            'page'=>$show,// 赋值分页输出
            'lists'=>$lists,// 赋值数据集
            "SEO"=>$seo//SEO
        ));

        $this->display(T('Home@7230mobile/Widget/cs'));
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/12
     * @Comments 此方法仅供zt方法使用
     */
    private  function ztList($key,$id){
        $table = array('zt'=>'feature','k'=>'special','zq'=>'batch');
        $config =array('zt'=>C(FEATURE_ZT_DIR),'k'=>C(FEATURE_K_DIR),'zq'=>C(FEATURE_ZQ_DIR));


        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);

        //分页获取数据
        $count  = M($table[$key])->where('pid=0 AND interface = 1')->count();// 查询满足要求的总记录数

        $row = 8;

        //是否返回总页数
        if(I('gettotal')){
            if(empty($count)){
                echo 1;
            }else{
                echo ceil($count/$row);
            }
            exit();
        }

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = M($table[$key])->where('pid=0 AND interface = 1')->order('id DESC')->page($p, $row)->select();
        foreach($lists as $k=>&$v){
            $v['url'] = '/'.$config[$key].'/'.$v['url_token'];
        }

        $path = $page_info['path'] . getStaticExt();

        $Page = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();// 分页显示输出
        $this->assign(array(
            'key'=>$key,//页面标识
            'page'=>$show,// 赋值分页输出
            'lists'=>$lists,// 赋值数据集
            "SEO"=>WidgetSEO(array('special',null,$id))//SEO
        ));

        $this->display(T('Home@7230mobile/Widget/zq'));
    }

    /*******************************************手机二次改版*******************************************/
    /**
     * 作者:肖书成
     * 时间:2015/10/20
     * 描述：首页调用逻辑
     */
    public function home2(){
        //幻灯片 规则：调用下载和文章的全站推荐横图
        $slide1     = M('Down')->field('id,title,model_id,cover_id,update_time')->where('home_position & 1 AND status = 1 AND pid = 0 AND cover_id > 0')->order('update_time DESC')->limit(10)->select();

        $slide2     = M('Document')->field('id,title,model_id,cover_id,update_time')->where('home_position & 1 AND status = 1 AND pid = 0 AND cover_id > 0')->order('update_time DESC')->limit(10)->select();

        $slide = array_filter(array_merge((array)$slide1,(array)$slide2));

        $slideSort = array();
        foreach($slide as $k=>&$v){
            $slideSort[] = $v['update_time'];
            if($v['model_id']   == '13'){
                $v['model']     = 'Down';
            }else{
                $v['model']     = 'Document';
            }
        }

        //幻灯片 按照更新时间排序
        array_multisort($slideSort,SORT_DESC,$slide);

        //网游标签
        $gameTags = M('TagsMap')->alias('a')->field('b.id,b.name,b.title,count(b.id) num')->join('__TAGS__ b ON a.tid = b.id')->join('__DOWN__ c ON a.did = c.id')
                    ->where('b.status = 1 AND b.category = 1 AND c.status = 1')->group('b.id')->order('num DESC')->limit(5)->select();

        //软件标签
        $softTags = M('TagsMap')->alias('a')->field('b.id,b.name,b.title,count(b.id) num')->join('__TAGS__ b ON a.tid = b.id')->join('__DOWN__ c ON a.did = c.id')
            ->where('b.status = 1 AND b.category = 7 AND c.status = 1')->group('b.id')->order('num DESC')->limit(5)->select();

        //友情链接
        $link   = M('Link')->field('title,url_token')->where('`group` = "3" AND status = 1')->order('sort DESC')->select();

        //应用分类
        $gameCate   =   M('DownCategory')->field('id,title')->where('pid = 1 AND status =1')->order('sort ASC')->select();
        $softCate   =   M('DownCategory')->field('id,title')->where('pid = 2 AND status =1')->order('sort ASC')->select();

        //大图一
        $img1       =   M('Tags')->field('id,name,title,img')->where('category = 1 AND status = 1 AND pid !=0')->order('id DESC')->find();

        //大图二
        $img2       =   M('Tags')->field('id,name,title,img')->where('category = 7 AND status = 1 AND pid !=0')->order('id DESC')->find();

        $this->assign(array(
            'slide'     => $slide,
            'link'      => $link,
            'gameCate'  => $gameCate,
            'gameTags'  => $gameTags,
            'softCate'  => $softCate,
            'softTags'  => $softTags,
            'url'       => C('MOBILE_STATIC_URL'),
            'img1'      => $img1,
            'img2'      => $img2
        ));

        $this->display('Widget/home2');
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/20
     * 描述：首页的热门，单机，善用软件 调用的数据
     * @param int $cate
     * @param int $network
     */
    public function down_list($cate,$network){
        $where =    "b.category_rootid = $cate" . ($network?" AND c.network = $network":'');
        $data  =    M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.description,b.view,c.size,d.title cate')
                    ->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')
                    ->join('__DOWN_CATEGORY__ d ON b.category_id = d.id')->where($where." AND a.type='down' AND b.status = 1 AND b.pid = 0 AND c.size !=0")
                    ->order('b.update_time DESC')->group('a.tid')->limit(5)->select();

        $this->assign('data',$data);
        $this->display('Widget/down_list');
    }

    /**
     * 作者:肖书成
     * 描述:小编精选
     * 条件:推荐位，频道首页精品推荐
     */
    public function jx(){
        $data = M('ProductTagsMap')->alias('a')->field('b.id,b.title,b.smallimg,c.size')
            ->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')
            ->where("a.type='down' AND b.position & 1 AND b.status = 1 AND b.pid = 0")
            ->order('b.update_time DESC')->group('a.tid')->limit(8)->select();

        $this->assign('data',$data);
        $this->display('Widget/jx');
    }


    /**
     * 作者:肖书成
     * 描述:手机分类页面（游戏、软件）
     * @param $cate
     */
    public function m2Cate($cate,$network=false){
        $cate = $cate?$cate:I('cate');
        $network    =   $network?$network:I('network');

        $WidgetSEO  =   I('page_id');

        //分类信息、数据
        $info       =   M('DownCategory')->field('id,title')->where("id = $cate AND status = 1")->find();
        $category   =   M('DownCategory')->field('id,title')->where("pid = $cate AND status = 1")->order('sort ASC')->select();

        //最新数据
        $where      =   "category_rootid = $cate".($network?" AND network = $network":'');
        $new        =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,a.description,a.view,b.size,c.title cate')
                        ->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c on a.category_id = c.id')
                        ->where("$where AND a.status = 1 AND a.pid = 0")->order('a.update_time DESC')
                        ->limit(10)->select();

        //推荐数据
        $position   =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,a.description,a.view,b.size,c.title cate')
                        ->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c on a.category_id = c.id')
                        ->where("$where AND position & 1 AND a.status = 1 AND a.pid = 0")->order('a.update_time DESC')
                        ->limit(10)->select();

        //页面标题
        $title      =   $network?($network == '1'?'单机':'网游'):$info['title'];

        $this->assign(array(
            'title'     =>  $title,
            'info'      =>  $info,
            'cate'      =>  $category,
            'new'       =>  $new,
            'position'  =>  $position,
            'SEO'       =>  WidgetSEO(array('special',null,$WidgetSEO)),
            'url'       =>  C('MOBILE_STATIC_URL'),
            'network'   =>  $network?$network:'false'
        ));

        $this->display('Widget/m2Cate');
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/26
     * 描述:手机专题合集
     */
    public function m2_zt(){
        $this->hj('Home@7230mobile/Widget/hj','Feature','pid = 0 AND interface = 1 AND enabled = 1','id DESC',12,true);
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/26
     * 描述:手机专区合集
     */
    public function m2_zq(){
        $this->hj('Home@7230mobile/Widget/hj','Batch','pid = 0 AND interface = 1 AND enabled = 1','id DESC',12,true);
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/26
     * 描述:手机K页面合集
     */
    public function m2_k(){
        $this->hj('Home@7230mobile/Widget/hj','Special','pid = 0 AND interface = 1 AND enabled = 1','id DESC',12,true);
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/26
     * 描述:总合集
     */
    private function hj($template,$table,$where = 1,$order = 'id desc',$row = 18,$isMobile = false,$pageTitle=''){
        $baseWidget = new \Common\Controller\WidgetController();
        $baseWidget->hj($template,$table,$where,$order,$row,$isMobile,$pageTitle);
    }


    /**
     * 作者:肖书成
     * 时间:2015/10/27
     * 描述:搜索页面
     */
    public function m2search(){
        $this->assign('SEO',WidgetSEO(array('special',null,I('page_id'))));
        $this->display('Widget/search');
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/28
     * 描述:总标签页
     */
    public function tags(){
        $tags =     M('Tags')->field('id,category,name,title,pid')->where('category IN(1,7) AND status = 1 AND display = 1')->select();

        $tags   =   list_to_tree($tags,'id','pid','child',0);

        $game       =   array();
        $gameChild  =   array();
        $soft       =   array();
        $softChild  =   array();
        foreach($tags as $k=>$v){
            if($v['category'] == '1'){
                if(empty($v['child'])){
                    $gameChild[]= $v;
                }else{
                    $game[]     =   $v;
                }
            }else{
                if(empty($v['child'])){
                    $softChild[]=   $v;
                }else{
                    $soft[]     =   $v;
                }
            }
        }

        //判断是否含有其他标签
        if(!empty($gameChild)){
            $game[] =   array('name'=>'game','title'=>'其他','child'=>$gameChild);
        }

        if(!empty($softChild)){
            $soft[] =   array('name'=>'soft','title'=>'其他','child'=>$softChild);
        }

        $this->assign(array(
            'SEO'   =>  WidgetSEO(array('special',null,I('page_id'))),
            'game'=>$game,
            'soft'=>$soft,
            'url'   =>  C('MOBILE_STATIC_URL')
        ));

        $this->display('Widget/tags');
    }


       /**
    * 排行榜
    */
    public function ph (){
  //安卓下载排行ID 为31853
        $softid=M('_down_paihang')->where('id=31853')->getField(soft_id);

        $adata=M()->table('onethink_down as a,onethink_down_category as b ,onethink_down_dmain as c')->field('a.id,a.category_id,a.title,a.smallimg,a.description,a.view,b.title as categoryname,c.size')
                ->where("a.id in  ($softid) and a.id =c.id  and b.id = a.category_id")
                ->order("field(a.id,$softid)")->select();
 
 
       //苹果下载排行ID为31855
        $isoftid=M('_down_paihang')->where('id=31855')->getField(soft_id);
        $idata=M()->table('onethink_down as a,onethink_down_category as b ,onethink_down_dmain as c')->field('a.id,a.category_id,a.title,a.smallimg,a.description,a.view,b.title as categoryname,c.size')
                ->where("a.id in  ($isoftid) and a.id =c.id  and b.id = a.category_id")
                ->order("field(a.id,$isoftid)")->select();
      //手游总榜31849
        $zsoftid=M('_down_paihang')->where('id=31849')->getField(soft_id);
        $zdata=M()->table('onethink_down as a,onethink_down_category as b ,onethink_down_dmain as c')->field('a.id,a.category_id,a.title,a.smallimg,a.description,a.view,b.title as categoryname,c.size')
                ->where("a.id in  ($zsoftid) and a.id =c.id  and b.id = a.category_id")
                ->order("field(a.id,$zsoftid)")->select();

        //SEO 作者：肖书成
        $this->assign('SEO',WidgetSEO(array('special',null,I('page_id'))));

        $this->assign('adata',$adata);
        $this->assign('idata',$idata);
        $this->assign('zdata',$zdata);
        $this->display('Widget/ph');
    }

    /**
     * 作者:肖书成
     * 描述:手机版404页面
     * 时间:2016-2-1
     */
    public function error_404(){
        $this->assign('SEO',WidgetSEO(array('special',null,I('page_id'))));
        $this->display('Widget/404');
    }

}