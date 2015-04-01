<?php
require('config.php');
require(INCLUDES_PATH.'functions.php');

$_db = new db(array('database_name'=>DB_NAME,'username'=>DB_USER,'password'=>DB_PASSWORD,'server'=>DB_HOST,'port'=>DB_PORT));

if(CACHE_ON){
    $_cache = new Memcache;
    $_cache->connect(MEMCACHE_HOST, MEMCACHE_PORT) or die ("Could not connect");
}

$_view = empty($_GET['view'])?'Vindex':'V'.$_GET['view'];
$_entry = empty($_GET['entry'])?'entry':'entry_'.$_GET['entry'];

$viewInstance = new $_view();
if (!method_exists($viewInstance, $_entry))
    throw Exception("entry {$_view}->{$_entry} not existed");

call_user_func(array(&$viewInstance,$_entry));
