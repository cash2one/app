<?php
// +----------------------------------------------------------------------
// |  卡号逻辑模型 实现卡号相关逻辑
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Logic;
use Think\Model;

/**
 * 卡号逻辑模型
 */
class CardLogic extends Model{

    /**
     * 添加卡号
     * @param array $data 表单数组
     * @return boolean 结果
    */
    public function addForm($data){
        //获取id，如果是新增，TP的model类中会把插入ID写入到data；如果是修改，post数据会传递ID
        $id = $data['id'];
        $num  = I('card_number');
        $center_did  = I('center_did'); //礼包中心数据ID
        if( !is_numeric($num) || !is_numeric($center_did) || !is_numeric($id)){ 
            return false;
        }

        $domain = C('PACKAGE_API_URL');
        $key = C('PACKAGE_API_KEY');
        $url = $domain.'?c=index&a=getCardByNum&key='.$key['key'].'&domain='.$key['domain'].'&did='.$center_did.'&num='.$num;
        $response = request_by_curl($url);
        $response = json_decode($response, true);

        if(isset($response['error'])){
            return false;
        }elseif(isset($response['data'])){
            $add_data = $response['data'];
            foreach ($add_data as $key => $value) {
                //设定礼包中心的卡号表数据ID
                $add_data[$key]['center_cid'] =  $add_data[$key]['id'];
                unset($add_data[$key]['id']);
                //礼包中心此卡号所属文章数据ID
                $add_data[$key]['center_did'] =  $center_did;
                //礼包数据ID
                $add_data[$key]['did'] =  $id; 
                //获取时间
                $add_data[$key]['get_time'] =  time();
                $this->add($add_data[$key]);                                         
            }
            return $rs;
        }
    }

    /**
     * 清空回收站的时候删除关联数据
     * @param  array $ids did数组,文档表id
     * @param  string $type 类型
     * @return  boolean
     */
    public function remove($ids){
        //清除APC缓存
        foreach ($ids as $id) {
           deleteCard($id);
        }
        $map = array(
            'did'=>array( 'IN',trim(implode(',',$ids),',') ),
            );
        return $this->where( $map )->delete();              
    }

    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_insert($data, $options){
        //刷新APC缓存
        flushCard($data['did']);
    }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    // protected function _after_update(&$data, $options){
    //     //刷新APC缓存
    //     $info = D('card')->info($data['id'], 'did');
    //             flushCard($info['did']);
    // }

    /**
     * 删除成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    // protected function _after_delete($id, $options){
    //     //刷新APC缓存 删除操作第一个参数为空，第二个参数为条件
    //     $info = D('card')->info($id, 'did');
    //             flushCard($info['did']);

    // }



}
