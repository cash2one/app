<?php
// +----------------------------------------------------------------------
// | 下载模型
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------
namespace Admin\Model;

/**
 * 下载模型
 */
class DownModel extends BaseDocumentModel
{
    //主模型名
    public $document_name = 'Down';

    //分类模型名
    public $cate_name = 'DownCategory';

    //前台模块名
    public $module = 'Down';

    /**
     * 删除状态为-1的数据（包含扩展模型）
     * @return true 删除成功， false 删除失败
     * @author huajie <banhuajie@163.com>
     */
    public function remove()
    {
        //查询假删除的基础数据

        //删除分类权限检测 以后需要恢复
        // if ( is_administrator() ) {
        //     $map = array('status'=>-1);
        // }else{
        //     $cate_ids = AuthGroupModel::getAuthCategories(UID);
        //     $map = array('status'=>-1,'category_id'=>array( 'IN',trim(implode(',',$cate_ids),',') ));
        // }
        $map = array('status' => -1);
        
        $base_list = $this->where($map)->field('id,model_id')->select();
        //删除扩展模型数据
        $base_ids = array_column($base_list, 'id');
        //孤儿数据
        $orphan = get_stemma($base_ids, $this, 'id,model_id');

        $all_list  = array_merge($base_list, $orphan);
        foreach ($all_list as $key => $value) {
            $logic = $this->logic($value['model_id']);
            $logic->delete($value['id']);
        }

        //删除基础数据
        $ids = array_merge($base_ids, (array)array_column($orphan, 'id'));
        if (!empty($ids)) {
            // 删除静态文件 crohn 2015-3-18
            foreach ($ids as $id) {
                // PC版本路径
                if (is_null($this->staticInstance)) {
                    $this->createStaticInstance();
                }

                $file = $this->staticInstance->pathDetail($id);
                if (!empty($file)) {
                    $file = $this->staticInstance->static_root_dir .'/'. $file;
                    @unlink($file);
                }
                // 手机版本路径
                if (is_null($this->staticInstanceM)) {
                    $this->createStaticInstanceM();
                }

                $m_file = $this->staticInstanceM->pathDetail($id);

                if (!empty($m_file)) {
                    $m_file = $this->staticInstanceM->static_root_dir .'/'. $m_file;
                    @unlink($m_file);
                }
            }
            
            $res = $this->where(array('id' => array('IN', trim(implode(',', $ids), ','))))->delete();


            //删除标签关联数据 crohn 2014-8-7
            D('TagsMap')->remove($ids, $this->document_name);
            //删除产品标签关联数据 crohn 2014-10-9
            D('ProductTagsMap')->remove($ids, $this->document_name);
            //删除下载地址数据 crohn 2014-11-7
            M('DownAddress')->where(array('did' => array('IN', trim(implode(',', $ids), ','))))->delete();
        }

        return $res;
    }


    /**
     * HOOK方法，加载不同的特殊逻辑
     * @param string $method 方法名
     * @param array $params 参数
     * @return void
     * @author crohn <lllliuliu@163.com>
     */
    protected function hook($method, $params = array())
    {
        switch ($method) {
            case '_after_update':
            case '_after_insert':
                $id = $params['id'];
                //删除所有地址数据
                M('DownAddress')->where('did='. $id)->delete();
                //存入新数据 data里面已经被过滤，用I获取
                $site_id = I('down_address_siteid');
                $name = I('down_address_name');
                $url = I('down_address_url');
                $busine = I('down_address_busine');

                // 修改人：朱德胜 2016/3/10 曾经此处用for循环，由于修改了模板JS表达方式，无法精确遍历，修改为foreach。
                foreach ($site_id as $i => $value) {
                    $add = [
                        'did'           =>  $id,
                        'update_time'   =>  NOW_TIME,
                        'site_id'       =>  $site_id[$i],
                        'name'          =>  $name[$i],
                        'isbusine'      =>  !empty($busine[$i]) ? 1 : 0
                    ];
           
                    $curl = $url[$i];
                    if (empty($curl)) {
                        $curl = M('PresetSite')->getFieldByid($site_id[$i], 'autofill');
                        if ($curl) {
                            $curl = str_replace('{id}', $id, $curl);
                        }
                    }
                    $add['url'] = $curl;

                    M('DownAddress')->add($add);
                }
                break;
            //添加修改
            case 'update':
                if (C('BAIDU_RDF_POST_SWITCH')) {
                    //发送百度结构化数据推送
                    $info = D('Down')->detail($params);
                    if (empty($info)) {
                        return;
                    }

                    //PC版本
                    $sitemap_base['loc'] =  "<![CDATA[".staticUrl('detail', $info['id'], $this->document_name)."]]>";//拼接xml出现EntityRef: expecting ';'错误,url中的条件分割符&应该写成&amp;或者<![CDATA
                    $sitemap_base['lastmod'] = "<![CDATA[".date("Y-m-d", $info['update_time'])."]]>";
                    $sitemap_base['changefreq'] = 'always';
                    $sitemap_base['priority'] = 1;
                    $sitemap_data['name'] = "<![CDATA[{$info['title']}]]>";
                    $sitemap_data['fileSize'] = "<![CDATA[".format_size($info['size'], 'M')."]]>";
                    $sitemap_data['inLanguage'] = "<![CDATA[".showText($info['language'], 'language', false, $this->document_name)."]]>";
                    $sitemap_data['license'] = "<![CDATA[".showText($info['licence'], 'licence', false, $this->document_name)."]]>";
                    $sitemap_data['dateModified'] = "<![CDATA[".date("Y-m-d", $info['update_time'])."]]>";
                    $sitemap_data['operatingSystem'] = "<![CDATA[".showText($info['system'], 'system', false, $this->document_name)."]]>";
                    $sitemap_data['provider'] = "<![CDATA[".C('SITE_NAME')."]]>";
                    $sitemap_data['description'] = "<![CDATA[{$info['description']}]]>";
                    $sitemap_data['version'] = "<![CDATA[".str_replace('v', '', $info['version'])."]]>";
                   // $sitemap_data['downloadUrl'] = "<![CDATA[".staticUrl('detail',  $info['id'], $this->document_name)."]]>"; //拼接xml出现EntityRef: expecting ';'错误,url中的条件分割符&应该写成&amp;或者<![CDATA[文本内容]]>
                    $sitemap_data['downloadCount'] = "<![CDATA[".$info['view']."]]>";
                    $siteMapI =  new \Common\Library\SiteMapLibrary();
                    $siteMapI->postStructuring('p', array(array('base' => $sitemap_base, 'data' => $sitemap_data)));
                    //手机版本
                    $sitemap_base['loc'] = "<![CDATA[". staticUrlMobile('detail', $info['id'], $this->document_name)."]]>";//拼接xml出现EntityRef: expecting ';'错误,url中的条件分割符&应该写成&amp;或者<![CDATA
                    //$sitemap_data['downloadUrl'] = "<![CDATA[". staticUrlMobile('detail', $info['id'], $this->document_name)."]]>";//拼接xml出现EntityRef: expecting ';'错误,url中的条件分割符&应该写成&amp;或者<![CDATA
                    $siteMapI->postStructuring('m', array(array('base' => $sitemap_base, 'data' => $sitemap_data)));
                }
        }
    }
}
