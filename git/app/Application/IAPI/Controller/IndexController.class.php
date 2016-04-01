<?php
// +----------------------------------------------------------------------
// |  数据库表管理类
// +----------------------------------------------------------------------
// |  Author: 朱德胜
// |  Time  : 2015-07-09
// +----------------------------------------------------------------------

namespace IAPI\Controller;
use Think\Controller;

class IndexController extends Controller {
	
	function index(){
		send_http_status(403);
		exit;
	}
}
?>