<?php
class Vnode extends View{
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