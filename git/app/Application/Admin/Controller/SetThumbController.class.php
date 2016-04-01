<?php
// +----------------------------------------------------------------------
// | 描述
// +----------------------------------------------------------------------
// | Author: 谭坚 <tanjian691406429@163.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Date: 15-11-21 下午5:58    Version:1.0.0 
// +----------------------------------------------------------------------


namespace Admin\Controller;


class SetThumbController extends AdminController{
    /**
     * 描述：插入缩略图
     * @return bool
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function insertThumbImg()
    {
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
        //JSONP或其他src方式的回调函数名
        $callback = I('callback');
        $src = I('src');
        $model_id = I('model_id');
        if(!$src || !is_numeric($model_id)) return ;
        $thumb_field = $this->getThumbFiled($model_id);
        $thumb_id = $this->setPic($src);
        $rs['field'] = $thumb_field; //缩略图字段
        $rs['value'] = $thumb_id; //缩略图在图片表的id值
        echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 描述：获取缩略图字段
     * @param $model_id
     * @return string
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    function getThumbFiled($model_id)
    {
        $theme = C('THEME'); //获取站点主题
        $where['id'] = $model_id;
        $rs = M('model')->field('name,extend')->where($where)->find();
        if($rs['extend'] == '0')
        {
            $model = $rs['name'];
        }
        else
        {
            $where['id'] = $rs['extend'];
            $rs =  M('model')->field('name')->where($where)->find();
            $model = $rs['name'];
        }
        if($model == 'document')
        {
            switch($theme)
            {
                case 'jf96u':
                case 'qbaobei': return 'smallimg';
                case '7y7': return 'atlas_a';
                default: return 'cover_id';
            }
        }
        else if($model == 'down')
        {
            return 'cover_id';
        }
        else if($model == 'package')
        {
            return 'cover_id';
        }
    }

    /**
     * 描述：获取插入图片的id
     * @param $src
     * @return mixed
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    function setPic($src)
    {
        $data['path'] = $src;
        $data['create_time'] = time();
        $id = M('picture')->add($data);
        return $id;
    }
} 