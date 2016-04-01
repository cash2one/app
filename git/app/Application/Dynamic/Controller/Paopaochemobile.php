<?php
// +----------------------------------------------------------------------
// | PC6动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class PaopaochemobileController extends BaseController {

    /**
     * API接口初始化
     * @return void
     */
    protected function API_init(){
        //结果关闭trace
        C('SHOW_PAGE_TRACE', false);      
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if($referer){
            $referer = parse_url($referer);
            $host = $referer['host'];
            if(in_array($host, $cors)){
                header('Access-Control-Allow-Origin:http://'. $host);
            }        
        }
    }


     /**
     * 礼包API接口 根据产品标签获取礼包数据
     * @return void
     */
    public function API_PackgeForPTags(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $ptag = I('ptag');
            if(empty($ptag)){
                echo 'null';
                return;
            }
            $ptag_id = M('ProductTags')->where('status = 1 AND title="'.$ptag.'"')->getField('id');
            if(empty($ptag_id)){
                echo 'null';
                return;
            }           
            //根据产品标签查找数据

            $map['t.tid'] = $ptag_id;
            $map['t.type'] = "package";
            $map['d.status'] = 1;
            //$map['d.status']  = array('in','1,-1');
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PMAIN__ as m ON m.id = d.id')
                            ->group('d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->join('INNER JOIN __PRODUCT_TAGS_MAP__ as t ON t.did = d.id')
                            ->where($map)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                    //获取卡号数
                    $value['card_sur'] = $Card->getCardsCount($value['id']);
                    $value['card_all'] =  $Card->getAllCardsCount($value['id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }

    }

     /**
     * 礼包API接口 获取最新礼包数据
     * @return void
     */
    public function API_PackgeLists(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $num = I('num');
            if(!is_numeric($num)) $num = 10;
  
            //根据产品标签查找数据
            $map = array();
            $map['d.status'] = 1;
            //$map['d.status'] = array('in','1,-1');
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PMAIN__ as m ON m.id = d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->where($map)->order('id DESC')->limit($num)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                    //获取卡号数
                    $value['card_sur'] = $Card->getCardsCount($value['id']);
                    $value['card_all'] =  $Card->getAllCardsCount($value['id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }
    }

     /**
     * 礼包API接口 获取最新开服开测数据
     * @return void
     */
    public function API_PackgeListsK(){
            $this->API_init();
            //JSONP或其他src方式的回调函数名
            $callback = I('callback');

            $num = I('num');
            if(!is_numeric($num)) $num = 10;
            //cate
            $cate = I('cate');
            $cate = $cate==3 ? 3 : 6;
  
            //根据产品标签查找数据
            $map = array();
            $map['d.status'] = 1;
            //$map['d.status'] = array('in','1,-1');
            $map['d.category_id'] = $cate;
            $lists = M('Package')
                            ->alias('d')
                            ->join('INNER JOIN __PACKAGE_PARTICLE__ as m ON m.id = d.id')
                            ->field('d.id,d.title,d.cover_id,d.view,d.description')
                            ->where($map)->order('id DESC')->limit($num)->select();
            //echo(M()->_sql());
            if(empty($lists)){
                echo 'null';
                return;
            }else{
                $Card = A('Card'); 
                foreach ($lists as $key => &$value) {
                    $value['url'] = staticUrl('detail', $value['id'], 'Package');
                    $value['pic'] = 'http://libao.pc6.com' . get_cover($value['cover_id'],'path');
                    unset($value['cover_id']);
                }

                echo $callback ? $callback.'('.json_encode($lists).');' : json_encode($lists);
            }
    }





}