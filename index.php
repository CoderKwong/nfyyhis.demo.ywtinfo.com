<?php
//项目所有文件的入口文件
//防跳墙常量 
define('IS_INITPHP','http://api.hongzeit.com');
//关错错误输出
error_reporting(0);
//设置页面字符编码
header("Content-type: text/json; charset=utf-8");
//设置时区
date_default_timezone_set('Asia/Shanghai');

include './SinglePHP.class.php'; 
include './config.php';
include './yourCode.php';  

ini_set('soap.wsdl_cache_enabled','0');    //关闭WSDL缓存



SinglePHP::getInstance($config)->run();


?>
