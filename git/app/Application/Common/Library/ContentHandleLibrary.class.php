<?php
// +----------------------------------------------------------------------
// |  内容处理类  继承控制类使用某些方法
// +----------------------------------------------------------------------
// | Author: Jeffrey Lau
// +----------------------------------------------------------------------
namespace Common\Library;

use Think\Think;

/**
 * 内容插入处理类
 */
class ContentHandleLibrary
{

    //内容
    protected $content = '';
    //开关
    protected $enlink = '';
    //key
    protected $mobile = true;

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $view = null;

    /**
     * 初始化
     * @param array $params 参数
     * @return void
     */
    public function __construct($content)
    {
        //实例化视图类
        $this->view = Think::instance('Think\View');
        $this->content = $content;
    }

    /**
     * 处理
     *
     * @param array $params 参数
     * @return void
     */
    public function init()
    {
        //检测
        if (!$this->check()) {
            return false;
        }
        //关键词内链功能
        if ($this->enlink) {
            $this->innerLink();
        }
        //图片地址处理功能
        $this->picUrl();
        return $this->content;
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
     * 检测
     *
     * @return void
     */
    public function check()
    {
        if (empty($this->content)) {
            return false;
        }
        return true;
    }

    /*
     * 关键词内链功能
     *
     */
    public function innerLink()
    {
        $content = $this->content;
        $this->content = $this->transLink($content, 1);
    }

    /*
       * 关键词内链功能
       *
       */
    public function picUrl()
    {
        $content = $this->content;
        $this->content = $this->handlePicUrl($content);
    }

    /**
     * 处理图片 ADD BY TANJIAN 2016.1.20
     * 刘盼二次整合 (2016-3-5 15:25:07)
     *
     * @param string $body 内容
     * @return string
     */
    private function handlePicUrl($body)
    {
        $body = str_replace('src="Uploads', 'src="/Uploads', $body);
        $body = str_replace('src="/Uploads', 'src="' . C('PIC_HOST') . '/Uploads', $body);
        $body = str_replace('src="' . C('STATIC_URL'), 'src="' . C('PIC_HOST'), $body); //本域名地址换成图片域名地址
        $body = str_replace('src="' . C('MOBILE_STATIC_URL'), 'src="' . C('PIC_HOST'), $body);
        if (C('OPEN_THUMB')) { //判断是否开启图片缩略功能
            preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png|\.GIF|\.JPG|\.JPEG|\.PNG|\.jpeg]))[\'|\"].*?[\/]?>/', $body, $match);
            if (!$this->mobile && (C('MOBILE_THUMB_WIDTH') || C('MOBILE_THUMB_HEIGHT'))) {  //判断是否配置手机缩略
                $width = C('MOBILE_THUMB_WIDTH') ?: 0;
                $height = C('MOBILE_THUMB_HEIGHT') ?: 0;
                if ($match[1]) {
                    $search = $replace = array();

                    foreach ($match[1] as $img) {
                        $filename = substr($img, 0, strrpos($img, '.'));

                        $search[] = $img;

                        if (C('OPEN_WATER')) { //判断是否打水印
                            $replace[] = $filename . '_' . $width . '_' . $height . '_water' . strrchr($img, '.');
                        } else {
                            $replace[] = $filename . '_' . $width . '_' . $height . strrchr($img, '.');
                        }
                    }
                    $body = str_replace($search, $replace, $body);
                }
            } else {
                if ($this->mobile && (C('THUMB_WIDTH') || C('THUMB_HEIGHT'))) {//判断是否开启pc版缩略
                    $width = C('THUMB_WIDTH') ?: 0;
                    $height = C('THUMB_HEIGHT') ?: 0;
                    if ($match[1]) {
                        $search = $replace = array();

                        foreach ($match[1] as $img) {
                            $filename = substr($img, 0, strrpos($img, '.'));

                            $search[] = $img;

                            if (C('OPEN_WATER')) { //判断是否打水印
                                $replace[] = $filename . '_' . $width . '_' . $height . '_water' . strrchr($img, '.');
                            } else {
                                $replace[] = $filename . '_' . $width . '_' . $height . strrchr($img, '.');
                            }
                        }
                        $body = str_replace($search, $replace, $body);
                    }
                }
            }

        }
        return $body;

    }

    /**
     * 添加内链功能强化版高亮问题修正, 排除alt title <a></a>直接的字符替换
     *
     * @param string $body 内容
     * @param string $replaceNum 替换次数
     * @return string
     */
    private function transLink($body, $replaceNum = '1')
    {
        $body = preg_replace('/<a.*?class=".*?\binnerlink\b.*?".*?>(.*?)<\/a>/', '$1', $body);
        $karr = $kaarr = $GLOBALS['replaced'] = array();
        $body = preg_replace("#(<a(.*))(>)(.*)(<)(\/a>)#isU", '\\1-]-\\4-[-\\6', $body); //暂时屏蔽超链接
        $pcLink = S('PcInnerLink');//获取缓存的内链数据
        $mobileLink = S('MobileInnerLink');//获取缓存的内链数据
        if (empty($pcLink)) {
            $conditionPC['status']="1";
            $conditionPC['platform'] = array(array('eq',1),array('eq',2), 'or') ;
            $pcLink = M('Innerlink')->where($conditionPC)->select();
            S('PcInnerLink', $pcLink);//缓存内链数据
        }
        if (empty($mobileLink)) {
            $conditionM['status']="1";
            $conditionM['platform'] = array(array('eq',1),array('eq',3), 'or') ;
            $mobileLink = M('Innerlink')->where($conditionM)->select();
            S('MobileInnerLink', $mobileLink);//缓存内链数据
        }
        $link = $this->mobile ? $pcLink :$mobileLink;
        ksort($link);
        foreach ($link as $k => $v) {
            $key = trim($v['words']);
            $key_url = trim($v['url']);
            $karr[] = $key;
            $kaarr[] = '<a href="' . $key_url . '" title="' . $key . '" class="innerlink" target="_blank">' . $key . '</a>';
        }
        $body = @preg_replace("#(^|>)([^<]+)(?=<|$)#sUe", "linkHighlight('\\2', \$karr, \$kaarr, '\\1',$replaceNum)", $body);
        $body = preg_replace("#(<a(.*))-\]-(.*)-\[-(\/a>)#isU", '\\1>\\3<\\4', $body);//恢复超链接
        return $body;
    }
}
