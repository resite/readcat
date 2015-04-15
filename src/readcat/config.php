<?php
//base-------------------------------
//'m.'开头的是手机页面
if(strpos($_SERVER['SERVER_NAME'],'m.')===0){
    define('MOBILE',1);
}
define('DB_NAME','readcat');
define('DB_USER','root');
define('DB_PASSWORD','');
define('DB_HOST','localhost');
define('DB_PORT','3306');

define('DB_CHARSET','utf8');

define('CACHE_ON',false);
define('MEMCACHE_HOST','localhost');
define('MEMCACHE_PORT','11211');

define('ROOT_PATH',dirname(__FILE__).'/');
define('INCLUDES_PATH',ROOT_PATH.'includes/');
define('CONTENTS_PATH',ROOT_PATH.'contents/');
define('CDN_URL','');
if(defined('ADMIN')){
    define('VIEW_PATH',ROOT_PATH.'admin/view/');
    define('TEMPLATES_PATH',ROOT_PATH.'admin/templates/');
    define('SELECT_LIMIT',50);
}else{
    define('VIEW_PATH',ROOT_PATH.'view/');
    define('SELECT_LIMIT',25);
    
    if(defined('MOBILE')){
        define('TEMPLATES_PATH',ROOT_PATH.'m_templates/');
    }else{
        define('TEMPLATES_PATH',ROOT_PATH.'templates/');
    }
}


function __autoload($class){
    $s = $class{0};
    
    switch($s){
        case 'V':
            $file = VIEW_PATH.$class.'.php';
            break;
        default:
            $file = INCLUDES_PATH.$class.'.class.php';
            break;
    }
    if (is_file($file))
        return include($file);
    else
        throw new Exception("class [$class $file] not existed");
}

