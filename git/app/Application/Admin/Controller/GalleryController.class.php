<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 图库内容控制器
 */
class GalleryController extends BaseDocumentController {

	//分类模型名
    protected $cate_name = 'GalleryCategory';

    //文档模型名
    protected $document_name = 'Gallery';

	//会员模型名
	protected $member = 'member';

	//加载视图选择控制
    protected $view_select = array(
        'index' => 'BaseDocument:galleryMain',
		'add'	=> 'BaseDocument:galleryAdd',
		'edit'	=> 'BaseDocument:galleryEdit',
    );
	

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

		//获取当前文章列表的ID和分类ID
		$uid_id = $category_id = array();
		foreach ($data as $v) {
			$uid_id[$v['edit_id']] = $v['edit_id'];
			!isset ($uid_id[$v['uid']]) && $uid_id[$v['uid']] = $v['uid'];
			$category_id[$v['category_id']] = $v['category_id'];
		}

		//获取内容编辑员信息
		$uid_id_str = implode(',', $uid_id);
		$member_datas = M($this->member)->field('uid, nickname')->where(array('uid'=>array('in',$uid_id_str)))->select();
		foreach ($member_datas as $k => $v) {
			$member_data[$v['uid']] = $v;
			unset($member_datas[$k]);
		}

		//获取分类信息
		$category_id_str = implode(',', $category_id);
		$category_datas = M($this->cate_name)->field('id, title as c_title')->where(array('id' => array('in', $category_id_str)))->select();
		foreach($category_datas as $k => $v) {
			$category_data[$v['id']] = $v;
			unset($category_datas[$k]);
		}

		//注入数据
		foreach ($data as $k => $v) {
            $data[$k]['smallimg'] = $data[$k]['category_id'];
			$data[$k]['category_id'] = $category_data[$v['category_id']]['c_title'];
			$data[$k]['name']        = empty ($member_data[$v['edit_id']]['nickname']) ? $member_data[$v['uid']]['nickname'] : $member_data[$v['edit_id']]['nickname'];
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
		$self_field = array('name', 'edit_id');
		return array_merge($field, $self_field);
	}
}