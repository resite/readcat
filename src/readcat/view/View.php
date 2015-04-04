<?php
class View{
    var $user_id,$is_admin,$session,$assign=array();

    function __construct(){
        //todo 定义SEO，过滤非正常输入
        //$this->session = new SessionStorageDb();
        //$this->session->setConf(DB_HOST.':'.DB_PORT, DB_USER, DB_PASSWORD, DB_NAME, 'utf8');
        //$this->session->execute();
        
        session_start();
        $this->user_id = $_SESSION['user_id'];
        $this->is_admin = isset($_SESSION['is_admin'])?1:0;
        
        $sys_model = model::load('sys_config');
        $sys_config = $sys_model->make_assoc($sys_model->db->select($sys_model->table,array($sys_model->pkey,'v')),'v');
        $this->assign['sys_config'] = $sys_config;

        //if($sys_config['rewrite'])
        //    define('REWRITE',1);
        
    }
    
    function display($tpl){
        global $_view,$_entry;
        $nickname = $_COOKIE['nickname'];
        extract($this->assign);
        include TEMPLATES_PATH.$tpl.'.html';
    }
    
    function show_page($total = 0, $page_size = SELECT_LIMIT){
        $page_get = html::query_string(array('page'));
        
        $page = intval($_GET['page']);
        if($page < 1) $page=1;
        if($total < 0) $page = 0;
        
        $total_page = ceil($total / $page_size);
        if($total_page<1) $total_page=1;
        
        if($page>$total_page) $page=$total_page;
        
        $page_start = $page-3;
        $page_end = $page+3;
        if($page_start<1){
            $page_end = $page_end + (1-$page_start);
            $page_start=1;
        }
        if($page_end>$total_page){
            $page_start = $page_start-($page_end-$total_page);
            $page_end = $total_page;
            if($page_start<1) $page_start=1;
        }
        
        $page_arr = array('pre' => $page>1?$page-1:'');
        $page_arr['next'] = $page==$total_page?'':$page+1;
        $page_arr['page'] = $page;
        $page_arr['pages'] = range($page_start,$page_end);
        $page_arr['total'] = $total_page;
        $page_arr['get'] = $page_get;
        $this->assign['page'] = $page_arr;
    }
}