<?php
/**
 * 下载模块widget
 * 7230主题
 **/

namespace Down\Widget;

use Down\Controller\BaseController;

class P7230Widget extends BaseController{
    /**
     * 推荐精品手游列表页
     *@return void
     */
    public function jpsyLists(){
        //根据传入static_page表ID查找数据
        $page_id = I('page_id');
        $page_info = get_staticpage($page_id);

        //分页获取数据
        $row = 20;
        $where = array(
            'map' => array('_string' => 'position & 1')
        );
        $count  = D('Down')->listsWhereCount($where);// 查询满足要求的总记录数
        //是否返回总页数
        if(I('gettotal')){
            echo ceil($count/$row);
            exit();
        }

        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理

        $lists = D('Down')->page($p, $row)->listsWhere($where, true);
        $this->assign('lists',$lists);// 赋值数据集

        $Page = new \Think\Page($count, $row, '', false, $page_info['path']. getStaticExt());// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出
        $this->assign('page',$show);// 赋值分页输出

        //SEO
        $seo['title'] = $page_info['title'];
        $seo['keywords'] =  $page_info['keywords'];
        $seo['description'] = $page_info['description'];
        $this->assign('SEO',$seo);
        //模板选择
        $this->display('Widget/tj');
    }

    /**
     * 根据ID查询当前数据的标签
     */
    public function tags($id){
        $tags = get_tags($id,'down');
        $this->assign('tags',$tags);
        $this->display('Widget/tags');
    }


    /**
     * 游戏攻略 和 相关应用
     */
    public function articleApply($id,$cate){
//        //获取产品标签
//        $tid = get_tags($id,'DOWN','product')[0]['id'];
//
//        //相关攻略 (产品标签+攻略大分类)
//        $guide = M('productTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->where("a.tid = $tid AND a.type='document' AND b.category_rootid = 85")->limit(10)->select();
//        if(count((array)$guide)<10){
//            $arr = M('productTagsMap')->alias('a')->field('b.id,b.title,b.create_time')->join('__DOCUMENT__ b ON a.did = b.id')->where("a.tid = $tid AND a.type='document'")->limit(10)->select();
//            dump($arr);die;
//            $guide = array_merge();
//        }

//        dump(M('productTagsMap')->getLastSql());
//        dump($guide);die;

        /************************老的调用*****************************/
        //标签查询
        $tags = get_tags($id,'down');

        //根据标签和分类查找8个相关应用
        $apply = $this->get_apply_Bytagid($tags);

        //如果标签的数据小于八条就从分类里面查找
        if(count($apply)<8){
            $rel = M('down')->field('id,title,cover_id,smallimg,abet')->where('category_id = '.$cate)->limit(8)->select();
            $apply= array_filter(array_merge($apply,(array)$rel));
        }
        //去除重复
        $apply = array_unique_fb($apply);

        //对应用进行重新排序
        $arrsort = array();
        foreach($apply as $k=>$v){
            foreach($v as $k1=>$v1){
                $arrsort[$k1][$k] = $v1;
            }
        }
        array_multisort($arrsort['abet'],SORT_DESC,$apply);
        $this->assign('CrumbNavi',getCrumb($id,'Down'));//输出面包屑导航
        //根据产品标签、标签、最新 获取相应的游戏攻略
        $article= array();
        $product_tags = get_tags($id,MODULE_NAME,'product');
        $product = $this->get_content_byTags($product_tags,'product');
        $article = array_filter(array_merge($article,(array)$product));
        if(count($article)<8){
            $product = $this->get_content_byTags($tags);
            $article = array_filter(array_merge($article,(array)$product));
            if(count($article)<8){
                $product = M('document')->field('id,title,create_time')->order('create_time desc')->limit(8)->select();
                $article = array_filter(array_merge($article,(array)$product));
            }
        }

        $this->assign(array(
            'tags' =>$tags,
            'apply'=>$apply,
            'article'=>$article,
        ));

        $this->display('Widget/articleApplay');
    }

    /**
     * 根据标签ID(array)获取相关的应用
     * @param $id array
     * @return array|bool
     */
    public function get_apply_Bytagid($id){
        $info = array();
        if(!is_array($id))  return false;

        foreach($id as $k=>$v){
            $info = array_merge($info,$this->get_apply_Bytags($v['id']));
            if(count($info)>=8)    break;
        }

        return $info;
    }

    /**根据标签ID获取相关的应用
     * @param $id
     * @return array|bool
     */
    public function get_apply_Bytags($id){
        if(!is_numeric($id))    return false;

        $rel = M('tagsMap')->where('tid = '.$id.' AND type = "down"')->limit(8)->select();
        if(!$rel)    return false;

        $info = array();
        if(count($rel)>8){
            for($i=0;$i<8;$i++)
                $info[] = M($rel[$i]['type'])->field('id,title,cover_id,smallimg,abet')->find($rel[$i]['did']);
        }else{
            foreach($rel as $k=>$v)
                $info[] = M($v['type'])->field('id,title,cover_id,smallimg,abet')->find($v['did']);
        }

        return $info;
    }

	public function rankCon($id,$type){//排行榜
	    $id  = empty($id) ? '102394' : $id;
	    $_LISTS_=array();
		$softID=M('DownPaihang')->where("id='$id'")->getField('soft_id');
		$ids=split(",",$softID);
		foreach($ids as $id){
			$_LISTS_[]=M('Down')->where("status=1 AND `id`=$id")->select();
		}
		$arrT=array();
		foreach($_LISTS_ as $value)
		{
			foreach($value as $value)
			{
			$arrT[]=$value;
			}
		}
		
	    $this->assign(ranks,array_slice($arrT,0,10));
		switch($type){
			case '0':
			$this->display('Widget/rankDetail');
			return;
			case '1':
			$this->display('Widget/rankCon1');
			return;
			case '2':
		    $this->display(T('Down@7230/Widget/rankIndex'));
			return;
			case '3':
			$this->display('Widget/rankCon2');
			return;
		}
		
	
		
	}





	public function paihang(){//排行榜
	       $this->assign("SEO",WidgetSEO(array('special',null,'9')));
		   $this->display('Widget/phb');
		}


    /**根据产品（或）标签、最新，查找相应的游戏攻略
     * @param $id int/array
     * @param string $type
     * @return bool/array
     */

    function get_content_byTags($id,$type="tags"){

        $table = "tagsMap";
        $in= array();
        $where = array();
        if(is_array($id)){
            foreach($id as $k=>$v){
                $where[] =$v['id'];
            }
            if(!empty($where)){
                $where = implode(',',$where);
                $where = ' AND tid IN ('.$where.')';
            }else
                return false;
        }else return false;

        if($type=="product")
            $table = "productTagsMap";
        else if($type !=="tags")
            return false;

        $rel = M($table)->field('did')->where('type = "document"'.$where)->select();
        if(empty($rel)) return false;

        foreach($rel as $k => $v){
            $in[] =$v['did'];
        }
        $in = implode(',',$in);
        return M('document')->field('id,title,create_time')->order('create_time desc')->where('id IN ('.$in.')')->limit(8)->select();
    }

    //礼包 专区 开服
    public function lzk($id){
        $zqs = get_base_by_tag($id,'down','batch','product',false);
        $zqUrl = false;
        if($zqs){
            foreach($zqs as $k=>$v){
                if($v['pid']==0){
                    $zqUrl ='/'.C('FEATURE_ZQ_DIR').'/'. $v['url_token'];
                    if(substr($zqUrl,-1,1)!='/'){
                        $zqUrl .='/';
                    }
                    break;
                }
        }
        }

        $this->assign('zq',$zqUrl);

        $this->display('Widget/lzk');
    }

    /**
     * @Author 肖书成
     * @comment 下载详情页的右边 攻略、礼包、开服表、相关应用、厂商
     * @param $id
     * @param $tags
     * @param $company_id
     */
    public function detailRight($id,$tags,$company_id,$category_id){
        //产品标签
        $tid    = $this->get_product_tag($id);

        //攻略
        if($tid){
            $gl     = $this->get_product_data($tid,'document','b.category_rootid = 85');
            if(empty($gl)){
                $gl = $this->get_product_data($tid,'document',1,'b.create_time',8);
            }elseif(count($gl)<8){
                $gl2 = $this->get_product_data($tid,'document','b.id NOT IN('.implode(',',array_column($gl,'id')).')','b.create_time',8-count($gl));
                $gl  = array_merge($gl,$gl2);
            }
        }

        //礼包
        if($tid){
            $lb     = $this->get_product_data($tid,'package','b.category_id IN(1,2,4)','c.start_time,c.end_time',8,'__PACKAGE_PMAIN__ c ON b.id = c.id');
            if(empty($lb)){
                $lb = $this->get_package('a.id,a.title,b.start_time,b.end_time','a.category_id = 1',8,'__PACKAGE_PMAIN__');
            }elseif($lb && count($lb)<8){
                $lb = array_merge($lb,$this->get_package('a.id,a.title,b.start_time,b.end_time','a.category_id = 1 AND a.id NOT IN('.implode(',',array_column($lb,'id')).')',8-count($lb),'__PACKAGE_PMAIN__'));
            }
        }else{
            $lb = $this->get_package('a.id,a.title,b.start_time,b.end_time','a.category_id = 1',8,'__PACKAGE_PMAIN__');
        }

        //开服表
        if($tid){
            $kf     = $this->get_product_data($tid,'package','b.category_id = 3','c.start_time,c.server',8,'__PACKAGE_PARTICLE__ c ON b.id = c.id');
            if(empty($kf)){
                $kf = $this->get_package('a.id,a.title,b.start_time,b.server','a.category_id = 3',8,'__PACKAGE_PARTICLE__');
            }elseif(count($kf)<8){
                $kf = array_merge($kf,(array)$this->get_package('a.id,a.title,b.start_time,b.server','a.category_id = 3 AND a.id NOT IN('.implode(',',array_column($kf,'id')).')',8-count($kf),'__PACKAGE_PARTICLE__'));
            }
        }else{
            $kf = $this->get_package('a.id,a.title,b.start_time,b.server','a.category_id = 3',8,'__PACKAGE_PARTICLE__');
        }

        //相关应用
        $tags   = implode(',',array_column($tags,'id'));

        if(!empty($tags)){
            $number = $this->get_product_data($tags,'down','b.id !='.$id,false,8,false,'TagsMap');
            if((int)$number[0]['count']>=8){
                $max = $this->get_product_data($tags,'down','b.id !='.$id,'b.smallimg','7,1',false,'TagsMap')[0]['id'];
                $where = 'b.id >= ' . (rand(0,((int)$max - (int)$number[0]['min'])) + (int)$number[0]['min']) . ' AND b.id !=' .$id;
                $xgyy = $this->get_product_data($tags,'down',$where,'b.smallimg',8,false,'TagsMap','');
            }elseif((int)$number[0]['count']>0){
                $xgyy   = $this->get_product_data($tags,'down','b.id !='.$id,'b.smallimg',8,false,'TagsMap');
                if(count($xgyy)<8){
                    $xgyy   = array_merge($xgyy,(array)M('Down')->field('id,title,smallimg')->where('pid = 0 AND category_id = '.$category_id)->limit(8-(int)$number[0]['count'])->select());
                }
            }else{
                $xgyy   = M('Down')->field('id,title,smallimg')->where(rand_where('Down','80','pid = 0 AND  category_id = '.$category_id))->limit(8)->select();
            }
        }else{
            $xgyy   = M('Down')->field('id,title,smallimg')->where(rand_where('Down','80','pid = 0 AND category_id = '.$category_id))->limit(8)->select();
        }


        //厂商
        $cs     = M('Company')->field('id,name,path,img')->where('id ='.$company_id)->find();
        if($cs){
            $csyx   = M('Down')->alias('a')->field('a.id,a.title,a.smallimg')->join('__DOWN_DMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.id !='.$id.' AND b.company_id ='.$company_id)->limit(6)->select();
        }

        $this->assign(array(
            'gl'    =>$gl,
            'lb'    =>$lb,
            'kf'    =>$kf,
            'xgyy'  =>$xgyy,
            'cs'    =>$cs,
            'csyx'  =>$csyx,
        ));

        $this->display('Widget/detailRight');
    }

    public function otherDown($id){
        $tid  = $this->get_product_tag($id);
        if($tid){
            $list = M('ProductTagsMap')->alias('a')->field('b.id,b.title,c.system,c.channel')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c  ON b.id = c.id')->where("a.tid = $tid AND a.type='down' AND a.did != $id")->select();

            $channel = C('FIELD_DOWN_CHANNEL');
            $ck      = array_keys($channel);

            foreach($list as $k=>&$v){
                if(!in_array($v['channel'],$ck)){unset($list[$k]);}
            }
            if($list){
                $this->assign('lists',$list);
                $this->display('Widget/otherDown');
            }
        }
    }

    public function get_package($field = 'a.id',$where,$row,$join,$order = 'a.id DESC'){
       return M('Package')->alias('a')->field($field)->join($join.' b ON a.id = b.id')->where($where)->limit($row)->select();
    }

    //根据(产品)标签获取数据的方法(秒杀90%以上根据(产品)标签获取的数据)
    public function get_product_data($tid,$type,$where = 1,$field='b.create_time',$row = 8,$join = false,$table = 'ProductTagsMap',$order = 'b.id DESC'){
        $b = '__'.strtoupper($type).'__';

        $field = $field?'b.id,b.title,'.$field:'max(b.id) max,min(b.id) min,count(b.id) count';

        $model = M($table)->alias('a')->field($field)->join("$b b ON a.did = b.id");

        if($join) $model->join($join);

        $tWhere = is_numeric($tid)?"a.tid = $tid":"a.tid IN($tid)";

        $list = $model->where("$tWhere AND a.type = '$type' AND b.status = 1 AND b.pid = 0 AND $where")->order($order)->limit($row)->select();

        return $list;
    }

    public function get_product_tag($id){
        return M('ProductTagsMap')->where('did ='.$id.' AND type = "down"')->getField('tid');
    }

}