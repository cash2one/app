<?php
// +----------------------------------------------------------------------
// | 描述:首页模块widget类，对一些特殊逻辑处理
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-12 上午9:39    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Home\Widget;


use Common\Controller\WidgetController;

class Jf96uWidget extends WidgetController{

    /**
     * 描述：新标签获取路径回调函数
     * @param $params
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function parseModuleUrl($params)
    {
        if(isset($params['category_id'])){
            $model = $params['module']['1'];
            $where['id'] = $params['id'];
            $rs = M($model)->field('path_detail')->where($where)->find();
            $path = $rs['path_detail'];
            if(empty($path))
            $path = $params['path_detail'];
            $DIR = C(strtoupper($model) . '_DIR');
            if($DIR){
                $DIR =substr($DIR, -1) != '/' ? $DIR . '/' : $DIR;
                $path = $DIR . $path;
            }
            $path = str_replace('//', '/', $path);
            if(strstr($path,'{create_time|date=Ymd}'))
            {
                $value = date('Ymd',$params['create_time']);
               // $path = str_replace('{create_time|date=Ymd}', $value, $path);
            }
            if(strstr($path,'{Year}/{Month}/{Day}/{Time}')) //qbaobei路径
            {
                $str_date = date('Y',$params['create_time']) . '/'.date('m',$params['create_time']) . '/'.date('d',$params['create_time']) . '/'.date('His',$params['create_time']);
               // $path = str_replace('{Year}/{Month}/{Day}/{Time}', $str_date, $path);
            }
            if(strstr($path,'{Y}{M}')) //96u路径
            {
                $str_date96u = date('Y',$params['create_time']) . date('m',$params['create_time']);
               // $path = str_replace('{Y}{M}', $str_date96u, $path);
            }
            // 文档
            $url = str_replace(array('{id}','{create_time|date=Ymd}','{Year}/{Month}/{Day}/{Time}','{Y}{M}'), array($params['id'], $value,$str_date,$str_date96u), $path).'.html';
            $url = str_replace('index.html','',$url);
        }else{
            $model = $params['module']['1'];
            // 列表
            $filename = strtolower($params['path_lists_index']);
            $path = dirname($params['path_lists']).'/';
            $DIR = C(strtoupper($model) . '_DIR');
            if($DIR){
                $DIR =substr($DIR, -1) != '/' ? $DIR . '/' : $DIR;
                $path = $DIR . $path;
            }
            $path = str_replace('//', '/', $path);
            if($filename == 'index' || !$filename){
                $url = $path;
            }else{
                $url = $path.$filename.'.html';
            }
        }

        if($url){
            return '/'.$url;
        }
    }

    /**
     * 描述：获取seo信息
     * @param $type
     * @param null $module
     * @param null $id
     * @return array
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function SEO($type, $module = null, $id = null){
        $seo = array();
        switch ($type) {
            case 'index':
                $seo['title'] =C('WEB_SITE_TITLE');
                $seo['keywords'] = C('WEB_SITE_KEYWORD');
                $seo['description'] =C('WEB_SITE_DESCRIPTION');
                return $seo;
                break;
            case 'moduleindex':
                if(empty($module)) return;
                $module = strtoupper($module);
                $seo['title'] =C(''.$module.'_DEFAULT_TITLE');
                $seo['keywords'] = C(''.$module.'_DEFAULT_KEY');
                $seo['description'] =C(''.$module.'_DEFAULT_DESCRIPTION');
                return $seo;
                break;
            case 'special':
                $id=empty($id)?'1':$id;
                $cate=D('StaticPage')->where("id='$id'")->find();
                $seo['title'] =empty($cate['title'])?$cate['title']:$cate['title'];
                $seo['keywords'] = empty($cate['keywords'])?'':$cate['keywords'];
                $seo['description'] =empty($cate['description'])?'':$cate['description'];
                return $seo;
                break;
            case 'category':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = strtoupper($module);
                $cate_name = array(
                    'DOCUMENT' => 'Category',
                    'PACKAGE' => 'PackageCategory',
                    'DOWN' => 'DownCategory',
                    'GALLERY'=>'GalleryCategory',
                );
                $cate=D($cate_name[$module])->where("id='$id'")->find();
                $seo['title'] =empty($cate['meta_title'])?$cate['title']:$cate['meta_title'];
                if(C('SEO_STRING')){
                    $t = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode('_', $t);
                }
                $seo['keywords'] = empty($cate['keywords'])?C(''.$module.'_DEFAULT_KEY'):$cate['keywords'];
                $seo['description'] =empty($cate['description'])?C(''.$module.'_DEFAULT_KEY'):$cate['description'];
                return $seo;
                break;
            case 'detail':
                $id=empty($id)?'1':$id;
                if(empty($module)) return;
                $module = ucfirst(strtolower($module));
                $detail = D($module)->detail($id);

                //标题
                if($module=='Down'){
                    //下载的规则
                    //1、seotitle+版本号
                    //2、副标题|主标题+下载+版本号
                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title'];
                    }else{
                        $title = $detail['sub_title'] .'|'. $detail['title'] . '下载';
                    }
                }else{
                    //其他的规则
                    //1、seo title
                    //2、主标题
                    //$cate= M('Category')->where(array('id'=>$detail['category_id']))->getField("title");

                    if(!empty($detail['seo_title'])){
                        $title = $detail['seo_title'];
                    }else{

                        $title = $detail['title'];
                    }
                }
                //标题需要加前后缀
                if(C('SEO_STRING')){
                    $t = array();
                    $t[abs((int)C('SEO_PRE_SUF') - 1)] = $title;
                    $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                    ksort($t);
                    $seo['title'] = implode('_', $t);
                }else{
                    $seo['title'] = $title;
                }

                //关键词
                if(empty($detail['seo_keywords'])){
                    if($module=='Document'){
                        //文章 标签
                        $tag_ids = M('TagsMap')->where('did='.$id.' AND type="document"')->getField('tid', true);
                        if(empty($tag_ids)){
                            $seo['keywords'] = $detail['title'];
                        }else{
                            $tags = M('Tags')->where(array('id'=>array('in', $tag_ids)))->getField('title', true);
                            $seo['keywords'] = empty($tags) ? $detail['title'] : implode(',', $tags);
                        }
                    }else{
                        //其他 主标题+副标题
                        $seo['keywords'] = empty($detail['sub_title'])
                            ? $detail['title']
                            : $detail['title'] . ',' . $detail['sub_title'];
                    }
                }else{
                    $seo['keywords'] = $detail['seo_keywords'];
                }

                //描述分模块处理
                if(empty($detail['seo_description'])){
                    $des = array('Document'=>'description','Down'=>'conductor','Package'=>'content');
                    if(empty($detail[$des[$module]])){
                        $seo['description'] = substr(str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail['content']))),0,500);
                    }else{
                        $seo['description'] = str_replace('"', '', str_replace(PHP_EOL, '', strip_tags($detail[$des[$module]])));
                    }
                }else{
                    $seo['description'] = strip_tags($detail['seo_description']);
                }
                $seo['description']=ltrim($seo['description']);
                $seo['description']=rtrim($seo['description']);
                return $seo;
                break;
        }
    }
    /*-----------------------------------------------------谭坚 widget------------------------------------------------*/
    /**
     * 描述：网站底部关于我们页面
     * @param $template
     * @param $id
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function about()
    {
        $id = I('id');
        if(empty($id)) return;
        $where = array();
        $where['id'] = $id;
        $content = M('static_page')->field('custom_content,name')->where($where)->find();
        $this->assign(array(
            'id'=>$id,
            'name' => $content['name'],
            'content'  =>$content['custom_content'],    //头条
            'SEO'       =>WidgetSEO(array('special',null,$id))
        ));
        $this->display(T('Home@jf96u/Widget/about'));
    }

    /**
     * 描述：网站地图
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function sitemap(){
        $id = I('id');
        if(empty($id)) return;
        $where = array();
        $where['id'] = $id;
        $content = M('static_page')->field('custom_content,name')->where($where)->find();
        unset($where);
        $this->assign('name',$content['name']);
        $where['status'] = 1;
        $news  = D('Document')->field('id,title,create_time')->where($where)->order('create_time desc')->limit('100')->select();// 查询满足要求的总记录数
        $this->assign("news",$news);
        $down  = D('Down')->field('id,title,create_time')->where($where)->order('update_time desc')->limit('100')->select();// 查询满足要求的总记录数
        $this->assign("down",$down);
        unset($where);
        $this->assign('id',$id);
        $this->assign("SEO",WidgetSEO(array('special',null,$id)));
        $this->display(T('Home@jf96u/Widget/sitemap'));
    }

    /*-----------------------------------------------------谭坚 widget END--------------------------------------------*/

    /**
     * 作者:ganweili
     * 时间:2015/11/12
     * 描述:新手游首页控制器
     */

    public function newgame() {


        //新手游推荐
        $new=M()->table('onethink_down as a,onethink_down_category as b ,onethink_down_dmain as c')
            ->field('a.id,a.previewimg,a.model_id,a.category_id,a.title,a.smallimg,a.description,a.view,b.title as categoryname,c.size,c.game_type,c.soft_socre')
            ->where("   a. home_position & 16 and a.id =c.id  and b.id = a.category_id  and a.status =1")
            ->order('a.update_time   desc')
            ->limit('1')
            ->select();
        //为空
        if(empty($new)){
            $new=M()->table('onethink_down as a,onethink_down_category as b ,onethink_down_dmain as c')
                ->field('a.id,a.previewimg,a.model_id,a.category_id,a.title,a.smallimg,a.description,a.view,b.title as categoryname,c.size,c.game_type,c.soft_socre')
                ->where(" a.id =c.id  and b.id = a.category_id  and a.status =1")
                ->order('a.update_time   desc')
                ->limit('1')
                ->select();
        }

        //顶部新游推荐带图
        $topnew=M('down')->alias('a')->join('__DOWN___dmain b')
            ->field('a.id,a.category_id,a.title,a.smallimg,a.description,a.view,b.game_type')
            ->where('a.home_position & 32 and a.id=b.id and a.status =1  ')
            ->order('a.update_time desc')
            ->limit(7)
            ->select();
        //为空
        if(count($topnew)<7){
            $mi=7-count($topnew);
            $data=M('down')->alias('a')->join('__DOWN___dmain b')
                ->field('a.id,a.category_id,a.title,a.smallimg,a.description,a.view,b.game_type')
                ->where('a.id=b.id and a.status =1  ')
                ->order('a.update_time asc')
                ->limit($mi)
                ->select();
            if(empty($topnew)){
                $topnew=$data;
            }else{
                $topnew=array_merge($data,$topnew);
            }
        }

        //顶部新游不带图
        $topnewa=M('down')
            ->where(' home_position & 32  and  status=1  ')
            ->order(' update_time desc')
            ->limit(7,23)
            ->select();
        if(count($topnewa)<23){
            $mi=22-count($topnewa);
            $topnewa=M('down')
                ->where(' status=1   ')
                ->order(' update_time desc')
                ->limit(7,$mi)
                ->select();
            if(empty($topnewa)){
                $topnewa=$data;
            }else if(!empty($data)){
                $topnewa=array_merge($data,$topnewa);
            }
        }
        //新手游头条
        $tou=M('Document')->where('home_position & 32 and  status =1 ')
            ->limit(6)
            ->select();
        if($tou){
            $tou=M('Document')->where('status =1 ')
                ->limit(6)
                ->select();
        }




        $id = 72;
        $this->assign("SEO",WidgetSEO(array('special',null,$id)));
        $this->assign('cid',72);
        $this->assign('kaifu',$kaifu);
        $this->assign('kaice',$kaice);
        $this->assign('topnewa',$topnewa);
        $this->assign('topnew',$topnew);
        $this->assign('tou',$tou);
        $this->assign('new',$new);
        $this->display(T('Home@jf96u/Widget/newgame'));
    }

    /**
     * 作者:ganweili
     * 时间:2015/11/12
     * 描述:开服
     */

    public function kaifu(){
        $time=strtotime('today');
        $kaifu=M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server')
            ->where("a.category_id = 4 and b.start_time >= $time and a.status =1  ")
            ->order('start_time  asc')
            ->limit(9)
            ->select();
        if(count($kaifu)<9){
            $mi=9-count($kaifu);
            $data=M('package')->alias('a')
                ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
                ->field('a.* ,b.start_time,b.server')
                ->where("b.start_time<$time and a.category_id = 4 and a.status =1  ")
                ->order('start_time  asc')
                ->limit($mi)
                ->select();
            if(empty($kaifu)){
                $kaifu=$data;
            }else if(!empty($data)){
                $kaifu=array_merge($kaifu,$data);
            }
        }
        $this->assign('kaifu',$kaifu);
        $this->display(T('Home@jf96u/Widget/kaifu'));

    }


    /**
     * 作者:ganweili
     * 时间:2015/11/12
     * 描述:kaice
     */
    public function kaice()
    {

        $time = strtotime('today');
        $kaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server')
            ->where("a.category_id = 5 and b.start_time >= $time and a.status =1 ")
            ->order('start_time  asc')
            ->limit(9)
            ->select();
        if(count($kaice)<9){
            $mi=9-count($kaice);
            $data=M('package')->alias('a')
                ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
                ->field('a.* ,b.start_time,b.server')
                ->where("b.start_time<$time and a.category_id = 5 and a.status =1")
                ->order('start_time  asc')
                ->limit($mi)
                ->select();
            if(empty($kaice)){
                $kaice=$data;
            }else if(!empty($data)){
            $kaice=array_merge($kaice,$data);
            }
        }
        $this->assign('kaice', $kaice);
        $this->display(T('Home@jf96u/Widget/kaice'));

    }


    /**
     * 作者:ganweili
     * 时间:2015/11/12
     * 描述:开测页面控制器
     *
     */
    public function kaiceview(){

//当天的开测数据
        $nkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 5 and (  b.start_time < ". strtotime('today+1day') . "  and b.start_time >".strtotime('today'). " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($nkaice) ? $nkaice : $data['1'] = $nkaice;


//明天的开测数据
        $mkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 5 and (  b.start_time < ". strtotime('today+2day') . "  and b.start_time >".strtotime('today+1day'). " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($mkaice) ? $mkaice : $data['2'] = $mkaice;

//后天的开测数据
        $hkaice=M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 5 and (  b.start_time < ". strtotime('today+3day') . "  and b.start_time >".strtotime('today+2day'). " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($hkaice) ? $hkaice : $data['3'] = $hkaice;

        //连接数组获取所有已经被调用的ID
        foreach($data as $v){
            foreach($v as $k){
                $id.= $k['id'].',' ;
            }

        }
        $id=substr($id,0,-1);
        // 防止所有为空报错

        $id=empty($id)?$id=0:$id;

        //排除上面已经显示的  在调用以后的即将开测的
        $jkaice=M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 5  and a.id not in (".$id .") and b.start_time > ". strtotime('today')." and a.status=1")
            ->order('start_time  asc')
            ->limit(6)
            ->select();

// 已经开测
        $ykaice=M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 5  and a.id not in (".$id .") and b.start_time < ". strtotime('today')." and a.status=1")
            ->order('start_time  desc')
            ->limit(9)
            ->select();

        $this->assign('ykaice',$ykaice);

        $this->assign("SEO",WidgetSEO(array('special',null,73)));

        $this->assign('cid',73);//用户导航选中效果  add by tanjian  2015/12/21 17:07
        $this->assign('jkaice',$jkaice);
        $this->assign('hkaice',$hkaice);
        $this->assign('mkaice',$mkaice);
        $this->assign('nkaice',$nkaice);
        $this->display(T('Home@jf96u/Widget/kaiceview'));

    }


    /**
     * 作者:ganweili
     * 时间:2015/11/13
     * 描述:开服
     */


    public function kaifuview()
    {


//当天的开测数据
        $nkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id =4 and (  b.start_time < " . strtotime('today+1day') . "  and b.start_time >" . strtotime('today') . " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($nkaice) ? $nkaice : $data['1'] = $nkaice;

//明天的开测数据
        $mkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 4 and (  b.start_time < " . strtotime('today+2day') . "  and b.start_time >" . strtotime('today+1day') . " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($mkaice) ? $mkaice : $data['2'] = $mkaice;
//后天的开测数据
        $hkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 4 and (  b.start_time < " . strtotime('today+3day') . "  and b.start_time >" . strtotime('today+2day') . " ) and a.status =1 ")
            ->order('start_time  asc')
            ->limit(3)
            ->select();
        empty($hkaice) ? $hkaice : $data['3'] = $hkaice;


        //连接数组获取所有已经被调用的ID


        foreach ($data as $v) {
            foreach ($v as $k) {
                $id .= $k['id'] . ',';
            }

        }

        $id = substr($id, 0, -1);
        // 防止为空报错

        $id = empty($id) ? $id = 0 : $id;


        //排除上面已经显示的  在调用以后的即将开测的
        $jkaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id =4 and a.id not in (" . $id . ") and b.start_time > " . strtotime('today') . " and a.status=1")
            ->order('start_time  asc')
            ->limit(6)
            ->select();


// 已经开测
        $ykaice = M('package')->alias('a')
            ->join("LEFT JOIN __PACKAGE_PARTICLE__ b ON  a.id=b.id   ")
            ->field('a.* ,b.start_time,b.server ,b.game_type')
            ->where("a.category_id = 4 and a.id not in (" . $id . ") and b.start_time < " . strtotime('today') . " and a.status=1")
            ->order('start_time  desc')
            ->limit(9)
            ->select();

        $this->assign('ykaice', $ykaice);

        $this->assign("SEO",WidgetSEO(array('special',null,74)));
        $this->assign('jkaice', $jkaice);
        $this->assign('hkaice', $hkaice);
        $this->assign('mkaice', $mkaice);
        $this->assign('nkaice', $nkaice);
        $this->display(T('Home@jf96u/Widget/kaifuview'));

    }

    /**
     * 作者:ganweili
     * 时间:2015/11/14
     * 描述:游戏库
     */
    public function yxksx(){
        $where="a.type=2 and a.status=1 and  a.id=c.id  ";
        //获取get组装URL
        $uget=I('');
        $where=!is_numeric($uget['system'])?$where:$where."and c.system =".$uget['system'] ;
        $where=!is_numeric($uget['network']) ?$where:$where." and c.network=".$uget['network'] ;
        $where=!is_numeric($uget['type'])?$where:$where." and c.game_type=".$uget['type'];
        $where=!is_numeric($uget['image']) ?$where:$where." and c.game_image=".$uget['image'];
        $where=!is_numeric($uget['rank'])?$where:$where." and c.rank=".$uget['rank'];

        $count  = M('down')->where(" type=2 and status=1  and model_id=13")->count();// 查询满足要求的总记录数
        $row = 15;
        $p = intval(I('p'));
        if (!is_numeric($p) || $p<=0 ) $p = 1;
        if ($p > $count ) $p = $count; //容错处理
        //order
        if(empty($uget['order']) or $uget['order']>2){
            $or="a.id desc";
        }elseif($uget['order']==1){
            $or='a.view desc ';

        }elseif($uget['order']==2){
            $or="a.create_time desc";
        }
        $lists = M('down')->alias('a') ->join("__DOWN_DMAIN__ c")
            ->field("a.*,c.size,c.system,c.game_type,c.game_image")
            ->where($where)
            ->order($or)->page($p, $row)->select();
        $listscount = M('down')->alias('a') ->join("__DOWN_DMAIN__ c")
            ->field("a.*,c.size,c.system,c.game_type,c.game_image")
            ->where($where)
            ->count();
        $Page = new \Think\Page($listscount, $row);
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('last','尾页');
        $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        $show = $Page->show();// 分页显示输出
        $show=str_replace('/index.php?s=/Widget/index/theme/Jf96u/method/yxksx/t/jf96u/p','/yxksx',$show);
        $show=str_replace('/order/','_',$show);
        $show=str_replace('/system/','_',$show);
        $show=str_replace('/network/','_',$show);
        $show=str_replace('/type/','_',$show);
        $show=str_replace('/image/','_',$show);
        $show=str_replace('/rank/','_',$show);

        //筛选格式化
        $system= C(FIELD_DOWN_SYSTEM);
        $network=C(FIELD_DOWN_NETWORK);
        $game_type=C(FIELD_GAME_TYPE);
        $game_image=C(FIELD_GAME_IMAGE);
        $rank=C(FIELD_DOWN_RANK);
        $order= array (
            "1"  => '人气最高',
            "2" => '最新入库',

        );
        //URL 生成
        $orurl=editurl($order,order,1,'1');

        $sysurl=editurl($system,system,'','2');
        $neturl=editurl($network,network,'','3');
        $typeurl=editurl($game_type,type,'','4');
        $imageurl=editurl($game_image,image,'','5');
        $rankurl=editurl($rank,rank,'','6');


        foreach( $uget as $k=>$v  ){
            switch ($v){
                case $k=='order' && $v !='all' :
                    $seoarr.= $order[$v].'_';
                    break;

                case $k=='system' && $v !='all':
                    $seoarr.= $system[$v].'_';
                    break;
                case $k=='network' && $v !='all' :
                    $seoarr.= $network[$v].'_';
                    break;

                case $k=='type' && $v !='all' :
                    $seoarr.= $game_type[$v].'_';
                    break;
                case $k=='image' && $v !='all'  :
                    $seoarr.= $game_image[$v].'_';
                    break;
                case $k=='rank'  &&  $v !='all':
                    $seoarr.= $rank[$v].'_';
                    break;
            }
        }


        $seo['title']= $seoarr."手机游戏_手机游戏下载_手机游戏破解版下载_好玩的手机游戏下载_96u手游网";
        $seo['keywords']=$seoarr."手机游戏,手机游戏下载,手机游戏破解版下载,好玩的手机游戏下载,96u手游网";
        $seo['description']=$seoarr."手机游戏,手机游戏下载,手机游戏破解版下载,好玩的手机游戏下载,96u手游网";


        //人气最高 最新入库




        //本周新增
        $zweek=strtotime('-1 week Sunday');
        $xweek=strtotime('+0 week Sunday 23 hours  59 minute  59 seconds');


        $week=M('down')
            ->where("create_time >'$zweek'and create_time <'$xweek' ")
            ->count('id');


        //本周新增
        $this->assign('week',$week);







        //字段game_type对应的数组 游戏类型
        $this->assign("SEO",$seo);
        $this->assign('game_type',$game_type);
        $this->assign('listscount',$listscount);
        $this->assign('show',$show);
        $this->assign('orurl',$orurl);
        $this->assign('sysurl',$sysurl);
        $this->assign('neturl',$neturl);
        $this->assign('typeurl',$typeurl);
        $this->assign('imageurl',$imageurl);
        $this->assign('rankurl',$rankurl);
        $this->assign('count',$count);
        $this->assign('lists',$lists);
        $this->assign('cid',9999);
        $this->display(T('Home@jf96u/Widget/yxksx'));
      }

    public function gamedown(){
        
        
        $this->display('Widget/gamedown');
    }

/******************************合集部分***********************************/
    /**
     * 作者:肖书成
     * 描述:专题，专区，K页面合集，当然也可以用做厂商，这里没做设计
     * 时间:2015-12-03
     */
    public function hj(){
        $key    =   I('key');
        if(!in_array($key,array('f','b','s'))){
            return false;
        }

        $table  =   array('f'=>'Feature','b'=>'Batch','s'=>'Special');
        $where  =   array(
                        'f'=>'pid = 0 AND interface = 0 AND enabled = 1',
                        'b'=>'pid = 0 AND interface = 0 AND enabled = 1',
                        's'=>'');

        $this->assign('key',$key);

        $baseWidget = new \Common\Controller\WidgetController();
        $baseWidget->hj('Home@jf96u/Widget/hj',$table[$key],$where[$key],'id DESC',12,false);
    }


    /**
     * 作者肖书成
     * 描述:厂商的Widget
     * 功能:随机调取厂商页
     * @param $id
     */
    public function cs($id){
        $count  =   M('Company')->where('status = 1 AND id != '.$id)->count('id');
        $count  =   (int)$count;

        if($count > 8){
            $rand   =   mt_rand(0,$count - 4);
            $lists  =   M('Company')->where('status = 1 AND id != '.$id)->limit($rand,8)->select();
        }elseif($count > 0){
            $lists  =   M('Company')->where('status = 1 AND id != '.$id)->select();
        }else{
            return ;
        }

        $url    =   C('STATIC_URL');

        if(empty($lists)) return ;

        foreach($lists as $k=>&$v){
            $v['url']   =   $url.'/'.(substr($v['path'],0,1) == '/'?substr($v['path'],1):$v['path']);
            $v['url']   =   substr($url,-1) == '/'?$v['url']:$v['url'].'/';
        }unset($v);

        $this->assign('lists',$lists);
        $this->display('Widget/cs');

    }
/******************************合集部分END********************************/

   public function gameTypeData($type){
	  if(!is_numeric($type)){return;}
	  $lists =   M("Down")->alias("__DOWN")->where("status=1  AND game_type=$type")->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->limit("24")->field("*")->select();
	  $this->assign("lists",$lists);
	  $this->display("Widget/gameTypeData");
   }
   
   
  public function indexSlider($pos){
	  $lists = $this->docAndDown($pos,3,3,3);
	  $this->assign("lists",$lists);
	  $this->display('Widget/slider');
  }
  
   public function indexHeadline($pos){
	  $lists = $this->docAndDown($pos,1,1);
      $clists = $this->docAndDown(4,1,5);
	  $this->assign("lists",$lists);
      $this->assign("clists",$clists);
	  $this->display('Widget/headline');
	 
  }

    /**
     * 描述：
     * @param $position
     * @param string $model(1表示只调用文章模块，2表示只调用下载模块，3表示调用文章和下载模块)
     * @param string $docnum 表示文章数量
     * @param string $downnum 表示下载数量
     * @return array
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    private function docAndDown($position,$model='3',$docnum='6',$downnum='6'){
	   if(!empty($position)){
		   $position_str = " AND home_position & $position";
		}
       if($model==3 || $model==2)
       {
           $down =   M("Down")->where("status=1".$position_str)->field("id,title,description,update_time,smallimg,cover_id,vertical_pic")->limit($downnum)->order("update_time desc")->select();
           $downa = array();
           foreach($down as $key=>$val){
                $downa[]=array(
                    'title'=>$val['title'],
                    'url'=>str_replace('index.html','',staticUrl("detail",$val['id'],"Down")),
                    'description'=>$val['description'],
                    'update_time'=>$val['update_time'],
                    'smallimg'=>$val['smallimg'],
                    'cover_id'=>$val['cover_id'],
                    'vertical_pic'=>$val['vertical_pic']
                );
            }
       }
      if($model==3 || $model==1)
      {
          $doc =   M("Document")->where("status=1".$position_str)->field("id,title,description,update_time,smallimg,cover_id,vertical_pic")->limit($docnum)->order("update_time desc")->select();
          $doca=array();
          foreach($doc as $key=>$val){
                $doca[]=array(
                    'title'=>$val['title'],
                    'url'=>staticUrl("detail",$val['id'],"Document"),
                    'description'=>$val['description'],
                    'update_time'=>$val['update_time'],
                    'smallimg'=>$val['smallimg'],
                    'cover_id'=>$val['cover_id'],
                    'vertical_pic'=>$val['vertical_pic']
                );
           }
      }
    if(empty($doca)) $lists=$downa;
    else if(empty($downa)) $lists=$doca;
    else $lists = array_merge($downa,$doca);
    $lists = array_sort($lists,'update_time','DESC');
    return $lists;
  }


  // 首页网游攻略
  // Author:Jeffrey Lau
   public function getDownTags(){
	      $doc =   M("Document")->where(array("status"=>"1","category_id"=>"1588"))->field("id,title,description,update_time")->limit("30")->order("update_time desc")->select();
		  $tags=array();
		  foreach($doc as $key=>$val){
			 $rs =M("Tags")->alias("__TAG")->join("INNER JOIN __TAGS_MAP__ ON __TAGS_MAP__.tid=__TAG.id")->where(array("did"=>$val['id'],"type"=>"document"))->field("__TAG.id,__TAG.title")->limit("20")->find();
		     if(!empty($rs))  $tags[] = $rs;
          }
		 $tags=array_unique_fb($tags);
		 $tags= array_slice($tags,0,10);
		 $this->assign("tags",$tags);
	     $this->display('Widget/gonglve');
	}

   public function getTagGame($id){
	   	 $tagsMap = M("TagsMap")->alias("__MAP")->join("INNER JOIN __DOWN__ ON __DOWN__.id = __MAP.did")->where("__MAP.type='down' AND __MAP.tid= '$id'")->field("*")->order("__MAP.update_time desc")->find();
		 $this->assign("down",$tagsMap);
	     $this->display('Widget/gameBlock');
	}
   public function getTagNews($id){
	     $tagsMap = M("TagsMap")->alias("__MAP")->join("INNER JOIN __DOCUMENT__ ON __DOCUMENT__.id = __MAP.did")->where("__MAP.type='document' AND __MAP.tid= '$id'")->field("title,__MAP.did")->order("__MAP.update_time desc")->select();
		 $tagsMap=array_unique_fb($tagsMap);
		 $tagsMap= array_slice($tagsMap,0,8);
		 $this->assign("lists",$tagsMap);
	     $this->display('Widget/newsBlock');
	}
		//首页厂商部分
	 //Jeffrey Lau
	public function indexCompany(){
		$company=M('Company')->order("update_time desc")->where("status=1 and position=1")->field("id,name,img,path")->limit(20)->select();
        $c = count($company);
        if($c < 20)
        {
            $limit = 20 - $c;
            $company_other=M('Company')->order("update_time desc")->where("status=1 and position!=1")->field("id,name,img,path")->limit($limit)->select();
            if($c > 0) $company = array_merge($company,$company_other);
            else $company=$company_other;
        }
		$this->assign("lists",$company);
        $this->display('Widget/indexCompany');
	}

  // 首页网游资料
  // Author:Jeffrey Lau
  public function indexWangyou($id){
	   $cateList = M('DownCategory')->where(array("pid"=>'1666',"status"=>"1"))->select();
	   foreach($cateList as $key=>$val){
			    $ids.=$val['id'].",";
	   }
	   $ids = rtrim($ids,",");
       $down =  M("Down")->alias("__DOWN")->where("status=1 AND category_id IN(".$ids.")")->order("update_time desc")->join("INNER JOIN __DOWN_DMAIN__ ON __DOWN.id = __DOWN_DMAIN__.id")->limit("3")->field("*")->select();;
	   $result = array();
	   foreach($down as $k=>$va){
		     $result[$k]['title']= $va['title'];
			 $result[$k]['icon']= get_cover($va['smallimg'],'path');
			 $result[$k]['url']= str_replace('index.html','',staticUrl('detail',$va['id'],'Down'));
			 $result[$k]['gonglve']  =  $this->getRelateArticle($va['id'],array("1590"));
		   	 $result[$k]['jietu']    =  $this->getRelateArticle($va['id'],array("1588"));
			 $result[$k]['wenda']    =  $this->getRelateArticle($va['id'],array("1764","1592"));
			 $result[$k]['zixun']    =  $this->getRelateArticle($va['id'],array("1622"));
			 $result[$k]['shipin']   =  $this->getRelateArticle($va['id'],array("1591"));
			 $result[$k]['pingce']   =  $this->getRelateArticle($va['id'],array("1589"));
	   }
	   

	   $this->assign("lists",$result);
	   $this->display('Widget/Wangyou');
	}



   private function getRelateArticle($ID,$filterID){
	      
	       $relate=get_base_by_tag($ID,'Down','Document','',false);
		   foreach($relate as $key=>$val){
			    if(!in_array($val['category_id'],$filterID)){
				   unset($relate[$key]);
				}
				
		   }
		   $relate= multi_array_sort($relate,'update_time',SORT_DESC);
		   if(empty($relate)){
			   return;
		   }   
	       return staticUrl('detail',$relate[0]['id'],'Document');
   }
 //首页专题
  public function indexZhuanti(){
         $where = array();
         $where['status'] = 1;
         $where['interface'] =0;
         $where['pid'] =0;
         $where['_string'] = "home_position & 1";
	     $feature = M('Feature')->where($where)->select();
		 $this->assign("lists",$feature);
		 unset($where);
	     $this->display('Widget/indexZhuanti');
	 }


 
 
 
 
 
 
 
 
} 