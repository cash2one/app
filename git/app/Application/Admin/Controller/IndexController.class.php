<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController
{

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index()
    {
        if (IS_POST) {
            //提交表单
            $date_time = I('time-end');
            $firstday = date("Y-m-01", strtotime($date_time));
            $lastday = date("Y-m-d", strtotime("$firstday +1 month -1 day"));
            $month_startdate = $firstday . ' 00:00:00';
            $month_enddate = $lastday . ' 23:59:59';
            $startdate = $date_time . ' 00:00:00';
            $enddate = $date_time . ' 23:59:59';
            $date18 = $date_time . ' 18:00:00';
            $starttime = strtotime($startdate);
            $endtime = strtotime($enddate);
            $month_starttime = strtotime($month_startdate); //月开始时间
            $month_endtime = strtotime($month_enddate); //月结束时间
            $time18 = strtotime($date18); //当日18点后
            $cateId = D('Main')->getCateID(array('开测表', '开服表'), 'id,title'); //分类（开服，开测）
            $catId0 = $cateId['a1'];
            $catId1 = $cateId['a2']; //分类（大礼包，激活码，新手卡）
            //获取编辑信息
            $main = D('Main');

            $member_info = D('Member')->lists();
            $work_data = array();
            foreach ($member_info as $value) {
                $work_data[$value['uid']]['nickname'] = $value['nickname']; //昵称
                $work_data[$value['uid']]['username'] = $value['username']; //姓名
                $work_data[$value['uid']]['down_0'] = $main->getDownCount($value['uid'], 0, $starttime, $endtime); //新增下载量
                $work_data[$value['uid']]['document_0'] = $main->getDocumentCount($value['uid'], 0, $starttime, $endtime); //新增文档量
                $work_data[$value['uid']]['document_all'] = $main->getDocumentCount($value['uid'], 0, '', ''); //新增文档量
                $work_data[$value['uid']]['down_1'] = $main->getDownCount($value['uid'], 1, $starttime, $endtime); //更新下载量
                $work_data[$value['uid']]['document_1'] = $main->getDocumentCount($value['uid'], 1, $starttime, $endtime); //更新文档量

                $work_data[$value['uid']]['package'] = $main->getPackageCount($value['uid'], $catId1, $starttime, $endtime); //礼包数据统计
                $work_data[$value['uid']]['package_3_5'] = $main->getPackageCount($value['uid'], $catId0, $starttime, $endtime); //新增开测开服表 3,开测.5，开服
                $work_data[$value['uid']]['feature'] = $main->getFeatureCount($value['uid'], $startdate, $enddate); //新增专题
                $work_data[$value['uid']]['special'] = $main->getSpecialCount($value['uid'], $startdate, $enddate); //新增K页面
                $work_data[$value['uid']]['batch'] = $main->getBatchCount($value['uid'], $startdate, $enddate); //新增专区
                $work_data[$value['uid']]['company'] = $main->getCompanyCount($value['uid'], $starttime, $endtime); //新增厂商
                $work_data[$value['uid']]['document_view'] = $main->getViewSum($value['uid'], 'document', $starttime, $endtime); //作者当日创建文章总点击量 add by tanjian 2015-7-19
                $work_data[$value['uid']]['down_view'] = $main->getViewSum($value['uid'], 'down', $starttime, $endtime); //作者当日创建下载总点击量 add by tanjian 2015-7-19
                $work_data[$value['uid']]['document_month_view'] = $main->getViewSum($value['uid'], 'document', $month_starttime, $month_endtime); //作者当月创建文章总点击量 add by tanjian 2015-7-19
                $work_data[$value['uid']]['down_month_view'] = $main->getViewSum($value['uid'], 'down', $month_starttime, $month_endtime); //作者当月创建下载总点击量 add by tanjian 2015-7-19
                $work_data[$value['uid']]['document_all_view'] = $main->getViewSum($value['uid'], 'document', '', ''); //文章总点击量 add by tanjian 2015-7-19
                $work_data[$value['uid']]['down_all_view'] = $main->getViewSum($value['uid'], 'down', '', ''); //下载总点击量 add by tanjian 2015-7-19
                //当日新增的
                $down_0 = $work_data[$value['uid']]['down_0'];
                $document_0 = $work_data[$value['uid']]['document_0'];
                $package = $work_data[$value['uid']]['package'];
                $package35 = $work_data[$value['uid']]['package_3_5'];
                $special = $work_data[$value['uid']]['special'];
                $feature = $work_data[$value['uid']]['feature'];
                $batch = $work_data[$value['uid']]['batch'];
                $company = $work_data[$value['uid']]['company'];
                //当日修改的
                $down_1 = $work_data[$value['uid']]['down_1'];
                $document_1 = $work_data[$value['uid']]['document_1'];

                $work_data[$value['uid']]['add_count'] = $down_0 + $document_0 + $package + $package35 + $special + $feature + $batch + $company; //当日新加内容总量
                $work_data[$value['uid']]['edit_count'] = $down_1 + $document_1; //当日修改内容总量

                //18点后更新的
                $down_two = $main->getDownCount($value['uid'], 0, $time18, $endtime); //18点后新增下载量
                $document_two = $main->getDocumentCount($value['uid'], 0, $time18, $endtime); //18点后新增文档量
                $package_two = $main->getPackageCount($value['uid'], '', $time18, $endtime); //礼包数据统计
                $special_two = $main->getSpecialCount($value['uid'], $date18, $enddate); //新增K页面
                $feature_two = $main->getFeatureCount($value['uid'], $date18, $enddate); //新增专题
                $batch_two = $main->getBatchCount($value['uid'], $date18, $enddate); //新增专区
                $company_two = $main->getCompanyCount($value['uid'], $time18, $endtime); //新增厂商

                $down_two1 = $main->getDownCount($value['uid'], 1, $time18, $endtime); //更新下载量
                $document_two1 = $main->getDocumentCount($value['uid'], 1, $time18, $endtime); //更新文档量

                $work_data[$value['uid']]['edit_18count'] = $down_two + $document_two + $package_two + $special_two + $feature_two + $batch_two + $company_two + $down_two1 + $document_two1; //当日18:00后增加数量

            }
            $this->assign('date_time', $date_time);
            $this->assign('work', $work_data); //编辑工作量统计
        } else {
            $this->meta_title = '管理首页';
        }
        $this->display();
    }

    /*
     * 导出当日URL地址
     *
     */

    public function exportRecord()
    {
        $datetime = I('datetime');
        $type = I('type'); //类型,1为PC版,2为手机版
        $filePath = RUNTIME_PATH . 'url.txt'; //创建一个txt
        @unlink($filePath); //删除该文件
        $start = strtotime($datetime);
        $end = strtotime($datetime) + (60 * 60 * 24);
        $field = "id,title";
        $condition['create_time'] = array(array('gt', $start), array('lt', $end));
        $condition['status'] = "1";
        $result = array(); //存放结果
        $down = M('Down')->where($condition)->field($field)->select(); //下载
        $doc = M('Document')->where($condition)->field($field)->select(); //文章
        $package = M('Package')->where($condition)->field($field)->select(); //礼包
        foreach ($down as $key => $val) {
            $result[] = $type == "1" ? staticUrl('detail', $val['id'], 'Down') : staticUrlMobile('detail', $val['id'], 'Down');
        }
        foreach ($doc as $key => $val) {
            $result[] = $type == "1" ? staticUrl('detail', $val['id'], 'Document') : staticUrlMobile('detail', $val['id'], 'Document');
        }
        foreach ($package as $key => $val) {
            $result[] = $type == "1" ? staticUrl('detail', $val['id'], 'Package') : staticUrlMobile('detail', $val['id'], 'Package');
        }
        $result = array_unique($result);
        file_put_contents($filePath, '');
        $fp_write = fopen($filePath, "w"); //打开
        foreach ($result as $item) {
            fwrite($fp_write, $item); //写入
            $res = fwrite($fp_write, "\r\n");
        }
        if ($res) {
//将文件输出到浏览器
            if (is_file($filePath)) {
                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=" . basename($filePath));
                readfile($filePath);
                exit;
            } else {
                echo "导出失败,记录文件不存在！";
                exit;
            }
        }
        fclose($fp_write); //关闭

    }

    public function getinform()
    {
        $data = I('post.');
        $data['token'] = token();
        $url = $data['url'];

        unset($data['url']);
        $args = '';
        foreach ($data as $v => $k) {
            $args .= '&' . $v . "=" . $k;
        }
        $args = substr($args, 1);

        $return = getHttpResponseGET($url . '?' . $args);

        echo $return;

    }
}
