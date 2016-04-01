<?php
// +----------------------------------------------------------------------
// |  产品标签逻辑模型 实现产品标签模块逻辑
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Logic;
use Think\Model;

/**
 * 产品标签逻辑模型
 */
class ProductTagsCategoryLogic extends Model{

    /**
     * 获取标签分类并显示select结构
     * @param string $class select的类名
     * @param string $selected 当前选择
     * @return string HTML标签字符     
    */
    public function getSelect($class = 'product_tags_category', $selected = null){
        $list = D('ProductTagsCategory')->getList();
        if ($selected &&  !is_array($selected)) $selected = explode(',', $selected);
        $html = '<select name="'.$class.'">';
        foreach ($list as $key => $value) {
            $check = '';
            if ($selected && in_array($value['id'], $selected)) $check = 'selected="selected"';
            $html .= '<option value="'.$value['id'].'" '.$check.'>'.$value['title'].'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * 获取标签分类并显示checkbox结构
     * @param string $class select的类名
     * @param string $selected 当前选择
     * @return string HTML标签字符     
    */
    public function getCheckbox($class = 'product_tags_category', $selected = null){
        $list = D('ProductTagsCategory')->getList();
        if ($selected &&  !is_array($selected)) $selected = explode(',', $selected);
        $html = '';
        foreach ($list as $key => $value) {
            $check = '';
            if ($selected && in_array($value['id'], $selected)) $check = 'checked="checked"';
            $html .= '<label class="checkbox"><input type="checkbox" value="'.$value['id'].'" name="'.$class.'[]" '.$check.'>'.$value['title'].'</label>';
        }
        return $html;
    }
    



}
