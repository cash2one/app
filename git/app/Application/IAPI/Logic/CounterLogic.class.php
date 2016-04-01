<?php
namespace IAPI\Logic;

/**
 * StatisLogic.class.php
 *
 * 发稿量逻辑处理层
 *
 * @author 朱德胜
 */
class CounterLogic
{

    /**
     * 错误信息
     *
     * @var string
     */
    private $condition = [];

    /**
     * 错误信息
     *
     * @var string
     */
    private $error;

    /**
     * 稿件统计计算
     *
     * @param interge $userid    会员ID、会员昵称
     * @param interge $modelid   模型id
     * @param interge $time      时间表达式
     * @param interge $starttime 起始时间
     * @param interge $endtime   结束时间
     * @return array
     */
    public function calculate($userid, $modelid = '', $time = '', $starttime = '', $endtime = '')
    {
        $this->condition = [];
        $this->error = '';

        // 会员信息
        $user = $this->getUserInfo($userid);
        if (!$user) {
            $this->error = '数据资料库中未检索到会员';

            return false;
        }

        $this->condition['uid'] = $userid;

        // 模型信息
        $model = getModelInfo($modelid);
        if (!$model) {
            $this->error = '不存在的模型';

            return false;
        }

        // 时间参数分析
        $unit = strtolower($time);
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                $this->error = '无效的时间值';

                return false;
            }
        } else {
            $range = 1;
        }

        $range--;

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                $starttime = strtotime(date('Y-m-d 00:00:00', strtotime("-$range day")));
                $this->condition['create_time'] = ['egt', $starttime];
                break;
            // 周
            case 'weeks':
                $range += 1;
                $starttime = strtotime("-$range Monday");
                $endtime = strtotime(date('Y-m-d 23:59:59', strtotime(date('Y-m-d', $starttime) . " Sunday")));
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 月
            case 'month':
                $starttime = strtotime(date('Y-m-01 00:00:00', strtotime("-$range month")));
                $endtime = strtotime(date('Y-m-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 年
            case 'years':
                $starttime = strtotime(date('Y-01-01', strtotime("-$range years")));
                $endtime = strtotime(date('Y-12-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 自定义
            case 'custom':
                // 数据检查
                if (!$starttime && !$endtime) {
                    $this->error = '不存在的时间范围';

                    return false;
                }

                if ($starttime) {
                    if (!$this->isTime($starttime)) {
                        $this->error = '起始时间格式错误';

                        return false;
                    }

                    $starttime = strtotime($starttime);
                }

                if ($endtime) {
                    if (!$this->isTime($endtime)) {
                        $this->error = '结束时间格式错误';

                        return false;
                    }

                    $endtime = strtotime($endtime);
                }

                /* 时间范围设定 */
                if ($starttime && $endtime) {
                    // 检查起始时间是否大于结束时间
                    if ($starttime >= $endtime) {
                        $this->error = '起始时间不允许超过结束时间';

                        return false;
                    }

                    $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                } elseif ($starttime) {
                    // 大于当前时间
                    $this->condition['create_time'] = ['egt', $starttime];
                } elseif ($endtime) {
                    // 小于结束时间
                    $this->condition['create_time'] = ['elt', $endtime];
                }
                break;

            default:
                $this->error = '不存在的时间类型';

                return false;
        }

        /* 发稿量统计 */
        $draft = 0;
        $details = [];
        $moddraft = [];
        foreach ($model as $vars) {
            $module = M($vars[1]);

            // 栏目id
            $typeid = $module->field('category_id')->group('category_id')->where($this->condition)->select();

            if ($typeid) {
                $field = '';
                foreach ($typeid as $var) {
                    $field .= "SUM(IF(category_id={$var[category_id]},'1','0')) AS '{$var[category_id]}',";
                }

                $field = rtrim($field, ',');

                // 各栏目发稿数量
                $counter = $module->field($field)->group('uid')->where($this->condition)->find();

                if ($counter) {
                    $category = M($vars[0]);
                    $typeid = implode(array_keys($counter), ',');
                    $array = $category->field('id,title')->where('id IN(' . $typeid . ')')->select();

                    // 取得栏目名称
                    $column = [];
                    foreach ($array as $value) {
                        $column[$value['id']] = $value['title'];
                    }

                    foreach ($counter as $typeid => $amount) {
                        if (isset($column[$typeid])) {
                            $details[$vars['name']][$column[$typeid]] = $amount;
                        } else {
                            unset($counter[$typeid]);
                        }
                    }
                }

                // 模型发稿总量
                $count = array_sum($counter);
            } else {
                $count = 0;
            }

            $moddraft[$vars['name']] = $count;
            $draft += $count;
        }

        $info = [
            'uid' => $user['uid'],       // 会员id
            'nickname' => $user['nickname'],  // 会员账号
            'username' => $user['username'],  // 会员昵称
            'draft' => $draft,             // 发稿总量
            'moddraft' => $moddraft,          // 模型发稿量
            'details' => $details,           // 明细
        ];

        return $info;
    }

    public function calculateTotal($modelid = '', $time = '', $starttime = '', $endtime = '')
    {
        $this->condition = [];
        $this->error = '';

        // 模型信息
        $model = getModelInfo($modelid);
        if (!$model) {
            $this->error = '不存在的模型';

            return false;
        }

        // 时间参数分析
        $unit = strtolower($time);
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                $this->error = '无效的时间值';

                return false;
            }
        } else {
            $range = 1;
        }

        $range--;

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                $starttime = strtotime(date('Y-m-d 00:00:00', strtotime("-$range day")));
                $this->condition['create_time'] = ['egt', $starttime];
                break;
            // 周
            case 'weeks':
                $starttime = strtotime("-$range Monday");
                $endtime = strtotime(date('Y-m-d 23:59:59', strtotime(date('Y-m-d', $starttime) . " Sunday")));
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 月
            case 'month':
                $starttime = strtotime(date('Y-m-01 00:00:00', strtotime("-$range month")));
                $endtime = strtotime(date('Y-m-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 年
            case 'years':
                $starttime = strtotime(date('Y-01-01', strtotime("-$range years")));
                $endtime = strtotime(date('Y-12-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 自定义
            case 'custom':
                // 数据检查
                if (!$starttime && !$endtime) {
                    $this->error = '不存在的时间范围';

                    return false;
                }

                if ($starttime) {
                    if (!$this->isTime($starttime)) {
                        $this->error = '起始时间格式错误';

                        return false;
                    }

                    $starttime = strtotime($starttime);
                }

                if ($endtime) {
                    if (!$this->isTime($endtime)) {
                        $this->error = '结束时间格式错误';

                        return false;
                    }

                    $endtime = strtotime($endtime);
                }

                /* 时间范围设定 */
                if ($starttime && $endtime) {
                    // 检查起始时间是否大于结束时间
                    if ($starttime >= $endtime) {
                        $this->error = '起始时间不允许超过结束时间';

                        return false;
                    }

                    $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                } elseif ($starttime) {
                    // 大于当前时间
                    $this->condition['create_time'] = ['egt', $starttime];
                } elseif ($endtime) {
                    // 小于结束时间
                    $this->condition['create_time'] = ['elt', $endtime];
                }

                break;

            default:
                $this->error = '不存在的时间类型';

                return false;
        }

        /* 发稿量统计 */
        $draft = 0;
        $details = [];
        $moddraft = [];
        foreach ($model as $vars) {
            $module = M($vars[1]);
            $count = $module->where($this->condition)->count();

            if (!$count) {
                $count = 0;
            }

            $draft += $count;
        }

        if ($draft == '0' || $draft) {
            return $draft;
        } else {
            false;
        }
    }

    public function totalList($modelid = '', $time = '', $starttime = '', $endtime = '')
    {
        $this->condition = [];
        $this->error = '';

        // 模型信息
        $model = getModelInfo($modelid);
        if (!$model) {
            $this->error = '不存在的模型';

            return false;
        }

        // 时间参数分析
        $unit = strtolower($time);
        if (strpos($unit, '-') !== false) {
            list($unit, $range) = explode('-', $unit);

            if (!is_numeric($range) || $range == '0') {
                $this->error = '无效的时间值';

                return false;
            }
        } else {
            $range = 1;
        }

        $range--;

        /* 统计查询时间定义 */
        switch ($unit) {
            // 日
            case 'day':
                $starttime = strtotime(date('Y-m-d 00:00:00', strtotime("-$range day")));
                $this->condition['create_time'] = ['egt', $starttime];
                break;
            // 周
            case 'weeks':
                $starttime = strtotime("-$range Monday");
                $endtime = strtotime(date('Y-m-d 23:59:59', strtotime(date('Y-m-d', $starttime) . " Sunday")));
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 月
            case 'month':
                $starttime = strtotime(date('Y-m-01 00:00:00', strtotime("-$range month")));
                $endtime = strtotime(date('Y-m-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 年
            case 'years':
                $starttime = strtotime(date('Y-01-01', strtotime("-$range years")));
                $endtime = strtotime(date('Y-12-01', $starttime) . ' +1 month -1 day');
                $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                break;
            // 自定义
            case 'custom':
                // 数据检查
                if (!$starttime && !$endtime) {
                    $this->error = '不存在的时间范围';

                    return false;
                }

                if ($starttime) {
                    if (!$this->isTime($starttime)) {
                        $this->error = '起始时间格式错误';

                        return false;
                    }

                    $starttime = strtotime($starttime);
                }

                if ($endtime) {
                    if (!$this->isTime($endtime)) {
                        $this->error = '结束时间格式错误';

                        return false;
                    }

                    $endtime = strtotime($endtime);
                }

                /* 时间范围设定 */
                if ($starttime && $endtime) {
                    // 检查起始时间是否大于结束时间
                    if ($starttime >= $endtime) {
                        $this->error = '起始时间不允许超过结束时间';

                        return false;
                    }

                    $this->condition['create_time'] = ['between', [$starttime, $endtime]];
                } elseif ($starttime) {
                    // 大于当前时间
                    $this->condition['create_time'] = ['egt', $starttime];
                } elseif ($endtime) {
                    // 小于结束时间
                    $this->condition['create_time'] = ['elt', $endtime];
                }

                break;

            default:
                $this->error = '不存在的时间类型';
                return false;
        }

        /* 发稿量统计 */
        $draft = 0;
        $details = [];
        $moddraft = [];
        foreach ($model as $vars) {
            $module = M($vars[1]);
            $count = $module->where($this->condition)->count();

            if (!$count) {
                $count = 0;
            }

            $draft += $count;
        }

        if ($draft == '0' || $draft) {
            return $draft;
        } else {
            false;
        }
    }

    /**
     * 获取会员记录信息
     *
     * @param interge $userid 会员ID、会员昵称
     * @return array
     */
    private function getUserInfo($userid)
    {
        if (is_numeric($userid)) {
            // 会员id条件
            return M('member')->where(['uid' => $userid])->find();
        } else {
            // 会员通行证条件
            return M('member')->where(['nickname' => $userid])->find();
        }
    }

    /**
     * 检查时间格式验证
     *
     * @param interge $datetime 时间格式
     * @return bool
     */
    private function isTime($datetime = '')
    {
        if ($datetime) {
            $datetime .= ' 00:00:00';

            return preg_match('/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)\s+([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $datetime) === 1;
        }

        return false;
    }

    public function __get($name)
    {
        if (isset($this->error)) {
            return $this->error;
        }
    }
}
