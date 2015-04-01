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
    
    function entry_payment(){
        $model = model::load('payment');
        $fields = array('payment_id','payment_name');
        $this->assign['payment_list'] = $model->select($fields,$_GET);
        $this->show_page($model->count($_GET));
        $this->display('payment_list');
    }
    
    function entry_payment_edit(){
        $model = model::load('payment');
        
        if(is_post()){
            if($_POST['payment_id'] && !$model->insert($_POST)){
                $this->assign['message'] = $model->message;
            }
            if($_GET['payment_id'] && !$model->update($_POST)){
                $this->assign['message'] = $model->message;
            }
        }
        
        $this->assign['payment'] = $model->get($_GET['payment_id']);
        $this->display('payment_edit');
    }
    
    function entry_payment_del(){
        $model = model::load('payment');
        $model->delete($_GET);
        header('Locatioin: /admin/index.php?view=sys_config&entry=payment');
    }
    
    function entry_article_cate(){
        $model = model::load('sys_config');
        $model->init('article_cate');
        $this->assign['article_cate_list'] = $model->select('*',$_GET);
        $this->display('article_cate_list');
    }
    
    function entry_article_cate_edit(){
        $model = model::load('sys_config');
        $model->init('article_cate');
        
        if(is_post() && !$model->edit($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        $this->assign['article_cate'] = $model->get($_GET['article_cate_id']);
        $this->display('article_cate_edit');
    }
    
    function entry_article_cate_del(){
        $model = model::load('sys_config');
        $model->init('article_cate');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=sys_config&entry=article_cate');
    }
    
    function entry_article(){
        $model = model::load('sys_config');
        $model->init('article');
        $fields = array('article_id','cate_id','user_id','title','add_time');
        $this->assign['article_list'] = $model->select($fields,$_GET,'article_id DESC');
        
        $this->show_page($model->count($_GET));
        $this->display('article_list');
    }
    
    function entry_article_edit(){
        $model = model::load('sys_config');
        $model->init('article');
        
        $_POST['user_id'] = $this->user_id;
        if(is_post() && !$model->edit_article($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        $this->assign['article'] = $model->get_article($_GET['article_id']);
        $model->init('article_cate');
        $this->assign['article_cate'] = $model->make_assoc($model->db->select($model->table,array('article_cate_id','cate_name')),'cate_name');
        $this->display('article_edit');
    }
    
    function entry_article_del(){
        $model = model::load('sys_config');
        $model->init('article');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=sys_config&entry=article');
    }
    
    function entry_industry(){
        $model = model::load('sys_config');
        $model->init('industry');
        $this->assign['industry'] = $model->make_assoc($model->db->select($model->table,array('industry_id','industry_name'),array('parent_id'=>0)),'industry_name');
        $this->display('industry_list');
    }
    
    function entry_industry_edit(){
        $model = model::load('sys_config');
        $model->init('industry');
        
        if(is_post() && !$model->edit($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        if($_GET['purpose'] == 'edit'){
            $this->assign['industry'] = $model->get($_GET['industry_id']);
        }else{
            $_GET['parent_id'] = $_GET['industry_id'];
        }
        $this->display('industry_edit');
    }
    
    function entry_industry_del(){
        $model = model::load('sys_config');
        $model->init('industry');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=sys_config&entry=industry');
    }
    
    function entry_region(){
        $model = model::load('sys_config');
        $model->init('region');
        $this->assign['region'] = $model->make_assoc($model->db->select($model->table,array('region_id','region_name'),array('parent_id'=>0)),'region_name');
        $this->display('region_list');
    }
    
    function entry_region_edit(){
        $model = model::load('sys_config');
        $model->init('region');
        
        if(is_post() && !$model->edit($_POST)){
            $this->assign['message'] = $model->message;
        }
        
        if($_GET['purpose'] == 'edit'){
            $this->assign['region'] = $model->get($_GET['region_id']);
        }else{
            $_GET['parent_id'] = $_GET['region_id'];
        }
        $this->display('region_edit');
    }
    
    function entry_region_del(){
        $model = model::load('sys_config');
        $model->init('region');
        $model->delete($_GET);
        header('Location: /admin/index.php?view=sys_config&entry=region');
    }
}