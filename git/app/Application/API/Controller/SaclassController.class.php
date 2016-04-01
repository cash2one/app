<?php
// +----------------------------------------------------------------------
// |  开始APP分类接口
// +----------------------------------------------------------------------
// |  Author: 刘鎏
// |  Time  : 2015-12-4
// +----------------------------------------------------------------------

namespace API\Controller;

class SaclassController extends BaseController{

    /**
     * 获取下载分类
     * @return string 接口显示结果
     */
    public function get(){       
        // 查询是否有缓存 有则返回缓存数据
        $result = S('sa_class');
        if($result) $this->ajaxReturn($result,$this->returnType,false);

        // 查询
        $classes = M('DownCategory')->field('id as classid,title  as classname')->where('status = 1')->order('sort asc,id desc')->select();
        $result = ['status'=>1, 'error'=>'', 'class'=>$classes];
        $classes && S('sa_classes',json_encode($result, JSON_UNESCAPED_SLASHES),1200);
        $this->ajaxReturn(json_encode($result, JSON_UNESCAPED_SLASHES), $this->returnType, false);
     }



}