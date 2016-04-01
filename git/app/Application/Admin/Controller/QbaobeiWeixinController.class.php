<?php
// +----------------------------------------------------------------------
// | 描述:亲宝贝微信控制器功能
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 16-3-7 下午5:13    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;

class QbaobeiWeixinController extends AdminController
{
    /**
     * 描述：初始化构造
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function _initialize()
    {
        parent::_initialize();

    }

    /**
     * 描述：新增微信二维码信息
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function add()
    {
        if (D('QbaobeiWeixin')->addWeixin($this->getCategoryInfos())) {
            $this->success('微信模块添加成功！', U('QbaobeiWeixin/index/type/all'));
        } else {
            $this->error('微信模块添加失败！');
        }
    }

    /**
     * 描述：更新微信二维码信息
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function update()
    {
        if (D('QbaobeiWeixin')->updateWeixin($this->getCategoryInfos())) {
            $this->success('微信模块更新成功！', U('QbaobeiWeixin/index/type/all'));
        } else {
            $this->error('微信模块更新失败！');
        }
    }

    /**
     * 描述：新增/编辑微信信息
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function edit()
    {
        $id = I('id');
        $type = I('type');
        $url = U('add');
        if (is_numeric($id)) {
            $qbaobeiWeixin = D('QbaobeiWeixin');
            $data = $qbaobeiWeixin->getWeixinById($id);
            $data['category_id'] = $qbaobeiWeixin->getCategoryIds($id, $data['type']);
            $this->assign('data', $data);
            $url = U('update');

        }
        $this->assign('url', $url);
        $this->assign('type', $type);
        $this->display('edit');
    }

    /**
     * 描述：根据类型（手机和pc,手机位1,pc为0）,获取微信列表
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function index()
    {
        //微信类型
        $type = I('type') ? : 0;
        $row = 20;
        $p = intval(I('p'));
        $p <= 0 && $p = 1;
        //获取微信列表数据
        $rs = D('QbaobeiWeixin')->listWeixin($type, $p, $row);
        $count = D('QbaobeiWeixin')->getCountWeixin($type);
        //判断返回结果是否为空
        if (!empty($rs)) {
            foreach ($rs as &$val) {
                $val['category_id'] = explode(',', $val['category_str']);
            }
        }
        $page = new \Think\Page($count, $row);
        $p = $page->show();
        $this->assign('_page', $p ? $p : '');
        $this->assign('list', $rs);
        $this->display('list');
    }

    /**
     * 描述：根据类型，二维码html模块，分类数组生成各个栏目的二维码ssi 文件
     *
     * @param int    $type
     * @param string $html_content
     * @param array  $category_ids
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    private function _create($type = 0, $html_content = '', $category_ids = array())
    {
        if (empty($category_ids)) {
            return;
        }
        //PC版微信二维码ssi文件生成目录
        $pc_weixin_dir = C('STATIC_ROOT') . '/weixin/';
        //手机版版微信二维码ssi文件生成目录
        $m_weixin_dir = C('MOBILE_STATIC_ROOT') . '/weixin/';
        //文件后缀
        $ext = '.html';
        //判断类型为手机还是pc
        if ($type == 1) {
            $dir = $m_weixin_dir;
        } else {
            $dir = $pc_weixin_dir;
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        foreach (@$category_ids as $val) {
            $full_name = $dir . $val . $ext;
            self::staticCreate($full_name, $html_content);
        }
    }

    /**
     * 描述：生成微信二维码ssi 文件
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function createWeixin()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            $this->error('生成失败！');
        }
        $rs = D('QbaobeiWeixin')->getWeixinById($id);
        if (!empty($rs)) {
            $category_ids = explode(',', $rs['category_str']);
            $out['info'] = '生成成功！';
            $out['status'] = 1;
            $out['url'] = Cookie('__forward__');
            header('Content-Type:application/json; charset=utf-8');
            echo(json_encode($out));
            //数据添加完毕直接响应客户端，后续后台运行;下面逻辑均为后台逻辑
            function_exists('fastcgi_finish_request') && fastcgi_finish_request();
            $this->_create($rs['type'], $rs['content'], $category_ids);
        } else {
            $this->error('生成失败！');
        }
    }

    /**
     * 描述：生成静态文件
     *
     * @param $path
     * @param $data
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public static function staticCreate($path, $data)
    {
        if (!$data) {
            return;
        }
        $fh = fopen($path, 'w');
        //独占锁
        flock($fh, LOCK_EX);
        fwrite($fh, $data);
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
     * 描述：获取微信二维码模块对应的栏目数组
     *
     * @return array|bool
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    protected function getCategoryInfos()
    {
        $category_ids = I('category_id');
        if (!is_array($category_ids)) {
            return false;
        } else {
            $data = array();
            foreach ($category_ids as $key => $val) {
                $data[$key]['type'] = I('type') ? : 0;
                if (I('id')) {
                    $data[$key]['type_id'] = I('id');
                }
                $data[$key]['create_time'] = time();
                $data[$key]['update_time'] = time();
                $data[$key]['category_id'] = $val;
            }
            return $data;
        }
    }

    /**
     * 描述：删除微信模块
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function deleteWeixin()
    {
        $id = I('id');
        if (D('QbaobeiWeixin')->deleteWeixin($id)) {
            $this->success('成功删除该微信模块！');
        } else {
            $this->error('删除该微信模块失败！');
        }
    }
}
