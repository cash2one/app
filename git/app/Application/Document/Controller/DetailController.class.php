<<<<<<< .mine
<?php
namespace Document\Controller;
use Think\Controller;
class DetailController extends BaseController {

    /**
     * 主函数
     * @return void
     */
    public function index(){
        $id=I('get.id');
        if (!is_numeric($id)) $this->error('页面不存在！');
        //数据查询
        $info = D('Document')->detail($id);

        if (!$info) $this->error('页面不存在！');


        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 第一次请求获取分页
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (I('get.gettotal')=='true' && !empty($info['content'])) {
            preg_match_all('/\[page\](.*?)\[\/page\]/',$info['content'],$match);
            $total = count($match[0]);
            if ($total>2) {
                echo $total-1;
                exit(); 
            }
        }
		
		// 标签支持变量 zhudesheng 2015-9-16
		$_GET['MODELID'] = $info['model_id'];
		
		// 点击量自增 zhudesheng 2015-9-17
		$info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '".C('DYNAMIC_SERVER_URL')."/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";
        
        $priv = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id <" .$id. " AND `status`='1'")->order('a.id desc')->find();
        $next = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id >" .$id. " AND `status`='1'")->order('a.id asc')->find();
        $this->assign("priv",$priv);
        $this->assign("next",$next);

        $array=getArticleTag($id);//根据ID获取文章标签

        $this->assign('tags',$array);//输出相关Tags
        
        $this->assign('CrumbNavi',getCrumb($id,'Document'));//输出面包屑导航
        
        //SEO
        $SEO = WidgetSEO(array('detail','Document', $id));

        //插入数据处理
        $ic = new \Common\Library\InsertContentLibrary($info['content']);
        $temp_t = I('get.t');
        if(!empty($temp_t)) $ic->setProperty('path_field', 'mobile_path');
        $info['content'] = $ic->init();

        $this->assign('info', $info);
        $tid=$info['id'];


        //判断是否是手机版
        $theme = I('t');
        $key = true;
        if(substr($theme,-6) == 'mobile') $key = false;
        /** 模板选择 */
        $category = D('Category')->info($info['category_id']);
        $info['cate']   =   $category['title'];
        if ($info['template']){
            // 详情页填写模板权重最高
            $temp = $info['template'];
        }elseif(($bid = ifBatchDetail($id)) && $key){
            // 专区模板启用
            // TODO 修改模板解析逻辑为框架认可的标签库扩展
            $temp = C('FEATURE_ZQ_D_DTEMP');
            $f_name = 'batch';
            // ----------------------COPY 后台parse方法 START----------------------------
             $content=file_get_contents(T($temp));
             $regular='/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';
             if(preg_match_all($regular,$content,$results)){//匹配挂件区域
                $widgets=json_decode($data['widget'],true);
                if(isset($widgets['custom'])) unset($widgets);
                /*
                 * 优先widget目录文件解析，无文件则解析数据行，无数据库行(row)则提取layout片断。
                 */
                foreach($results[1] as $k=>$v){
                     if(file_exists($file='./Application/Home/View/'.C('THEME').'/'.$f_name.'/widget/'.$v.'.htm')){
                         //widget文件存在
                         $content=str_replace($results[0][$k],file_get_contents($file),$content);
                     }elseif($widgets[$v]){
                         //数据源widget存在
                         if(is_array($widgets[$v])){
                             $content=str_replace($results[0][$k],$widgets[$v][$k],$content);
                         }else{
                             $content=str_replace($results[0][$k],$widgets[$v],$content);
                         }
                     }else{
                         //都不存在
                         $content=str_replace($results[0][$k],$results[2][$k],$content);
                     }
                     $this->view->assign($v,R('Home/'. getWName(C('THEME')) .strtolower($f_name).'/'.$v,array('id'=>$bid),'Widget'));
                }
             }
             $details=explode('<hr />',$content);
             if(isset($details[1])){
                $content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml"><head>'.$details[0].'</head><body>'.$details[1].'</body></html>';
             }else{
                //do nothing
             }
             if($content) $content=$this->view->fetch('',$content);//解析全部内容

             if(preg_match('/<br\s+totalPageNumber="(\d+)"\s+style="display:none">/is',$content,$pages)){
                $total=$pages[1];
                $this->pages[$id]=$total;
                $content=str_replace($pages[0],'',$content);
             }

            //处理图片
            $content = str_replace('src="Uploads','src="/Uploads',$content);
            $content = str_replace('src="/Uploads','src="'.C('PIC_HOST').'/Uploads',$content);
            //处理SEO
            if(C('SEO_STRING')){
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $info['seo_title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seoTitle = implode(' - ', $t);
            }

            $content = substr($content,0,strpos($content,'<title>')+7) . $seoTitle. substr($content,strpos($content,'</title>'));
            $content1 = substr($content,0,strpos($content,'keywords')+19) .$info['seo_keywords'];
            $content2 = substr($content,strpos($content,'keywords')+19);
            $content3 = substr($content2,strpos($content2,'"/>'));
            $content  = $content1.$content3;
            $content1 = substr($content,0,strpos($content,'description')+22) .$info['seo_description'];
            $content2 = substr($content,strpos($content,'description')+22);
            $content3 = substr($content2,strpos($content2,'"/>'));
            $content  = $content1.$content3;
            // ----------------------COPY 后台parse方法 END----------------------------
            $this->show($content);
            exit();

        }elseif($category['template_detail']){
            // 分类模板
            $temp = $category['template_detail'];
            // 亲宝贝手机版没有用到模板  谭坚 2015-6-26
            if($theme == 'qbaobeimobile')
            {
                $templates = array('jiankang','tuku','yingyang','baike','food','video','chengzhang');
                if(!in_array($temp,$templates)) $temp='index';
            }
          
        }else{
            // 默认
            $temp = 'index';
        }

        //处理图片 ADD BY TANJIAN 2016.1.20
        $info['content'] = str_replace('src="Uploads','src="/Uploads',$info['content']);
        $info['content'] = str_replace('src="/Uploads','src="'.C('PIC_HOST').'/Uploads',$info['content']);
        $info['content'] = str_replace('src="'.C('STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']); //本域名地址换成图片域名地址
        $info['content'] = str_replace('src="'.C('MOBILE_STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']);
        if(C('OPEN_THUMB')) //判断是否开启图片缩略功能
        {
            preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.GIF|\.JPG|\.JPEG|\.PNG|\.jpeg]))[\'|\"].*?[\/]?>/',$info['content'],$match);
            if(!$key && (C('MOBILE_THUMB_WIDTH') || C('MOBILE_THUMB_HEIGHT'))) //判断是否配置手机缩略
            {
                $width = C('MOBILE_THUMB_WIDTH') ?:0;
                $height = C('MOBILE_THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }
            else if($key &&  (C('THUMB_WIDTH') || C('THUMB_HEIGHT'))) //判断是否开启pc版缩略
            {
                $width = C('THUMB_WIDTH') ?:0;
                $height = C('THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }

        }

       // prt($info);exit;
        //分步阅读内容 add liujun 2015-8-24
        if($info['step_read']=='1'){
        	$info['stepList'] = stepContent($info['content']);
        }
        $mobile_url = staticUrlMobile('detail',$id,'Document');//获取手机版地址
        $this->assign('MOBLIE_URL',$mobile_url);
        $this->assign('SEO',$SEO);
        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 分页内容处理
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (is_numeric($p = I('get.p')) && !empty($info['content'])) {
            // 分页
            $page_handle_str = contentHandle(
                $info,
                $p,
                $theme, 
                $key ? staticUrl('detail',$id,'Document') : staticUrlMobile('detail',$id,'Document')
                );             
            $this->assign('info', $info);
            if ($page_handle_str===False) {
                $this->display($temp);
            } else {
                $ec = $this->fetch($temp);
                $ec = preg_replace('/<title>.*?<\/title>/', '<title>'. $page_handle_str['title'] . '</title>', $ec);
                echo $ec;
            }
        } else {
            //不分页情况下，开启第一页和第二页，第二页跳转标签关联文章
            if(C('CONTENT_ADD_PAGE')) //需要配置开启
            {
              content_add_page($info,$theme,strtolower(MODULE_NAME));

            }
            // 不分页
            $this->assign('info', $info);

            //echo $temp;
            $this->display($temp);

        }

    }








}||||||| .r7337
<?php
namespace Document\Controller;
use Think\Controller;
class DetailController extends BaseController {

    /**
     * 主函数
     * @return void
     */
    public function index(){
        $id=I('get.id');
        if (!is_numeric($id)) $this->error('页面不存在！');
        //数据查询
        $info = D('Document')->detail($id);
        if (!$info) $this->error('页面不存在！');

        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 第一次请求获取分页
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (I('get.gettotal')=='true' && !empty($info['content'])) {
            preg_match_all('/\[page\](.*?)\[\/page\]/',$info['content'],$match);
            $total = count($match[0]);
            if ($total>2) {
                echo $total-1;
                exit(); 
            }
        }
		
		// 标签支持变量 zhudesheng 2015-9-16
		$_GET['MODELID'] = $info['model_id'];
		
		// 点击量自增 zhudesheng 2015-9-17
		$info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '".C('DYNAMIC_SERVER_URL')."/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";
        
        $priv = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id <" .$id. " AND `status`='1'")->order('a.id desc')->find();
        $next = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id >" .$id. " AND `status`='1'")->order('a.id asc')->find();
        $this->assign("priv",$priv);
        $this->assign("next",$next);

        $array=getArticleTag($id);//根据ID获取文章标签
		//var_dump($array);
        $this->assign('tags',$array);//输出相关Tags
        
        $this->assign('CrumbNavi',getCrumb($id,'Document'));//输出面包屑导航
        
        //SEO
        $SEO = WidgetSEO(array('detail','Document', $id));

        //插入数据处理
        $ic = new \Common\Library\InsertContentLibrary($info['content']);
        $temp_t = I('get.t');
        if(!empty($temp_t)) $ic->setProperty('path_field', 'mobile_path');
        $info['content'] = $ic->init();

        $this->assign('info', $info);
        $tid=$info['id'];

        //判断是否是手机版
        $theme = I('t');
        $key = true;
        if(substr($theme,-6) == 'mobile') $key = false;

        /** 模板选择 */
        $category = D('Category')->info($info['category_id']);
        $info['cate']   =   $category['title'];
        if ($info['template']){
            // 详情页填写模板权重最高
            $temp = $info['template'];
        }elseif(($bid = ifBatchDetail($id)) && $key){
            // 专区模板启用
            // TODO 修改模板解析逻辑为框架认可的标签库扩展
            $temp = C('FEATURE_ZQ_D_DTEMP');
            $f_name = 'batch';
            // ----------------------COPY 后台parse方法 START----------------------------
             $content=file_get_contents(T($temp));
             $regular='/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';
             if(preg_match_all($regular,$content,$results)){//匹配挂件区域
                $widgets=json_decode($data['widget'],true);
                if(isset($widgets['custom'])) unset($widgets);
                /*
                 * 优先widget目录文件解析，无文件则解析数据行，无数据库行(row)则提取layout片断。
                 */
                foreach($results[1] as $k=>$v){
                     if(file_exists($file='./Application/Home/View/'.C('THEME').'/'.$f_name.'/widget/'.$v.'.htm')){
                         //widget文件存在
                         $content=str_replace($results[0][$k],file_get_contents($file),$content);
                     }elseif($widgets[$v]){
                         //数据源widget存在
                         if(is_array($widgets[$v])){
                             $content=str_replace($results[0][$k],$widgets[$v][$k],$content);
                         }else{
                             $content=str_replace($results[0][$k],$widgets[$v],$content);
                         }
                     }else{
                         //都不存在
                         $content=str_replace($results[0][$k],$results[2][$k],$content);
                     }
                     $this->view->assign($v,R('Home/'. getWName(C('THEME')) .strtolower($f_name).'/'.$v,array('id'=>$bid),'Widget'));
                }
             }
             $details=explode('<hr />',$content);
             if(isset($details[1])){
                $content='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml"><head>'.$details[0].'</head><body>'.$details[1].'</body></html>';
             }else{
                //do nothing
             }
             if($content) $content=$this->view->fetch('',$content);//解析全部内容

             if(preg_match('/<br\s+totalPageNumber="(\d+)"\s+style="display:none">/is',$content,$pages)){
                $total=$pages[1];
                $this->pages[$id]=$total;
                $content=str_replace($pages[0],'',$content);
             }

            //处理图片
            $content = str_replace('src="Uploads','src="/Uploads',$content);
            $content = str_replace('src="/Uploads','src="'.C('PIC_HOST').'/Uploads',$content);
            //处理SEO
            if(C('SEO_STRING')){
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $info['seo_title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seoTitle = implode(' - ', $t);
            }

            $content = substr($content,0,strpos($content,'<title>')+7) . $seoTitle. substr($content,strpos($content,'</title>'));
            $content1 = substr($content,0,strpos($content,'keywords')+19) .$info['seo_keywords'];
            $content2 = substr($content,strpos($content,'keywords')+19);
            $content3 = substr($content2,strpos($content2,'"/>'));
            $content  = $content1.$content3;
            $content1 = substr($content,0,strpos($content,'description')+22) .$info['seo_description'];
            $content2 = substr($content,strpos($content,'description')+22);
            $content3 = substr($content2,strpos($content2,'"/>'));
            $content  = $content1.$content3;
            // ----------------------COPY 后台parse方法 END----------------------------
            $this->show($content);
            exit();

        }elseif($category['template_detail']){
            // 分类模板
            $temp = $category['template_detail'];
            // 亲宝贝手机版没有用到模板  谭坚 2015-6-26
            if($theme == 'qbaobeimobile')
            {
                $templates = array('jiankang','tuku','yingyang','baike','food','video','chengzhang');
                if(!in_array($temp,$templates)) $temp='index';
            }
          
        }else{
            // 默认
            $temp = 'index';
        }

        //处理图片 ADD BY TANJIAN 2016.1.20
        $info['content'] = str_replace('src="Uploads','src="/Uploads',$info['content']);
        $info['content'] = str_replace('src="/Uploads','src="'.C('PIC_HOST').'/Uploads',$info['content']);
        $info['content'] = str_replace('src="'.C('STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']); //本域名地址换成图片域名地址
        $info['content'] = str_replace('src="'.C('MOBILE_STATIC_URL'),'src="'.C('PIC_HOST'),$info['content']);
        if(C('OPEN_THUMB')) //判断是否开启图片缩略功能
        {
            preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.GIF|\.JPG|\.JPEG|\.PNG|\.jpeg]))[\'|\"].*?[\/]?>/',$info['content'],$match);
            if(!$key && (C('MOBILE_THUMB_WIDTH') || C('MOBILE_THUMB_HEIGHT'))) //判断是否配置手机缩略
            {
                $width = C('MOBILE_THUMB_WIDTH') ?:0;
                $height = C('MOBILE_THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }
            else if($key &&  (C('THUMB_WIDTH') || C('THUMB_HEIGHT'))) //判断是否开启pc版缩略
            {
                $width = C('THUMB_WIDTH') ?:0;
                $height = C('THUMB_HEIGHT') ?: 0;
                if($match[1]){
                    $search = $replace = array();

                    foreach($match[1] as $img){
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[]  = $img;

                        if(C('OPEN_WATER')){ //判断是否打水印
                            $replace[] = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                        }else{
                            $replace[] = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                        }
                    }
                    $info['content'] = str_replace($search, $replace, $info['content']);
                }
            }

        }

        //分步阅读内容 add liujun 2015-8-24
        if($info['step_read']=='1'){
        	$info['stepList'] = stepContent($info['content']);
        }
        $mobile_url = staticUrlMobile('detail',$id,'Document');//获取手机版地址
        $this->assign('MOBLIE_URL',$mobile_url);
        $this->assign('SEO',$SEO);
        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 分页内容处理
         * 
         * Author:liuliu 
         * Time:2015-11-4
         */
        if (is_numeric($p = I('get.p')) && !empty($info['content'])) {
            // 分页
            $page_handle_str = contentHandle(
                $info,
                $p,
                $theme, 
                $key ? staticUrl('detail',$id,'Document') : staticUrlMobile('detail',$id,'Document')
                );             
            $this->assign('info', $info);
            if ($page_handle_str===False) {
                $this->display($temp);
            } else {
                $ec = $this->fetch($temp);
                $ec = preg_replace('/<title>.*?<\/title>/', '<title>'. $page_handle_str['title'] . '</title>', $ec);
                echo $ec;
            }
        } else {
            //不分页情况下，开启第一页和第二页，第二页跳转标签关联文章
            if(C('CONTENT_ADD_PAGE')) //需要配置开启
            {
              content_add_page($info,$theme,strtolower(MODULE_NAME));

            }
            // 不分页
            $this->assign('info', $info);

            $this->display($temp);

        }

    }








}=======
<?php
namespace Document\Controller;

use Think\Controller;

class DetailController extends BaseController
{

    /**
     * 主函数
     * @return void
     */
    public function index()
    {
        $id = I('get.id');
        if (!is_numeric($id)) {
            $this->error('页面不存在！');
        }
        //数据查询
        $info = D('Document')->detail($id);
        if (!$info) {
            $this->error('页面不存在！');
        }

        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 第一次请求获取分页
         *
         * Author:liuliu
         * Time:2015-11-4
         */
        if (I('get.gettotal') == 'true' && !empty($info['content'])) {
            preg_match_all('/\[page\](.*?)\[\/page\]/', $info['content'], $match);
            $total = count($match[0]);
            if ($total > 2) {
                echo $total - 1;
                exit();
            }
        }

        // 标签支持变量 zhudesheng 2015-9-16
        $_GET['MODELID'] = $info['model_id'];

        // 点击量自增 zhudesheng 2015-9-17
        $info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '" . C('DYNAMIC_SERVER_URL') . "/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";

        $priv = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id <" . $id . " AND `status`='1'")->order('a.id desc')->find();
        $next = D('Document')->alias('a')->join('LEFT JOIN __DOCUMENT_ARTICLE__ b on a.id=b.id')->where("a.id >" . $id . " AND `status`='1'")->order('a.id asc')->find();
        $this->assign("priv", $priv);
        $this->assign("next", $next);

        $array = getArticleTag($id);//根据ID获取文章标签
        //var_dump($array);
        $this->assign('tags', $array);//输出相关Tags

        $this->assign('CrumbNavi', getCrumb($id, 'Document'));//输出面包屑导航

        //SEO
        $SEO = WidgetSEO(array('detail', 'Document', $id));

        //判断是否是手机版
        $theme = I('t');
        $key = true;
        if (substr($theme, -6) == 'mobile') {
            $key = false;
        }


        //插入数据处理
        $ic = new \Common\Library\InsertContentLibrary($info['content']);
        $temp_t = I('get.t');
        if (!empty($temp_t)) {
            $ic->setProperty('path_field', 'mobile_path');
        }
        $info['content'] = $ic->init();

        //--------文本内容处理------------ By Jeffrey Lau,Date: 2016-3-3 15:00:17
        $ih = new \Common\Library\ContentHandleLibrary($info['content']);
        $ih->setProperty('mobile', $key);
        $ih->setProperty('enlink', $info['enlink']);
        $content_done = $ih->init();
        $info['content'] = $content_done;
        //-------------end---------------


        $this->assign('info', $info);
        $tid = $info['id'];


        /** 模板选择 */
        $category = D('Category')->info($info['category_id']);
        $info['cate'] = $category['title'];
        if ($info['template']) {
            // 详情页填写模板权重最高
            $temp = $info['template'];
        } elseif (($bid = ifBatchDetail($id)) && $key) {
            // 专区模板启用
            // TODO 修改模板解析逻辑为框架认可的标签库扩展
            $temp = C('FEATURE_ZQ_D_DTEMP');
            $f_name = 'batch';
            // ----------------------COPY 后台parse方法 START----------------------------
            $content = file_get_contents(T($temp));
            $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';
            if (preg_match_all($regular, $content, $results)) {//匹配挂件区域
                $widgets = json_decode($data['widget'], true);
                if (isset($widgets['custom'])) {
                    unset($widgets);
                }
                /*
                 * 优先widget目录文件解析，无文件则解析数据行，无数据库行(row)则提取layout片断。
                 */
                foreach ($results[1] as $k => $v) {
                    if (file_exists($file = './Application/Home/View/' . C('THEME') . '/' . $f_name . '/widget/' . $v . '.htm')) {
                        //widget文件存在
                        $content = str_replace($results[0][$k], file_get_contents($file), $content);
                    } elseif ($widgets[$v]) {
                        //数据源widget存在
                        if (is_array($widgets[$v])) {
                            $content = str_replace($results[0][$k], $widgets[$v][$k], $content);
                        } else {
                            $content = str_replace($results[0][$k], $widgets[$v], $content);
                        }
                    } else {
                        //都不存在
                        $content = str_replace($results[0][$k], $results[2][$k], $content);
                    }
                    $this->view->assign($v, R('Home/' . getWName(C('THEME')) . strtolower($f_name) . '/' . $v, array('id' => $bid), 'Widget'));
                }
            }
            $details = explode('<hr />', $content);
            if (isset($details[1])) {
                $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
                <html xmlns="http://www.w3.org/1999/xhtml"><head>' . $details[0] . '</head><body>' . $details[1] . '</body></html>';
            } else {
                //do nothing
            }
            if ($content) {
                $content = $this->view->fetch('', $content);
            }//解析全部内容

            if (preg_match('/<br\s+totalPageNumber="(\d+)"\s+style="display:none">/is', $content, $pages)) {
                $total = $pages[1];
                $this->pages[$id] = $total;
                $content = str_replace($pages[0], '', $content);
            }

            //处理图片
            $content = str_replace('src="Uploads', 'src="/Uploads', $content);
            $content = str_replace('src="/Uploads', 'src="' . C('PIC_HOST') . '/Uploads', $content);
            //处理SEO
            if (C('SEO_STRING')) {
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $info['seo_title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seoTitle = implode(' - ', $t);
            }

            $content = substr($content, 0, strpos($content, '<title>') + 7) . $seoTitle . substr($content, strpos($content, '</title>'));
            $content1 = substr($content, 0, strpos($content, 'keywords') + 19) . $info['seo_keywords'];
            $content2 = substr($content, strpos($content, 'keywords') + 19);
            $content3 = substr($content2, strpos($content2, '"/>'));
            $content = $content1 . $content3;
            $content1 = substr($content, 0, strpos($content, 'description') + 22) . $info['seo_description'];
            $content2 = substr($content, strpos($content, 'description') + 22);
            $content3 = substr($content2, strpos($content2, '"/>'));
            $content = $content1 . $content3;
            // ----------------------COPY 后台parse方法 END----------------------------
            $this->show($content);
            exit();

        } elseif ($category['template_detail']) {
            // 分类模板
            $temp = $category['template_detail'];
            // 亲宝贝手机版没有用到模板  谭坚 2015-6-26
            if ($theme == 'qbaobeimobile') {
                $templates = array('jiankang', 'tuku', 'yingyang', 'baike', 'food', 'video', 'chengzhang');
                if (!in_array($temp, $templates)) {
                    $temp = 'index';
                }
            }

        } else {
            // 默认
            $temp = 'index';
        }

        //分步阅读内容 add liujun 2015-8-24
        if ($info['step_read'] == '1') {
            $info['stepList'] = stepContent($info['content']);
        }
        $mobile_url = staticUrlMobile('detail', $id, 'Document');//获取手机版地址
        $this->assign('MOBLIE_URL', $mobile_url);
        $this->assign('SEO', $SEO);
        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         *
         * 分页内容处理
         *
         * Author:liuliu
         * Time:2015-11-4
         */
        if (is_numeric($p = I('get.p')) && !empty($info['content'])) {
            // 分页
            $page_handle_str = contentHandle(
                $info,
                $p,
                $theme,
                $key ? staticUrl('detail', $id, 'Document') : staticUrlMobile('detail', $id, 'Document')
            );
            $this->assign('info', $info);
            if ($page_handle_str === false) {
                $this->display($temp);
            } else {
                $ec = $this->fetch($temp);
                $ec = preg_replace('/<title>.*?<\/title>/', '<title>' . $page_handle_str['title'] . '</title>', $ec);
                echo $ec;
            }
        } else {
            //不分页情况下，开启第一页和第二页，第二页跳转标签关联文章
            if (C('CONTENT_ADD_PAGE')) { //需要配置开启
                content_add_page($info, $theme, strtolower(MODULE_NAME));

            }
            // 不分页
            $this->assign('info', $info);
            $this->display($temp);

        }

    }
}
>>>>>>> .r7397
