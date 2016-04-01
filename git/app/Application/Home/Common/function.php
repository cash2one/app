<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}

/**
 * 作者:ganweili
 * 时间:2015/11/14
 * 描述:根据下载模块的ID 以TAG查找出游戏开服开测状态
 */

  function get_server_type($id){
      $did=M('tags_map')->where("did='$id'")->getfield('tid');


   $data=M('tags_map')->alias('a')->join('__PACKAGE_PARTICLE__  b')
          ->where("a.tid='$did' and a.type='package'  and a.did=b.id ")
          ->getfield('b.server');
         // ->getfield('b.server');*/
      return $data;
  }

/**
 * 作者:ganweili
 * 时间:2015/11/14
 * 描述:筛选URL处理函数
 */
function editurl($system,$keyword,$type,$weizhi){

    $urlc=$_SERVER['SERVER_NAME'];
    $show=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];

    $show=str_replace('/index.php?s=/Widget/index/theme/Jf96u/method/yxksx/t/jf96u/p/','',$show);
    $show='yxksx/1'.strstr($show,'/');

    $show=str_replace('/order/','_',$show);
    $show=str_replace('/system/','_',$show);
    $show=str_replace('/network/','_',$show);
    $show=str_replace('/type/','_',$show);
    $show=str_replace('/image/','_',$show);

    $url=str_replace('/rank/','_',$show);

    $url=explode('_',$url);

    $get=I($keyword);

    foreach ($url as $k =>$v){
     if($k==$weizhi){
         $urlb.= 'all'.'_';
     }else{
     $urlb.= $v.'_';

     }

     }

    foreach($system as $k=>$v){
       if($get==$k){
             $class= "   class='active'";
           }else{unset($class);}
     $url[$weizhi]=$k;
        $url['0']='1';

     $arr=implode('_',$url);

        $sysurl.="<a href='http://".$urlc.'/yxksx/'.$arr.".html'".$class.">$v</a>";
    }
    if(!empty($type)){
        $sysurl=str_replace('/.html','.html',$sysurl);

        return $sysurl;
    }

    if(!is_numeric($get)){
        $urlb=substr($urlb,0,-1);
    $sysurl="<a href='http://".$urlc.'/'.$urlb.".html' class='active'>全部</a>".$sysurl;
    }else{
        $urlb=substr($urlb,0,-1);
        $sysurl= "<a href='http://".$urlc.'/'.$urlb.".html''>全部</a>".$sysurl;
    }



    $sysurl=str_replace('Jf96u','',$sysurl);
    $sysurl=str_replace('jf96u','',$sysurl);
    $sysurl=str_replace('/.html','.html',$sysurl);
    return $sysurl;
}

