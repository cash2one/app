<?php
// +----------------------------------------------------------------------
// | 文件采集
// +----------------------------------------------------------------------
// | Author: Jeffrey Lau <liupan182@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 2015-10-16    Version:1.0.0
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;

class SpiderController extends AdminController
{
    private $name = '采集规则';

    /**
     * 构造函数，处理动态边栏
     *
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
        $config = C('DOWN_SPIDER');
        if (empty($config)) {
            echo "您的站点未配置采集参数...";
            return;
        }
        $this->assign("sid", $config['SID']);
        $this->assign("gid", $config['GID']);
        $this->display();
    }

    /**
     * 获取该页码的详情页面地址
     *
     * @author JeffreyLau
     */
    public function getCategory()
    {
        $page = I('page');
        $url = I('url');
        $webid = I('webid');
        if (empty($webid)) {
            return;
        }
        $url = empty($url) ? 'http://zhushou.360.cn/list/index/cid/2/order/newest?page={page}' : $url;

        $urls = array();
        //===正则列表====
        $pregs = array(

            '1' => array(
                array(
                    'host' => 'http://zhushou.360.cn',
                    'url' => '/<a sid=\"\d*\" href=\"(?<url>[^"]+?)\">/i',
                ),
            ),
            '2' => array(
                array(
                    'host' => 'http://zhushou.360.cn',
                    'url' => '/<a sid=\"\d*\" href=\"(?<url>[^"]+?)\">/i',
                ),
            ),
            '3' => array(
                array(
                    'host' => 'http://app.mi.com',
                    'areaStart' => '全部应用</h3>',
                    'areaEnd' => 'all-pagination',
                    'url' => '/<h5><a href=\"(?<url>[^"]+?)\">[\s\S]+?<\/a><\/h5>/i',
                ),
            ),
            '4' => array(
                array(
                    'host' => 'http://ios.25pp.com',
                    'url' => '/<a class=\"app_a\" target=\"_blank\" title=\".*\" href=\"(?<url>[^"]+?)\">/i',
                ),
            ),
            '5' => array(
                array(
                    'host' => 'http://ios.25pp.com',
                    'url' => '/<a class=\"app_a\" target=\"_blank\" title=\".*\" href=\"(?<url>[^"]+?)\">/i',
                ),
            ),
            '6' => array(
                array(
                    'host' => 'http://www.xyzs.com',
                    'url' => '/<h4 class=\"talkbox\"><a href=\"(?<url>[^"]+?)\"/i',
                ),
            ),
            '7' => array(
                array(
                    'host' => 'http://www.xyzs.com',
                    'url' => '/<h4 class=\"talkbox\"><a href=\"(?<url>[^"]+?)\"/i',
                ),
            ),
            '8' => array(
                array(
                    'host' => 'http://apk.91.com',
                    'areaStart' => 'rptSoft',
                    'areaEnd' => 'Pager',
                    'url' => '/(?<url>[^"]+?)\">立即下载/i',
                ),
            ),
            '9' => array(
                array(
                    'host' => 'http://apk.91.com',
                    'areaStart' => 'rptSoft',
                    'areaEnd' => 'Pager',
                    'url' => '/(?<url>[^"]+?)\">立即下载/i',
                ),
            ),
            '10' => array(
                array(
                    'host' => 'http://www.eoemarket.com',
                    'url' => '/<a href=\"(?<url>[^"]+?)\" class=\"classf_list_img fl\"/i',
                ),
            ),
            '11' => array(
                array(
                    'host' => 'http://www.eoemarket.com',
                    'url' => '/<a href=\"(?<url>[^"]+?)\" class=\"classf_list_img fl\"/i',
                ),
            ),
            '12' => array(
                array(
                    'host' => 'http://www.9game.cn',
                    'url' => '/<a href=\"(?<url>[^"]+?)\" class=\"info\"/i',
                ),
            ),
            '13' => array(
                array(
                    'host' => 'http://apk.hiapk.com',
                    'areaStart' => 'apps_right',
                    'areaEnd' => 'page_box',
                    'url' => '/<span class=\"list_title font14_2\">[\s\S]+?<a href=\"(?<url>[^"]+?)\">/i',
                ),
            ),
            '14' => array(
                array(
                    'host' => 'http://app.youxibaba.cn',
                    'areaStart' => 'content-categoryCtn-content',
                    'areaEnd' => 'page-item',
                    'url' => '/<h4>[\s\S]+?<a href=\"(?<url>[^"]+?)\">.*<\/a><\/h4>/i',
                ),
            ),

        );
        $req_url = str_replace("{page}", $page, $url);
        $list_html = file_get_contents($req_url);
        if ($pregs[$webid][0]['areaStart']) {
            $list_html = $this->getStrs($list_html, $pregs[$webid][0]['areaStart'], $pregs[$webid][0]['areaEnd']);

        }
        preg_match_all($pregs[$webid][0]['url'], $list_html, $listurl);
        $details = array_unique($listurl['url']); //提取详情地址
        foreach ($details as $val) {
            $detailUrl = !stristr($val, "http") ? $pregs[$webid][0]['host'] . $val : $val; //获得详情地址
            $urls[] = $detailUrl;
        }
        $this->ajaxReturn($urls);
    }

    /**
     * 获取详情地址
     *
     */
    public function getDetail()
    {
        $url = I('url');
        $webid = I('webid');
        $cid = I('cid');
        $data_type = I('type');
        if (empty($cid)) {
            return;
        }
        if (empty($data_type)) {
            return;
        }
        import("ORG.simple_html_dom");
        $config = C('DOWN_SPIDER');
        //===正则列表====
        $pregs = array(

            '1' => array(
                array(
                    'host' => 'http://zhushou.360.cn',
                    'title' => '/<h2 id="app-name"><span title="(?<title1>[^"]+?)">(?<title>[^"]+?)<\/span>/i', //done!
                    'icon' => '/<dt><img src="(?<icon>[^"]+?)" width="72" height="72" alt=".*"><\/dt>/i', //done!
                    'size' => '/<span class="s-3">(?<size>[^"]+?(M|KB|kb))<\/span>/i', //done!
                    'version' => '/<td><strong>版本：<\/strong>(?<version>[^"]+?)\</i',
                    'screenshot' => '/<div id="scrollbar" data-snaps="(?<screenshot>[^"]+?)">/i', //done!
                    'content' => '/<div class="breif">(?<content>[^"]+?)<div/i',
                    'source' => '/url=(?<source>[^"]+?(apk))"[\s\S]+?data-sid/i',
                ),
            ),
            '2' => array(
                array(
                    'host' => 'http://zhushou.360.cn',
                    'title' => '/<h2 id="app-name"><span title="(?<title1>[^"]+?)">(?<title>[^"]+?)<\/span>/i', //done!
                    'icon' => '/<dt><img src="(?<icon>[^"]+?)" width="72" height="72" alt=".*"><\/dt>/i',
                    'size' => '/<span class="s-3">(?<size>[^"]+?(M|KB|kb))<\/span>/i',
                    'version' => '/<td><strong>版本：<\/strong>(?<version>[^"]+?)\</i',
                    'screenshot' => '/<div id="scrollbar" data-snaps="(?<screenshot>[^"]+?)">/i',
                    'content' => '/<div class="breif">(?<content>[^"]+?)<div/i',
                    'source' => '/url=(?<source>[^"]+?(apk))"[\s\S]+?data-sid/i',
                ),
            ),
            '3' => array(
                array(
                    'host' => 'http://app.mi.com',
                    'title' => '/<h3>(?<title>[^"]+?)<\/h3>/i',
                    'icon' => '/<img class="yellow-flower" src="(?<icon>[^"]+?)" alt=".*" width="114" height="114">/i',
                    'size' => '/<li>(?<size>[^"]+?(M|KB|kb))<\/li>/i',
                    'version' => '/版本号：<\/li><li>(?<version>[^"]+?)<\/li>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=img-view]',
                    'content' => '/<p class="pslide">(?<content>[^"]+?)<\/p>/i',
                    'source' => '/<a href="(?<source>[^"]+?)" class="download">直接下载<\/a>/i',
                ),
            ),
            '4' => array(
                array(
                    'host' => 'http://ios.25pp.com',
                    'title' => '/<h1>(?<title>[^"]+?)<\/h1>/i',
                    'icon' => '/<div class="pic">.*<img src="(?<icon>[^"]+?)" alt=".*">/i',
                    'size' => '/<li>大小：(?<size>[^"]+?)<\/li>/i',
                    'version' => '/<li>版本：(?<version>[^"]+?)<\/li>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=picBox]',
                    'content' => '/<div class="intro-content">(?<content>[^"]+?)<\/div>/i',
                    'source' => '//i',
                ),
            ),
            '5' => array(
                array(
                    'host' => 'http://ios.25pp.com',
                    'title' => '/<h1>(?<title>[^"]+?)<\/h1>/i',
                    'icon' => '/<div class="pic">.*<img src="(?<icon>[^"]+?)" alt=".*">/i',
                    'size' => '/<li>大小：(?<size>[^"]+?)<\/li>/i',
                    'version' => '/<li>版本：(?<version>[^"]+?)<\/li>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=picBox]',
                    'content' => '/<div class="intro-content">(?<content>[^"]+?)<\/div>/i',
                    'source' => '//i',
                ),
            ),
            '6' => array(
                array(
                    'host' => 'http://www.xyzs.com',
                    'title' => '/<h1 id=\"title\">(?<title>[^"]+?)<\/h1>/i', //done!
                    'icon' => '/<img src="(?<icon>[^"]+?)"[\s\S]+?alt=\".*\"[\s\S]+?title=\".*\"[\s\S]+?class=\"xy_img/i', //done!
                    'size' => '/<p>大小：(?<size>[^"]+?(M|KB|kb))<\/p>/i', //done!
                    'version' => '/<p>版本：(?<version>[^"]+?)<\/p>/i', //done!
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=PicBox]',
                    'content' => '/<span id="content">(?<content>[^"]+?)<\/span>/i',
                    'source' => '//i',
                ),
            ),
            '7' => array(
                array(
                    'host' => 'http://www.xyzs.com',
                    'title' => '/<h1 id=\"title\">(?<title>[^"]+?)<\/h1>/i',
                    'icon' => '/<img src="(?<icon>[^"]+?)"[\s\S]+?alt=".*"[\s\S]+?title=".*"[\s\S]+?"/i',
                    'size' => '/<p>大小：(?<size>[^"]+?)<\/p>/i',
                    'version' => '/<p>版本：(?<version>[^"]+?)<\/p>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=PicBox]',
                    'content' => '/<span id="content">(?<content>[^"]+?)<\/span>/i',
                    'source' => '//i',
                ),
            ),
            '8' => array(
                array(
                    'host' => 'http://apk.91.com',
                    'title' => '/<title>(?<title>[^"]+?)_/i',
                    'icon' => '/<img src="(?<icon>[^"]+?)"[\s\S]+?alt=".*"[\s\S]+?width="75"[\s\S]+?height="75"/i',
                    'size' => '/<li>文件大小：(?<size>[^"]+?)<\/li>/i',
                    'version' => '/版本：(?<version>[^"]+?)<a/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=viewport]',
                    'content' => '/<div[\s\S]+?class="o-content">(?<content>[^"]+?)<\/div>/i',
                    'source' => '/var thunder_url[\s\S]+?=[\s\S]+?"(?<source>[^"]+?(apk))"/i',
                ),
            ),
            '9' => array(
                array(
                    'host' => 'http://apk.91.com',
                    'title' => '/<title>(?<title>[^"]+?)_/i',
                    'icon' => '/<img src="(?<icon>[^"]+?)"[\s\S]+?alt=".*"[\s\S]+?width="75"[\s\S]+?height="75"/i',
                    'size' => '/<li>文件大小：(?<size>[^"]+?)<\/li>/i',
                    'version' => '/版本：(?<version>[^"]+?)<a/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=viewport]',
                    'content' => '/<div[\s\S]+?class="o-content">(?<content>[^"]+?)<\/div>/i',
                    'source' => '/var thunder_url[\s\S]+?=[\s\S]+?"(?<source>[^"]+?(apk))"/i',
                ),
            ),
            '10' => array(
                array(
                    'host' => 'http://www.eoemarket.com',
                    'title' => '/<title>(?<title>[^"]+?)下载/i',
                    'icon' => '/<span>[\s\S]+?<img src="(?<icon>[^"]+?)">[\s\S]+?<\/span>/i',
                    'size' => '/<em>大小：(?<size>[^"]+?)<\/em>/i',
                    'version' => '/<em>版本：(?<version>[^"]+?)<\/em>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=introd_imgsc]',
                    'content' => '/<div class="profilecon_c">(?<content>[^"]+?)<\/div>/i',
                    'source' => '//i',
                ),
            ),
            '11' => array(
                array(
                    'host' => 'http://www.eoemarket.com',
                    'title' => '/<title>(?<title>[^"]+?)下载/i',
                    'icon' => '/<span>[\s\S]+?<img src="(?<icon>[^"]+?)">[\s\S]+?<\/span>/i',
                    'size' => '/<em>大小：(?<size>[^"]+?)<\/em>/i',
                    'version' => '/<em>版本：(?<version>[^"]+?)<\/em>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=introd_imgsc]',
                    'content' => '/<div class="profilecon_c">(?<content>[^"]+?)<\/div>/i',
                    'source' => '//i',
                ),
            ),
            '12' => array(
                array(
                    'host' => 'http://www.9game.cn',
                    'title' => '/<div class=\"title\">(?<title>[^"]+?)<\/div>/i',
                    'icon' => '/<span class="img">[\s\S]+?<img src="(?<icon>[^"]+?)" alt=".*">/i',
                    'size' => '//i',
                    'version' => '//i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=special-img]',
                    'content' => '/<p class="text">(?<content>[^"]+?)<\/p>/i',
                    'source' => '/<a href="(?<source>[^"]+?)" rel="nofollow" class="btn android"/i',
                ),
            ),
            '13' => array(
                array(
                    'host' => 'http://apk.hiapk.com',
                    'title' => '/<div id=\"appSoftName\" class=\"detail_title left\">(?<title>[^"]+?)<\/div>/i',
                    'icon' => '/<img[\s\S]+?width="72"[\s\S]+?height="72"[\s\S]+?src="(?<icon>[^"]+?)"/i',
                    'size' => '/<span id="appSize" class="font14">(?<size>[^"]+?)<\/span>/i',
                    'version' => '//i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=screen_img_box]',
                    'content' => '/<pre id="softIntroduce">(?<content>[^"]+?)<\/pre>/i',
                    'source' => '/<a href="(?<source>[^"]+?)" class="link_btn" rel="nofollow" id="appInfoDownUrl"/i',
                ),
            ),
            '14' => array(
                array(
                    'host' => 'http://app.youxibaba.cn',
                    'title' => '/<h1>(?<title>[^"]+?)<\/h1>/i',
                    'icon' => '/<div class="content-detailCtn-icon">[\s\S]+?<p><img src="(?<icon>[^"]+?)">/i',
                    'size' => '/<div>(?<size>[^"]+?(M|KB|kb))<\/div>/i',
                    'version' => '/<span>版本号：<\/span>[\s\S]+?<div>(?<version>[^"]+?)<\/div>/i',
                    'screenshot_preg' => '0', //是否正则,0为否
                    'screenshot' => 'div[class=slide-line]',
                    'content' => '/<div id="toggle_content" class="toggle-detail-content">(?<content>[^"]+?)<\/div>/i',
                    'source' => '/<a href="(?<source>[^"]+?(apk))">下载<\/a>/i',
                ),
            ),

        );

        $detail_html = file_get_html($url); //获取所有内容
        preg_match($pregs[$webid][0]['title'], $detail_html, $app_title); //匹配标题
        preg_match($pregs[$webid][0]['icon'], $detail_html, $app_icon); //匹配图标
        preg_match($pregs[$webid][0]['size'], $detail_html, $app_size); //匹配大小
        preg_match($pregs[$webid][0]['version'], $detail_html, $app_version); //匹配版本

        preg_match($pregs[$webid][0]['content'], $detail_html, $app_content); //匹配内容
        preg_match($pregs[$webid][0]['source'], $detail_html, $app_source); //匹配下载

        $title = $app_title['title']; //标题
        $title = rtrim(ltrim($title));
        $icon = $app_icon['icon']; //图标
        $size = $app_size['size']; //大小
        $version = rtrim($app_version['version']); //版本

        $content = $app_content['content']; //内容
        $content = rtrim(ltrim($app_content['content'])); //内容
        $source = $app_source['source']; //apk地址

        if ($webid == '3') {
            $source = "http://app.mi.com" . $source;
        }
        if ($webid == '13') {
            $source = "http://apk.hiapk.com" . $source;
        }
        if ($pregs[$webid][0]['screenshot_preg'] == '0') {
            $screenshot = $detail_html->find($pregs[$webid][0]['screenshot'], 0)->innertext;
        } else {
            preg_match($pregs[$webid][0]['screenshot'], $detail_html, $app_screenshot); //匹配截图
            $screenshot = $app_screenshot['screenshot']; //截图
        }

        if ($webid == '1' || $webid == '2') {
//360截图特殊处理
            $img_screenshot[] = explode(",", $screenshot);
            $sliderArray = $img_screenshot[0];
        } else if ($webid == '10' || $webid == '11') {
            preg_match_all("/\<img.*?data-original\=\"(.*?)\"[^>]*>/i", $screenshot, $mscreenshot);
            $sliderArray = $mscreenshot[1];
        } else {
            preg_match_all("/\<img.*?src\=\"(.*?)\"[^>]*>/i", $screenshot, $mscreenshot);
            $sliderArray = $mscreenshot[1];
        }

        $fileUrl = $this->downApk($source, $title);

        //截图处理---
        //下载地址提示编辑
        //下载ICON图片
        $icon_path = $this->replacePath($icon);
        $this->downloadPic($icon, $icon_path); //下载图片
        $pic['path'] = str_replace("./", "/", $icon_path);
        $pic['create_time'] = time();
        $pic['status'] = "1";
        $icon_id = M('Picture')->add($pic);
        $imgs_id = "";
        //下载屏幕截图-----------
        foreach ($sliderArray as $key => $val) {
            $path = $this->replacePath($val);
            $this->downloadPic($val, $path); //下载图片
            $picid = M('Picture')->add(array(
                'path' => str_replace("./", "/", $path),
                'title' => '',
                'status' => 1,
                'old' => '',
                'create_time' => time(),
            ));
            $imgs_id .= $picid . ",";
        }

        $imgs_id = rtrim($imgs_id, ",");

        //构造数据-----------
        $data = array();
        $data_main = array();
        $data['uid'] = "1";
        $data['category_id'] = $cid;
        $data['smallimg'] = $icon_id; //小图标
        $data['previewimg'] = $imgs_id; //截图
        $data['title'] = $title;
        $data['size'] = $size;
        $data['model_id'] = $config['MODEL'];
        $data['type'] = "2";
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['status'] = "0";

        $data_main['content'] = $content;
        $data_main['size'] = $size;
        $data_main['version'] = $version;
        $source_Url = $url;
        $this->insertData($data_type, $data, $data_main, $source_Url, $fileUrl); //插入数据

    }

    /**
     * 插入数据到数据库
     *
     * @param array $tableType 数据类型
     * @param array $data
     * @param array $data_main
     * @param string $source_Url 下载地址
     * @param string $file 文件名
     */

    private function insertData($tableType = "1", $data = array(), $data_main = array(), $source_Url = "", $file = "")
    {
        $config = C('DOWN_SPIDER');
        if (empty($data)) {
            return;
        }
        $d = M("Down")->where(array("title" => $data['title']))->find();
        if ($d) {
            $this->ajaxReturn($data['title'] . " - 数据已经存在");
            return;
        }
        $down = M("Down")->add($data);
        $tableName = $tableType == "1" ? $config['STABLE'] : $config['GTABLE']; //软件1，游戏2
        $dmain = M($tableName)->add($data_main);
        if ($down && $dmain) {
//成功后添加下载
            $downApk['did'] = $down;
            $downApk['url'] = empty($file) ? ('下载失败,原网页：' . $source_Url) : $file;
            $downApk['site_id'] = $config['DOWNID'];
            $downApk['update_time'] = time();
            $downAddress = M("DownAddress")->add($downApk);
            $this->ajaxReturn($data['title'] . " - 入库成功");
        } else {
            $this->ajaxReturn($data['title'] . " - 入库失败");
        }

    }

    /**
     * 下载文件
     *
     * @param $url 下载包地址
     * @param $title 标题
     * @return string 服务器返回的下载地址
     */

    private function downApk($url, $title)
    {
        $config = C('DOWN_SPIDER');
        $apkpath = date("Y-m", time());
        $apkname = get_pinyin($title) . rand(100, 999) . ".apk";

        $serverApi = "http://218.75.155.43:5000/down?url=" . $url . "&path=" . $config['NAME'] . "_source/" . $apkpath . "/" . $apkname;
        $serverResponse = file_get_contents($serverApi);
        //if ($serverResponse == "success") {
        // 使用aria2的RPC调用下载服务接口 liuliu 2106-3-11
        import("ORG.Aria2.PHPAria2");
        $aria2 = new \Aria2\PhpAria2('http://218.75.155.43:6800/jsonrpc');
        $serverResponse = $aria2->addUri([$url], ['dir' => $config['NAME'] . "_source/" . $apkpath . "/" , 'out' => $apkname]);
        if (!empty($serverResponse) && $serverResponse!='null') {
            $apkUrl = "http://" . $config['URL'] . "/" . $config['NAME'] . "_source/" . $apkpath . "/" . $apkname; //添加成功后的下载地址
        } else {
            $apkUrl = "0";
        }
        return $apkUrl;
    }

    /**
     * 图片路径获取
     *
     * @param $src_url 图片地址
     * @return string 图片地址
     */
    private function replacePath($src_url)
    {
        $image = getimagesize($src_url);
        $src = $image['mime'];
        $suffix = "";
        if (strpos($src, "jpeg") > 0) {
            $suffix = ".jpeg";
        } else if (strpos($src, "jpg") > 0) {
            $suffix = ".jpg";
        } else if (strpos($src, "png") > 0) {
            $suffix = ".png";
        } else if (strpos($src, "gif") > 0) {
            $suffix = ".gif";
        } else {
            $suffix = ".jpeg";
        }
        $dir = "./Uploads/Picture/" . date("Y-m-d", time()) . "/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $path = $dir . md5(rand(1000000, 999999999) . time()) . $suffix;
        return $path;
    }

    /**
     * 下载图片
     *
     * @param $file_url 图片地址
     * @param $save_to 保存路径
     */
    private function downloadPic($file_url, $save_to)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);

        $downloaded_file = fopen($save_to, 'w');
        fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);

    }

    /**
     * 字符串截取
     *
     * @param $s 源字符串
     * @param $s1 开始字符串
     * @param $s2 结束字符串
     * @return string 结果
     */
    private function getStrs($s, $s1, $s2)
    {
        $n1 = strpos($s, $s1) + strlen($s1); //开始位置
        $n2 = strpos($s, $s2, $n1); //结束位置
        return substr($s, $n1, $n2 - $n1);
    }
}
