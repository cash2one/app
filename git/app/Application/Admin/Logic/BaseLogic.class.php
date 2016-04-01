<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Admin\Logic;
use Think\Model;

/**
 * 文档模型逻辑层公共模型
 * 所有逻辑层模型都需要继承此模型
 */
class BaseLogic extends Model {

    /* 自动验证规则 */
    protected $_validate    =   array();

    /* 自动完成规则 */
    protected $_auto        =   array();


    /**
     * 构造函数
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name = '', $tablePrefix = '', $connection = '') {
        //获取主模型表明
        $documentName = $this->document_name ? strtolower($this->document_name) : 'document';
        /* 设置默认的表前缀 */
        $this->tablePrefix = C('DB_PREFIX') . $documentName.'_';
        /* 执行构造方法 */
        parent::__construct($name, $tablePrefix, $connection);
    }

    /**
     * 获取模型详细信息
     * @param  integer $id 文档ID
     * @return array       当前模型详细信息
     */
    public function detail($id) {
        if ($this->getDbFields() == false) {
            $data = array();
        } else {
            $data = $this->field(true)->find($id);
            if (!$data) {
                $this->error = '获取详细信息出错！';
                return false;
            }
        }
        return $data;
    }

    /**
     * 新增或添加模型数据
     * @param  number $id 文章ID
     * @return boolean    true-操作成功，false-操作失败
     */
    public function update($id = 0,&$data) {
        /* 获取数据 */
        $logic = $this->create();

        if ($logic === false) {
            return false;
        }

        /* 字段设置中存储处理 crohn 2014-10-23*/
        $model_id   =   I('model_id',0);
        $logic = model_save_type($model_id, $logic);
        if(!is_array($logic)){
            $this->error = $logic;
            return false;
        }
        //过滤外网的图片
        if($logic['content']){
            $logic['content'] = $this->getCurImg($logic['content']);
        }

        //合并到 指针data
        $data = array_merge($data,$logic);

        if (empty($logic['id'])) {//新增数据
            $logic['id'] = $id;
            $id = $this->add($logic);
            if (!$id) {
                $this->error .= '新增数据失败！';
                return false;
            }
        } else { //更新数据
            $status = $this->save($logic);
            if (false === $status) {
                $this->error .= '更新数据失败！';
                return false;
            }
        }
        return true;
    }

    /**
     * 模型数据自动保存
     * @return boolean
     */
    public function autoSave($id = 0) {
        $this->_validate = array();
        return $this->update($id);
    }


    /**
     * 作者:肖书成
     * 描述:把内容外部的图片资源 下载到本地服务器
     * @param $cont
     * @return mixed
     */
    public function getCurImg($cont){
        //正则匹配所有 图片地址的url
        // "/(src)=[\"|\'| ]{0,}(http:\/\/(.*)\.(gif|jpg|jpeg|bmp|png))[\"|\'| ]{0,}/isU"  (这段正则有问题)
        /**        /<[img|IMG].*?src=[\'|\"](http:\/\/.*?(?:[\.gif|\.jpg|\.jpeg|\.bmp|\.png]))[\'|\"].*?[\/]?>/i  (匹配图片) **/
        /**        "/<img.+?src=(?<t>['\"])(?<img>http:\/\/.+?(?:\.(?:gif|jpg|jpeg|bmp|png)))\g{t}/i" */
        /**     "/<[img|IMG].*?src=[\'|\"](http:\/\/.*?(?:\.(gif|jpg|jpeg|bmp|png)))[\'|\"].*?[\/]?>/i" */
        preg_match_all("/<img.+?src=(?<t>['\"])(http:\/\/.+?(?:\.(gif|jpg|jpeg|bmp|png)))\g{t}/i", $cont, $img_array);

        $basehost = "http://".$_SERVER["HTTP_HOST"];
        $pic_host   = C('PIC_HOST');
        $staticUrl  = C('STATIC_URL');

        $parr       = is_array($pic_host);
        $pic_url    = $pic_host;
        foreach($img_array[2] as $k=>$v){
            if($parr){
                $key = false;
                foreach($pic_host as $k1=>$v1){
                    if(strpos($v1,$v) !== false){
                        $key    =   true;
                        break;
                    }
                }
                if($key){
                    $pic_url    =   $pic_host[0];
                    continue;
                }
            }elseif(strpos($v,$pic_host) !== false){
                continue;
            }elseif(strpos($v,$basehost) !== false){
                continue;
            }elseif(strpos($v,$staticUrl) !== false){
                continue;
            }

            if(!in_array($img_array[3][$k],array('gif','jpg','jpeg','bmp','png'))){
                continue;
            }

            $imgPath = "./Uploads/Gather/".date("Y-m-d")."/";

            if(dir_create($imgPath)){
                $imgPath = $imgPath.uniqid().'.'.$img_array[3][$k];
            }

            if($this->saveImage($v,$imgPath) !== false){
                $cont = str_replace($v, $pic_url.substr($imgPath,1),$cont);
            }

        }

        return $cont;
    }


    /**
     * 作者:肖书成
     * 描述:所传的远程地址和保存路径 下载文件并且保存。
     * @param $path     远程文件地址
     * @param $imgPath  保存服务器的路径
     * @return int
     */
    private function saveImage($path,$imgPath){
        $ch = curl_init($path);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);

        $img    =   curl_exec($ch);//文件信息
        $info   =   curl_getinfo($ch);//头部信息

        curl_close($ch);

        if($info['http_code'] != 200 || strpos($info['content_type'],'image') === false || empty($img)){
            return false;
        }

        $fp = fopen($imgPath,'w');
        $rel =  fwrite($fp, $img);
        fclose($fp);
        return $rel;
    }

}
