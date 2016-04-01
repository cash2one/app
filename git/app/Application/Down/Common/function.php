<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/16
 * Time: 16:36
 */

/**
 * 作者:ganweili
 * 时间:2015/11/17
 * 描述:下载模块 down页面 更具传过来的ID查找出游戏类型（栏目形式）
 */
function get_game_typedown ($id){
$cateName=D('DownCategory')->where('id='.$id)->getField('title');
return $cateName;
}


