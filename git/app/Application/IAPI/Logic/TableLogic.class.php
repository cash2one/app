<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace IAPI\Logic;
use Think\Model;

/**
 * 文档模型逻辑层公共模型
 * 所有逻辑层模型都需要继承此模型
 */
class TableLogic extends Model {

    /* 自动验证规则 */
    protected $_validate = array(
        array('name', 'require', '字段名必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '/^[a-zA-Z][\w_]{1,29}$/', '字段名不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('field', 'require', '字段定义必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

	 // 是否自动检测数据表字段信息
    protected $autoCheckFields  =   false;

	protected $table_name;

    /**
     * 构造函数
     * @param string $name 模型名称
     */
    public function __construct($name = '') {
		$prefix = C('DB_PREFIX');

		$this->table_name = $name;
	
        parent::__construct(ltrim($name, $prefix), $prefix);
    }

    /**
     * 新建字段
     * @return boolean
     */
	public function addField(){
		  //获取默认值
        if($this->value === ''){
            $default = '';
        }elseif (is_numeric($this->value)){
            $default = ' DEFAULT '.$this->value;
        }elseif (is_string($this->value)){
            $default = ' DEFAULT \''.$this->value.'\'';
        }else {
            $default = '';
        }
		
		$sql = <<<sql
                ALTER TABLE `{$this->table_name}`
ADD COLUMN `{$this->data['name']}`  {$this->field} {$default} COMMENT '{$this->title}';
sql;

        $res = M()->execute($sql);
        return $res !== false;
	}
}
