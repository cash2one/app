<?php
// +----------------------------------------------------------------------
// |  数据中心公共类
// +----------------------------------------------------------------------
// |  Author: 肖书成
// |  Time  : 2015-11-22
// +----------------------------------------------------------------------

namespace IAPI\Controller;


use Think\Controller;
class DataBaseController extends Controller{
    protected $returnType;

    function __construct()
    {
        $status = array('status' => 0);

        $this->returnType = $_GET['_rt'] ? $_GET['_rt'] : 'json';

        // 检查请求IP地址
//        if (!in_array(get_client_ip(0, true), C('API_IP_LIMIT'))) {
//            $status['error'] = '无权限访问接口';
//            $this->ajaxReturn($status, $this->returnType);
//        }

        // $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        // $referer = $_SERVER['HTTP_REFERER'];
        // if($referer){
        //     $referer = parse_url($referer);
        //     $host = $referer['host'];
        //     if(in_array($host, $cors)){
        //         header('Access-Control-Allow-Origin:http://'. $host);
        //     }
        // }

        header('Access-Control-Allow-Origin:*');
    }

    /**
     * 描述:Ajax返回结果 ，继承了系统原生的 ajaxReturn
     * 时间:2015-11-21
     * @param mixed $data
     * @param string $type
     * @param bool $isEncode 后来扩建的，是否需要编码，默认是需要编码的 目前只有json数据使用了
     */
    protected function ajaxReturn($data,$type='',$isEncode = true){
        // 返回JSON数据格式到客户端 包含状态信息
        $callback       =   I('callback');
        empty($type)    &&  $type='JSON';
        if (strtoupper($type)=='JSON' && $callback) {
            if($isEncode){
                header('Content-Type:application/json; charset=utf-8');
                exit($callback.'('.json_encode($data, JSON_UNESCAPED_SLASHES).');');
            }else{
                header('Content-Type:application/json; charset=utf-8');
                exit($callback.'('.$data.');');
            }
        }elseif(strtoupper($type)=='JSON' && !$isEncode){
            header('Content-Type:application/json; charset=utf-8');
            exit($data);
        }

        parent::ajaxReturn($data,$type);
    }

    /**
     * 描述: 将数组转换成分类树。
     * @param $cate
     * @param string $name
     * @param int $pid
     * @return array
     */
    Static Public function array_tree($cate, $name = 'child', $pid = 0) {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $child = self::array_tree($cate, $name, $v['id']);
                if($child){
                    $v[$name] = $child;
                }
                $arr[] = $v;
            }
        }
        return $arr;
    }
} 