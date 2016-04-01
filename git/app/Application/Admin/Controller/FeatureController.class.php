<?php
// +----------------------------------------------------------------------
// leha modified
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台控制器
 * @author leha.com
 */

class FeatureController extends AdminController
{

    //文档模型名
    protected $document_name = 'Feature';
    protected $base_view = 'Feature';
    protected $main_title = '专题';
    protected $model_id = '11';
    protected $cate_name='FeatureCategory';
    protected $dir = array(
        'Feature' => 'FEATURE_ZT_DIR',
        'Batch' => 'FEATURE_ZQ_DIR',
        'Special' => 'FEATURE_K_DIR'
    );
    public $pages = array();

    public function __construct()
    {
        parent::__construct();
        $this->assign('main_title', $this->main_title);
    }

    /**
     * 列表
     * @author leha.com
     */
    public function index()
    {
        $pid = I('get.pid', 0);
        /* 获取列表（搜索） */
        $map = array('enabled' => array('gt', -1), 'pid' => $pid);
        if (isset($_GET['title'])) {
            $map['title'] = array('like', '%' . (string)I('title') . '%');
        }
        if (isset($_GET['enabled'])) {
            $map['enabled'] = I('enabled');
            $enabled = $map['enabled'];
        } else {
            $enabled = null;
            $map['enabled'] = array('in', '0,1,2');
        }
        if (isset($_GET['time-start'])) {
            $map['update_time'][] = array('egt', I('time-start'));
        }
        if (isset($_GET['time-end'])) {
            $map['update_time'][] = array('elt', date('Y-m-d H:i:s', 24 * 60 * 60 + strtotime(I('time-end'))));
        }
        if (isset($_GET['nickname'])) {
            $map['uid'] = M('Member')->where(array('nickname' => I('nickname')))->getField('uid');
        }
        //var_dump($map);//分页
        $p = $_GET['p'] ? $_GET['p'] : '1';
        $page = 10;
        $list = M($this->document_name)->where($map)->order('id desc')->page($p . ',' . $page)->select();
        $count = M($this->document_name)->where($map)->count(); // 查询满足要求的总记录数
        $Page = new \Think\Page($count, $page); // 实例化分页类 传入总记录数和每页显示的记录数
        $pagination = $Page->show(); // 分页显示输出
        $this->assign('pagination', $pagination);

        $this->assign('list', $list);
        foreach ($list as $k => $v) {
            if ($k) $id .= ',\'' . $v['id'] . '\'';
            else $id .= '\'' . $v['id'] . '\'';
        }
        if ($id) $kids = M($this->document_name)->where('pid in (' . $id . ')')->select();

        foreach ($kids as $k => $v) {
            $pids[$v['pid']][] = $v;
        }

        $this->assign('pids', $pids);
        $this->assign('module', C($this->dir[$this->document_name]));

        //var_dump($write);
        $this->meta_title = '专题管理';

        $this->display($this->base_view . ':' . strtolower(ACTION_NAME));
        //die('a');
    }

    public function trigger()
    { //触发更新WIDGET.JS
        $files = glob('./Application/Home/View/' . C('THEME') . '/' . $this->document_name . '/layout/*');
        $str = '';
        foreach ($files as $k => $v) {
            $str .= $v . ':' . str_replace('./Application/Home/View/' . C('THEME') . '/' . $this->document_name . '/layout/', '', $v) . "\r\n";
            if (is_dir($v)) {
                foreach (glob($v . '/*.html') as $k => $v) {
                    $str .= $v . ':' . str_replace('./Application/Home/View/' . C('THEME') . '/' . $this->document_name . '/layout/', '', $v) . "\r\n";
                }
            }
        }
        //die(var_dump($str));

        M('attribute')->where('name=\'layout\' and model_id=' . $this->model_id)->save(array('extra' => $str));
        //$js=file_get_contents(APP_PATH.'Admin/View/Feature/widget.js');
        /*
        $tags = M('product_tags')->select();
        foreach ($tags as $k => $v) {
            $details[$v['id']] = $v;
        }

        $maps=M('product_tags_map')->where(array('type'=>'down'))->select();

        $option='<option value="0">请选择专区</option>';
        foreach($maps as $k=>$v){
            $results[$v['tid']]=$details[$v['tid']]['title'];
        }

        foreach($results as $k=>$v){
            $option.="<option value=\"{$k}\">{$v}</option>";
        }
        $js=str_replace('{$option}',$option,$js);
        $write=file_put_contents('./Public/static/kindeditor/plugins/widget/widget.js',$js);
        */
        return true;
    }

    /**
     * 添加
     * @author leha.com
     */
    public function add()
    { //新增专题专区
        $this->trigger();
        $models = get_model_by('id', $this->model_id);
        $this->assign('models', $models);
        //获取表单字段排序
        $fields = get_attribute($models['id']);
        $this->assign('fields', $fields);
        $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $models['tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $models['product_tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $this->assign('tags_url', $tags_url);
        $this->assign('ifEdit', 'false');
        if (IS_POST) {
            $Feature = M($this->document_name);
            $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';

            if (preg_match_all($regular, $_POST['content'], $results)) {
                foreach ($results[1] as $k => $v) {
                    $widgets[$v][$k] = $results[2][$k];
                }
                $_POST['widget'] = isset($widgets['custom']) ? '' : json_encode($widgets);
            }
            $_POST['uid'] = UID; //赋值
            $_POST['position'] = $this->getPosition();//谭坚
            $data = $Feature->create();
            if ($data) {
                $id = $Feature->add();
                //die(var_dump($id));
                $this->parse($id, true);
                $data['id'] = $id;
                $this->after_insert($data);
                if ($_POST['label']) {
                    $this->batch_create($id);
                }
                if ($id) {
                    $this->success('新增成功', U('index'), $id);
                    //记录行为
                    action_log('update_Feature', 'Feature', $id, UID);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Feature->getError());
            }
        } else {
            $pid = I('get.pid', 0);
            //获取父节点
            if (!empty($pid)) {
                $parent = M($this->document_name)->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $categorys = M('FeatureCategory')->field('id,name,title')->where(array('pid' => '0'))->select();
            $this->assign('cateTable',$this->cate_name);
            $this->assign('categorys', $categorys);
            $this->assign('pid', $pid);
            $this->assign('info', null);
            $this->meta_title = '新增';
            $this->display($this->base_view . ':edit');
        }
    }

    public function success($message, $jumpUrl = '', $id = 0)
    {
        $status = 1;
        $this->pages || $this->pages = S($this->document_name . 'PageNumber');
        //die(var_dump($this->document_name.'PageNumber','----',$this->pages));
        if (count($this->pages)) foreach ($this->pages as $k => $v) {
            for ($i = 0; $i < $v; $i++) {
                $this->parse($k, true, $i + 1);
                if(C('THEME') !== 'gfw'){
                    if ($i > 50) break; //分页的最大个数，不需要限制的请注释掉这一下。
                }
            }
        }

        if (IS_AJAX) {
            // AJAX提交
            $data = is_array($ajax) ? $ajax : array();
            $data['info'] = $message;
            $data['status'] = $status;
            $data['url'] = $jumpUrl;
            $this->ajaxReturn($data);
        }

        if (!empty($jumpUrl)) $this->assign('jumpUrl', $jumpUrl);
        // 提示标题
        $this->assign('msgTitle', $status ? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if ($this->get('closeWin')) $this->assign('jumpUrl', 'javascript:window.close();');
        $this->assign('status', $status); // 状态
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON', false);
        if ($status) { //发送成功信息

            $this->assign('message', $message); // 提示信息
            // 成功操作后默认停留1秒
            if (!isset($this->waitSecond)) $this->assign('waitSecond', '1');
            // 默认操作成功自动返回操作前页面
            if (!isset($this->jumpUrl)) $this->assign("jumpUrl", $_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }
    }

    /**
     * 编辑
     * @author leha.com
     */
    public function edit($id = 0)
    {
        // 代码优化 @Author 肖书成
        if(IS_GET){
            $this->trigger();
            $id = $id ? $id : I('id');
            $models = get_model_by('id', $this->model_id);
            $this->assign('models', $models);
            //获取表单字段排序
            $fields = get_attribute($models['id']);
            $this->assign('fields', $fields);
            $this->assign('cateTable',$this->cate_name);

            //标签挂载处理,U方法构造ajax地址
            $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $models['tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
            $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $models['product_tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
            $this->assign('tags_url', $tags_url);
            $this->assign('ifEdit', 'true');
        }

        if (IS_POST) {
            unset($_POST['parse']);
            $Feature = M($this->document_name);
            $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';

            if ($_POST['label']) {
                if ($id) $Feature->where(array('pid' => $id))->delete();
                $this->batch_create($id);
            }
            if (preg_match_all($regular, $_POST['content'], $results)) {
                foreach ($results[1] as $k => $v) {
                    $widgets[$v][$k] = $results[2][$k];
                }
                $_POST['widget'] = isset($widgets['custom']) ? '' : json_encode($widgets);
            }
            //$_POST['uid'] = UID;//赋值
            $_POST['position'] = $this->getPosition();
            $_POST['home_position'] = $this->getHomePosition();
            $data = $Feature->create();
            //var_dump($data);

            if ($data) {
                $this->after_update($data);
                if ($Feature->save() !== false) {
                    //die(var_dump($data));
                    //记录行为
                    action_log('update_' . $this->document_name, $this->document_name, $data['id'], UID);
                    $this->parse($data['id'], true);
                    //专题、专区、k页面时时推送百度sitemap  add  by 谭坚
                    if (C('BAIDU_SITEMAP_POST_SWITCH')) {
                        //只有启用的时候推送
                        if ($data['enabled']) {
                            $id = $id ? $id : $data['id'];
                            $sitemap_data['time'] = date("Y-m-d", time());
                            $siteMapI = new \Common\Library\SiteMapLibrary();
                            //PC版本
                            if (!$data['interface']) {
                                $sitemap_data['url'] = $this->getPath($id, $this->document_name);
                                $result = $siteMapI->post('p', array($sitemap_data));
                            } else {
                                //手机版
                                $sitemap_data['url'] = $this->getPath($id, $this->document_name);
                                $result = $siteMapI->post('m', array($sitemap_data));
                            }
                            $maplog['url'] = $sitemap_data['url'];
                            $maplog['update_time'] = time();
                            $maplog['status'] = $result['success'] ? '200' :  ($result['error']? $result['error'] : '404');
                            $sql = "SHOW TABLES LIKE '". C('DB_PREFIX')."api_sitemap_log'";
                            $res = M('Model')->query($sql);
                            if(count($res) > 0)
                            {
                                 M('api_sitemap_log')->add($maplog);//记录时时推送日志
                            }
                        }
                    }
                    $this->success('编辑成功', U('index'));
                } else {
                    $this->error('编辑失败');
                }

            } else {
                $this->error($Feature->getError());
            }
        } else {
            $data = array();
            /* 获取数据 */
            $data = M($this->document_name)->find($id);
            $data['content'] = strtr($data['content'], array('<textarea>' => '&lt;textarea&gt;', '</textarea>' => '&lt;/textarea&gt;'));

            if (false === $data) {
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父节点
            if (!empty($pid)) {
                $parent = M($this->document_name)->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $categorys = M('FeatureCategory')->field('id,name,title')->where(array('pid' => '0'))->select();
            //die(var_dump($categorys));
            $category_array = $this->getCategoryIds($data['category_id']);
            $this->assign('category_array',$category_array);
            $this->assign('categorys', $categorys);
            $this->assign('pid', $pid);
            $this->assign('data', $data);
            $this->meta_title = '编辑';
            $this->display($this->base_view . ':' . strtolower(ACTION_NAME));
        }
    }


    /**
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getPosition(){
        $position = I('post.position');
        if(!is_array($position)){
            return 0;
        }else{
            $pos = 0;
            foreach ($position as $key=>$value){
                $pos += $value;		//将各个推荐位的值相加
            }
            return $pos;
        }
    }
    /**
     * 生成推荐位的值
     * @return number 推荐位
     * @author huajie <banhuajie@163.com>
     */
    protected function getHomePosition(){
        $position = I('post.home_position');
        if(!is_array($position)){
            return 0;
        }else{
            $pos = 0;
            foreach ($position as $key=>$value){
                $pos += $value;		//将各个推荐位的值相加
            }
            return $pos;
        }
    }
    /**
     * 描述：递归获取各级分类id
     * @param $category_id
     * @return array|bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected function getCategoryIds($category_id)
    {
        static $category_id_arr = array();
        if(!is_numeric($category_id)) return false;
        $map['id'] = $category_id;
        $rs = M('FeatureCategory')->field('id,pid')->where($map)->find();
        if($rs['pid'] == "0"  || empty($rs['pid']))
        {
            $category_id_arr[] = $category_id;
        }
        else
        {
            $category_id_arr[] = $category_id;
            $this->getCategoryIds($rs['pid']);
        }
        return array_reverse($category_id_arr);
    }


    public function mobile($id = 0)
    { //手机版
        $id = $id ? $id : I('id');
        $models = get_model_by('id', $this->model_id);
        $this->assign('models', $models);
        //获取表单字段排序
        $fields = get_attribute($models['id']);
        $this->assign('fields', $fields);

        //标签挂载处理,U方法构造ajax地址
        $tags_url['Tags'] = U('api/getTagsJson?type=Tags&category=' . $models['tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $tags_url['ProductTags'] = U('api/getTagsJson?type=ProductTags&category=' . $models['product_tags_category'] . '&did=' . $id . '&maptype=' . $this->document_name);
        $this->assign('tags_url', $tags_url);
        $this->assign('ifEdit', 'true');

        if (IS_POST) {
            $Feature = M($this->document_name);
            $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';

            if (preg_match_all($regular, $_POST['content'], $results)) {
                foreach ($results[1] as $k => $v) {
                    $widgets[$v] = $results[2][$k];
                }
                $_POST['widget'] = json_encode($widgets);
            }
            $_POST['position'] = $this->getPosition();
            $_POST['home_position'] = $this->getHomePosition();
            $data = $Feature->create();
            if ($data) {
                $id = $Feature->add();
                //die(var_dump($id));
                $this->parse($id, true);
                $data['id'] = $id;
                $this->after_insert($data);
                if ($_POST['label']) {
                    $this->batch_create($id);
                }
                if ($id) {
                    $this->success('新增成功', U('index'), $id);
                    //记录行为
                    action_log('update_Feature', 'Feature', $id, UID);
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Feature->getError());
            }
        } else {
            $data = array();
            /* 获取数据 */
            $data = M($this->document_name)->find($id);
            unset($data['id']);
            $data['interface'] = '1'; //触屏版
            $data['layout'] = str_replace('/zq', '/sjzq', $data['layout']);
            $data['content'] = file_get_contents($data['layout']);
            if ($data['content']) {
                $data['content'] = strtr($data['content'], array('<textarea>' => '&lt;textarea&gt;', '</textarea>' => '&lt;/textarea&gt;'));
            } else {
                $data['layout'] = '';
            }

            if (false === $data) {
                $this->error('获取配置信息错误');
            }

            $pid = I('get.pid', 0);
            //获取父节点
            if (!empty($pid)) {
                $parent = M($this->document_name)->where(array('id' => $pid))->field('title')->find();
                $this->assign('parent', $parent);
            }
            $categorys = M('FeatureCategory')->field('id,name,title')->where(array('pid' => '0'))->select();
            //die(var_dump($categorys));

            $this->assign('categorys', $categorys);
            $this->assign('pid', $pid);
            $this->assign('data', $data);
            $this->meta_title = '编辑';
            $this->display($this->base_view . ':edit');
            $this->trigger();
        }
    }

    public function batch_create($id)
    { //专区专用
        //unset($_POST['tag_id']);
        $temps = $_POST;
        //$temps['widget']='';
        $temps['pid'] = $id;
        unset($temps['id']);
        parse_str($_POST['label'], $widgets);
        unset($temps['label']);
        foreach ($widgets as $k => $v) {
            $temps['enabled'] = 1;
            $temps['title'] = $_POST['title'] . $k;
            $temps['layout'] = $_POST['layout'] . '/' . $v . '.html';
            $content = file_get_contents($temps['layout']);
            $regular = '/<include file="([^"]+)"\/>/is';

            if (preg_match_all($regular, $content, $results)) {
                $file = str_replace('@', '/View/', $results[1][0]); //头部友好显示
                $content = str_replace($results[0][0], file_get_contents('./Application/' . $file . '.html'), $content);
                //var_dump($results,$file);
            }

            $configs = C('TMPL_PARSE_STRING');

            $temps['url_token'] = $_POST['url_token'] . $v.'/';
            $temps['content'] = str_replace('__PUBLIC__', $configs['__PUBLIC__'], $content);

            if (preg_match_all('/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is', $temps['content'], $matches)) {
                foreach ($matches[1] as $k => &$vv) {
                    $fragments[$vv] = $results[2][$k];
                }
                $temps['widget'] = json_encode($fragments);
                $_POST['widget'] = isset($fragments['custom']) ? '' : json_encode($fragments);
            }

            //die(var_dump($temps));
            $result = M($this->document_name)->add($temps);
            $_POST['id'] = $result;
            $this->after_insert($_POST);

            if ($result) $this->parse($result, true);
        }
    }

    public function create()
    {
        if (I('cmd')) //命令存入文件，命令行运行生成
        {
            createHtml(ACTION_NAME);
            parent::success('系统正在后台生成，请稍后查看生成结果！');
        } elseif(I('where')){
            set_time_limit(600);
            $lists = M($this->document_name)->where('enabled = 1 AND '.I('where'))->select();
            if (is_array($lists)) {
                foreach ($lists as &$v) {
                    $this->parse($v['id'], true);
                }
            }
            action_log('create' . $this->document_name, $this->document_name, $id, UID);
            set_time_limit(30);
            $this->success('全部生成完毕');
        }else {
            set_time_limit(0);
            $lists = M($this->document_name)->where(array('enabled' => '1'))->select();
            if (is_array($lists)) {
                foreach ($lists as &$v) {
                    $this->parse($v['id'], true);
                }
            }
            action_log('create' . $this->document_name, $this->document_name, $id, UID);
            $this->success('全部生成完毕');
        }
    }

    /**
     * 描述：获取专区、专题、k页面的地址
     * @param $id
     * @param $m
     * @param bool $write
     * @param null $page
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getPath($id, $m, $write = true, $page = null)
    {
        $data = M($m)->find($id);
        //修改生成地址为基于静态root地址
        //@author crohn
        //@date 2017-12-23
        $static = $data['interface'] ? C('MOBILE_STATIC_URL') . '/' : C('STATIC_URL') . '/';
        $path = $static . C($this->dir[$m]) . ($data['url_token']{0} !== '/' ? '/' . $data['url_token'] : $data['url_token']);
        if ($write) {
            if (substr($path, -1) !== '/') $path .= '/';
            if ($page) {
                $path .= str_replace('{page}', $page, C('FEATURE_PAGE'));
            } else {
                $path .= 'index.html';
            }
        }
        return $path;
    }

    /**
     * 生成
     * @author leha.com
     */
    public function flush($id)
    {
        $data = M($this->document_name)->find($id);

        if (!empty($data['enabled'])) {
            $this->parse($id, true);
            $s = 1;
        }
        $pids = M($this->document_name)->where(array('pid' => $id))->select();
        if (is_array($pids)) foreach ($pids as &$v) {
            if (empty($v['enabled'])) {
                continue; //禁用跳过不生成
            }
            $this->parse($v['id'], true);
        }
        //记录行为
        action_log('flush_Feature', 'Feature', $id, UID);
        if ($s == 1) {
            $this->success('刷新成功');
        }
    }

    /**
     * 删除
     * @author leha.com
     */
    public function remove()
    {
        $id = array_unique((array)I('id', 0));

        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id));
        if (M($this->document_name)->where($map)->delete()) {
            //记录行为
            action_log('update_Feature', 'Feature', $id, UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    //重构父类delete,resume,forbid
    protected function delete($model, $where = array(), $msg = array('success' => '删除成功！', 'error' => '删除失败！'))
    {
        $data['enabled'] = -1;
        $data['status'] = -1;
        $this->editRow($model, $data, $where, $msg);
    }

    protected function resume($model, $where = array(), $msg = array('success' => '状态恢复成功！', 'error' => '状态恢复失败！'))
    {
        $data = array('enabled' => 1);
        $data['status'] = 1;
        $this->editRow($model, $data, $where, $msg);
    }

    protected function forbid($model, $where = array(), $msg = array('success' => '状态禁用成功！', 'error' => '状态禁用失败！'))
    {
        $data = array('enabled' => 0);
        $data['status'] = 0;
        $this->editRow($model, $data, $where, $msg);
    }

    //模版解析
    public function parse($id, $write = false, $page = null)
    {
        //die(var_dump(strtr('aaaaaaa',array('aa'=>'1','a'=>'2'))));
        $_GET['p'] = $page ? $page : $_GET['p'];

        $data = M($this->document_name)->find($id);
        $data['content'] = html_entity_decode($data['content']);
        $data['layout'] = strpos($data['layout'], 'htm') ? $data['layout'] : $data['layout'] . '/index.html';
        //var_dump($data['widget']);

        $layout = array(
            './Application/Home/View/7230/Special/layout/hjzt.html',
            './Application/Home/View/7230/Feature/layout/hjzt.html'
        );
        $themes = array('gfw');
        $theme  = C('THEME');
        if(in_array($theme,$themes) || in_array($data['layout'],$layout)){
            $content = $data['content'];
        }else{
            $content = $data['widget'] ? file_get_contents($data['layout']) : $data['content'];
            $content = $content ? $content : $data['content'];
        }
        //die($content);
        $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';

        if (preg_match_all($regular, $content, $results)) { //匹配挂件区域
            $widgets = json_decode($data['widget'], true);
            if (isset($widgets['custom'])) unset($widgets);
            /*
             * 优先widget目录文件解析，无文件则解析数据行，无数据库行(row)则提取layout片断。
             */

            foreach ($results[1] as $k => $v) {
                if (file_exists($file = './Application/Home/View/' . C('THEME') . '/' . $this->document_name . '/widget/' . $v . '.htm')) {
                    //widget文件存在
                    $content = str_replace($results[0][$k], file_get_contents($file), $content);
                } elseif ($widgets[$v]) {
                    //数据源widget存在
                    if(is_array($widgets[$v])){
                        $content=str_replace_once($results[0][$k],$widgets[$v][$k],$content);
                    }else{
                        $content=str_replace($results[0][$k],$widgets[$v],$content);
                    }
                } else {
                    //都不存在
                    $content = str_replace($results[0][$k], $results[2][$k], $content);
                }
                $this->view->assign($v, R('Home/' . getWName(C('THEME')) . strtolower($this->document_name) . '/' . $v, array('id' => $id), 'Widget'));
            }
        }

        $details = explode('<hr />', $content);
        if (isset($details[1])) {
            if(in_array($theme,$themes)){
                $content = '<!DOCTYPE html>
<html>
<head>
'. $details[0] . '
</head>
<body>' . $details[1] . '
</body>
</html>';
            }else{
                $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        	<html xmlns="http://www.w3.org/1999/xhtml"><head>' . $details[0] . '</head><body>' . $details[1] . '</body></html>';
            }
        } else {
            //do nothing
        }

        //$this->view->display($data['layout']);
        if ($content) $content = $this->view->fetch('', $content); //解析全部内容

        if (preg_match('/<br\s+totalPageNumber="(\d+)"\s+style="display:none">/is', $content, $pages)) {
            $total = $pages[1];
            $this->pages[$id] = $total;
            $content = str_replace($pages[0], '', $content);
        }

        //处理图片
        $content = str_replace('src="Uploads', 'src="/Uploads', $content);

        //die(var_dump($pages));

        /*SEO元素替换*/
        /*$content=preg_replace("/<title>(.*?)<link/is",
                          "<title>{$data['seo_title']}</title>
                          <meta name=\"keywords\" content=\"{$data['keywords']}\" />
                          <meta name=\"description\" content=\"{$data['description']}\" /><link",$content);*/


        /*$content='<?php var_dump($_GET);$_GET["p"]=2;?>'.$content;*/
        //die('=========='.$this->view->fetch('',$content));
        //$path=getcwd().'/Public/static'.$data['url_token'];
        //修改生成地址为基于静态root地址 
        //@author crohn
        //@date 2017-12-23
        $static = $data['interface'] ? C('MOBILE_STATIC_ROOT') : C('STATIC_ROOT');
        $path = $static . '/' . C($this->dir[$this->document_name]) . ($data['url_token']{0} !== '/' ? '/' . $data['url_token'] : $data['url_token']);

        //var_dump($_GET);
        if ($write) {
            if (substr($path, -1) !== '/') $path .= '/';
            dir_create($path);
            if ($page) {
                $path .= str_replace('{page}', $page, C('FEATURE_PAGE'));
            } else {
                $path .= 'index.html';
            }
            //die(var_dump($_GET,$id));
            //dir_create($path)&&file_put_contents((substr($path, -1) !=='/' ? $path.'/' : $path).'index.html',$content);
            file_put_contents($path, $content);
        } else {
            die($content); //调试用 显示解析内容
        }
    }

    function ids($ids)
    {
        //var_dump($ids);
        $id = implode('\',\'', $ids);
        $lists = M('down')->where('id in (\'' . $id . '\')')->limit('30')->select();

        foreach ($lists as $k => &$v) {
            $v = $v + M('down_dmain')->where('id=' . $v['id'])->find();
            $v['size'] = round($v['size'] / 1024, 2);
            $vv = M('down_address')->where('did=' . $v['id'])->find();
            if (is_array($vv)) $v += $vv;
        }
        return $lists;
    }

    function cats($ids)
    {
        //var_dump($ids);
        $id = implode('\',\'', $ids);
        $lists = M('down')->where('category_id in (\'' . $id . '\')')->limit('30')->select();

        foreach ($lists as $k => &$v) {
            $v = $v + M('down_dmain')->where('id=' . $v['id'])->find();
            $v['size'] = round($v['size'] / 1024, 2);
            $vv = M('down_address')->where('did=' . $v['id'])->find();
            if (is_array($vv)) $v += $vv;
        }
        return $lists;
    }

    public function collect()
    {
        //die('仅供抓数据导入用');
        set_time_limit(0);
        $hrefs = M($this->document_name)->where(array('content' => ''))->select();
        $Feature = M($this->document_name);
        foreach ($hrefs as $v) {
            $content = iconv('GBK', 'UTF-8', file_get_contents('http://www.7230.com/' . C($this->dir[$this->document_name]) . '/' . $v['url_token']));
            //die('http://www.7230.com/'.C($this->dir[$this->document_name]).'/'.$v['url_token']);
            preg_match('/<title>(.+)<\/title>/isU', $content, $out);
            preg_match_all('/<meta\s+name="(\w+)"\s+content="([^"]+)"/is', $content, $keywords);
            //preg_match('/<meta\s+name="description"\s+content="([^"]+)"/is',$content,$description);
            $features['seo_title'] = $out[1];
            $features['keywords'] = 'keywords' == $keywords[1][0] ? $keywords[2][0] : $keywords[2][1];
            $features['description'] = 'description' == $keywords[1][1] ? $keywords[2][1] : $keywords[2][0];
            //$features['url_token']='/z'.$v['url_token'];
            $features['content'] = $content;
            //die(var_dump($features));
            $result = $Feature->where(array('id' => $v['id']))->save($features);
            $rand = rand(1, 5);
            usleep(5000 * $rand);
        }
        die(var_dump($result));
    }

    function checked($items)
    {
        var_dump($items, func_get_args());
        return $items;
    }

    public function __lists($id)
    {
        $_GET['content'] = '';
        $result = M($this->document_name)->where("id='{$id}'")->data($_GET)->save();
        //die(var_dump($result,$id));
        $content = file_get_contents('http://www.7230.com/k/wlyz');
        $content = iconv('GBK', 'UTF-8', $content);
        $content = str_replace('gb2312', 'utf-8', $content);

        $content = preg_replace('/href="\/css\/([^"]+)"/is', 'href="/public/Home/7230/css/old/$1"', $content);
        $content = preg_replace('/href="\/(sk[^"]+)"/is', 'href="/public/Home/7230/css/old/$1"', $content);
        $content = preg_replace('/href="(sk[^"]+)"/is', 'href="/public/Home/7230/css/old/$1"', $content);
        $content = preg_replace('/"text\/javascript" src="([^"]+)"/is', '"text/javascript" src="__PUBLIC__/Home/7230/js/$1"', $content);
        $content = preg_replace('/<dd id="klist">(.*?)<\/dd>/is', '<div class="dragable" widget="down_category"><dd id="klist">$1</dd></div>', $content);
        $content = preg_replace('/<ul([^>]+)>(.*?)<\/ul>/is', '<div class="dragable" widget="product_tags"><ul$1>$2</ul>', $content);
        $content = strtr($content, array('js//' => 'js/', '"/up/' => '"http://pic.7230.com/up/'));
        var_dump($content);
    }

    public function pages($count, $row, $id)
    {
        //die('--------------');
        $Page = new \Think\Page($count, $row, '', false); // 实例化分页类 指定路径规则
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '尾页');
        $show = $Page->show(); // 分页显示输出
        $this->assign('page', str_replace('end', '"end" end=' . $count, $show)); // 赋值分页输出
    }

    public function kpage($id)
    {
        //die('转换原K页面数据适应现有结构！时间较长，请运行且只运行一次。');
        set_time_limit(0);
        if ($id) $and = 'and id=' . $id;
        $contents = M($this->document_name)->field('id,url_token,content')->where(' id>0 ' . $and)->select();
        foreach ($contents as $v) {
            //$content=iconv('GBK','UTF-8',file_get_contents('http://www.7230.com'.$v['url_token']));

            $content = $v['content'];
//	  $content=iconv('GBK','UTF-8',$content);
//      $content=preg_replace('/(<img id="banner"[^>]+>)/is','<div class="dragable" widget="image">$1</div>',$v['content']);

            //$content=iconv('GB2312','UTF-8',$content);
            $content = str_replace('gb2312', 'utf-8', $content);

            $content = preg_replace('/href="\/css\/([^"]+)"/is', 'href="/Public/Home/7230/css/old/$1"', $content);
            $content = preg_replace('/href="\/(sk[^"]+)"/is', 'href="/Public/Home/7230/css/old/$1"', $content);
            $content = preg_replace('/href="(sk[^"]+)"/is', 'href="/Public/Home/7230/css/old/$1"', $content);
            /*
            $content=preg_replace('/"text\/javascript" src="([^"]+)"/is','"text/javascript" src="__PUBLIC__/Home/7230/js/$1"',$content);
            //$content=preg_replace('/<dd id="klist">(.*?)<\/dd>/is','<div class="dragable" widget="down_category"><dd id="klist">$1</dd></div>',$content);
            //$content=preg_replace('/<ul([^>]+)>(.*?)<\/ul>/is','<div class="dragable" widget="product_tags"><ul$1>$2</ul>',$content);
            */
            $content = strtr($content, array('js//' => 'js/', '"/up/' => '"http://pic.7230.com/up/', '"com.css"' => '"/Public/Home/7230/css/old/com.css"'));
            //$content=strtr($content,array('"/up/'=>'"http://pic.7230.com/up/'));

            //$content=preg_replace('/<div id="container"(.*?)<script/is','<div class="dragable" widget="recommends"><div id="container"$1</div><script',$content);
            $content = preg_replace('/<b>最新专题<\/b>(.*?)<\/dd>/is', '<div class="dragable" widget="__feature"><b>最新专题</b><p><volist name="__feature" id="v"><a href="/z/{:$v.url_token}">{$v.title}</a></volist>$1</p></dd></div>', $content);

            $data['content'] = $content;
            $result = M($this->document_name)->where("url_token='{$v['url_token']}'")->data($data)->save();
        }
        die(var_dump($content, $result));
    }


    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function after_insert(&$data)
    {
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);
        //no hook
        //$this->hook(__FUNCTION__,$data);
    }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function after_update(&$data)
    {
        //die(var_dump($data['id']));
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);
        //no hook
        //$this->hook(__FUNCTION__, $data);
    }

    /*下面是所有挂件要调用的方法
     				[{'id':'down__product_tags','title':'下载产品标签'},
		            {'id':'down__tags','title':'下载标签'},
		            {'id':'down__down_category','title':'下载分类'},
		            {'id':'feature__product_tags','title':'专题产品标签'},
		            {'id':'feature__tags','title':'专题标签'},
		            {'id':'special__product_tags','title':'K页面产品标签'},
		            {'id':'special__tags','title':'K页面产品标签'},
		            {'id':'batch__product_tags','title':'专区产品标签'},
		            {'id':'batch__tags','title':'专区标签'}];
     */
    public function __call($method, $args)
    {
        //die(var_dump($args));
        $in = $args[0];
        $models = explode('__', $method);
        $maps = M($models[1] . '_map')->field('did')->where('tid in (\'' . $in . '\')')->limit('300')->select();
        foreach ($maps as $k => $v) {
            if ($k) $did .= '\',\'' . $v['did'];
            else $did .= $v['did'];
        }
        $lists = $this->$models[0]($did);
        return $lists;
    }

    /*************************************下载数据源********************************/
    function down($in)
    {
        if (empty($in)) {
            return false;
        }

        //查询下载基础表的基础数据
        $lists  =   M('Down')->field('id,title name,category_id,model_id,cover_id,description,update_time,abet,smallimg,previewimg,create_time,update_time')->where("`status` = 1 AND id IN('$in')")->select();
        if(empty($lists)){
            return false;
        }

        //查询下载分类
        $cate   =   M('DownCategory')->where('status = 1')->getField('id,title');

        //查找下载的附属模型
        $model_ids  =   array_unique(array_column($lists,'model_id'));
        $model      =   M('Model')->where('id IN('.implode(',',$model_ids).') AND name !="paihang"')->getField('id,name');

        //根据数据的附属模型产生数据分支
        $arr        =   array();
        $arr_ids    =   array();
        $ids        =   array();
        foreach($lists as $k=>$v){
            foreach($model as $k1=>$v1){
                if($v['model_id'] == $k1){
                    $v['title']         =   $cate[$v['category_id']];
                    $arr[$v1][] =   $v;
                    if($arr_ids[$v1]){
                        $arr_ids[$v1]   .=  ','.$v['id'];
                        $ids[]                  =   $v['id'];
                    }else{
                        $arr_ids[$v1]   =   $v['id'];
                        $ids[]                  =   $v['id'];
                    }
                }
            }
        }

        //查找附属模型的数据
        $modelData  =   array();
        foreach($arr_ids as $k=>$v){
            $modelData[$k]  =   M('Down'.ucfirst($k))->where("id IN($v)")->getField('id,content,size,system,licence,version,language,network,company_id');
        }

        //查找厂商数据
        $company    =   M('Company')->where('status = 1')->getField('id,name');

        //查找预定义站点
        $preset_site    =   M('PresetSite')->getField('id,site_name');

        //查找下载地址
        $address        =   M('DownAddress')->field('did,url,hits,site_id')->where("did IN(".implode(',',$ids).")")->select();
        $address2       =   array();
        foreach($address as $k => $v){
            $v['title'] =   $preset_site[$v['site_id']];
            $v['url']   =   formatAddress($v['url'], $v['site_id']);
            $address2[$v['did']][]  =   $v;
        }

        //基础数据和附属数据相互融合
        $lists  =   array();
        foreach($arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($modelData[$k][$v1['id']]){
                    if($modelData[$k][$v1['id']]['company_id'] > 0){
                        $v1['company']  =   $company[$modelData[$k][$v1['id']]['company_id']];
                    }else{
                        $v1['company']  =   '未知';
                    }
                    if($address2[$v1['id']]){
                        $v1['downAll']  =   $address2[$v1['id']];
                        $v1['url']      =   $address2[$v1['id']][0]['url'];
                    }
                    $v1['model']        =   $k;
                    $lists[]        =   array_merge($v1,$modelData[$k][$v1['id']]);
                }
            }
        }


        foreach ($lists as $k => &$v) {
            $v['previewimg'] = explode(',', $v['previewimg']);
            $v['size'] = is_numeric($v['size']) ? format_size($v['size']) : $v['size'];

            $v['tags'] = get_tags($v['id'],'Down');
            /***新功能扩展，增加安卓和苹果版***/
            $product    = get_tags($v['id'],'Down','product')[0]['id'];
            if($v['system']=='1'){
                $v['adr'] = $v['id'];
                $v['adrSize'] = $v['size'];
                if(empty($product)) continue;
                $data = M('down')->alias('a')->field('a.id,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where("a.status = 1 AND b.system = 2 AND c.tid = $product AND c.type='down'")->order('a.id DESC')->find();
                $v['ios'] = $data['id'];
                $v['iosSize'] = is_numeric($data['size'])?format_size($data['size']):$data['size'];
            }else{
                $v['ios'] = $v['id'];
                $v['iosSize'] = $v['size'];
                if(empty($product)) continue;
                $data = M('down')->alias('a')->field('a.id,b.size')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where("a.status = 1 AND b.system = 1 AND c.tid = $product AND c.type='down'")->order('a.id DESC')->find();
                $v['adr'] = $data['id'];
                $v['adrSize'] = is_numeric($data['size'])?format_size($data['size']):$data['size'];;
            }
        }
        unset($v); //foreach($lists as $k=>&$v) 循环中用了&$v传址,所以unset一下，避免下次使用变量$v发生异常
        return $lists;
    }

    function down__down_category($in)
    {
        //return $lists;
        $maps = M('Down')->field('id')->where('status = 1 AND category_id in (\'' . $in . '\')')->limit('600')->select();
        $did  = '';
        foreach ($maps as $k => &$v) {
            if ($k) $did .= '\',\'' . $v['id'];
            else $did .= $v['id'];
        }
        $lists = $this->down($did);
        return $lists;
    }

    /*************************************专题数据源********************************/
    function feature($in)
    {
        $lists = M(__FUNCTION__)->where('id in (\'' . $in . '\')')->limit('50')->order('id desc')->select();
        $dir    =   C('FEATURE_ZT_DIR');
        $dir    =   $dir?'/'.$dir.'/':'/';
        foreach ($lists as $k =>&$v) {
            $url        = $dir . (substr($v['url_token'],0,1) == '/'?substr($v['url_token'],1):$v['url_token']);
            $v['url']   = substr($url,-1,1) == '/' ? $url:$url.'/';
        }unset($v);
        return $lists;
    }

    /*************************************专区数据源********************************/
    function batch($in)
    {
        $lists = M(__FUNCTION__)->where('id in (\'' . $in . '\') AND pid = 0')->limit('30')->order('id desc')->select();
        $dir    =   C('FEATURE_ZQ_DIR');
        $dir    =   $dir?'/'.$dir.'/':'/';
        foreach ($lists as $k =>&$v) {
            $url        = $dir . (substr($v['url_token'],0,1) == '/'?substr($v['url_token'],1):$v['url_token']);
            $v['url']   = substr($url,-1,1) == '/' ? $url:$url.'/';
        }unset($v);
        return $lists;
    }

    /*************************************K页面数据源********************************/
    function special($in)
    {
        $lists = M(__FUNCTION__)->where('id in (\'' . $in . '\')')->limit('50')->order('id desc')->select();
        $dir    =   C('FEATURE_K_DIR');
        $dir    =   $dir?'/'.$dir.'/':'/';
        foreach ($lists as $k =>&$v) {
            $url        = $dir . (substr($v['url_token'],0,1) == '/'?substr($v['url_token'],1):$v['url_token']);
            $v['url']   = substr($url,-1,1) == '/' ? $url:$url.'/';
        }unset($v);
        return $lists;
    }

    function lists($id)
    {
        R($this->document_name . '/__lists', array('id' => $id));
        //$this->__lists($id);
    }
}