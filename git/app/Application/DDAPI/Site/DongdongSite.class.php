<?php
// +----------------------------------------------
// |  东东助手站点类
// *----------------------------------------------
// |  Author: liuliu
// |  Time  : 2015-10-26
//+----------------------------------------------
namespace DDAPI\Site;

use DDAPI\Site;

class DongdongSite extends DefaultSite
{

    // 查找主表和附属表的字段
    public $field = 'a.id,a.title,b.sub_title,a.smallimg,a.create_time,b.content,b.t_engine as optimalemu,b.android_engine as suitemu,b.package_name';

    /**
     * 东东手游网的下载地址特殊处理
     * @return string
     */
    public function handleWithAddress()
    {
        $info = $this->info;
        $lists = M('DownAddress')->field('name,url,site_id')->where('did = ' . $info['id'])->select();
        foreach ($lists as $address) {
            if (substr($address['url'], 0, 5) != 'setup') {
                return $this->formatUrl($address);
            }
        }
        return false;
    }

    /**
     * 东东手游网的包文件名提取
     * @return string
     */
    public function getPackName()
    {
        $ApkFileName = $this->info['package_name'];
        // if (empty($ApkFileName)) return parent::getFileName(); // 没有填则使用普通站点方式
        return $this->ApkFileName = $ApkFileName;
    }
}
