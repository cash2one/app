<?php
namespace Gallery\Controller;
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
        $info = D('Gallery')->detail($id);
        if (!$info) $this->error('页面不存在！');

		// 标签支持变量 zhudesheng 2015-9-16
		$_GET['MODELID'] = $info['model_id'];

		// 点击量自增 zhudesheng 2015-9-17
		$info['view_sc'] = "<font id='hash-view_sc'></font><script>window.onload = function(){ var _filer_ef = document.createElement('script');
		_filer_ef.setAttribute('src', '".C('DYNAMIC_SERVER_URL')."/dynamic.php?s=Base/getClickAmount/modelid/{$info['model_id']}/id/{$info['id']}');document.getElementsByTagName('head')[0].appendChild(_filer_ef); }</script>";
        
        $priv = D('Gallery')->alias('a')->join('LEFT JOIN __GALLERY_ALBUM__ b on a.id=b.id')->where("a.id <" .$id. " AND `status`='1'")->order('a.id desc')->find();
        $next = D('Gallery')->alias('a')->join('LEFT JOIN __GALLERY_ALBUM__ b on a.id=b.id')->where("a.id >" .$id. " AND `status`='1'")->order('a.id asc')->find();
        $this->assign("priv",$priv);
        $this->assign("next",$next);
		
        $this->assign('CrumbNavi',getCrumb($id,'Gallery'));//输出面包屑导航
        
        //SEO
        $SEO = WidgetSEO(array('detail','Gallery', $id));

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
        $category = D('GalleryCategory')->info($info['category_id']);
		
		if($info['imgpack']){
			$info['imgpack'] = unserialize($info['imgpack']);
            if(C('OPEN_THUMB') && !$key && (C('MOBILE_GALLERY_THUMB_WIDTH') || C('MOBILE_GALLERY_THUMB_HEIGHT'))) //判断是否配置手机缩略
            {
                $width = C('MOBILE_GALLERY_THUMB_WIDTH') ?:0;
                $height = C('MOBILE_GALLERY_THUMB_HEIGHT') ?: 0;
            }
            foreach($info['imgpack'] as $k => $value){
                $img  = C('PIC_HOST').$value['image'];
                $filename = $img; //复制给图片值复制给filename
                if($width || $height)
                {
                    $filename = substr($img, 0, strrpos($img, '.'));
                    if(C('OPEN_WATER')){ //判断是否打水印
                        $filename = $filename.'_'.$width.'_'.$height.'_water'.strrchr($img, '.');
                    }else{
                        $filename = $filename.'_'.$width.'_'.$height.strrchr($img, '.');
                    }
                }
                $info['imgpack'][$k]['image'] = $filename;
            }
		}
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
        }else{
            // 默认
            $temp = 'index';
        }
        $mobile_url = staticUrlMobile('detail',$id,'Document');//获取手机版地址
        $this->assign('MOBLIE_URL',$mobile_url);
        $this->assign('SEO',$SEO);
        $this->assign('info', $info);
        $this->display($temp);
    }
}