<?php
// +----------------------------------------------------------------------
// | 生成控制
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// | Time：2016-1
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

use Think\Controller;

/**
 * 生成控制
 *
 * 用于自动生成任务处理
 */
class GenerateController extends Controller
{
    /** @var object|null 实例化缓存对象 */
    public $cache = null;

    /** @var string reids队列数据的KEY */
    public $list;

    /**
     * 初始化 默认使用redis作为高速缓存
     *
     * @return void
     */
    protected function init()
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
            $this->err('cache error');
        }
        if (!$this->cache->auth($options['auth'])) {
            $this->err('auth error');
        }

        // 队列名称
        $this->list = C('GE_CACHE_KEY') ? $options['prefix'] . C('GE_CACHE_KEY') : $options['prefix'] . 'list';

        // CORS跨域
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if ($referer) {
            $referer = parse_url($referer);
            $host = $referer['host'];
            if (in_array($host, $cors)) {
                header('Access-Control-Allow-Origin:http://' . $host);
            }
        }
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
     * 自动生成任务入队
     *
     * @param string $uri 请求的uri
     * @return mixed
     */
    public function push()
    {
        $url = I('url');
        $gm = I('gm');
        if (empty($url) || empty($gm)) {
            $this->err('param error');
        }

        // url规则检测
        $pattern = C('GE_ALLOW_URL');
        if (!empty($pattern)) {
            if (!preg_match($pattern, $url)) {
                $this->err('url pattern error');
            }
        }

        $this->init();
        $this->cache->rpush($this->list, $url . '|' . $gm);
    }
}
