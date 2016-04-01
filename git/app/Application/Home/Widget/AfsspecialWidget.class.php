<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/18
 * Time: 16:07
 */

namespace Home\Widget;

use Think\Controller;

class AfsspecialWidget extends Controller{

    public function base($id){
        //基础数据
        $base = M('special')->field('title,seo_title,keywords,description')->where('id = '.$id)->find();
        //SEO
        $SEO['title'] = $base['seo_title']?$base['seo_title']:$base['title'];
        $SEO['keywords'] = $base['keywords'];
        $SEO['description'] = $base['description'];
        $this->assign(array(
            'SEO'=>$SEO,
            'info'=>$base
        ));
    }

    public function __call($method, $args){
        return;
    }
} 