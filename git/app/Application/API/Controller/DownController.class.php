<?php
/**
 * 作者: 肖书成
 * 时间: 2015/7/14
 * 描述: 下载数据接口类
 */

namespace API\Controller;
use Think\Controller;

class DownController extends Controller{


    /*********************************App详情Start***********************************/

    /**
     * 作者:肖书成
     * 描述:获取详情页数据
     */
    public function getAppDetailInfo(){
        //接收参数
        $id     =   I('get.appId');

        //验证参数
        if(!is_numeric($id) || (int)$id<1){
            echo json_encode(request_status(400));exit;
        }

        //数据查询
        $info   =   M('Down')->alias('a')->field('a.title,a.previewimg,a.create_time,b.content')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.id = '.$id)->find();

        $value = array();
        if($info){
            $value['name']  =   $info['title'];
            if($info['previewimg']){
                $imgs = explode(',',$info['previewimg']);
                foreach($imgs as $k=>$v){
                    $value['imgs'][] = get_cover($v,'path');
                }
            }

            $value['createTime']    =   $info['create_time'];
            $value['detailInfo']       =   $info['content'];
        }

        //获取状态
        $result = request_status(200);
        $result = array_merge($result,$value);
        echo json_encode($result);

    }


    /**
     * 作者:肖书成
     * 描述:获取评论数据
     */
    public function getAppCommentList(){
        //接收参数
        $id     =   I('get.appId');
        $type   =   I('get.commentType');
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        $arr    =   array($id,$p,$row);
        foreach($arr as $v){
            if(!is_numeric($v)||(int)$v<1){
                echo json_encode(request_status(400));exit;
            }
        }
        if(!in_array($type,array(0,1,2))){
            echo json_encode(request_status(400));exit;
        }

        //处理参数
        $star = ($p-1)*$row;

        //数据查询
        $sql    =   M('Comment')->field('id,uid,message,add_time')->where('enabled = 1 AND type = "down" AND document_id = '.$id)->order('id DESC')->buildSql();
        $count  =   M()->query("SELECT count('id') count FROM $sql str");
        $count  =   $count[0]['count'];
        $data   =   M()->query("$sql limit $star,$row");

        //数据映射
        $lists                  =   array();
        $lists['commentNumMan'] =   $lists['commentNum']  = $count;
        $lists['commentScore']  =   0;
        $lists['goodNum']       =   0;
        $lists['midNum']        =   0;
        $lists['badNum']        =   0;

        if($count>0){
            foreach($data as $k=>$v){
                $list['userIcon']       = '';
                $list['userName']      = '';
                $list['userId']         = $v['uid'];
                $list['commentId ']     = $v['id'];
                $list['commentScore']   = 0;
                $list['commentContent'] = $v['message'];
                $list['commentTime']    = $v['add_time'];

                $lists['commentList'][] = $list;
            }
        }else{
            $lists['commentList']       = array();
        }

        //获取状态
        $result = request_status(200);
        $result = array_merge($result,$lists);
        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:插入APP评论数据
     */
    public function addAppComment(){
        //接收参数
        $id         =   I('post.appId');
        $message    =   I('post.message');
        $message    =   preg_replace("/\s|　|'|\"|%|_/","",$message);

        //验证参数
        if(!is_numeric($id)||(int)$id<1 || empty($message)){
            echo json_encode(request_status(400));exit;
        }
        $count = M('down')->where('status = 1 AND id = '.$id)->count();
        if($count<1){
            echo json_encode(request_status(400));exit;
        }

        //参数处理
        $data['type'] = 'down';
        $data['uid']  = 0;
        $data['uname']= '来自APP助手';
        $data['document_id']    =   $id;
        $data['message']        =   $message;
        $data['add_time']       =   time();
        $data['at_uid']         =   0;
        $data['votes']          =   0;
        $data['enabled']        =   0;

        $rel                    =   M('comment')->add($data);

        if($rel>0){
            $result = request_status(200);
            $result['commentId'] =  $rel;
        }else{
            $result = request_status(500);
        }

        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:助手反馈
     */
    public function addComment(){
        //接收参数
        $message    =   I('post.message');
        $message    =   preg_replace("/\s|　|'|\"|%|_/","",$message);

        //验证参数
        if(empty($message)){
            echo json_encode(request_status(400));exit;
        }

        //参数处理
        $data['type'] = 'app';
        $data['uid']  = 0;
        $data['uname']= '来自APP助手';
        $data['document_id']    =   0;
        $data['message']        =   $message;
        $data['add_time']       =   time();
        $data['at_uid']         =   0;
        $data['votes']          =   0;
        $data['enabled']        =   0;

        $result     = M('comment')->add($data);

        if($result>0){
            $result = request_status(200);
        }else{
            $result = request_status(500);
        }

        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:下载统计
     */
    public function updateDownloadNum(){
        //接收参数
        $id = I('get.appId');

        //验证参数
        if(!is_numeric($id) || (int)$id<1){
            echo json_encode(request_status(400));exit;
        }

        //数据库执行操作
        $view = M('Down')->where('status = 1 AND id = '.$id)->getField('view');
        if(empty($view)){
            echo json_encode(request_status(400));exit;
        }
        $view = (int)$view + 1 ;
        $rel = M('Down')->where('status = 1 AND id = '.$id)->setField('view',$view);

        //结果处理
        if(!$rel){
            echo json_encode(request_status(400));exit;
        }else{
            echo json_encode(request_status(200));exit;
        }

    }


    /********************************* 首页 ***********************************/

    /**
     * 作者:肖书成
     * 描述:首页幻灯片
     */
    public function getHomeRecommendIndex($isJson = true){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //数据查询
        $data   =   M('Down')->alias('a')
            ->field('a.id,a.title,a.description,a.category_id,a.view,a.cover_id,a.smallimg,a.create_time,b.size,b.version,c.title cate,d.url,d.site_id,d.bytes,e.title company')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')
            ->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')
            ->join('__DOWN_ADDRESS__ d ON a.id = d.did','left')
            ->join('__COMPANY__ e ON b.company_id = e.id','left')
            ->where('a.status = 1 AND a.app_position & 1 AND d.site_id NOT IN(5,6)')->order('a.update_time DESC')->group('a.id')->page($p,$row)->select();

        //数据映射
        $lists = array();
        foreach($data as $k=>$v){
            $list['appId']          = $v['id'];
            $list['name']           = $v['title'];
            $list['icon']           = get_cover($v['smallimg'],'path');
            $list['bigIcon']        = get_cover($v['cover_id'],'path');
            $list['category']       = $v['category_id'];
            $list['categoryName']   = $v['cate'];
            $list['downloadNum']    = $v['view'];
            $list['fileLength']     = $v['bytes'];
            $list['size']           = $v['size'];
            $list['intro']          = $v['description'];
            $list['versionCode']    = '';
            $list['versionName']    = $v['version'];
            $list['downloadUrl']    = $v['site_id']?formatAddress($v['url'],$v['site_id']):'';
            $list['author']         = $v['company'];
            $list['virusState ']    = 0;
            $list['adState']        = 0;
            $list['protectState']   = 0;
            $list['createTime']     = $v['create_time'];

            $lists[] = $list;
        }

        if(!$isJson){
            return $lists;
        }

        //获取状态
        $result = request_status(200);
        $result['apps'] = $lists;

        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:首页网游推荐
     */
    public function getIndexOnlineGame(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //结果处理
        $this->listResult(' AND app_position & 2','a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:首页列表
     */
    public function getHomeRecommendList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //结果处理
        $this->listResult(' AND app_position & 4','a.update_time DESC',$p,$row);
    }

    /**
     * 作者:肖书成
     * 描述:推荐
     */
    public function getIndexPosition(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //结果处理
        $this->listResult(' AND app_position & 8','a.update_time DESC',$p,$row);
    }

    /**
     * 作者:肖书成
     * 描述:常用
     */
    public function getOftenList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }


        //结果处理
        $this->listResult(' AND app_position & 16','a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:热门
     */
    public function getHomeDownNumList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //结果处理
        $this->listResult(' AND a.app_position & 32','a.update_time DESC',$p,$row);
    }

//----------------------------------------------
    /**
     * 作者:肖书成
     * 描述:最新
     */
    public function getNewList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }


        //结果处理
        $this->listResult(' AND a.app_position & 64','a.update_time DESC',$p,$row);
    }


    /*********************************软件Start***********************************/

    //--------------------------------精品----------------------------------------
    /**
     * 作者:肖书成
     * 描述:优质首发
     */
    public function getSoftRecList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }


        //结果处理
        $where  =   ' AND category_rootid = 12 AND a.app_position & 128';
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:最新软件
     */
    public function getNewSoftList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');


        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   ' AND a.category_rootid = 12 AND a.app_position & 256';

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }

    //--------------------------------分类----------------------------------------
    /**
     * 作者:肖书成
     * 描述:获取下载分类
     */
    public function getCategory(){
        //接收参数
        $pid    =   I('get.type');

        //检验参数
        if(!in_array($pid,array('1','2'))){
            echo json_encode(request_status(400));exit;
        }

        //数据获取
        $cate = array(2=>'(pid IN(1,116) OR id IN(1,116))',1=>'(pid = 12 OR id = 12)');

        $data   = M('DownCategory')->field('id,title,pid,icon')->where("status = 1 AND ".$cate[$pid])->select();

        //数据映射
        $lists                  =   array();
        $arr                    =   array();
        foreach($data as $k=>$v){
            $list               =   array();
            $list['cateId']     =   $v['id'];
            $list['name']       =   $v['title'];
            if(((int)$v['pid'])>0){
                $list['parentId'] = $v['pid'];
                $list['icon']     =   get_cover($v['icon'],'path');
            }else{
                $arr[]            = $list;
                continue;
            }
            $lists[]            =   $list;
        }

        foreach($lists as $k=>$v){
            foreach($arr as $x=>$y){
                if($y['cateId'] == $v['parentId']){
                    $arr[$x]['child'][] = $v;
                }
            }
        }

        //获取状态
        $result = request_status(200);
        $result['category'] = $arr;
        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:根据分类查询应用APP列表
     */
    public function getAppForCategory(){
        //接收参数
        $cate   =   I('get.Category');
        $p      =   I('get.page');
        $row    =   I('pageSize');

        //检验参数
        $arr    =   array($cate,$p,$row);
        foreach($arr as $k=>$v){
            if(!is_numeric($v) || (int)$v < 1){
                echo json_encode(request_status(400));exit;
            }
        }

        if($row>30){
            echo json_encode(request_status(400));exit;
        }

        if(in_array($cate,array(1,116,12))){
            $where = " AND category_rootid = ".$cate;
        }else{
            $where = " AND category_id = ".$cate;
        }

        //结果处理
        $this->listResult($where,'a.id DESC',$p,$row);
    }


    //--------------------------------排行----------------------------------------
    /**
     * 作者:肖书成
     * 描述:单机、网页、软件 排行
     */
    public function getSortList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');
        $sortType   =   I('get.sortType');

        $arr        =   array('1','12','116','game');

        if(!in_array($sortType,$arr)){
            echo json_encode(request_status(400));exit;
        }

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   $sortType == 'game'?' AND category_rootid IN(1,116)':' AND category_rootid = '.$sortType;

        //结果处理
        $this->listResult($where,'a.view DESC ,a.update_time DESC',$p,$row);
    }


    /*********************************游戏Start***********************************/
    //--------------------------------精品----------------------------------------
    /**
     * 作者:肖书成
     * 描述:游戏风云榜
     */
    public function getGameRecList(){
        //接收参数
        $p      =   I('get.page');
        $row    =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        //结果处理
        $where      =   ' AND a.category_rootid IN(1,116) AND a.app_position & 512';
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:游戏精品列表
     */
    public function getGameList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');


        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   ' AND a.category_rootid IN(1,116) AND a.app_position & 1024';

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:最新游戏
     */
    public function getNewGameList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');


        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   ' AND a.category_rootid IN(1,116) AND a.app_position & 2048';

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }

    /**
     * 作者:肖书成
     * 描述:最新网游
     */
    public function getNewOnlineGameList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');


        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   ' AND a.category_rootid = 1 AND a.app_position & 4096';

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }


    /**
     * 作者:肖书成
     * 描述:最新网游
     */
    public function getNewConsoleGameList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');


        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   ' AND a.category_rootid = 116 AND a.app_position & 8192';

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }


    /*********************************搜索Start***********************************/


    /**
     * 作者:肖书成
     * 获取搜索关键词排行榜
     */
    //搜索关键字
    public function getSearchKeyList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');

        //检验参数
        if(!is_numeric($row) || !is_numeric($p) || (int)$row>100 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $lists = M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')->where('a.status = 1 AND a.app_position & 32')->order('a.update_time DESC')->page($p,$row)->getField('sub_title',true);
        //获取状态
        $result = request_status(200);
        $result['keys'] = $lists?$lists:array();

        echo json_encode($result);
    }


    //搜索匹配关键词APP列表
    public function getSearchKeyContentList(){
        //接收参数
        $p          =   I('get.page');
        $row        =   I('get.pageSize');
        $searchKey  =   remove_xss(I('get.searchKey'));

        $searchKey  = preg_replace("/\s|　|'|\"|%|_/","",$searchKey);

        //检验参数
        if(empty($searchKey) || !is_numeric($row) || !is_numeric($p) || (int)$row>30 || (int)$row<1 || (int)$p <1){
            echo json_encode(request_status(400));exit;
        }

        $where      =   " AND a.title like '%".$searchKey."%'";

        //结果处理
        $this->listResult($where,'a.update_time DESC',$p,$row);
    }





    /**
     * 作者:肖书成
     * 描述:手机应用更新
     */
    public function updateApp(){
        $size = I('post.size');
        $size = (int)$size;

        //验证参数
        if(!is_numeric($size) || (int)$size<1){
            echo json_encode(request_status(400));exit;
        }

        $lists = array();
        for($i = 0;$i<$size;$i++){
            $pkg            =   I('post.pkg'.$i);

            if($pkg){
                $lists[$pkg]    =   preg_replace("/'/",'',I('post.vName'.$i));
            }
        }

        $ids = array();
        foreach($lists as $k=>$v){
            $rel = M('Down')->alias('a')->join('__DOWN_DMAIN__ b ON a.id = b.id')->field('a.id,b.version,b.package_name')
                ->where("a.status = 1 AND b.package_name = '$k'")->select();

            if($rel){
                foreach($rel as $k1=>$v2){
                    if(strcmp($v2['version'],$v)>0){
                        $ids[] = $v2['id'];
                    }
                }
            }
        }

        $lists                      = array();
        if(!empty($ids)){
            //数据查询
            $where  =' AND a.id IN('.implode(',',$ids).')';
            $data = $this->select($where,'a.update_time DESC',0,10000);

            //数据映射
            $lists = $this->formatList($data);
        }

        //获取状态
        $result = request_status(200);
        $result['apps'] = $lists;

        echo json_encode($result);
    }



    /**
     * 作者:肖书成
     * 描述:检测 “东东助手APP” 是否有是否有新的版本
     */
    public function getVersion(){
        //接收参数
        $type = I('get.type');

        //验证参数
        if(!is_numeric($type) || (int)$type<1){
            echo json_encode(request_status(400));exit;
        }

        $data = M('App')->field('code,title,content,url,type,icon')->where('status = 1 AND type = '.$type)->order('id DESC')->find();


        if(!$data){
            echo json_encode(request_status(200));exit;
        }
        $data['icon'] = get_cover($data['icon'],'path');

        //获取状态
        $result = array_merge(request_status(200),$data);
        echo json_encode($result);
    }



    /****************************综合方法*********************************/
    /**
     * 作者:肖书成
     * 描述:首页的综合数据 (首页推荐位、网游推荐、首页推荐APP)
     */
    public function HomeDataList(){
        //判断是否有缓存
//        $rel = S('HomeDataList');
//        if($rel){
//            echo $rel;
//            exit;
//        }

        //首页幻灯片
        $_GET['page'] = 1;
        $_GET['pageSize'] = 6;
        $position   = $this->getHomeRecommendIndex(false);

        //首页网游推荐
        $online     = $this->select(' AND a.app_position & 2','a.update_time DESC',1,2);


        //首页推荐列表
        $list       = $this->select(' AND a.app_position & 4','a.update_time DESC',1,10);

        //获取状态
        $result = request_status(200);
        $result['indexApps']         = $position;
        $result['apps']    = $this->formatList($list);
        $result['gameApps']     = $this->formatList($online);

        $rel            = json_encode($result);

        //加入缓存
//        S('HomeDataList',$rel,1200);

        echo $rel;
    }


    /**
     * 作者:肖书成
     * 描述:软件首页综合接口（首页推荐APP软件、软件首发APP）
     */
    public function softDataList(){
        //判断是否有缓存
//        $rel = S('softDataList');
//        if($rel){
//            echo $rel;
//            exit;
//        }


        //优质首发
        $where      = ' AND category_rootid = 12 AND a.app_position & 128';
        $position   = $this->select($where,'a.update_time DESC',1,2);


        //精品软件列表
        $where      = ' AND a.category_rootid = 12 AND a.app_position & 256';
        $list       = $this->select($where,'a.update_time DESC',1,10);

        //获取状态
        $result = request_status(200);
        $result['apps']         = $this->formatList($position);
        $result['softApps']     = $this->formatList($list);

        $rel            = json_encode($result);

        //加入缓存
//        S('softDataList',$rel,1200);

        echo $rel;
    }


    /**
     * 作者:肖书成
     * 描述:游戏首页综合接口（首页推荐游戏APP、游戏首发APP）
     */
    public function gameDataList(){
        //判断是否有缓存
//        $rel = S('gameDataList');
//        if($rel){
//            echo $rel;
//            exit;
//        }


        //游戏风云榜（精品游戏）
        $where  =   ' AND category_rootid IN (1,116) AND a.app_position & 512';
        $position   = $this->select($where,'a.update_time DESC',1,2);


        //最新游戏
        $where      = ' AND a.category_rootid IN(1,116) AND a.app_position & 1024';
        $list       = $this->select($where,'a.update_time DESC',1,10);


        //获取状态
        $result = request_status(200);
        $result['apps']         = $this->formatList($position);
        $result['gameApps']     = $this->formatList($list);

        $rel            = json_encode($result);

        //加入缓存
//        S('gameDataList',$rel,1200);

        echo $rel;
    }

    /*****************************私有方法*******************************/


    /**
     * 作者:肖书成
     * 描述:操作数据库获取数据列表
     * @param string $where
     * @param int    $p
     * @param int    $row
     * @return mixed
     */
    private function select($where,$order,$p,$row){
        //数据查询
        $data   =   M('Down')->alias('a')
            ->field('a.id,a.title,a.description,a.category_id,a.view,a.smallimg,a.create_time,b.size,b.version,b.package_name,c.title cate,d.url,d.site_id,d.bytes,e.title company')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')
            ->join('__DOWN_CATEGORY__ c ON a.category_id = c.id')
            ->join('__DOWN_ADDRESS__ d ON a.id = d.did','left')
            ->join('__COMPANY__ e ON b.company_id = e.id','left')
            ->where('a.status = 1'.$where.' AND d.site_id NOT IN(5,6)')->group('a.id')->order($order)->page($p,$row)->select();
        return $data;
    }


    /**
     * 作者:肖书成
     * 描述：列表页数据映射处理
     */
    private function formatList($data){
        //数据映射
        $lists                      = array();
        foreach($data as $k=>$v){
            $list['appId']          = $v['id'];
            $list['name']           = $v['title'];
            $list['icon']           = get_cover($v['smallimg'],'path');
            $list['category']       = $v['category_id'];
            $list['categoryName']   = $v['cate'];
            $list['downloadNum']    = $v['view'];
            $list['fileLength']     = $v['bytes'];
            $list['size']           = $v['size'];
            $list['intro']          = $v['description'];
            $list['versionCode']    = '';
            $list['versionName']    = $v['version'];
            $list['pkgName']        = $v['package_name'];
            $list['downloadUrl']    = $v['site_id']?formatAddress($v['url'],$v['site_id']):'';
            $list['author']         = $v['company'];
            $list['virusState ']    = 0;
            $list['adState']        = 0;
            $list['protectState']   = 0;
            $list['createTime']     = $v['create_time'];

            $lists[] = $list;
        }

        return $lists;
    }

    /**
     * 作者:肖书成
     * 描述:结果处理,打印json数据
     * @param array $lists
     */
    private function result($lists){
        //获取状态
        $result = request_status(200);
        $result['apps'] = $lists;

        echo json_encode($result);
    }


    /**
     * 作者:肖书成
     * 描述:列表数据结果处理
     * @param string $where
     * @param string $order
     * @param int    $p
     * @param int    $row
     */
    private function listResult($where,$order,$p,$row){

        //判断是否有缓存，有缓存直接取缓存；
//        $key = md5($where.$order.$p.$row);
//        $rel = S($key);
//        if($rel){
//            echo $rel;
//            exit;
//        }

        //数据查询
        $data   =   $this->select($where,$order,$p,$row);

        //数据映射
        $lists  =   $this->formatList($data);


        //获取状态
        $result = request_status(200);
        $result['apps'] = $lists;
        $rel            = json_encode($result);

        //加入到缓存
//        S($key,$rel,1200);

        echo $rel;
    }


    /********************脚本*********************/
    public function bytes(){
        $star = I('star')?I('star'):0;
        if(I('all')){
            $all = I('all');
        }else{
            $all = M('DownAddress')->where('bytes = "0"')->count();
        }

        $num = I('num')?I('num'):0;
        $error = I('error')?I('error'):0;

        set_time_limit(1800);
        $i = 0;

        $data = M('DownAddress')->field('`id`,`url`,`site_id`,`bytes`')->where('bytes = "0"')->limit($star,10)->select();

        if(empty($data)){
            echo '一共'.$all.'条数据，成功更新了'.$num.'条数据,有'.$error.'条数据执行失败';
            echo PHP_EOL.'全部执行完毕';
            exit;
        }

        $count = count($data);
        for($i;$i<$count;$i++){
            $address    =   formatAddress($data[$i]['url'],$data[$i]['site_id']);
            $heaer      =   get_headers($address,1);
            if(is_array($heaer['Content-Length'])){
                $byte = $heaer['Content-Length'][1];
            }else{
                $byte = $heaer['Content-Length'];
            }

            if(!is_numeric($byte)){
                $error++;
                continue;
            }

            $list['bytes']  = $byte;
            $number = M('DownAddress')->where('id = '.$data[$i]['id'])->save($list);

            if($number>0){
                $num += $number;
            }else{
                $error++;
            }
        }

        header('Content-Type:text/html; charset=utf-8');

        $info = '一共'.$all.'条数据，成功更新了'.$num.'条数据,有'.$error.'条数据执行失败';

        $url = C('STATIC_URL'). "/api.php?s=/Down/bytes/star/$error/num/$num/error/$error/all/$all";

        $this->success($info,$url);

    }


    public function bytess(){
        $data = M('DownAddress')->field('`id`,`url`,`site_id`,`bytes`')->where('bytes = "0"')->select();

        $i      = 0;
        $error  = 0;
        $all    = 0;
        $num    = 0;

        $count = count($data);
        for($i;$i<$count;$i++){
            $address    =   formatAddress($data[$i]['url'],$data[$i]['site_id']);
            $heaer      =   get_headers($address,1);
            if(is_array($heaer['Content-Length'])){
                $byte = $heaer['Content-Length'][1];
            }else{
                $byte = $heaer['Content-Length'];
            }

            if(!is_numeric($byte)){
                $error++;
                continue;
            }

            $list['bytes']  = $byte;
            $number = M('DownAddress')->where('id = '.$data[$i]['id'])->save($list);

            if($number>0){
                $num += $number;
            }else{
                $error++;
            }
        }

        header('Content-Type:text/html; charset=utf-8');

        $info = '一共'.$all.'条数据，成功更新了'.$num.'条数据,有'.$error.'条数据执行失败';

        echo $info;
    }


}