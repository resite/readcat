<?php
class sys_config extends model{
    function __construct(){
        $this->init();
        parent::__construct();
    }
    
    function init($table=''){
        switch($table){
        case 'sys_config':
        default:
            $this->table = 'sys_config';
            $this->pkey = 'sys_config_id';
            $this->fields = array('sys_config_id'=>'','v'=>'');
            break;
        }
    }
}