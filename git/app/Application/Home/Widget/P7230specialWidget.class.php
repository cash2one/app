<?php
namespace Home\Widget;
use Think\Controller;

/**
 * 后台控制器
 * @author leha.com
 */

class P7230SpecialWidget extends Controller {

	//文档模型名
	protected $document_name = 'Special';
	protected $base_view = 'Feature';
	protected $main_title='K页面';
	protected $model_id='17';

    public function base($id){
        //基础数据
        $base = M('special')->field('title,seo_title,keywords,description,icon')->where('id = '.$id)->find();
//        $base = M('special')->field(true)->where('id = '.$id)->find();
        //SEO
        $SEO['title'] = $base['seo_title']?$base['seo_title']:$base['title'];
        $SEO['keywords'] = $base['keywords'];
        $SEO['description'] = $base['description'];

        $this->assign('SEO',$SEO);
        return $base;
    }


	function recommends($tags,$cats){
		$results=M('down')->where('status=1')->limit('12')->select();
		foreach($results as $k=>&$v){
			$v=$v+M('down_dmain')->where('id='.$v['id'])->find();
			$vv=M('down_address')->where('did='.$v['id'])->find();
			if(is_array($vv)) $v+=$vv;
		}
		return $results;
	}
	
	function lists($cats,$tags){
		$id='';
		//var_dump($cats,$tags);
		if($tags){
			$tid=implode('\',\'',$tags);
			$maps=M('product_tags_map')->field('did')->where('tid in (\''.$tid.'\')')->limit('300')->select();
			foreach($maps as $k=>$v){
				if($k) $id.='\',\''.$v['did'];
				else $id.=$v['did'];
			}
			$lists=M('down')->where('id in (\''.$id.'\')')->limit('30')->select();
		}else{
			$cid=implode('\',\'',$cats);
			$lists=M('down')->where('category_id in (\''.$cid.'\')')->limit('30')->select();
		}
		foreach($lists as $k=>&$v){
			$v=$v+M('down_dmain')->where('id='.$v['id'])->find();
			$v['size']=round($v['size']/1024,2);
			$vv=M('down_address')->where('did='.$v['id'])->find();
			if(is_array($vv)) $v+=$vv;
		}
		//$this->assign('lists',$lists);
		return $lists;
	}

    //分页
    public function pages($count,$row,$id){
    	$rows=M($this->document_name)->field('url_token')->find($id);
        $Page = new \Think\Page($count,$row,'',false,C('FEATURE_ZQ_DIR').'/'.$rows['url_token'].C('FEATURE_PAGE'));// 实例化分页类 指定路径规则
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $show       = $Page->show();// 分页显示输出
        $show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';
        $this->assign('page',$show);// 赋值分页输出
        S($this->document_name.'PageNumber',array($id=>ceil($count/$row)),86400);
    }	
	
	public function __call($method, $args){
		return;
	}

    //月报热门游戏
    public function monthGame(){
        return $this->getGame('hits_month DESC');
    }

    //周报热门游戏
    public function weekGame(){
        return $this->getGame('hits_week DESC');
    }

    private function getGame($order){
        return  M('down')->alias('a')->field('a.id,a.title,a.description,a.abet,a.smallimg,c.hits')->join('__DOWN_DMAIN__ b ON a.id=b.id')->join('__DOWN_ADDRESS__ c ON a.id = c.did')->where('status = 1')->order($order)->limit(8)->select();
    }

    public function hjztsj($id)
    {
        $pcTitle = M('Special')->where('id =' . $id)->getField('title');
//        $spacial = M('Special')->field('layout,content')->where("interface = 0 AND title ='$pcTitle'")->find();
        $content = M('Special')->where("interface = 0 AND title ='$pcTitle'")->getField('content');
//
        $regular = '/<div\s+class="dragable"\s+widget="([^"]+)"[^>]*?>(.*?)<\/div>/is';

        if(preg_match_all($regular, $content, $results)){
            foreach ($results[1] as $k => $v) {
                $widget[$v][$k] = $results[2][$k];
            }
        }

        $regular    = '/<img src="([^"]+)" alt="" \/>/is';
        preg_match($regular,$widget['image'][2],$rel);

        //小编头像
         $author['icon'] = $rel[1];

        //小编有话说
        $author['description'] = clean_str($widget['html'][3]);
        unset($widget['html'][3]);

        $cate[]['cate'] = clean_str($widget['html'][4]);
        unset($widget['html'][4]);

        //大分类
        foreach($widget['html'] as $k=>$v){
            if($widget['html'][$k+1]){
                if(!$widget['html'][$k-1]){
                    $cate[]['cate'] = clean_str($widget['html'][$k+1]);
                }
            }
        }

        //下载参数正则
        $regular1 = '/checked\(\'([^"]+)\'\);/is';
        $i = 0;

        foreach($widget['down__product_tags'] as $k=>$v){
            preg_match($regular1,$v,$down);
            $downId = str_replace('&amp;','&',$down[1]);
            parse_str($downId,$downArr);

            if($downArr['down'][0]){
                $downList[] = $this->getDown($downArr['down'][0]);

                if(!$widget['down__product_tags'][$k+2]){
                    $cate[$i]['data'] = $downList;
                    $i++;
                    unset($downList);
                }
            }
        }
//dump($cate);die;
        $this->assign(array(
            'author'=>$author,
            'cate'  =>$cate,
        ));
    }

    public function getDown($id){
        if(empty($id)) return false;
        $rel = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,b.system,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where("a.id = $id")->find();
        $rel['size'] = format_size($rel['size']);
        $rel['tags'] = get_tags($id,'Down');
        $rel['product'] = get_tags($id,'Down','product')[0]['id'];
        if($rel['product']){
            if($rel['system'] == '1'){
                $rel['adr'] = $rel['id'];
                $result = M('ProductTagsMap')->alias('a')->field('b.id')->join('__DOWN_DMAIN__ b ON a.did = b.id')->where('a.tid = '.$rel['product'].' AND a.type = "down" AND b.system = 2')->order('b.id DESC')->find();
                if($result['id']) $rel['ios'] = $result['id'];
            }else{
                $rel['ios'] = $rel['id'];
                $result = M('ProductTagsMap')->alias('a')->field('b.id')->join('__DOWN_DMAIN__ b ON a.did = b.id')->where('a.tid = '.$rel['product'].' AND a.type = "down" AND b.system = 1')->order('b.id DESC')->find();
                if($result['id']) $rel['adr'] = $result['id'];
            }
        }

        return $rel;
    }

}