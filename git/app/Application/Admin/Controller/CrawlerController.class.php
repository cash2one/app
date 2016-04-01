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

class CrawlerController extends AdminController
{
    private $name = '采集规则';


    /**
     * 构造函数，处理动态边栏
     * @return void
     */
    public function _initialize()
    {
        parent::_initialize();
    }


    public function index()
    {
        $lists = M('Crawler')->select();
        $this->assign('lists', $lists);
        $this->assign('name', $this->name);
        $this->display();
    }

    public function add()
    {
        $this->assign('type', '1');
        $this->assign('name', $this->name);
        $this->display();

    }

    public function edit()
    {
        $id = I('get.id');
        $c = M('Crawler')->where(array("id" => $id))->find();
        $this->assign('c', $c);
        $this->assign('name', $this->name);
        $this->assign('type', '2');
        $this->display('add');

    }

    public function add_action()
    {
        $name = I('name');
        $id = I('id');
        $type = I('type');
        $description = I('description');
        $multi_img = I('multi_img');
        $title_str = I('title_rule');
        $description_str = I('description_rule');
        $content_str = I('content_rule');
        $version_str = I('version_rule');
        $size_str = I('size_rule');
        $fileurl_str = I('file_rule');//下载地址
        $icon_str = I('icon_img');
        $images_str = I('multi_img');
        $seo_title = I('seo_title');
        $seo_keywords = I('seo_keywords');
        $seo_description = I('seo_description');
        if (empty($name)) {
            $this->error('规则名不能为空');
        }
        $c = M('Crawler');
        $data['name'] = $name;
        $data['description'] = $description;
        $data['title_str'] = $title_str;
        $data['description_str'] = $description_str;
        $data['content_str'] = $content_str;
        $data['version_str'] = $version_str;
        $data['size_str'] = $size_str;
        $data['fileurl_str'] = $fileurl_str;
        $data['icon_str'] = $icon_str;
        $data['images_str'] = $images_str;

        $data['seo_title_str'] = $seo_title;
        $data['seo_keywords_str'] = $seo_keywords;
        $data['seo_description_str'] = $seo_description;

        $data['status'] = '1';
        if ($type == "1") {//新增
            $c->add($data);
            $this->success('新增成功！', U('index'));
        } else {
            $c->where(array("id" => $id))->save($data);
            $this->success('编辑成功！', U('index'));
        }


    }

    public function removeRule()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            return;
        }
        $c = M('Crawler')->where(array("id" => $id))->delete();
        if ($c) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //禁用启用
    public function setStatus()
    {
        $id = I('id');
        $type = I('type');
        if (!is_numeric($id)) {
            return;
        }
        if ($type == "1") {//启用
            $data['status'] = "1";
        } else {//禁用
            $data['status'] = "0";
        }
        $c = M('Crawler')->where(array("id" => $id))->save($data);
        if ($c) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }

    }


    public function test()
    {
        $rule_id = '2';
        $url = "http://app.mi.com/detail/2005";
        //	 if(!is_numeric($rule_id)){return;}
        //  $rule = M("Crawler")->where(array('id'=>$rule_id))->find();
        import("ORG.simple_html_dom");
        $doc = file_get_html($url); //获取所有内容


        $fileurl = $doc->find("div[class=app-info-down]", 0)->innertext;//下载地址


        $pattern = "/\<img.*?src\=\"(.*?)\"[^>]*>/i";
        preg_match_all($pattern, $slider, $match);
        preg_match_all($pattern, $icon, $iconmatch);

        $fileurl = str_replace("href=\"javascript:void(0)\"", "", $fileurl);
        $fileurl = str_replace("ex_url", "href", $fileurl);
        $fileurl = str_replace("data-apkurl", "href", $fileurl);//(?\'href\'[^<]+)
        if (strstr($contentHtml, "data-snaps")) {
            $pat = '/url=(.*)" data-sid/';//360正则
        } else {
            $pat = '/<a .*?href="(.*?)".*?>/is';
        }


        preg_match($pat, $fileurl, $murl);

        exit();
        if (strstr($rule['description_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['description_str'], $dindex);
            $dom_did = $dindex[2];
            $description_r = str_replace("(" . $dindex[0] . ")", "", $rule['description_str']);
            $description = $doc->find($description_r, $dom_did)->plaintext;//描述
        } else {
            $description = $doc->find($rule['description_str'], 0)->innertext;//描述
        }


        $content = $doc->find($rule['content_str'], 0)->innertext;//内容

        if (strstr($rule['version_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['version_str'], $mindex);
            $dom_id = $mindex[2];
            $version_r = str_replace("(" . $mindex[0] . ")", "", $rule['version_str']);
            $version = $doc->find($version_r, $dom_id)->plaintext;//版本
        } else {
            $version = $doc->find($rule['version_str'], 0)->plaintext;//版本
        }

        if (strstr($rule['size_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['size_str'], $sindex);
            $sdom_id = $sindex[2];
            $size_r = str_replace("(" . $sindex[0] . ")", "", $rule['size_str']);
            $size = $doc->find($size_r, $sdom_id)->innertext;//软件大小
        } else {
            $size = $doc->find($rule['size_str'], 0)->innertext;//软件大小
        }

        $icon = $doc->find($rule['icon_str'], 0)->innertext;//图标
        $slider = $doc->find($rule['images_str'], 0)->innertext;//多图
        $fileurl = $doc->find($rule['fileurl_str'], 0)->innertext;//下载地址

        $slider = str_replace("src=\"\"", "", $slider);
        $slider = str_replace("data-src", "src", $slider);
        $slider = str_replace("'", "\"", $slider);

        $icon = str_replace("src=\"\"", "", $icon);
        $icon = str_replace("data-src", "src", $icon);
        $icon = str_replace("'", "\"", $icon);

        $pattern = "/\<img.*?src\=\"(.*?)\"[^>]*>/i";
        preg_match_all($pattern, $slider, $match);
        preg_match_all($pattern, $icon, $iconmatch);

        $fileurl = str_replace("href=\"javascript:void(0)\"", "", $fileurl);
        $fileurl = str_replace("ex_url", "href", $fileurl);
        $fileurl = str_replace("data-apkurl", "href", $fileurl);//(?\'href\'[^<]+)
        $contentHtml = $doc->innertext;
        if (strstr($contentHtml, "data-snaps")) {
            $pat = '/url=(.*)" data-sid/';//360正则
        } else {
            $pat = '/<a .*?href="(.*?)".*?>/is';
        }


        preg_match($pat, $fileurl, $murl);


        var_dump($description);

    }

    /**
     * 抓取结果
     */
    public function get()
    {
        $rule_id = I('rules');
        $url = I('url');
        if (!is_numeric($rule_id)) {
            return;
        }
        $rule = M("Crawler")->where(array('id' => $rule_id))->find();
        import("ORG.simple_html_dom");
        $doc = file_get_html($url); //获取所有内容

        $title = $doc->find($rule['title_str'], 0)->plaintext;//标题


        if (strstr($rule['description_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['description_str'], $dindex);
            $dom_did = $dindex[2];
            $description_r = str_replace("(" . $dindex[0] . ")", "", $rule['description_str']);
            $description = $doc->find($description_r, $dom_did)->plaintext;//描述
        } else {
            $description = $doc->find($rule['description_str'], 0)->innertext;//描述
        }


        $content = $doc->find($rule['content_str'], 0)->innertext;//内容

        if (strstr($rule['version_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['version_str'], $mindex);
            $dom_id = $mindex[2];
            $version_r = str_replace("(" . $mindex[0] . ")", "", $rule['version_str']);
            $version = $doc->find($version_r, $dom_id)->plaintext;//版本
        } else {
            $version = $doc->find($rule['version_str'], 0)->plaintext;//版本
        }

        if (strstr($rule['size_str'], "(index=")) {
            preg_match("/(index=(\d+?))/i", $rule['size_str'], $sindex);
            $sdom_id = $sindex[2];
            $size_r = str_replace("(" . $sindex[0] . ")", "", $rule['size_str']);
            $size = $doc->find($size_r, $sdom_id)->innertext;//软件大小
        } else {
            $size = $doc->find($rule['size_str'], 0)->innertext;//软件大小
        }

        $icon = $doc->find($rule['icon_str'], 0)->innertext;//图标
        $slider = $doc->find($rule['images_str'], 0)->innertext;//多图
        $fileurl = $doc->find($rule['fileurl_str'], 0)->innertext;//下载地址

        $slider = str_replace("src=\"\"", "", $slider);
        $slider = str_replace("data-src", "src", $slider);
        $slider = str_replace("'", "\"", $slider);

        $icon = str_replace("src=\"\"", "", $icon);
        $icon = str_replace("data-src", "src", $icon);
        $icon = str_replace("'", "\"", $icon);

        $pattern = "/\<img.*?src\=\"(.*?)\"[^>]*>/i";
        preg_match_all($pattern, $slider, $match);
        preg_match_all($pattern, $icon, $iconmatch);

        $fileurl = str_replace("href=\"javascript:void(0)\"", "", $fileurl);
        $fileurl = str_replace("ex_url", "href", $fileurl);
        $fileurl = str_replace("data-apkurl", "href", $fileurl);//(?\'href\'[^<]+)
        $contentHtml = $doc->innertext;
        if (strstr($contentHtml, "data-snaps")) {
            $pat = '/url=(.*)" data-sid/';//360正则
        } else {
            $pat = '/<a .*?href="(.*?)".*?>/is';
        }


        preg_match($pat, $fileurl, $murl);


        //下载处理ICON图标
        $json = array();

        $icon_path = $this->replace_path($iconmatch[1][0]);
        $this->download_pic($iconmatch[1][0], $icon_path);//下载图片
        $iconid = M('Picture')->add(array(
            'path' => str_replace("./", "/", $icon_path),
            'title' => '',
            'status' => 1,
            'old' => '',
            'create_time' => time()
        ));
        $json['icon'] = $icon_path;
        $json['iconid'] = $iconid;
        //------------下载多图-------------

        //360多图兼容处理
        $sliderArray = array();

        if (strstr($contentHtml, "data-snaps")) {
            $sliders_360 = $doc->find('div[id=scrollbar]', 0)->getAttribute('data-snaps');
            $img_360[] = explode(",", $sliders_360);
            $sliderArray = $img_360[0];
        } else {
            $sliderArray = $match[1];
        }

        foreach ($sliderArray as $key => $val) {
            $path = $this->replace_path($val);
            $this->download_pic($val, $path);//下载图片
            $picid = M('Picture')->add(array(
                'path' => str_replace("./", "/", $path),
                'title' => '',
                'status' => 1,
                'old' => '',
                'create_time' => time()
            ));
            $img[$key]['url'] = $path;
            $img[$key]['id'] = $picid;

        }
        $size = str_replace(" ", "", strip_tags($size));
        $json['title'] = str_replace(" ", "", strip_tags($title));
        $json['description'] = strip_tags($description);
        $content = str_replace("<b>【更新内容】</b>", "<h3>更新说明</h3>", $content);
        $json['content'] = $content;
        $json['version'] = str_replace("版本：", "", $version);
        $json['size'] = $size;
        $json['imgs'] = $img;
        $json['seotitle'] = str_replace("{标题}", $title, $rule['seo_title_str']);
        $json['seokeywords'] = str_replace("{标题}", $title, $rule['seo_keywords_str']);
        $json['seodescription'] = str_replace("{标题}", $title, $rule['seo_description_str']);
        $apkpath = date("Y-m", time());
        $apkname = get_pinyin($title) . rand(100, 999) . ".apk";
        if (!strstr($murl[1], "http://")) {
            $down = "http://app.mi.com" . $murl[1];
        } else {
            $down = $murl[1];
        }
//        $serverApi = "http://218.75.155.43:5000/down?url=" . $down . "&path=afs_source/" . $apkpath . "/" . $apkname;
//        $serverResponse = file_get_contents($serverApi);
        import("ORG.Aria2.PHPAria2");
        $aria2 = new \Aria2\PhpAria2('http://218.75.155.43:6800/jsonrpc');
        $serverResponse = $aria2->addUri([$down], ['dir' => "afs_source/" . $apkpath . "/" , 'out' => $apkname]);
        if (!empty($serverResponse) && $serverResponse!='null') {
       // if ($serverResponse == "success") {
            $json['apk'] = "http://d2.anfensi.com/afs_source/" . $apkpath . "/" . $apkname;//添加成功后的下载地址
            $json['ret'] = "1";
        } else {
            $json['apk'] = "添加到服务器自动下载失败,请重试或手动上传";
            $json['ret'] = "0";
        }
        echo json_encode($json);
        unset($json);


    }

    private function replace_path($src_url)
    {
        $image = getimagesize($src_url);
        $src = $image['mime'];
        $suffix = "";
        if (strpos($src, "jpeg") > 0) {
            $suffix = ".jpeg";
        } else {
            if (strpos($src, "jpg") > 0) {
                $suffix = ".jpg";
            } else {
                if (strpos($src, "png") > 0) {
                    $suffix = ".png";
                } else {
                    if (strpos($src, "gif") > 0) {
                        $suffix = ".gif";
                    } else {
                        $suffix = ".jpeg";
                    }
                }
            }
        }
        $dir = "./Uploads/Picture/" . date("Y-m-d", time()) . "/";
        if (!file_exists($dir)) {
            mkdir($dir, 0777);
        }
        $path = $dir . md5(rand(1000000, 999999999) . time()) . $suffix;
        return $path;
    }


    //下载图片
    private function download_pic($file_url, $save_to)
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

    //字符串截取函数
    private function get_str($s, $s1, $s2)
    {
        $n1 = strpos($s, $s1) + strlen($s1);   //开始位置
        $n2 = strpos($s, $s2, $n1);               //结束位置
        return substr($s, $n1, $n2 - $n1);
    }
}
