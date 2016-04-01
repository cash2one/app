<?php
/**
 * 作者: 肖书成
 * 描述：96U专区Widget
 * 时间: 2015/11/14
 */

namespace Home\Widget;

use Home\Controller\BatchController;
class Jf96ubatchWidget extends BatchController{

    /**
     * 描述:空操作
     * @param string $method
     * @param array $args
     * @return mixed|void
     */
    public function __call($method, $args){
        return;
    }

    /**
     * 作者:肖书成
     * 描述:专区的基础数据
     * 时间:2015/11/17
     * @param int $id
     * @param int $isM (0代表PC版、1代表手机版)
     */
    public function base($id,$isM = 0){
        //获取标签
        $tag    =   M('TagsMap')->alias('a')->field('b.id,b.name,b.title')->join('__TAGS__ b ON a.tid = b.id')->where("a.type='batch' AND a.did = $id")->find();
        if(!empty($tag)){
            //专区首页基础数据
            $tid    =   $tag['id'];


            //基础信息
            $info   =   M('batch')->field('id,pid,title,seo_title,keywords,description,url_token,icon,themeimg,uid')->where("id = $id")->find();
            if((int)$info['pid'] > 0){
                $base   =   M('batch')->field('id,pid,title,seo_title,keywords,description,url_token,icon,themeimg')->where("id = ".$info['pid'])->find();
            }else{
                $base   =   $info;
            }

            //SEO
            $SEO['title']       =   $info['seo_title']?$info['seo_title']:$base['seo_title'];
            $SEO['keywords']    =   $info['keywords']?$info['keywords']:$base['keywords'];
            $SEO['description'] =   $info['description']?$info['description']:$base['description'];

            $title  =   $SEO['title'];
            //标题需要加前后缀
            if(C('SEO_STRING')){
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $SEO['title'] = implode('_', $t);
            }

        }

        $staticUrl  =   ($isM == 0 ? C('STATIC_URL'):C('MOBILE_STATIC_URL'));
        $zq_dir     =   C('FEATURE_ZQ_DIR');

        $zqUrl      =   $staticUrl .'/'.(empty($zq_dir)?'':$zq_dir.'/');

        $baseUrl    =   $zqUrl.(substr($base['url_token'],1) != '/'?$base['url_token']:substr($base['url_token'],1));
        !(substr($baseUrl,-1) == '/') && $baseUrl .= '/';


        //导航
        if($base){
            $nav    =   M('Batch')->field('id,title,url_token')->where('pid = '.$base['id'].' AND enabled = 1')->select();

            //导航验证
            $nav_index  =   true;
            foreach($nav as $k=>&$v){
                if($v['id'] == $id){
                    $v['active']    =   1;
                    $nav_index      =   false;
                }
                $v['url']   =   $zqUrl . (substr($v['url_token'],1) != '/'?$v['url_token']:substr($v['url_token'],1));
                $v['title'] =   substr($v['title'],-6);
                switch($v['title']){
                    case '攻略':$nav2['gl']   =   $v;break;
                    case '资讯':$nav2['zx']   =   $v;break;
                    case '评测':$nav2['pc']   =   $v;break;
                    case '问答':$nav2['wd']   =   $v;break;
                    case '视频':$nav2['sp']   =   $v;break;
                    default: break;
                }
            }unset($v);

            //验证是否有礼包，有则添加礼包
            if($tag){
                $package    =   M('TagsMap')->alias('a')->field('b.id,b.title')->join('__PACKAGE__ b ON a.did = b.id')->where('a.tid = '.$tag['id'].' AND a.type = "package" AND  b.status = 1')->find();

                if($package){
                    $nav[]  = array(
                        'title'     =>  '礼包',
                        'url'       =>  staticUrl('detail',$package['id'],'Package')
                    );
                }

                $down       =   M('TagsMap')->alias('a')->field('b.id,b.title')->join('__DOWN__ b ON a.did = b.id')->where('a.tid = '.$tag['id'].' AND a.type = "down" AND  b.status = 1')->order('b.id desc')->find();
                $nav[]      =   array(
                    'title'     =>  '下载',
                    'url'       =>  staticUrl('detail',$down['id'],'Down')
                );

                $nav[]      =   array(
                    'title'     =>  '游戏库',
                    'url'       =>  $staticUrl.'/yxksx/1/1/all/all/all/all/all'
                );
            }

            $this->assign(array(
                'nav_index' =>  $nav_index,
                'nav'       =>  $nav,
                'nav2'      =>  $nav2
            ));
        }

        //数据赋值
        $this->assign(array(
            'tag'   =>  $tag,
            'base'  =>  $base,
            'info'  =>  $info,
            'SEO'   =>  $SEO,
            'staticUrl' =>  $staticUrl,
            'zqUrl'     =>  $zqUrl,
            'baseUrl'   =>  $baseUrl
        ));

        $data['tag']    =   $tag;
        $data['base']   =   $base;

        return $data;
    }


    /**
     * 作者:肖书成
     * 描述:专题一的头部
     * 时间:2015/11/17
     * @param array $SEO
     * @param array $base
     * @param int   $id
     */
    public function zq1_header($SEO,$nav,$id,$nav_index){
        $this->assign(array(
            'nav_index' =>  $nav_index,
            'SEO'       =>  $SEO,
            'nav'       =>  $nav,
            'id'        =>  $id
        ));
        $this->display(T('Home@jf96u/Batch/widget/header'));
    }

    /**
     * 作者:肖书成
     * 描述:专题一的脚部
     * 时间:2015/11/17
     */
    public function zq1_footer(){
        $this->display(T('Home@jf96u/Batch/widget/footer'));
    }


    /**
     * 作者:肖书成
     * 描述:根据标签获取文章数据
     * 时间:2015/11/17
     * @param $id
     * @param string $tid
     * @param int $field
     * @param int $where
     * @param int $row
     * @param bool $isPage
     * @param string $order
     * @param bool $join
     * @param bool $product
     * @param bool $isMobile
     * @return mixed
     */
    public function getDocumentDate($id,$tid,$field,$where = 1,$row = 10, $isPage = false,$order = true,$product = false ,$join = false, $isMobile = false){
        //分页数据获取
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;

        //判断是否是首页获取，是则做分页处理
        $where = "a.tid = $tid AND a.type='document' AND b.status =1 AND ".$where;
        if($isPage){
            $model = M('TagsMap')->alias('a')->join('__DOCUMENT__ b ON a.did = b.id');
            $product && $model->join('__PRODUCT_TAGS_MAP__ d ON b.id = d.did');
            $count = $model->where($where)->count();
            if ($p > $count ) $p = $count; //容错处理
            $this->pages($count,$row,$id,$isMobile);
        }

        //列表数据
        $model = M('TagsMap')->alias('a')->field($field)->join('__DOCUMENT__ b ON a.did = b.id');
        $join && $model->join($join);
        $product &&$model->join('__PRODUCT_TAGS_MAP__ d ON a.did = d.did');

        //数据返回
        ($order === true)?$order = "b.update_time DESC":'';
        return $model->where($where)->order($order)->page($p,$row)->select();
    }


    public function batchTag($base){
        $pid    = M('ProductTags')->where('category = 1 AND title = "'.$base['title'].'"')->getField('id');
        if(empty($pid)) return false;
        $tags   = M('ProductTags')->field('id,name,title')->where('pid = '.$pid.' AND status = 1')->select();
        if(empty($tags)) return false;

        $data   = array();
        foreach($tags as $k=>&$v){
            $tit        =   substr($v['title'],-6);
            $v['title'] =   substr($v['title'],0,-6);
            switch($tit){
                case '介绍': $data['js'] = $v; break;
                case '玩法': $data['wf'] = $v; break;
                case '幻灯': $data['hd'] = $v; break;
                case '头条': $data['tt'] = $v; break;
                case '新闻': $data['xw'] = $v; break;
                case '资讯': $data['zx'] = $v; break;
                case '攻略': $data['gl'] = $v; break;
                case '视频': $data['sp'] = $v; break;
            }
        }

        return $data;
    }

    public function arr_ids($ids,$data){
        if(empty($data)) return $ids;
        $ids = array_filter(array_merge($ids,(array)array_column($data,'id')));
        return $ids;
    }

    public function str_ids($ids){
        if(empty($ids)) return '';
        return " AND b.id NOT IN(".implode(',',$ids).")";
    }

    /*******************************************PC专区六大模块（zq1）***************************************/
    public function zq1_index($id){
        $data   =   $this->base($id);

        //专区标签
        $bTags  =   $this->batchTag($data['base']);
        $ids    =   array();

        //获取文章分类
        $cate   =   M('Category')->where('pid = 1586 AND status = 1')->getField('id,title');


        if($data['tag']['id']){
            //幻灯片
            if($bTags['hd']['id']){
                $slide  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.cover_id,b.smallimg',"b.smallimg != 0 AND d.type = 'document' AND d.tid = ".$bTags['hd']['id'],5,false,true,true);
            }

            if(empty($slide)){
                $slide  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.cover_id,b.smallimg',"b.smallimg != 0",5);
            }
            $ids = (array)array_column($slide,'id');

            //头条
            if($bTags['tt']['id']){
                $head   =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description',"d.type = 'document' AND d.tid = ".$bTags['tt']['id'],1,false,true,true);

            }
            if(empty($head)){
                $head   =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description','1'.$this->str_ids($ids),1);
            }

            $ids    =   $this->arr_ids($ids,$head);
            $head   =   $head[0];


            //新闻
            $new_count  =   0;
            if($bTags['xw']['id']) {
                $news = $this->getDocumentDate($id, $data['tag']['id'], 'b.id,b.title,b.category_id,b.update_time', "d.type = 'document' AND d.tid = " . $bTags['xw']['id'], 9, false, true, true);
                $new_count = count($new_count);
            }
            $new_count  = 9 - (int)$new_count;
            if($new_count > 0){
                $news1  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.category_id,b.update_time','1'.$this->str_ids($ids),$new_count,false,'b.id DESC');
                $news   =   array_filter(array_merge((array)$news1,(array)$news));
            }else{
                $news  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.category_id,b.update_time','1'.$this->str_ids($ids),9,false,'b.id DESC');
            }

            $ids    =   $this->arr_ids($ids,$news);

            //资讯
            if($bTags['zx']['id']){
                $zxImg     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.smallimg != 0 AND d.type = 'document' AND d.tid = ".$bTags['zx']['id'],2,false,true,true);

                if(empty($zxImg)){
                    $zxImg     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.category_id = 1622 AND b.smallimg != 0".$this->str_ids($ids),2);
                }
            }else{

                $zxImg     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.category_id = 1622 AND b.smallimg != 0".$this->str_ids($ids),2);
            }
            $ids    =   $this->arr_ids($ids,$zxImg);

            //资讯列表
            $zx     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.update_time',"b.category_id = 1622".$this->str_ids($ids),5);


            //攻略
            if($bTags['gl']['id']){
                $glImg  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.smallimg != 0 AND d.type = 'document' AND d.tid = ".$bTags['gl']['id'],2,false,true,true);
                if(empty($glImg)){
                    $glImg  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.category_id = 1588 AND b.smallimg != 0",2);
                }
            }else{
                $glImg  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.category_id = 1588 AND b.smallimg != 0",2);
            }
            $ids    =   $this->arr_ids($ids,$glImg);


            //攻略列表
            $gl     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.update_time',"b.category_id = 1588".$this->str_ids($ids),5);


            //视频
            if($bTags['sp']['id']){
                $sp  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.smallimg != 0 AND d.type = 'document' AND d.tid = ".$bTags['sp']['id'],2,false,true,true);
            }
            if(empty($sp)){
                $sp  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.smallimg',"b.category_id = 1591 AND b.smallimg != 0",2);
            }


            //游戏截图
            $img    =   M('TagsMap')->alias('a')->join('__DOWN__ b ON a.did = b.id')
                ->where('a.tid = '.$data['tag']['id'].' AND a.type = "down" AND b.status = 1')->order('b.id DESC')->getField('previewimg');


            //游戏问答
            $wd     =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.update_time',"b.category_id = 1592".$this->str_ids($ids),10);
        }

        $this->assign(array(
            'cate'  =>  $cate,
            'slide' =>  $slide,
            'head'  =>  $head,
            'news'  =>  $news,
            'zxImg' =>  $zxImg,
            'zx'    =>  $zx,
            'glImg' =>  $glImg,
            'gl'    =>  $gl,
            'sp'    =>  $sp,
            'img'   =>  explode(',',$img),
            'wd'    =>  $wd
        ));
    }


    /**
     * 描述:专区1攻略列表
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_gl($id){
        $data   =   $this->base($id);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time,b.smallimg','(b.category_id = 1588 or b.category_id = 1590)',10,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }


    /**
     * 描述:专区1资讯列表
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_zx($id){
        $data   =   $this->base($id);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time,b.smallimg','b.category_id = 1622',10,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }


    /**
     * 描述：专区1评测
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_pc($id){
        $data   =   $this->base($id);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time','b.category_id = 1589',10,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }

    /**
     * 描述：专区1问答
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_wd($id){
        $data   =   $this->base($id);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time','(b.category_id = 1764 or b.category_id = 1592)',10,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }



    /**
     * 描述：专区1视频
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_sp($id){
        $data   =   $this->base($id);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time','b.category_id = 1591',10,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }


    /**
     * 描述:专区推荐
     * 时间:2015-12-8
     */
    public function zq1_top($base,$tag){
        if($tag){
            //专区标签
            $bTags  =   $this->batchTag($base);

            //介绍
            if($bTags['js']['id']){
                $top['js']  =   $this->getDocumentDate(false,$tag['id'],'b.id,b.title,c.sub_title',"d.type = 'document' AND d.tid = ".$bTags['js']['id'],9,false,true,true,'__DOCUMENT_ARTICLE__ c ON b.id = c.id');
            }

            //玩法
            if($bTags['wf']['id']){
                $top['wf']  =   $this->getDocumentDate(false,$tag['id'],'b.id,b.title,c.sub_title',"d.type = 'document' AND d.tid = ".$bTags['wf']['id'],9,false,true,true,'__DOCUMENT_ARTICLE__ c ON b.id = c.id');
            }

            if(!empty($top)){
                $this->assign(array(
                    'top'   =>  $top,
                    'bTags' =>  $bTags
                ));
                $this->display(T('Home@jf96u/Batch/widget/zq1_top'));
            }
        }


    }


    /**
     * 描述:专区右边部分（除首页外调用）
     * @param $tag
     */
    public function zq1_right($tag,$more){

        //排行版
        $lists  =   M('Down')->field('id,title,view')->where('status = 1 AND pid = 0')->order('view DESC')->limit(10)->select();
//        dump($lists);die;

//        $lists  =   M('TagsMap')->alias('a')->field('a.tid,b.id,b.title,b.view')->join('__DOWN__ b ON a.did = b.id')
//                    ->where('b.status = 1 AND b.pid = 0')->order('b.view DESC')->group('a.tid')->limit(10)->select();
//        dump($lists);die;


        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $tag,
            'more'  =>  $more
        ));
        $this->display(T('Home@jf96u/Batch/widget/zq1_right'));
    }


    /**
     * 描述:专区右边 “游戏信息”
     * 时间:2015-12-07
     * @param $tag
     */
    public function zq1_right1($tag,$is_back = false){
        if(empty($tag)) return;


        //查找标签数据
        $data = M('TagsMap')->alias('a')->field('b.id,b.title,b.smallimg,b.category_id,b.description,b.update_time,b.model_id')->join('__DOWN__ b ON a.did = b.id')
            ->where("a.tid = ".$tag['id']." AND a.type = 'down' AND b.status = 1 ")->order('b.id DESC')->select();
        if(empty($data)){return false;}

        //根据标签数据查找相应附属模型
        $model_id   =   implode(',',array_unique(array_column($data,'model_id')));
        $model      =   M('Model')->where("id IN($model_id) AND name != 'paihang'")->getField('id,name');
        $update_time=   $data[0]['update_time'];
        $data_id    =   array();
        $arr        =   array();
        foreach($data as $k=>$v){
            foreach($model as $k1=>$v1){
                if($k1 == $v['model_id']){
                    $data_id[$v1][] =   $v['id'];
                    $arr[$v1][] =   $v;
                    continue;
                }
            }
        }

        //根据标签数据查找相应附属模型数据
        $arr_data   =   array();
        foreach($data_id as $k=>$v){
            $arr_data[$k]  =   M('Down'.ucfirst($k))->where('id IN('.implode(',',$v).')')->getField('id,version,system,size,language');
        }

        //合并附属模型的数据
        $data   =   array();
        $rel['id']  =   0;
        foreach($arr as $k=>$v){
            foreach($v as $k1=>$v1){
                if($arr_data[$k][$v1['id']]){
                    $data[] =   array_merge($v1,$arr_data[$k][$v1['id']]);
                }
            }
        }

        //游戏语言
        $language  =   C('FIELD_DOWN_LANGUAGE');

        if($data){
            $rel            =   $data[0];
            $rel['cate']    =   M('DownCategory')->field('title')->where('id = '.$data[0]['category_id'])->getField('title');
            $rel['title']   =   $tag['title'];
            $rel['update_time'] =   $update_time;
            $rel['language']=   $language[$rel['language']];
            $system         =   C('FIELD_DOWN_SYSTEM');
            $language       =   C('FIELD_DOWN_LANGUAGE');
            $rel['system']  =   $system[$rel['system']];
            foreach($data as $k=>$v){
                foreach($system as $k1=>$v1){
                    if($v['system'] == $k1){
                        $val['id']      =   $v['id'];
                        $val['title']   =   $v['title'];
                        $val['sys']     =   $v1;
                        $val['sys_id']  =   $k1;
                        $rel['sys'][]   =   $val;
                        break;
                    }
                }
            }

            $rel['sys']  =   list_sort_by($rel['sys'],'sys_id','asc');

            //获取产品标签
            $rel['tags'] = M('ProductTagsMap')->alias('a')->field('b.title')->join('__PRODUCT_TAGS__ b ON a.tid = b.id')->where('a.type = "down" AND a.did = '.$rel['id'])->select();
            $this->assign(array(
                'language'  =>  $language,
                'down'      =>  $rel,
            ));
//dump($rel);die;
            if($is_back){
                return;
            }
        }

        $this->display(T('Home@jf96u/Batch/widget/zq1_right1'));
    }


    /**
     * 描述:专区1右边 “热门礼包”
     * 时间:2015-12-7
     * @param $tag
     */
    public function zq1_package($tag){

        if(!empty($tag)){
            $package    =   M('TagsMap')->alias('a')->field('b.id,b.title')->join('__PACKAGE__ b ON a.did = b.id')->where('b.status = 1 AND a.type = "package" AND a.tid = '.$tag['id'])->limit(10)->select();
            if(empty($package)) return;
        }else{
            return;
        }
        $this->assign('package',$package);
        $this->display(T('Home@jf96u/Batch/widget/zq1_package'));
    }


    /**
     * 描述:专区1右边 “热门攻略”
     * 时间:2015-12-7
     * @param $tag
     */
    public function zq1_hot_gl($tag,$more){
        if(empty($tag)) return;
        $gl =   $this->getDocumentDate(false,$tag['id'],'b.id,b.title','(b.category_id = 1588 or b.category_id = 1590)',10,false,'b.view DESC');
        if(empty($gl)) return;
        $this->assign(array(
            'more'=>$more,
            'gl'=>$gl
        ));
        $this->display(T('Home@jf96u/Batch/widget/zq1_hot_gl'));
    }

    /**
     * 描述:自己写的脚本，有关于乱斗西游的，使用完后 会删除
     * 时间:2015-12-7
     */
    public function jb(){
        $title  =   I('title');
        $list   =   M('Document')->field('id,title')->where('`status` = 1 AND title like "%'.$title.'%"')->select();

        $tag    =   M('Tags')->field('id,title')->where('`status` = 1 AND title = "'.$title.'"')->find();
        if(empty($tag)){
            echo '未找到标签"'.$title.'"';exit;
        }


        $error  =   0;
        $success=   0;
        $info   =   '';
        $count  =   count($list);

        foreach($list as $k => $v){
            $isHave = M('TagsMap')->where('did = '.$v['id'].' AND tid = '.$tag['id'].' AND type = "document"')->count('id');
            if((int)$isHave < 1){
                $data['did']    =   $v['id'];
                $data['tid']    =   $tag['id'];
                $data['type']   =   'document';
                $data['update_time']    =   $data['create_time']    =   time();
                $datas[]    =   $data;
                $success++;
            }else{
                $info   .=  'ID：'.$v['id']."\t\t标题：".$v['title']."<br/>";
                $error++;
            }

        }

        M('TagsMap')->addAll($datas);

        header("Content-type:text/html;charset=utf-8");
        echo "一共 $count 条数据,其中成功插入 $success 条数据, 失败 $error 条数据 或者 已经存在".PHP_EOL;
        if(!empty($info)){
            echo "<h3>插入失败的数据如下：</h3>";
            echo $info;
        }
    }


    /**
     * 描述:专区打标签
     * 时间:2015-12-8
     */
    public function zqbq(){

        $tag    =   M('Tags')->field('id,title')->where('`status` = 1 AND title = "乱斗西游"')->find();

        $list  =   M('Batch')->field('id,title')->where('id = 1 or pid = 1')->select();

        $error  =   0;
        $success=   0;
        $info   =   '';
        $count  =   count($list);

        foreach($list as $k => $v){
            $isHave = M('TagsMap')->where('did = '.$v['id'].' AND tid = '.$tag['id'].' AND type = "batch"')->count('id');
//            $tagmap = M('TagsMap')->where('did = '.$v['id'].' AND tid = '.$tag['id'].' AND type = "batch"')->select();

            if((int)$isHave < 1){
                $data['did']    =   $v['id'];
                $data['tid']    =   $tag['id'];
                $data['type']   =   'batch';
                $data['update_time']    =   $data['create_time']    =   time();
                $datas[]    =   $data;
                $success++;
            }else{
                $info   .=  'ID：'.$v['id']."\t\t标题：".$v['title']."<br/>";
                $error++;
            }
        }



        M('TagsMap')->addAll($datas);

        header("Content-type:text/html;charset=utf-8");
        echo "一共 $count 条数据,其中成功插入 $success 条数据, 失败 $error 条数据 或者 已经存在".PHP_EOL;
        if(!empty($info)){
            echo "<h3>插入失败的数据如下：</h3>";
            echo $info;
        }


    }




    /**
     * 描述:专区手机版的基础数据
     * @param $id
     */
    public function batch_zq1_base($id){
        //基础数据
        $data = $this->base($id,1);
        if($data['tag']){
            //下载数据
            $this->zq1_right1($data['tag'],true);

            //游戏截图
            $img    =   M('TagsMap')->alias('a')->join('__DOWN__ b ON a.did = b.id')
                ->where('a.tid = '.$data['tag']['id'].' AND a.type = "down" AND b.status = 1')->order('b.id DESC')->getField('previewimg');
            $img    =   explode(',',$img);

            //礼包
            $package    =   M('TagsMap')->alias('a')->field('b.id,b.title')->join('__PACKAGE__ b ON a.did = b.id')->where('a. tid = '.$data['tag']['id'].' AND a.type = "package"')->select();

            //攻略(最新4条)
            $gl         =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.update_time','(b.category_id = 1590 or b.category_id = 1588)',5,false,'b.id DESC');

            //资讯(最新4条)
            $zx         =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.update_time','b.category_id = 1622',5,false,'b.id DESC');

            $this->assign(array(
                'img'       =>  $img,
                'package'   =>  $package,
                'gl'        =>  $gl,
                'zx'        =>  $zx
            ));
        }
    }

    /**
     * 描述:专区1攻略列表(手机版)
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_gl_mobile($id){
        $data   =   $this->base($id,1);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time,b.smallimg','(b.category_id = 1588 or b.category_id = 1590)',10,true,true,false,false,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }

    /**
     * 描述:专区1资讯列表(手机版)
     * 时间:2015-12-7
     * @param $id
     */
    public function zq1_zx_mobile($id){
        $data   =   $this->base($id,1);

        if($data['tag']['id']){
            $lists  =   $this->getDocumentDate($id,$data['tag']['id'],'b.id,b.title,b.description,b.update_time,b.smallimg','b.category_id = 1622',10,true,true,false,false,true);
        }

        $this->assign(array(
            'lists' =>  $lists,
            'tag'   =>  $data['tag']
        ));
    }


}