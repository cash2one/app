<?php
// +----------------------------------------------------------------------
// | 7230mobile动态访问控制类
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;

class P7230mobileController extends BaseController {
    /**
     * 筛选
     * @return void
     */
    public function filter(){
        //下载首页还是游戏首页
        $type = I('type');
        $type_allow = array('rj'=>'软件','danji'=>'单机','wangy'=>'网游');
        if(!array_key_exists($type, $type_allow)) return;
        $this->assign('type_name', $type_allow[$type]);
        //参数
        $params = I('params');
        if(empty($params)){
            $p = 1;
            $sort = 0;
        }else{
            $params = explode('_', $params);
            $p = array_pop($params);
            $sort = array_pop($params);
        }
        $this->assign('sort',$sort);

        //游戏还是软件
        if($type == 'rj'){
            $map['d.category_rootid'] =  2;
        }else{
            $map['d.category_rootid'] =  1;
            //单机还是网游
            $map['m.network'] = $type == 'danji' ? 1 : 2;
        }


        //排序
        $sorts = array('view', 'hits_today', 'update_time');
        //判断手机操作系统
        $sys = get_device_type(true);
        if($sys == 'Android'){
            $where = ' And m.system=1';
        }else if($sys == 'iPhone'){
            $where = ' And m.system=2';
        }else{
            $where = '';
        }
        foreach ($sorts as $k=>$s) {
            //获取子查询语句
            M('Down')->alias('d') ->join('INNER JOIN __DOWN_DMAIN__ as m ON m.id = d.id AND d.status=1'.$where)->group('d.id')->field('d.id,d.title,d.smallimg,d.abet,d.view,d.description,d.category_id,m.licence,m.size');
            //排序
            M('Down')->order('d.' . $s. ' DESC');
            //获取子查询语句
            $subQuery  = M('Down')->where($map)->buildSql();
            //计算总数
            $count = M()->query("select count(id) as count from $subQuery sub limit 0,1");
            $count = $count[0]['count'];
            //分页获取数据
            $row = 10;
            if (!is_numeric($p) || $p<0 ) $p = 1;
            if ($p > $count ) $p = $count; //容错处理
            $_GET['p'] = $p; //设置P参数让分页类获取
            $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
            $lists = M()->query("$subQuery limit $lr,$row") ;
            //var_dump($subQuery);exit();
            $this->assign($s,$lists);
            //分页路径
            $path = '/'. $type .'/'.$k.'_{page}.html';
            //分页
            $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last','尾页');
            $Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
            $show       = $Page->show();// 分页显示输出
            $this->assign('page_'.$k,$show);// 赋值分页输出
        }



        //SEO 按照指定规则
        $this->assign("SEO",WidgetSEO(array('index'), '7230mobile'));

        //模板选择
        $this->display(T('Down@7230mobile/Filter/index'));
    }


    /**
     * 分类
     * @return void
     */
    public function downCategory(){
        $cate = I('cate');
        if (!is_numeric($cate)) $this->error('页面不存在！');
        //分类信息查询
        $info = D('DownCategory')->info($cate);
        if (!$info) $this->error('页面不存在！');
        $this->assign('info', $info);
        //参数
        $params = I('params');
        if(empty($params)){
            $p = 1;
            $sort = 0;
        }else{
            $params = explode('_', $params);
            $p = array_pop($params);
            $sort = array_pop($params);
        }
        $this->assign('sort',$sort);
        //分类条件
        $map['d.category_id'] =  $cate;
        //排序
        $sorts = array('update_time', 'view');
        //判断手机操作系统
        $sys = get_device_type(true);
        if($sys == 'Android'){
            $where = ' And m.system=1';
        }else if($sys == 'iPhone'){
            $where = ' And m.system=2';
        }else{
            $where = '';
        }
        foreach ($sorts as $k=>$s) {
            //获取子查询语句
            M('Down')->alias('d') ->join('INNER JOIN __DOWN_DMAIN__ as m ON m.id = d.id AND d.status=1'.$where)->field('d.id,d.title,d.smallimg,d.abet,d.view,d.description,d.category_id,m.licence,m.size');
            //排序
            M('Down')->order('d.' . $s. ' DESC');
            //获取子查询语句
            $subQuery  = M('Down')->where($map)->buildSql();
            //计算总数
            $count = M()->query("select count(id) as count from $subQuery sub limit 0,1");
            $count = $count[0]['count'];
            //分页获取数据
            $row = 10;
            if (!is_numeric($p) || $p<0 ) $p = 1;
            if ($p > $count ) $p = $count; //容错处理
            $_GET['p'] = $p; //设置P参数让分页类获取
            $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
            $lists = M()->query("$subQuery limit $lr,$row") ;
            //var_dump($subQuery);exit();
            $this->assign($s,$lists);
            //分页路径
            $path = '/cat'.$cate.'/'.$k.'_{page}.html';
            //分页
            $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last','尾页');
            $Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
            $show       = $Page->show();// 分页显示输出
            $this->assign('page_'.$k,$show);// 赋值分页输出
        }

        //SEO 按照指定规则
        $title = $info['title'];
        $seo = array(
            'title'=>'手机'.$title.'游戏排行榜 - 7230手游',
            'keywords'=>'手机游戏排行榜,手机'.$title.'游戏,手机'.$title.'游戏排行榜',
            'description'=>'7230手游网为您提供热门手机'.$title.'游戏下载,最好玩的手机'.$title.'游戏排行榜',
        );
        $this->assign("SEO",$seo);

        //模板选择
        $this->display(T('Down@7230mobile/Category/index'));
    }

    /*******************************************手机版二次改版**********************************************/
    /**
     * 作者:肖书成
     * 时间:2015/10/28
     * 描述:标签页
     */
    public function tagss(){
        //接收参数，查询标签
        $name   =   remove_xss(I('name'));
        $info   =   M('Tags')->field('id,name,title,pid,list_row,meta_title,keywords,description,img')->where("status = 1 AND `name` = '$name' AND display = 1")->find();

        //如果不存在，则跳转到404
        if(empty($info)){
            $this->_empty();
        }

        //列表数据查询
        $limit  =   (int)$info['list_row']>0?$info['list_row']:10;
        $lists  =   M('TagsMap')->alias('a')->field('b.id,b.title,b.description,b.view,b.smallimg,c.size,d.title cate')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')
            ->join('__DOWN_CATEGORY__ d ON b.category_id = d.id')->where('a.tid = '.$info['id'].' AND a.type ="down" AND b.status = 1 AND b.pid = 0')->order('a.sort DESC,b.level DESC,b.update_time DESC')->limit($limit)->select();

        //同类标签
        $sameTag    =  array();
        $sameLists  =   array();
        if(!empty($info['pid'])){
            $sameTag    = M('Tags')->where('id='.$info['pid'])->getField('title');
            $count      = M('Tags')->where('status = 1 AND pid ='.$info['pid'].' AND id != '.$info['id'])->count('id');
            $star       = rand(0,$count-11);
            $star<0?$star=0:'';
            $sameLists  = M('Tags')->field('name,title')->where('status = 1 AND pid ='.$info['pid'].' AND id != '.$info['id'])->limit($star,10)->select();
        }

        //SEO
        $SEO['title']       =   $info['meta_title'];
        $SEO['keywords']    =   $info['keywords'];
        $SEO['description'] =   $info['description'];

        //数据赋值
        $this->assign(array(
            'SEO'       =>  $SEO,
            'info'      =>  $info,
            'lists'     =>  $lists,
            'sameTag'   =>  $sameTag,
            'sameLists' =>  $sameLists,
            'url'       => C('MOBILE_STATIC_URL')
        ));

        $this->display(T('Down@7230mobile/Tag/tags'));
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/29
     * 描述:下载分类列表
     */
    public function downCate(){
        //接收参数
        $cate   =   (int)I('cate');

        //数据异常则跳转到404
        if($cate<1){
            $this->_empty();
        }

        //查找分类详情。
        $info   =   M('DownCategory')->field('id,name,title,pid,meta_title,keywords,description')->where('status = 1 AND id = '.$cate)->find();
        if(empty($info) || !in_array($info['pid'],array('1','2'))){
            $this->_empty();
        }

        $lists  =   M('Down')->alias('a')->field('a.id,a.title,a.smallimg,a.description,a.view,b.size')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')
            ->where('a.status = 1 AND a.pid = 0 AND a.category_id ='.$info['id'])->order('a.update_time DESC')->limit(10)->select();

        $info['pid']=='1'?$type = 'yx':$type = 'rj';

        //SEO
        $SEO['title']       = $info['meta_title']?$info['meta_title']:('最新手机'.$info['title'].'下载大全 - 7230手游网');
        $SEO['keywords']    = $info['keywords']?$info['keywords']:'好玩的手机游戏,'.$info['title'].'游戏,'.$info['title'].'游戏排行榜下载';
        $SEO['description'] = $info['description']?$info['description']:'7230手游网提供好玩的手机'.$info['title'].'游戏排行榜,最新手机'.$info['title'].'游戏下载大全';

        $this->assign(array(
            'SEO'   =>  $SEO,
            'info'  =>  $info,
            'lists' =>  $lists
        ));

        $this->display(T('Down@7230mobile/Category/index'));
    }


    /**
     * 作者:肖书成
     * 日期:2015/10/29
     * 描述:Ajax处理下载列表数据
     * @param $lists
     * @return mixed
     */
    private function m2_ajax_lists($lists){
        //分类
        $cate   =   S('down_cate');
        if(empty($cate)){
            $cate   =   M('DownCategory')->where('pid = 1 or pid = 2')->getField('id,title');
            S('down_cate',$cate,3600);
        }

        //列表数据处理
        foreach($lists as &$v){
            $v['smallimg']  =   get_cover($v['smallimg'],'path');
            $v['url']       =   staticUrlMobile('detail',$v['id'],'Down');
            $v['size']      =   format_size($v['size']);
            $v['cate']      =   $cate[$v['category_id']];
        }
        unset($v);

        //结果输出
        $this->API_result($lists);
        exit;
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/28
     * 描述:标签详情页的加载更多Ajax
     */
    public function API_tags(){
        //接受参数
        $key        =       I('key');
        $star       =       I('star');


        //验证参数
        if(!is_numeric($key) || !is_numeric($star) || (int)$key<=0 || (int)$star<0){
            $this->API_false();
        }


        //列表数据查询
        $lists  =   M('TagsMap')->alias('a')->field('b.id,b.title,b.category_id,b.description,b.view,b.smallimg,c.size')->join('__DOWN__ b ON a.did = b.id')->join('__DOWN_DMAIN__ c ON b.id = c.id')
            ->where('a.tid = '.$key.' AND a.type ="down" AND b.status = 1 AND b.pid = 0')->order('a.sort DESC,b.level DESC,b.update_time DESC')->limit($star,10)->select();

        //结果处理
        $this->m2_ajax_lists($lists);
    }

    /**
     * 作者:肖书成
     * 时间:2015/11/5
     * 描述:下载分类点击加载更多
     */
    public function API_down_cate()
    {
        //接受参数
        $cate = I('cate');
        $star = I('star');

        //验证参数
        if (!is_numeric($cate) || !is_numeric($star) || (int)$cate <= 2 || (int)$star < 0) {
            $this->API_false();
        }


        //列表数据查询
        $lists = M('Down')->alias('a')->field('a.id,a.title,a.category_id,a.smallimg,a.description,a.view,b.size')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')
            ->where('a.category_id =' . $cate.' AND a.status = 1 AND a.pid = 0')->order('a.update_time DESC')->limit($star, 10)->select();

        //结果处理
        $this->m2_ajax_lists($lists);
    }



    /**
     * 作者:肖书成
     * 时间:2015/10/28
     * 描述:为单机、网游、软件 三个特殊页面提供的API接口（点击加载更多）。
     */
    public function API_down(){
        //接受参数
        $cate       =       I('cate');
        $net        =       I('net');                   //联网（区分单机、网游）
        $pos        =       I('pos')=='1'?1:false;      //推荐
        $star       =       I('star');

        //验证参数
        if(!in_array($cate,array('1','2')) || (int)$star<10 || !in_array($net,array('false','1','2'))){
            $this->API_false();
        }

        //列表数据查询
        $where      =   "a.category_rootid = $cate AND a.status = 1 AND a.pid = 0 ";
        $cate=='1'?$where  .=   "AND b.network = $net ":'';
        $pos?$where .=  "AND a.position & 1":'';

        $lists      =   M('Down')->alias('a')->field('a.id,a.title,a.category_id,a.smallimg,a.description,a.view,b.size')
            ->join('__DOWN_DMAIN__ b ON a.id = b.id')
            ->where($where)->order('a.update_time DESC')->limit($star,10)->select();

        //结果处理
        $this->m2_ajax_lists($lists);
    }

    /*******************************************手机版二次改版 END**********************************************/

    /**
     * 标签
     * @return void
     */
    public function tags(){
        $name =  strip_tags(I('name'));
        $info = M('Tags')->where('status=1 AND name="%s"', $name)->field('id,title,meta_title,keywords,description')->find();
        $id = $info['id'];
        if (!is_numeric($id)) $this->error('页面不存在！');
        //参数
        $params =  strip_tags(I('params'));
        if(empty($params)){
            $p = 1;
            $sort = 0;
        }else{
            $params = explode('_', $params);
            $p = array_pop($params);
            $sort = array_pop($params);
        }
        $this->assign('sort',$sort);

        //排序
        $sorts = array('update_time', 'view');
        //判断手机操作系统
        $sys = get_device_type(true);
        if($sys == 'Android'){
            $where = ' And m.system=1';
        }else if($sys == 'iPhone'){
            $where = ' And m.system=2';
        }else{
            $where = '';
        }

        foreach ($sorts as $k=>$s) {
            //计算总数
            $count = M()->query('SELECT count(d.id) AS count FROM onethink_down as d INNER JOIN onethink_down_dmain as m ON m.id = d.id where d.status=1'.$where.' AND d.id in ( select did from  onethink_tags_map where type="down" AND tid='.$id.' ) limit 0,1');
            $count = $count[0]['count'];
            //分页获取数据
            $row = 10;
            if (!is_numeric($p) || $p<0 ) $p = 1;
            if ($p > $count ) $p = $count; //容错处理
            $_GET['p'] = $p; //设置P参数让分页类获取
            $lr = (($p-1)*$row)>0 ? (($p-1)*$row) : 0;
            $lists = M()->query('SELECT d.id,d.title,d.smallimg,d.abet,d.view,d.description,d.category_id,m.licence,m.size,m.iconcorner FROM onethink_down d INNER JOIN onethink_down_dmain as m ON m.id = d.id AND d.status=1'.$where.' where d.id in ( select did from  onethink_tags_map where type="down" AND tid='.$id.' )  ORDER BY d.' . $s. ' DESC LIMIT '.$lr.','.$row);
            $this->assign($s,$lists);
            //分页路径
            $path = '/tag/'.$name.'/'.$k.'_{page}.html';
            //分页
            $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $Page->setConfig('first','首页');
            $Page->setConfig('last','尾页');
            $Page->setConfig('theme','%UP_PAGE%  %DOWN_PAGE%');
            $show       = $Page->show();// 分页显示输出
            $this->assign('page_'.$k,$show);// 赋值分页输出
        }


        $title = $info['title'];
        //Edit By JeffreyLau;Date:2015-7-21 16:01:29
        $seo_title = empty($info['meta_title']) ? '手机'.$title.'游戏排行榜 - 7230手游网' : $info['meta_title'];
        $seo_keywords = empty($info['keywords']) ? '手机游戏排行榜,手机'.$title.'游戏,手机'.$title.'游戏排行榜' : $info['keywords'];
        $seo_description = empty($info['description']) ? '7230手游网为您提供热门手机'.$title.'游戏下载,最好玩的手机'.$title.'游戏排行榜' : $info['description'];
        //SEO 按照指定规则

        $seo = array(
            'title'=>$seo_title,
            'keywords'=>$seo_keywords,
            'description'=>$seo_description,
        );
        $this->assign("SEO",$seo);
        //模板选择
        $this->display(T('Down@7230mobile/Tag/index'));
    }

    /**
     * 搜索结果
     * @return void
     */
    public function search(){
        $keyword =  remove_xss(strip_tags(I('keyword')));
        $type =  remove_xss(I('type'));
        $type = ucfirst(strtolower($type));
        $allow = array('Down','Document','Package');
        $this->assign("system",get_device_type());//手机系统判断
        //if (!$keyword) $this->error('请输入关键词！');
        if(!empty($keyword) && !empty($type) && in_array($type, $allow)){
            //结果页面
            $this->assign('keyword',$keyword);// 赋值关键词
            $this->assign('type',$type);// 赋值类型

            // //下载数据匹配
            // $map = array();
            // $map['title'] = array('like','%'. $keyword .'%');
            // $down = M('Down')->where($map)->limit(0,10)->order('id DESC')->select();
            // //var_dump($down);
            // if($down){
            //     $this->assign('down', $down);
            // }

            // //文章数据匹配
            // $map = array();
            // $map['title'] = array('like','%'. $keyword .'%');
            // $document = M('Document')->where($map)->limit(0,5)->order('id DESC')->select();
            // //var_dump($document);
            // if($document){
            //     $this->assign('document', $document);
            // }


            //分页获取数据
            $where = array(
                'map' => array('title' => array('like','%'. $keyword .'%'))
            );
            $row = 10;
            $count  = D($type)->listsWhereCount($where);// 查询满足要求的总记录数
            $this->assign('count',$count);

            $p = intval(I('p'));
            if (!is_numeric($p) || $p<0 ) $p = 1;
            if ($p > $count ) $p = $count; //容错处理

            $lists = D($type)->page($p, $row)->listsWhere($where, true);
            // 赋值数据集
            $this->assign('lists',$lists);

            $path = '/search.html?keyword='.$keyword.'&type='.$type.'&p={page}';
            $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类 传入总记录数和每页显示的记录数
            $Page->setConfig('first','首页');
            $Page->setConfig('end','尾页');
            $Page->setConfig('prev',"上一页");
            $Page->setConfig('next','下一页');
            $Page->setConfig('theme','%UP_PAGE% %DOWN_PAGE%');
            $show       = $Page->show();// 分页显示输出
            $this->assign('page',$show);// 赋值分页输出

            //SEO
            $seo['title'] =C('WEB_SITE_TITLE') .'-'. $keyword . '搜索结果';
            $seo['keywords'] = C('WEB_SITE_KEYWORD') .','. $keyword;
            $seo['description'] =C('WEB_SITE_DESCRIPTION').' '. $keyword. '搜索结果';
            $this->assign("SEO",$seo);

            //var_dump($lists);die();
            //模板选择
            $this->display(T('Home@7230mobile/Search/searched'));
        }else{
            //SEO
            $this->assign("SEO",WidgetSEO(array('index'), '7230mobile'));
            //初始页面
            $this->display(T('Home@7230mobile/Search/search'));
        }



    }

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
     * 赞
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_Praise($id = 0, $model = ''){
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        if(!is_numeric($id) || empty($model)) return;

        $field = array('Document'=>'ding','Down'=>'abet');
        if(!array_key_exists($model, $field)) return;
        $m = M($model);
        $m->where('id='. $id)->setInc($field[$model]);
        $rs = $this->API_GetPraise($id, $model, true);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 获取赞数
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @param boolean $fuc 是否是函数调用
     * @return void
     */
    public function API_GetPraise($id = 0, $model = '', $fuc = false){
        if(!$fuc){
            $this->API_init();
            $callback = I('callback');
        }
        if(!is_numeric($id) || empty($model)) return;

        $field = array('Document'=>'ding','Down'=>'abet');
        if(!array_key_exists($model, $field)) return;
        $m = M($model);
        $rs = $m->where('id='. $id)->getField($field[$model]);
        if($fuc){
            return $rs;
        }else{
            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
        }
    }

    /**
     * 批量获取赞数目
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function API_BatchGetPraise($b = array()){
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        if(empty($b)) return;

        $rs =array();
        foreach ($b as $id => $model) {
            $num = $this->API_GetPraise($id, $model, true);
            $rs[$id] = $num;
        }
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }


//    /**
//     * 评论列表接口
//     * @param integer $id 数据ID
//     * @param string $model 模型名
//     * @return void
//     */
//    public function API_comment($id, $model){
//        $this->API_init();
//        $callback = I('callback');
//
//        if(!is_numeric($id) || empty($model)) return;
//
//        $m = M('Comment');
//        $map =array();
//        $map['document_id'] = intval($id);
//        $map['enabled'] = 1;
//        $map['type'] = strip_tags($model);
//
//        $rs = $m->where($map)->field('id,uname,message,add_time')->order('id ASC')->limit(0,5)->select();
//        if($fuc){
//            return $rs;
//        }else{
//            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
//        }
//    }

    /**
     * 显示评论页面
     * @param integer $id 数据ID
     * @param string $model 模型名
     * @return void
     */
    public function commentIndex($id, $model){
        if(!is_numeric($id) || empty($model)) return;
        $m = M('Comment');
        $map =array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);

        $count = $m->where($map)->count();
        $row = 20;
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理
        $lists = $m->where($map)->field('id,uname,message,add_time')->page($p, $row)->order('id ASC')->select();
        $path = C('STATIC_URL') . '/P7230/commentIndex/id/'. $id .'/model/'. $model .'/p/{page}' ;
        $Page       = new \Think\Page($count, $row, '', false, $path);// 实例化分页类
        // $Page->setConfig('first','首页');
        // $Page->setConfig('end','尾页');
        // $Page->setConfig('prev',"上一页");
        // $Page->setConfig('next','下一页');
        // $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');
        $show   = $Page->show();// 分页显示输出
        $this->assign('lists',$lists);// 赋值数据集
        $this->assign('count',$count);
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('id',$id);
        $this->assign('model',$model);
        $this->assign('sp',($p-1)*$row);

        //查找数据
        $d = M(ucfirst(strtolower($model)));
        $info = $d->getById($id);
        $this->assign('info',$info);// 赋值分页输出

        //SEO
        $this->assign("SEO",WidgetSEO(array('detail',ucfirst(strtolower($model)), $id), '7230'));
        //模板选择
        $this->display(T('Home@7230/Comment/index'));
    }


    /**
     * 提交评论跨域代理
     * @return void
     */
    public function API_commentSubPro(){

    }

    /**
     * 提交评论
     * @return void
     */
    public function API_commentSub(){
        $this->API_init();
        $callback = I('callback');

        $id = I('id');
        $model = I('model');
        if(!is_numeric($id) || empty($model)) return;

        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 描述：获取更多攻略信息
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function ajaxCourse()
    {
        $callback = I('callback');
        $start=I('get.page')*5;
        $type=I('get.type');//单机1，网游2
        $id = I('id');
        if(is_numeric($id))
        {
            if($type =="1")
            {
                $map['b.rootid'] = array('in','2,70');   //由于以前缺陷，所以这一只有写死 5表示铃声，不知道铃声是否算软件
            }
            else
            {
                $map['b.rootid'] = array('not in','38,2,5,70'); //由于以前缺陷，所以这一只有写死
            }
            $map['a.status'] = 1;
            $map['b.status'] = 1;
            $map['c.company_id'] = $id;
            $rs =  M('Down')->alias('a')->join('__DOWN_DMAIN__ c ON c.id=a.id')->join('__DOWN_CATEGORY__ b on b.id=a.category_id')->field('a.id as id')->where($map)->select();
            $ids = array();
            if(is_array($rs) && !empty($rs))
            {
                foreach($rs as $val)
                {
                    $ids[] = $val['id'];
                }
            }
            if(!empty($ids))
            {
                $where['did'] = array('in',$ids);
                $where['type'] = 'down';
                $tids = M('Product_tags_map')->where($where)->getField('tid',true);
                if(!empty($tids))
                {
                    unset($where);
                    $where['b.tid'] = array('in',$tids);
                    $where['b.type'] = 'document';
                    $where['a.status'] = '1';
                    $list =  M('Document')->alias('a')->join('__PRODUCT_TAGS_MAP__ b on b.did=a.id')->field('a.id as id,a.title as title')->order('a.update_time desc')->where($where)->limit("$start,5")->select();
                }
                if(!empty($list))
                {
                    foreach($list as &$val)
                    {
                        $val['url'] = staticUrlMobile('detail', $val['id'],'Document');
                    }
                }
            }
        }
        echo  $callback."(".json_encode($list).")";
    }

    public function ajaxCsSoft()
    {
        $callback = I('callback');
        $start=I('get.page')*5;
        $type=I('get.type');//游戏1，软件2
        $id = I('id');
        $system = I('system');
        if(is_numeric($id))
        {
            if($type =="1")
            {
                $map['b.rootid'] = array('in','2,70');   //由于以前缺陷，所以这一只有写死 5表示铃声，不知道铃声是否算软件
            }
            else
            {
                $map['b.rootid'] = array('not in','38,2,5,70'); //由于以前缺陷，所以这一只有写死
            }
            $map['a.status'] = 1;
            $map['b.status'] = 1;
            $map['c.company_id'] = $id;
            if($system) $map['c.system'] = $system;
            //获取相关厂商的游戏字段
            $list =  M('Down')->alias('a')->join('__DOWN_DMAIN__ c ON c.id=a.id')->join('__DOWN_CATEGORY__ b on b.id=a.category_id')->field('a.id as id,a.title as title,a.smallimg as smallimg,c.size as size')->where($map)->limit("$start,5")->order('a.update_time desc')->select();
            if(!empty($list))
            {
                foreach($list as &$val)
                {
                    $val['url'] = staticUrlMobile('detail', $val['id'],'Document');
                    $val['path'] = get_cover($val['smallimg'],'path');
                    $val['cate'] = getCateName($val['id'],'Down');
                    $val['lang'] = getLanguage($val['id']);
                    $val['size'] = number_format($val['size']/1024,1);
                }
            }
        }
        echo  $callback."(".json_encode($list).")";
    }

    /**
     * 作者:肖书成
     * 描述:评论查询
     */
    public function API_comment(){
        //参数接收
        $id     =   (int)I('data_id');
        $type   =   I('data_type');
        $star   =   I('star')?(int)I('star'):0;

        //参数验证
        if($id < 1 || !in_array($type,array('document','down','package')) || $star < 0){
            $this->API_false();
        }

        //列表数据查询
        $count  =   M('comment')->field('uname,message,add_time')->where("document_id = $id AND type = '$type' AND enabled = 1")->count('id');

        if($count == '0'){
            $this->API_result(null);exit;
        }
        if($star > (int)$count) $this->API_false();

        $lists  =   M('comment')->field('uname,message,add_time')->where("document_id = $id AND type = '$type' AND enabled = 1")->order('id DESC')->limit($star,5)->select();
        if($lists){
            foreach($lists as $k=>$v){
                $lists[$k]['add_time'] = date('Y-m-d H:i',$v['add_time']);
            }
        }

        $result =   array('count'=>$count,'lists'=>$lists);

        //结果输出
        $this->API_result($result);
    }

    /**
     * 作者:肖书成
     * 描述:评论插入
     */
    public function API_comment_add(){
        $this->API_init();

        //参数接收
        $id     =   (int)I('data_id');

        $type   =   I('data_type');
        $info   =   remove_xss(I('pl_info'));
        $info   =   preg_replace("/\s|　|'|\"|%|_/","",$info);
        $ip     =   get_client_ip();
        $sip    =   S('7230IP');
        $md5ip  =   md5($ip);

        //参数验证
        if(empty($info)){
            $this->API_false();
        }

        if($md5ip == $sip){
            $this->API_result('IP');
            exit;
        }
        if($id < 1 || !in_array($type,array('document','down','package'))){
            $this->API_false();
        }

        //参数处理
        $data['type'] = $type;
        $data['uid']  = 0;
        $data['uname']= '游客';
        $data['document_id']    =   $id;
        $data['message']        =   $info;
        $data['add_time']       =   time();
        $data['at_uid']         =   0;
        $data['votes']          =   0;
        $data['enabled']        =   0;

        //插入数据库
        $result     = M('comment')->add($data);

        //结果处理
        S('7230IP',$md5ip,120);
        $this->API_result($result);
    }

}