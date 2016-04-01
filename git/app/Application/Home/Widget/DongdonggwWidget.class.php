<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/4/16 下午2:58
 */

namespace Home\Widget;
use Common\Controller\WidgetController;
use Think\Controller;


class DongdongGWWidget extends WidgetController
{
	public function Index()
	{
		//根据传入static_page表ID查找数据
		$page_id = I('page_id');
		$page_info = get_staticpage($page_id);

		$seo_info = array(
			'title' => $page_info['title'],
			'keywords' => $page_info['keywords'],
			'description' => $page_info['description'],
		);

		$config = C('PHONE_GAME_INFO');
		$this->assign('pgi', $config);
		$this->assign('seo', $seo_info);
		$this->display('Widget/index');
	}

	/**
	 * 更新日志
	 */
	public function update_log()
	{
		$page_id = (int)I('page_id');
		$page_info = get_staticpage($page_id);

		$seo_info = array(
			'title' => $page_info['title'],
			'keywords' => $page_info['keywords'],
			'description' => $page_info['description'],
		);

		$this->assign('custom_content', $page_info['custom_content']);
		$this->assign('seo', $seo_info);
		$this->display('Widget/update_log');
	}

	/**
	 * 帮助中心
	 */
	public function help()
	{
		$page_id = (int)I('page_id');
		$page_info = get_staticpage($page_id);

		$seo_info = array(
			'title' => $page_info['title'],
			'keywords' => $page_info['keywords'],
			'description' => $page_info['description'],
		);

		$this->assign('seo', $seo_info);

		$this->display('Widget/help');
	}

	/**
	 * 问题反馈
	 */
	public function feedback()
	{
		$page_id = (int)I('page_id');
		$page_info = get_staticpage($page_id);

		$seo_info = array(
			'title' => $page_info['title'],
			'keywords' => $page_info['keywords'],
			'description' => $page_info['description'],
		);

		$this->assign('seo', $seo_info);

		$this->assign('hand_tag', C('DDW_HAND_NET_TAG'));
		$this->assign('hand_aide_tag', C('DDW_HAND_AIDE_TAG'));
		$this->assign('self', __SELF__);
		$this->display('Widget/feedback');
	}

    /**
     * 作者:肖书成
     * 描述:助手页面的脚部
     */
    public function foot(){
        $page_id = (int)I('page_id');
        $page_info = get_staticpage($page_id);

        if(!empty($page_info['description'])){
            $arr = explode('|',$page_info['description']);
            $data['qq']     = trim($arr[0]);
            $data['email']  = trim($arr[1]);
            $data['qq']     = array_filter(explode('|',str_replace(PHP_EOL,'|',$data['qq'])));
            $data['email']  = array_filter(explode('|',str_replace(PHP_EOL,'|',$data['email'])));
            $this->assign('data',$data);
        }
        $this->display('Widget/zsFoot');
    }

}