<?php
// +----------------------------------------------------------------------
// |  开始APP数据接口
// +----------------------------------------------------------------------
// |  Author: 刘鎏
// |  Time  : 2015-12-4
// +----------------------------------------------------------------------

namespace API\Controller;

class SaappController extends BaseController
{

    /**
     * 根据标签获取数据列表
     * @return string 接口显示结果
     */
    public function getlist()
    {
        $tagid  =   I('tagid');
        if (!empty($tagid) && !is_numeric($tagid)) {
            $this->ajaxReturn(['status'=>0, 'error'=>'参数错误'],$this->returnType);
        }
        $page  =   (I('page') && is_numeric(I('page')) && I('page')>0) ? floor(I('page')) : 1;
        $pagesize  =   I('pagesize') && is_numeric(I('pagesize')) ? I('pagesize') : 20;
        ( 1 > $pagesize || $pagesize > 250 ) && $pagesize = 20;
        $snum = floor(($page-1)*$pagesize);
        $snum = $snum<0 ? 0 : $snum;


        // 查询所有数据使用缓存
        if (empty($tagid)) {
            $result = S('sa_allapp_'. $pagesize . '_' . $page);
            if($result) $this->ajaxReturn($result,$this->returnType,false);
        }

        // 查询
        $result = [];
        $m = M('Down')
                    ->alias('d')
                    ->join('__DOWN_DMAIN__ m ON d.id= m.id')
                    ->join('__TAGS_MAP__ tm ON d.id= tm.did')
                    ->field('d.id as appid,d.title as appname,m.package_name as packagename,m.version_code as versioncode,m.version,tm.sort as position,d.smallimg')
                    ->limit($snum, $pagesize);
        // tagid为空则查询所有数据
        if (empty($tagid)) {
            $result = $m->where('tm.type="down" AND d.status = 1')
                        ->order('d.id desc')->select();
        } else {
            $result = $m->where('tm.type="down" AND tm.tid='.$tagid.' AND d.status = 1')
                        ->order('tm.sort asc,d.id desc')->select();
        }

        $result || $this->ajaxReturn(['status'=>0, 'error'=>'数据被禁用'],$this->returnType);
        foreach ($result as &$value) {
            $value['appicon'] = $value['smallimg'] ? get_cover($value['smallimg'], 'path') : '';
            $value['url'] = getFileUrl($value['appid']);
            $value['tagid'] = M('TagsMap')->where('did='.$value['appid'].' AND type="down"')->getField('tid',true);
            unset($value['smallimg']);
        }

        $result = ['status'=>1, 'error'=>'', 'data'=>$result];

        // 存储所有数据缓存
        empty($tagid) && S('sa_allapp_'. $pagesize . '_' . $page,json_encode($result, JSON_UNESCAPED_SLASHES),1200);

        $this->ajaxReturn(json_encode($result, JSON_UNESCAPED_SLASHES), $this->returnType, false);

    }

    /**
     * 统计用户点击次数
     * @return string 接口显示结果
     */
    public function setcount()
    {
        if (!IS_POST) $this->ajaxReturn(['status'=>0, 'error'=>'请求错误'],$this->returnType);

        $args = json_decode(I('args'), true);
        $args || $this->ajaxReturn(['status'=>0, 'error'=>'请求错误'],$this->returnType);

        foreach ($args as $arg) {
            // 添加点击数
            if (!is_numeric($arg['appid']) || !is_numeric($arg['count'])) $this->ajaxReturn(['status'=>0, 'error'=>'参数错误'],$this->returnType);
            M('Down')->where('id='.$arg['appid'])->setInc('view', $arg['count']);
        }

        $result = ['status'=>1, 'error'=>''];
        $this->ajaxReturn(json_encode($result, JSON_UNESCAPED_SLASHES), $this->returnType, false);
    }

}