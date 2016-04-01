<?php
/**
 * 作者:肖书成
 * 描述:专区的基础类
 * 时间:2015/11/17
 * 备注:(96U专区、专题开始使用，)
 */

namespace Home\Controller;

use Think\Controller;
class BatchController extends Controller{
    /**
     * @Author 周良才、肖书成
     * @createTime 2015/3/11
     * @Comments 用来分页的方法（做过特殊处理）
     * @param $count
     * @param $row
     * @param $id
     * @param $isMobile
     */
    protected function pages($count,$row,$id,$isMobile){
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
        $show.='<br totalPageNumber="'.ceil($count/$row).'" style="display:none">';
        $this->assign('page',$show);// 赋值分页输出
        S('Batch'.'PageNumber',array($id=>ceil($count/$row)),86400);
    }

    /**
     * @Author 肖书成
     * @createTime 2015/3/11
     * @Comments 用来获取文章表的数据(目前还未启用)
     * 备注:（96U暂时没有使用）
     * @param $id
     * @param int $where
     * @param bool $isIndex
     * @param bool $isAndTag
     * @param int $row
     * @param string $order
     * @param bool $isMobile
     * @return mixed
     **/
    protected function getDocumentDate($id,$field='a.id,a.title,a.description,a.cover_id,a.view,a.create_time,a.ding,a.smallimg', $where = 1, $row = 10, $isIndex = true, $isArticle = false,$isAndTag = false, $isWenda = false,$order = "a.id DESC" , $isMobile = false){
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

    public function featureBase($id){
        //基础数据
        $base = M('Feature')->field('id,title,seo_title,keywords,description')->where('id = '.$id)->find();

        //SEO
        $SEO['title'] = $base['seo_title']?$base['seo_title']:$base['title'];
        $SEO['keywords'] = $base['keywords'];
        $SEO['description'] = $base['description'];

        //标题需要加前后缀
        if(C('SEO_STRING')){
            $t[abs((int)C('SEO_PRE_SUF') - 1)] = $SEO['title'];
            $t[(int)C('SEO_PRE_SUF')] = C('SEO_STRING');
            ksort($t);
            $SEO['title'] = implode('_', $t);
        }

        $this->assign(array(
            'SEO'=>$SEO,
            'info'=>$base
        ));
    }
}