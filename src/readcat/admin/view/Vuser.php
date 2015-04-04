<?php
class Vuser extends View{
    function __construct(){
        global $_entry;
        parent::__construct();
        if(!$this->is_admin && $_entry != 'entry_login')
            header('Location: /admin/index.php?view=user&entry=login');
    }
    
    function entry(){
        $this->entry_user();
    }
    
    function entry_login(){
        $model = model::load('user');
        if(is_post() && $model->admin_login($_POST)){
            header('Location: /admin/index.php?view=user');
        }else{
            $this->assign['message']=$model->message;
        }
        
        $this->display('login');
    }
    
    function entry_logout(){
        $model = model::load('user');
        $model->logout();
        header('Location: /admin/index.php');
    }
    
    function entry_user(){
        $model = model::load('user');
        $fields = array('user_id','nickname','mob_phone','type_id');
        $this->assign['user_list'] = $model->select($fields,$_GET);
        $this->show_page($model->count($_GET));
        $this->display('user_list');
    }

    function entry_user_edit(){
        $model = model::load('user');
        
        if(is_post()&& !$model->edit($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        if($_GET['user_id'])
            $this->assign['user'] = $model->get($_GET);
            
        $this->display('user_edit');
    }
    
    function entry_admin(){
        $model = model::load('user');
        $fields = array('user_id','nickname','mob_phone','type_id');
        $this->assign['admin_list'] = $model->select($fields,$_GET);
        $this->show_page($model->count($_GET));
        $this->display('admin_list');
    }

    function entry_admin_edit(){
        $model = model::load('user');
        
        if(is_post() && !$model->edit($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        if($_GET['user_id'])
            $this->assign['admin'] = $model->get($_GET);
        $this->display('admin_edit');
    }
    
    function entry_admin_del(){
        $model = model::load('user');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=user&entry=admin');
    }
}