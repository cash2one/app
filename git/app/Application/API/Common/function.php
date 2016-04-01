<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/14
 * Time: 17:43
 */

/**
 * 请求数据状态
 * @param integer $code 状态码
 * @return array
 */
function request_status($code) {
    static $_status = array(
        // Success 2xx
        200 => 'OK',
        // Redirection 3xx
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        // Client Error 4xx
        400 => 'Bad Request',
        403 => 'Forbidden',
        404 => 'Not Found',
        // Server Error 5xx
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    );

    if(isset($_status[$code])){
        return array('msg'=>$_status[$code],'status'=>$code);
    }else{
        header('Content-type:text/html;charset=utf-8');
        echo '未知状态';exit;
    }
}