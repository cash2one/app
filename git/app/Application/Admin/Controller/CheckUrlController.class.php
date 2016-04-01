<?php
// +----------------------------------------------------------------------
// | 描述
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-7-30 上午11:55    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;


use Think\Controller;

class CheckUrlController extends Controller{

    //文档模型名
    protected $document_name = 'Down';

    /**
     * 检查URL
     */
    public function check_url()
    {
        $download_point = M('preset_site')->getField('id, site_name, site_url, download_name, download_url');
        $download_data  = M($this->document_name)->alias('a')->join('RIGHT JOIN __DOWN_ADDRESS__ b ON b.did=a.id')->field('b.id, a.title, a.category_id, a.update_time, a.status,b.check_status,b.site_id,b.url')->where('a.update_time > b.check_time or b.check_status=0 or b.check_status=999 or (b.check_status=404 and a.status=1)')->order('b.check_status desc,a.update_time desc')->select();
        $download_data = $this->download_address($download_data, $download_point);
        if(!empty($download_data))
        {
            foreach($download_data as $val)
            {
                $url = $val['download_url'];
                if(!empty($url))
                {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_HEADER, 0);//不获取http头信息
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_TIMEOUT,60);
                    curl_exec($ch);
                    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);//网页状态码
                    curl_close($ch);
                    unset($ch);
                    $data= array();
                    $data['id'] = $val['id'];
                    $data['check_status'] = $code;
                    $data['check_time'] = time();
                  //  dump($data);
                    M('DownAddress')->save($data);
                    unset($data);
                }
                else
                {
                    $data= array();
                    $data['id'] = $val['id'];
                    $data['check_status'] = 888;  //链接地址为空
                    $data['check_time'] = time();
                    //  dump($data);
                    M('DownAddress')->save($data);
                    unset($data);
                }

            }
        }
    }
    private function download_address(array $download_address, array $download_point)
    {
        $_webServer = C('WEB_SERVER');
        $webServer = array();
        foreach ($_webServer as $kk => $vv)
            $webServer[$kk] = (false !== strpos($vv, '|')) ? $_tmp = explode('|', $vv) : array($vv);


        $download_address_data = array();
        foreach ($download_address as $key => $val)
        {
            if (empty ($download_point[$val['site_id']]))
                continue;

            $_tmp_url = parse_url($download_point[$val['site_id']]['site_url']);

            $download_address[$key]['site_name'] = $download_point[$val['site_id']]['site_name'];
            $download_address[$key]['site_url'] = $download_point[$val['site_id']]['site_url'];
            $download_address[$key]['download_url'] = formatAddress($val['url'],$val['site_id']);

            if (empty($webServer[$_tmp_url['host']]))
            {
                $download_address[$key]['down_ip'] = '';
            }
            else
            {
                foreach ($webServer[$_tmp_url['host']] as $v)
                {
                    $download_address[$key]['down_ip'] = $v;
                }
            }

            $download_address_data[] = $download_address[$key];
        }

        return $download_address_data;
    }
} 