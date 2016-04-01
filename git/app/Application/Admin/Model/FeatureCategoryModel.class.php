<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/5/12
 * Time: 11:34
 */

namespace Admin\Model;


class FeatureCategoryModel extends BaseCategoryModel{
    //主模型名
    public $document_name = 'Feature';

    //分类模型名
    public $cate_name = 'FeatureCategory';

    //前台模块名
    public $module = 'Feature';

} 