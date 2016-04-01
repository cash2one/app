<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

/**
 * 礼包内容控制器
 */
class PackageController extends BaseDocumentController {
 
    //分类模型名
    protected $cate_name = 'PackageCategory';

    //文档模型名
    protected $document_name = 'Package';
    //会员模型名
    protected $member = 'member';

    //加载视图选择控制
    protected $view_select = array(
        'add' => 'Package:add', 
        'edit' => 'Package:edit', 
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
        }

        //获取内容编辑员信息
        $uid_id_str = implode(',', $uid_id);
        $member_datas = M($this->member)->field('uid, nickname,username')->where(array('uid'=>array('in',$uid_id_str)))->select();
        foreach ($member_datas as $k => $v) {
            $member_data[$v['uid']] = $v;
            unset($member_datas[$k]);
        }
        //注入数据
        foreach ($data as $k => $v) {
          $data[$k]['name']        = empty ($member_data[$v['edit_id']]['username']) ? (empty($member_data[$v['uid']]['username']) ? $member_data[$v['uid']]['nickname'] : $member_data[$v['uid']]['username']) : $member_data[$v['edit_id']]['username'];
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
}