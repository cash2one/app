<?php
namespace Home\Widget;
use Think\Controller;

/**
 * 后台控制器
 * @author leha.com
 */

class P7230batchWidget extends Controller {
	//文档模型名
	protected $document_name = 'Batch';
	protected $base_view = 'Feature';
	protected $main_title='专区';
	protected $model_id='16';
    public $page = null;

    /**
     * 专区基础数据调用
     * @param $id
     * @return mixed
     */
	public function __call($method, $args){
		return;
	}
	
    public function menus($id){
        //专区基本信息
        $batch = M('batch')->where('id = '.$id)->find();

        //专区导航首页
        $home = M($this->document_name)->field('id,title,seo_title,keywords,description,url_token,icon,abet')->where('id ='.(($batch['pid']>0?$batch['pid']:$id)))->find();

        //获取标签
        $home['tags'] = array_column(get_tags($home['id'],'batch'),'title');

        //获取一条游戏数据
        $game = get_base_by_tag($id,'batch','down','product');

        //SEO
        $SEO = array();
        if($batch['id']!=$home['id']){
            $sValue = array(
                '资讯'=>array(
                    'title'=>$home['title'].'资讯_'.$home['title'].'活动 - 7230手游网',
                    'keywords'=>$home['title'].'资讯,'.$home['title'].'活动'
                ),
                '资料'=>array(
                    'title'=>$home['title'].'资料_'.$home['title'].'数据库 - 7230手游网',
                    'keywords'=>$home['title'].'资料,'.$home['title'].'数据库'
                ),
                '攻略'=>array(
                    'title'=>$home['title'].'攻略_'.$home['title'].'玩法 - 7230手游网',
                    'keywords'=>$home['title'].'攻略,'.$home['title'].'玩法'
                ),
                '问答'=>array(
                    'title'=>$home['title'].'问答_'.$home['title'].'常见问题答疑 - 7230手游网',
                    'keywords'=>$home['title'].'问答,'.$home['title'].'常见问题答疑'
                ),
                '视频'=>array(
                    'title'=>$home['title'].'视频攻略_'.$home['title'].'游戏视频 - 7230手游网',
                    'keywords'=>$home['title'].'视频攻略,'.$home['title'].'游戏视频'
                ),
                '下载'=>array(
                    'title'=>$home['title'].'下载_'.$home['title'].'手机版下载 - 7230手游网',
                    'keywords'=>$home['title'].'下载,'.$home['title'].'手机版下载'
                ),
                '列表'=>array(
                    'title'=>$home['title'].'游戏攻略_'.$home['title'].'游戏资讯 - 7230手游网',
                    'keywords'=>$home['title'].'游戏攻略,'.$home['title'].'游戏资讯'
                ));

            $sTitle = substr($batch['title'],-6);

            foreach($sValue as $k=>$v){
                if($sTitle == $k){
                    $SEO['title'] = $v['title'];
                    $SEO['keywords'] = $v['keywords'];
                }
            }
        }else{
            $SEO['title'] = $batch['seo_title'];
            $SEO['keywords'] = $batch['keywords'];
        }
        $SEO['description'] = $batch['description']?$batch['description']:$game['description'];


        //导航
        $batches = M($this->document_name)->field('url_token,title')->where('enabled=1 and pid ='.($batch['pid']>0?$batch['pid']:$id))->order('sort DESC')->select();

        $more =array();
        foreach($batches as $k=>&$v){
            $v['title'] = substr($v['title'],-6);
            if(substr($v['url_token'],0,1)!='/'){
                $v['url_token'] = '/'.$v['url_token'];
            }

            if(substr($v['url_token'],-1,1)!='/'){
                $v['url_token'] .= '/';
            }

            if($v['title']=='攻略'){
                $more['guide'] = $v['url_token'];
            }elseif($v['title']=='问答'){
                $more['ask'] = $v['url_token'];
            }elseif($v['title']=='视频'){
                $more['video'] = $v['url_token'];
            }elseif($v['title']=='列表'){
                $more['document'] = $v['url_token'];
            }
        }
        unset($v);

        $this->assign(array(
            'home'=>$home,        //专区导航首页
            'batch'=>$batch,      //专区基本信息
            'SEO'=>$SEO,
            'more'=>$more,  //更多
            'game'=>$game,      //游戏数据
            'empty'=>'<span style="display: block; color: #666; text-align: center; line-height: 30px; height: 30px; width: 100%">暂时没有数据</span>',
        ));
		return $batches;
	}

    /**
     * 获取下载表（Down）的下载地址
     * @param $id
     * @return string
     */
    public function downAddr($id){
        if(empty($id)) return false;
        $info = M('down')->alias('a')->field('a.id,a.title name,a.smallimg,a.previewimg,a.view,a.update_time,a.create_time,b.content,b.size,b.system,b.licence,b.version,b.language,c.title')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.id = '.$id)->find();
        $address = M('downAddress')->where('did = '.$id)->find();
        if(empty($address)) return false;
        $info['hits'] = (int)$info['view']>20?$info['view']:(int)$info['view'] + (int)$address['hits'];
        $info['previewimg'] = explode(',',$info['previewimg']);

        //获取下载地址
        $info['down'] = formatAddress($address['url'],$address['site_id']);

        return $info;
    }

    public function newlibao(&$package){
        $package = M('package')->field('id,title')->where("category_id = 1")->order('create_time desc')->limit(5)->select();
    }


    //分页
    //分页
    public function pages($count,$row,$id,$isMobile){
    	$rows=M($this->document_name)->field('url_token')->find($id);
        $Page = new \Think\Page($count,$row,'',false,C('FEATURE_ZQ_DIR').'/'.$rows['url_token'].C('FEATURE_PAGE'));// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        if($isMobile){
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
        }

        $show       = $Page->show();// 分页显示输出
        //var_dump(array($id=>ceil($count/$row)),'----');
        $show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';
        $this->assign('page',$show);// 赋值分页输出
        S($this->document_name.'PageNumber',array($id=>ceil($count/$row)),86400);
    }
    

    /********************************************专区六大频道*****************************************/
    //资料频道
    public function info($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);
        return $this->getDocumentDate($id,'d.tid = 23059',false,true);
    }

    //攻略频道
    public function guide($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);
        return $this->getDocumentDate($id,'a.category_id IN(89,92,95)',false,false);
    }

    //问答频道
    public function ask($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);
        return $this->getDocumentDate($id,'d.tid = 23058',false,true);
    }

    //资讯列表
    public function news($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);
        return $this->getDocumentDate($id,'a.category_id IN(87,90,94)',false,false);
    }

    //游戏视频
    public function video($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);
        return $this->getDocumentDate($id,'a.video = 1',false,false);
    }

    //下载频道
    public function down($id){
        $tid = $this->getTagId($id);
        $downIds = M('productTagsMap')->where('tid = \''.$tid .'\' AND type="down"')->order('did DESC')->select();
        if(empty($downIds)){
            return false;
        }

        $list = array();
        foreach($downIds as $k=>$v){
            $date = $this->downAddr($v['did']);

            if($date){
                if($date['system'] == '1' && (int)$list[0]<(int)$date['update_time']){//android
                    $list[0] = $date;
                }elseif($date['system'] == '2' && (int)$list[1]<(int)$date['update_time']){//IOS
                    $list[1] = $date;
                }elseif($date['system'] == '4' && (int)$list[2]<(int)$date['update_time']){//PC
                    $list[2] = $date;
                }
            }
        }


        //生成下载二维码
        foreach($list as $k=>&$v){
            $v['erCode'] = builtQrcode($v['down']);
        }
        unset($v);

        sort($list);
        $this->assign('down',$list);
        return $list;
    }
    /******************************************专区六大频道结束**********************************************/

    /******************************************首页数据**************************************************/
    //首页左上推荐大图
    public function indexPosition($id){
        //开测开服数据
        $tid = $this->getTagId($id);
        $this->indexLeft($tid);


        $list = $this->getDocumentDate($id,'a.position & 8',true,false,1);
        if(empty($list)){
            $list = $this->getDocumentDate($id,'a.cover_id > 0',true,false,1);
        }
        return $list;
    }

    //获取游戏新闻 产品标签 + 资讯（14条）
    public function indexNews($id){
        //return $this->getDocumentDate($id,'a.category_id IN(87,90,94)',true,false,14);
        $list = $this->getDocumentDate($id,'a.category_id IN(87,90,94)',true,false,14);
        if(count($list)<14){
            $list = array_merge((array)$list,(array)$this->getDocumentDate($id,'a.category_id IN(89,92,95)',true,false,(14 - count($list))));
        }
        return array_filter($list);
    }

    //首页 攻略部分
    public function indexGuide($id){
        $imgLists = $this->getDocumentDate($id,'a.cover_id > 0 AND a.category_id IN(89,92,95)',true,false,3);
        $lists = $this->getDocumentDate($id,'a.category_id IN(89,92,95)',true,false,13);
        foreach($imgLists as $k=>$v){
            foreach($lists as $k1=>$v1){
                if($v1['id']==$v['id']){
                    unset($lists[$k1]);
                }
            }
        }
        $lists = array_filter(array_merge((array)$imgLists,(array)$lists));
        //dump($lists);die;
        return $lists;
    }

    //首页 评测部分
    public function indexTest($id){
        return $this->getDocumentDate($id,'a.category_id = 91',true,false,1);
    }

    //首页 问答部分
    public function indexAsk($id){
        return $this->getDocumentDate($id,'d.tid = 23058');
    }

    //首页 关卡部分
    public function indexPass($id){

        //获取文章带“关卡”标签的数据
        $date = $this->getDocumentDate($id,'d.tid = 23068',true,true,80000);
        if(empty($date))
            return false;

        $dateids = implode(',',array_column($date,'id'));
        $list = array();

        for($i = 1;$i<20000;$i++){
            //判断章节
            $title = '第'.$this->ToChinaseNum($i).'章';
            $dtid = M('tags')->where("title = '$title'")->order('id DESC')->getField('id');
            if(!$dtid) break;

            //根据章节查找数据
            $result = $this->getDocumentDate($id,'a.id IN('.$dateids.') AND d.tid ='.$dtid,true,true,1000);
            if(empty($result)) break;

            $list[$i]['title'] = $title.'节';
            $list[$i]['data'] = $result;
        }
        return $list;
    }


    //阿拉伯数字转换成中文数字 (数字转换器) 备注：此方法只有 关卡部分indexPass() 方法用到
    private function ToChinaseNum($num) {
        $char = array("零","一","二","三","四","五","六","七","八","九");
        $dw = array("","十","百","千","万","亿","兆");
        $retval = "";
        $proZero = false;
        for($i = 0;$i < strlen($num);$i++){
            if($i > 0)
                $temp = (int)(($num % pow (10,$i+1)) / pow (10,$i));
            else $temp = (int)($num % pow (10,1));
            if($proZero == true && $temp == 0) continue;
            if($temp == 0) $proZero = true;
            else
                $proZero = false;
            if($proZero){
                if($retval == "") continue;
                $retval = $char[$temp].$retval;
            }else
                $retval = $char[$temp].$dw[$i].$retval;
        }
        if($retval == "一十")
            $retval = "十";

        return $retval;
    }


    //游戏视频
    public function indexVideo($id){
        return $this->getDocumentDate($id,'a.video = 1',true,false,3);
    }


    //礼包、开服、最新入库游戏
    private function indexLeft($tid){
        //礼包
        $packList = M('package')->alias('a')->field('a.*')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('a.category_id = 1 AND b.tid = "'.$tid.'" AND b.type="package"')->order('a.id DESC')->limit(5)->select();
        $packIsNull = false;
        if(empty($packList)){
            $packIsNull = true;
            $packList = M('package')->where('category_id = 1')->order('create_time DESC')->limit(5)->select();
        }

        //开服
        $serList = M('package')->alias('a')->field('a.*,c.server,c.start_time')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->join('__PACKAGE_PARTICLE__ c ON a.id = c.id')->where('a.category_id = 3 AND b.tid = "'.$tid.'" AND b.type="package"')->order('a.create_time DESC')->limit(5)->select();
        $serIsNull = false;
        if(empty($serList)){
            $serIsNull = true;
            $serList = M('package')->alias('a')->field('a.*,c.server,c.start_time')->join('__PACKAGE_PARTICLE__ c ON a.id = c.id')->where('a.category_id = 3')->order('a.create_time DESC')->limit(5)->select();
        }

        foreach($serList as $k=>&$v){
            $tagid = get_tags($v['id'],'package','product');
            if(!empty($tagid)){
                $tagid = array_column($tagid,'id')[0];
                $gameDate = M('down')->alias('a')->field('a.id,a.title')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('b.type = "down" AND b.tid = \''.$tagid.'\'')->order('a.create_time DESC')->find();
                $batchDate = M('batch')->alias('a')->field('a.id,a.title,a.url_token')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('b.type = "batch" AND b.tid = \''.$tagid.'\'')->order('a.id DESC')->find();
            }
            $v['gameId'] = $gameDate['id']?$gameDate['id']:false;
            $v['game'] = $gameDate['title']?$gameDate['title']:false;
            $v['batchId'] = $batchDate['id']?$batchDate['id']:false;
            $v['batch'] = $batchDate['title']?$batchDate['title']:false;
            $v['batchUrl'] = $batchDate['url_token']?$batchDate['url_token']:false;
        }

        //最新入库游戏
        $newGame = M('batch')->field('id,title,icon,url_token')->where('pid = 0 AND interface = 0 AND enabled = 1')->order('id DESC')->limit(12)->select();
        foreach($newGame as $k=>&$v){
            $game = get_base_by_tag($v['id'],'batch','down','product');
            $v['gameLogo'] = $game['smallimg'];
        }

        $this->assign(array(
            'packList'=>$packList,
            'packIsNull'=>$packIsNull,
            'serList'=>$serList,
            'serIsNull'=>$serIsNull,
            'newGame'=>$newGame,
        ));

    }

/**********************************************************************************/

    //新手指南 (产品 + 新手)
    public function novice($id){
        //获取产品标签
        $tid = $this->getTagId($id);
        if(!$tid) return false;

        return M('documentArticle')->alias('a')->field('a.id,a.sub_title')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->join('__TAGS_MAP__ c ON a.id = c.did')->where('c.tid = 23119 AND b.tid = \''.$tid.'\'')->limit(20)->select();
    }

    //热点速递 (产品 order 赞)
    public function hotArticle(){
        $article = M('document')->field('a.id,b.sub_title')->alias('a')->join('__DOCUMENT_ARTICLE__ b ON a.id = b.id')->order('ding desc')->limit(5)->select();
        return $article;
    }

    //高手进阶 (产品 + 标签)
    public function master($id){
        //获取产品标签
        $tid = $this->getTagId($id);
        if(!$tid) return false;

        return M('documentArticle')->alias('a')->field('a.id,a.sub_title')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->join('__TAGS_MAP__ c ON a.id = c.did')->where('c.tid = 23120 AND b.tid = \''.$tid.'\'')->limit(20)->select();
    }

    //大家都在玩 (热门游戏 按月点击率)
    public function everyone(){
        $ids = M('downCategory')->field('id')->where('pid=1')->select();
        $ids = implode(',',array_column($ids,'id'));
        return M('down')->field('id,smallimg,title')->where('category_id IN ('.$ids.')')->order('hits_month desc')->limit(12)->select();
    }

    //热门标签
    public function hotTags($id){
        //获取产品标签
        $tid = $this->getTagId($id);
        if(!$tid) return false;

        //获取该产品标签的所有数据
        $did = M('productTagsMap')->field('did,type')->where('tid = \''.$tid.'\'')->select();

        $tags =array();

        foreach($did as $k=>$v){
            $tag = M('tags')->alias('a')->field('a.name,a.title')->join('__TAGS_MAP__ b ON a.id = b.tid')->where('b.did = '.$v['did'].' AND b.type = "'.$v['type'].'"')->select();

            if($tag){
                $tags = array_merge($tags,(array)$tag);
            }

        }

        return array_unique_fb($tags);
    }


    //获取产品标签
    private function getTagId($id ,$model = "batch"){
        return M('productTagsMap')->where('did = '.$id.' AND type = "'.$model.'"')->getField('tid');
    }

    //用于 资料、攻略、问答、资讯模块来获取文章表的数据
    private function getDocumentDate($id, $where = 1, $isIndex = true, $isAndTag = true, $row = 10,$order = "a.id DESC" , $isMobile = false){
        //获取产品标签
        $tid = $this->getTagId($id);
        if(!$tid) return false;

        //分页数据获取
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;

        //判断是否是首页获取
        $where = "c.tid = $tid AND c.type='document' AND ".$where;
        if(!$isIndex){
            if($isAndTag){
                $count = M('Document')->alias('a')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->join('__TAGS_MAP__ d ON a.id = d.did')->where($where)->count();
            }else{
                $count = M('Document')->alias('a')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where($where)->count();
            }
            if ($p > $count ) $p = $count; //容错处理
            $this->pages($count,$row,$id,$isMobile);
        }

        //列表数据
        if($isAndTag){
            $lists = M('Document')->alias('a')->field('a.id,a.title,a.description,a.cover_id,a.view,a.create_time,a.ding,a.smallimg,b.sub_title,b.author,b.source')->join('__DOCUMENT_ARTICLE__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->join('__TAGS_MAP__ d ON a.id = d.did')->where($where)->order($order)->page($p,$row)->select();
        }else{
            $lists = M('Document')->alias('a')->field('a.id,a.title,a.description,a.cover_id,a.view,a.create_time,a.ding,a.smallimg,b.sub_title,b.author,b.source')->join('__DOCUMENT_ARTICLE__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where($where)->order($order)->page($p,$row)->select();
        }

        //数据返回
        return $lists;
    }

    /**
     * 手机版文章列表用
     */
    //手机文章列表
    public function mobileArticle($id){
        return $this->getDocumentDate($id,1,false,false,10,"a.id DESC",true);
    }

    //独家礼包（最新的一条礼包数据）
    public function mobilePackage($id){
        //产品标签
        $tid = $this->getTagId($id);
        if($tid){
            return M('package')->alias('a')->field('a.*')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where('a.category_id IN(1,2,4) AND b.type = "package" AND b.tid = '.$tid)->order('a.id DESC')->find();
        }
    }

    /**
     * 描述： PC2专题首页
     * 作者：肖书成
     * 时间：2015/06/15
     */
    public function pc2($id){
        //基础数据 返回 产品标签
        $cid = $this->pc2_base($id);

        if($cid){
            $child  = M('Batch')->field('id,title,url_token')->where('pid = '.$id)->select();
            $system = C('STATIC_URL');
            $zq     = C('FEATURE_ZQ_DIR');
            foreach($child as $k=>$v){
                if(substr($v['title'],-6) == '攻略'){
                    $more['gl'] = $system . '/' . $zq . '/' . $v['url_token'];
                }elseif(substr($v['title'],-6) == '问答'){
                    $more['wd'] = $system . '/' . $zq . '/' . $v['url_token'];
                }
            }

            //幻灯片
            $slide      = $this->get_tags2_article($id,$cid['id'],false,'b.cover_id',false,'b.position & 16',false,5,false,'b.update_time DESC');

            //最新文章
            $zx = $this->get_tags2_article($id,$cid['id'],false,'b.description',false,1,false,1,false,'b.id DESC');

            //更新文章
            $gx = $this->get_tags2_article($id,$cid['id'],false,'b.update_time',false,1,false,6,false,'b.update_time DESC');

            //攻略
            $glimg      = $this->get_tags2_article($id,$cid['id'],false,'b.cover_id',false,'b.cover_id >0 AND b.category_id IN(89,92,95)',false,3,false,'b.id DESC');
            $glimg && $gl   = $this->get_tags2_article($id,$cid['id'],false,'b.update_time,c.source','__DOCUMENT_ARTICLE__ c ON a.did = c.id','a.did NOT IN('.implode(',',array_column($glimg,'id')).') AND b.category_id IN(89,92,95)',false,8,false,'b.id DESC');


            //问答
            $askimg     = $this->get_tags2_article($id,$cid['id'],23058,'b.cover_id',false,'b.cover_id >0',false,3,false,'b.id DESC');
            $askimg && $ask = $this->get_tags2_article($id,$cid['id'],23058,'b.update_time,c.source','__DOCUMENT_ARTICLE__ c ON a.did = c.id','a.did NOT IN('.implode(',',array_column($askimg,'id')).')',false,8,false,'b.id DESC');


            $this->assign(array(
                'more'      =>  $more,      //更多
                'slide'     =>  $slide,     //幻灯片
                'zx'        =>  $zx[0],     //最新文章（首页头条）
                'gx'        =>  $gx,        //头条下的文章，按跟新时间排序
                'glimg'     =>  $glimg,     //攻略区域的三条图片数据
                'gl'        =>  $gl,        //攻略 六条文章数据
                'askimg'    =>  $askimg,    //问答 三条图片数据
                'ask'       =>  $ask        //问答的 六条文章数据
            ));
        }
    }

    //攻略
    public function pc2_gl($id){
        //产品标签
        $cid = $this->pc2_base($id);
        if(!$cid) return false;
        //攻略列表数据
        $lists = $this->get_tags2_article($id,$cid['id'],false,'b.description,b.cover_id,b.update_time,b.ding,c.author,c.source','__DOCUMENT_ARTICLE__ c ON a.did = c.id','b.category_id IN(89,92,95)',true,10);
        $this->assign('lists',$lists);
    }

    //问答
    public function pc2_wd($id){
        //产品标签
        $cid = $this->pc2_base($id);
        if(!$cid) return false;
        //攻略列表数据
        $lists = $this->get_tags2_article($id,$cid['id'],23058,'b.description,b.cover_id,b.update_time,b.ding,c.author,c.source','__DOCUMENT_ARTICLE__ c ON a.did = c.id','b.category_id IN(89,92,95)',true,10);
        $this->assign('lists',$lists);
    }

    //通过产品标签（并且标签）调用文章数据（最多连四张表，支持专区分页）；
    private function get_tags2_article($id,$tid1,$tid2,$field = false,$join=false,$where = 1,$isPage = false,$row = false,$isMobile = false,$order='b.update_time DESC',$key = false ,$count = false){
        $model = M('ProductTagsMap')->alias('a');
        ($isPage && !$key)?$model->join('__DOCUMENT__ b ON a.did = b.id'):$model->field('b.id,b.title'.($field?','.$field:''))->join('__DOCUMENT__ b ON a.did = b.id');
        $join&&$model->join($join);

        if($tid2){
            $model->join('__TAGS_MAP__ d ON b.id = d.did')->where('a.tid ='.$tid1.' AND a.type="document" AND b.status = 1 AND '.$where.' AND d.tid ='.$tid2.' AND d.type="document"');
        }else{
            $model->where('a.tid ='.$tid1.' AND a.type="document" AND b.status = 1 AND '.$where);
        }
        $model->order($order);

        if($row){
            if($isPage){
                if($key){
                    //分页数据获取
                    $p = intval(I('p'));
                    if (!is_numeric($p) || $p<=0 ) $p = 1;
                    return $model->page($p,$row)->select();
                }else{
                    $count = $model->count();
                    $this->pages($count,$row,$id,$isMobile);
                    return $this->get_tags2_article($id,$tid1,$tid2,$field,$join,$where,$isPage,$row,$isMobile,$order,true ,$count);
                }
            }else{
                $model->limit($row);
            }
        }
        return $model->select();
    }

    public function pc2_base($id){
        //产品标签
        $cid    = get_tags($id,'batch','product')[0];
        if(!$cid) return false;

        //基础数据
        $base   = M('Batch')->field('id,pid,title,seo_title,keywords,description')->where('id = '.$id)->find();

        if($base['pid'] > 0){
            $pBase   = M('Batch')->field('id,title,seo_title,keywords,description,url_token')->where('id = '.$base['pid'])->find();
            $cid['url_token'] = C('STATIC_URL') . '/' . C('FEATURE_ZQ_DIR') . '/' . $pBase['url_token'];
        }

        //SEO
        $SEO['title'] = $base['seo_title'];
        $SEO['keywords'] = $base['keywords']?$base['keywords']:$pBase['keywords'];
        $SEO['description'] =   $base['description']?$base['description']:$pBase['description'];

        $this->assign(array(
            'SEO'       =>  $SEO,       //SEO
            'base'      =>  $base,      //基本信息
            'cid'       =>  $cid,       //产品标签
        ));

        return $cid;
    }

    public function pc2head($tid,$base){
        if(!$tid) return false;
        if((int)$base['pid']>0) $base = M('Batch')->field('id,title,seo_title,keywords,description')->where('id = '.$base['pid'])->find();
        $game       = M('ProductTagsMap')->alias('a')->field('b.id,b.description,smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')->where('a.tid = '.$tid['id'].' AND a.type = "down" AND b.status = 1')->find();
        $tags       = get_tags($game['id'],'down');
        $tid['description'] = $base['description']?$base['description']:$game['description'];

        $this->assign(array(
            'tid'   =>  $tid,
            'game'  =>  $game,
            'tags'  =>  $tags
        ));
        $this->display(T('Home@7230/Widget/pc2head'));
    }

    public function pc2right($tid){
        if(!$tid) return false;
        $sysDown = M('ProductTagsMap')->alias('a')->field('b.title,c.id,c.system')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->where('a.tid = '.$tid.' AND a.type = "down" AND b.status = 1')->group('c.system')->select();
        $xgk     = M('Special')->field('title,url_token,icon')->where(rand_where('Special',4,'interface = 0 AND id >403'))->limit(4)->select();

        $this->assign(array(
            'down'=>$sysDown,
            'xgk' =>$xgk,
        ));
        $this->display(T('Home@7230/Widget/pc2right'));
    }


}