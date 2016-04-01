<?php
// +----------------------------------------------------------------------
// |  内容插入处理类  继承控制类使用某些方法
// +----------------------------------------------------------------------
// | Author: crohn <lllliuliu@163.com> 
// +----------------------------------------------------------------------
namespace Common\Library;
use Think\Think;
/**
 * 内容插入处理类 
 */
class InsertContentLibrary{

    //内容
    protected $content = '';

    //模板路径字段
    protected $path_field = 'path';

    //处理模式，默认替换 replace-替换 delete-删除
    protected $model = 'replace';

    /**
     * 视图实例对象
     * @var view
     * @access protected
     */    
    protected $view     =  null;

    /**
     * 初始化
     * @param array $params 参数
     * @return void
     */
    public function __construct($content) {
        //实例化视图类
        $this->view = Think::instance('Think\View');
        $this->content = $content;
    }

    /**
     * 已存在属性设置
     * @param string $k 
     * @param string $v 
     * @return void
     */
    public function setProperty($k, $v = null) {
        if(is_array($k)){
            foreach ($k as $k_t => $v_t) {
                $this->setProperty($k_t, $v_t);
            }
        }else{
            if(isset($this->$k)){
                $this->$k = $v;
            }else{
                return false;
            }             
        }   
    }

    /**
     * 处理
     * @param array $params 参数
     * @return void
     */
    public function init() {
        //检测
        if(!$this->check()) return false;        
        //文章插入处理
        $this->document();
        //内链插入处理
        $this->internalLink();
        //下载插入处理
        $this->down();
        //下载插入处理
        $this->package();
        //插入分页标识
        //$this-> insertPage();
        return $this->content;
    }

    /**
     * 检测
     * @return void 
    */
    public function check(){
        if(empty($this->content)) return false;
        return true;     
    }

    /**
     * 获取模板管理数据
     * @param string  $type 类型     
     * @param int  id 
     * @return void 
    */
    public function getTemplate($type , $id = 0){
        if(!$id){
            $rs =  M('Template')->where('type="'.$type.'"')->find();
        }else{
            $rs =  M('Template')->where('type="'.$type.'"')->getById($id);             
        }
        return $rs;
    }

    /**
     * 插入文章数据处理
     * @return void 
    */
    public function document(){
        $pattern = '/\{insc m=(?:"|&quot;)(\d+?)(?:"|&quot;) i=(?:"|&quot;)(\d+?)(?:"|&quot;)\}/i';  
        switch ($this->model) {
            case 'delete':
                $this->content = preg_replace($pattern, '', $this->content);
                break;                   
            default:
                $this->content = preg_replace_callback(
                                              $pattern,
                                              function($matches){
                                                  if(empty($matches[1]) || empty($matches[2])) return '';
                                                  //获取数据
                                                  $info = D('Document')->detail($matches[2]);
                                                  if(empty($info)) return '';
                                                  //获取模板数据
                                                  $template = $this->getTemplate('InsertArticle' , $matches[1]); 
                                                  if(empty($template)) $template = $this->getTemplate('InsertArticle');
                                                  //传入数据并解析模板
                                                  $this->view->assign('info',$info);
                                                  return $this->view->fetch(T($template[$this->path_field]),'','');
                                              },
                                              $this->content
                                          );  
                break;
        }  
     
    }

    /**
     * 插入下载数据处理
     * @return void 
    */
    public function down(){
        $pattern = '/\{insd m=(?:"|&quot;)(\d+?)(?:"|&quot;) i=(?:"|&quot;)(\d+?)(?:"|&quot;)\}/i';         
        switch ($this->model) {
            case 'delete':
                $this->content = preg_replace($pattern, '', $this->content);
                break;                   
            default:
                $this->content = preg_replace_callback(
                                                $pattern,
                                                function($matches){
                                                    if(empty($matches[1]) || empty($matches[2])) return '';
                                                    //获取数据
                                                    $info = D('Down')->downAll($matches[2]);
                                                    if(empty($info)) return '';
                                                    //获取模板数据
                                                    $template = $this->getTemplate('InsertDown' , $matches[1]); 
                                                    if(empty($template)) $template = $this->getTemplate('InsertDown');
                                                    //传入数据并解析模板
                                                    $this->view->assign('info',$info);
                                                    return $this->view->fetch(T($template[$this->path_field]),'','');
                                                },
                                                $this->content
                                            );   
                break;
        }  
    }

    /**
     * 插入礼包数据处理
     * @return void
     */
    public function package(){
        $pattern = '/\{insp m=(?:"|&quot;)(\d+?)(?:"|&quot;) i=(?:"|&quot;)(\d+?)(?:"|&quot;)\}/i';
        switch ($this->model) {
            case 'delete':
                $this->content = preg_replace($pattern, '', $this->content);
                break;
            default:
                $this->content = preg_replace_callback(
                    $pattern,
                    function($matches){
                        if(empty($matches[1]) || empty($matches[2])) return '';
                        //获取数据
                        $info = D('Package')->detail($matches[2]);
                        if(empty($info)) return '';
                        //获取模板数据
                        $template = $this->getTemplate('InsertPackage' , $matches[1]);
                        if(empty($template)) $template = $this->getTemplate('InsertPackage');
                        //传入数据并解析模板
                        $this->view->assign('info',$info);
                        return $this->view->fetch(T($template[$this->path_field]),'','');
                    },
                    $this->content
                );
                break;
        }
    }

    /**
     * 插入内链数据处理
     * @return void 
    */
    public function internalLink(){
        $pattern = '/\{insw i=(?:"|&quot;)(\d+?)(?:"|&quot;)\}/i';
        switch ($this->model) {
            case 'delete':
                $this->content = preg_replace($pattern, '', $this->content);
                break;                   
            default:
                $this->content = preg_replace_callback(
                                                $pattern,
                                                function($matches){
                                                    if(empty($matches[1])) return '';
                                                    //获取数据
                                                    $info = M('InternalLink')->getById($matches[1]);
                                                    if(empty($info)) return '';
                                                    //获取模板数据
                                                    $template = $this->getTemplate('InternalLink' ,$info['template_id']); 
                                                    if(empty($template)) $template = $this->getTemplate('InternalLink');
                                                    //传入数据并解析模板
                                                    preg_match_all('/(.+?)(?:([\n\r]+?|$))/', $info['content'], $cs);
                                                    foreach ($cs[1] as &$c) {
                                                        $c = explode('|', $c);
                                                    }
                                                    $this->view->assign('content',$cs[1]);
                                                    $this->view->assign('info',$info);
                                                    return $this->view->fetch(T($template[$this->path_field]),'','');
                                                },
                                                $this->content
                                            );   
                break;
        } 
    }

    /**
     * 描述：内容插入分页标识
     * Author:谭坚
     * Version:1.0.0
     * Modify Time:
     * Modify Author:
     */
    public function insertPage()
    {
         $this->content.="<div id=\"insert_page\"></div>";
    }
}
