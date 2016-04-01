<?php
// +----------------------------------------------
// |  东东助手接口站点数据获取，控制类
// *----------------------------------------------
// |  Author: liuliu
// |  Time  : 2015-10-14
//+----------------------------------------------
namespace DDAPI\Controller;

use DDAPI\Controller;

class WebController extends CommonController
{

    // 站点对象
    protected $site_Instance;

    // 站点对象反射
    protected $site_reflection;

    // 主题
    protected $theme;

    /**
     * 初始化
     * @param void
     * @return void
     */
    protected function _initialize()
    {
        parent::_initialize();
        $this->theme = C('THEME');
        // 实例化站点对象
        $this->site_Instance = self::siteFactory($this->theme);
    }

    /**
     * 站点对象工厂
     * @param string $theme 主题
     * @return void
     */
    public static function siteFactory($theme = '')
    {
        $class_name = empty($theme) || !class_exists('\DDAPI\Site\\' . ucfirst($theme) . 'Site')
        ? '\DDAPI\Site\DefaultSite'
        : '\DDAPI\Site\\' . ucfirst($theme) . 'Site';
        $r = new \ReflectionClass($class_name);
        return $r->newInstance();
    }

    /**
     * 获取下载详细数据
     * @param $id integer 数据ID
     * @param $rf string 返回结果格式 json,xml
     * @return string
     */
    public function get($id, $rf = 'json')
    {
        // 验证参数
        if (!is_numeric($id) || (int) $id < 1) {
            goto error404;
        }

        // 数据查询
        $info = $this->site_Instance->selectInfo($id);
        if (!$info) {
            goto error404;
        }

        // 下载地址查询
        $durl = $this->site_Instance->handleWithAddress();
        if (empty($durl)) {
            goto error404;
        }

        // APK文件名提取
        $ApkFileName = $this->site_Instance->getFileName();

        // 包文件名提取
        $AppPackName = $this->site_Instance->getPackName();

        // 组合结果
        $value = [
            'ApkFileName' => $ApkFileName,
            'AppName' => $info['title'],
            'AppName2' => $info['sub_title'],
            'AppPackName' => $AppPackName,
            'DownloadUrl' => $durl,
            'AppPictureUrl' => get_cover($info['smallimg'], 'path'),
            'NormalPicture' => '',
        ];
        is_numeric($info['optimalemu']) && $value['OptimalEmu'] = (int) $info['optimalemu'];
        is_numeric($info['suitemu']) && $value['SuitEmu'] = (int) $info['suitemu'];

        // 获取状态
        echo str_replace('\/', '/', json_encode($value, JSON_UNESCAPED_UNICODE));
        exit;

        // 404处理
        error404:
        send_http_status(404);
        exit;

    }
}
