<?php
class Vnode extends View{
    function __construct(){
        parent::__construct();
        
        global $_entry;
        
        $login_entry = array('entry_add_node');
        if(in_array($_entry,$login_entry)){
            if(!$this->user_id){
                header('Location: /index.php?view=index&entry=login');
                exit;
            }
            $user_mod = model::load('user');
            $user = $user_mod->get_cache($this->user_id);

            if($user['type_id'] == user::TYPE_BLOCKED){
                $this->assign['message'] ='½ûÖ¹²Ù×÷';
                $this->display('message');
                exit;
            }
        }
    }
    
    function entry(){
        $this->entry_node_list();
    }
    
    function entry_node_list(){
        $node_mod = model::load('node');
        $fields = array('node_id','alias_id','node_name');
        if($_GET['my']){
            $node_list = $node_mod->select_nodes($fields,$_GET,$this->user_id,14);
        }else{
            $node_list = $node_mod->select_nodes($fields,$_GET,null,14);
        }
        $this->assign['node_list'] =$node_list;
        if(count($node_list) == 14){
            $this->show_page(14*100);
        }
        
        $node_mod->init('cate');
        $fields = array('cate_id','cate_name');
        $this->assign['cate_list'] = $node_mod->select($fields);
        $this->display('node_list');
    }
    
    function entry_add_node(){
        $node_mod = model::load('node');
        if(is_post()){
            $node_id = $node_mod->add_node($_POST,$this->user_id);
            if($node_id){
                header('Location: /index.php?view=feed&node_id='.$node_id);
            }else{
                $this->assign['message'] = $node_mod->message;
                $this->display('message');
            }
        }else{
            $this->display('edit_node');
        }
    }
    
    function entry_subscribe(){
        $node_id = intval($_REQUEST['node_id']);
        if($node_id <= 0)
            return;
        
        $node_mod = model::load('node');
        $node_mod->init('user_node_relation');
        $res = $node_mod->insert(array('node_id'=>$node_id,'user_id'=>$this->user_id,'add_time'=>$_SERVER['REQUEST_TIME']));
        $node_mod->delete_select_cache(array('node_id'),array('user_id'=>$this->user_id),null,5000);
        echo $res;
    }
    
    function entry_unsubscribe(){
        $node_id = intval($_REQUEST['node_id']);
        if($node_id <= 0)
            return;
        
        $node_mod = model::load('node');
        $node_mod->init('user_node_relation');
        $res = $node_mod->delete(array('node_id'=>$node_id,'user_id'=>$this->user_id));
        $node_mod->delete_select_cache(array('node_id'),array('user_id'=>$this->user_id),null,5000);
        echo $res;
    }
}