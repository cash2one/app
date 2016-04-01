<?php
namespace Admin\Controller;
use Think\Controller;
use Common\PinYin;
use User\Api\UserApi;

/**
 * 数据库操作控制器
 * @author wwei <wangwei@163.com>
 */
class SqlInstallController extends Controller{

    /**
     * 初始化 设定数据库配置
     * @return void
     */
    public function __construct(){
        header("Content-type:text/html;charset=utf-8");
        //设置允许内存
        ini_set("memory_limit","512M");
        set_time_limit('3600');
        C('SHOW_PAGE_TRACE', true);
    }

    /**
     * 数据库字段更新
     * @param url   admin.php?s=/SqlInstall/run.html
     * @return string
     */
    public function run($dblang='utf8'){
        //获得数据库版本信息
        $rs = mysql_query("SELECT VERSION();");
        $row = mysql_fetch_array($rs);
        $mysqlVersions = explode('.',trim($row[0]));
        $mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];
        mysql_query("SET NAMES '$dblang',character_set_client=binary,sql_mode='';");

        if($mysqlVersion >= 4.1)
        {
            $sql4tmp = "ENGINE=MyISAM DEFAULT CHARSET=".$dblang;
        }
        //创建数据表（字段）
        $query = '';
        $dbprefix = C('DB_PREFIX');
        $fp = fopen('./Public/static/sql-dftables.txt','r');

        while(!feof($fp))
        {
            $line = rtrim(fgets($fp,1024));
            if(preg_match("#;$#", $line))
            {
                $query .= $line."\n";
                $query = str_replace('#@__',$dbprefix,$query);
                if($mysqlVersion < 4.1)
                {
                    $rs = M()->execute($query);
                } else {
                    if(preg_match('#CREATE#i', $query))
                    {
                        $rs = M()->execute(preg_replace("#TYPE=MyISAM#i",$sql4tmp,$query));
                    }
                    else if(preg_match('#ALTER#i', $query)){
                        //获取表名与字段名
                        $query_arr = explode(' ', $query);
                        $isfield = M()->query("Describe $query_arr[2] $query_arr[4]");
                        if($isfield){
                            continue;
                        }
                        $rs = M()->execute($query);
                    }
                    else
                    {
                        //$rs = M()->query($query);
                    }
                }
                $query='';
            } else if(!preg_match("#^(\/\/|--)#", $line))
            {
                $query .= $line;
            }
        }
        fclose($fp);

        //导入默认数据
        $query = '';
        $fp = fopen('./Public/static/sql-dfdata.txt','r');
        while(!feof($fp))
        {
            $line = rtrim(fgets($fp, 1024));
            if(preg_match("#;$#", $line))
            {
                $query .= $line;
                $query = str_replace('#@__',$dbprefix,$query);
                if($mysqlVersion < 4.1){
                    $rs = M()->execute($query);
                } else{
                    //获取插入字段与值得对应关系
                    $pattern = '/\s*INSERT\s*INTO\s*([a-zA-Z_`]+)\s*\((.*)\)\s*VALUES\s*\((.*)\)/is';

                    preg_match($pattern, $query, $m);
                    $m[1] = str_replace('`' , '', trim($m[1]));
                    $m[2] = str_replace('`' , '', trim($m[2]));

                    $field_arr = explode(',', $m[2]);
                    foreach($field_arr as $v){
                        $field_data[] = trim($v);
                    }
                    $value_arr = preg_split("/,\s/", $m[3]);
                    $map = array_combine($field_data, $value_arr);
                    array_shift($map);//删除主键
                    $data_map = array();
                    $where = '1=1';
                    foreach($map as $k=>$v){
                        if($v=="''"){
                            continue;
                        }
                        if(preg_match('/\'(.*)\'/is', $v)){
                            $data_map[$k] = preg_replace('/\'(.*)\'/is', '$1', $v);
                        }else{
                            $data_map[$k] = intval($v);
                        }
                        $where .= ' and '.$k.'='.$v;
                    }
                    if(!empty($data_map)){
                        $select_data = M()->table($m[1])->where($where)->select();
                        //echo M()->getLastSql();
                    }
                    if(!empty($select_data)){
                        continue;
                    }
                    $rs = M()->execute(str_replace('#~lang~#',$dblang,$query));
                }
                $query='';
            } else if(!preg_match("#^(\/\/|--)#", $line))
            {
                $query .= $line;
            }
        }
        fclose($fp);

        echo '更新成功！';
    }

}
