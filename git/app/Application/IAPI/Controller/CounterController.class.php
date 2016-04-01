<?php
/**
 * StatisController.class.php
 *
 * 统计管理类
 *
 * @author 朱德胜
 */
namespace IAPI\Controller;

class CounterController extends CommonController
{

    /**
     * 逻辑处理资源
     *
     * @var resource
     */
    private $logic;

    /**
     * 架构函数
     *
     * @return void
     */
    public function initialize()
    {
        $this->logic = new \IAPI\Logic\CounterLogic();
    }

    /**
     * 投稿量统计汇总
     *
     * 此函数不会返回每天的发稿量
     *
     * @param interge $userid    (必选参数) 会员ID、会员昵称
     * @param string  $time      day:日 weeks:周 month:月 years:年 custom: 自定义 默认当日
     *                           自定义:starttime 起始时间 endtime 结束时间 格式:2015-01-10
     * @param interge $modelid   模型id,在不选的情况下全部,默认全部模型
     * @param interge $starttime 起始时间
     * @param interge $endtime   结束时间
     * @return json
     */
    public function get()
    {
        $counter = $this->logic->calculate(I('userid'), I('modelid'), I('time', 'day'), I('starttime'), I('endtime'));
        if (!$counter) {
            resultFormat(0, $this->logic->error);
        }

        resultFormat(1, false, $counter);
    }

    /**
     * 投稿量流水
     *
     * 此函数会返回每天的发稿数量
     *
     * @param interge $userid  (必选参数) 会员ID、会员昵称
     * @param string  $time    day:日 weeks:周 month:月 years:年
     * @param interge $modelid 模型id,在不选的情况下全部,默认全部模型
     * @return json
     */
    public function listGet()
    {
        // 时间参数分析
        $unit = strtolower(I('time', 'day'));
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                resultFormat(0, '无效的时间值');
            }
        } else {
            $range = 1;
        }

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                break;
            // 周
            case 'weeks':
                $range = $range * 7;
                break;
            // 月
            case 'month':
                $range = $range * 30;
                break;
            // 年
            case 'years':
                $range = $range * 365;
                break;

            default:
                resultFormat(0, '不存在的时间类型');
        }

        // 顺序从远到近排序
        for ($i = $range; $i > 0; $i--) {
            $starttime = date('Y-m-d', strtotime('-' . ($i - 1) . ' day'));
            $endtime = date('Y-m-d', strtotime('-' . ($i - 2) . ' day'));
            $counter = $this->logic->calculate(I('userid'), I('modelid'), 'custom', $starttime, $endtime);

            if ($counter) {
                $result[$i] = $counter;
            } else {
                resultFormat(0, $this->logic->error);
            }
        }

        resultFormat(1, false, $result);
    }

    /**
     * 全部会员汇总
     *
     * 此函数不会返回每天的发稿量
     *
     * @param string  $time      day:日 weeks:周 month:月 years:年 custom: 自定义 默认当日
     *                           自定义参数:starttime 起始时间 endtime 结束时间 格式:2015-01-10
     * @param interge $modelid   模型id,在不选的情况下全部,默认全部模型
     * @param interge $starttime 起始时间
     * @param interge $endtime   结束时间
     * @return json
     */
    public function allGet()
    {
        // 会员信息
        $user = M('member')->field('uid')->select();
        if (!$user) {
            resultFormat(0, '数据资料库中未检索到会员');
        }

        $result = [];
        foreach ($user as $vars) {
            $counter = $this->logic->calculate($vars['uid'], I('modelid'), I('time', 'day'), I('starttime'), I('endtime'));
            if (!$counter) {
                resultFormat(0, $this->logic->error);
            }

            $result[] = $counter;
        }

        // 排序，发稿量越多的用户越排前
        $result = Sorts($result, 'draft');
        resultFormat(1, false, $result);
    }

    /**
     * 所有会员投稿量统计详情，会以列表的形式展开每日的投稿信息
     *
     * @param string  $time    day:日 weeks:周 month:月 years:年
     * @param interge $modelid 模型id,在不选的情况下全部,默认全部模型
     * @return json
     */
    public function listAllGet()
    {
        // 时间参数分析
        $unit = strtolower(I('time', 'day'));
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                resultFormat(0, '无效的时间值');
            }
        } else {
            $range = 1;
        }

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                break;
            // 周
            case 'weeks':
                $range = $range * 7;
                break;
            // 月
            case 'month':
                $range = $range * 30;
                break;
            // 年
            case 'years':
                $range = $range * 365;
                break;

            default:
                resultFormat(0, '不存在的时间类型');
        }

        // 会员信息
        $user = M('member')->field('uid')->select();
        if (!$user) {
            resultFormat(0, '数据资料库中未检索到会员');
        }

        foreach ($user as $k => $vars) {
            // 顺序从远到近排序
            for ($i = $range; $i > 0; $i--) {
                $starttime = date('Y-m-d', strtotime('-' . ($i - 1) . ' day'));
                $endtime = date('Y-m-d', strtotime('-' . ($i - 2) . ' day'));
                $counter = $this->logic->calculate($vars['uid'], I('modelid'), 'custom', $starttime, $endtime);
                if ($counter) {
                    $result[$k][$i] = $counter;
                } else {
                    resultFormat(0, $this->logic->error);
                }
            }
        }

        resultFormat(1, false, $result);
    }

    /**
     * 投稿量总统计，只会返回最终数值
     *
     * @param string  $time      day:日 weeks:周 month:月 years:年 custom: 自定义 默认当日
     *                           自定义:starttime 起始时间 endtime 结束时间 格式:2015-01-10
     * @param interge $modelid   模型id,在不选的情况下全部,默认全部模型
     * @param interge $starttime 起始时间
     * @param interge $endtime   结束时间
     * @return json
     */
    public function totalGet()
    {
        $counter = $this->logic->calculateTotal(I('modelid'), I('time', 'day'), I('starttime'), I('endtime'));

        if ($counter === false) {
            resultFormat(0, $this->logic->error);
        }

        resultFormat(1, false, $counter);
    }

    /**
     * 投稿量总统计，返回每日投稿量信息
     *
     * @param string  $time    day:日 weeks:周 month:月 years:年 默认当日
     * @param interge $modelid 模型id,在不选的情况下全部,默认全部模型
     * @return json
     */
    public function listTotalGet()
    {
        // 时间参数分析
        $unit = strtolower(I('time', 'day'));
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                resultFormat(0, '无效的时间值');
            }
        } else {
            $range = 1;
        }

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                break;
            // 周
            case 'weeks':
                $range = $range * 7;
                break;
            // 月
            case 'month':
                $range = $range * 30;
                break;
            // 年
            case 'years':
                $range = $range * 365;
                break;

            default:
                resultFormat(0, '不存在的时间类型');
        }

        // 顺序从远到近排序
        for ($i = $range; $i > 0; $i--) {
            $starttime = date('Y-m-d', strtotime('-' . ($i - 1) . ' day'));
            $endtime = date('Y-m-d', strtotime('-' . ($i - 2) . ' day'));
            $counter = $this->logic->totalList(I('modelid'), 'custom', $starttime, $endtime);
            if ($counter !== false) {
                $result[$i] = $counter;
            } else {
                resultFormat(0, $this->logic->error);
            }
        }

        resultFormat(1, false, $result);
    }
}
