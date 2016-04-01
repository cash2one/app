<?php
namespace Admin\Model;
use Think\Model;

/**
 * 标签模型
 */
class TagsMapModel extends Model{
    /**
     * 清空回收站的时候删除关联数据
     * @param  array $ids did数组,文档表id
     * @param  string $type 类型
     * @return  boolean
     * @author crohn <lllliuliu@163.com>
     */
    public function remove($ids, $type = 'document'){
		if(is_array($ids)){
			$ids = trim(implode(',', $ids), ',');
		}

        $map = array(
			'did'	=>	array('IN', $ids),
            'type'	=>	strtolower($type)
		);

        return $this->where( $map )->delete();
    }

    /**
     * 文档数据插入或修改时操作
     * @param  array $data 数据
     * @param  string $type 类型
     * @return void
     * @author zhudesheng
	 * @edittime 2015-07-24
     */
    public function update($data, $type = 'document'){
		$_tag  = I('post.Tags');
		
		// 模型名称
        $type = strtolower($type);

		// 文档ID
        $id	  = $data['id'];
		
		if(is_array($_tag)){
			$tagid = implode(',', $_tag);
			
			$tag   = M('tags')->select($tagid);
		}

		// 文档标签
		$archiveTag = $this->where(array('type' => $type, 'did' => $id))->select();

		if(!$archiveTag && $tag){
			// 增加分支处理层
            $tagid = array_column($tag, 'id');
			$dataList = array();
			foreach($_tag as $value){
                if(in_array($value,$tagid))
                {
                    $dataList[] = array(
                        'tid'			=>	$value,
                        'did'			=>	$id,
                        'sort'			=>	50,//关联表默认排序大小50 update liujun 2015-07-24
                        'type'			=>	$type,
                        'update_time'	=>	NOW_TIME,
                        'create_time'	=>	NOW_TIME
                    );
                }
			}
			
			// 记录标签记录
			$this->addAll($dataList);
		}else{
			// 修改分支处理层
			if($archiveTag && !$tag){
				// 删除全部标签
				$this->where(array('type' => $type, 'did' => $id))->delete();
			}else{
				$archiveid = array_column($archiveTag, 'tid');
				$tagid	   = array_column($tag, 'id');
				$diffid	   = array_diff($archiveid, $tagid);
				
				// 移除部分标签
				if($diffid){
					$map = array('type' => $type, 'did' => $id, 'tid'	=>	array('IN', $diffid));

					$this->where($map)->delete();
				}
				
				$diffid	   = array_diff($tagid, $archiveid);
				
				$dataList = array();
				foreach($_tag as $value){
					if(in_array($value, $diffid)){
						$dataList[] = array(
							'tid'			=>	$value,
							'did'			=>	$id,
							'sort'			=>	50,//关联表默认排序大小50 update liujun 2015-07-24
							'type'			=>	$type,
							'update_time'	=>	NOW_TIME,
							'create_time'	=>	NOW_TIME
						);
					}
				}
				
				// 记录新标签记录
				if($dataList){
					$this->addAll($dataList);
				}
			}
		}
    }

}
