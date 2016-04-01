<?php
/**
 * Created by Evan Hou.
 * User: Evan <hjpking@126.com>
 * Date: 15/5/7 下午4:23
 */

namespace I\Controller;

use Think\Controller;
use Think\Upload;

class ProductController extends BaseController
{
	protected $table_category = 'down_category';

	public function index()
	{
		$limit = 10;
		$page  = (int)I('p', 1);
		$start = ($page > 0 ? $page - 1 : 0) * $limit;

		$user = user_is_login();
		$where = array('user_id' => $user['uid']);
		$data = M('down')->where($where)->field('id, title, category_id, update_time, status, model_id, previewimg')
			->order('id desc')->limit($start, $limit)->select();
		$data = parseDocumentList($data);


		$category_id = array_filter(array_unique(array_column($data, 'category_id')));
		$category_data = (empty ($category_id)) ? array() :
			M('down_category')->where(array('id'=>array('in',implode(',', $category_id))))->getField('id, name, title, pid');



		$img_id = array_filter(array_unique(array_column($data, 'previewimg')));
		$picture_data = (empty ($img_id)) ? array() : M('picture')
			->where(array('id' => array('in' => implode(',', $img_id))))->getField('id, path');


		$count      = M('down')->where($where)->count();
		$Page       = new \Think\Page($count, $limit);
		$pagination = $Page->show();


		$SEO = array(
			'title' => '产品列表 -- 官方网',
			'keywords' => '产品列表',
			'description' => '产品列表'
		);
		$this->assign('SEO', $SEO);
		$this->assign('cate', $category_data);
		$this->assign('list', $data);
		$this->assign('page', $pagination);
		$this->assign('pic',  $picture_data);
		$this->display();
	}

	public function create()
	{
		$product_type = C('PRODUCT_TYPE');
		$pid = (int)I('pid');
		$step = trim(strtolower(I('step')));
		$token = guid();
		cookie('token', $token);


		if ($pid)
		{
			$data = M('down d')->where(array('d.id' => $pid))->find();
			if (empty($data['model_id']))
			{
				exit('模型不存在');
			}

			$model_data = M('model')->getById($data['model_id']);
			$detail_table_name = 'down_'.$model_data['name'];


			if (array_key_exists($step, $product_type))
			{
				$detail_data = M($detail_table_name)->getById($pid);
				$data = array_merge($data, $detail_data);
			}


			$download_data = M('down_address')->where(array('did' => $pid))->getField('id, did, name, url');

			$picture_data = array();
			if (!empty ($data['previewimg']))
			{
				$previewimg = array('id'=>array('in',$data['previewimg']));
				$picture_data = M('picture')->where($previewimg)->select();
			}


			$tmp_cate_data = M('down_category')->getField('id, title, pid');
			$cate_link = getParentCategory($data['category_id'], 'down_category');
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

			if (empty ($cate_link))
				$cate_link = $this->get_category(0);

			$cate_data = $cate_link;
			$SEO = array(
				'title' => '修改产品 -- 官方网',
				'keywords' => '修改产品',
				'description' => '修改产品'
			);
			$this->assign('SEO', $SEO);
			$this->assign('picture_data', $picture_data);
			$this->assign('download_data', $download_data);
			$this->assign('data', $data);
			$this->assign('pid', $pid);
		}
		else
		{
			$SEO = array(
				'title' => '添加产品 -- 官方网',
				'keywords' => '添加产品',
				'description' => '添加产品'
			);
			$cate_data[0] = $this->get_category(0);
		}


		$user = user_is_login();
		$website_data = M('package')->where(array('user_id' => $user['uid']))->getField('id,title');


		switch ($step)
		{
			case 'base':
				$tpl = 'create';break;
			case 'detail_entity':
				$tpl = 'create_entity';
				break;
			case 'detail_network':
				$tpl = 'create_network';
				break;
			default:
				$tpl = 'create';
				break;
		}

		$this->assign('token', $token);
		$this->assign('SEO', $SEO);
		$this->assign('product_type', $product_type);
		$this->assign('website_data', $website_data);
		$this->assign('cate_data', $cate_data);
		$this->display($tpl);
	}

	public function save()
	{
		$timestamp = time();
		$product_type = C('PRODUCT_TYPE');
		$pid = (int)I('pid');
		$step = trim(strtolower(I('step')));
		$save_step = trim(strtolower(I('save_step')));

		//基础数据操作
		if ('base' == $save_step)
		{
			$token = cookie('token');
			if ($token != I('token'))
			{
				$this->error('提交的内容已失效');
				exit();
			}


			$user = user_is_login();
			$category_id = (array)I('category_id');
			$name = get_pinyin(trim(I('title')));
			$title = trim(I('title'));

			$data = array(
				'uid' => 0,
				'name' => $name,
				'user_id' => $user['uid'],
				'title' => $title,
				'title_pinyin' => $name,
				'package_id' => (int)I('package_id'),
				'category_id' => array_pop($category_id),
				'model_id' => $product_type[$step]['model_id'],
				'status' => 2,
				'update_time' => $timestamp,
				'seo_title' => $title.' 价格 批发 厂家_官方网',
				'seo_keywords' => $title.'价格 '.$title.'批发 '.$title.'厂家',
				'seo_description' => '官方网为您提供最新的'.$title.'价格，'.$title.'批发，'.$title.'厂家。想了解更加全面的'.$title.'就上官方网',
			);

			$pid && $data['id'] = $pid;
			if (!$pid)
			{
				$data['description'] = '';
				//$data['path_detail'] = 'product/'.$name;//(empty($name) ? '{id}' : $name);
				$data['path_detail'] = '';
				$data['edit_id'] = 0;
				$data['position'] = 0;
				$data['audit'] = 0;
				$data['home_position'] = 0;
                $data['create_time'] = time();
			}

			$last_id = $pid ? M('down')->save($data) : M('down')->add($data);
			$pid = $pid ? $pid : $last_id;
			if (false === $last_id)
			{
				exit('操作失败');
			}
			redirect('/Product/create/step/'.$step.'/pid/'.$pid);
			return ;
		}

		//详细数据操作
		if ('detail' == $save_step)
		{
			$data = M('down')->where(array('id' => $pid))->find();
			if (empty($data['model_id']))
			{
				exit('模型不存在');
			}

			$model_data = M('model')->getById($data['model_id']);
			$detail_table_name = 'down_'.$model_data['name'];

			//处理附加参数
			$custom_param = (array)I('custom_param');
			$parameter = '';
			foreach ($custom_param as $v)
			{
				$replace_arr = array(':', '：', '|', '|');
				$key = str_replace($replace_arr, '', $v['key']);
				$val = str_replace($replace_arr, '', $v['val']);

				$parameter .= $key.':'.$val.'|';
			}

			if (13 == $data['model_id'])
			{
				$detail_data = array(
					'id' => $pid,
					'size' => trim(I('size')),
					'language' => trim(I('language')),
					'version' => trim(I('version')),
					'network' => trim(I('network')),
					'licence' => trim(I('licence')),
					'rank' => trim(I('rank')),
					'supplier' => trim(I('supplier')),
					'update_time' => strtotime(I('update_time')),
					'system' => trim(I('system')),
					'parameter' => rtrim($parameter, '|'),
					'content' => trim(I('content')),
					'sub_title' => $data['title'],
				);
			}
			else
			{
				$detail_data = array(
					'id' => $pid,
					'brand' => trim(I('brand')),
					'material' => trim(I('material')),
					'model' => trim(I('model')),
					'market_price' => I('market_price'),
					'price' => I('price'),
					'limit' => (int)I('limit'),
					'stock' => (int)I('stock'),
					'parameter' => rtrim($parameter, '|'),
					'content' => trim(I('content')),
					'supplier' => trim(I('supplier')),
				);
			}


			//检测详情表是否存在数据
			$is_exist = M($detail_table_name)->getById($pid);
           
          //  if (!$is_exist)
			//{
				//exit('发布失败,请重试...');
			//}
			$res = (null == $is_exist) ? M($detail_table_name)->add($detail_data) : M($detail_table_name)->save($detail_data);

			if (false === $res)
			{
				exit('操作失败');
			}

			//保存下载地址
			if (13 == $data['model_id'])
			{
				$download = I('download');

				$download_data = array();
				foreach ($download as $k => $v)
				{
					if (empty($v['url']) || $pid < 0)
						continue;

					$download_data['url'] = trim($v['url']);
					$download_data['did'] = (int)$pid;
					$download_data['name'] = $data['title'];
					$download_data['update_time'] = $timestamp;
					isset ($v['id']) && $v['id'] > 0 && $download_data['id'] = (int)$v['id'];

					$res = $download_data['id'] > 0 ? M('down_address')->save($download_data) : M('down_address')->add($download_data);
				}
			}

			//图片保存
			if (isset($_FILES['images']) && 0 >= $_FILES['images']['error'][0])
			{
				$photo_id = array();
				foreach ($_FILES['images'] as $key => $item) {
					foreach ($item as $k => $v)
						$_FILES['image' . $k][$key] = $v;
				}
				unset($_FILES['images']);
				if ($_FILES['image0']['size'] > 0) {
					$config = array(
						'maxSize'  => 3145728,
						'savePath' => '/Picture/',
						'saveName' => array('uniqid',''),
						'exts'     => array('jpg', 'gif', 'png', 'jpeg'),
						'autoSub'  => true,
						'subName'  => array('date','Ymd'),
					);
					$upload = new Upload($config);

					foreach ($_FILES as $key => $item) {
						$upload_info = $upload->upload(array($_FILES[$key]));
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
								'create_time' => $timestamp,
							);
							$res = M('picture')->add($photo);
							(false != $res) &&  $photo_id[] = $res;
						}
					}

					if (!empty ($photo_id))
					{
						$photo_str = implode(',', array_filter(array_unique($photo_id)));
						$suffix = empty ($data['previewimg']) ? '' : $data['previewimg'].',';
						M('down')->save(array('id' => $pid, 'previewimg' => $suffix.$photo_str));
					}
				}
			}
		}
		$SEO = array(
			'title' => '保存产品 -- 官方网',
			'keywords' => '保存产品',
			'description' => '保存产品'
		);
		$this->assign('SEO', $SEO);
		$tpl = 'success';
		$this->display($tpl);
	}

	public function delete()
	{
		$user = user_is_login();
		$pid  = (int)I('pid');

		if (!$pid)
		{
			$this->ajaxReturn(ajax_return_data_format($pid, '网站ID为空', 0));
			return;
		}

		//获取产品基础信息
		$data = M('down')->where(array('id' => $pid, 'user_id' => $user['uid']))->find();
		if (empty($data))
		{
			$this->ajaxReturn(ajax_return_data_format($pid, '产品不存在', 0));
			return;
		}

		//删除产品基础信息
		$where = array('id' => $pid);
		$res = M('down')->where($where)->delete();
		if (false === $res)
		{
			$this->ajaxReturn(ajax_return_data_format($pid, '删除基础信息失败', 0));
			return;
		}

		//删除产品详细信息
		$model_data = M('model')->getById($data['model_id']);
		$detail_table_name = 'down_'.$model_data['name'];
		$res = M($detail_table_name)->where($where)->delete();
		if (false === $res)
		{
			$this->ajaxReturn(ajax_return_data_format($pid, '删除失败', 0));
			return;
		}

		//判断是否为网络产品，是则删除下载地址
		if (13 == $data['model_id'])
		{
			$res = M('down_address')->where(array('did' => $pid))->delete();
			if (false === $res)
			{
				$this->ajaxReturn(ajax_return_data_format($pid, '删除下载地址失败', 0));
				return;
			}
		}

		//删除产品图片信息
		if (!empty ($data['previewimg']))
		{
			$res = M('picture')->delete($data['previewimg']);
			if (false === $res)
			{
				$this->ajaxReturn(ajax_return_data_format($pid, '删除失败', 0));
				return;
			}
		}

		$this->ajaxReturn(ajax_return_data_format($pid, '删除成功'));
	}

	public function delete_pic()
	{
		$id = (int)I('id');
		if (!$id)
		{
			$this->ajaxReturn(ajax_return_data_format($id, '图片ID为空', 0));
			return ;
		}

		$where = array('id' => $id);
		$res = M('picture')->where($where)->delete();
		if (false === $res)
		{
			$this->ajaxReturn(ajax_return_data_format($id, '删除失败', 0));
			return;
		}

		$this->ajaxReturn(ajax_return_data_format($id, '删除成功'));
	}
}