<?php
// +----------------------------------------------
// |  默认站点类
// *----------------------------------------------
// |  Author: liuliu
// |  Time  : 2015-10-26
//+----------------------------------------------
namespace DDAPI\Site;

class DefaultSite
{

    // 查找主表和附属表的字段
    public $field = 'a.id,a.title,b.sub_title,a.smallimg,a.create_time,b.content,b.relative_down_url,b.optimalemu,b.suitemu';

    // 查询的信息
    public $info = [];

    // 包下载地址
    public $durl = '';

    // 包文件名
    public $ApkFileName = '';

    /**
     * 数据查询
     * @param $id integer 数据id
     * @return string
     */
    public function selectInfo($id)
    {
        if (!is_numeric($id) || (int) $id < 1) {
            return false;
        }

        $this->info = M('Down')->alias('a')->field($this->field)->join('__DOWN_DMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.id = ' . $id)->find();
        return $this->info;
    }

    /**
     * 下载地址处理
     * @return string
     */
    public function handleWithAddress()
    {
        $info = $this->info;
        if (empty($info['relative_down_url'])) {
            return false;
        }

        if (!is_numeric($info['relative_down_url'])) {
            // URL
            if (!ckeckDURL($info['relative_down_url'])) {
                return false;
            }

            return $this->formatUrl(['url' => $info['relative_down_url']]);
        } else {
            // ID
            $address = M('DownAddress')->field('name,url,site_id')->where('did = ' . $info['relative_down_url'])->find();
            if (empty($address)) {
                return false;
            }

            return $this->formatUrl($address);
        }
        return false;
    }

    /**
     * 非完整地址补全以及格式化地址
     * @return string
     */
    public function formatUrl($address)
    {
        if (!ckeckDURL($address['url'])) {
            $site = M('PresetSite')->field('site_url')->where('id = ' . $address['site_id'])->find();
            if (!$site || !$site['site_url']) {
                return false;
            }

            $durl = $site['site_url'] . $address['url'];
            $durl = preg_replace('/(?<!:)\/\//', '/', $durl);
            if (!ckeckDURL($durl)) {
                return false;
            }

        } else {
            $durl = preg_replace('/(?<!:)\/\//', '/', $address['url']);
        }
        return $this->durl = $durl;
    }

    /**
     * APK文件名提取
     * @return string
     */
    public function getFileName()
    {
        $durl = $this->durl;
        $ApkFileName = md5($durl) . '.apk';
        $parserurl = pathinfo($durl);

        if (!empty($parserurl['extension'])) {
            if ($parserurl['extension'] == 'apk') {
                $ApkFileName = $parserurl['basename'];
            }

        } else {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_URL, $durl);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果把这行注释掉的话，就会直接输出
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); //连接超时时间
            $tmpInfo = curl_exec($ch);
            curl_close($ch);

            $header_array = explode("\n", $tmpInfo);
            foreach ($header_array as $header_value) {
                $header_pieces = explode(':', $header_value);
                if (count($header_pieces) == 2) {
                    $headers[$header_pieces[0]] = trim($header_pieces[1]);
                }
            }
            if (pathinfo($headers['Content-Disposition'])['extension'] == 'apk') {
                $ApkFileName = $headers['Content-Disposition'];
            }

        }

        return $this->ApkFileName = $ApkFileName;
    }

    /**
     * 包文件名提取
     * @return string
     */
    public function getPackName()
    {
        return '';
    }
}
