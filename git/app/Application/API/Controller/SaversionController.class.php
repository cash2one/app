<?php
// +----------------------------------------------------------------------
// |  开始APP版本接口
// +----------------------------------------------------------------------
// |  Author: 刘鎏
// |  Time  : 2015-12-9
// +----------------------------------------------------------------------

namespace API\Controller;

class SaversionController extends BaseController{

    /**
     * 根据获取版本信息
     * @return string 接口显示结果
     */
    public function get(){
        $type  =   I('type');
        is_numeric($type) || $this->ajaxReturn(['status'=>0, 'error'=>'参数错误'],$this->returnType);

        // 查询
        $result = [];
        $result = M('App')->field('title as name,code,content as info,url')->where('status=1 AND type='.$type)->order('id desc')->find();
        if ($result) {
            $result['status'] = 1;
            $result['error'] = '';
            $this->ajaxReturn(json_encode($result, JSON_UNESCAPED_SLASHES), $this->returnType, false);
        } else {
            $this->ajaxReturn(['status'=>0, 'error'=>'没有数据'],$this->returnType);
        }
    }

}