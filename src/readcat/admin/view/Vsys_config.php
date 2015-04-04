<?php
class Vsys_config extends View{
    function __construct(){
        parent::__construct();
        if(!$this->is_admin)
            header('Location: /');
    }
    
    function entry(){
        $this->entry_sys_config();
    }
    
    function entry_sys_config(){
        $model = model::load('sys_config');
        $this->assign['sys_config_list'] = $model->select('*',$_GET);
        $this->show_page($model->count($_GET));
        $this->display('sys_config_list');
    }

    function entry_sys_config_edit(){
        $model = model::load('sys_config');
        
        if(is_post()){
            if($_POST['sys_config_id'] && !$model->insert($_POST)){
                $this->assign['message'] = $model->message;
            }
            if($_GET['sys_config_id'] && !$model->update($_POST)){
                $this->assign['message'] = $model->message;
            }
        }
        
        if($_GET['sys_config_id'])
            $this->assign['sys_config'] = $model->get($_GET);
            
        $this->display('sys_config_edit');
    }
    
    function entry_sys_config_del(){
        $model = model::load('sys_config');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=sys_config');
    }
}