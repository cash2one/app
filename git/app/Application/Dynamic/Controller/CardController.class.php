<?php
// +----------------------------------------------------------------------
// | 动态WEB服务：
// |    礼包领取
// +----------------------------------------------------------------------
// |    基于APC扩展，PHP>=5.5请使用APCu扩展
// |    自定义自旋锁防止数据污染
// |    IP限制领取，可扩展添加验证码 
// |    重要：这里不能用D方法，因为外部用了扩展D模型的操作来维护缓存
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@crohn.com>
// +----------------------------------------------------------------------

namespace Dynamic\Controller;
use Think\Controller;

class CardController extends Controller {

    protected $pre = 'CARD:'; //全局前缀

    protected $ip_key = 'CARD:ip_'; //IP缓存前缀

    protected $lock_key = 'CARD:LOCK_'; //锁前缀

    protected $card_key = 'CARD:cards_'; //剩余领取卡号队列前缀
    protected $cache_key = 'CARD:cards_cache_';//缓冲池前缀 
    protected $cache_overtime = 10; //缓冲池超时存入时间 -秒
    protected $cache_overcount = 5; //缓冲池存储数据数目上限

    protected $all_count_key = 'CARD:all_count_'; //所有卡号总数缓存前缀

    protected $draw_key = 'CARD:draw_'; //已经领取卡号缓存前缀



    protected $apc_overtime = 1800; //缓存超时时间-秒

    /**
     * 初始化
     * @return void
     */
    protected function _initialize(){
        //结果关闭trace

        C('SHOW_PAGE_TRACE', false); 
        //配置不缓存就需要读取数据库，额外损耗性能
       //header('Access-Control-Allow-Origin:http://libao.liuliu.com');
        //读取站点配置 
        // $config = api('Config/lists');
        // C($config); 
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
     * 领取礼包卡号
     * @return string  领取的卡号
     */
    public function drawCard(){
        $callback = I('callback');

        $id = I('id');
        if (empty($id) || !is_numeric($id)) {
            echo  json_encode(array('error'=>'params error'));   
            return;
        }

        /***锁***/
        $lock_key = $this->lock_key. $id;
        $this->getLock($lock_key);

        /***IP检测***/
        $ip = get_client_ip(0, true);
        $ip_key = $this->ip_key . $id;
        //不存在则获取
        if(!apc_exists($ip_key)){
            $exists_ip = M('Card')->where('did='. $id . ' AND draw_status=1')->field('ip,number')->select();
             apc_store($ip_key, $exists_ip, $this->apc_overtime);
        }
        $exists_ip = apc_fetch($ip_key);
        if($exists_ip){
            foreach ($exists_ip as $v) {
                if($v['ip']==$ip){
                    $this->releaseLock($lock_key);
                    $rs = json_encode(array('error'=>'同一个IP只能领取一次同样的礼包，请您领取其他礼包！'));
                    echo  $callback ? $callback.'('.$rs.');' : $rs;
                    return;                      
                }
            }
        }

        /***卡号获取***/
        //获取卡号
        $card_key = $this->card_key . $id; //剩余领取卡号队列
        $cache_key = $this->cache_key . $id; //缓冲池 每5个请求存储一次到数据库
        //不存在则获取
        if(!apc_exists($card_key)) $this->setCardsAPC($id, $lock_key);    // 已经抢到锁
        $cards = apc_fetch($card_key);
        if(!$cards){
            $this->releaseLock($lock_key);
            $rs = json_encode(array('error'=>'礼包已经发完，请领取其他礼包'));
            echo  $callback ? $callback.'('.$rs.');' : $rs;
            return;               
        }
        $current = $cards[count($cards)-1];
        unset($cards[count($cards)-1]); 
        apc_store($card_key, $cards, $this->apc_overtime);  //数据保持超时设置防止内存占用过多

        //添加缓冲队列
        $current['draw_time'] = time(); //设置缓存时间
        $current['ip'] = $ip; //设置IP
        $current['draw_status'] = 1; //设置领取状态
        $cache = apc_fetch($cache_key);
        if(!$cache){
            $cache = array($current);
            apc_store($cache_key, $cache);
            $overtime = false;
        }else{
            $cache_last = $cache[count($cache)-1]; //最后一个缓冲数据
            $cache[] = $current;
            $overtime = ( (time() - $cache_last['draw_time'])>$this->cache_overtime );  //超时设置          
        }
        //大于超时时间或者大于存储上限数据就存储数据并清空缓冲池
        if( $overtime || count($cache)>=$this->cache_overcount){
            //update
            $this->updateCards($cache);
            apc_delete($cache_key); //清空缓冲池
            //刷新已领取数据缓存
            $this->setlistsDraw($id, $lock_key);
        }else{
            $cache = apc_store($cache_key, $cache);  //缓冲数据不设超时，PHP维护完整性          
        }
        
        //缓存领取记录
        $exists_ip[] = array('ip'=>$ip,'number'=>$current['number']);
        
        //更新ip队列
        apc_store($ip_key, $exists_ip, $this->apc_overtime);

        //释放锁
        $this->releaseLock($lock_key);
        //返回数据
        $rs = json_encode(array('row'=>$current['number']));
        echo  $callback ? $callback.'('.$rs.');' : $rs;
    }

    /**
     * 修改卡号状态
     * @param array  $cards 卡号数组
     * @return array
     */
    private function updateCards($cards){
        foreach ($cards as $card) {
            M('Card')->save($card);
        }
    }

    /**
     * 争抢锁 自旋锁 5秒超时
     * @param string  $key 锁key
     * @return array
     */
    protected function getLock($key){
        $callback = I('callback');
        $i=0;
        while(apc_add($key, 1, 5)){
            $i++;
            if($i>200){
                $rs = json_encode(array('error'=>'服务器繁忙！请然后重试！'));
                echo  $callback ? $callback.'('.$rs.');' : $rs;
                return '服务器繁忙！请然后重试！';
            }
            usleep(mt_rand(10000,50000));
        }
    }

    /**
     * 释放锁
     * @param string  $key 锁key
     * @return array
     */
    protected function releaseLock($key){
        return apc_delete($key);
    }

    /**
     * 设置剩余卡号队列
     * @param integer  $id 数据id
     * @param string  $lock 是否已经争抢到锁
     * @return void
     */
    public function setCardsAPC($id,$lock = false){
        if (empty($id) || !is_numeric($id))  return;
        $card_key = $this->card_key . $id; //剩余领取卡号队列
        $cache_key = $this->cache_key . $id; //缓冲池
        //没有带锁进入则争抢锁 
        if(!$lock){
            $lock_key = $this->lock_key. $id; //锁
            $this->getLock($lock_key);
        }
        //提交缓冲池数据并清空
        $cache = apc_fetch($cache_key);
        if($cache){
            $this->updateCards($cache);
            apc_delete($cache_key);                
        }
        //获取数据
        $cards = M('Card')->where('did='. $id . ' AND draw_status=0')->field('id,number')->select();
        //存入内存
        apc_store($card_key, $cards, $this->apc_overtime); 
        //没有带锁进入则释放锁 
        if(!$lock) $this->releaseLock($lock_key);
    }

    /**
     * 获取剩余卡号数
     * @param integer  $id 数据id
     * @return integer
     */
    public function getCardsCount($id){
        if (empty($id) || !is_numeric($id))  return;
        $card_key = $this->card_key . $id; //剩余领取卡号队列
        $cache_key = $this->cache_key . $id; //缓冲池
        //如果不存在则生成
        if(!apc_exists($card_key)) $this->setCardsAPC($id);
        //获取卡号队列
        $cards = apc_fetch($card_key); 
        if(!$cards)  return 0; 
        $count = $cards ? count($cards) : 0;
        return $count;
    }

    /**
     * 获取卡号总数
     * @param integer  $id 数据id
     * @return integer
     */
    public function getAllCardsCount($id){
        if (empty($id) || !is_numeric($id))  return;
        $key = $this->all_count_key . $id;
        //如果不存在则生成
        if(!apc_exists($key))  $this->setAllCardsCount($id);
        $count = apc_fetch($key);
        return $count;
    }

    /**
     * 设置卡号总数
     * @param integer  $id 数据id
     * @return void
     */
    public function setAllCardsCount($id){
        if (empty($id) || !is_numeric($id))  return;
        $key = $this->all_count_key . $id;
        $count = M('Card')->where('did='. $id)->count();
        apc_store($key, $count, $this->apc_overtime);
        return $count;
    }

    /**
     * 批量获取卡号总数
     * @param array  $ids 数据id数组
     * @return integer
     */
    public function batchAllCardsCount($ids){
        if(!is_array($ids)) return;        
        foreach ($ids as $id => $value) {
            $ids[$id] = $this->getAllCardsCount($id);
        }
        return $ids;
    }


    /**
     * 批量获取剩余卡号数
     * @param array  $ids 数据id数组
     * @return integer
     */
    public function batchCardsCount($ids){
        if(!is_array($ids)) return;        
        foreach ($ids as $id => $value) {
            $ids[$id] = $this->getCardsCount($id);
        }
        return $ids;
    }

    /**
     * ajax入口
     * @return integer
     */
    public function batchAjax(){
        $surplus_ids = I('surplus_ids');
        $all_ids = I('all_ids');
        if(!is_array($surplus_ids)) return;        
        if(!is_array($all_ids)) return; 

        $surplus_ids = $this->batchCardsCount($surplus_ids);
        $all_ids = $this->batchAllCardsCount($all_ids);
        $rs = array('surplus_ids'=>$surplus_ids, 'all_ids'=>$all_ids);

        $callback = I('callback');
        echo  $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);         
    }

    /**
     * 设置领取信息列表
     * @param integer  $id 数据id
     * @return void
     */
    public function setlistsDraw($id,$lock = false){
        if (empty($id) || !is_numeric($id))  return;
        $draw_key = $this->draw_key . $id; //已经领取卡号
        $cache_key = $this->cache_key . $id; //缓冲池
        //没有带锁进入则争抢锁 
        if(!$lock){
            $lock_key = $this->lock_key. $id; //锁
            $this->getLock($lock_key);
        }
        //获取数据
        $cards = M('Card')->where('did='. $id . ' AND draw_status=1')->field('id,number,ip,draw_time')->order('draw_time ASC')->select();
        //存入内存
        apc_store($draw_key, $cards, $this->apc_overtime); 
        //没有带锁进入则释放锁 
        if(!$lock) $this->releaseLock($lock_key);
    } 

    /**
     * 获取已领取卡号信息
     * @param integer  $id 数据id
     * @return integer
     */
    public function getlistsDraw($id){
        if (empty($id) || !is_numeric($id))  return;
        $draw_key = $this->draw_key . $id; //已经领取卡号
        $cache_key = $this->cache_key . $id; //缓冲池
        //如果不存在则生成
        if(!apc_exists($draw_key)) $this->setlistsDraw($id);
        //获取卡号队列
        $draws = apc_fetch($draw_key);
        //获取缓冲池数据
        $cache = apc_fetch($cache_key);
        $rs = array_merge((array)$draws,(array)$cache);
        $rs = array_filter($rs);
        //只返回最新10条
        $rs = array_slice($rs, -10, 10);
        //号码隐藏
        foreach ($rs as $key => $value) {
            $value['number'] = '****' . substr($value['number'], -1, 1);
            $rs[$key] = $value;
        }

        $callback = I('callback');
        echo $callback ? $callback.'('.json_encode(array_filter($rs)).');' : json_encode(array_filter($rs));         
    }


    /**
     * 删除数据时清除所有相关缓存
     * @param integer  $id 数据id
     * @return void
     */
    public function clearById($id){
        if (empty($id) || !is_numeric($id))  return;
        //删除IP缓存
        apc_delete($this->ip_key . $id);
        //删除锁缓存
        apc_delete($this->lock_key . $id);
        //删除剩余领取卡号队列缓存
        apc_delete($this->card_key . $id);
        //删除缓冲池缓存
        apc_delete($this->cache_key . $id);
        //删除所有卡号总数缓存
        apc_delete($this->all_count_key . $id);
        //删除已领取卡号
        apc_delete($this->draw_key . $id);
    }


    /**
     * 查看当前APC 使用状态
     * @return void
     */
    // public function information(){
    //     var_dump(apc_cache_info());
    // }

    /**
     * 清楚缓存
     * @return void
     */
    // public function clear(){
    //     apc_clear_cache('user');
    // }
    
    /**
     * 获取我领取的卡号
     * @param integer $id 数据id
     * @return integer
     * @date: 2015-7-1
     * @author: liujun
    */
	public function myCard($id){
    	$rs = array('status'=>-1,'number'=>'');
    	$callback = I('callback');
    	$isQrCode = I('qrCode');//是否生成二维码
    	$id = I('id');
    	if (empty($id) || !is_numeric($id)) {
    		echo  json_encode(array('error'=>'params error'));
    		return;
    	}
    	
    	$ip = get_client_ip(0, true);
    	$ip_key = $this->ip_key . $id;

    	$exists_ip = apc_fetch($ip_key);//获取缓存区卡号信息
    	if($exists_ip){
    		foreach ($exists_ip as $v) {
    			if($v['ip'] == $ip){
    				if(!empty($v['number'])){
	    				$rs['status'] = 1;
	    				$rs['number'] = $v['number'];
    				}
    				break;
    			}
    		}
    	}
    	//缓存区卡号不存在 则从数据库检索
    	if($rs['status'] != '1'){
    		$card = M('Card')->field('number,ip')->where(array('did'=>$id,'ip'=>$ip))->find();
    		if($card){
    			$rs['status'] = 1;
    			$rs['number'] = $card['number'];
    			
    			//将结果写入缓存：更新ip队列
    			$exists_ip[] = array('ip'=>$card['ip'],'number'=>$card['number']);
    			apc_store($ip_key, $exists_ip, $this->apc_overtime);
    		}
    	}
    	
    	echo $callback ? $callback.'('.json_encode($rs).');' : json_encode($rs);
    }

    /**
     * 作者:肖书成
     * 时间:2015/10/29
     * 描述:Ajax调用的API接口,发生错误返回 false
     */
    protected function API_false(){
        $this->API_result(false);
        exit;
    }

    /**
     * 作者:肖书成
     * 时间：2015/10/29
     * 描述: Ajax调用的API接口,最终结果输出
     * @param $val
     */
    protected function API_result($val){
        $callback   =   I('callback');
        echo $callback ? $callback.'('.json_encode($val).');' : json_encode($val);
    }

    /**
     * 作者:肖书成
     * 日期:2015/10/31
     * 描述:获取剩余卡号数;
     */
    public function API_surplus(){
        //接收并验证参数
        $surplus_ids    = I('surplus_ids');
        if(!is_array($surplus_ids)) $this->API_false();

        //获取剩余卡号数
        $surplus        = $this->batchCardsCount($surplus_ids);

        //结果返回
        $this->API_result($surplus);
    }
}