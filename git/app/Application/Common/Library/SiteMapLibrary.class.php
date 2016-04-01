<?php
// +----------------------------------------------------------------------
// |  站点地图处理类 
// |  1.添加文章实时curl post到百度站点地图
// |  2.生成XML站点地图文件
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com> 
// +----------------------------------------------------------------------
namespace Common\Library;
use Think\Think;
/**
 * 站点地图处理类
 */
class SiteMapLibrary{

    //百度推送地址
    protected $host_post = 'ping.baidu.com';
    //新百度推送接口
    protected $host_post_new = 'http://data.zz.baidu.com';
    //手机版KEY
    protected $m_key = '';
    //手机版实时推送地址
    protected $m_url = '';

    //PC版KEY
    protected $p_key = '';
    //PC版实时推送地址
    protected $p_url = '';    


    /**
     * 初始化
     * @param array $params 参数
     * @return void
     */
    public function __construct() {
        /* 读取后台站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        //手机版KEY
        $this->m_key = C('MOBILE_BAIDU_SITEMAP_KEY');
        //手机版实时推送地址
        $this->m_host = C('MOBILE_STATIC_URL');
        $this->m_url = '/sitemap?site='.str_replace('/', '', str_replace('http://', '', $this->m_host));
        $this->m_url_new = '/urls?site='.str_replace('/', '', str_replace('http://', '', $this->m_host));

        //PC版KEY
        $this->p_key = C('BAIDU_SITEMAP_KEY');
        //PC版实时推送地址
        $this->p_host = C('STATIC_URL');
        $this->p_url = '/sitemap?site='.str_replace('/', '', str_replace('http://', '', $this->p_host));
        $this->p_url_new = '/urls?site='.str_replace('/', '', str_replace('http://', '', $this->p_host));
    }

    /**
     * 已存在属性设置
     * @param string $k 
     * @param string $v 
     * @return void
     */
    public function setProperty($k, $v = null) {
        if(is_array($k)){
            foreach ($k as $k_t => $v_t) {
                $this->setProperty($k_t, $v_t);
            }
        }else{
            if(isset($this->$k)){
                $this->$k = $v;
            }else{
                return false;
            }             
        }   
    }

    /**
     * 实时推送站点地图
     * @param string $theme 版本主题
     * @param array $lists 结果数组
     * @return integer|boolean
     */
    public function post($theme, $lists) {

        if(C('MOBLIE_BAIDU_NEW_TOKEN'))
        {
            //主题
            switch ($theme) {
                case 'm':
                    $url = $this->host_post_new . $this->m_url_new . '&token=' . C('MOBLIE_BAIDU_NEW_TOKEN');
                    break;
                case 'p':
                    $url = $this->host_post_new . $this->p_url_new . '&token=' . C('MOBLIE_BAIDU_NEW_TOKEN');
                    break;
            }



            //获取数据
            foreach ($lists as $key => $value) {
                $urls[] = $value['url'];
            }
            //请求
            return $this->curl_baidu($url,$urls);
        }
        else
        {
            $this->postOld($theme,$lists);
        }

    }

    protected function curl_baidu($api,$urls)
    {
        $ch = curl_init();
        $options =  array(
            CURLOPT_URL => $api,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => implode("\n", $urls),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        );
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        return json_decode($result,true);
    }

    /**
     * 实时推送站点地图
     * @param string $theme 版本主题
     * @param array $lists 结果数组
     * @return integer|boolean
     */
    public function postOld($theme, $lists) {
        //主题
        switch ($theme) {
            case 'm':
                $url = $this->host_post . $this->m_url . '&resource_name=sitemap&access_token=' . $this->m_key;
                break;
            case 'p':
                $url = $this->host_post . $this->p_url . '&resource_name=sitemap&access_token=' . $this->p_key;
                break;
        }

        //获取数据
        foreach ($lists as $key => $value) {
            $data .= "<url><loc><![CDATA[{$value['url']}]]></loc><lastmod>{$value['time']}</lastmod> <changefreq>always</changefreq> <priority>1</priority> </url>";            
        }
        $data = '<?xml version="1.0" encoding="UTF-8"?> <urlset> '.$data.' </urlset>';
        //请求
        $rs = request_by_curl($url, 'post', $data, 3);
        preg_match('/<int>(\d+?)<\/int>/i', $rs, $matches);
        return is_numeric($matches[1]) ? $matches[1] : false;
    }


    /**
     * 实时推送结构化数据
     * @param string $theme 版本主题
     * @param array $lists 结果数组
     * @return integer|boolean
     */
    public function postStructuring($theme, $lists) {
        //主题
        switch ($theme) {
            case 'm':
                $url = $this->host_post . $this->m_url . '&resource_name=RDF_Internet_SoftwareApplication&access_token=' . $this->m_key;
                break;
            case 'p':
                $url = $this->host_post . $this->p_url . '&resource_name=RDF_Internet_SoftwareApplication&access_token=' . $this->p_key;
                break;
        }
        //获取数据
        foreach ($lists as $list) {
            //基础数据
            foreach ($list['base'] as $key => $value) {
                $data .= "<{$key}>{$value}</{$key}>";    
            }
            //附加数据
            $data .= "<data><display>";
            foreach ($list['data'] as $key => $value) {
                $data .= "<{$key}>{$value}</{$key}>";    
            }
            $data .= "</display></data>";   
        }
        $data = '<?xml version="1.0" encoding="UTF-8"?> <urlset><url> '.$data.' </url></urlset>';
        //请求
        $rs = request_by_curl($url, 'post', $data, 3);
        preg_match('/<int>(\d+?)<\/int>/i', $rs, $matches);
        return is_numeric($matches[1]) ? $matches[1] : false;
    }

    /**
     * 描述：递归获取xml
     * @param $data
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    protected  function getXML($data,$theme = "m"){
        $xml = '';
        if(is_array($data))
        {
            foreach($data as $key => $value){
                $xml.= "<{$key}>{$this->getXml($value)}</{$key}>".PHP_EOL;
                if($theme=='m' && $key=='loc') $xml.= '<mobile:mobile type="mobile"/>'.PHP_EOL;
            }
        }
        else
        {
            $xml = $data;
        }
        return $xml;
    }



   /**
     * 生成站点地图XML
     * @param string $theme 版本主题
     * @param string $path 生成路径
     * @param array $lists 结果数组
     * @return boolean
     */
    public function createXML($theme, $path, $lists) {
        if(empty($path) || empty($lists)) return;
        //获取数据
        foreach ($lists as $list) {
            $data .= "<url>".PHP_EOL;
            //基础数据
            /*
            foreach ($list as $key => $value) {
                $data .= "<{$key}>{$value}</{$key}>".PHP_EOL; 
                if($theme=='m' && $key=='loc') $data .= '<mobile:mobile type="mobile"/>'.PHP_EOL;
            }
            */
            $data .= $this->getXML($list,$theme);
            $data .= "</url>".PHP_EOL;
        }
        $urlset = $theme=='m' ? '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">' : '<urlset>';
        $data = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.$urlset.PHP_EOL.$data.' </urlset>';
        //写入
        $fh = fopen($path, 'w'); 
        flock($fh, LOCK_EX);
        fwrite($fh, $data); 
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
     * 生成神马搜索结构化数据
     * @param string $theme 版本主题
     * @param string $path 生成路径
     * @param array $lists 结果数组
     * @return boolean
     */
    public function createShenmaXML($theme, $path, $lists) {
        if(empty($path) || empty($lists)) return;
        //获取数据
        foreach ($lists as $list) {
            $data .= "<item>".PHP_EOL;
            $data .= $this->getXML($list,$theme);
            $data .= "</item>".PHP_EOL;
        }              

        $site_name = C('SITE_NAME');
        $site_host = substr(C('STATIC_URL'), 7);
        $data = <<<EEE
<?xml version="1.0" encoding="UTF-8"?>
<document>
<webName><![CDATA[$site_name]]></webName>
<hostName><![CDATA[$site_host]]></hostName>
<datalist>$data</datalist>        
</document>

EEE;

        //写入
        $fh = fopen($path, 'w'); 
        flock($fh, LOCK_EX);
        fwrite($fh, $data); 
        flock($fh, LOCK_UN);
        fclose($fh);
    }

    /**
      * 生成索引
      * @param string $path 生成路径
      * @param array $lists 结果数组
      * @return boolean
      */
     public function createSiteMap($path, $lists) {
         if(empty($path) || empty($lists)) return;
         //获取数据
         foreach ($lists as $list) {
             $data .= "<sitemap>".PHP_EOL;
             $data .= "<loc><![CDATA[{$list['url']}]]></loc><lastmod>{$list['time']}</lastmod>".PHP_EOL;      
             $data .= "</sitemap>".PHP_EOL;
         }
         $data = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL.' <sitemapindex>'.PHP_EOL.$data.' </sitemapindex>';
         //写入
         $fh = fopen($path, 'w'); 
         flock($fh, LOCK_EX);
         fwrite($fh, $data); 
         flock($fh, LOCK_UN);
         fclose($fh);
     }

}
