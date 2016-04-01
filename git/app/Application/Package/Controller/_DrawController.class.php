<?php
// +----------------------------------------------------------------------
// | 礼包领取web服务类控制器
// +----------------------------------------------------------------------
// | 动态WEB服务：
// |    礼包领取
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Package\Controller;
use Think\Controller;

class DrawController extends Controller {

    /**
     * 领取礼包卡号
     * @return string  领取的卡号
     */
    public function drawCard(){
        $id = I('id');
        if (!is_numeric($id)) {
            echo  json_encode(array('error'=>'params error'));   
            return;
        }
        $model  = D('Card');
        //同一个IP同一个数据ID只能获取1次
        $ip = get_client_ip();
        $rs = D('Package')
                    ->alias('p')
                    ->where('c.ip="'. $ip .'" AND c.draw_status=1 AND p.id='. $id)
                    ->join('LEFT JOIN __CARD__ as c ON c.did = p.id')
                    ->field('p.id')
                    ->find();
        if ($rs){
            echo  json_encode(array('error'=>'同一个IP只能领取一次同样的礼包，请您领取其他礼包！'));   
            return;           
        }  

        //启用事务
        $model->startTrans();
        $one = $model->where('did='. $id . ' AND draw_status=0 AND status=1')->find();
        if(!$one){
            $model->rollback();
            echo json_encode(array('error'=>'礼包已经发完，请领取其他礼包'));   
            return;        
        }
        $sava_arr = array(
            'ip' => $ip,
            'draw_time' => time(),
            'draw_status' => 1 
            );
        $rs = $model->where('id='. $one['id'])->save($sava_arr);
        if($rs){
            $model->commit();
            echo json_encode(array('row'=>$one['number']));
        }else{
            $model->rollback();
            echo json_encode(array('error'=>'sql error'));
        } 
    }

}