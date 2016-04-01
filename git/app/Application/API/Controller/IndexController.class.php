<?php
// +----------------------------------------------
// |
// *----------------------------------------------
// |  Author: 肖书成
// |  Time  : 2015-7-14
//+----------------------------------------------
namespace API\Controller;
use Think\Controller;

class IndexController extends Controller {

    public function index(){
        send_http_status(403);
        exit;
    }
}