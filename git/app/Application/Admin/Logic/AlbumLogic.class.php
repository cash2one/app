<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhudesheng
// +----------------------------------------------------------------------
namespace Admin\Logic;


/**
 * 图库模型子模型 - 图库模型
 */
class AlbumLogic extends BaseLogic{

    //主模型名
    protected $document_name = 'Gallery';

    /* 自动验证规则 */
    protected $_validate = array(
		// array('imgpack', 'require', '请上传图片'),
    );

    /**
     * 新增或添加模型数据
     * @param  number $id 文章ID
     * @return boolean    true-操作成功，false-操作失败
     */
    public function update($id = 0) {
        /* 获取数据 */
        $data = $this->create();
		
        if ($data === false) {
            return false;
        }
		
		// 重新组装数据
		$count   = count($data['imgpack']['title']);
		
		$imgpack = array();
		for($i = 0; $i < $count; $i++){
			$imgpack[$i]['image'] = $data['imgpack']['image'][$i];
			$imgpack[$i]['thumb'] = $data['imgpack']['thumb'][$i];
			$imgpack[$i]['title'] = $data['imgpack']['title'][$i];
			$imgpack[$i]['mark']  = $data['imgpack']['mark'][$i];
		}
		
		$data['imgpack'] = serialize($imgpack);
		
        /* 字段设置中存储处理 crohn 2014-10-23*/
        $model_id   =   I('model_id',0);
        $data = model_save_type($model_id, $data);
        if(!is_array($data)){
            $this->error = $data;           
            return false;
        }

        if (empty($data['id'])) {//新增数据
            $data['id'] = $id;
            $id = $this->add($data);
            if (!$id) {
                $this->error = '新增数据失败！';
                return false;
            }
        } else { 
			$absPack = $this->where(array('id' => $data['id']))->getField('imgpack');

			//更新数据
            $status = $this->save($data);
            if (false === $status) {
                $this->error = '更新数据失败！';
                return false;
            }
			
			// 图库文件删除
			if($absPack){
				$absPack  = unserialize($absPack);
				
				$original = array();
				if($data['imgpack']){
					$original = unserialize($data['imgpack']);
				}
				
				if(count($original) < count($absPack)){
					foreach($absPack as $k => $val){
						foreach($original as $value){
							if($val['image'] == $value['image']){
								unset($absPack[$k]);
							}
						}
					}
					
					foreach($absPack as $val){
						$image = ltrim($val['image'], '/');
						
						$thumb = ltrim($val['thumb'], '/');

						if(is_file($image)){
							unlink($image);
						}

						if(is_file($thumb)){
							unlink($thumb);
						}
					}
				}
			}
        }
        return true;
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
			$data['imgpack'] = unserialize($data['imgpack']);
        }
        return $data;
    }
}
