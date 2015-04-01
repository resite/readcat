<?php
class Vnode extends View{
    function entry(){
        $this->entry_node_list();
    }
    
    function entry_node_list(){
        $node_mod = model::load('node');
        $fields = array('node_id','keywords');
        if($_GET['my']){
            $node_list = $node_mod->select_nodes($fields,$_GET,$this->user_id,14);
        }else{
            $node_list = $node_mod->select_nodes($fields,$_GET,null,14);
        }
        $this->assign['node_list'] =$node_list;
        $this->show_page(SELECT_LIMIT*100);
        
        $node_mod->init('cate');
        $fields = array('cate_id','cate_name');
        $this->assign['cate_list'] = $node_mod->select($fields);
        $this->display('node_list');
    }
    
    function entry_subscribe(){
        $node_mod = model::load('node');
        $node_mod->init('user_node_relation');
        $res = $node_mod->insert(array('node_id'=>$_REQUEST['node_id'],'user_id'=>$this->user_id,'add_time'=>$_SERVER['REQUEST_TIME']));
        echo $res;
    }
    
    function entry_unsubscribe(){
        $node_mod = model::load('node');
        $node_mod->init('user_node_relation');
        $res = $node_mod->delete(array('node_id'=>$_REQUEST['node_id'],'user_id'=>$this->user_id,'add_time'=>$_SERVER['REQUEST_TIME']));
        echo $res;
    }
}