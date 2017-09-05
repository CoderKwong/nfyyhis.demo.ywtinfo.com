<?php
$config = array(
    'APP_PATH'    => './App/',       #APP业务代码文件夹
	'DB_HOST'     => 'rm-wz941i5qk052i7g15o.mysql.rds.aliyuncs.com',    #数据库主机地址
//    'DB_HOST'     => 'localhost',    #数据库主机地址
	'DB_PORT'     => '3306',         #数据库端口，默认为3306
	 'DB_USER'     => 'gdfangsi_2017',         #数据库用户名
	 'DB_PWD'      => 'gdfangsi@2017',         #数据库密码
//	'DB_USER'     => 'root',         #数据库用户名
//	'DB_PWD'      => 'root',         #数据库密码
	'DB_NAME'     => 'gdhistest_2017',    #数据库名
    'DB_CHARSET'  => 'utf8',         #数据库编码，默认utf8
    'TABLEPRE'  => 'hz_',         #表名前缀, 同一数据库安装多个论坛请修改此处
	'PATH_MOD'    => 'NORMAL',       #路由方式，支持NORMAL和PATHINFO，默认NORMAL
    'USE_SESSION' => false,           #是否开启session，默认false
	'HIS_URL'    => 'http://icare.clh.com:81/ws/',       #api与HIS同步的接口
	'APPKEY'  => '20151015hz',
	'HOSPITALID' =>'1051',
	'maxRegDays' =>'7',
	'PushServerIp' =>'127.0.0.1',
	'PushServerPort' =>'9999',
	'PushAppId' =>'1',
	'PushVersion' =>'1', 
	'AppDDPushIP' =>'116.254.222.90', 
	'AppVersion' =>'1.0', 
	'WebVersion' =>'1.0', 
	'SmsApi'    => 'http://127.0.0.1/Server/SMS_Send.aspx?sn=SDK-NFY-020-00011&pwd=NFYYYYGHAPP',       #sms api 短信接口 121.14.17.208
);
?>