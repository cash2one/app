<?php

namespace Home\Widget;
use Think\Controller;

class AfsbatchWidget extends Controller{

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 专区的基础数据
     * @param $id
     */
    private function base($id,$isMobile = false){
        //基础数据
        $batch = $this->getBatchData('id = '.$id,true,'id,title,pid,seo_title,keywords,description,url_token');

        //专区导航首页
        $home = $this->getBatchData('id = '.(($batch['pid']>0?$batch['pid']:$id)),true,'id,title,seo_title,keywords,description,url_token,icon,abet,themeimg,hint');

        //导航条
        $nav = $this->getBatchData('pid = '.($batch['pid']>0?$batch['pid']:$id),false,'title,url_token',10,'id ASC');

        //获取产品标签ID
        $tid = $this->getProductTag($id);

        //SEO
        $SEO = array();

        //判断是否是手机版
        if($isMobile){
            $staticUrl = C('MOBILE_STATIC_URL');
        }else{
            $staticUrl = C('STATIC_URL');
        }

        if($batch['id']!=$home['id']){
            $sValue = array(
                '资讯'=>array(
                    'title'=>$home['title'].'资讯_'.$home['title'].'活动 - 安粉丝手游网',
                    'keywords'=>$home['title'].'资讯,'.$home['title'].'活动'
                ),
                '下载'=>array(
                    'title'=>$home['title'].'下载_'.$home['title'].'手机版下载 - 安粉丝手游网',
                    'keywords'=>$home['title'].'下载,'.$home['title'].'手机版下载'
                ),
                '攻略'=>array(
                    'title'=>$home['title'].'攻略_'.$home['title'].'玩法 - 安粉丝手游网',
                    'keywords'=>$home['title'].'攻略,'.$home['title'].'玩法'
                ),
                '问答'=>array(
                    'title'=>$home['title'].'问答_'.$home['title'].'常见问题答疑 - 安粉丝手游网',
                    'keywords'=>$home['title'].'问答,'.$home['title'].'常见问题答疑'
                ));

            $sTitle = substr($batch['title'],-6);

            foreach($sValue as $k=>$v){
                if($sTitle == $k){
                    $SEO['title'] = $batch['seo_title']?$batch['seo_title']:$v['title'];
                    $SEO['keywords'] = $batch['keywords']?$batch['keywords']:$v['keywords'];
                }
            }
        }else{
            $SEO['title'] = $batch['seo_title'];
            $SEO['keywords'] = $batch['keywords'];
        }

        if(empty($SEO)){
            $SEO['title'] = $batch['seo_title'];
            $SEO['keywords'] = $batch['keywords'];
        }
        $SEO['description'] = $batch['description']?$batch['description']:$home['description'];

        //游戏数据
        if($tid){
            $down = M('productTagsMap')->alias('a')->field('b.*,c.*,e.title category')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON a.did = c.id')->join('__DOWN_CATEGORY__ e ON b.category_id = e.id')->where("a.tid = $tid AND a.type = 'down' AND b.status = 1 AND c.data_type = 1")->order('b.id DESC')->select();
        }

        if($down){
            $game = $down[0];
            $game['hits'] = 0;
            foreach($down as $k=>$v){
                if($v['system']=='1'){
                    $downAll = M('downAddress')->alias('a')->field('a.url,a.hits,a.site_id,b.site_name title')->join('__PRESET_SITE__ b ON a.site_id = b.id','left')->where('a.did = '.$v['id'])->order('a.id ASC')->select();
                    if($downAll) {
                        foreach ($downAll as $k1 => $v1) {
                            if ($v1['url']) {
                                $v1['url'] = formatAddress($v1['url'], $v1['site_id']);
                                $v1['code'] = builtQrcode($v1['url']);
                                $game['adrId'] = $v['id'];
                                $game['androidDownAll'][] = $v1;
                                $game['hits'] += $v1['hits'];
                            }
                        }
                    }
                }

                if($v['system']=='2'){
                    $downAll = M('downAddress')->alias('a')->field('a.url,a.hits,a.site_id,b.site_name title')->join('__PRESET_SITE__ b ON a.site_id = b.id','left')->where('did = '.$v['id'])->order('a.id ASC')->select();
                    if($downAll) {
                        foreach ($downAll as $k1 => $v1) {
                            if ($v1['url']) {
                                $v1['url'] = formatAddress($v1['url'], $v1['site_id']);
                                $v1['code'] = builtQrcode($v1['url']);
                                $game['iosId'] = $v['id'];
                                $game['IOSDownAll'][] = $v1;
                                $game['hits'] += $v1['hits'];
                            }
                        }
                    }
                }
            }
        }

        $game['androidUrl']  = $game['androidDownAll'][0]['url'];
        $game['androidCode'] = $game['androidDownAll'][0]['code'];
        $game['IOSUrl']      = $game['IOSDownAll'][0]['url'];
        $game['IOSCode']     = $game['IOSDownAll'][0]['code'];

        //游戏礼包
        $package = $this->getPackageData($id,'a.id,a.title,b.conditions,b.content,b.card_type','a.category_id = 1',true);
        foreach($package as $k=>&$v){
            $v['content'] = clean_str(strip_tags($v['content']));
            $v['android'] = $v['conditions']&1;
            $v['IOS']     = $v['conditions']&2;
        }unset($v);

        //热点推荐
        $batchHot = $this->getDocumentDate($id,'a.id,a.title,b.sub_title','a.position & 256',12,true,true);
        
        //小编答疑
        $ask = $this->getDocumentDate($id,'a.id,a.title','a.category_id = 191',5);

        //处理导航链接地址
        $zq = C('FEATURE_ZQ_DIR');
        $more = array();//处理更多
        foreach($nav as $k=>&$v){
            $v['url_token'] = $staticUrl . ($zq?'/'.$zq.'/':'/').$v['url_token'];
            if(substr($v['title'],-6)=='问答'){$more['ask'] = $v['url_token'];}
            elseif(substr($v['title'],-6)=='资讯'){$more['info'] = $v['url_token'];}
            elseif(substr($v['title'],-6)=='攻略'){$more['guide'] = $v['url_token'];}
            elseif(substr($v['title'],-6)=='新闻'){$more['news'] = $v['url_token'];}
            elseif(substr($v['title'],-6)=='开服'){$more['server'] = $v['url_token'];}
        }
        unset($v);
        $home['url_token'] = $staticUrl . ($zq?'/'.$zq.'/':'/') . $home['url_token'];

        //插入单个礼包数据
        if($package){
            $title = $nav[0]['title'];
            $arr = array_splice($nav,2);
            $nav[] = array('title'=>substr($title,0,-6).'礼包','url_token'=>C('PACKAGE_SLD').'/oneli/'.$game['id'].'.html');
            $nav = array_merge($nav,$arr);
        }

        //热门专区
        $hotBatch = $this->getBatchData('pid = 0 AND enabled = 1 AND interface = '.($isMobile?1:0),false,'id,title,url_token,icon',8);
        foreach($hotBatch as $k=>&$v){
            $v['url_token'] = $staticUrl . ($zq?'/'.$zq.'/':'/').$v['url_token'];
        }
        unset($v);

        //友情链接
        $this->link($id,$isMobile);

        //处理基础数据的URL
        $batch['url_token'] = $staticUrl . ($zq?'/'.$zq.'/':'/').$batch['url_token'];

        $this->assign(array(
            'batch'=>$batch,        //基础数据
            'home'=>$home,          //首条数据
            'nav'=>$nav,            //导航条
            'game'=>$game,          //游戏数据
            'down'=>$down,          //下载数据
            'package'=>$package,    //礼包
            'batchHot'=>$batchHot,  //专区热门推荐
            'ask'=>$ask,            //公共问答
            'SEO'=>$SEO,            //SEO
            'hotBatch'=>$hotBatch,  //热门专区
            'more'=>$more,          //更多
        ));
    }

    /**
     *  外部公共使用base接口
     *  @param integer $id 专区id
     *  @return null
     */
    public function P_base($id){
        $this->base($id);
    }

    /***************************************************首页数据**********************************************
     * @Author 肖书成
     * @createTime 2015/3/12
     * @Comments 专区的所有首页数据
     **/
    //首页新闻
    public function indexNews($id){
        //基础数据
        $this->base($id);

        //专区布局
        $this->layout($id);

        //专区新闻头条
        $headline = $this->getDocumentDate($id,'a.id,a.title,a.description','a.position & 1024',1);

        //专区新闻
        $news = (array)$this->getDocumentDate($id,'a.id,a.title,a.create_time','a.category_id = 182',8);
        if(count($news)<8){
            $news = array_filter(array_merge($news,(array)$this->getDocumentDate($id,'a.id,a.title,a.description,a.create_time','a.category_id = 181',10)));
        }

        //新闻头条赋值
        $this->assign('headline',$headline[0]);

        return $news;
    }

    //专区幻灯
    public function indexRecom($id){
        return $this->getDocumentDate($id,'a.id,a.title,a.cover_id','a.position & 512',6);
    }

    /************游戏图鉴部分****************/
    private function handbook($id){
        //查找这个游戏名字
        $batchTitle = $this->getBatchData('id = '.$id,true,'title')['title'];

        //根据游戏的名字来查找这个游戏的布局标签
        $layoutId = M('tags')->where("title = '$batchTitle' AND category = 3")->getField('id');

        if(empty($layoutId)) return false;

        //获取游戏图鉴的ID
        $tjId = $this->getTagData($layoutId,'title = "图鉴"')[0]['id'];

        if(!empty($tjId)){
            $list = $this->getTagData($tjId);
            foreach($list as $k=>&$v){
                $v['child'] = $this->getTagData($v['id']);
            }
            unset($v);

            //查找文章数据
            foreach($list as $k=>&$v){
                if($v['child']){
                    foreach($v['child'] as $k1=>$v1){
                        $v['child'][$k1]['data'] = $this->getDocumentDate($id,'a.id,a.cover_id,a.smallimg,b.sub_title','d.tid = '.$v1['id'].' AND d.type = "document"',80000,true,true,true);
                    }
                }else{
                    $v['data'] = $this->getDocumentDate($id,'a.id,a.cover_id,a.smallimg,b.sub_title','d.tid = '.$v['id'].' AND d.type = "document"',80000,true,true,true);
                }
            }
            unset($v);
        }

        $this->assign('handBook',$list);

    }

    //首页布局
    private function layout($id){
        //查找这个游戏名字
        $batchTitle = $this->getBatchData('id = '.$id,true,'title')['title'];

        //根据游戏的名字来查找这个游戏的布局标签
        $layoutId = M('tags')->where("title = '$batchTitle' AND category = 3")->getField('id');

        if(empty($layoutId)) return false;

        /************游戏图鉴部分****************/

        $this->handbook($id);

        /*************游戏文章部分****************/
        //获取游戏文章的ID
        $artiId = $this->getTagData($layoutId,"title = '文章'")[0]['id'];

        if(!empty($artiId)){
            //获取所有文章的分类
            $artlist = $this->getTagData($artiId);
            $para = array(1,2);
            foreach($artlist as $k=>$v){
                $v['title'] = str_replace(' ','',$v['title']);
                $category = explode('|',$v['title']);
                $artlist[$k]['style'] = array_pop($category);
                if(!in_array($artlist[$k]['style'],$para)){
                    $artlist[$k]['style'] = 1;
                }
                $artlist[$k]['title'] = implode(',',$category);
                $artlist[$k]['data'] = $this->getDocumentDate($id,'a.id,a.title,a.create_time','d.tid = '.$v['id'],30,true,false,true);
            }

        }
        /***************游戏特色部分******************/
        //获取游戏特色的ID
        $featureId = $this->getTagData($layoutId,"title = '特色'")[0]['id'];

        if(!empty($featureId)){
            //获取所有特色的分类
            $featureList = $this->getTagData($featureId);

            foreach($featureList as $k=>&$v){
                $v['data'] = $this->getDocumentDate($id,'a.id,a.title,a.create_time,b.sub_title','d.tid = '.$v['id'],30,true,true,true);
                if(!$v['data']){
                    unset($featureList[$k]);
                }

            }
            unset($v);
        }

        //数据赋值
        $this->assign(array(
            'feaList'=>$featureList,//游戏特色
            'artList'=>$artlist,//游戏文章
        ));
    }

    private function getTagData($pid ,$where = 1){
        return M('tags')->field('id,title')->where("pid = $pid AND category = 3 AND ".$where)->order('id ASC')->select();
    }

    /************************************专区频道数据（资讯、下载、礼包、攻略、问答）*****************************
     * @Author 肖书成
     * @createTime 2015/3/12
     * @Comments 专区频道数据（资讯、下载、礼包、攻略、问答）
     * @Para int $id
     * @Return array
     **/

    //资讯频道
    public function information($id){
        $this->base($id);
        return $this->getDocumentDate($id,'a.id,a.title,a.description,a.cover_id,a.create_time,b.author','a.category_id IN(182,192)',10,false,true);
    }

    //下载频道
    public function download($id){
        $this->base($id);
    }

    //攻略频道
    public function guide($id){
        $this->base($id);
        return $this->getDocumentDate($id,'a.id,a.title,a.description,a.cover_id,a.create_time,b.author','a.category_id = 181',10,false,true);
    }

    //问答频道
    public function question($id){
        $this->base($id);
        return $this->getDocumentDate($id,'a.id,a.title,a.description,a.cover_id,a.create_time','a.category_id = 191',10,false,false,false,true);
    }

    /***************************************************手机版专区部分**********************************************/
    //手机首页部分
    public function mobileIndex($id){
        //基础数据
        $this->base($id,true);

        //获取产品标签
        $tid = $this->getProductTag($id);
        $product = get_tags($id,'batch','product')[0];

        //新闻
        $news = (array)$this->getDocumentDate($id,'a.id,a.title','a.category_id = 182',4);
        if(count($news)<4){
            $news = array_filter(array_merge($news,(array)$this->getDocumentDate($id,'a.id,a.title,a.description,a.create_time','a.category_id = 181',4)));
        }

        //开测开服
        $server = $this->getPackageData($id,'a.id,c.server,c.start_time','a.category_id IN(5,4)',false,true,2);

        //攻略
        $guide = $this->getDocumentDate($id,'a.id,a.title,a.description','a.category_id = 181',4);

        //标签
        $tags = $this->articleTags($id);

        //图鉴
        $this->handbook($id);

        //游戏推荐
        if($tags){
            $recomGame = M('tagsMap')->alias('a')->field('b.id,b.title,b.smallimg')->join('__DOWN__ b ON a.did = b.id')->join('__PRODUCT_TAGS_MAP__ c ON b.id = c.did')->where('a.tid IN ('.implode(',',array_column($tags,'id')).') AND c.tid != '.$tid)->limit(4)->order('b.id DESC')->select();
        }else{
            $recomGame = M('down')->field('id,title,smallimg')->order('id DESC')->limit(4)->select();
        }

        $this->assign(array(
            'news'      =>$news,     //新闻
            'server'    =>$server,   //开测开服
            'guide'     =>$guide,    //攻略
            'tags'      =>$tags,     //标签
            'product'   =>$product,  //产品标签英文名
            'recomGame' =>$recomGame //推荐游戏
        ));
    }

    private function link($id,$isMobile){
        //查找专区基础数据
        $home = $this->getBatchData('id ='.$id,true,'title');

        //根据游戏的名字来查找这个游戏的布局标签
        $layoutId = M('tags')->where("title = '".$home['title']."' AND category = 3")->getField('id');
        if(empty($layoutId)) return false;

        //获友情链接的ID
        $linkId = $this->getTagData($layoutId,'title like "%友情链接%"')[0]['title'];

        if($linkId){
            $linkId = '"'.str_replace('|','","',$linkId).'"';
            $isMobile?$where = ' AND `group` != 5':$where = '';
            $link = M('link')->field('title,url_token')->where('status = 1 AND id IN('.$linkId.')'.$where)->select();
        }
        $this->assign('link',$link);
    }

    //手机开测开服部分
    public function mobileServer($id){
        //基础部分
        $this->base($id,true);

        //开测开服
        return $this->getPackageData($id,'a.title,c.server,c.start_time','a.category_id IN(4,5)',false,true,20,true,true);
    }

    //手机攻略部分
    public function guideMobile($id){
        //基础部分
        $this->base($id,true);

        //标签
        $this->assign('tags',$this->articleTags($id));

        //攻略
        return  $this->getDocumentDate($id,'a.id,a.title,a.create_time','a.category_id = 181',20,false,false,false,false,'a.id DESC',true);
    }

    //手机新闻部分
    public function newsMobile($id){
        //基础部分
        $this->base($id,true);

        //新闻
        return $news = (array)$this->getDocumentDate($id,'a.id,a.title,a.create_time','a.category_id = 182',20,false,false,false,false,'a.id DESC',true);
    }

    //手机问答部分
    public function askMobile($id){
        //基础部分
        $this->base($id,true);

        //问答
        return $this->getDocumentDate($id,'a.id,a.title,a.create_time','a.category_id = 191',20,false,false,false,false,'a.id DESC',true);
    }

    private function  articleTags($id){
        //获取产品标签
        $tid = get_tags($id,'batch','product');
        if(empty($tid)) return false;

        //获取标签
        $documentIds = M('Document')->alias('a')->field('distinct b.did')->join('__PRODUCT_TAGS_MAP__ b ON a.id = b.did')->where("b.tid = ". $tid[0]['id'] ." AND b.type='document'")->select();
        if($documentIds){
            $list = M('tagsMap')->alias('a')->field('distinct b.id,b.name,b.title')->join('__TAGS__ b ON a.tid = b.id')->where('a.did IN ('.implode(',',array_column($documentIds,'did')).') AND a.type="document" AND b.category = 1')->limit(8)->select();
            foreach($list as $k=>&$v){
                $v['url'] = C('MOBILE_STATIC_URL') . '/zqguide/' . $tid[0]['name'] . '/' . $id .'_'.$v['id'].'_1.html';
            }unset($v);
        }
        return $list;
    }

    /***************************************************频道数据结束，以下是方法********************************/

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 获取产品标签
     * @param $id
     * @return array
     */
    private function getProductTag($id){
        return get_tags($id,'batch','product')[0]['id'];
    }

    /**
     * *@Author 肖书成
     * @createTime 2015/3/11
     * @Comments 下载地址容错处理
     * @param $url
     * @param $sit_id
     * @return string
     */
    private function formatAddress($url,$sit_id){
        if(substr($url,0,8) != "https://" && substr($url,0,7) != "http://"){

            $down = M('presetSite')->where('id ='.$sit_id)->find()['site_url'];
            if(substr($url,0,1)=='/'){
                $url=substr($url,1);
            }
            if(substr($down,0,7)!="http://" && substr($down,0,8)!="https://"){
                $down = "http://".$down;
            }
            if(substr($down,-1,1)!='/'){
                $down = $down.'/';
            }
            $url = $down . $url;
        }
        return $url;
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用来获取文章表的数据
     * @param $id
     * @param int $where
     * @param bool $isIndex
     * @param bool $isAndTag
     * @param int $row
     * @param string $order
     * @param bool $isMobile
     * @return mixed
     **/
    private function getDocumentDate($id,$field='a.id,a.title,a.description,a.cover_id,a.view,a.create_time,a.ding,a.smallimg', $where = 1, $row = 10, $isIndex = true, $isArticle = false,$isAndTag = false, $isWenda = false,$order = "a.id DESC" , $isMobile = false){
        //获取产品标签
        $tid = $this->getProductTag($id);
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
        $model = M('Document')->alias('a')->field($field);

        if($isArticle){
            $model->join('__DOCUMENT_ARTICLE__ b ON a.id = b.id');
        }

        if($isWenda){
            $model->join('__DOCUMENT_WENDA__ e ON a.id = e.id');
        }

        $model->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did');

        if($isAndTag){
            $model->join('__TAGS_MAP__ d ON a.id = d.did');
        }

        //数据返回
        return $model->where($where)->order($order)->page($p,$row)->select();
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用获取专区数据的方法
     * @param int $where
     * @param bool $isFind
     * @param string $field
     * @return mixed
     */
    private function getBatchData($where= 1, $isFind = false, $field = "*", $limit = 10,$order = 'id DESC'){
        $model = M('Batch')->field($field)->where($where)->order($order);
        if($isFind){
            return $model->find();
        }else{
            return $model->limit($limit)->select();
        }
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
    private function getPackageData($id,$field = false,$where = 1,$isPmain=true,$isParticle = false,$row = 3,$isPage = false,$isMobile = false){
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
        //M('Document')->alias('a')->field('a.id,a.title,a.description,a.cover_id,a.view,a.create_time,a.ding,a.smallimg,b.sub_title,b.author,b.source')->join('__DOCUMENT_ARTICLE__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where($where)->order($order)->page($p,$row)->select();

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
     * @Author 周良才、肖书成
     * @createTime 2015/3/11
     * @Comments 用来分页的方法（做过特殊处理）
     * @param $count
     * @param $row
     * @param $id
     * @param $isMobile
     */
    private function pages($count,$row,$id,$isMobile){
        $rows=M('Batch')->field('url_token')->find($id);
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
        S('Batch'.'PageNumber',array($id=>ceil($count/$row)),86400);
    }
}