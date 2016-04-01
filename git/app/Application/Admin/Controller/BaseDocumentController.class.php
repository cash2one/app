<?php
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;

use Admin\Model\AuthGroupModel;

/**
 * 基础文档内容控制器
 */
class BaseDocumentController extends AdminController
{

    /* 保存允许访问的公共方法 */
    protected static $allow = array('draftbox', 'mydocument');

    protected $cate_id = null; //文档分类id

    //基础视图模板文件夹
    protected $base_view = 'BaseDocument';

    /**
     * 检测需要动态判断的文档类目有关的权限
     *
     * @return boolean|null
     *      返回true则表示当前访问有权限
     *      返回false则表示当前访问无权限
     *      返回null，则会进入checkRule根据节点授权判断权限
     *
     * @author 朱亚杰  <xcoolcc@gmail.com>
     */
/*    protected function checkDynamic(){
if(IS_ROOT){
return true;//管理员允许访问任何页面
}
$cates = AuthGroupModel::getAuthCategories(UID);
switch(strtolower(ACTION_NAME)){
case 'index':   //文档列表
case 'add':   // 新增
$cate_id =  I('cate_id');
break;
case 'edit':    //编辑
case 'update':  //更新
$doc_id  =  I('id');
$cate_id =  M('Document')->where(array('id'=>$doc_id))->getField('category_id');
break;
case 'setstatus': //更改状态
case 'permit':    //回收站
$doc_id  =  (array)I('ids');
$cate_id =  M('Document')->where(array('id'=>array('in',$doc_id)))->getField('category_id',true);
$cate_id =  array_unique($cate_id);
break;
}
if(!$cate_id){
return null;//不明,需checkRule
}elseif( !is_array($cate_id) && in_array($cate_id,$cates) ) {
return true;//有权限
}elseif( is_array($cate_id) && $cate_id==array_intersect($cate_id,$cates) ){
return true;//有权限
}else{
return false;//无权限
}
return null;//不明,需checkRule
}*/

    /**
     * 显示左边菜单，进行权限控制
     * @author huajie <banhuajie@163.com>
     */
    protected function getMenu()
    {
        /*可以细分到栏目权限，需要扩展权限体系中的auth_extend表的type*/
        /*--start--*/
        //获取动态分类
        // $cate_auth  =   AuthGroupModel::getAuthCategories(UID); //获取当前用户所有的内容权限节点
        // $cate_auth  =   $cate_auth == null ? array() : $cate_auth;
        //$cate       =   M('PackageCategory')->where(array('status'=>1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();
        //没有权限的分类则不显示
        // if(!IS_ROOT){
        //     foreach ($cate as $key=>$value){
        //         if(!in_array($value['id'], $cate_auth)){
        //             unset($cate[$key]);
        //         }
        //     }
        // }
        /*--end--*/
        $cate = M($this->cate_name)->where(array('status' => 1))->field('id,title,pid,allow_publish,model')->order('pid,sort')->select();
        $cate = list_to_tree($cate); //生成分类树

        //获取分类id
        $cate_id = I('param.cate_id');
        if (!is_numeric($cate_id)) {
            $cate_id = I('param.cateid');
        }

        $this->cate_id = $cate_id;

        //是否展开分类
        $hide_cate = false;
        if (ACTION_NAME != 'recycle' && ACTION_NAME != 'draftbox' && ACTION_NAME != 'mydocument') {
            $hide_cate = true;
        }

        // 新增变量search,改造数据结构,数据用于频道栏目搜索 朱德胜 2015-06-25

        //生成每个分类的url
        $search = array();
        foreach ($cate as $key => &$value) {
            $value['url'] = $this->document_name . '/index?cate_id=' . $value['id'];
            if ($cate_id == $value['id'] && $hide_cate) {
                $value['current'] = true;
            } else {
                $value['current'] = false;
            }

            $search[$value['id']] = $value;
            $search[$value['id']] = array_merge($search[$value['id']], array('absTitle' => $value['title'], 'absurl' => U($value['url']), 'showType' => 'spread'));

            if (!empty($value['_child'])) {
                unset($search[$value['id']]['_child']);

                $is_child = false;
                foreach ($value['_child'] as $ka => &$va) {
                    $va['url'] = $this->document_name . '/index?cate_id=' . $va['id'];

                    $search[$va['id']] = $va;
                    $search[$va['id']] = array_merge($search[$va['id']], array('absTitle' => '<font color="#2B99FF">[' . $value['title'] . ']</font>&nbsp;' . $va['title'], 'absurl' => U($va['url']), 'showType' => 'open'));

                    if (!empty($va['_child'])) {
                        unset($search[$va['id']]['_child']);

                        foreach ($va['_child'] as $k => &$v) {
                            $v['url'] = $this->document_name . '/index?cate_id=' . $v['id'];
                            $v['absurl'] = U($v['url']);
                            $v['pid'] = $va['id'];
                            $is_child = $v['id'] == $cate_id ? true : false;

                            $search[$v['id']] = $v;
                            $search[$v['id']] = array_merge($search[$v['id']], array('absTitle' => '<font color="#2B99FF">[' . $va['title'] . ']</font>&nbsp;' . $v['title'], 'absurl' => U($va['url']), 'showType' => 'highlight'));
                        }
                    }

                    //展开子分类的父分类
                    if ($va['id'] == $cate_id || $is_child) {
                        $is_child = false;
                        if ($hide_cate) {
                            $value['current'] = true;
                            $va['current'] = true;
                        } else {
                            $value['current'] = false;
                            $va['current'] = false;
                        }
                    } else {
                        $va['current'] = false;
                    }
                }
            }
        }

        $this->assign('search', $search);
        $this->assign('nodes', $cate);
        $this->assign('cate_id', $this->cate_id);

        //获取面包屑信息
        $nav = get_parent_category_by_model($cate_id, $this->cate_name);
        $this->assign('rightNav', $nav);

        //获取回收站权限
        $show_recycle = $this->checkRule('Admin/' . $this->document_name . '/recycle');
        $this->assign('show_recycle', IS_ROOT || $show_recycle);
        //获取草稿箱权限
        $this->assign('show_draftbox', C('OPEN_DRAFTBOX'));
    }

    /**
     * 分类文档列表页
     * @param integer $cate_id 分类id
     * @param integer $model_id 模型id
     * @param integer $position 推荐标志
     */
    public function index($cate_id = null, $model_id = null, $position = null)
    {
        //获取左边菜单
        $this->getMenu();
        //正常左侧切换分类，清楚采集session
        session('SESSION_REDIRECTURL', null);
        session('SESSION_SITEID', null);
        if ($cate_id === null) {
            //清空移动和复制的cookie 此处有个BUG，如果在列表首页，则复制文档不成功。
            //session('moveArticle', null);
            //session('copyArticle', null);
            $cate_id = $this->cate_id;
        }
        $addtion_map = [];//聚合查询条件
        if (!empty($cate_id)) {
            $pid = I('pid', 0);
            // 获取列表绑定的模型
            if ($pid == 0) {
                $models = get_category_by_model($cate_id, 'model', $this->cate_name);
            } else {
                // 子文档列表
                $models = get_category_by_model($cate_id, 'model_sub', $this->cate_name);
            }
            if (is_null($model_id) && !is_numeric($models)) {
                // 绑定多个模型 取基础模型的列表定义
                $model = M('Model')->getByName($this->document_name);
            } else {
                $model_id = $model_id ?: $models;
                //获取模型信息
                $model = M('Model')->getById($model_id);
                if (empty($model['list_grid'])) {
                    $model['list_grid'] = M('Model')->getFieldByName($this->document_name, 'list_grid');
                }
            }
            $this->assign('model', explode(',', $models));
        } else {
            // 获取基础模型信息
            $model = M('Model')->getByName($this->document_name);
            $model_id = null;
            $cate_id = 0;
            $this->assign('model', null);

            //自定义聚合搜索字段 sunjianhua
            $aggregation_arr = [];
            if (!empty($model['aggregation_fields'])) {
                $aggregation_fields = explode(';', $model['aggregation_fields']);

                $attribute_mod = D('attribute');
                $map['model_id'] = $model['id'];
                $map['type'] = 'checkbox';
                foreach ($aggregation_fields as $v) {
                    $map['name'] = $v;
                    //获取自定义聚合字段配置值
                    $extra_ifno = $attribute_mod->getInfo($map, 'extra');
                    if (!$extra_ifno) {
                        $this->error('获取模型字段信息错误，请检查字段是否有误：' . $v);
                    }

                    $extra_ifno = $extra_ifno[0]['extra'];
                    if (preg_match('%\[(.*?)\]%', $extra_ifno, $match) > 0) {
                        //为系统配置信息
                        $aggregation_arr[$v] = C($match[1]);
                    } else {
                        //解析配置为数组
                        $aggregation_arr[$v] = parse_config_attr($extra_ifno);
                    }
                }

                //获取模型所有字段
                $model_fields_res = $attribute_mod->getInfo(['model_id' => $model['id']], 'name, title');
                foreach ($model_fields_res as $f) {
                    $model_fields[] = $f['name'];
                    $field_titles[$f['name']] = $f['title'];
                }

                //查询字段过滤
                foreach ($_GET as $k => $v) {
                    if (in_array($k, $model_fields)) {
                        $addtion_map[] = "$k & $v = $v";//checkbox类型字段特殊查询匹配
                    }
                }
            }

            $this->assign('aggregation', $aggregation_arr);
            $this->assign('field_titles', $field_titles);
        }

        //解析列表规则
        $fields = array();
        $grids = preg_split('/[;\r\n]+/s', trim($model['list_grid']));

        foreach ($grids as &$value) {
            // 字段:标题:链接
            $val = explode(':', $value);
            // 支持多个字段显示
            $field = explode(',', $val[0]);
            $value = array('field' => $field, 'title' => $val[1]);
            if (isset($val[2])) {
                // 链接信息
                $value['href'] = $val[2];
                // 搜索链接信息中的字段信息
                preg_replace_callback('/\[([a-z_]+)\]/', function ($match) use (&$fields) {
                    $fields[] = $match[1];
                }, $value['href']);
            }
            if (strpos($val[1], '|')) {
                // 显示格式定义
                list($value['title'], $value['format']) = explode('|', $val[1]);
            }
            foreach ($field as $val) {
                $array = explode('|', $val);
                $fields[] = $array[0];
            }
        }

        // 文档模型列表始终要获取的数据字段 用于其他用途
        $fields[] = 'category_id';
        $fields[] = 'model_id';
        $fields[] = 'pid';
        $fields[] = 'uid';

        //bug fix 2023: 根据不同的类别，类别各自实现附加字段名方法
        method_exists($this, 'addition_field_name') && $fields = $this->addition_field_name($fields);

        // 过滤重复字段信息
        $fields = array_unique($fields);

        // 列表查询
        $list = $this->getDocumentList($cate_id, $model_id, $position, $fields, $addtion_map);

        // 列表显示处理
        $list = parseDocumentList($list, $model_id);

        //bug fix 2023: 根据不同的类别，类别各自实现附加字段数据方法
        method_exists($this, 'addition_field') && $list = $this->addition_field($list);

        $this->assign('model_id', $model_id);
        $this->assign('position', $position);
        $this->assign('list', $list);
        $this->assign('model_name', $this->document_name);

        //$this->assign('category',   $list);
        $this->assign('list_grids', $grids);
        $this->assign('model_list', $model);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 默认文档列表方法
     * @param integer $cate_id 分类id
     * @param integer $model_id 模型id
     * @param integer $position 推荐标志
     * @param mixed $field 字段列表
     * @param array $addtion_map 查询条件 sunjianhua 2016.3.28
     */
    protected function getDocumentList($cate_id = 0, $model_id = null, $position = null, $field = true, $addtion_map = [])
    {
        /* 查询条件初始化 */
        $map = array();
        if (isset($_GET['title'])) {
            $t = (string) I('title');
            if (strstr($t, ' ')) {
                $t = array_filter(split(' ', $t));
                $map['_string'] = '1';
                foreach ($t as $value) {
                    $map['_string'] .= ' AND title like "%' . $value . '%"';
                }
            } else {
                $map['title'] = array('like', '%' . (string) I('title') . '%');
            }
        }
        if (isset($_GET['status'])) {
            $map['status'] = I('status');
            $status = $map['status'];
        } else {
            $status = null;
            $map['status'] = array('in', '0,1,2');
        }
        if (isset($_GET['time_start'])) {
            $map['update_time'][] = array('egt', strtotime(I('time_start')));
        }
        if (isset($_GET['time_end'])) {
            $map['update_time'][] = array('elt', 24 * 60 * 60 + strtotime(I('time_end')));
        }
        if (isset($_GET['nickname'])) {
            $map['uid'] = M('Member')->where(array('nickname' => I('nickname')))->getField('uid');
        }
        //需要对id算交集
        if (isset($_GET['system']) && $this->document_name == "Down") {
            $sid = M('DownDmain')->where(array('system' => I('system')))->getField('id', true); //符合要求的平台id
        }

        //需要对id算交集

        if ($this->document_name == "Down" && isset($_GET['download_address'])) {
            if ($_GET['download_address'] == "1") {
                $w['b.url'] = array(array('EXP', 'IS NULL'), array('eq', ''), 'or');
                //echo M('Down')->alias('a')->join('LEFT JOIN __DOWN_ADDRESS__ b ON a.id = b.did')->field('a.id')->where($w)->fetchSql(true)->select();
                $aid_arr = M('Down')->alias('a')->join('LEFT JOIN __DOWN_ADDRESS__ b ON a.id = b.did')->field('a.id as id')->where($w)->select(); //符合要求的平台id
                if (is_array($aid_arr)) {
                    foreach ($aid_arr as $v) {
                        $aid[] = $v['id'];
                    }
                }

            } elseif ($_GET['download_address'] == "2") {
                $w['b.id'] = array('gt', 0);
                $w['b.url'] = array(array('EXP', 'IS NOT NULL'), array('neq', ''), 'and');
                // echo M('Down')->alias('a')->join(' __DOWN_ADDRESS__ b ON a.id = b.did')->field('a.id')->where($w)->fetchSql(true)->select();
                $aid_arr = M('Down')->alias('a')->join('__DOWN_ADDRESS__ b ON a.id = b.did')->field('a.id as id')->where($w)->select(); //符合要求的平台id
                if (is_array($aid_arr)) {
                    foreach ($aid_arr as $v) {
                        $aid[] = $v['id'];
                    }
                }
            }
        }
        if (isset($_GET['tagname'])) {
            unset($data);
            $data['a.title'] = array('like', '%' . I('tagname') . '%');
            $data['b.type'] = strtolower($this->document_name);
            $tid = M('Tags')->alias('a')->join('__TAGS_MAP__ b ON a.id = b.tid')->where($data)->getField('did', true); //获取符合标签的id

        }
        if (isset($_GET['producttag'])) {
            unset($data);
            $data['a.title'] = array('like', '%' . I('producttag') . '%');
            $data['b.type'] = strtolower($this->document_name);
            $ptid = M('ProductTags')->alias('a')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.tid')->where($data)->getField('did', true); //获取符合产品标签的id
        }
        $flag = 0; //搜索标识位
        //算交集
        if (isset($_GET['system']) && $this->document_name == "Down") {
            $map_id = $sid;
            $flag = 1;
        }

        if (isset($_GET['download_address']) && $this->document_name == "Down") {
            $map_id = !isset($map_id) ? $aid : array_intersect($map_id, $aid);
            $flag = 1;
        }

        if (isset($_GET['tagname'])) {
            $map_id = !isset($map_id) ? $tid : array_intersect($map_id, $tid);
            $flag = 1;
        }
        if (isset($_GET['producttag'])) {
            $map_id = !isset($map_id) ? $ptid : array_intersect($map_id, $ptid);
            $flag = 1;
        }
        //交集为空，就查询所有的
        if (!empty($map_id)) {
            $map['id'] = array('in', $map_id);
        } elseif (empty($map_id) && $flag == "1") {
            $map['id'] = array('eq', 0);
        }
        // 构建列表数据
        $Document = M($this->document_name);
        if ($cate_id) {
            $_tmp_tree = D($this->cate_name)->getTree($cate_id);
            $cate_ids = array_column($_tmp_tree['_'], 'id');
            $cate_ids[] = $cate_id;
            $map['category_id'] = array('in', array_filter($cate_ids));

            //$map['category_id'] =   $cate_id;
        }
        $map['pid'] = I('pid', 0);
        if ($map['pid']) {
            // 子文档列表忽略分类
            unset($map['category_id']);
        }
        if (!is_null($model_id)) {
            $map['model_id'] = $model_id;
            if (is_array($field) && array_diff($Document->getDbFields(), $field)) {
                $modelName = M('Model')->getFieldById($model_id, 'name');
                //读取列表为什么要把详情表读出来？ 注释为解决主表有数据详情表没有数据的情况下，保证列表可以读出数据来
                //$Document->alias('DOCUMENT')->join('__'.strtoupper($this->document_name).'_'.strtoupper($modelName).'__ '.$modelName.' ON DOCUMENT.id='.$modelName.'.id');
                $key = array_search('id', $field);
                if (false !== $key) {
                    unset($field[$key]);
                    $field[] = 'id';
                }
                if (!empty($map_id)) {
                    unset($map['id']);
                    $map['id'] = array('in', $map_id);
                }
            }
        }
        if (!is_null($position)) {
            $map[] = "position & {$position} = {$position}";
        }

        if (!empty($addtion_map)) {
            $map = array_merge($map, $addtion_map);
        }

        $list = $this->lists($Document, $map, 'id DESC, update_time DESC', $field);

        if ($map['pid']) {
            // 获取上级文档
            $article = $Document->field('id,title,type')->find($map['pid']);
            $this->assign('article', $article);
        }
        //检查该分类是否允许发布内容
        $allow_publish = get_category_by_model($cate_id, 'allow_publish', $this->cate_name);

        $this->assign('status', $status);
        $this->assign('allow', $allow_publish);
        $this->assign('pid', $map['pid']);

        $this->meta_title = '文档列表';
        return $list;
    }

    /**
     * 设置一条或者多条数据的状态
     * @author huajie <banhuajie@163.com>
     */
    public function setStatus()
    {
        return parent::setStatus($this->document_name);
    }

    /**
     * 文档新增页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function add()
    {
        //获取左边菜单
        $this->getMenu();

        $cate_id = I('get.cate_id', 0);
        $model_id = I('get.model_id', 0);

        empty($cate_id) && $this->error('参数不能为空！');
        empty($model_id) && $this->error('该分类未绑定模型！');

        //检查该分类是否允许发布
        $allow_publish = check_category($cate_id, $this->cate_name);
        !$allow_publish && $this->error('该分类不允许发布内容！');

        // 获取当前的模型信息
        $model = get_model_by('id', $model_id);

        //处理结果
        $info['pid'] = $_GET['pid'] ? $_GET['pid'] : 0;
        $info['model_id'] = $model_id;
        $info['category_id'] = $cate_id;
        $info['category_rootid'] = get_category_by_model($cate_id, 'rootid', $this->cate_name);
        if ($info['pid']) {
            // 获取上级文档
            $article = M($this->document_name)->field('id,title,type')->find($info['pid']);
            $this->assign('article', $article);

            //作者:肖书成
            //描述:增加了子数据的功能（修改于2015/09/17）
            // 获取详细数据
            $data = D($this->document_name)->detail($info['pid']);

            if ($data) {
                $data['pid'] = $info['pid'];
                unset($data['uid']);
                unset($data['id']);
                unset($data['create_time']);
                unset($data['update_time']);
                unset($data['position']);
                unset($data['link']);
                unset($data['deadline']);
                unset($data['attach']);
                unset($data['view']);
                unset($data['comment']);
                unset($data['abet']);
                unset($data['argue']);
                unset($data['hits_month']);
                unset($data['hits_week']);
                unset($data['hits_today']);
                unset($data['date_month']);
                unset($data['date_week']);
                unset($data['date_today']);
                unset($data['home_position']);
                unset($data['edit_id']);
                $this->assign('data', $data);
            }

            //标签挂载处理,U方法构造ajax地址
            $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $model['tags_category'] . '&did=' . $info['pid'] . '&maptype=' . $this->document_name);
            $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $model['product_tags_category'] . '&did=' . $info['pid'] . '&maptype=' . $this->document_name);
            $this->assign('ifEdit', 'true');
        } else {
            //标签挂载处理,U方法构造ajax地址
            $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $model['tags_category']);
            $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $model['product_tags_category']);
            $this->assign('ifEdit', 'false');
        }

        //是否启用副分类
        if (C('SUB_CATEGORY') == '1') {
            $tags_url['Category'] = U('api/getTagsJson?type=' . $this->cate_name . '&category=0');
            $this->assign('cateTable', $this->cate_name);
        }

        //标签赋值
        $this->assign('tags_url', $tags_url);

        //hook
        $this->hook(__FUNCTION__);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('info', $info);
        //默认状态1，正常
        $data['status'] = 1;
        $this->assign('data', $data);

        $this->assign('fields', $fields);
        $this->assign('type_list', get_type_bycate($cate_id, $this->cate_name));
        $this->assign('model', $model);
        $this->meta_title = '新增' . $model['title'];
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 文档编辑页面初始化
     * @author huajie <banhuajie@163.com>
     */
    public function edit()
    {
        //获取左边菜单
        $this->getMenu();

        $id = I('get.id', '');
        if (empty($id)) {
            $this->error('参数不能为空！');
        }

        // 获取详细数据
        $Document = D($this->document_name);
        $data = $Document->detail($id);

        ###
        //加入安粉丝数据采集更新开关,如果存在说明是采集对比更新
        if(I('afs_update') == 1){
            $page = I('page') ? I('page') : 1;
            $new_id = I('new_id');
            $new_arr = R('AfsUpdate/id2arr',array('id'=>$new_id,'page'=>$page));
            //进行要更新替换的选项

            $data['smallimg'] = R('AfsUpdate/pic',array('v'=>$new_arr['iconUrl']));
            $data['size'] = sprintf('%.2f',($new_arr['apkSize'] / 1024 /1024)).'MB';
            $data['bag']  = $new_arr['packageName'];     //替换包名
            $data['update_info'] = $new_arr['updateInfo'];  //更新信息
            $data['version']  = $new_arr['versionName'];
            $data['update_time'] = strtotime($new_arr['updateTime']);

            $this->hook(__FUNCTION__, $id ,$new_arr);
        }else{
            //hook
            $this->hook(__FUNCTION__, $id ); //down_address
        }
        ###

        if (!$data) {
            $this->error($Document->getError());
        }

        if ($data['pid']) {
            // 获取上级文档
            $article = $Document->field('id,title,type')->find($data['pid']);
            $this->assign('article', $article);
        }

        // 获取当前的模型信息
        $model = get_model_by('id', $data['model_id']);

        $this->assign('data', $data);
        $this->assign('model_id', $data['model_id']);
        $this->assign('model', $model);

        //获取表单字段排序
        $fields = get_model_attribute($model['id']);
        $this->assign('fields', $fields);

        //获取当前分类的文档类型
        $this->assign('type_list', get_type_bycate($data['category_id'], $this->cate_name));

        //是否启用副分类
        if (C('SUB_CATEGORY') == '1') {
            $tags_url['Category'] = U('api/getTagsJson?type=' . $this->cate_name . '&category=0&did=' . $id . '&$maptype=' . $this->document_name);
            $this->assign('cateTable', $this->cate_name);
        }

        //标签挂载处理,U方法构造ajax地址
        $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $model['tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $model['product_tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $this->assign('tags_url', $tags_url);
        $this->assign('ifEdit', 'true');


        $this->meta_title = '编辑文档';

        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 更新一条数据
     * @author huajie <banhuajie@163.com>
     */
    public function update()
    {
        $document = D($this->document_name);
        $res = $document->update();
        if (!$res) {
            $this->error($document->getError());
        } else {
            //提交成功，清除采集session
            session('SESSION_REDIRECTURL', null);
            session('SESSION_SITEID', null);
            // 返回
            $out['info'] = $res['op'] == 'add' ? '新增成功' : '更新成功';
            $out['status'] = 1;
            $out['url'] = Cookie('__forward__');
            header('Content-Type:application/json; charset=utf-8');
            echo (json_encode($out));

            /*---
            数据添加完毕直接响应客户端，后续后台运行;下面逻辑均为后台逻辑
            liuliu 2015-12-17
             */
            function_exists('fastcgi_finish_request') && fastcgi_finish_request();
            $document->my_update_after($res['data'], $res['op']);
        }
    }

    /**
     * 静态生成一条数据
     * @author crohn <lllliuliu@163.com>
     */
    public function create()
    {
        $ids = I('ids');
        $id = I('id');
        $document = D($this->document_name);
        if (is_numeric($id) && empty($ids)) {
            // 检查状态 crohn 2015-6-16
            $status = $document->where('id=' . $id)->getField('status');
            if ($status != 1) {
                $this->error('数据状态正常（已审核）才能生成生成！');
            } else {
                //PC版
                $res = $document->createStaticDetail($id);
                //手机版
                $resM = $document->createStaticDetailM($id);
                if (!$res) {
                    $this->error('PC版生成失败！');
                } elseif (!$resM) {
                    $this->error('手机版生成失败！');
                } else {
                    $this->success('生成成功！', Cookie('__forward__'));
                }
            }
        } elseif (is_array($ids)) {
            //批量
            $rs = '';
            foreach ($ids as $id) {
                // 检查状态 crohn 2015-6-16
                $status = $document->where('id=' . $id)->getField('status');
                if ($status != 1) {
                    $rs .= $id . '-数据状态正常（已审核）才能生成生成！' . PHP_EOL;
                } else {
                    //PC版
                    $res = $document->createStaticDetail($id);
                    if (!$res) {
                        $rs .= $id . '-PC版生成失败！' . PHP_EOL;
                    }

                    //手机版
                    $resM = $document->createStaticDetailM($id);
                    if (!$resM) {
                        $rs .= $id . '-手机版版生成失败！' . PHP_EOL;
                    }

                }
            }
            //结果
            if (empty($rs)) {
                $this->success('生成成功！', Cookie('__forward__'));
            } else {
                $this->success($rs, Cookie('__forward__'));
            }

        }
    }

    /**
     * 跳转到生成地址
     * @author crohn <lllliuliu@163.com>
     */
    public function redirectUrl()
    {
        $id = intval(I('id'));
        $url = staticUrl('detail', $id, $this->document_name);
        redirect($url);
    }

    public function redirectView()
    {
        if (I('id')) {
            $id = I('id');
            //实例化静态生成类
            $obj['model'] = $this->document_name; //不需要指定数据模型
            $obj['module'] = $this->document_name;
            $obj['category'] = $this->cate_name; //不需要指定分类模型
            $obj['static_url'] = C('STATIC_CREATE_URL');
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($obj);
            $params['id'] = $id;
            $url = $staticInstance->getViewUrl('Detail', 'index', $params);
            redirect($url);
        }
    }

    /**
     * 待审核列表
     */
    public function examine()
    {
        //获取左边菜单
        $this->getMenu();

        $map['status'] = 2;
        if (!IS_ROOT) {
            $cate_auth = AuthGroupModel::getAuthCategories(UID);
            if ($cate_auth) {
                $map['category_id'] = array('IN', $cate_auth);
            } else {
                $map['category_id'] = -1;
            }
        }
        $list = $this->lists(M($this->document_name), $map, 'update_time desc');
        //处理列表数据
        if (is_array($list)) {
            foreach ($list as $k => &$v) {
                $v['username'] = get_nickname($v['uid']);
            }
        }

        $this->assign('list', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->meta_title = '待审核';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 回收站列表
     * @author huajie <banhuajie@163.com>
     */
    public function recycle()
    {
        //获取左边菜单
        $this->getMenu();

        $map['status'] = -1;
        //分类授权完成以后这里需要检测分类授权
        // if ( !IS_ROOT ) {
        //     $cate_auth  =   AuthGroupModel::getAuthCategories(UID);
        //     if($cate_auth){
        //         $map['category_id']    =   array('IN',$cate_auth);
        //     }else{
        //         $map['category_id']    =   -1;
        //     }
        // }
        $list = $this->lists(M($this->document_name), $map, 'update_time desc');

        //处理列表数据
        if (is_array($list)) {
            foreach ($list as $k => &$v) {
                $v['username'] = get_nickname($v['uid']);
            }
        }

        $this->assign('list', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->meta_title = '回收站';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 写文章时自动保存至草稿箱
     * @author huajie <banhuajie@163.com>
     */
    public function autoSave()
    {
        $res = D($this->document_name)->autoSave();
        if ($res !== false) {
            $return['data'] = $res;
            $return['info'] = '保存草稿成功';
            $return['status'] = 1;
            $this->ajaxReturn($return);
        } else {
            $this->error('保存草稿失败：' . D($this->document_name)->getError());
        }
    }

    /**
     * 草稿箱
     * @author huajie <banhuajie@163.com>
     */
    public function draftBox()
    {
        //获取左边菜单
        $this->getMenu();

        $Document = D($this->document_name);
        $map = array('status' => 3, 'uid' => UID);
        $list = $this->lists($Document, $map);
        //获取状态文字
        //int_to_string($list);

        $this->assign('list', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->meta_title = '草稿箱';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 我的文档
     * @author huajie <banhuajie@163.com>
     */
    public function mydocument($status = null, $title = null)
    {
        //获取左边菜单
        $this->getMenu();

        $Document = D($this->document_name);
        /* 查询条件初始化 */
        $map['uid'] = UID;
        if (isset($title)) {
            $map['title'] = array('like', '%' . $title . '%');
        }
        if (isset($status)) {
            $map['status'] = $status;
        } else {
            $map['status'] = array('in', '0,1,2');
        }
        if (isset($_GET['time_start'])) {
            $map['update_time'][] = array('egt', strtotime(I('time_start')));
        }
        if (isset($_GET['time_end'])) {
            $map['update_time'][] = array('elt', 24 * 60 * 60 + strtotime(I('time_end')));
        }

        //只查询pid为0的文章
        $map['pid'] = 0;
        $list = $this->lists($Document, $map, 'update_time desc');
        $list = parseDocumentList($list, 1);

        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->assign('status', $status);
        $this->assign('list', $list);
        $this->assign('cate_name', $this->cate_name);
        $this->meta_title = '我的文档';
        $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
    }

    /**
     * 还原被删除的数据
     * @author huajie <banhuajie@163.com>
     */
    public function permit()
    {
        /*参数过滤*/
        $ids = I('param.ids');
        if (empty($ids)) {
            $this->error('请选择要操作的数据');
        }

        /*拼接参数并修改状态*/
        $Model = $this->document_name;
        $map = array();
        if (is_array($ids)) {
            $map['id'] = array('in', $ids);
        } elseif (is_numeric($ids)) {
            $map['id'] = $ids;
        }
        $this->restore($Model, $map);
    }

    /**
     * 清空回收站
     * @author huajie <banhuajie@163.com>
     */
    public function clear()
    {
        //判断删除权限
        $delDocumentName = array('Document', 'Down');
        if (in_array(CONTROLLER_NAME, $delDocumentName)) {
            if (session('user_auth.uid') != '1') {
                $this->error("只有超级管理员才能删除", Cookie('__forward__'));
                exit;
            }
        }
        $res = D($this->document_name)->remove();
        if ($res !== false) {
            $this->success('清空回收站成功！');
        } else {
            $this->error('清空回收站失败！');
        }
    }

    /**
     * 移动文档
     * @author huajie <banhuajie@163.com>
     */
    public function move()
    {
        if (empty($_POST['ids'])) {
            $this->error('请选择要移动的文档！');
        }

        //获取分类

        session('moveArticle', $_POST['ids']);
        session('copyArticle', null);
        $this->success('请选择要移动到的分类！');
    }

    /**
     * 拷贝文档
     * @author huajie <banhuajie@163.com>
     */
    public function copy()
    {
        if (empty($_POST['ids'])) {
            $this->error('请选择要复制的文档！');
        }
        session('copyArticle', $_POST['ids']);
        session('moveArticle', null);
        $this->success('请选择要复制到的分类！');
    }

    /**
     * 粘贴文档
     * @author huajie <banhuajie@163.com>
     */
    public function paste()
    {
        $moveList = session('moveArticle');
        $copyList = session('copyArticle');
        if (empty($moveList) && empty($copyList)) {
            $this->error('没有选择文档！');
        }
        if (!isset($_POST['cate_id'])) {
            $this->error('请选择要粘贴到的分类！');
        }
        $cate_id = I('post.cate_id'); //当前分类
        $pid = I('post.pid', 0); //当前父类数据id

        //检查所选择的数据是否符合粘贴要求
        $check = $this->checkPaste(empty($moveList) ? $copyList : $moveList, $cate_id, $pid);
        if (!$check['status']) {
            $this->error($check['info']);
        }

        if (!empty($moveList)) {
// 移动    TODO:检查name重复
            foreach ($moveList as $key => $value) {
                $Model = M($this->document_name);
                $map['id'] = $value;
                $data['category_id'] = $cate_id;
                $data['category_rootid'] = get_category_by_model($cate_id, 'rootid', $this->cate_name);
                $data['pid'] = $pid;
                //获取root
                if ($pid == 0) {
                    $data['root'] = 0;
                } else {
                    $p_root = $Model->getFieldById($pid, 'root');
                    $data['root'] = $p_root == 0 ? $pid : $p_root;
                }
                $res = $Model->where($map)->save($data);
            }
            session('moveArticle', null);
            if (false !== $res) {
                $this->success('文档移动成功！');
            } else {
                $this->error('文档移动失败！');
            }
        } elseif (!empty($copyList)) {
            // 复制
            foreach ($copyList as $key => $value) {
                $Model = M($this->document_name);
                $data = $Model->find($value);
                unset($data['id']);
                unset($data['name']);
                $data['category_id'] = $cate_id;
                $data['category_rootid'] = get_category_by_model($cate_id, 'rootid', $this->cate_name);
                $data['pid'] = $pid;
                $data['create_time'] = NOW_TIME;
                $data['update_time'] = NOW_TIME;
                //获取root
                if ($pid == 0) {
                    $data['root'] = 0;
                } else {
                    $p_root = $Model->getFieldById($pid, 'root');
                    $data['root'] = $p_root == 0 ? $pid : $p_root;
                }

                $result = $Model->add($data);
                if ($result) {
                    $model_name = get_model_by('id', $data['model_id'], 'name');
                    $logic = D($model_name['name'], 'Logic');
                    $data = $logic->detail($value); //获取指定ID的扩展数据
                    $data['id'] = $result;
                    $res = $logic->add($data);
                }
            }
            session('copyArticle', null);
            if ($res) {
                $this->success('文档复制成功！');
            } else {
                $this->error('文档复制失败！');
            }
        }
    }

    /**
     * 检查数据是否符合粘贴的要求
     * @author huajie <banhuajie@163.com>
     */
    protected function checkPaste($list, $cate_id, $pid)
    {
        $return = array('status' => 1);
        $Document = D($this->document_name);

        // 检查支持的文档模型
        $modelList = M($this->cate_name)->getFieldById($cate_id, 'model'); // 当前分类支持的文档模型
        foreach ($list as $key => $value) {
            //不能将自己粘贴为自己的子内容
            if ($value == $pid) {
                $return['status'] = 0;
                $return['info'] = '不能将编号为 ' . $value . ' 的数据粘贴为他的子内容！';
                return $return;
            }
            // 移动文档的所属文档模型
            $modelType = $Document->getFieldById($value, 'model_id');
            if (!in_array($modelType, explode(',', $modelList))) {
                $return['status'] = 0;
                $return['info'] = '当前分类的文档模型不支持编号为 ' . $value . ' 的数据！';
                return $return;
            }
        }

        // 检查支持的文档类型和层级规则
        $typeList = M($this->cate_name)->getFieldById($cate_id, 'type'); // 当前分类支持的文档模型
        foreach ($list as $key => $value) {
            // 移动文档的所属文档模型
            $modelType = $Document->getFieldById($value, 'type');
            if (!in_array($modelType, explode(',', $typeList))) {
                $return['status'] = 0;
                $return['info'] = '当前分类的文档类型不支持编号为 ' . $value . ' 的数据！';
                return $return;
            }
            $res = $Document->checkDocumentType($modelType, $pid);
            if (!$res['status']) {
                $return['status'] = 0;
                $return['info'] = $res['info'] . '。错误数据编号：' . $value;
                return $return;
            }
        }

        return $return;
    }

    /**
     * 文档排序
     * @author huajie <banhuajie@163.com>
     */
    public function sort()
    {
        if (IS_GET) {
            //获取左边菜单
            $this->getMenu();

            $ids = I('get.ids');
            $cate_id = I('get.cate_id');
            $pid = I('get.pid');

            //获取排序的数据
            $map['status'] = array('gt', -1);
            if (!empty($ids)) {
                $map['id'] = array('in', $ids);
            } else {
                if ($cate_id !== '') {
                    $map['category_id'] = $cate_id;
                }
                if ($pid !== '') {
                    $map['pid'] = $pid;
                }
            }
            $list = M($this->document_name)->where($map)->field('id,title')->order('level DESC,id DESC')->select();

            $this->assign('list', $list);
            $this->meta_title = '文档排序';
            $this->display($this->view_select[strtolower(ACTION_NAME)] ? $this->view_select[strtolower(ACTION_NAME)] : $this->base_view . ':' . strtolower(ACTION_NAME));
        } elseif (IS_POST) {
            $ids = I('post.ids');
            $ids = array_reverse(explode(',', $ids));
            foreach ($ids as $key => $value) {
                $res = M($this->document_name)->where(array('id' => $value))->setField('level', $key + 1);
            }
            if ($res !== false) {
                $this->success('排序成功！');
            } else {
                $this->error('排序失败！');
            }
        } else {
            $this->error('非法请求！');
        }
    }

    /**
     * HOOK方法，加载不同的特殊逻辑，子类覆盖
     * @param string $method 方法名
     * @param array $params 参数
     * @return void
     * @author crohn <lllliuliu@163.com>
     */
    protected function hook($method, $params = array())
    {

    }
}
