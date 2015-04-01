<?php
define('ADMIN','1');
require('../config.php');
require(INCLUDES_PATH.'functions.php');
require(ROOT_PATH.'view/View.php');

$_db = new db(array('database_name'=>DB_NAME,'username'=>DB_USER,'password'=>DB_PASSWORD,'server'=>DB_HOST,'port'=>DB_PORT));
//$_cache = new cache();
$_view = empty($_GET['view'])?'Vuser':'V'.$_GET['view'];
$_entry = empty($_GET['entry'])?'entry':'entry_'.$_GET['entry'];

$viewInstance = new $_view();

if(!$viewInstance->is_admin && $_entry != 'entry_login'){
    header('Location: /admin/index.php?view=user&entry=login');
}

if (!method_exists($viewInstance, $_entry))
    throw Exception("entry {$_view}->{$_entry} not existed");

call_user_func(array(&$viewInstance,$_entry));
