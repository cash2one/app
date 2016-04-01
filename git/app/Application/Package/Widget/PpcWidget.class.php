<?php
// +----------------------------------------------------------------------
// | 卡号相关信息
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com>
// +----------------------------------------------------------------------

namespace Package\Widget;
use Think\Controller;

class PpcWidget extends Controller{

    /**
     * 最新发号信息
     * @return void
     */
    public function packageNewList(){
        $list = M('Card')
                    ->alias('c')
                    ->where('c.draw_status=1 ')
                    ->join('__PACKAGE__ as p ON c.did = p.id')
                    ->field('p.title as title, p.id as id')
                    ->group('p.id')
                    ->limit(5)
                    ->select();
        $this->assign('list', $list);
        $this->display('Widget/cardNewList');
    }

    /**
     * 热门激活码
     * @return void
     */
    public function packageHotList(){
        $list = M('Package')
                    ->alias('p')
                    ->where('p.category_id=2 AND p.status=1 AND c.draw_status=1')
                    ->join('LEFT JOIN __CARD__ as c ON c.did = p.id')
                    ->group('p.id')
                    ->order('numSur')
                    ->field('p.title as title,p.id as id,count(c.id) as numsur')
                    ->limit(5)
                    ->select();
        //echo D('Package')->getLastSql();
        $this->assign('list', $list);
        $this->display('Widget/cardHotList');
    }

	
}
