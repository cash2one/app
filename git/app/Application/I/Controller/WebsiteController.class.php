<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/5/7 下午4:26
 */

namespace I\Controller;

use Think\Controller;
use Think\Upload;

class WebsiteController extends BaseController
{
	protected $table_category = 'package_category';
	/**
	 * 列表
	 */
	public function index()
	{
		$user  = user_is_login();
		$limit = 10;
		$page  = (int)I('p', 1);
		$start = ($page > 0 ? $page - 1 : 0) * $limit;


		$where = array('user_id' => $user['uid']);
		$data = M('package')->field('id, title, category_id, update_time, status, url, model_id, srecom_id')
			->where($where)->order('id desc')->limit($start, $limit)->select();
		$data = parseDocumentList($data);


		$category_id = array_filter(array_unique(array_column($data, 'category_id')));
		$category_data =  empty ($category_id) ? array() : M('package_category')->where(array('id'=>array('in',implode(',', $category_id))))
			->getField('id, name, title, pid');



		$count      = M('package')->where($where)->count();
		$Page       = new \Think\Page($count, $limit);
		$pagination = $Page->show();


		$SEO = array(
			'title' => '网站列表 -- 官方网',
			'keywords' => '网站列表',
			'description' => '网站列表'
		);
		$this->assign('SEO', $SEO);
		$this->assign('cate', $category_data);
		$this->assign('list', $data);
		$this->assign('page', $pagination);
		$this->display();
	}

	/**
	 * 创建
	 */
	public function create()
	{
		$user = user_is_login();
		$wid  = (int)I('wid');
		$step = trim(strtolower(I('step')));
		$token = guid();
		cookie('token', $token);

		if ($wid)
		{
			$data = M('package p')->join('left join onethink_package_particle pp on p.id=pp.id')
				->where(array('p.id' => $wid, 'p.user_id' => $user['uid']))->find();
			$this->assign('data', $data);


			$tmp_cate_data = M('package_category')->getField('id, title, pid');
			$cate_link = getParentCategory($data['category_id']);
			foreach ($cate_link as $key => $val)
			{
				foreach ($tmp_cate_data as $k => $v)
				{
					if ($v['pid'] == $val['pid'])
					{
						$cate_link[$key]['data'][$k] = $v;
						unset($tmp_cate_data[$k]);
					}
				}
			}

			$SEO = array(
				'title' => '修改网站 -- 官方网',
				'keywords' => '修改网站',
				'description' => '修改网站'
			);

			if (empty ($cate_link))
				$cate_link[0] = $this->get_category(0);

			$cate_data = $cate_link;

			$this->assign('city_data', $this->get_area_next_tree($data['province_id'], 'province'));
			$this->assign('area_data', $this->get_area_next_tree($data['city_id'], 'city'));
		}
		else
		{
			$SEO = array(
				'title' => '添加网站 -- 官方网',
				'keywords' => '添加网站',
				'description' => '添加网站'
			);
			$cate_data[0] = $this->get_category(0);
		}


		switch ($step)
		{
			case 'base'  : $tpl = 'create';break;
			case 'detail': $tpl = 'detail';break;
			default: $tpl = 'create';break;
		}


		$this->assign('SEO', $SEO);
		$this->assign('token', $token);
		$this->assign('province_data', getProvince());
		$this->assign('cate_data', $cate_data);
		$this->display($tpl);
	}

	/**
	 * 持久
	 */
	public function save()
	{
		$step = trim(strtolower(I('step')));
		$wid  = (int)I('wid');

		switch ($step)
		{
			case 'detail':
				$token = cookie('token');
				if ($token != I('token'))
				{
					$this->error('提交的内容已失效');
					exit();
				}
				$timestamp = time();
				$category_id = (array)I('category_id');
				$user  = user_is_login();
				$name = get_pinyin(trim(I('title')));
				$url  = trim(I('url'));
				$url  = (false === strpos($url, 'http')) ? 'http://'.$url : $url;
				$title = trim(I('title'));
				$set_title = $title.'官网_'.$title.'官方网站 - www.guanfang123.com官方网';
				$set_key = $title.'官网,'.$title.'官方网站,'.$title.'官方产品,'.$title.'官方介绍';
				$set_description = 'guanfang123官网为您整理出'.$title.'官方网站的'.$title.'官网介绍、访问地址、官方产品等内容';

				$base_data = array(
					'user_id'   => $user['uid'],
					'name'      => $name,
					'title'     => $title,
					'url'       => $url,
					'model_id'  => 14,
					'link'      => trim(I('url')),
					'category_id'     => array_pop($category_id),
					'update_time'     => $timestamp,
					'title_pinyin'    => $name,
					'seo_title' => $set_title,
					'seo_key'   => $set_key,
					'seo_description' => $set_description,
				);
				$wid && $base_data['id'] = $wid;
				if (!$wid)
				{
					$path_detail = 'website/'.(empty ($name) ? '{id}' : $name);
					$base_data['status'] = 2;
					$base_data['create_time'] = $timestamp;
					$base_data['description'] = '';
					//$base_data['path_detail'] = $path_detail;
					$base_data['path_detail'] = '';
					$base_data['brecom_id'] = 0;
					$base_data['srecom_id'] = 0;
					$base_data['home_position'] = 0;
					$base_data['vertical_pic'] = 0;
					$base_data['create_time'] = $timestamp;
				}
				$last_id = $wid ? M('package')->save($base_data) : M('package')->add($base_data);

				if (false == $last_id)
				{
					exit('添加失败!');
				}

				$is_first = false;
				if (!$wid)
				{
					$wid = $last_id;
					$is_first = true;
				}

				$detail_data = array(
					'id'       => $wid,
					'contacts' => trim(I('contacts')),
					'phone'    => trim(I('phone')),
					'telphone' => trim(I('telphone')),
					'address'  => trim(I('address')),
					'country'  => 1,
					'start_time'  => $timestamp,
					'province_id' => (int)I('province_id'),
					'city_id' => (int)I('city_id'),
					'area_id' => (int)I('area_id'),
				);
				if ($is_first)
				{
					$detail_data['content'] = '';
					$detail_data['server'] = '';
					$detail_data['game_type'] = '';
					$detail_data['server_type'] = '';
					$detail_data['county'] = 1;
				}
				$res = $is_first ? M('package_particle')->add($detail_data) : M('package_particle')->save($detail_data);
				if (!$res)
				{
					exit('操作失败');
				}

				redirect('/Website/create/step/detail/wid/'.$wid);
				break;
			case 'save':
				$description = trim(I('description'));
				$data = array(
					'id' => $wid,
					'content' => $description,
				);
				$res = M('package_particle')->save($data);
				if (false === $res)
				{
					exit('添加失败');
				}

				if (isset($_FILES['images']) && 0 >= $_FILES['images']['error'])
				{
					$config = array(
						'maxSize'  => 3145728,
						'savePath' => '/Picture/',
						'saveName' => array('uniqid',''),
						'exts'     => array('jpg', 'gif', 'png', 'jpeg'),
						'autoSub'  => true,
						'subName'  => array('date','Ymd'),
					);
					$upload = new Upload($config);
					$upload_info = $upload->upload(array($_FILES['images']));
					if (!$upload_info) {
						exit('图片上传失败');
					} else {
						$upload_info = $upload_info[0];
						$photo = array(
							'path' => '/Uploads'.$upload_info['savepath'].$upload_info['savename'],
							'url' => '',
							'md5' => $upload_info['md5'],
							'sha1' => $upload_info['sha1'],
							'status' => 1,
							'create_time' => time(),
						);
						$photo_id = M('picture')->add($photo);
						if (false === $photo_id)
						{
							exit('保存图片失败');
						}

						M('package')->save(array('id' => $wid, 'brecom_id' => $photo_id, 'srecom_id' => $photo_id, 'vertical_pic' => $photo_id, 'cover_id' => $photo_id));
					}
				}
				$tpl = 'success';
				break;
			default:
				$tpl = 'detail';
				break;
		}

		$SEO = array(
			'title' => '保存网站 -- 官方网',
			'keywords' => '保存网站',
			'description' => '保存网站'
		);
		$this->assign('SEO', $SEO);
		$this->display($tpl);
	}

	/**
	 * 清空
	 */
	public function delete()
	{
		$wid = (int)I('wid');

		if (!$wid)
		{
			$this->ajaxReturn(ajax_return_data_format($wid, '网站ID为空', 0));
			return;
		}

		$user = user_is_login();
		$res = M('package')->where(array('id' => $wid, 'user_id' => $user['uid']))->delete();
		if (false === $res)
		{
			$this->ajaxReturn(ajax_return_data_format($wid, '删除失败', 0));
			return;
		}

		$res = M('package_particle')->where(array('id' => $wid))->delete();
		if (false === $res)
		{
			$this->ajaxReturn(ajax_return_data_format($wid, '删除失败', 0));
			return;
		}

		$this->ajaxReturn(ajax_return_data_format($wid, '删除成功'));
	}

	/**
	 * 获取地区信息
	 *
	 * @param        $id
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_area_next_tree($id, $type = 'province')
	{
		if (IS_AJAX)
		{
			$id = (int)I('id');
			$type = trim(I('type'));
		}

		if (!$id)
			return IS_AJAX ? $this->ajaxReturn(ajax_return_data_format(array())) : array();


		switch ($type)
		{
			case 'province':
				$cur_table = 'province';
				$next_table = 'city';
				$field = 'provincecode';
				break;
			case 'city':
				$cur_table = 'city';
				$next_table = 'area';
				$field = 'citycode';
				break;
		}

		if (!isset($cur_table))
			return IS_AJAX ? $this->ajaxReturn(ajax_return_data_format(array())) : array();

		$cur_data = M($cur_table)->where(array('id' => $id))->find();

		if (empty ($cur_data) || !isset($cur_data['code']))
			return array();


		$tree_data = M($next_table)->where(array($field => $cur_data['code']))->getField('id, code, name');


		return IS_AJAX ? $this->ajaxReturn(ajax_return_data_format($tree_data)) : $tree_data;
	}
}