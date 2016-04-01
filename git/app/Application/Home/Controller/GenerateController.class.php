<?php
/**
 * 静态文件的自动生成和刷新
 * @author liuliu <lllliuliu@163.com>
 */
namespace Home\Controller;

use Think\Controller;

class GenerateController extends Controller
{
    /** @var int 超时时间 */
    public $timeout = '300';
    /** @var int 请求uri */
    public $uri;
    /** @var string 生成模式，PC：p，移动：m */
    public $gm = 'p';
    /** @var string 主题 */
    public $theme = '';
    /** @var array 模块分类表 文章，下载，礼包，图库 */
    public $module_cate = [
        'Document' => 'Category',
        'Down' => 'DownCategory',
        'Package' => 'PackageCategory',
        'Gallery' => 'GalleryCategory',
    ];
    /** @var string|null 限定模块，当启用了二级域名的时候需要限定module_cate模块 */
    public $sp_module_cate = null;
    /** @var object|null 缓存对象 */
    public $cache = null;
    /** @var string 缓存key */
    public $key;

    /**
     * 空操作
     *
     * @return boolean
     */
    public function _empty()
    {
        exit();
    }

    /**
     * 初始化
     *
     * @return void
     */
    protected function _initialize()
    {
        /* 读取后台站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        $this->theme = C('THEME');
        $this->ext = empty(C('STATIC_FILE_EXT')) ? '.html' : C('STATIC_FILE_EXT');
        $this->timeout = C('GE_TIMEOUT') ? C('GE_TIMEOUT') : $this->timeout;

        $this->initCache();
    }

    /**
     * 缓存对象
     *
     * @return object
     */
    public function initCache()
    {
        // 缓存初始化参数
        $options = [];
        $options['type'] = C('GE_CACHE_TYPE') ? C('GE_CACHE_TYPE') : 'redis';
        $options['host'] = C('GE_CACHE_HOST') ? C('GE_CACHE_HOST') : '127.0.0.1';
        $options['port'] = C('GE_CACHE_PORT') ? C('GE_CACHE_PORT') : '6379';
        $options['prefix'] = C('GE_CACHE_PREFIX') ? C('GE_CACHE_PREFIX') : 'ge_';
        $options['auth'] = C('GE_CACHE_AUTH') ? C('GE_CACHE_AUTH') : '';

        // 实例化并验证密码
        $this->cache = \Think\Cache::getInstance($options['type'], $options);
        if (!is_object($this->cache)) {
            $this->err();
        }
        if (!$this->cache->auth($options['auth'])) {
            $this->err();
        };
        // 队列名称
        $this->key = C('GE_CACHE_KEY') ? $options['prefix'] . C('GE_CACHE_KEY') : $options['prefix'] . 'list';
    }

    /**
     * 错误处理
     *
     * @param string $info 报错信息
     * @return mixed
     */
    public function err($info = '')
    {
        echo $info;
        exit();
    }

    /**
     * 处理
     *
     * @return boolean
     */
    public function handle()
    {
        $nummax = C('GE_HANDLE_NUM') ? C('GE_HANDLE_NUM') : 5;
        $root_path = ['p' => C('STATIC_ROOT'), 'm' => C('MOBILE_STATIC_ROOT')];
        $rewrite_reverse = ['p' => C('WEBSERVER_REWRITE'), 'm' => C('MOBILE_WEBSERVER_REWRITE')];
        $rewrite_reverse = array_map(function ($v) {
            return is_array($v) ? array_filter($v) : $v;
        }, $rewrite_reverse);

        for ($num = 1; $num <= $nummax; $num++) {
            $v = $this->cache->lpop($this->key);
            if (empty($v)) {
                continue;
            }
            list($this->uri, $this->gm) = explode('|', $v);
            if (empty($this->uri) || empty($this->gm)) {
                continue;
            }

            // uri地址反向处理
            $pattern = $rewrite_reverse[$this->gm];
            if (!empty($pattern)) {
                foreach ($pattern as $key => $value) {
                    $this->uri = preg_replace($key, $value, $this->uri);
                }
            }

            // 排除不生成的地址
            $ignore_url = ['/index.html', '/404.html'];
            if (in_array($this->uri, $ignore_url)) {
                continue;
            }
            /*
            // 如果限定模块，改变模块范围，当启用了二级域名的时候需要限定module_cate模块
            // 当前只支持PC版二级域名的限定模块
            if (I('spm') && $this->gm=='p'){
            $spm = ucfirst(strtolower(I('spm')));
            array_key_exists($spm, $this->module_cate) && $this->module_cate = [ $spm => $this->module_cate[$spm] ];
            }
             */
            // 获取静态文件根目录
            $path = $root_path[$this->gm];
            if (empty($path)) {
                continue;
            }
            // 文件检测
            $file = str_replace('//', '/', $path . '/' . $this->uri);
            if ($this->fileCheck($file)) {
                $this->fileGenerate();
            } else {
                $num--;
            }
        }
    }

    /**
     * 静态文件超时和存在检测
     *
     * @param $file string 文件路径
     * @param $timeout int 超时时间，单位为秒
     * @return boolean 是否需要生成
     */
    public function fileCheck($file, $timeout = null)
    {
        if (empty($file)) {
            return false;
        }
        $timeout = empty($timeout) ? $this->timeout : $timeout;
        if (is_file($file)) {
            $last_time = filemtime($file);
            if (time() - $last_time > $timeout) {
                // 存在但是超时，直接返回并后续处理
                // TODO:移到单独的入口文件，直接在文件存在的时候返回，提高效率
                // function_exists('fastcgi_finish_request') && fastcgi_finish_request(); 后台运行
                return true;
            }
        } else {
            return true;
        }
        return false;
    }

    /**
     * 文件生成和刷新
     *
     * @return boolean
     */
    public function fileGenerate()
    {
        if ($rs = $this->fileAnalyze()) {
            $m_i = D('Admin/' . $rs['module']);
            switch ($rs['type']) {
                case 'lists':
                    // 分类检查状态
                    $status = M($this->module_cate[$rs['module']])->where('id=' . $rs['id'])->getField('status');
                    if ($status != 1) {
                        return false;
                    }
                    // TODO:手机版列表页生成
                    if ($this->gm == 'p') {
                        $m_i->createStaticLists($rs['id'], 5);
                        echo date('Y-m-d h:i:s') . ' : list m ' . $rs['module'] . ' ' . $rs['id'] . PHP_EOL;
                    }
                    break;
                case 'detail':
                    // 数据检查状态
                    $status = $m_i->where('id=' . $rs['id'])->getField('status');
                    if ($status != 1) {
                        return false;
                    }
                    if ($this->gm == 'p') {
                        $m_i->createStaticDetail($rs['id']);
                        echo date('Y-m-d h:i:s') . ' : detail p ' . $rs['module'] . ' ' . $rs['id'] . PHP_EOL;
                    } else {
                        $m_i->createStaticDetailM($rs['id']);
                        echo date('Y-m-d h:i:s') . ' : detail m ' . $rs['module'] . ' ' . $rs['id'] . PHP_EOL;
                    }
                    break;
            }
        }
    }

    /**
     * uri解析
     *
     * @todo 1.PC版详情页自己配置生成路径处理
     *       2.对于移动版的除详情页之外的规则匹配（目前移动版列表都是特殊页或动态）
     *       3.对于复杂的移动版的生成路径处理（目前只能处理一个类型一个生成规则）
     * @return boolean
     */
    public function fileAnalyze()
    {
        $gm = $this->gm;
        // 某些站点移动版和PC版一样的规则
        if (in_array($this->theme, ['jf96u'])) {
            $gm = 'p';
        }
        switch ($gm) {
            // PC处理 根据模块分类配置匹配
            case 'p':
                foreach ($this->module_cate as $module => $cate) {
                    $lists = get_category_by_model(0, null, $cate, true);
                    foreach ($lists as $list) {
                        // 文章和列表循环处理
                        foreach (['lists' => 'path_lists', 'detail' => 'path_detail'] as $type => $ld) {
                            if (empty($pattern = $list[$ld])) {
                                continue;
                            }
                            $pattern = $this->patternHandleForType($pattern, $type);
                            // 列表页要匹配首页，文章要匹配分页
                            if ($match = $this->uriMatch($pattern)) {
                                return [
                                    'id' => $type == 'lists' ? $list['id'] : $match['id'][0],
                                    'type' => $type,
                                    'module' => $module,
                                ];
                            }
                        }
                    }
                }
                break;
            // 移动处理
            case 'm':
                foreach ($this->module_cate as $module => $cate) {
                    if (empty($path_config = C('MOBILE_' . strtoupper($module)))) {
                        continue;
                    }
                    foreach ($path_config as $type => $pattern) {
                        if ($type != 'detail') {
                            continue; //只处理详情
                        }
                        $pattern = $this->patternHandleForType($pattern, $type);
                        // 列表页要匹配首页，文章要匹配分页
                        if (empty($pattern)) {
                            continue;
                        }
                        if ($match = $this->uriMatch($pattern)) {
                            return [
                                'id' => $match['id'][0],
                                'type' => $type,
                                'module' => $module,
                            ];
                        }
                    }
                }
                break;
        }
        return false;
    }

    /**
     * uri匹配
     *
     * @param $pattern string 匹配模式
     * @param $uri string uri
     * @return boolean|array
     */
    public function uriMatch($pattern, $uri = '')
    {
        $reg_replace = [
            '{id}' => '(?<id>\d+?)',
            '{category_id}' => '(\d+?)',
            '{category_name}' => '([^/\\]+?)',
            '{page}' => '(\d+?)',
            '{create_time|date=Ymd}' => '(\d{8})',
            '{Year}' => '(\d{4})',
            '{Month}' => '(\d{2})',
            '{Day}' => '(\d{2})',
            '{Time}' => '(\d+?)',
            '{Y}' => '(\d{4})',
            '{M}' => '(\d{2})',
            '/' => '\/',
        ];
        $pattern = "/^\/" . str_replace(array_keys($reg_replace), $reg_replace, $pattern) . "\\" . $this->ext . "$/i";
        $match = '';
        $uri = $uri ? $uri : $this->uri;
        if (preg_match_all($pattern, $uri, $match)) {
            return $match;
        }
        return false;
    }

    /**
     * pattern根据类型处理
     *
     * 列表页要匹配首页，文章要匹配分页
     *
     * @param $pattern string 匹配模式
     * @param $type string 类型
     * @return string
     */
    public function patternHandleForType($pattern, $type)
    {
        switch ($type) {
            case 'lists':
                $pattern = str_replace('_{page}', '(_{page})?', $pattern);
                break;
            case 'detail':
                //内容分页内容之间的分隔标识符
                $page_flag = C('CONTENT_PAGE_FLAG') ? C('CONTENT_PAGE_FLAG') : "_";
                $pattern = str_replace('{id}', '{id}(' . $page_flag . '{page})?', $pattern);
                break;
        }
        return $pattern;
    }
}
