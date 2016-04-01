<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 15-1-22
 * Time: 下午12:00
 * To change this template use File | Settings | File Templates.
 */

namespace Home\Widget;
use Think\Controller;

class P7230featureWidget extends Controller{

    public function base($id){
        //基础数据
        $base = M('Feature')->where('id = '.$id)->find();
        //SEO
        $SEO['title'] = $base['seo_title']?$base['seo_title']:$base['title'];
        $SEO['keywords'] = $base['keywords'];
        $SEO['description'] = $base['description'];

        $this->assign('SEO',$SEO);
        return $base;
    }

//    //网游
//    public function online(){
//        $onlineGame = $this->getDownData(1);
//        //数据展示
//        $this->assign('onlineGame',$onlineGame);
//    }
//
//    //单机
//    public function alone(){
//        $aloneGame = $this->getDownData(0);
//        //数据展示
//        $this->assign('aloneGame',$aloneGame);
//    }
//
//    private function getDownData($network){
//        //查询出所有分类
//        $game = M('downCategory')->field('id,title')->where('pid = 1')->select();
//
//        //找出每个分类的4条数据
//        $cateGame = array();
//        foreach($game as $k=>$v){
//            $rel = M('down')->alias('a')->field('a.id,a.title,a.description,a.abet,a.smallimg,c.hits')->join('__DOWN_DMAIN__ b ON a.id=b.id')->join('__DOWN_ADDRESS__ c ON a.id = c.did')->where('b.network = '.$network .' AND a.category_id = '.$v['id'])->limit(4)->select();
//            if($rel){
//                $cateGame[$k]['cate']=$v['title'];
//                $cateGame[$k]['data']=$rel;
//            }
//        }
//
//        //返回数据
//        return $cateGame;
//    }
//
    //分页
    public function pages($count,$row,$id){
    	$rows=M($this->document_name)->field('url_token')->find($id);
        $Page = new \Think\Page($count,$row,'',false,C('FEATURE_ZQ_DIR').'/'.$rows['url_token'].C('FEATURE_PAGE'));// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出

        $show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';
        $this->assign('page',$show);// 赋值分页输出
        //echo(var_dump($this->document_name.'PageNumber',$this->pages));
        S($this->document_name.'PageNumber',array($id=>ceil($count/$row)),86400);
    }
    
    public function __call($method, $args){
    	return;
    }

}