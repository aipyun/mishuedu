<?php

$config['base_url'] = '';
#开启调试模式
echo "<pre>";print_r($_SERVER);
echo "1<br>";
$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://':"http://";
define('HTTP_TYPE', $http_type);//定义当前域名使用协议是http还是https
define('URL', $http_type.$_SERVER['HTTP_HOST']);
define('OLD_URL', $http_type.$_SERVER['HTTP_HOST']);//老版本专用

?>