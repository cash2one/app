<?php
/**
 * CommonController.class.php
 *
 * 公共管理类
 *
 * @author 朱德胜
 */
namespace IAPI\Controller;

use Think\Controller;

class CommonController extends Controller
{

    /**
     * 架构函数,令牌验证
     *
     * @return void
     */
    public function _initialize()
    {
        // 令牌验证
        if (!checkToken(I('request.token'))) {
            $this->ajaxReturn(resultFormat(0, '令牌验证失败'));
        }
        
        if (isset($_POST['token'])) {
            $data = $_POST['data'];
            
            unset($_POST['token'], $_POST['data']);

            if ($data) {
                $_POST = array_merge($data, $_POST);
            }
        } else {
            unset($_GET['token']);
        }

        // 控制器构造函数
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }
}
