<?php
// +----------------------------------------------------------------------
// | 礼包基础模型
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Admin\Model;

/**
 * 礼包基础模型
 */
class PackageModel extends BaseDocumentModel{
    //主模型名
    public $document_name = 'Package';

    //分类模型名
    public $cate_name = 'PackageCategory';

    //前台模块名
    public $module = 'Package';


    /**
     * 删除状态为-1的数据（包含扩展模型）
     * @return true 删除成功， false 删除失败
     * @author huajie <banhuajie@163.com>
     */
    public function remove(){
        //查询假删除的基础数据

        //删除分类权限检测 以后需要恢复
        // if ( is_administrator() ) {
        //     $map = array('status'=>-1);
        // }else{
        //     $cate_ids = AuthGroupModel::getAuthCategories(UID);
        //     $map = array('status'=>-1,'category_id'=>array( 'IN',trim(implode(',',$cate_ids),',') ));
        // }
        $map = array('status'=>-1);     
        $base_list = $this->where($map)->field('id,model_id')->select();
        //删除扩展模型数据
        $base_ids = array_column($base_list,'id');
        //孤儿数据
        $orphan   = get_stemma( $base_ids,$this, 'id,model_id');

        $all_list  = array_merge( $base_list,$orphan );
        foreach ($all_list as $key=>$value){
            $logic = $this->logic($value['model_id']);
            $logic->delete($value['id']);
        }

        //删除基础数据
        $ids = array_merge( $base_ids, (array)array_column($orphan,'id') );
        if(!empty($ids)){
            // 删除静态文件 crohn 2015-3-18
            foreach ($ids as $id) {
                // PC版本路径
                if(is_null($this->staticInstance)) $this->createStaticInstance();
                $file = $this->staticInstance->pathDetail($id);
                if(!empty($file)){
                    $file = $this->staticInstance->static_root_dir .'/'. $file;
                    @unlink($file);
                }
                // 手机版本路径
                if(is_null($this->staticInstanceM)) $this->createStaticInstanceM();
                $m_file = $this->staticInstanceM->pathDetail($id);
                if(!empty($m_file)){
                    $m_file = $this->staticInstanceM->static_root_dir .'/'. $m_file;
                    @unlink($m_file);
                }
            }

            $res = $this->where( array( 'id'=>array( 'IN',trim(implode(',',$ids),',') ) ) )->delete();
            //删除标签关联数据 crohn 2014-8-7
            D('TagsMap')->remove($ids, $this->document_name);
            //删除产品标签关联数据 crohn 2014-10-9
            D('ProductTagsMap')->remove($ids,  $this->document_name);
            //删除卡号关联数据 crohn 2014-10-9
            D('Card', 'Logic')->remove($ids);
        }

        return $res;
    }


    /**
     * 插入成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_insert(&$data, $options){
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);
        //卡号处理
        D('Card', 'Logic')->addForm($data);        
    }

    /**
     * 更新成功后操作
     * @param  array $data 数据
     * @return  void
     * @author crohn <lllliuliu@163.com>
     */
    protected function _after_update(&$data, $options){
        //标签数据处理
        D('TagsMap')->update($data, $this->document_name);
        //产品标签数据处理
        D('ProductTagsMap')->update($data, $this->document_name);

        //是否启用副分类
        if(C('SUB_CATEGORY')=='1'){
            //副分类数据处理
            D('CategoryMap')->update($data, $this->document_name);
        }

        //卡号处理
        D('Card', 'Logic')->addForm($data); 
    }
}