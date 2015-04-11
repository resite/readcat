<?php
class Vuser extends View{
    function __construct(){
        parent::__construct();
        if(!$this->user_id)
            header('Location: /');
    }
    
    function entry(){
        $this->entry_voted_feeds();
    }
    
    function entry_voted_feeds(){
        $feed_mod = model::load('feed');
        $fields = array('feed_id','url','title','top_image','ups','downs','add_time','domain','user_id');
        $this->assign['feed_list'] = $feed_mod->select_feeds($fields,$_GET,$this->user_id,'like');

        if(count($this->assign['feed_list']) == SELECT_LIMIT){
            $this->show_page(SELECT_LIMIT*100);
        }
        
        $fields = array('feed_id','title','ups');
        $where = array('order'=>'ups');
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        
        $this->display('feed_list');
    }
    
    function entry_collected_feeds(){
        $feed_mod = model::load('feed');
        $fields = array('feed_id','url','title','top_image','ups','downs','add_time','domain','user_id');
        $this->assign['feed_list'] = $feed_mod->select_feeds($fields,$_GET,$this->user_id,'collect');
        
        if(count($this->assign['feed_list']) == SELECT_LIMIT){
            $this->show_page(SELECT_LIMIT*100);
        }
        
        $fields = array('feed_id','title','ups');
        $where = array('order'=>'ups');
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        
        $this->display('feed_list');
    }
    
    function entry_manage(){
        $user_mod = model::load('user');
        $this->assign['user'] = $user_mod->get_cache($this->user_id);
        
        $feed_mod = model::load('feed');
        $fields = array('feed_id','title','ups');
        $where = array('order'=>'ups');
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        
        $this->display('user_manage');
    }
    
    function entry_logout(){
        $model = model::load('user');
        $model->logout();
        header('Location: /');
    }
    
    function entry_user_edit(){
        $model = model::load('user');
        $model->init('userinfo');
        $_POST['user_id'] = $this->user_id;
        if(is_post() && $model->edit($_POST)===false){
            $this->assign['message'] = $model->message;
        }
        
        $userinfo = $model->get($this->user_id);
        //如果没有用户信息，初始化
        if(!$userinfo){
            $model->insert(array('user_id'=>$user_id));
        }
        
        $this->assign['user'] = $userinfo;
        $this->display('user_edit');
    }
    
    function entry_password_edit(){
        $model = model::load('user');
        $_POST['user_id'] = $this->user_id;
        if(is_post() && $model->edit_password($_POST)===false){
            $this->assign['message'] = $model->message;
        }
        $this->display('password_edit');
    }
}