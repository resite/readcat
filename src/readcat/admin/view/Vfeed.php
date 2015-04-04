<?php
class Vfeed extends View{
    function __construct(){
        global $_entry;
        parent::__construct();
        if(!$this->is_admin && $_entry != 'entry_login')
            header('Location: /admin/index.php?view=user&entry=login');
    }
    
    function entry_feed_list(){
        $feed_mod = model::load('feed');
        $fields = array('feed_id','user_id','cate_id','url','title','status');
        $_GET['status'] = feed::FEED_STATUS_WAITING;
        $this->assign['feed_list'] = $feed_mod->select($fields,$_GET);
        $this->display('feed_list');
    }
    
    function entry_feed(){
        $feed_mod = model::load('feed');
        if(is_post()){
            $feed_mod->edit($_POST);
        }
        
        $this->assign['feed'] = $feed_mod->get($_GET);
        $this->display('feed_edit');
    }
    
    function entry_report_list(){
        $feed_mod = model::load('feed');
        $feed_mod->init('reports');
        $fields = array('report_id','report_type','aim_id');
        $this->assign['report_list'] = $feed_mod->select($fields,$_GET);
        $this->display('report_list');
    }
    
    function entry_comment(){
        $feed_mod = model::load('feed');
        $feed_mod->init('comments');
        
        if(is_post()){
            $feed_mod->edit($_POST);
        }
        
        $this->assign['comment'] = $feed_mod->get($_GET);
        $this->display('comment_edit');
    }
}