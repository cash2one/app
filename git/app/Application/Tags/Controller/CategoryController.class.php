<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/13
 * Time: 19:01
 */

namespace Tags\Controller;


class CategoryController extends BaseController{

    /**
     * 主方法
     * @return void
     */
    public function index(){
        $model = I('model') ? strtolower(I('model')):'document'; //选择选择生成标签列表的模型，默认为文章模型
        $tag = I('tag');
        if (!is_numeric($tag)) $this->error('页面不存在！');
        //获取标签相关信息
        $info = M('Tags')->where('id = '.$tag)->field(true)->find();
        if (!$info) $this->error('页面不存在！');
        $this->assign('info', $info);
        $isMobile = (substr(I('t'),-6) === 'mobile');
        //分页获取数据
        $row = $info['list_row'] ? $info['list_row'] : 10;
        $where['a.status'] = 1;
        $where['b.tid'] = $tag;
        $where['b.type'] = $model;
        $fields = 'a.id as id,a.title as title,a.description as description';
        $count  = M(ucfirst($model))->alias('a')->join('__TAGS_MAP__ b on b.did = a.id')->field($fields)->where($where)->count();
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理
        $lists =M(ucfirst($model))->alias('a')->join('__TAGS_MAP__ b on b.did = a.id')->field($fields)->where($where)->page($p, $row)->select();
        $this->assign('lists',$lists);// 赋值数据集
       // dump($lists);
       // $url = staticTagsUrl( $tag, 'Tags', $p,false);
       // dump($url);
       // exit;
        $Page       = new \Think\Page($count, $row, '',$tag,false,true,'Tags');// 实例化分页类 传入总记录数和每页显示的记录数 和静态分类id

        $Page->setConfig('first','首页');
        $Page->setConfig('end','尾页');
        $Page->setConfig('prev',"上一页");
        $Page->setConfig('next','下一页');
        if($isMobile){
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
            $Page->isMobile = true;
        }else{
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        }
        $show   = $Page->show();// 分页显示输出
        //dump($show);
        $this->assign('count',$count);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('tid',$tag);// 标签id
        //SEO
        $this->assign("SEO",$this->SEO($tag));
        // 模板选择
        $temp = $info['template_index']
            ? $info['template_index']
            : ($info['template_lists'] ? $info['template_lists'] : 'index');
        $this->display($temp);
    }
} 