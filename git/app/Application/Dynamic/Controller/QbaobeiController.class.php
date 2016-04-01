<?php

namespace Dynamic\Controller;

class QbaobeiController extends BaseController
{

    /**
     * API接口初始化
     *
     * @return void
     */
    protected function API_init()
    {
        //结果关闭trace
        C('SHOW_PAGE_TRACE', false);
        $cors = C('DYNAMIC_SERVER_ALLOW_CORS');
        $referer = $_SERVER['HTTP_REFERER'];
        if ($referer) {
            $referer = parse_url($referer);
            $host = $referer['host'];
            if (in_array($host, $cors)) {
                header('Access-Control-Allow-Origin:http://' . $host);
            }
        }
    }

    /**
     * 浏览次数
     * author:Jeffrey Lau
     *
     * @param integer $id    数据ID
     * @param string  $model 模型名
     * @return void
     */
    public function API_View()
    {
        $this->API_init();
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $id = I('id');
        $model = ucfirst(I('model'));
        $model = empty($model) ? 'Document' : $model;
        if (!is_numeric($id)) {
            return;
        }

        $m = M($model);
        $m->where('id=' . $id)->setInc('view');

        $rs = M($model)->field('view')->where(array('id' => $id))->find();
        echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
    }

    /**
     * 评论列表接口
     *
     * @param integer $id    数据ID
     * @param string  $model 模型名
     * @return void
     */
    public function API_comment($id, $model)
    {
        $this->API_init();
        $callback = I('callback');

        if (!is_numeric($id) || empty($model)) {
            return;
        }
        $this->API_View($id, $model); //浏览次数+1
        $m = M('Comment');
        $map = array();
        $map['document_id'] = intval($id);
        $map['enabled'] = 1;
        $map['type'] = strip_tags($model);
        $rs = $m->where($map)->field('id,uname,message,add_time')->order('add_time desc')->limit(0, 10)->select();
        if ($fuc) {
            return $rs;
        } else {
            echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
        }
    }

    /**
     * 提交评论
     *
     * @return void
     */
    public function API_commentSub()
    {
        $this->API_init();
        $callback = I('callback');

        $id = I('id');
        $model = I('model');
        if (!is_numeric($id) || empty($model)) {
            return;
        }

        $m = M('Comment');
        $data['message'] = strip_tags(I('message'));
        $data['document_id'] = intval($id);
        $data['type'] = strip_tags($model);
        $data['uname'] = strip_tags(I('uname'));
        $data['add_time'] = time();
        $data['enable'] = C('COMMENT_AUDIT') ? C('COMMENT_AUDIT') : 0;
        $rs = $m->add($data);
        echo $callback ? $callback . '(' . json_encode($rs) . ');' : json_encode($rs);
    }
    /**
     * 获取浏览次数
     *
     * @param integer $id    数据ID
     * @param string  $model 模型名
     * @return void
     */
//    public function API_GetView($id = 0, $fuc = false){
//        $this->API_init();
//        //JSONP或其他src方式的回调函数名
//        $callback = I('callback');
//        if(!is_numeric($id)) return;
//        $rs = M('Document')->where('id='.$id)->getField("view");
//		
//        if($fuc){
//            return $rs;
//        }else{
//            echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);           
//        }
//		
//		
//		
//    }

    //标签搜索页面
    public function tag()
    {
        $keyword = remove_xss(I('keyword'));

        $tid = M('tags')->field('id,title,meta_title,keywords,description')->where(
            "`name` = '$keyword' AND status = 1"
        )->find();

        if ($tid) {
            $sql = M('TagsMap')->alias('a')->field('b.id,b.title,b.description,b.smallimg')
                ->join('__DOCUMENT__ b ON a.did = b.id')
                ->where("a.tid = " . $tid['id'] . " AND a.type = 'document' AND b.status = 1 ")->order(
                    'b.update_time DESC'
                )->buildSql();

            //数据统计
            $count = M()->query("SELECT count(id) count FROM $sql sub ");
            $count = $count[0]['count'];

            //分页参数处理
            $p = I('p');
            if (!is_numeric($p) || $p < 0) {
                $p = 1;
            }
            $row = 10;
            $str = ($p - 1) * $row;

            //列表数据
            $lists = M()->query("$sql limit $str,$row");

            //标签获取
            foreach ($lists as $k => &$v) {
                $v['tags'] = M('TagsMap')->field('b.id,b.title,b.name')->alias('a')->join(
                    '__TAGS__ b ON a.tid = b.id'
                )->where('a.type = "document" AND a.did = ' . $v['id'])->select();
            }
            unset($v); //用了指针，防止出错

            //SEO
            $seo['title'] = $tid['meta_title'] ? $tid['meta_title'] : $tid['title'];
            $seo['keywords'] = $tid['keywords'] ? $tid['keywords'] : $tid['title'];
            $seo['description'] = $tid['description'] ? $tid['description'] : '亲亲宝贝网为大家提供“' . $tid['title'] . '”的相关文章，希望能为大家提供帮助。';

            //标题需要加前后缀
            if (C('SEO_STRING')) {
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seo['title'] = implode('_', $t);
            }

            //分页
            $path = C('STATIC_URL') . '/tag/' . $keyword . '/{page}.html';

            $Page = new \Think\Page($count, $row, '', false, $path); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->rollPage = 5;
            $Page->setConfig('first', '首页');
            $Page->setConfig('end', '尾页');
            $Page->setConfig('prev', "上一页");
            $Page->setConfig('next', '下一页');
            $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');

            $show = $Page->show(); // 分页显示输出
            $this->assign('page', $show); // 赋值分页输出

            $this->assign(
                array(
                    'info' => $tid,
                    'SEO' => $seo,
                    'count' => $count,
                    'lists' => $lists,
                )
            );
            $this->display(T('Home@qbaobei/Widget/tags'));
        } else {
            $this->error('404');
        }
    }


    //标签搜索页面
    public function imgTag()
    {
        $keyword = remove_xss(I('keyword'));

        $tid = M('tags')->field('id,title,meta_title,keywords,description')->where(
            "`name` = '$keyword' AND status = 1"
        )->find();

        if ($tid) {
            $sql = M('TagsMap')->alias('a')->field('b.id,b.title,b.description,b.smallimg,c.content')
                ->join('__GALLERY__ b ON a.did = b.id')
                ->join('__GALLERY_ALBUM__ c ON b.id = c.id')
                ->where("a.tid = " . $tid['id'] . " AND a.type = 'gallery' AND b.status = 1 ")->order(
                    'b.update_time DESC'
                )->buildSql();

            //数据统计
            $count = M()->query("SELECT count(id) count FROM $sql sub ");
            $count = $count[0]['count'];

            //分页参数处理
            $p = I('p');
            if (!is_numeric($p) || $p < 0) {
                $p = 1;
            }
            $row = 10;
            $str = ($p - 1) * $row;

            //列表数据
            $lists = M()->query("$sql limit $str,$row");

            //标签获取
            foreach ($lists as $k => &$v) {
                $v['tags'] = M('TagsMap')->field('b.id,b.title,b.name')->alias('a')->join(
                    '__TAGS__ b ON a.tid = b.id'
                )->where('a.type = "gallery" AND a.did = ' . $v['id'])->select();
            }
            unset($v); //用了指针，防止出错

            //SEO
            $seo['title'] = $tid['meta_title'] ? $tid['meta_title'] : $tid['title'];
            $seo['keywords'] = $tid['keywords'] ? $tid['keywords'] : $tid['title'];
            $seo['description'] = $tid['description'] ? $tid['description'] : '亲亲宝贝网为大家提供“' . $tid['title'] . '”的相关文章，希望能为大家提供帮助。';

            //标题需要加前后缀
            if (C('SEO_STRING')) {
                $t = array();
                $t[abs((int)C('SEO_PRE_SUF') - 1)] = $seo['title'];
                $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
                ksort($t);
                $seo['title'] = implode('_', $t);
            }

            //分页
            $path = C('STATIC_URL') . '/tag/' . $keyword . '/{page}.html';

            $Page = new \Think\Page($count, $row, '', false, $path); // 实例化分页类 传入总记录数和每页显示的记录数
            $Page->rollPage = 5;
            $Page->setConfig('first', '首页');
            $Page->setConfig('end', '尾页');
            $Page->setConfig('prev', "上一页");
            $Page->setConfig('next', '下一页');
            $Page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %END% %DOWN_PAGE%');

            $show = $Page->show(); // 分页显示输出
            $this->assign('page', $show); // 赋值分页输出

            $this->assign(
                array(
                    'info' => $tid,
                    'SEO' => $seo,
                    'count' => $count,
                    'lists' => $lists,
                )
            );
            $this->display(T('Home@qbaobei/Widget/imgTags'));
        } else {
            $this->error('404');
        }
    }

    /**
     * 描述：获取微信二维码html代码
     *
     * @author  谭坚
     * @version 1.0.0
     * @modify  time:
     * @modify  author:
     */
    public function weiXin()
    {
        $id = I('id');
        if (!is_numeric($id)) {
            return;
        }
        $tag_name = I('tag_name');
        $tag_href = I('tag_href');
        $callback = I('callback');
        $this->API_init();
        $ext = '.html';
        $file_name = C('STATIC_ROOT') . '/weixin/' . $id . $ext;
        if (file_exists($file_name)) {
            $html = file_get_contents($file_name);
            $html = str_replace(array('{tag_name}', '{tag_href}'), array($tag_name, $tag_href), $html);
            $data['html'] = $html;
            $lists = json_encode($data);
            //数据返回
            echo $callback ? $callback . '(' . $lists . ');' : $lists;
        }
    }
}
