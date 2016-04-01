<?php
// +----------------------------------------------------------------------
// |  模板生成类 
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Common\Library;

/**
 * 模板生成类
 */
class TempCreateLibrary
{
    //数据模型
    protected $model = 'Document';
    //模块
    protected $module = 'Document';
    //分类模型
    protected $category = 'Category';
    //静态生成的CURL访问URL
    protected $static_url = 'http://localhost/';
    //静态生成的根文件夹
    public $static_root_dir = './Static';
    //静态文件后缀
    public $ext = '';
    //静态文件生成访问入口文件
    protected $static_php = 'index.php';

    //挂载其他主题时的固定生成路径
    public $the_lists_path = '';
    public $the_detail_path = '';
    public $the_moduleindex_path = '';

    //生成静态时的主题参数
    protected $theme = '';

    //列表页最大生成页数
    protected $lists_maxpage = '';

    /**
     * 构造函数
     *
     * @param array $params 参数
     * @return void
     */
    public function __construct($params = array())
    {
        //设置生成静态根目录
        $this->static_root_dir = $params['static_root_dir'] ? $params['static_root_dir'] : C('STATIC_ROOT');
        //传入参数设置
        $params['model'] && $this->model = $params['model'];
        $params['module'] && $this->module = $params['module'];
        $params['category'] && $this->category = $params['category'];
        $params['static_url'] && $this->static_url = $params['static_url'];

        $static_file_ext = C('STATIC_FILE_EXT');
        $this->ext = empty($static_file_ext) ? '.html' : $static_file_ext;
    }

    /**
     * 已存在属性设置
     *
     * @param string $k
     * @param string $v
     * @return void
     */
    public function setProperty($k, $v = null)
    {
        if (is_array($k)) {
            foreach ($k as $k_t => $v_t) {
                $this->setProperty($k_t, $v_t);
            }
        } else {
            if (isset($this->$k)) {
                $this->$k = $v;
            } else {
                return false;
            }
        }
    }

    /**
     * 模块静态文件夹处理
     *
     * @param string $path 路径
     * @return void
     */
    protected function moduleDir($path)
    {
        $DIR = C(strtoupper($this->module) . '_DIR');
        if ($DIR) {
            $DIR = substr($DIR, -1) != '/' ? $DIR . '/' : $DIR;
            $path = $DIR . $path;
        }
        return str_replace('//', '/', $path);
    }

    /**
     * 标签和产品列表生成
     *
     * @param integer $id     标签id
     * @param string  $action 控制层
     * @param string  $method 方法
     * @param string  $params 参数
     * @param string  $layer  控制层名称
     * @param string  $model  模块
     * @return boolean 结果
     */
    public function tagLists($id, $action, $method, $params = array(), $model = 'Tags', $layer = '')
    {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }
        //获取静态生成路径
        list($tag_rs, $path) = $this->pathTagLists($id, $model);
        if (!$path) {
            return false;
        }
        $where = array();
        if ($params['model']) {
            $where['type'] = $params['model'];
        }
        $where['tid'] = $id;
        $rs = M($model . 'Map')->where($where)->getField('did', true); //获取分类及分类所有id
        if ($rs && $params['model']) {
            $path = $this->static_root_dir . '/' . $path;
            $dir = substr($path, 0, strrpos($path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $w['id'] = array('in', $rs); //子分类
            //生成栏目分页多页
            $count = M($params['model'])->where($w)->where('status=1 AND create_time<' . NOW_TIME)->count();
            ////列表页条数没设置时默认为10
            $tag_rs['list_row'] = intval($tag_rs['list_row']) > 0 ? intval($tag_rs['list_row']) : 10;
            $page = ceil($count / $tag_rs['list_row']);
            $page = $page ? $page : 1;
            //最大生成页数限制
            if (is_numeric($this->lists_maxpage) && $this->lists_maxpage < $page) {
                $page = is_numeric($this->lists_maxpage) ? $this->lists_maxpage : $page;
            }
            for ($p = 1; $p <= $page; $p++) {
                $map = array(
                    '{page}' => $p,
                    '{tag_id}' => $id,
                    '{id}' => $id,
                    '{tid}' => $id
                );
                $upath = $this->pathMap($path, $map);
                //添加参数
                $params['tag'] = $id;
                $params['p'] = $p;
                //远程访问
                $uparams = $this->buildParams($params);
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $model . '/' . $action . '/' . $method . $uparams . '.html';
                $data = request_by_curl($url);
                $this->staticCreate($upath, $data);
                //填写列表页首页名称的栏目生成同一个第一页
                if ($p == 1 && $tag_rs['path_lists_index']) {
                    $upath = substr($upath, 0, strrpos($upath, '/') + 1) . $tag_rs['path_lists_index'] . $this->ext;
                    $this->staticCreate($upath, $data);
                }
            }
        }
        return true;
    }

    /**
     * 分类页路径规则解析
     *
     * @param integer $id  数据主键
     * @param boolean $dir 是否添加模块静态文件夹
     * @return string 解析之后的路径字符串
     */
    public function pathTagLists($id, $model = 'Tags')
    {
        if (!is_numeric($id) || empty($model)) {
            return false;
        }
        $tag_rs = M($model)->where('id=' . $id)->find();
        //没有指定路径，获取数据库路径
        $path = $tag_rs['path_lists'];
        if (!$path) {
            $path = C(strtoupper($model) . '_PATH');
        }
        if (!$path) {
            return false;
        }
        //模块静态文件夹处理 生成URL的时候不加，生成文件的时候默认加
        $map = array(
            '{tag_id}' => $id,
            '{id}' => $id,
            '{tag_name}' => $tag_rs['name'],
            '{name}' => $tag_rs['name']
        );
        $path = $this->pathMap($path, $map);
        $path .= $this->ext;
        return array($tag_rs, $path);
    }

    /**
     * 生成分类页访问url
     *
     * @param integer $id   数据主键
     * @param string  $page 页码
     * @return string 解析之后的路径字符串
     */
    public function getTagListsUrl($id, $model, $page, $is_mobile = false)
    {
        $page || $page = 1;
        //不加模块静态文件夹
        list($tag_rs, $path) = $this->pathTagLists($id, $model);
        //不指定路径
        $path = '/' . $path;
        $SLD = C(strtoupper($model) . '_SLD');
        if ($SLD) {
            $SLD = substr($SLD, -1) == '/' ? substr($SLD, 0, strlen($SLD) - 1) : $SLD;
            $path = $SLD . $path;
        } else {
            //没有二级域名，地址要加上模块静态文件夹
            // $path =  '/' . $this->moduleDir($path);
            $path = str_replace('//', '/', $path);
            //全站静态访问地址
            $SURL = C('STATIC_URL');
            if ($is_mobile) {
                $SURL = C('MOBILE_STATIC_URL');
            }
            $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
            $path = $SURL . $path;
        }
        //填写列表页首页名称的栏目的第一页地址
        if ($page == 1 && $tag_rs['path_lists_index']) {
            $path = substr($path, 0, strrpos($path, '/') + 1);
            $path .= $tag_rs['path_lists_index'] == 'index' ? '' : $tag_rs['path_lists_index'] . $this->ext;
        } else {
            $path = str_replace('{page}', $page, $path);
        }
        return $path;
    }

    /**
     * 分类列表数据生成
     *
     * @param integer $id     分类id
     * @param string  $action 控制层
     * @param string  $method 方法
     * @param string  $params 参数
     * @param string  $layer  控制层名称
     * @return boolean 结果
     */
    public function lists($id, $action, $method, $params = array(), $layer = '')
    {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        //获取静态生成路径
        list($cate_rs, $path) = $this->pathLists($id);
        //最热生成列表 modify by tanjian   目前只有qbaobei和96u有最热列表页
        if ($this->theme == 'qbaobei' || $this->theme == 'jf96u') {
            list($cate_rs_hot, $hot_path) = $this->pathLists($id, true, true);
        }
        if (!$path) {
            return false;
        }
        $path = $this->static_root_dir . '/' . $path;
        $dir = substr($path, 0, strrpos($path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if ($hot_path) {
            $hot_path = $this->static_root_dir . '/' . $hot_path; //最热静态路径
            $dir = substr($hot_path, 0, strrpos($hot_path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        //是否是生成分类首页
        if ($cate_rs['template_index'] && $cate_rs['path_index'] && empty($this->the_lists_path)) {
            //生成分类首页单页
            $params['cate'] = $id;
            if ($hot_path) {
                $params['hot'] = 1;
                $params = $this->buildParams($params);
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $this->module . '/' . $action . '/' . $method . $params . '.html';
                $data = request_by_curl($url);
                $this->staticCreate($hot_path, $data);
            }
            $params['hot'] = 0;
            $params = $this->buildParams($params);
            $url = $this->static_url . strtolower(
                $this->static_php
            ) . '?s=' . $this->module . '/' . $action . '/' . $method . $params . '.html';
            $data = request_by_curl($url);
            $this->staticCreate($path, $data);


        } else {
            $rs = D($this->category)->getAllChildrenId($id); //获取分类及分类所有id
            $w['category_id'] = array('in', $rs); //子分类
            //生成栏目分页多页
            $count = M($this->model)->where($w)->where('status=1 AND create_time<' . NOW_TIME)->count();
            ////列表页条数没设置时默认为10
            $cate_rs['list_row'] = intval($cate_rs['list_row']) > 0 ? intval($cate_rs['list_row']) : 10;
            $page = ceil($count / $cate_rs['list_row']);
            $total = $page = $page ? $page : 1;
            //最大生成页数限制
            if (is_numeric($this->lists_maxpage) && $this->lists_maxpage < $page) {
                $page = is_numeric($this->lists_maxpage) ? $this->lists_maxpage : $page;
            }

            // 有最大生成页数限制时，同时再生成后3页，暂时处理，待重构 crohn 2015-7-10   修改人：谭坚  2016-1-22 修改页面总数小于3个bug
            for ($p = $total; $p > $total - 3 && $p > $page; $p--) {
                $map = array(
                    '{page}' => $p,
                    '{category_id}' => $id,
                    '{id}' => $id
                );
                if ($hot_path) {
                    $upath = $this->pathMap($hot_path, $map);
                    //添加参数
                    $params['cate'] = $id;
                    $params['p'] = $p;
                    $params['hot'] = 1;
                    //远程访问
                    $uparams = $this->buildParams($params);
                    $url = $this->static_url . strtolower(
                        $this->static_php
                    ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
                    $data = request_by_curl($url);
                    $this->staticCreate($upath, $data);
                }
                $upath = $this->pathMap($path, $map);
                //添加参数
                $params['cate'] = $id;
                $params['p'] = $p;
                $params['hot'] = 0;
                //远程访问
                $uparams = $this->buildParams($params);
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
                $data = request_by_curl($url);
                $this->staticCreate($upath, $data);

            }
            // 生成
            for ($p = 1; $p <= $page; $p++) {
                $map = array(
                    '{page}' => $p,
                    '{category_id}' => $id,
                    '{id}' => $id
                );
                if ($hot_path) {
                    $upath = $this->pathMap($hot_path, $map);
                    //添加参数
                    $params['cate'] = $id;
                    $params['p'] = $p;
                    $params['hot'] = 1;
                    //远程访问
                    $uparams = $this->buildParams($params);
                    $url = $this->static_url . strtolower(
                        $this->static_php
                    ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
                    $data = request_by_curl($url);
                    $this->staticCreate($upath, $data);
                    //填写列表页首页名称的栏目生成同一个第一页
                    if ($p == 1 && $cate_rs['path_lists_index']) {
                        $upath = substr(
                            $upath,
                            0,
                            strrpos($upath, '/') + 1
                        ) . $cate_rs['path_lists_index'] . $this->ext;
                        //p($upath);
                        $this->staticCreate($upath, $data);
                    }
                }
                $upath = $this->pathMap($path, $map);
                //添加参数
                $params['cate'] = $id;
                $params['p'] = $p;
                $params['hot'] = 0;
                //远程访问
                $uparams = $this->buildParams($params);
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
                $data = request_by_curl($url);
                $this->staticCreate($upath, $data);
                //填写列表页首页名称的栏目生成同一个第一页
                if ($p == 1 && $cate_rs['path_lists_index']) {
                    $upath = substr($upath, 0, strrpos($upath, '/') + 1) . $cate_rs['path_lists_index'] . $this->ext;
                    //p($upath);
                    $this->staticCreate($upath, $data);
                }
            }
        }
        return true;
    }

    /**
     * 修改人：谭坚
     * 新增功能：添加了最热列表控制参数
     * 分类页路径规则解析
     * $is_hot 是否为最热列表页面
     *
     * @param integer $id  数据主键
     * @param boolean $dir 是否添加模块静态文件夹
     * @return string 解析之后的路径字符串
     */
    public function pathLists($id, $dir = true, $is_hot = false)
    {
        $cate_rs = M($this->category)->where('id=' . $id)->find();
        //没有指定路径，获取数据库路径
        $path = $cate_rs['path_index'] ? $cate_rs['path_index'] : $cate_rs['path_lists'];
        //返回最热列表页面
        if ($is_hot) {
            $path = $cate_rs['path_lists_hot'];
        }
        if (!$path) {
            return false;
        }
        //模块静态文件夹处理 生成URL的时候不加，生成文件的时候默认加
        $dir && $path = $this->moduleDir($path);
        if ($this->theme == "gfwmobile" || $this->theme == 'jf96umobile') {
            //add by 谭坚 为了适应gfw手机列表页改动
            if (!empty($this->the_lists_path) && !$cate_rs['path_lists']) {
                $path = $this->the_lists_path;
            }
        } else {
            if (!empty($this->the_lists_path)) {
                $path = $this->the_lists_path;
            }
        }
        $map = array(
            '{category_id}' => $id,
            '{id}' => $id,
            '{category_name}' => $cate_rs['name']
        );
        $path = $this->pathMap($path, $map);
        //$path = $path{0} !=='/' ? '/' . $path : $path;
        $path .= $this->ext;


        return array($cate_rs, $path);
    }

    /**
     *  修改人：谭坚
     *  新增功能：添加了最热列表控制参数
     * 生成分类页访问url
     *
     * @param integer $id   数据主键
     * @param string  $page 页码
     * @param boolean $hot  最热
     * @return string 解析之后的路径字符串
     */
    public function getListsUrl($id, $page, $hot = false)
    {
        $page || $page = 1;
        //不加模块静态文件夹
        list($cate_rs, $path) = $this->pathLists($id, false, $hot);
        if (empty($this->the_lists_path)) {
            $path = '/' . $path;
            $SLD = C(strtoupper($this->module) . '_SLD');
            if ($SLD) {
                $SLD = substr($SLD, -1) == '/' ? substr($SLD, 0, strlen($SLD) - 1) : $SLD;
                $path = $SLD . $path;
            } else {
                //没有二级域名，地址要加上模块静态文件夹
                $path = '/' . $this->moduleDir($path);
                $path = str_replace('//', '/', $path);
                //全站静态访问地址

                // 肖书成 为了融合手机版列表页的生成
                substr($this->theme, -6) == 'mobile' ? $SURL = C('MOBILE_STATIC_URL') : $SURL = C('STATIC_URL');

                $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
                $path = $SURL . $path;
            }

            //填写列表页首页名称的栏目的第一页地址
            if ($page == 1 && $cate_rs['path_lists_index']) {
                $path = substr($path, 0, strrpos($path, '/') + 1);
                $path .= $cate_rs['path_lists_index'] == 'index' ? '' : $cate_rs['path_lists_index'] . $this->ext;
            } else {
                $path = str_replace('{page}', $page, $path);
            }
        } else {
            list($cate_rs, $path) = $this->pathLists($id, true, $hot); //为了适应手机默认为pc路径  add by tanjian 2015.12.22
            //指定路径
            $path = str_replace('//', '/', '/' . $path);
            $SURL = C('MOBILE_STATIC_URL');
            if ($page == 1) {
                if ($cate_rs['path_lists_index']) {
                    $path = substr($path, 0, strrpos($path, '/') + 1);
                    $path .= $cate_rs['path_lists_index'] == 'index' ? '' : $cate_rs['path_lists_index'] . $this->ext;
                } else {
                    $path = str_replace('{page}', $page, $path);
                }
            }
            $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
            $path = $SURL . $path;
        }
        return $path;
    }

    /**
     * 内容页生成
     *
     * @param integer $id     数据主键
     * @param string  $action 控制层
     * @param string  $method 方法
     * @param string  $params 参数
     * @param string  $layer  控制层名称
     * @return boolean 结果
     */
    public function detail($id, $action, $method, $params = array(), $layer = '')
    {
        if (empty($id) || !is_numeric($id)) {
            return false;
        }

        //获取静态生成路径
        $path = $this->pathDetail($id, $params);
        $path = $this->static_root_dir . '/' . $path;
        $dir = substr($path, 0, strrpos($path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        //写入参数
        $params['id'] = $id;

        /**
         * 修改详情页分页逻辑到前台，让动态访问也可分页
         * Author:liuliu
         * Time:2015-11-4
         */
        $params['gettotal'] = 'true';
        $first_url = $this->static_url . strtolower(
            $this->static_php
        ) . '?s=' . $this->module . '/' . $action . '/' . $method . $this->buildParams($params) . '.html';
        $first_result = request_by_curl($first_url);
        if (!$first_result) {
            return false;
        }

        // 判断第一次请求结果
        unset($params['gettotal']);
        if (is_numeric($first_result) && $first_result > 1) {
            // 为数字且大于1则有分页，循环请求生成
            $pathinfo = pathinfo($path);
            $basename = basename($path, "." . $pathinfo['extension']);
            if (C('CONTENT_PAGE_FLAG')) {
                $page_flag = C('CONTENT_PAGE_FLAG');
            }
            //内容分页内容之间的分隔标识符
            if (empty($page_flag)) {
                $page_flag = "_";
            }
            // 循环
            for ($i = 1; $i <= $first_result; $i++) {
                $params['p'] = $i;
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $this->module . '/' . $action . '/' . $method . $this->buildParams($params) . '.html';
                $data = request_by_curl($url);
                // 分页路径
                $cpath = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $basename . $page_flag . $i . "." . $pathinfo['extension'];
                if ($i == 1) {
                    $cpath = $path;
                } //  第一页为原地址
                $data && $this->staticCreate($cpath, $data);
            }
        } else {
            // 没有分页原地址生成
            $this->staticCreate($path, $first_result);
        }

        return true;
    }


    /**
     * 内容页数据路径规则解析
     *
     * @param integer $id     数据主键
     * @param string  $params 参数
     * @param boolean $dir    是否添加模块静态文件夹
     * @return string 解析之后的路径字符串
     */
    public function pathDetail($id, $params = array(), $dir = true)
    {
        $rs = M($this->model)->where('id=' . $id)->field('path_detail,category_id,create_time')->find();
        if (empty($rs)) {
            return '';
        }
        $cate_rs = M($this->category)->where('id=' . $rs['category_id'])->find();
        $path = $rs['path_detail'];
        if (!$path) {
            $path = $cate_rs['path_detail'];
        }
        /* -----原代码--*/
        if (!$path) {
            return false;
        }
        //模块静态文件夹处理 生成URL的时候不加，生成文件的时候默认加
        $dir && $path = $this->moduleDir($path);
        //指定路径
        // if(!empty($this->the_detail_path)) $path = $this->the_detail_path; //原来代码，为了适应anfs和7230改动
        if ($this->theme == "afsmobile" || $this->theme == "7230mobile" || $this->theme == "gfwmobile") {
            //add by 谭坚 为了适应anfs和7230详情页改动
            if (!empty($this->the_detail_path) && !$rs['path_detail']) {
                $path = $this->the_detail_path;
            }
        } else {
            if (!empty($this->the_detail_path) && $this->theme != 'jf96umobile') {
                $path = $this->the_detail_path;
            }
        }

        /*
        // 新加代码 ，满足安粉丝生成规则
        if($path)
        {
            $dir && $path =  $this->moduleDir($path);
        }
        else if(!empty($this->the_detail_path) && !$path)
        {
            $path = $this->the_detail_path;
        }
        if(!$path) return false;
        */
        // 新加代码结束   最新修改人：谭坚  2015-12-23  加入其它属性解析，主要用于qbaobei 和 96u
        $map = array(
            '{category_id}' => $rs['category_id'],
            '{id}' => $id,
            '{category_name}' => $cate_rs['name'],
            '{page}' => $params['page'],
            '{create_time|date=Ymd}' => date('Ymd', $rs['create_time']),
            '{Year}' => date('Y', $rs['create_time']),
            '{Month}' => date('m', $rs['create_time']),
            '{Day}' => date('d', $rs['create_time']),
            '{Time}' => date('His', $rs['create_time']),
            '{Y}' => date('Y', $rs['create_time']),
            '{M}' => date('m', $rs['create_time'])
        );
        //创建文件夹
        $path = $this->pathMap($path, $map);
        //$path = $path{0} !=='/' ? '/' . $path : $path;
        $path .= $this->ext;

        return $path;
    }

    /**
     * 生成内容页访问url
     *
     * @param integer $id     数据主键
     * @param string  $params 参数
     * @return string 解析之后的路径字符串
     */
    public function getDetailUrl($id, $params = array())
    {
        $path = $this->pathDetail($id, $params, false);
        // if(empty($this->the_detail_path)){ //原代码
        if (empty($this->the_detail_path)) {
            //未指定路径
            $path = '/' . $path;
            $SLD = C(strtoupper($this->module) . '_SLD');
            if ($SLD) {
                $SLD = substr($SLD, -1) == '/' ? substr($SLD, 0, strlen($SLD) - 1) : $SLD;
                $path = $SLD . $path;
            } else {
                //没有二级域名，地址要加上模块静态文件夹
                $path = '/' . $this->moduleDir($path);
                $path = str_replace('//', '/', $path);
                //全站静态访问地址
                $SURL = C('STATIC_URL');
                $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
                $path = $SURL . $path;
            }
        } else {
            $path = $this->pathDetail($id, $params, true); //为了适应手机默认为pc路径  add by tanjian 2015.12.22
            //指定路径
            $path = str_replace('//', '/', '/' . $path);
            $SURL = C('MOBILE_STATIC_URL');
            $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
            $path = $SURL . $path;
        }
        return $path;
    }


    /**
     * 模块首页生成
     *
     * @param string $action 控制层
     * @param string $method 方法
     * @param string $params 参数
     * @param string $layer  控制层名称
     * @return boolean 结果
     */
    public function moduleIndex($action, $method, $params = '', $layer = '')
    {
        if (!empty($this->the_moduleindex_path)) {
            $path = $this->the_moduleindex_path;
        } else {
            //获取配置
            $path = C(strtoupper($this->module) . '_INDEX_PATH');
        }

        if (!$path) {
            return false;
        }

        //模块静态文件夹处理
        $path = $this->moduleDir($path);
        //获取静态生成路径
        $path = $this->static_root_dir . '/' . $path . $this->ext;

        if (!$path) {
            return false;
        }
        $dir = substr($path, 0, strrpos($path, '/'));
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        if (!empty($this->theme)) {
            $params['theme'] = $this->theme;
        }
        //远程访问
        $params = $this->buildParams($params);
        $url = $this->static_url . strtolower(
            $this->static_php
        ) . '?s=' . $this->module . '/' . $action . '/' . $method . $params . '.html';
        $data = request_by_curl($url, 'get', '', 30);
        if ($data) {
            $this->staticCreate($path, $data);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 生成首页访问url
     *
     * @return string 解析之后的路径字符串
     */
    public function getIndexUrl()
    {
        if (empty($this->the_moduleindex_path)) {
            //获取配置
            $path = C(strtoupper($this->module) . '_INDEX_PATH');
            $path = (!$path || $path == 'index') ? '/' : '/' . $path . $this->ext;
            $SLD = C(strtoupper($this->module) . '_SLD');
            if ($SLD) {
                $SLD = substr($SLD, -1) == '/' ? substr($SLD, 0, strlen($SLD) - 1) : $SLD;
                $path = $SLD . $path;
            } else {
                //没有二级域名，地址要加上模块静态文件夹
                $path = '/' . $this->moduleDir($path);
                $path = str_replace('//', '/', $path);
                //全站静态访问地址
                $SURL = C('STATIC_URL');
                $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
                $path = $SURL . $path;
            }
        } else {
            $path = $this->the_moduleindex_path;
            $path = (!$path || $path == 'index') ? '/' : '/' . $path . $this->ext;
            $path = '/' . $this->moduleDir($path);
            $path = str_replace('//', '/', $path);
            //全站静态访问地址
            $SURL = C('MOBILE_STATIC_URL');
            $SURL && $SURL = substr($SURL, -1) == '/' ? substr($SURL, 0, strlen($SURL) - 1) : $SURL;
            $path = $SURL . $path;
        }
        return $path;
    }

    /**
     * 描述：获取查看的url地址
     *
     * @param        $action
     * @param        $method
     * @param string $params
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function getViewUrl($action, $method, $params = '')
    {
        $params = $this->buildParams($params);
        $url = $this->static_url . strtolower(
            $this->static_php
        ) . '?s=' . $this->module . '/' . $action . '/' . $method . $params . '.html';
        return $url;
    }

    /**
     * widget控制层特殊页生成
     *
     * @param string $action     控制层
     * @param string $method     方法
     * @param string $path       生成路径
     * @param string $params     参数
     * @param string $page       是否多页，为空则为单页
     * @param string $page_index 多页第一页名
     * @param string $layer      控制层名称
     * @return boolean 结果
     */
    public function widget($action, $method, $path, $params = '', $page = '', $page_index = '', $layer = '')
    {
        if ($page) {
            //获取静态生成路径
            $path = $this->static_root_dir . '/' . $path . $this->ext;
            if (!$path) {
                return false;
            }
            $dir = substr($path, 0, strrpos($path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            //获取总页数
            $params['gettotal'] = 1;
            $uparams = $this->buildParams($params);
            $url = $this->static_url . strtolower(
                $this->static_php
            ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
            $total = request_by_curl($url);
            unset($params['gettotal']);
            //分页生成
            for ($p = 1; $p <= intval($total); $p++) {
                $map = array(
                    '{page}' => $p
                );
                $upath = $this->pathMap($path, $map);
                //远程访问
                $params[$page] = $p;
                $uparams = $this->buildParams($params);
                $url = $this->static_url . strtolower(
                    $this->static_php
                ) . '?s=' . $this->module . '/' . $action . '/' . $method . $uparams . '.html';
                $data = request_by_curl($url);
                if ($data) {
                    $this->staticCreate($upath, $data);
                    //填写多页生成第一页名的生成同一个第一页
                    if ($p == 1 && $page_index) {
                        $upath = substr($upath, 0, strrpos($upath, '/') + 1) . $page_index . $this->ext;
                        $this->staticCreate($upath, $data);
                    }
                }
            }
            return true;
        } else {
            //获取静态生成路径
            $path = $this->static_root_dir . '/' . $path . $this->ext;
            if (!$path) {
                return false;
            }
            $dir = substr($path, 0, strrpos($path, '/'));
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            //远程访问
            $params = $this->buildParams($params);
            $url = $this->static_url . strtolower(
                $this->static_php
            ) . '?s=' . $this->module . '/' . $action . '/' . $method . $params . '.html';

            $data = request_by_curl($url);

            if ($data) {
                $this->staticCreate($path, $data);
                return true;
            } else {
                return false;
            }
        }

    }

    /**
     * 生成静态文件
     *
     * @param string $path 生成路径
     * @param string $data 输出文件流数据
     * @return boolean 结果
     */
    public static function staticCreate($path, $data)
    {
        if (!$data) {
            return;
        }
        $fh = fopen($path, 'w');
        //独占锁
        flock($fh, LOCK_EX);
        fwrite($fh, $data);
        flock($fh, LOCK_UN);
        fclose($fh);
    }


    /**
     * 路径规则解析
     *
     * @param string $path 生成路径
     * @param array  $map  解析规则映射
     * @return string 解析之后的路径字符串
     */
    protected function pathMap($path, $map)
    {
        foreach ($map as $key => $value) {
            $path = str_replace($key, $value, $path);
        }

        return $path;
    }

    /**
     * 数组转换为符合要求的url参数
     *
     * @param array $params 参数数组
     * @return string
     */
    protected function buildParams($params)
    {
        if (empty($params)) {
            return '';
        }
        $rs = '';
        foreach ($params as $key => $value) {
            $rs .= '/' . $key . '/' . $value;
        }
        //如果有主题切换设置参数
        if (!empty($this->theme)) {
            $rs .= '/t/' . $this->theme;
        }
        return $rs;
    }
}
