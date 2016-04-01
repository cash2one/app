<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Administrator
 * Date: 14-11-12
 * Time: 下午3:01
 * To change this template use File | Settings | File Templates.
 */

namespace Common\Model;


class DownModel extends BaseDocumentModel{
    //主模型名
    protected $document_name = 'Down';

    //分类模型名
    protected $cate_name = 'DownCategory';


    public function downAll($id){
        $info = $this->detail($id);
        if(empty($info)) return false;
        //获取厂商名称
        $info['company'] = M('company')->getFieldById($info['company_id'],'name');

        //获取数据分类
        $info['cate'] = M('downCategory')->getFieldById($info['category_id'],'title');

        //编辑名称
        $info['author'] = M('member')->getFieldByUid($info['uid'],'nickname');

        //图片预览
        $info['previewimg'] = empty($info['previewimg'])?false:explode(',',$info['previewimg']);

        //产品自定义属性
        $info['parameter'] = empty($info['parameter'])?false:explode('|',$info['parameter']);

        //标签
        $info['tags'] = get_tags($id,'down');

        //产品标签
        $info['productTags'] = get_tags($id,'down','product');

        //获取软件评分
        $info['score'] = empty($info['score'])?get_score($id,true):$info['score'];

        //下载
        $address = M('downAddress')->alias('a')->field('a.id,a.url,a.hits,a.site_id,b.site_name title')->join('__PRESET_SITE__ b ON a.site_id = b.id','left')->where('did = '.$id)->order('a.id ASC')->select();

        foreach($address as $k=>&$v){
            $v['url'] = formatAddress($v['url'],$v['site_id']);
            $v['qrcode'] = builtQrcode($v['url']);
        }unset($v);
        if(!empty($address)){
            $info['down'] = $address[0]['url'];
            $info['qrcode'] = $address[0]['qrcode'];
        }
        $info['downAll'] = $address;

        return $info;
    }
}