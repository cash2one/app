<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/3/3
 * Time: 16:00
 */

namespace Package\Widget;

use Package\Controller\BaseController;

class AfsWidget extends BaseController{
    /**
     * @Author 肖书成
     * @Create_time 2015/03/03
     * @Comments 礼包详情页下面的 相关礼包
     */
    public function detailOtherPackage($id){
        //产品标签
        $tid = get_tags($id,'package','product')[0]['id'];

        //根据产品标签查找相关礼包
        $packages = null;
        if($tid){
            $packages = M('productTagsMap')->alias('a')->field('b.*,c.card_type,c.conditions')->join('__PACKAGE__ b ON a.did = b.id')->join('__PACKAGE_PMAIN__ c ON b.id = c.id')->where("a.tid = $tid AND a.type = 'package' AND b.category_id = 1 AND b.id != $id")->order('b.id DESC')->limit(9)->select();
            //根据产品标签获取游戏
            $game = M('productTagsMap')->alias('a')->field('b.id')->join('__DOWN__ b ON a.did = b.id')->where("a.tid = $tid AND a.type='down'")->order('b.id DESC')->find();
            if($game){
                $game['url'] = C('PACKAGE_SLD').'/oneli/'.$game['id'].'.html';
            }
        }

        $this->assign(array(
            'packages'=>$packages,//相关礼包
            'game'=>$game['url'],//游戏（更多这个地方使用）
            'empty'=>'<li style="width: 100%; font-size: 24px;  height: 318px; line-height: 318px; text-align: center; color: #C0C0C0">暂无相关礼包</li>'
        ));
        $this->display('Widget/detailOtherPackage');
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 开测表、开服表
     */

    public function testServer(){
        $cate_id = I('cate');
        $theme = I('tem');
        $seo = I('seo');

        //今日开测
        $todayData = $this->getTestServer($cate_id,'b.start_time >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL 0 DAY)) AND b.start_time < UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL -1 DAY))',0,1000);

        //即将开测
        $willData = $this->getTestServer($cate_id,'b.start_time >= UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL -1 DAY))',0,1000);

        //历史开测
        $historyData = $this->getTestServer($cate_id,'b.start_time < UNIX_TIMESTAMP(DATE_SUB(CURDATE(),INTERVAL 0 DAY))');
        $this->assign(array(
            'todayData'=>$todayData,
            'willData'=>$willData,
            'historyData'=>$historyData
        ));

        //SEO
        $this->assign("SEO",WidgetSEO(array('special',null,$seo)));

        $this->display('Category/'.$theme);
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 获取开测开服的数据（此方法仅供testServer方法调用）
     * @param $cate_id  //礼包分类ID
     * @param $where    //查询数据的条件
     * @return $date array
     */

    private function getTestServer($cate_id,$where,$sta=0,$len=10){
        //获取数据
        $date = M('Package')->alias('a')->field('a.id,a.title,a.description,a.cover_id,b.start_time,b.server,b.game_type,server_type,b.conditions,c.tid')->join('__PACKAGE_PARTICLE__ b ON a.id = b.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did','left')->where("a.status = 1 AND a.category_id = $cate_id AND $where AND c.type='package'")->limit($sta,$len)->order('b.start_time DESC')->select();

        //获取每条数据的产品标签
        foreach($date as $k=>$v){
            if($v['tid']){
                $games = M('productTagsMap')->alias('a')->field('b.id,b.description,c.system,d.name company,e.url,e.site_id')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')->join('__COMPANY__ d ON c.company_id = d.id','left')->join('__DOWN_ADDRESS__ e ON b.id = e.did')->order('b.id DESC')->where('a.tid = '.$v['tid'].' AND a.type="down"')->select();
                if(empty($games)){
                    continue;
                }

                foreach($games as $k1=>$v1){
                    if($v1['system'] == 1){
                        if($date[$k]['androidUrl'])
                            continue;
                        $date[$k]['androidUrl'] = $this->formatAddress($v1['url'],$v1['site_id']);
                        $date[$k]['androidCode'] = builtQrcode($date[$k]['androidUrl']);
                    }elseif($v1['system'] == 2){
                        if($date[$k]['iosUrl'])
                            continue;
                        $date[$k]['iosUrl'] = $this->formatAddress($v1['url'],$v1['site_id']);
                        $date[$k]['iosCode'] = builtQrcode($date[$k]['iosUrl']);
                    }
                    if(!$date[$k]['company']){
                        $date[$k]['company'] = $v1['company']?$v1['company']:'';
                    }
                }
                $date[$k]['game'] = $games[0]['id'];
                $date[$k]['description'] = empty($date[$k]['description'])?$games[0]['description']:$date[$k]['description'];
            }
        }

        return $date;
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/4
     * @Comments 下载地址容错处理（此方法仅供getTestServer方法调用）
     * @Param string  $url      //url 地址
     * @Param int     $sit_id
     * @Return string $url
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
     * @Create_time 2015/3/16
     * @Comments 礼包首页游戏排行表
     */
    public function gameRank(){
        $rank = M('DownPaihang')->find('105641');
        $rank = explode(',',$rank['soft_id']);
        $games = array();
        for($i = 0;$i<10;$i++){
            $games[] = M('Down')->alias('a')->field('a.id,a.title,a.smallimg,b.size,c.title cate')->join('__DOWN_DMAIN__ b ON a.id = b.id')->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')->where('a.id ='.$rank[$i])->find();
        }

        //开测
        $test = $this->linkGame(5);

        //开服
        $server = $this->linkGame(4);

        $this->assign(array(
            'games' =>  $games,
            'test'  =>  $test,
            'server'=>  $server,
        ));
        $this->display(T('Package@afs/Widget/gameRank'));
    }

    private function linkGame($cate){
        $data = M('Package')->alias('a')->field('a.title,b.start_time,b.server,c.tid')->join('__PACKAGE_PARTICLE__ b ON a.id = b.id','left')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did','left')->where('a.category_id = '.$cate.' AND c.type = "package"')->order('a.id DESC')->limit(12)->select();
        foreach($data as $k=>&$v){
            $game = M('Down')->alias('a')->field('a.id')->join('__PRODUCT_TAGS_MAP__ c ON a.id = c.did')->where('a.status = 1 AND c.tid = '.$v['tid'].' AND c.type = "down"')->order('a.id DESC')->select();
            if($game){
                $v['game'] = $game[0]['id'];
            }
        }unset($v);

        return $data;
    }

    /**
     * @Author 肖书成
     * @Create_time 2015/3/24
     * @Comments 礼包首页游戏排行表
     */
    public function packageGame($id){
        //获取产品标签
        $tid = get_tags($id,'package','product')[0]['id'];

        //获取该产品标签的游戏和专区
        if($tid){
            $game = M('productTagsMap')->alias('a')->field('b.id')->join('__DOWN__ b ON a.did = b.id')->where("a.tid = $tid AND a.type = 'down'")->order('id DESC')->find();
            $batch = M('productTagsMap')->alias('a')->field('b.url_token')->join('__BATCH__ b ON a.did = b.id')->where("a.tid = $tid AND a.type = 'batch' AND b.pid = 0 AND b.interface = 0")->find();
            $zq = C(FEATURE_ZQ_DIR);
            if($batch){
                $batch = C('STATIC_URL') . ($zq?'/'.$zq.'/':'/').$batch['url_token'];
            }
        }

        //给页面赋值
        $this->assign(array(
            'game'=>$game['id'],
            'batch'=>$batch,
        ));

        //选择模板
        $this->display('Widget/packageGame');
    }


}