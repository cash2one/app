<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 下载内容控制器
 */
class DownController extends BaseDocumentController {

    //分类模型名
    protected $cate_name = 'DownCategory';

    //文档模型名
    protected $document_name = 'Down';

	protected $download_dmain = 'down_dmain';

	//会员模型名
	protected $member = 'member';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'Down:add', 
        'edit' => 'Down:edit', 
        );

    /**
     * HOOK方法，加载不同的特殊逻辑
     * @param string $method 方法名
     * @param array $params 参数
     * @return void
     * @author crohn <lllliuliu@163.com>
     */
    protected function hook($method, $params = array() , $data = array()){
        switch ($method) {
            case 'edit':
                //读取下载表
                $id = $params;
                $down_address = M('DownAddress')->where('did='. $id)->select();
				if(!empty($data)){
					$down_address[0]['url'] = $data['downloadUrl'];
				}
                $this->assign('down_address', $down_address);
            case 'add':
                //加载预定义站点
                $this->assign('presetsite', D('PresetSite')->getList());
                break;
        }
    }

    /**
     * 描述：批量审核
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function audit()
    {
        $ids    =   I('request.ids');
        if(empty($ids)) {
            $this->error('请选择要审核的文档！');
        }
        $data['audit'] = 1;
        foreach ($ids as $id) {  //主要是钩子引起__after_update要循环，有时间可以改这个hook
           $data['id'] = $id;
           D($this->document_name)->save($data);
        }
        $this->success('生成成功！', Cookie('__forward__'));
    }
	/**
	 * 列表页附加字段数据
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function addition_field(array $data)
	{
		if (empty ($data))
			return $data;


		//获取当前下载列表的ID和分类ID
		$download_id = $category_id = $uid_id = $user_id = $package_id = array();
		foreach ($data as $v) {
			$uid_id[$v['edit_id']] = $v['edit_id'];
			!isset ($uid_id[$v['uid']]) && $uid_id[$v['uid']] = $v['uid'];
            $user_id[$v['user_id']] = $v['user_id'];
			$download_id[$v['id']] = $v['id'];
            $package_id[$v['package_id']] = $v['package_id'];
			$category_id[$v['category_id']] = $v['category_id'];
		}

		//获取软件版本和分类
		$document_id_str = implode(',', $download_id);
		$article_datas = M($this->download_dmain)->field('id, version,system')->where(array('id'=>array('in',$document_id_str)))->select();
        $sql = "SHOW TABLES LIKE '". C('DB_PREFIX')."down_dsoft'";
        $res = M('Model')->query($sql);
        if(empty($article_datas) && count($res) > 0)
        {
            $article_datas = M('down_dsoft')->field('id, version,system')->where(array('id'=>array('in',$document_id_str)))->select();
        }
		foreach ($article_datas as $k => $v) {
			$article_data[$v['id']] = $v;
			unset($article_datas[$k]);
		}

		//获取分类信息
		$category_id_str = implode(',', $category_id);
		$category_datas = M($this->cate_name)->field('id, title as c_title')->where(array('id' => array('in', $category_id_str)))->select();
		foreach($category_datas as $k => $v) {
			$category_data[$v['id']] = $v;
			unset($category_datas[$k]);
		}

		//获取编辑员信息
		$uid_id_str = implode(',', $uid_id);
		$member_datas = M($this->member)->field('uid, nickname')->where(array('uid'=>array('in',$uid_id_str)))->select();
		foreach ($member_datas as $k => $v) {
			$member_data[$v['uid']] = $v;
			unset($member_datas[$k]);
		}
        //获取前台用户信息
       $user_id_str = implode(',', $user_id);
       $user_datas = M($this->member)->field('uid, nickname')->where(array('uid'=>array('in',$user_id_str)))->select();
        foreach ($user_datas as $k => $v) {
            $user_data[$v['uid']] = $v;
            unset($user_datas[$k]);
        }
        $package_id_str = implode(',', $package_id);
        $package_datas = M('Package')->field('id, title')->where(array('id'=>array('in',$package_id_str)))->select();
        foreach ($package_datas as $k => $v) {
            $package_data[$v['id']] = $v;
            unset($package_datas[$k]);
        }
        //获取下载平台数组
        $system_array =  C(FIELD_DOWN_SYSTEM);
		//注入数据
		foreach ($data as $k => $v) {
            $sys_key = $article_data[$v['id']]['system'];
            /**
             * 以后系统中，命名尽量观词达意，尽量不要占用数据库中的字段
             * 图片字段不明朗和有可能在以后系统中要用到，建议去掉用图片id代替分类id，还有很多字段在数据库中存在，可能以后系统会用到
             * 由于前面很多系统用了这些字段，暂时不替换
             */
            $data[$k]['cover_id']       = $system_array[$sys_key];
            $data[$k]['smallimg']       = $data[$k]['category_id'];
            $data[$k]['category_id']    = $category_data[$v['category_id']]['c_title'];
			$data[$k]['name']           = $article_data[$v['id']]['version'];
            $data[$k]['package_id']     = $package_data[$v['package_id']]['title'];
			$data[$k]['description']    = empty($member_data[$v['edit_id']]['nickname']) ? $member_data[$v['uid']]['nickname'] : $member_data[$v['edit_id']]['nickname'];
            $data[$k]['user_id']        = $user_data[$v['user_id']]['nickname'];
            $data[$k]['cate_id']        = $data[$k]['category_id']; //肖书成 增加分类ID 修改于2015-9-17
            /*
             * @date 修改于2015-8-13日
             * @author 谭坚
             *
             */
            $data[$k]['root'] = $member_data[$v['uid']]['nickname']; //创建人
            $data[$k]['pid'] = $member_data[$v['edit_id']]['nickname']; //最后修改人

		}

		return $data;
	}

	/**
	 * 根据不同的模型给予不同的字段
	 *
	 * @param array $field
	 *
	 * @return array
	 */
	protected function addition_field_name(array $field)
	{
		$self_field = array('edit_id');
		return array_merge($field, $self_field);
	}

	public function address_check()
	{
		$where      = array();
		$page_size  = 20;
		$page       = I('p') ? I('p') : 1 ;
		$title      = trim(I('title'));
		$status     = (int)I('status');
        $check_status = I('check_status');
		$time_end   = strtotime(I('time_end'));
		$time_start = strtotime(I('time_start'));
		$start_page = ($page - 1) * $page_size;
		if (!empty ($time_start))
		{
			$time_end = empty ($time_end) ? time() : $time_end;
			$where['a.update_time'] = array(array('gt', $time_start),array('lt', $time_end)) ;
		}
        if($check_status !='') $where['b.check_status'] = $check_status;
        $status && $where['a.status'] = $status;
		$title  && $where['a.title']  = array('like','%'.$title.'%');
		$category_data  = M($this->cate_name)->getField('id, name, title, pid');
		$download_point = M('preset_site')->getField('id, site_name, site_url, download_name, download_url');
		$download_count = M($this->document_name)->alias('a')->join('RIGHT JOIN __DOWN_ADDRESS__ b ON b.did=a.id')->where($where)->count('a.id');
		$download_data  = M($this->document_name)->alias('a')->join('RIGHT JOIN __DOWN_ADDRESS__ b ON b.did=a.id')->field('a.id, a.title, a.category_id, a.update_time, a.status,b.check_status,b.site_id,b.url')->where($where)->order('b.check_status desc,a.update_time desc')->limit($start_page, $page_size)->select();
        $download_data = $this->download_address($download_data, $download_point);
		$Page       = new \Think\Page($download_count, $page_size);
		$pagination = $Page->show();

		$this->assign('total', $download_count);
		$this->assign('category', $category_data);
		$this->assign('download', $download_data);
		//$this->assign('download_point', $download_point);
		$this->assign('_page', $pagination);
		$this->display();
	}

	/**
	 * 检查URL
	 */
	public function check_url()
	{
        $download_point = M('preset_site')->getField('id, site_name, site_url, download_name, download_url');
        $download_data  = M($this->document_name)->alias('a')->join('RIGHT JOIN __DOWN_ADDRESS__ b ON b.did=a.id')->field('b.id, a.title, a.category_id, a.update_time, a.status,b.check_status,b.site_id,b.url')->where('a.update_time > b.check_time or b.check_status=0 or b.check_status=999  or (b.check_status=404 and a.status=1) ')->order('b.check_status desc,a.update_time desc')->select();
        $download_data = $this->download_address($download_data, $download_point);
        if(!empty($download_data))
        {
            foreach($download_data as $val)
            {
                $url = $val['download_url'];
                if(!empty($url))
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);//不获取http头信息
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT,20);
                    curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);//网页状态码
                    curl_close($ch);
                    unset($ch);
                    $data= array();
                    $data['id'] = $val['id'];
                    $data['check_status'] = $code;
                    $data['check_time'] = time();
                    M('DownAddress')->save($data);
                    unset($data);
                }
                else
                {
                    $data= array();
                    $data['id'] = $val['id'];
                    $data['check_status'] = 888;  //链接地址为空
                    $data['check_time'] = time();
                    //  dump($data);
                    M('DownAddress')->save($data);
                    unset($data);
                }

            }
        }
        $this->success('检测成功！','',IS_AJAX);

	}
	/**
	 * 下载数据与下载地址数据拼装
	 *
	 * @param array $download
	 * @param array $address
	 *
	 * @return array
	 */
	private function download_address_assembled(array $download, array $address)
	{
		if (empty ($download) || empty ($address))
			return array();

		foreach ($download as $key => $val)
		{
			foreach ($address as $k => $v)
			{
				if ($val['id'] == $v['did'])
				{
					$v['download_url'] = formatAddress($v['url'],$v['site_id']);
					$download[$key]['address'][] = $v;
					unset($address[$k]);
				}

			}
		}

		return $download;
	}

	private function download_address(array $download_address, array $download_point)
	{
		$_webServer = C('WEB_SERVER');
		$webServer = array();
		foreach ($_webServer as $kk => $vv)
			$webServer[$kk] = (false !== strpos($vv, '|')) ? $_tmp = explode('|', $vv) : array($vv);


		$download_address_data = array();
		foreach ($download_address as $key => $val)
		{
			if (empty ($download_point[$val['site_id']]))
				continue;

			$_tmp_url = parse_url($download_point[$val['site_id']]['site_url']);

			$download_address[$key]['site_name'] = $download_point[$val['site_id']]['site_name'];
			$download_address[$key]['site_url'] = $download_point[$val['site_id']]['site_url'];
            $download_address[$key]['download_url'] = formatAddress($val['url'],$val['site_id']);

			if (empty($webServer[$_tmp_url['host']]))
			{
				$download_address[$key]['down_ip'] = '';
			}
			else
			{
				foreach ($webServer[$_tmp_url['host']] as $v)
				{
					$download_address[$key]['down_ip'] = $v;
				}
			}

			$download_address_data[] = $download_address[$key];
		}

		return $download_address_data;
	}
}