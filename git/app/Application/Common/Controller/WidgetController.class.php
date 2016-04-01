<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/4/10
 * Time: 11:01
 */

namespace Common\Controller;
use Think\Controller;

class WidgetController extends Controller{


    /**
     * @author 肖书成
     * @crate_time 2015/4/10
     * @comments 专门为厂商、专题、专区、K页面的合集页所用
     * @param int GET sid
     * @param string $table
     * @param int $where
     * @param string $order
     * @param int $row
     */
    public function hj($template,$table,$where = 1,$order = 'id desc',$row = 18,$isMobile = false,$pageTitle =''){
        //参数接收
        $WidgetSEO = I('page_id');

        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);


        $table = ucfirst(strtolower($table));
        //专区专题厂商路径地址
        $c = array('Feature'=>C('FEATURE_ZT_DIR'),'Batch'=>C('FEATURE_ZQ_DIR'),'Special'=>C('FEATURE_K_DIR'),'Company'=>'');
        $crumbs = array('Feature'=>'专题合集','Batch'=>'专区合集','Special'=>'k页面合集','Company'=>'厂商合集');

        //分页获取数据
        $count  = M($table)->where('1 AND '.$where)->count();// 查询满足要求的总记录数

        //是否返回总页数
        if(I('gettotal')){
            if(empty($count)){
                echo 1;
            }else{
                echo ceil($count/$row);
            }
            exit();
        }

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        //数据查询
        $lists = M($table)->where('1 AND '.$where)->order($order)->page($p, $row)->select();

        //判断是否是手机
        $staUrl = $isMobile?C('MOBILE_STATIC_URL'):C('STATIC_URL');

        //给数据赋值url地址
        foreach($lists as $k=>&$v){
            if($v['horizontalimg']){
                $v['icon'] = $v['horizontalimg'];
            }

            if($c[$table]){
                $v['staticUrl'] = $staUrl . '/'.$c[$table] . (substr($v['url_token'],0,1)=='/'?$v['url_token']:'/'.$v['url_token']);
            }else{
                $v['staticUrl'] = $staUrl . (substr($v['url_token'],0,1)=='/'?$v['url_token']:'/'.$v['url_token']);
            }
        }
        unset($v);

        //生成路径规则
        $path = $page_info['path'] . getStaticExt();

        //分页
        $this->pages($count,$row,'',false,$path,false,$isMobile);

        //为了融合手机页面的面包屑
        $info['title'] = $crumbs[$table];

        //数据赋值
        $this->assign(array(
            'pTitle'  =>  $pageTitle,                                   //页面名字
            'lists'   =>  $lists,                                       //赋值数据
            'crumbs'  =>  $crumbs[$table],                              //面包屑
            'info'    =>  $info,                                        //为了融合手机页面的面包屑
            "SEO"     =>  WidgetSEO(array('special',null,$WidgetSEO))   //SEO
        ));
        $this->display(T($template));
    }


    /**
     * @author 肖书成
     * @crate_time 2015/4/10
     * @comments 专为分页所用
     * @param int $count
     * @param int $row
     * @param string $parameter
     * @param bool $static_id
     * @param bool $path
     * @param bool $isMobile
     */
    public function pages($count,$row,$parameter='',$static_id=false,$path=false,$isBatch = false,$isMobile = false){
        $Page = new \Think\Page($count, $row,$parameter, $static_id, $path);// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        if($isMobile){
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }
        $show       = $Page->show();// 分页显示输出
        if($isBatch){$show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';}
        $this->assign('page',$show);// 赋值分页输出
        if($isBatch){S('Batch'.'PageNumber',array($isBatch=>ceil($count/$row)),86400);}
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用获取礼包数据的方法
     * @param int $id
     * @param bool $field
     * @param int $where
     * @param bool $isPmain
     * @param bool $isParticle
     * @param int $row
     * @param bool $isPage
     * @return mixed
     */
    public function getPackageData($id,$field = false,$where = 1,$isPmain=true,$isParticle = false,$row = 3,$isPage = false,$isMobile = false){
        $tid = $this->getProductTag($id);
        if(empty($tid)) return false;

        $wheres ="d.tid = $tid AND d.type = 'package' AND $where";

        //是否分页
        if($isPage){
            $count = M('Package')->alias('a')->join('__PRODUCT_TAGS_MAP__ d ON a.id = d.did')->where($wheres)->count();
            $this->pages($count,$row,$id,$isMobile);
        }

        $model = M('Package')->alias('a');
        if($field)
            $model->field($field);

        if($isPmain){
            $model->join('__PACKAGE_PMAIN__ b ON a.id = b.id');
        }

        if($isParticle)
            $model->join('__PACKAGE_PARTICLE__ c ON a.id = c.id');

        $model->join('__PRODUCT_TAGS_MAP__ d ON a.id = d.did')->where($wheres);

        if($isPage){
            //分页数据获取
            $p = intval(I('p'));
            if (!is_numeric($p) || $p<=0 ) $p = 1;
            $model->page($p,$row);
        }elseif($row)
            $model->limit($row);

        return $model->select();
    }

   /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用来处理获取数据表的对象方法(比较适用三大模块表，document，down，package)
     * @return Object
     * @param string $feild
     * @param $base
     * @param bool $main
     * @param bool $cate
     * @param bool $productMap
     * @param bool $tagsMap
     * @param bool $append
     */
    public function solveTable($feild = '*',$base,$main= false,$cate = false,$productMap = false,$tagsMap = false,$append = false){
        $base = ucfirst(strtolower($base));
        $model = M($base)->field($feild)->alias('b');

        if($main){$model->join($this->splitHumpStr($main).' m ON b.id = m.id','left');}

        if($cate){$model->join($this->splitHumpStr($cate).' c ON b.category_id = c.category_id');}

        if($productMap){$model->join('__PRODUCT_TAGS_MAP__ p ON b.id = p.did');}

        if($tagsMap){$model->join('__TAGS_MAP__ t ON b.id = t.did');}

        if($append){
            foreach($append as $k=>$v){
                $model->join($this->splitHumpStr($k).' '.$v);
            }
        }

        return $model;
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用来处理获取数条件的方法
     * @param $where
     * @param string $order
     * @param bool $p
     * @param bool $row
     * @param bool $find
     * @return array
     */
    public function solveWhere($where,$order = 'b.id DESC',$p = false,$row = false,$find = false){
        $this->where($where);
        if($order){
            $record = clone($this);
            $this->order($order);
        }

        if($p){
            $this->page($p,$row);
        }elseif($row){
            $this->limit($row);
        }

        if($find){
            return $this->find();
        }else{
            $lists = $this->select();
            if($lists){
                return array($record->count(),$lists);
            }

            return null;
        }
    }

    public function splitHumpStr($str){
        $str = preg_split('/(?=[A-Z])/', $str);
        return strtoupper('__'.implode('_',$str).'__');
    }


    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用来处理获开测开服数据的方法
     * @param $cate_id
     * @param $where
     * @param int $sta
     * @param int $len
     * @return mixed
     */
    public function getTestServer($cate_id,$where,$sta=0,$len=10){
        //获取数据
        $date = M('Package')->alias('a')->join('__PACKAGE_PARTICLE__ b ON a.id = b.id')->where("status = 1 AND category_id = $cate_id AND $where")->limit($sta,$len)->order('b.start_time DESC')->select();

        //获取每条数据的产品标签
        foreach($date as $k=>$v){
            $tid = get_tags($v['id'],'package','product')[0]['id'];
            if($tid){
                $games = M('productTagsMap')->alias('a')->field('b.*,c.system,d.name company,e.url,e.site_id')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')->join('__COMPANY__ d ON c.company_id = d.id','left')->join('__DOWN_ADDRESS__ e ON b.id = e.did')->order('b.id DESC')->where("a.tid = $tid AND a.type='down'")->select();
                if(empty($games)){
                    continue;
                }

                foreach($games as $k1=>$v1){
                    if($v1['system'] == 1){
                        if($date[$k]['androidUrl'])
                            continue;
                        $date[$k]['androidUrl'] = formatAddress($v1['url'],$v1['site_id']);
                        $date[$k]['androidCode'] = builtQrcode($date[$k]['androidUrl']);
                    }elseif($v1['system'] == 2){
                        if($date[$k]['iosUrl'])
                            continue;
                        $date[$k]['iosUrl'] = formatAddress($v1['url'],$v1['site_id']);
                        $date[$k]['iosCode'] = builtQrcode($date[$k]['iosUrl']);
                        if(!$date[$k]['company']){
                            $date[$k]['company'] = $v1['company']?$v1['company']:'';
                        }
                    }
                }
                $date[$k]['game'] = $games[0]['id'];
                $date[$k]['description'] = empty($date[$k]['description'])?$games[0]['description']:$date[$k]['description'];
            }
        }

        return $date;
    }

//$id,$field = false,$where = 1,$isPmain=true,$isParticle = false,$row = 3,$isPage = false,$isMobile = false
//$count,$row,$parameter='',$static_id=false,$path=false,$isBatch = false,$isMobile = false //page
    protected function getDownData($field = '*',$where = 1,$order = false,$p = false,$row = false,$find = false,$isDMain = false,$isCate = false,$isAddress = false,$isProTag = false,$isTag = false){
        $this->solveTable($field,'Down',$isDMain?'downDmain':$isDMain,$isCate?'downCategory':$isCate,$isProTag,$isTag,array('downAddress'=>'d ON m.id = d.did'))
             ->solveWhere($where,$order,$p,$row,$find);
    }

    /************************************************下载模块常用的方法********************************************/

//    //获取下载数据的方法
//    public function getDownDate($field = '*',$addMain,$where,$order,$limit = false){
//        return M('down')->field($field)->where("id != $id AND status = 1")->order($order2)->limit($row - count($tagGame))->select();
//    }

    /**
     * @Author 肖书成
     * @createTime 2015/4/20
     * @Comments 用来获取相同标签的游戏
     * @param int $row
     * @param string $order1
     * @param string $order2
     * @return array (返回大于等于$row行数)
     */
    protected function relateGame($id,$tags,$row,$order1 = 'id DESC',$order2 = 'id DESC',$field = false){
        //相同标签游戏
        $tags = implode(',',$tags);
        if(!empty($tags)){
            $model = M('tagsMap')->distinct(true)->field('did')->where("tid IN($tags) AND type='down'")->buildSql();
            $tagGame = M('Down')->field('id,title,smallimg'.($field?','.$field:''))->where('id IN('.$model.') AND status = 1 AND id !='.$id)->order($order1)->limit($row)->select();
        }

        //如果游戏小于所指定的条数，则按照其他相关条件补上
        if(count($tagGame)<$row){
           // $tagGame = array_merge((array)$tagGame,M('Down')->field('id,title,smallimg'.($field?','.$field:''))->where("id != $id AND status = 1")->order($order2)->limit($row - count($tagGame))->select());
//            $tagGame = array_merge((array)$tagGame,$this->getRankDown($field,'id != '.$id,$row - count($tagGame)));
            $tagGame = $this->appendDownData($field,'id != '.$id,$tagGame,$row);
        }
        return $tagGame;
    }

    protected function appendDownData($field,$where,$arr,$row){
        return array_merge((array)$arr,$this->getRankDown($field,$where,$row - count($arr)));
    }

    protected function getRankDown($field,$where = 1,$row){
        return M('Down')->field('id,title,smallimg'.($field?','.$field:''))->where('id>= ((SELECT MAX(id) FROM onethink_down)-(SELECT MIN(id) FROM onethink_down))*RAND() + (SELECT MIN(id) FROM onethink_down) AND status = 1 AND '.$where)->limit($row)->select();
    }


}