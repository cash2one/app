<?php
// +----------------------------------------------------------------------
// |  开始APP标签接口
// +----------------------------------------------------------------------
// |  Author: 刘鎏
// |  Time  : 2015-12-9
// +----------------------------------------------------------------------

namespace API\Controller;

class SatagController extends BaseController{

    // 下载分类ID
    public $tag_cate = 1;

    /**
     * 获取标签中从属于下载分类的标签
     * @return string 接口显示结果
     */
    public function get(){       
        // 查询是否有缓存 有则返回缓存数据
        $result = S('sa_tags_'.$this->tag_cate);
        if($result) $this->ajaxReturn($result,$this->returnType,false);

        // 查询
        $tags = M('Tags')->field('id as tagid,title  as tagname,sort as position')->where('status = 1 AND category='.$this->tag_cate)->order('sort asc,id desc')->select();
        $result = ['status'=>1, 'error'=>'', 'tags'=>$tags];
        $tags && S('sa_tags_'.$this->tag_cate,json_encode($result, JSON_UNESCAPED_SLASHES),1200);
        $this->ajaxReturn(json_encode($result, JSON_UNESCAPED_SLASHES), $this->returnType, false);
     }



}