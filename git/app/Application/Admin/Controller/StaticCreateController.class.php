<?php
// +----------------------------------------------------------------------
// | 静态文件生成管理（目前命令行生成，只是预留的接口，shell那边还没实现，要想使用命令行生成需要带上cmd=1的参数）
// +----------------------------------------------------------------------
// | date 2014-10-22
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

/*
创建onethink_static_page表

CREATE TABLE onethink_static_page (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
`type` int(10) unsigned NOT NULL  DEFAULT '0'  COMMENT '类型，0-PC版,1-手机版',
`group` varchar(50) NOT NULL COMMENT '分组，用于显示',
`name` varchar(50) NOT NULL COMMENT '页面名字',
`multipage` varchar(50) NOT NULL COMMENT '多页生成的页码参数，不填为单页',
`multipage_index` varchar(50) NOT NULL COMMENT '多页的第一页名',
`module_name` varchar(50) NOT NULL COMMENT '生成访问模块名',
`controller_name` varchar(50) NOT NULL COMMENT '生成访问控制层名',
`method_name` varchar(50) NOT NULL COMMENT '生成访问方法名',
`params` text NOT NULL COMMENT '生成访问参数',
`path` varchar(500) NOT NULL COMMENT '生成路径',
`keywords` varchar(500) NOT NULL COMMENT '关键词',
`title` varchar(500) NOT NULL COMMENT 'titile',
`description` text NOT NULL COMMENT '厂商详细说明 描述',
`create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
`update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='静态页面管理表'
 */
namespace Admin\Controller;

use Think\Think;

class StaticCreateController extends AdminController
{

    private $log_name = 'static_page';
    private $subjectArray = array('document' => '文章', 'down' => '下载', 'package' => '礼包');

    /**
     * 构造函数，处理动态边栏
     *
     * @return void
     */
    public function _initialize()
    {
        if (!IS_CLI) {
            parent::_initialize();
        } else {
            /* 读取数据库中的配置 */
            $config = S('DB_CONFIG_DATA');
            if (!$config) {
                $config = api('Config/lists');
                S('DB_CONFIG_DATA', $config);
            }
            C($config); //添加配置
        }
        //设置允许内存
        ini_set("memory_limit", "512M");
        //设置超时时间
        ini_set("max_execution_time", "0");
    }

    /**
     * 描述：获取模块名称
     *
     * @param string $url
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getMenuTitle($url = 'Document/index')
    {
        if (!empty($url)) {
            $where = array();
            $where['url'] = array('eq', $url);
            $rs = M('Menu')->field('url,title')->where($where)->find();
            return $rs['title'];
        }
        return false;
    }

    /**
     * 描述：获取常用四大模块名称
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Time:
     * Modify Author:
     */
    public function getGeneralTitle()
    {
        $document_name = $this->getMenuTitle('Document/index');
        $document_name = $document_name ? $document_name : '文章模块';
        $this->assign('document_name', $document_name);
        $down_name = $this->getMenuTitle('Down/index');
        $down_name = $down_name ? $down_name : '下载模块';
        $this->assign('down_name', $down_name);
        $package_name = $this->getMenuTitle('Package/index');
        $package_name = $package_name ? $package_name : '礼包模块';
        $this->assign('package_name', $package_name);
        $gallery_name = $this->getMenuTitle('Gallery/index');
        $gallery_name = $gallery_name ? $gallery_name : '图库模块';
        $this->assign('gallery_name', $gallery_name);

    }

    /**
     * 首页
     *
     * @return void
     */
    public function create()
    {
        $this->getGeneralTitle();
        $this->display();
    }

    /**
     * 描述：官方网生成首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function gfwcreate()
    {
        $this->display();
    }

    /**
     * 描述：7230手机版生成首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function p7230mobile()
    {
        $this->display();
    }

    /**
     * 描述：游戏后台生成首页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function cmdrun()
    {
        $this->getGeneralTitle();
        $this->display();
    }

    /**
     * 按栏目
     */
    public function generator()
    {
        $subjectArray = $this->subjectArray;
        $category_data = D(D(key($subjectArray))->cate_name)->getTree(0, 'id, title');

        $this->assign('category_data', $category_data);
        $this->assign('subject', $subjectArray);
        $this->display();
    }

    /**
     * 整站生成
     */
    public function allGenerator()
    {
        $index_method = 'createStaticIndex'; //列表首页
        $list_method = 'createStaticLists'; //列表页
        $list_methodM = 'createStaticListsM'; //列表页
        $detail_method = 'createStaticDetail'; //内容页
        $detail_methodM = 'createStaticDetailM'; //内容页

        //$step 1网站首页 2模块首页 3列表页 4详情页
        $param = array(
            'step' => (int) I('step'),
            'page_number' => (int) I('page_number', 0),
            'last_updated_time' => trim(I('last_updated_time')),
            'module_index' => trim(I('module_index')),
        );

        if (($up_timestamp = strtotime($param['last_updated_time'])) === false) {
            $this->error('日期错误');
        }

        $where = "update_time > {$up_timestamp}";

        //网站首页生成
        if (1 == $param['step']) {
            $params = array(
                'model' => '',
                'module' => 'Home',
                'category' => '',
                'static_url' => C('STATIC_CREATE_URL'),
            );
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($params);
            $rs = $staticInstance->moduleIndex('Index', 'index');
            $message = '首页生成' . ($rs ? '成功！' : '失败！');

            $param['step'] = 2;
            $url = 'StaticCreate/allGenerator&' . http_build_query($param);
            $this->success($message, U($url, '', false));
            return;
        }

        //模块首页
        if (2 == $param['step']) {
            $str = '';
            foreach ($this->subjectArray as $key => $val) {
                $model = ucfirst($key);
                $rs = D($model)->$index_method();
                $str .= $val . ' -- 模块首页生成' . ($rs ? '成功' : '失败') . '<br/>';
            }

            $param['step'] = 3;
            $url = 'StaticCreate/allGenerator&' . http_build_query($param);
            $this->success($str, U($url, '', false));
            return;
        }
        //第一次进来没有模型, 默认提取第一个  删除已经生成完成的模型
        $model = empty($param['module_index']) ? key($this->subjectArray) : $param['module_index'];
        $new_subject = array();
        $switch = false;
        foreach ($this->subjectArray as $key => $val) {
            $model == $key && $switch = true;
            $switch && $new_subject[$key] = $val;
        }

        //列表页  每页码来生成 单次只生成一个分类
        if (3 == $param['step']) {
            $cate_ids = D(D($model)->cate_name)->where("update_time > {$up_timestamp}")->getTree(
                0,
                'id, title'
            ); //列表所有分类

            //当前模型所有的分类是否生成完成，生成完成后前往下一个模型或前往详情页的生成
            if (!isset($cate_ids[$param['page_number']])) {
                next($new_subject);
                $model = key($new_subject);
                $param['page_number'] = 0;
                if (null == $model) {
                    $param['step'] = 4;
                    $param['module_index'] = key($this->subjectArray);
                } else {
                    $param['step'] = 3;
                    $param['module_index'] = $model;
                }

                $url = 'StaticCreate/allGenerator&' . http_build_query($param);
                $this->success('列表页全部生成完成', U($url, '', false));
                return;
            }

            $rs = D($model)->$list_method($cate_ids[$param['page_number']]['id']);
            $m_rs = D($model)->$list_methodM($cate_ids[$param['page_number']]['id']);
            $message = '列表页 --' . $cate_ids[$param['page_number']]['title'] . ($rs ? ' --生成成功!' : ' --生成失败!') . ' -- 手机端生成' . ($m_rs ? '成功' : '失败');

            $param['page_number'] += 1;
            $url = 'StaticCreate/allGenerator&' . http_build_query($param);

            $this->success($message, U($url, '', false));
            return;
        }

        //详情页
        if (4 == $param['step']) {
            //计算分页
            $max_number = 10;
            $start = $param['page_number'] * $max_number;
            $data = D($model)->where($where)->field('id,title')->order('update_time desc')->limit(
                $start,
                $max_number
            )->select();

            //判断当前模型是否生成完成，生成完成后往下一个模型或结束生成
            if (empty($data)) {
                next($new_subject);
                $model = key($new_subject);

                if (null == $model) {
                    $this->success('全部生成完成', '', false, false);
                    return;
                }

                $param['step'] = 4;
                $param['page_number'] = 0;
                $param['module_index'] = $model;
                $url = 'StaticCreate/allGenerator&' . http_build_query($param);
                $this->success('', U($url, '', false));
                return;
            }

            $str = '';
            foreach ($data as $key => $val) {
                //开始生成
                $rs = D($model)->$detail_method($val['id']);
                $m_rs = D($model)->$detail_methodM($val['id']);
                $str .= '文章 ' . $val['title'] . ' 生成' . ($rs ? '成功' : '失败') . ' -- 手机端生成' . ($m_rs ? '成功' : '失败') . '<br/>';
            }

            $param['page_number'] += 1; //自动翻页
            $url = 'StaticCreate/allGenerator&' . http_build_query($param);
            $this->success($str, U($url, '', false));
        }
    }

    /**
     * 栏目生成
     */
    public function subjectGenerator()
    {
        $param = array(
            'step' => (int) I('step'), //1：首页 2：列表页 3：内容页
            'subject' => trim(I('subject')),
            'page_number' => (int) I('page_number', 0),
            'category_id' => (int) I('category_id', 0),
            'last_updated_num' => (int) I('last_updated_num', 0),
        );

        if (!array_key_exists($param['subject'], $this->subjectArray)) {
            $this->error('未知操作');
        }

        //更新首页 列表页 详情页
        $model = ucfirst($param['subject']);
        $index_method = 'createStaticIndex';
        $list_method = 'createStaticLists';
        $list_methodM = 'createStaticListsM';

        //更新首页
        if (1 == $param['step']) {
            $rs = D($model)->$index_method();
            $message = '列表首页生成' . ($rs ? '成功!' : '失败!');
            $param['step'] = 2;

            $url = 'StaticCreate/subjectGenerator&' . http_build_query($param);
            $this->success($message, U($url, '', false));
        }

        //更新列表页
        if (2 == $param['step']) {
            //是否指定某个分类生成
            if ($param['category_id']) {
                $rs = D($model)->$list_method($param['category_id']);
                $m_rs = D($model)->$list_methodM($param['category_id']); //生成手机端

                $message = '列表页 --' . ($rs ? ' 生成成功!' : ' 生成失败!') . ' -- 手机端生成' . ($m_rs ? '成功' : '失败');

                $this->success($message, '', false, false);
                return;
            }

            $cate_ids = D(D($model)->cate_name)->getTree(0, 'id, title'); //列表所有分类
            if (!isset($cate_ids[$param['page_number']])) {
                $this->success('全部生成完成', '', false, false);
                return;
            }

            //判断是否已达到指定的生成条数  如果最近更新条数为零，则全部生成
            if (0 !== $param['last_updated_num'] && $param['page_number'] > ($param['last_updated_num'] - 1)) {
                $this->success('<br/>已全部更新完成!', '', false, false);
                return;
            }

            $rs = D($model)->$list_method($cate_ids[$param['page_number']]['id']);
            $m_rs = D($model)->$list_methodM($param['category_id']); //生成手机端
            $message = '列表页 --' . $cate_ids[$param['page_number']]['title'] . ($rs ? ' --生成成功!' : ' --生成失败!') . ' -- 手机端生成' . ($m_rs ? '成功' : '失败');

            $param['page_number'] += 1;
            $url = 'StaticCreate/subjectGenerator&' . http_build_query($param);
            $this->success($message, U($url, '', false));
        }
    }

    /**
     * 按内容生成
     */
    public function contentGenerator()
    {
        $max_number = 10; //单次生成总数量

        $param = array(
            'last_updated_num' => (int) I('last_updated_num', 0),
            'subject' => trim(I('subject')),
            'page_number' => (int) I('page_number', 0),
            'category_id' => (int) I('category_id', 0),
            'start_id' => (int) I('start_id', 0),
            'end_id' => (int) I('end_id', 0),
            'start_time' => trim(I('start_time')),
        );

        if (!array_key_exists($param['subject'], $this->subjectArray)) {
            $this->error('未知操作');
        }

        $up_timestamp = strtotime($param['start_time']) ? strtotime($param['start_time']) : 0;

        //详情页
        $model = ucfirst($param['subject']);
        $detail_method = 'createStaticDetail';
        $detail_methodM = 'createStaticDetailM';

        $start = $param['page_number'] * $max_number;

        //拼接查询条件
        $where = ' status =1 ';
        $param['end_id'] && $where .= " and id < {$param['end_id']} ";
        $param['start_id'] && $where .= " and id > {$param['start_id']} ";
        $up_timestamp && $where .= " and update_time > {$up_timestamp}";
        $param['category_id'] && $where .= " and category_id = {$param['category_id']} ";

        $data = D($model)->where($where)->field('id,title')->order('update_time desc')->limit(
            $start,
            $max_number
        )->select();
        if (empty($data)) {
            $this->success('已全部更新完成!', '', false, false);
            return;
        }

        $last_generator_number = (0 === $param['page_number']) ? 0 : (($param['page_number'] - 1) * $max_number); //上一次生成的总数量
        $str = '';
        foreach ($data as $key => $val) {
            //判断是否已达到指定的生成条数  如果最近更新条数为零，则全部生成
            $last_generator_number++;
            if (0 !== $param['last_updated_num'] && $last_generator_number > ($param['last_updated_num'] - 1)) {
                $this->success('<br/>已全部更新完成!', '', false, false);
                return;
            }

            //开始生成
            $rs = D($model)->$detail_method($val['id']);
            $m_rs = D($model)->$detail_methodM($val['id']); //生成手机端
            $str .= $val['title'] . ' 生成' . ($rs ? '成功' : '失败') . ' -- 手机端生成 ' . ($m_rs ? '成功' : '失败') . '<br/>';
        }

        $param['page_number'] += 1; //自动翻页
        $url = 'StaticCreate/contentGenerator&' . http_build_query($param);
        $this->success($str, U($url, '', false));
    }

    /**
     * 获取分类
     */
    public function get_category()
    {
        $subject = trim(I('subject'));

        if (!isset($this->subjectArray[$subject])) {
            $this->ajaxReturn(array(), '未知的操作', 0);
            return;
        }

        $category_data = D(D($subject)->cate_name)->getTree(0, 'id, title');

        $this->ajaxReturn(ajax_return_data_format($category_data), 'json');
    }

    /**
     * 生成全站首页或其他相关页
     *
     * @return void
     */
    public function home()
    {
        $method = I('method');
        !$method && $this->error('方法参数错误！');
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/method/' . $method);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $params['model'] = ''; //不需要指定数据模型
            $params['module'] = 'Home';
            $params['category'] = ''; //不需要指定分类模型
            $params['static_url'] = C('STATIC_CREATE_URL');
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($params);
            if ($method == 'index') {
                $rs = $staticInstance->moduleIndex('Index', 'index');
                $rs ? $this->success('刷新成功！') : $this->error('刷新失败！');
            }
        }

    }

    /**
     * 生成模块首页
     *
     * @return void
     */
    public function moduleIndex()
    {
        $model = I('model');
        !$model && $this->error('模型参数错误！');
        $method = I('method');
        !$method && $this->error('方法参数错误！');
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/model/' . $model . '/method/' . $method);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $rs = D($model)->$method();
            $rs ? $this->success('刷新成功！') : $this->error('刷新失败！');
        }
    }

    /**
     * 生成模块列表页
     *
     * @return void
     */
    public function moduleLists()
    {
        $model = I('model');
        !$model && $this->error('模型参数错误！');
        $method = I('method');
        !$method && $this->error('方法参数错误！');
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/model/' . $model . '/method/' . $method . '/max_page/10000000');
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $category_id = I('category_id');
            //最大生成页数
            $max_page = I('max_page');
            $max_page = empty($max_page) ? 30 : $max_page;
            $str = '';

            $level = I('level');
            if (empty($category_id) || !is_numeric($category_id)) {
                //为亲宝贝（一期）生成做限制，以后去掉
                if (C('THEME') == "qbaobei") {
                    $cate_where['template_lists'] = array(
                        array('eq', 'jiaoyu'),
                        array('eq', 'jiankang'),
                        array('eq', 'tuku'),
                        array('eq', 'yingyang'),
                        array('eq', 'baike'),
                        array('eq', 'food'),
                        array('eq', 'video'),
                        array('eq', 'chengzhang'),
                        array('eq', 'ganmao'),
                        array('eq', 'zuowen'),
                        'or',
                    );
                    $cate_arr = array(
                        'Document' => 'category',
                        'Down' => 'down_category',
                        'Package' => 'package_category',
                        'Feature' => 'feature_category',
                        'Gallery' => 'gallery_category',
                    );
                    $cate_model = $cate_arr[$model] ? $cate_arr[$model] : 'category';
                    $cate_rs = M($cate_model)->where($cate_where)->getField('id', true);
                    $map['id'] = array('in', $cate_rs);
                }
                if ($level == '2') {
                    $category = D(D($model)->cate_name);
                    $map['pid'] = array('eq', 0); //查询二级分类条件
                    $map['status'] = array('gt', 0);
                    $rs = $category->where($map)->getField('id', true); //获取所有顶级分类id
                    if (!empty($rs)) {
                        $map['pid'] = array('in', $rs); //查询二级分类条件
                        $list = $category->where($map)->getField('id', true);
                    }
                }
                $cids = D(D($model)->cate_name)->getTree(0, 'id,title');
                foreach ($cids as $cid) {
                    if ($level == '2' && !in_array($cid['id'], $list)) {
                        continue;
                    }
                    $rs = D($model)->$method($cid['id'], $max_page);
                    D($model)->createStaticListsM($category_id, $max_page); //加入手机版生成 modify by 谭坚 2016-2-2
                    if (!IS_CLI) {
                        $str .= $rs ? $cid['title'] . ' 刷新成功！</br>' : $cid['title'] . ' 刷新失败！</br>';
                    } else {
                        echo $rs ? /*$cid['title'] .' 刷新成功！' . PHP_EOL*/
                        '' : $cid['title'] . ' 刷新失败！' . PHP_EOL;
                    }
                }
                $this->success($str, '', 5);
            } elseif ($level == '1') {
                //生成某个父分类下的 子分类。
                //获取一个子分类下的所有子分类
                $list = $this->getChileCate($category_id, D($model)->cate_name);

                if (!empty($list)) {
                    foreach ($list as $k => $v) {
                        $rs = D($model)->$method($v['id'], $max_page);
                        D($model)->createStaticListsM($category_id, $max_page); //加入手机版生成 modify by 谭坚 2016-2-2
                        if (!IS_CLI) {
                            $str .= $rs ? $v['title'] . ' 刷新成功！</br>' : $v['title'] . ' 刷新失败！</br>';
                        } else {
                            echo $rs ? /*$cid['title'] .' 刷新成功！' . PHP_EOL*/
                            '' : $v['title'] . ' 刷新失败！' . PHP_EOL;
                        }
                    }
                }
                $this->success($str, '', 5);
            } else {
                $rs = D($model)->$method($category_id, $max_page);
                D($model)->createStaticListsM($category_id, $max_page); //加入手机版生成 modify by 谭坚 2016-2-2
                $rs ? $this->success('刷新成功！') : $this->error('刷新失败！');
            }
        }
    }

    /**
     * 作者:肖书成
     * 描述:获取一个分类下的所有子分类。
     * 时间:2015/9/2
     *
     * @param        $id
     * @param string $table
     * @param array  $lists
     * @return array
     */
    private function getChileCate($id, $table = 'Category', $lists = array())
    {
        //验证参数
        if (empty($id) && !is_array($id) && !is_numeric($id)) {
            return;
        }
        if (!is_array($lists)) {
            return false;
        }

        //执行SQL操作
        $where = 'status = 1 AND pid ';

        if (is_array($id)) {
            is_array($id[0]) ? $where .= 'IN(' . implode(
                ',',
                array_column($id, 'id')
            ) . ')' : $where .= 'IN(' . implode(',', $id) . ')';
        } elseif (is_numeric($id)) {
            $where .= '=' . $id;
        } else {
            return $lists;
        }

        $list = M($table)->field('id,title,pid')->where($where)->select();

        //结果处理 （递归）
        if (!empty($list)) {
            $lists = array_filter(array_merge($lists, $list));
            $lists = $this->getChileCate($list, $table, $lists);

        }

        //返回结果
        return $lists;
    }

    /**
     * 描述：生成标签（产品标签）列表页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function tagList()
    {
        $model = I('model');
        !$model && $this->error('模型参数错误！');
        $type = I('type') ? I('type') : 'document';
        $tag = I('tag');
        $is_mobile = I('mobile') ? true : false;
        //最大生成页数
        $max_page = I('max_page');
        $max_page = empty($max_page) ? 30 : $max_page;
        $str = '';
        //实例化静态生成类
        if ($is_mobile) {
            $params = array(
                'static_url' => C('STATIC_CREATE_URL'),
                'static_root_dir' => C('MOBILE_STATIC_ROOT'),
            );
        } else {
            $params = array(
                'static_url' => C('STATIC_CREATE_URL'),
                'static_root_dir' => C('STATIC_ROOT'),
            );
        }
        $class = 'Common\\Library\\TempCreateLibrary';
        $staticInstance = new $class($params);
        if (is_numeric($max_page)) {
            $staticInstance->setProperty('lists_maxpage', $max_page);
        }
        if ($is_mobile) {
            $staticInstance->setProperty('theme', C('MOBILE_THEME'));
        } else {
            $staticInstance->setProperty('theme', C('THEME'));
        }
        unset($params);
        $params['model'] = $type;
        if (empty($tag) || !is_numeric($tag)) {
            $tags = D($model)->field('id,title')->where('status=1')->select(); //获取标签id
            foreach ($tags as $tag_id) {
                $flag = $staticInstance->tagLists($tag_id['id'], 'Category', 'index', $params, $model);
                if ($flag) {
                    $str .= $flag ? $tag_id['title'] . ' 刷新成功！</br>' : $tag_id['title'] . ' 刷新失败！</br>';
                } else {
                    echo $flag ? '' : $tag_id['title'] . ' 刷新失败！' . PHP_EOL;
                }
            }
            $this->success($str, '', 5);
        } else {
            $rs = D($model)->field('id')->where('status=1 and id=' . $tag)->find(); //获取标签id
            if ($rs) {
                $flag = $staticInstance->tagLists($rs['id'], 'Category', 'index', $params, $model);
            } else {
                $flag = false;
            }
            $flag ? $this->success('刷新成功！') : $this->error('刷新失败！');
        }
    }

    /**
     * 生成模块详情页
     *
     * @return void
     */
    public function moduleDetail()
    {
        $model = I('model');
        !$model && $this->error('模型参数错误！');
        $method = I('method');
        !$method && $this->error('方法参数错误！');
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/model/' . $model . '/method/' . $method);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $category_id = I('category_id');

            $map['status'] = 1;
            if ($category_id && is_numeric($category_id)) {
                $map['category_id'] = $category_id;
            }
            //为亲宝贝（一期）生成做限制，以后去掉
            if (C('THEME') == "qbaobei") {
                $cate_where['template_lists'] = array(
                    array('eq', 'jiaoyu'),
                    array('eq', 'jiankang'),
                    array('eq', 'tuku'),
                    array('eq', 'yingyang'),
                    array('eq', 'baike'),
                    array('eq', 'video'),
                    array('eq', 'food'),
                    array('eq', 'chengzhang'),
                    array('eq', 'ganmao'),
                    array('eq', 'zuowen'),
                    'or',
                );
                $cate_arr = array(
                    'Document' => 'category',
                    'Down' => 'down_category',
                    'Package' => 'package_category',
                    'Feature' => 'feature_category',
                    'Gallery' => 'gallery_category',
                );
                $cate_model = $cate_arr[$model] ? $cate_arr[$model] : 'category';
                $cate_rs = M($cate_model)->where($cate_where)->getField('id', true);
                if ($category_id && is_numeric($category_id)) {
                    $cate_rs[] = $category_id;
                }
                $map['category_id'] = array('in', $cate_rs);
            }

            //大于
            $gt = I('gt');
            if (!empty($gt)) {
                is_numeric($gt) && $map['id'][] = array('egt', $gt);
            }

            //小于
            $lt = I('lt');
            if (!empty($lt)) {
                is_numeric($lt) && $map['id'][] = array('elt', $lt);
            }

            $order = strtolower(I('order'));
            !in_array($order, array('ASC', 'DESC')) && $order = 'DESC';

            $all = D($model)->where($map)->field('id,title')->order("id $order")->select();
            $allNum = count($all);

            foreach ($all as $k => $value) {
                $rs = D($model)->$method($value['id']);
                $resM = D($model)->createStaticDetailM($value['id']);
                if (!IS_CLI) {
                    $str .= $rs ? '' : $value['title'] . ' 刷新失败！</br>';
                } else {
                    echo $rs ? $value['id'] . '~' . $value['title'] . ' PC刷新成功！' . PHP_EOL : $value['id'] . '~' . $value['title'] . ' PC刷新失败！' . PHP_EOL;
                    echo $resM ? $value['id'] . '~' . $value['title'] . ' 手机刷新成功！' . PHP_EOL : $value['id'] . '~' . $value['title'] . ' 手机刷新失败！' . PHP_EOL;
                    echo '一共有' . $allNum . '条数据,已经生成了' . $k . '条数据,还剩下' . ($allNum - $k - 1) . '条数据未生成' . PHP_EOL;
                }
            }
            $this->success($str, '', 5);
        }
    }

    /**
     * 描述：生成省份分类（主要用于官方网）
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function provinceList()
    {
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            //实例化静态生成类
            $params = array(
                'model' => '',
                'module' => 'Package',
                'category' => '',
                'static_url' => C('STATIC_CREATE_URL'),
            );
            //$params['theme'] = C('THEME');
            //$params['method'] = 'websitePlace';
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($params);
            $place = C('PLACE_PROVINCE_STATIC_PATH');
            $place_list = C('PLACE_CATE_STATIC_PATH');
            $provice = M('Province')->field('id,pinyin')->select();
            // dump($provice);exit;
            $list = M('PackageCategory')->where('status > 0')->field('id')->select();
            // dump($list);exit;
            $method = 'index/theme/' . C('THEME') . '/method/websitePlace';
            foreach ($provice as $value) {
                $params['province'] = $value['id'];
                $path = str_replace("{pinyin}", $value['pinyin'], $place);
                $staticInstance->widget('Widget', $method, $path, $params, 'p', 'index'); //使用widget方法生成地方网站列表
                if (is_array($list)) {
                    foreach ($list as $val) {
                        $params['cate'] = $val['id'];
                        $path = str_replace(
                            "{pinyin}",
                            $value['pinyin'],
                            str_replace("{category_id}", $val['id'], $place_list)
                        );
                        $staticInstance->widget('Widget', $method, $path, $params, 'p', 'index'); //使用widget方法地方网站分类列表
                    }
                    unset($params['cate']);
                }
            }

        }
        $this->success('生成完成！', '', 5);
    }

    /**
     * 生成厂商详情
     *
     * @return void
     */
    public function companyDetail()
    {
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $param = array(
                'id' => trim(I('id')),
                'ids' => I('ids'),
                'jump_url' => trim(I('jump_url')),
                'page_number' => (int) I('page_number', 0),
                'last_updated_time' => trim(I('last_updated_time')),
                'type' => (int) I('type', 0), //肖书成 区分手机和PC版
            );
            //if (($up_timestamp = strtotime($param['last_updated_time'])) === false)
            //  $this->error('日期错误');
            $max_number = 10;
            $start = $param['page_number'] * $max_number;

            //实例化静态生成类
            $params = array(
                'model' => '',
                'module' => 'Home',
                'category' => '',
                'static_url' => C('STATIC_CREATE_URL'),
            );
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($params);
            //获取手机版参数  //作者:肖书成 描述:增加手机版详情页的功能 时间:2015-9-21
            if (I('type') == 1) {
                $property = array(
                    'theme' => C('MOBILE_THEME'),
                    'static_root_dir' => C('MOBILE_STATIC_ROOT'),
                );
                $staticInstance->setProperty($property);
            }
            //禁用状态的不生成
            $where = array('status' => array('gt', 0));
            isset($up_timestamp) && $where['update_time'] = array('gt', $up_timestamp);
            if (!empty($param['id']) || !empty($param['ids'])) {
                if (is_numeric($param['id'])) {
                    $where['id'] = $param['id'];
                }

                if (false !== strpos($param['id'], ',')) {
                    $ids = array_filter(array_unique(explode(',', $param['id'])));
                    !empty($ids) && $where['id'] = array('in', $ids);
                }

                if (is_array($param['ids']) && !empty($param['ids'])) {
                    $where['id'] = array('in', array_filter(array_unique($param['ids'])));
                }
            }

            $str = '';
            $all = D('Company')->where($where)->limit($start, $max_number)->order('id desc')->select();
            if (empty($all)) {
                !empty($param['jump_url']) ? $this->success('已全部更新完成!', U($param['jump_url'])) : $this->success(
                    '已全部更新完成!',
                    '',
                    false,
                    false
                );
                return;
            }
            foreach ($all as $value) {
                $rs = null;
                if ($value['path']) {
                    $params['id'] = $value['id'];
                    $rs = $staticInstance->widget(
                        'Company',
                        'detail',
                        $value['path'] . '/page_{page}',
                        $params,
                        'p',
                        'index'
                    ); //使用widget方法
                }
                $str .= $value['name'] . ' -- 生成' . ($rs ? '成功' : '失败') . '</br>';
            }

            $param['page_number'] += 1; //自动翻页
            $url = 'StaticCreate/companyDetail&' . http_build_query($param);
            $this->success($str, U($url, '', false));
        }
    }

    /**
     * 描述：手机版厂商内容页
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function createCsDetail()
    {
        //实例化静态生成类
        $params = array(
            'model' => '',
            'module' => 'Home',
            'category' => '',
            'static_url' => C('STATIC_CREATE_URL'),
        );
        $class = 'Common\\Library\\TempCreateLibrary';
        $staticInstance = new $class($params);
        $property = array(
            'theme' => C('MOBILE_THEME'),
            'static_root_dir' => C('MOBILE_STATIC_ROOT'),
        );
        $staticInstance->setProperty($property);
        $where['status'] = 1;
        $all = D('Company')->where($where)->order('id desc')->select();
        foreach ($all as $value) {
            $rs = null;
            if ($value['path']) {
                if (C('MOBILE_THEME') == '7230mobile') {
                    $value['path'] = $value['path'] . '/index';
                }
                $params['id'] = $value['id'];
                $rs = $staticInstance->widget(
                    'Company',
                    'mobileDetail',
                    $value['path'],
                    $params,
                    '',
                    'index'
                ); //使用widget方法
            }
            // $str .= $value['name'] . ' -- 生成' . ($rs ? '成功' : '失败') . '</br>';
        }
        $this->success('生成完成！', '', 3);
    }

    /**
     * 描述：生成专区、k页面、主题sitemap
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function siteMapToTopic()
    {
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            $model_arr = array('Feature', 'Batch', 'Special');
            $siteMapI = new \Common\Library\SiteMapLibrary();
            $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据
            //文件夹名
            $bd = 'xml';
            //手机版root路径
            $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            //PC版root路径
            $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            //url host
            $m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
            $p_host = C('STATIC_URL') . '/'; //PC版url host
            $sp = 1000; //全站数据的量级
            foreach ($model_arr as $model) {
                /***********分页处理全站数据*************/
                $base_file = 'sitemapall_' . strtolower($model); //基础文件名
                $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
                $m = ucfirst(strtolower($model));
                $count = M($m)->where(array('enabled' => '1'))->count();
                $page = ceil($count / $sp); //总页数
                $skey = strtoupper($model) . 'MAPKEY';
                // var_dump($count);            var_dump($page,$m_root_path.$path, $xml[0]);exit();
                for ($i = 0; $i <= $page; $i++) {
                    $lists = M($m)->field('id,update_time,interface')->where(array('enabled' => '1'))->limit(
                        $i * $sp,
                        $sp
                    )->order('id DESC')->select();
                    if (empty($lists)) {
                        continue;
                    }
                    if ($i == 0) {
                        S($skey, $lists[0]['id']);
                    } //缓存最大id
                    //手机版
                    foreach ($lists as $key => $value) {
                        if (!$value['interface']) {
                            continue;
                        }
                        $m_url = R("Feature/getPath", array($value['id'], $m));
                        if ($m_url == $m_host) {
                            continue;
                        }
                        $xml[$key]['loc'] = $m_url;
                        $xml[$key]['lastmod'] = date("Y-m-d", strtotime($value['update_time']));
                        $xml[$key]['changefreq'] = 'always';
                        $xml[$key]['priority'] = 1;
                    }
                    $path = $base_file . '_m_' . $i . '.xml';
                    $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                    $m_sitemap[$i]['time'] = date("Y-m-d");

                    $siteMapI->createXML('m', $m_root_path . $path, $xml);
                    unset($xml);
                    //PC版
                    foreach ($lists as $key => $value) {
                        if ($value['interface']) {
                            continue;
                        }
                        $p_url = R("Feature/getPath", array($value['id'], $m));
                        if ($p_url == $p_host || $p_url == $s_host) {
                            continue;
                        }
                        $xml[$key]['loc'] = $p_url;
                        $xml[$key]['lastmod'] = date("Y-m-d", strtotime($value['update_time']));
                        $xml[$key]['changefreq'] = 'always';
                        $xml[$key]['priority'] = 1;
                    }
                    $path = $base_file . '_p_' . $i . '.xml';
                    $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                    $p_sitemap[$i]['time'] = date("Y-m-d");
                    $siteMapI->createXML('p', $p_root_path . $path, $xml);
                }
                //生成sitemap格式全站数据
                $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
                $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
                unset($m_sitemap, $p_sitemap);
            }
            $this->success('生成完成！', '', 5);
        }
    }

    /**
     * 分模块生成全站数据站点地图，用于站内搜索(7230提出的要求,只针对站内搜索的下载)
     *
     * @return void
     */
    public function siteOtherMap()
    {
        //实例化站点地图对象
        $siteMapI = new \Common\Library\SiteMapLibrary();
        $model = I('model');
        $skey = strtoupper($model) . 'ZHANNEIMAPKEY';
        /***********分页处理全站数据*************/
        $base_file = 'baidu_zhannei_' . strtolower($model); //基础文件名
        $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据
        //文件夹名
        $bd = 'xml';
        //手机版root路径
        $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        //PC版root路径
        $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        //url host
        $m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
        $p_host = C('STATIC_URL') . '/'; //PC版url host
        $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
        $m = ucfirst(strtolower($model));
        if (strtolower($model) == "document") {
            $category = strtoupper("category"); //分类表
        } else {
            $category = strtoupper($model . "_category"); //分类表
        }
        $where['c.type'] = trim(strtolower($model)); //标签类型条件
        $sp = 1000; //全站数据的量级
        $count = M($m)->count();
        $page = ceil($count / $sp); //总页数
        // var_dump($count);            var_dump($page,$m_root_path.$path, $xml[0]);exit();
        for ($i = 0; $i <= $page; $i++) {
            if (strtolower($model) == "package") {
                $lists = M($m)->alias('a')->join('LEFT JOIN __' . $category . '__ f ON f.id = a.category_id ')->join(
                    'RIGHT JOIN __' . $category . '__ g ON g.id = f.rootid '
                )->field(
                    'a.id as id,a.update_time as update_time,a.title as name,a.description as description,a.view as view,a.create_time as create_time,f.title as subtitle,g.title as ptitle'
                )->limit($i * $sp, $sp)->order('a.id DESC')->select();

            } else {
                $lists = M($m)->alias('a')->join('LEFT JOIN __PICTURE__ b ON b.id = a.smallimg ')->join(
                    'LEFT JOIN __' . $category . '__ f ON f.id = a.category_id '
                )->join('RIGHT JOIN __' . $category . '__ g ON g.id = f.rootid ')->field(
                    'a.id as id,a.update_time as update_time,a.title as name,a.description as description,b.url as url,b.path as picurl,a.view as view,a.create_time as create_time,f.title as subtitle,g.title as ptitle'
                )->limit($i * $sp, $sp)->order('a.id DESC')->select();
            }
            if (empty($lists)) {
                continue;
            }
            if ($i == 0) {
                S($skey, $lists[0]['id']);
            } //缓存最大id
            //手机版
            foreach ($lists as $key => $value) {
                $m_url = staticUrlMobile('detail', $value['id'], $m);
                if ($m_url == $m_host) {
                    continue;
                }
                if (empty($m_url) || empty($value['name'])) {
                    continue;
                }
                $where['c.did'] = $value['id'];
                $tags = M('TagsMap')->alias('c')->join('LEFT JOIN __TAGS__ d ON c.tid = d.id ')->field(
                    'GROUP_CONCAT(d.title) as tag'
                )->where($where)->group('c.did')->find();
                $xml[$key]['loc'] = "<![CDATA[" . $m_url . " ]]>";
                $xml[$key]['lastmod'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                $xml[$key]['data']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                $xml[$key]['data']['url'] = "<![CDATA[" . $m_url . "]]>";
                if (empty($value['ptitle'])) {
                    $value['ptitle'] = $value['name'];
                }
                $xml[$key]['data']['gameCategory'] = "<![CDATA[" . $value['ptitle'] . "]]>";
                if (empty($value['subtitle'])) {
                    $value['subtitle'] = $value['name'];
                }
                $xml[$key]['data']['gameSubCategory'] = "<![CDATA[" . $value['subtitle'] . "]]>";
                if (empty($value['description'])) {
                    $value['description'] = $value['name'];
                }
                $xml[$key]['data']['description'] = "<![CDATA[" . $value['description'] . "]]>";
                $xml[$key]['data']['image']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                if (!empty($value['picurl'])) {
                    $xml[$key]['data']['image']['url'] = strstr(
                        $value['picurl'],
                        'http://'
                    ) ? "<![CDATA[" . $value['picurl'] . " ]]>" : "<![CDATA[" . get_pic_host($value['picurl']) . "]]>";
                }
                $xml[$key]['data']['datePublished'] = "<![CDATA[" . date("Y-m-d", $value['create_time']) . "]]>";
                $xml[$key]['data']['dateModified'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                if (!empty($tags['tag'])) {
                    $xml[$key]['data']['tag'] = "<![CDATA[" . $tags['tag'] . "]]>";
                }
                //if(!empty($tags['view']))
                //$xml[$key]['data']['downloadCount'] = "<![CDATA[".$value['view']." ]]>";
                // $xml[$key]['data']['version'] = ' ';
            }
            $path = $base_file . '_m_' . $i . '.xml';
            $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
            $m_sitemap[$i]['time'] = date("Y-m-d");

            $siteMapI->createXML('m', $m_root_path . $path, $xml);
            //PC版
            foreach ($lists as $key => $value) {
                $p_url = staticUrl('detail', $value['id'], $m);
                if ($p_url == $p_host || $p_url == $s_host) {
                    continue;
                }
                if (empty($p_url) || empty($value['name'])) {
                    continue;
                }
                $where['c.did'] = $value['id'];
                $tags = M('TagsMap')->alias('c')->join('LEFT JOIN __TAGS__ d ON c.tid = d.id ')->field(
                    'GROUP_CONCAT(d.title) as tag'
                )->where($where)->group('c.did')->find();
                $xml[$key]['loc'] = "<![CDATA[" . $p_url . "]]>";
                $xml[$key]['lastmod'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                $xml[$key]['data']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                $xml[$key]['data']['url'] = "<![CDATA[" . $p_url . "]]>";
                if (empty($value['ptitle'])) {
                    $value['ptitle'] = $value['name'];
                }
                $xml[$key]['data']['gameCategory'] = "<![CDATA[" . $value['ptitle'] . "]]>";
                if (empty($value['subtitle'])) {
                    $value['subtitle'] = $value['name'];
                }
                $xml[$key]['data']['gameSubCategory'] = "<![CDATA[" . $value['subtitle'] . "]]>";
                if (empty($value['description'])) {
                    $value['description'] = $value['name'];
                }
                $xml[$key]['data']['description'] = "<![CDATA[" . $value['description'] . "]]>";
                $xml[$key]['data']['image']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                if (!empty($value['picurl'])) {
                    $xml[$key]['data']['image']['url'] = strstr(
                        $value['picurl'],
                        'http://'
                    ) ? "<![CDATA[" . $value['picurl'] . "]]>" : "<![CDATA[" . get_pic_host($value['picurl']) . "]]>";
                }
                $xml[$key]['data']['datePublished'] = "<![CDATA[" . date("Y-m-d", $value['create_time']) . "]]>";
                $xml[$key]['data']['dateModified'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                if (!empty($tags['tag'])) {
                    $xml[$key]['data']['tag'] = "<![CDATA[" . $tags['tag'] . "]]>";
                }
                // if(!empty($value['view']))
                // $xml[$key]['data']['downloadCount'] = "<![CDATA[".$value['view']."]]>";
                // $xml[$key]['data']['version'] = '';
            }
            $path = $base_file . '_p_' . $i . '.xml';
            $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
            $p_sitemap[$i]['time'] = date("Y-m-d");
            $siteMapI->createXML('p', $p_root_path . $path, $xml);
        }
        //生成sitemap格式全站数据
        $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
        $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);

        //$this->success('生成完成！', '', 5);
    }

    /**
     * 分模块生成全站数据站点地图
     *
     * @return void
     */
    public function siteMap()
    {
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/model/' . I('model'));
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            set_time_limit(0);
            //实例化站点地图对象
            $siteMapI = new \Common\Library\SiteMapLibrary();
            $model = I('model');
            $skey = strtoupper($model) . 'MAPKEY';

            /***********分页处理全站数据*************/
            $base_file = 'sitemapall_' . strtolower($model); //基础文件名
            $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据

            //文件夹名
            $bd = 'xml';
            //手机版root路径
            $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            //PC版root路径
            $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            //url host
            $m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
            $p_host = C('STATIC_URL') . '/'; //PC版url host
            $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host

            // 只生成审核数据 crohn 2015-6-16
            $where = array();
            $allow = array('Down', 'Document', 'Package');
            if (in_array($m, $allow)) {
                $where['status'] = 1;
            }

            $m = ucfirst(strtolower($model));
            $sp = 1000; //全站数据的量级
            $count = M($m)->where($where)->count();
            //总页数
            $page = ceil($count / $sp);
            for ($i = 0; $i <= $page; $i++) {
                $lists = M($m)->field('id,update_time')->where($where)->limit($i * $sp, $sp)->order('id DESC')->select(
                );
                if (empty($lists)) {
                    continue;
                }
                if ($i == 0) {
                    S($skey, $lists[0]['id']);
                } //缓存最大id
                //手机版
                foreach ($lists as $key => $value) {
                    $m_url = staticUrlMobile('detail', $value['id'], $m);
                    if ($m_url == $m_host) {
                        continue;
                    }
                    $xml[$key]['loc'] = $m_url;
                    $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $xml[$key]['changefreq'] = 'always';
                    $xml[$key]['priority'] = 1;
                }
                $path = $base_file . '_m_' . $i . '.xml';
                $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                $m_sitemap[$i]['time'] = date("Y-m-d");

                $siteMapI->createXML('m', $m_root_path . $path, $xml);
                //PC版
                foreach ($lists as $key => $value) {
                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['loc'] = $p_url;
                    $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $xml[$key]['changefreq'] = 'always';
                    $xml[$key]['priority'] = 1;
                }
                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createXML('p', $p_root_path . $path, $xml);
            }
            //生成sitemap格式全站数据
            $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
            $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
            if (strtolower($model) == "down") {
                $this->siteOtherMap();
            }
            $this->success('生成完成！', '', 5);
        }
    }

    /**
     * 分模块生成最新数据站点地图（文章、下载、礼包、专题、专区、k页面模块）
     *
     * @return void
     */
    public function siteMapNew()
    {
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            //实例化站点地图对象
            $siteMapI = new \Common\Library\SiteMapLibrary();
            /***********分页处理全站数据*************/
            $file = 'sitemapnew.xml'; //基础文件名
            //文件夹名
            $bd = 'xml';
            //手机版root路径
            $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            //PC版root路径
            $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
            $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            //url host
            $m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
            $p_host = C('STATIC_URL') . '/'; //PC版url host
            $sp = 1000; //数据的量级
            $m_i = $p_i = 0; //计数
            $allow = array('Down', 'Document', 'Package');
            $m_xml = $p_xml = array(); //数据
            foreach ($allow as $m) {
                $base_file = 'sitemapall_' . strtolower($m); //基础文件名
                $skey = strtoupper($m) . 'MAPKEY'; //缓存key值
                S($skey, null);
                // 只生成审核数据 crohn 2015-6-16
                $where['status'] = 1;
                //判断缓存值是否存在
                if (S($skey)) {
                    $where['id'] = array('gt', S($skey));
                    $lists = M($m)->field('id,update_time')->where($where)->order('id DESC')->select();
                } else {
                    $lists = M($m)->field('id,update_time')->where($where)->limit(0, $sp)->order('id DESC')->select();
                }
                $s_host = C(strtoupper($m) . '_SLD') . '/'; //PC版二级 host
                if (empty($lists)) {
                    continue;
                }
                //手机版
                foreach ($lists as $key => $value) {
                    $m_url = staticUrlMobile('detail', $value['id'], $m);
                    if ($m_url == $m_host) {
                        continue;
                    }
                    $xml[$key]['loc'] = $m_url;
                    $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $xml[$key]['changefreq'] = 'always';
                    $xml[$key]['priority'] = 1;
                    $m_xml[$m_i]['loc'] = $m_url;
                    // $m_xml[$m_i]['lastmod'] = date("Y-m-d", $value['update_time']) . 'T' . date("h:i:s", $value['update_time']);
                    //修改百度sitemap时间格式  修改人：谭坚  日期：2015-9-25
                    $m_xml[$m_i]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $m_xml[$m_i]['changefreq'] = 'always';
                    $m_xml[$m_i]['priority'] = 1;
                    $m_i++;
                }
                $path = $base_file . '_m_0.xml';
                $siteMapI->createXML('m', $m_root_path . $path, $xml); //生成最新数据
                //PC版
                foreach ($lists as $key => $value) {
                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['loc'] = $p_url;
                    $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $xml[$key]['changefreq'] = 'always';
                    $xml[$key]['priority'] = 1;
                    $p_xml[$p_i]['loc'] = $p_url;
                    if ($p_xml[$p_i]['loc'] == $p_host || $p_xml[$p_i]['loc'] == $s_host) {
                        continue;
                    }
                    //$p_xml[$p_i]['lastmod'] = date("Y-m-d", $value['update_time']) . 'T' . date("h:i:s", $value['update_time'])
                    //修改百度sitemap时间格式  修改人：谭坚  日期：2015-9-25
                    $p_xml[$p_i]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $p_xml[$p_i]['changefreq'] = 'always';
                    $p_xml[$p_i]['priority'] = 1;
                    $p_i++;
                }
                $path = $base_file . '_p_0.xml';
                $siteMapI->createXML('p', $p_root_path . $path, $xml); //生成最新数据
                unset($xml);
            }
            //释放掉条件变量  谭坚  2015-7-24
            unset($where);
            //专题、专区、k页面最新数据
            $model_arr = array('Feature', 'Batch', 'Special');
            foreach ($model_arr as $model) {
                $base_file = 'sitemapall_' . strtolower($model); //基础文件名
                /***********分页处理全站数据*************/
                $m = ucfirst(strtolower($model));
                $skey = strtoupper($model) . 'MAPKEY';
                S($skey, null);
                //判断缓存值是否存在
                if (S($skey)) {
                    $where['id'] = array('gt', S($skey));
                    $where['enable'] = '1';
                    $lists = M($m)->field('id,update_time,interface')->where($where)->order('id DESC')->select();
                } else {
                    $lists = M($m)->field('id,update_time,interface')->where(array('enabled' => '1'))->limit(
                        0,
                        $sp
                    )->order('id DESC')->select();
                }
                if (empty($lists)) {
                    continue;
                }
                //手机版
                foreach ($lists as $key => $value) {
                    if (!$value['interface']) {
                        continue;
                    }
                    $m_url = R("Feature/getPath", array($value['id'], $m));
                    if ($m_url == $m_host) {
                        continue;
                    }
                    $k_xml[$key]['loc'] = $m_url;
                    $k_xml[$key]['lastmod'] = date("Y-m-d", strtotime($value['update_time']));
                    $k_xml[$key]['changefreq'] = 'always';
                    $k_xml[$key]['priority'] = 1;
                    $m_xml[$m_i]['loc'] = $m_url;
                    //$m_xml[$m_i]['lastmod'] = date("Y-m-d", $value['update_time']) . 'T' . date("h:i:s", $value['update_time']);
                    //修改百度sitemap时间格式  修改人：谭坚  日期：2015-9-25
                    $m_xml[$m_i]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $m_xml[$m_i]['changefreq'] = 'always';
                    $m_xml[$m_i]['priority'] = 1;
                    $m_i++;
                }
                $path = $base_file . '_m_0.xml';
                $siteMapI->createXML('m', $m_root_path . $path, $k_xml); //生成最新数据
                //PC版
                foreach ($lists as $key => $value) {
                    if ($value['interface']) {
                        continue;
                    }
                    $p_url = R("Feature/getPath", array($value['id'], $m));
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $k_xml[$key]['loc'] = $p_url;
                    $k_xml[$key]['lastmod'] = date("Y-m-d", strtotime($value['update_time']));
                    $k_xml[$key]['changefreq'] = 'always';
                    $k_xml[$key]['priority'] = 1;
                    $p_xml[$p_i]['loc'] = $p_url;
                    //$p_xml[$p_i]['lastmod'] = date("Y-m-d", $value['update_time']) . 'T' . date("h:i:s", $value['update_time']);
                    //修改百度sitemap时间格式  修改人：谭坚  日期：2015-9-25
                    $p_xml[$p_i]['lastmod'] = date("Y-m-d", $value['update_time']);
                    $p_xml[$p_i]['changefreq'] = 'always';
                    $p_xml[$p_i]['priority'] = 1;
                    $p_i++;
                }
                $path = $base_file . '_p_0.xml';
                $siteMapI->createXML('p', $p_root_path . $path, $k_xml); //生成最新的数据
                unset($k_xml);
            }
            //释放掉条件变量  谭坚  2015-7-24
            unset($where);
            //生成
            $siteMapI->createXML('m', $m_root_path . $file, $m_xml);
            $siteMapI->createXML('p', $p_root_path . $file, $p_xml);
            //生成站内搜索最新数据(只针对下载模块)
            unset($xml, $m_xml, $p_xml, $k_xml);
            $model = "Down";
            $skey = strtoupper($model) . 'ZHANNEIMAPKEY';
            $base_file = 'baidu_zhannei_' . strtolower($model); //基础文件名
            $m = ucfirst($model);
            $category = strtoupper($model . "_category"); //分类表
            $where['c.type'] = trim(strtolower($model)); //标签类型条件
            S($skey, null);
            //判断缓存值是否存在
            if (S($skey)) {
                $w['a.id'] = array('gt', S($skey));
                $lists = M($m)->alias('a')->join('LEFT JOIN __PICTURE__ b ON b.id = a.smallimg ')->join(
                    'LEFT JOIN __' . $category . '__ f ON f.id = a.category_id '
                )->join('RIGHT JOIN __' . $category . '__ g ON g.id = f.rootid ')->field(
                    'a.id as id,a.update_time as update_time,a.title as name,a.description as description,b.url as url,b.path as picurl,a.view as view,a.create_time as create_time,f.title as subtitle,g.title as ptitle'
                )->where($w)->order('a.id DESC')->select();
            } else {
                $lists = M($m)->alias('a')->join('LEFT JOIN __PICTURE__ b ON b.id = a.smallimg ')->join(
                    'LEFT JOIN __' . $category . '__ f ON f.id = a.category_id '
                )->join('RIGHT JOIN __' . $category . '__ g ON g.id = f.rootid ')->field(
                    'a.id as id,a.update_time as update_time,a.title as name,a.description as description,b.url as url,b.path as picurl,a.view as view,a.create_time as create_time,f.title as subtitle,g.title as ptitle'
                )->limit(0, $sp)->order('a.id DESC')->select();
            } //手机版
            foreach ($lists as $key => $value) {
                $m_url = staticUrlMobile('detail', $value['id'], $m);
                if ($m_url == $m_host) {
                    continue;
                }
                if (empty($m_url) || empty($value['name'])) {
                    continue;
                }
                $where['c.did'] = $value['id'];
                $tags = M('TagsMap')->alias('c')->join('LEFT JOIN __TAGS__ d ON c.tid = d.id ')->field(
                    'GROUP_CONCAT(d.title) as tag'
                )->where($where)->group('c.did')->find();
                $xml[$key]['loc'] = "<![CDATA[" . $m_url . " ]]>";
                $xml[$key]['lastmod'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                $xml[$key]['data']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                $xml[$key]['data']['url'] = "<![CDATA[" . $m_url . "]]>";
                if (empty($value['ptitle'])) {
                    $value['ptitle'] = $value['name'];
                }
                $xml[$key]['data']['gameCategory'] = "<![CDATA[" . $value['ptitle'] . "]]>";
                if (empty($value['subtitle'])) {
                    $value['subtitle'] = $value['name'];
                }
                $xml[$key]['data']['gameSubCategory'] = "<![CDATA[" . $value['subtitle'] . "]]>";
                if (empty($value['description'])) {
                    $value['description'] = $value['name'];
                }
                $xml[$key]['data']['description'] = "<![CDATA[" . $value['description'] . "]]>";
                $xml[$key]['data']['image']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                if (!empty($value['picurl'])) {
                    $xml[$key]['data']['image']['url'] = strstr(
                        $value['picurl'],
                        'http://'
                    ) ? "<![CDATA[" . $value['picurl'] . " ]]>" : "<![CDATA[" . get_pic_host($value['picurl']) . "]]>";
                }
                $xml[$key]['data']['datePublished'] = "<![CDATA[" . date("Y-m-d", $value['create_time']) . "]]>";
                $xml[$key]['data']['dateModified'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                if (!empty($tags['tag'])) {
                    $xml[$key]['data']['tag'] = "<![CDATA[" . $tags['tag'] . "]]>";
                }
            }
            $path = $base_file . '_m_0.xml';
            $siteMapI->createXML('m', $m_root_path . $path, $xml); //生成最新数据
            //PC版
            foreach ($lists as $key => $value) {
                $p_url = staticUrl('detail', $value['id'], $m);
                if ($p_url == $p_host || $p_url == $s_host) {
                    continue;
                }
                if (empty($p_url) || empty($value['name'])) {
                    continue;
                }
                $where['c.did'] = $value['id'];
                $tags = M('TagsMap')->alias('c')->join('LEFT JOIN __TAGS__ d ON c.tid = d.id ')->field(
                    'GROUP_CONCAT(d.title) as tag'
                )->where($where)->group('c.did')->find();
                $xml[$key]['loc'] = "<![CDATA[" . $p_url . "]]>";
                $xml[$key]['lastmod'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                $xml[$key]['data']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                $xml[$key]['data']['url'] = "<![CDATA[" . $p_url . "]]>";
                if (empty($value['ptitle'])) {
                    $value['ptitle'] = $value['name'];
                }
                $xml[$key]['data']['gameCategory'] = "<![CDATA[" . $value['ptitle'] . "]]>";
                if (empty($value['subtitle'])) {
                    $value['subtitle'] = $value['name'];
                }
                $xml[$key]['data']['gameSubCategory'] = "<![CDATA[" . $value['subtitle'] . "]]>";
                if (empty($value['description'])) {
                    $value['description'] = $value['name'];
                }
                $xml[$key]['data']['description'] = "<![CDATA[" . $value['description'] . "]]>";
                $xml[$key]['data']['image']['name'] = "<![CDATA[" . $value['name'] . "]]>";
                if (!empty($value['picurl'])) {
                    $xml[$key]['data']['image']['url'] = strstr(
                        $value['picurl'],
                        'http://'
                    ) ? "<![CDATA[" . $value['picurl'] . "]]>" : "<![CDATA[" . get_pic_host($value['picurl']) . "]]>";
                }
                $xml[$key]['data']['datePublished'] = "<![CDATA[" . date("Y-m-d", $value['create_time']) . "]]>";
                $xml[$key]['data']['dateModified'] = "<![CDATA[" . date("Y-m-d", $value['update_time']) . "]]>";
                if (!empty($tags['tag'])) {
                    $xml[$key]['data']['tag'] = "<![CDATA[" . $tags['tag'] . "]]>";
                }
            }
            $path = $base_file . '_p_0.xml';
            $siteMapI->createXML('p', $p_root_path . $path, $xml); //生成最新数据
            unset($xml);
            $this->success('生成完成！', '', 2);
        }
    }

    /**
     * 分模块生成全站神马搜索XML数据
     *
     * @return void
     */
    public function shenmaSiteMap()
    {
        set_time_limit(0);

        // 特殊站点使用特殊方法
        $theme = C('THEME');
        $reflect = new \ReflectionClass($this);
        if ($reflect->hasMethod('shenmaSiteMap_' . $theme)) {
            $method = 'shenmaSiteMap_' . $theme;
            $this->$method();
            exit();
        }

        //实例化站点地图对象
        $siteMapI = new \Common\Library\SiteMapLibrary();
        $model = I('model');
        //$skey = strtoupper($model) . 'MAPKEY';
        $base_file = 'shenma_data_' . strtolower($model); //基础文件名
        $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据

        //文件夹名
        $bd = 'xml/shenmaxml';
        // //手机版root路径
        // $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
        // $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
        // if (!is_dir($dir)) mkdir($dir, 0755, true);
        //PC版root路径
        $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        //url host
        //$m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
        $p_host = C('STATIC_URL') . '/'; //PC版url host
        $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
        $m = ucfirst(strtolower($model));

        // 只生成审核数据 crohn 2015-6-16
        $where = [];
        $allow = ['Down', 'Document' /*'Package'*/];
        $extend = [
            'Down' => ['__DOWN_DMAIN__', 'DownCategory'],
            'Document' => ['__DOCUMENT_ARTICLE__', 'Category'],
            /*'PACKAGE_PMAIN'*/
        ];
        if (in_array($m, $allow)) {
            $where['status'] = 1;
        }

        $sp = 1000; //全站数据的量级
        $count = M($m)->where($where)->count();
        $page = ceil($count / $sp); //总页数

        /*   文章     */
        if ($m == 'Document') {
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->alias('m')
                    ->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")
                    ->field('view,description,title,m.id as id,update_time,content,category_id')
                    ->where(['m.status' => 1])
                    ->limit($i * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(
                        ['detail', 'Document', $value['id']]
                    )['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, $extend[$m][1]);
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        /*   下载     */
        if ($m == 'Down') {
            $allow_system = ['', 'android', 'ios', 'windows', 'others'];
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->alias('m')
                    ->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")
                    ->field('description,title,m.id as id,update_time,category_id,smallimg,version,size,system,view')
                    ->where(['m.status' => 1])
                    ->limit($i * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Down', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['name'] = '<![CDATA[' . $value['title'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['icon'] = '<![CDATA[' . get_cover($value['smallimg'], 'path') . ']]>';

                    // 对下载文件大小的通用处理 liuliu 2016-3-15
                    preg_match('{([\d\.]+)[mk]b?}i', $value['size'], $match_size);
                    if (!empty($match_size[1]) && $match_size[1] != 0) {
                        $size = $value['size'];
                    } else {
                        $size = ((int) $value['size'] / 1000) . 'M';
                    }
                    $xml[$key]['fileSize'] = '<![CDATA[' . $size . ']]>';

                    $xml[$key]['version'] = '<![CDATA[' . $value['version'] . ']]>';
                    // 操作系统
                    $value['system'] = ($value['system'] > 3 or empty($value['system'])) ? 4 : $value['system'];
                    $xml[$key]['os'] = '<![CDATA[' . $allow_system[$value['system']] . ']]>';

                    // 下载地址
                    $xml[$key]['downloadLink'] = '<![CDATA[' . getFileUrl($value['id']) . ']]>';

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    $xml[$key]['category'] = '<![CDATA[' . $cate['title'] . ']]>';

                    // 下载次数
                    $xml[$key]['installation'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        //生成索引
        // $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
        $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
        $this->success('神马搜索结构化数据生成完成！', '', 5);
    }

    /**
     * 分模块生成全站神马搜索XML数据 官方网特殊处理,单纯实现功能，没有封装
     *
     * @return void
     */
    public function shenmaSiteMap_gfw()
    {
        //实例化站点地图对象
        $siteMapI = new \Common\Library\SiteMapLibrary();
        $model = I('model');
        //$skey = strtoupper($model) . 'MAPKEY';
        $base_file = 'shenma_data_' . strtolower($model); //基础文件名
        $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据

        //文件夹名
        $bd = 'xml/shenmaxml';
        // //手机版root路径
        // $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
        // $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
        // if (!is_dir($dir)) mkdir($dir, 0755, true);
        //PC版root路径
        $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        //url host
        //$m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
        $p_host = C('STATIC_URL') . '/'; //PC版url host
        $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
        $m = ucfirst(strtolower($model));

        // 只生成审核数据 crohn 2015-6-16
        $where = [];
        $allow = ['Down', 'Document' /*'Package'*/];
        $extend = [
            'Down' => ['__DOWN_DMAIN__', 'DownCategory'],
            'Document' => ['__DOCUMENT_ARTICLE__', 'Category'],
            /*'PACKAGE_PMAIN'*/
        ];

        /*   文章     */
        if ($m == 'Document') {
            // 文章模板
            if (in_array($m, $allow)) {
                $where['status'] = 1;
            }
            $sp = 1000; //全站数据的量级
            $count = M($m)->where($where)->count();
            $page = ceil($count / $sp); //总页数

            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->alias('m')
                    ->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")
                    ->field('view,description,title,m.id as id,update_time,content,category_id')
                    ->where(['m.status' => 1])
                    ->limit($i * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();

                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(
                        ['detail', 'Document', $value['id']]
                    )['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . iconv(
                        'UTF-8',
                        'UTF-8//IGNORE',
                        $value['description']
                    ) . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, $extend[$m][1]);
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");

                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
            $all_i = $i;

            // 下载模块的文章数据
            $where = [];
            $where['status'] = 1;
            $where['model_id'] = 20;
            $sp = 1000; //全站数据的量级
            $count = M('Down')->where($where)->count();
            $page = ceil($count / $sp); //总页数

            for ($i = $all_i; $i < $page + $all_i; $i++) {
                $xml = $lists = [];
                $lists = M('Down')
                    ->alias('m')
                    ->join("LEFT JOIN __DOWN_PRODUCT__ e ON m.id = e.id")
                    ->field('view,description,title,m.id as id,update_time,content,category_id')
                    ->where(['m.status' => 1, 'm.model_id' => 20])
                    ->limit(($i - $all_i) * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], 'Down');
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Down', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . iconv(
                        'UTF-8',
                        'UTF-8//IGNORE',
                        $value['description']
                    ) . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, 'DownCategory');
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, 'DownCategory');
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], 'down', 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], 'down', 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
            $all_i = $i;

            // 礼包模块的文章数据
            $where = [];
            $where['status'] = 1;
            $sp = 1000; //全站数据的量级
            $count = M('Package')->where($where)->count();
            $page = ceil($count / $sp); //总页数

            for ($i = $all_i; $i < $page + $all_i; $i++) {
                $xml = $lists = [];
                $sqli = M('Package')->alias('m')->join("LEFT JOIN __PACKAGE_PMAIN__ e ON m.id = e.id")->field(
                    'view,description,title,m.id as id,update_time,content,category_id'
                )->where(['m.status' => 1])->limit(($i - $all_i) * $sp, $sp)->order('m.id DESC')->select(false);
                $lists = M('Package')
                    ->alias('m')
                    ->join("LEFT JOIN __PACKAGE_PMAIN__ e ON m.id = e.id")
                    ->field('view,description,title,m.id as id,update_time,content,category_id')
                    ->where(['m.status' => 1])
                    ->limit(($i - $all_i) * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();

                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], 'Package');
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Package', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . iconv(
                        'UTF-8',
                        'UTF-8//IGNORE',
                        $value['description']
                    ) . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, 'PackageCategory');
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, 'PackageCategory');
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], 'package', 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], 'package', 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }

        }

        /*   下载     */
        if ($m == 'Down') {

            $where = [];
            $where['status'] = 1;
            $where['model_id'] = 13;
            $sp = 1000; //全站数据的量级
            $count = M($m)->where($where)->count();
            $page = ceil($count / $sp); //总页数

            $allow_system = ['', 'android', 'ios', 'windows', 'others'];

            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->alias('m')
                    ->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")
                    ->field('description,title,m.id as id,update_time,category_id,smallimg,version,size,system,view')
                    ->where(['m.status' => 1, 'm.model_id' => 13])
                    ->limit($i * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Down', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . iconv(
                        'UTF-8',
                        'UTF-8//IGNORE',
                        $value['description']
                    ) . ']]>';
                    $xml[$key]['name'] = '<![CDATA[' . $value['title'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['icon'] = '<![CDATA[' . get_cover($value['smallimg'], 'path') . ']]>';
                    //软件大小为0，随机10-50m，修改人：谭坚  2015-9-25
                    $size = intval($value['size']);
                    $size = $size ? $size : rand(10000, 50000);
                    $xml[$key]['fileSize'] = '<![CDATA[' . ((int) $size / 1000) . 'M' . ']]>';
                    $xml[$key]['version'] = '<![CDATA[' . $value['version'] . ']]>';

                    // 操作系统
                    $value['system'] = ($value['system'] > 3 or empty($value['system'])) ? 4 : $value['system'];
                    $xml[$key]['os'] = '<![CDATA[' . $allow_system[$value['system']] . ']]>';

                    $download_link = getFileUrl($value['id']);
                    // 下载地址为空，神马搜索不推送，修改人：谭坚  2015-9-25
                    if (empty($download_link)) {
                        unset($xml[$key]);
                        continue;
                    }
                    $xml[$key]['downloadLink'] = '<![CDATA[' . $download_link . ']]>';

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    $xml[$key]['category'] = '<![CDATA[' . $cate['title'] . ']]>';

                    // 下载次数
                    $xml[$key]['installation'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        //生成索引
        // $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
        $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
        $this->success('神马搜索结构化数据生成完成！', '', 5);
    }

    /**
     * 特殊页生成列表
     *
     * @return void
     */
    public function widgetCreate()
    {
        $map = array();
        $type = I('type');
        $type = empty($type) ? 0 : $type;
        $map['type'] = $type;
        $list = D('StaticPage')->getList($map);
        $groups = array();
        foreach ($list as $key => $value) {
            $groups[$value['group']][] = $value;
        }
        //var_dump($groups);
        $this->assign('groups', $groups);
        $this->display();
    }

    /**
     * 特殊页生成提交
     *
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    public function widgetSub()
    {
        $id = I('id');
        !$id && $this->error('id参数错误！');
        $info = D('StaticPage')->info($id);
        !$info && $this->error('id不存在！');
        //命令存入文件，命令行运行生成
        if (I('cmd')) {
            createHtml(ACTION_NAME . '/id/' . $id);
            $this->success('系统正在后台生成，请稍后查看生成结果！');
        } else {
            //参数处理为数组
            preg_match_all("/(\w+?\=.+?)([\n\r$]+?|$)/i", $info['params'], $matchs);
            $params = array();
            foreach ($matchs[1] as $v) {
                $p = explode('=', $v);
                $params[$p[0]] = urlencode($p[1]);
            }
            //参数默认带入主键ID用于前台查找路径和SEO
            $params['page_id'] = $id;
            //是否多页
            $page = $info['multipage'] ? $info['multipage'] : '';

            //实例化静态生成类
            $obj['model'] = ''; //不需要指定数据模型
            $obj['module'] = $info['module_name'];
            $obj['category'] = ''; //不需要指定分类模型
            $obj['static_url'] = C('STATIC_CREATE_URL');
            $class = 'Common\\Library\\TempCreateLibrary';
            $staticInstance = new $class($obj);
            //获取手机版参数
            if ($info['type'] == 1) {
                $property = array(
                    'theme' => C('MOBILE_THEME'),
                    'static_root_dir' => C('MOBILE_STATIC_ROOT'),
                );
                $staticInstance->setProperty($property);
            }
            //使用widget方法
            $rs = $staticInstance->widget(
                $info['controller_name'],
                $info['method_name'],
                $info['path'],
                $params,
                $page,
                $info['multipage_index']
            );
            $rs ? $this->success('刷新成功！') : $this->error('刷新失败！');
        }
    }

    /**
     * 页面管理首页
     *
     * @return void
     */
    public function index()
    {
        $map = array();
        $type = I('type');
        $type = empty($type) ? 0 : $type;
        $map['type'] = $type;
        $list = D('StaticPage')->getList($map);
        $request = (array) I('request.');
        $total = $list ? count($list) : 1;
        $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page = new \Think\Page($total, $listRows, $request);
        $voList = array_slice($list, $page->firstRow, $page->listRows);
        $p = $page->show();
        $this->assign('type', $type);
        $this->assign('_list', $voList);
        $this->assign('_page', $p ? $p : '');
        $this->display();
    }

    /**
     * 编辑
     *
     * @param  integer $id id
     * @return void
     */
    public function edit($id)
    {
        $model = D('StaticPage');
        if (IS_POST) {
            //提交表单
            if (false !== $model->update()) {
                $this->success('编辑成功！', U('index'));
            } else {
                $error = $model->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            /* 获取标签信息 */
            $info = $model->info($id);
            $type = I('type');
            $type = empty($type) ? 0 : $type;
            $this->assign('info', $info);
            $this->assign('type', $type);
            $this->display();
        }
    }

    /**
     * 新增
     *
     * @return void
     */
    public function add()
    {
        $model = D('StaticPage');
        //提交表单
        if (IS_POST) {
            if (false !== $model->update()) {
                $this->success('新增成功！', U('index'));
            } else {
                $error = $model->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {

            /* 获取标签信息 */
            $this->assign('info', null);
            $type = I('type');
            $type = empty($type) ? 0 : $type;
            $this->assign('type', $type);
            $this->display('edit');
        }
    }

    /**
     * 删除
     *
     * @return void
     */
    public function remove()
    {
        $id = I('id');
        if (empty($id)) {
            $this->error('参数错误!');
        }
        $model = D('StaticPage');

        //删除信息
        $res = $model->delete($id);

        if ($res !== false) {
            //记录删除行为
            action_log('delete_' . $this->log_name, $this->log_name, $id, UID);
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }

    }
    public function shenmaSiteMap_jf96u()
    {

        set_time_limit(0);

        // 特殊站点使用特殊方法


        //实例化站点地图对象
        $siteMapI = new \Common\Library\SiteMapLibrary();
        $model = I('model');
        //$skey = strtoupper($model) . 'MAPKEY';
        $base_file = 'shenma_data_' . strtolower($model); //基础文件名
        $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据

        //文件夹名
        $bd = 'xml/shenmaxml';
        // //手机版root路径
        // $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
        // $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
        // if (!is_dir($dir)) mkdir($dir, 0755, true);
        //PC版root路径
        $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        //url host
        //$m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
        $p_host = C('STATIC_URL') . '/'; //PC版url host
        $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
        $m = ucfirst(strtolower($model));

        // 只生成审核数据 crohn 2015-6-16
        $where = [];
        $allow = ['Down', 'Document' /*'Package'*/];
        $extend = [
            'Down' => ['__DOWN_DMAIN__', 'DownCategory'],
            'Document' => ['__DOCUMENT_ARTICLE__', 'Category'],
            /*'PACKAGE_PMAIN'*/
        ];
        if (in_array($m, $allow)) {
            $where['status'] = 1;
        }

        $sp = 1000; //全站数据的量级
        $count = M($m)->where($where)->count();
        $page = ceil($count / $sp); //总页数

        /*   文章     */
        if ($m == 'Document') {
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->alias('m')
                    ->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")
                    ->field('view,description,title,m.id as id,update_time,content,category_id')
                    ->where(['m.status' => 1])
                    ->limit($i * $sp, $sp)
                    ->order('m.id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Document', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, $extend[$m][1]);
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        /*   下载     */
        if ($m == 'Down') {
            $allow_system = ['', 'android', 'ios', 'windows', 'others'];
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)
                    ->field('description,title,id as id,update_time,category_id,smallimg,view')//version,size,system
                    ->where(['status' => 1])
                    ->limit($i * $sp, $sp)
                    ->order('id DESC')
                    ->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    $down = D('Down');
                    $data = $down->detail($value['id']);
                    $value['version'] = $data['version'];
                    $value['size'] = $data['size'];
                    $value['system'] = $data['system'];

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO(['detail', 'Down', $value['id']])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['name'] = '<![CDATA[' . $value['title'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['icon'] = '<![CDATA[' . get_cover($value['smallimg'], 'path') . ']]>';

                    // 对下载文件大小的通用处理 liuliu 2016-3-15
                    preg_match('{([\d\.]+)[mk]b?}i', $value['size'], $match_size);
                    if (!empty($match_size[1]) && $match_size[1] != 0) {
                        $size = $value['size'];
                    } else {
                        $size = ((int)$value['size'] / 1000) . 'M';
                    }
                    $xml[$key]['fileSize'] = '<![CDATA[' . $size . ']]>';

                    $xml[$key]['version'] = '<![CDATA[' . $value['version'] . ']]>';
                    // 操作系统
                    $value['system'] = ($value['system'] > 3 or empty($value['system'])) ? 4 : $value['system'];
                    $xml[$key]['os'] = '<![CDATA[' . $allow_system[$value['system']] . ']]>';

                    // 下载地址
                    $xml[$key]['downloadLink'] = '<![CDATA[' . getFileUrl($value['id']) . ']]>';

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    $xml[$key]['category'] = '<![CDATA[' . $cate['title'] . ']]>';

                    // 下载次数
                    $xml[$key]['installation'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        //生成索引
        // $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
        $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
        $this->success('神马搜索结构化数据生成完成！', '', 5);
    }
    /**
     * 作者:肖书成
     * 时间:2016-2-26
     * 描述:分模块生成全站神马搜索XML数据 安粉丝网特殊处理,单纯实现功能，没有封装
     * @return void
     */
    public function shenmaSiteMap_afs()
    {
        //实例化站点地图对象
        $siteMapI = new \Common\Library\SiteMapLibrary();
        $model = I('model');
        //$skey = strtoupper($model) . 'MAPKEY';
        $base_file = 'shenma_data_' . strtolower($model); //基础文件名
        $m_sitemap = $p_sitemap = array(); //sitemap格式生成数据

        //文件夹名
        $bd = 'xml/shenmaxml';
        // //手机版root路径
        // $m_root_path = './' . C('MOBILE_STATIC_ROOT') . '/' . $bd . '/';
        // $dir = substr($m_root_path, 0, strrpos($m_root_path, '/'));
        // if (!is_dir($dir)) mkdir($dir, 0755, true);
        //PC版root路径
        $p_root_path = C('STATIC_ROOT') . '/' . $bd . '/';
        $dir = substr($p_root_path, 0, strrpos($p_root_path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        //url host
        //$m_host = C('MOBILE_STATIC_URL') . '/'; //手机版url host
        $p_host = C('STATIC_URL') . '/'; //PC版url host
        $s_host = C(strtoupper($model) . '_SLD') . '/'; //PC版二级 host
        $m = ucfirst(strtolower($model));

        // 只生成审核数据 crohn 2015-6-16
        $where = [];
        $allow = [
            'Down',
            'Document',
            /*'Package'*/
        ];
        $extend = [
            'Down' => [
                '__DOWN_DMAIN__',
                'DownCategory',
            ],
            'Document' => [
                '__DOCUMENT_ARTICLE__',
                'Category',
            ],
            'Wenda' => [
                '__DOCUMENT_WENDA__',
                'Category',
            ],
            /*'PACKAGE_PMAIN'*/
        ];
        if (in_array($m, $allow)) {
            $where['status'] = 1;
        }

        $sp = 1000; //全站数据的量级
        if ($m != 'Wenda') {
            $count = M($m)->where($where)->count();
        } else {
            $count = M('Document')->alias('a')->join('__DOCUMENT_WENDA__ b ON a.id = b.id')->where('a.status = 1 AND a.id > 113488')->count();
        }

        $page = ceil($count / $sp); //总页数

        /*   文章     */
        if ($m == 'Document') {
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)->alias('m')->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")->field('view,description,title,m.id as id,update_time,content,category_id')->where(['m.status' => 1])->limit($i * $sp, $sp)->order('m.id DESC')->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    $matchs = $images = [];
                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    unset($matchs[0]);
                    foreach ($matchs[1] as $k => $v) {
                        if (if_url($v)) {
                            $images['img' . ($k + 1)] = '<![CDATA[' . $v . ']]>';
                        } else {
                            $images['img' . ($k + 1)] = '<![CDATA[' . C('PIC_HOST') . $v . ']]>';
                        }
                        if ($k >= 4) {
                            break;
                        }
                    }

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO([
                        'detail',
                        'Document',
                        $value['id'],
                    ])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['contentType'] = '<![CDATA[' . ($images ? 2 : 1) . ']]>';
                    $xml[$key]['pageView'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 合并图片
                    $xml[$key] = array_merge($xml[$key], $images);

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    if ($cate) {
                        $c = 1;
                        while ($c <= 3) {
                            $tcs[] = '<![CDATA[' . $cate['title'] . ']]>';
                            $cate = get_category_by_model($cate['pid'], null, $extend[$m][1]);
                            if (!$cate) {
                                break;
                            }
                            $c++;
                        }
                    }
                    krsort($tcs);
                    $t = 1;
                    foreach ($tcs as $tc) {
                        $xml[$key]['channel' . $t++] = $tc;
                    }

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        /*   文章问答    */
        if ($m == 'Wenda') {
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M('Document')->alias('m')->join($extend[$m][0] . " e ON m.id = e.id")->field('seo_keywords,view,description,title,m.id as id,wd_content,update_time,create_time,content,category_id')->where('m.status = 1 AND m.id > 113488')->limit($i * $sp, $sp)->order('m.id DESC')->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    // 正则抓取内容图片
                    //                    $matchs = $images = [];
                    //                    preg_match_all('/<img src=[\'"](.+?)[\'"]/i', $value['content'], $matchs);
                    //                    unset($matchs[0]);
                    //                    foreach ($matchs[1] as $k => $v) {
                    //                        if (if_url($v)) {
                    //                            $images['img' . ($k+1)] = '<![CDATA[' . $v. ']]>';
                    //                        }else{
                    //                            $images['img' . ($k+1)] = '<![CDATA[' . C('PIC_HOST') . $v. ']]>';
                    //                        }
                    //                        if ($k>=4) break;
                    //                    }

                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO([
                        'detail',
                        'Document',
                        $value['id'],
                    ])['title'] . ']]>';
                    $xml[$key]['question'] = '<![CDATA[' . $value['title'] . ']]>';

                    //问题描述
                    $wd_content = clean_str($value['wd_content']);
                    if (!empty($wd_content)) {
                        $xml[$key]['quDesc'] = '<![CDATA[' . msubstr($wd_content, 0, 60, "utf-8", false) . ']]>';
                    }

                    //最佳答案
                    $answer = clean_str($value['content']);
                    if (empty($answer)) {
                        continue;
                    }
                    $xml[$key]['bestAnswer'] = '<![CDATA[' . $answer . ']]>';

                    $xml[$key]['isAcceptAns'] = '<![CDATA[' . 0 . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['create_time']) . ']]>';
                    $xml[$key]['lastComment'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    //回答条数
                    $xml[$key]['answers'] = '<![CDATA[' . 1 . ']]>';

                    //所在分类
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    if ($cate) {
                        $xml[$key]['category'] = '<![CDATA[' . $cate['title'] . ']]>';
                    } else {
                        continue;
                    }

                    //关键词、可选
                    if ($value['seo_keywords']) {
                        $xml[$key]['m_seo_keywords'] = '<![CDATA[' . $value['seo_keywords'] . ']]>';
                    }
                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        /*   下载     */
        if ($m == 'Down') {
            $allow_system = [
                '',
                'android',
                'ios',
                'windows',
                'others',
            ];
            for ($i = 0; $i < $page; $i++) {
                $xml = $lists = [];
                $lists = M($m)->alias('m')->join("LEFT JOIN " . $extend[$m][0] . " e ON m.id = e.id")->field('description,title,m.id as id,update_time,category_id,smallimg,version,size,system,view')->where(['m.status' => 1])->limit($i * $sp, $sp)->order('m.id DESC')->select();
                if (empty($lists)) {
                    continue;
                }
                //if ($i == 0) S($skey, $lists[0]['id']); //缓存最大id

                // //手机版 暂时不生成手机版
                // foreach ($lists as $key => $value) {
                //     $m_url = staticUrlMobile('detail', $value['id'], $m);
                //     if ($m_url == $m_host) continue;
                //     $xml[$key]['loc'] = $m_url;
                //     $xml[$key]['lastmod'] = date("Y-m-d", $value['update_time']);
                //     $xml[$key]['changefreq'] = 'always';
                //     $xml[$key]['priority'] = 1;
                // }
                // $path = $base_file . '_m_' . $i . '.xml';
                // $m_sitemap[$i]['url'] = $m_host . $bd . '/' . $path;
                // $m_sitemap[$i]['time'] = date("Y-m-d");
                // $siteMapI->createXML('m', $m_root_path . $path, $xml);

                //PC版
                foreach ($lists as $key => $value) {
                    $p_url = staticUrl('detail', $value['id'], $m);
                    if ($p_url == $p_host || $p_url == $s_host) {
                        continue;
                    }
                    $xml[$key]['url'] = '<![CDATA[' . $p_url . ']]>';
                    $xml[$key]['title'] = '<![CDATA[' . WidgetSEO([
                        'detail',
                        'Down',
                        $value['id'],
                    ])['title'] . ']]>';
                    $xml[$key]['summary'] = '<![CDATA[' . $value['description'] . ']]>';
                    $xml[$key]['name'] = '<![CDATA[' . $value['title'] . ']]>';
                    $xml[$key]['publishDate'] = '<![CDATA[' . date("Y-m-d h:i:s", $value['update_time']) . ']]>';
                    $xml[$key]['icon'] = '<![CDATA[' . get_cover($value['smallimg'], 'path') . ']]>';
                    $xml[$key]['fileSize'] = '<![CDATA[' . ((int) $value['size'] / 1000) . 'M' . ']]>';
                    $xml[$key]['version'] = '<![CDATA[' . $value['version'] . ']]>';

                    // 操作系统
                    $value['system'] = ($value['system'] > 3 or empty($value['system'])) ? 4 : $value['system'];
                    $xml[$key]['os'] = '<![CDATA[' . $allow_system[$value['system']] . ']]>';

                    // 下载地址
                    $xml[$key]['downloadLink'] = '<![CDATA[' . getFileUrl($value['id']) . ']]>';

                    // 分类
                    $tcs = [];
                    $cate = get_category_by_model($value['category_id'], null, $extend[$m][1]);
                    $xml[$key]['category'] = '<![CDATA[' . $cate['title'] . ']]>';

                    // 下载次数
                    $xml[$key]['installation'] = '<![CDATA[' . ($value['view'] ? $value['view'] : rand(100, 200)) . ']]>';

                    // 标签
                    $ts = '';
                    $tags = get_tags($value['id'], strtolower($m), 'tags');
                    foreach ($tags as $tag) {
                        $ts .= $tag['title'] . ' ';
                    }
                    // 产品标签
                    $pro_tags = get_tags($value['id'], strtolower($m), 'product');
                    foreach ($pro_tags as $pro_tag) {
                        $ts .= $pro_tag['title'] . ' ';
                    }
                    $ts && $ts = substr($ts, 0, strlen($ts) - 1);
                    $xml[$key]["tags"] = '<![CDATA[' . $ts . ']]>';

                }

                $path = $base_file . '_p_' . $i . '.xml';
                $p_sitemap[$i]['url'] = $p_host . $bd . '/' . $path;
                $p_sitemap[$i]['time'] = date("Y-m-d");
                $siteMapI->createShenmaXML('p', $p_root_path . $path, $xml);
            }
        }

        //生成索引
        // $siteMapI->createSiteMap($m_root_path . $base_file . '.xml', $m_sitemap);
        $siteMapI->createSiteMap($p_root_path . $base_file . '.xml', $p_sitemap);
        $this->success('神马搜索结构化数据生成完成！', '', 5);
    }
}
