<?php
class sys_config extends model{
    const REPORT_TYPE_COMMENT = 0;
    
    function __construct(){
        $this->init();
        parent::__construct();
    }
    
    function init($table=''){
        switch($table){
        case 'reports':
            $this->table = 'reports';
            $this->pkey = 'report_id';
            $this->fields = array('report_id'=>'','aim_id'=>'','report_type'=>'','user_id'=>'','add_time'=>'');
        case 'sys_config':
        default:
            $this->table = 'sys_config';
            $this->pkey = 'sys_config_id';
            $this->fields = array('sys_config_id'=>'','v'=>'');
            break;
        }
    }
}