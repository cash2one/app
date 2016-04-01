<?php
/**
 * DownUpController.class.php
 *
 * 下载包管理类
 *
 * @author 朱德胜
 */
namespace Script\Controller;

use Think\Controller;

class DownUpController extends Controller
{
    /**
     * 配置定义
     *
     * 站点域名 => [站点id, 下载预定义站点]
     *
     * @return array
     */
    private $conf = [
        '7230.com'      =>  4,
        'anfensi.com'   =>  5,
        '96u.com'       =>  6,
        'idongdong.com' =>  7,
    ];
    
    // 站点id
    private $siteid;

    // 预定义站点id
    private $schemeid;

    // 接口请求地址
    private $apiUrl = 'http://t.5hn.com/api.php?s=/App/Lists';

    /**
     * 根据站点自动搜索站点、并向远程请求下载包内容
     * @return void
     */
    public function index()
    {
        $url = M('config')->where(['name' => 'STATIC_URL'])->getField('value');
        if ($url) {
            $vars = array_slice(explode('.', $url), -2);
            $name = implode($vars, '.');
            
            if (isset($this->conf[$name])) {
                $this->siteid = $this->conf[$name];
            }
        }
        
        if (!isset($this->siteid)) {
            $this->ajaxReturn('Parameter Error', 'EVAL');
        }
        
        // 接口参数随着更新时间填充查询条件,默认第一次取1000条记录
        $this->apiUrl .= "/siteid/{$this->siteid}";

        $time = F('DownUpTime_'.$this->siteid);
        if ($time) {
            $this->apiUrl .= '/modify_time/'.$time;
        } else {
            $this->apiUrl .= '/limit/1000';
        }
        
        // 接口请求,返回软件包
        $data = getHttpResponseGET($this->apiUrl);
        if ($data) {
            $data = json($data);
            $data = $data['data'];
        }

        if (!$data) {
            $this->ajaxReturn('No record need to be updated', 'EVAL');
        }

        // 根据不同站点更新
        $method = '_'.$vars[0];
        if (method_exists($this, $method)) {
            $this->$method($data);
        } else {
            $this->ajaxReturn('Request Fail', 'EVAL');
        }
    }

    /**
     * 7230站点处理层
     *
     * 更新软件包版本、大小
     *
     * @param array $data 接口返回的软件包
     * @return void
     */
    private function _7230($data)
    {
        // 下载软件包
        $down = M('DownAddress')->field('did,url')->where(['site_id' => 13])->select();

        if ($down) {
            $app = [];
            foreach ($down as $vars) {
                $appid = '';
                $url   = substr($vars['url'], 1);
                $exp   = explode('/', $url);
                $count = count($exp);

                // 是否为旧规则 3新规则 2旧规则
                if ($count == 3) {
                    $appid = intval(substr($exp[0], 3, 6));
                    // 渠道id
                    $app[$appid]['ditchid'] = intval(substr($exp[2], 3, 6));
                } elseif ($count == 2) {
                    // App包id
                    $appid = intval(substr($exp[0], 3, 4));
                }

                if ($appid) {
                    $app[$appid]['appid']    = $appid;
                    $app[$appid]['recordid'] = $vars['did'];
                }
            }

            if ($app) {
                // 站点包与API包进行比较
                $result = [];
                
                foreach ($data as $i => $value) {
                    if (isset($app[$value['id']])) {
                        if (isset($app[$value['id']]['ditchid'])) {
                            // 新数据处理
                            foreach ($value['ditch'] as $val) {
                                if ($val['ditchid'] == $app[$value['id']]['ditchid']) {
                                    $result[$i]['id']       = $app[$value['id']]['recordid'];
                                    $result[$i]['version']  = 'v'.$val['version'];
                                    $result[$i]['size']     = bcmul($val['size'], 1024, 0);
                                }
                            }
                        } else {
                            // 旧数据兼容
                            $ditch = array_shift($value['ditch']);
                            $result[$i]['id']       = $app[$value['id']]['recordid'];
                            $result[$i]['version']  = 'v'.$ditch['version'];
                            $result[$i]['size']     = bcmul($ditch['size'], 1024, 0);
                        }
                    }
                }

                unset($data);

                // 软件包记录更新
                if ($result) {
                    $model = M('DownDmain');
                    $down  = M('Down');

                    foreach ($result as $data) {
                        if ($model->save($data)) {
                            $downArray = [
                                'id'          => $data['id'],
                                'update_time' => time()
                            ];

                            $down->save($downArray);
                        }
                    }
                    
                    // 记录此次更新时间
                    F('DownUpTime_'.$this->siteid, date('Y-m-d H:i:s'));
                    $this->ajaxReturn('Operatton Success', 'EVAL');
                }
            }
        }

        $this->ajaxReturn('Operatton Fail', 'EVAL');
    }

    /**
     * 96u站点处理层
     *
     * 更新软件包版本、大小
     *
     * @param array $data 接口返回的软件包
     * @return void
     */
    private function _96u($data)
    {
        // 下载软件包
        $down = M('DownAddress')->field('did,url')->where(['isbusine' => 1])->select();

        if ($down) {
            $app = [];
            foreach ($down as $vars) {
                $appid = '';
                $info  = parse_url($vars['url']);
                $url   = substr($info['path'], 1);
                $exp   = explode('/', $url);
                $count = count($exp);

                // 是否为旧规则 3新规则 2旧规则
                if ($count == 3) {
                    $appid = intval(substr($exp[0], 3, 6));
                    // 渠道id
                    $app[$appid]['ditchid'] = intval(substr($exp[2], 3, 6));
                } elseif ($count == 2) {
                    // App包id
                    $appid = intval(substr($exp[0], 3, 4));
                }

                if ($appid) {
                    $app[$appid]['appid'] = $appid;
                    $app[$appid]['recordid'] = $vars['did'];
                }
            }

            if ($app) {
                // 站点包与API包进行比较
                $result = [];
                
                foreach ($data as $i => $value) {
                    if (isset($app[$value['id']])) {
                        if (isset($app[$value['id']]['ditchid'])) {
                            // 新数据处理
                            foreach ($value['ditch'] as $val) {
                                if ($val['ditchid'] == $app[$value['id']]['ditchid']) {
                                    $result[$i]['id']       = $app[$value['id']]['recordid'];
                                    $result[$i]['version']  = 'v'.$val['version'];
                                    $result[$i]['size']     = $val['size'].' MB';
                                }
                            }
                        } else {
                            // 旧数据兼容
                            $ditch = array_shift($value['ditch']);

                            $result[$i]['id']       = $app[$value['id']]['recordid'];
                            $result[$i]['version']  = 'v'.$ditch['version'];
                            $result[$i]['size']     = $ditch['size'].' MB';
                        }
                    }
                }
                
                unset($data);

                // 软件包记录更新
                if ($result) {
                    $model = M('DownDsoft');

                    foreach ($result as $data) {
                        $model->save($data);
                    }
                    
                    // 记录此次更新时间
                    F('DownUpTime_'.$this->siteid, date('Y-m-d H:i:s'));
                    $this->ajaxReturn('Operatton Success', 'EVAL');
                }
            }
        }

        $this->ajaxReturn('Operatton Fail', 'EVAL');
    }
}
