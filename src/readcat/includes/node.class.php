<?php
class node extends model{
    const NODE_TYPE_SEARCH = 0;
    const NODE_TYPE_SELECT = 1;
    const NODE_TYPE_USER = 2;
    
    static function node_type($id=null){
        $type = array(self::NOTE_TYPE_SEARCH => '搜索', self::NODE_TYPE_SELECT=>'精选', self::NODE_TYPE_USER=>'普通');
        if ($id == null) {
            return $type;
        } else {
            return $type[$id];
        }
    }
    
    function __construct(){
        $this->init();
        parent::__construct();
    }
    
    function init($table=''){
        if($this->table === $table) return;
        switch($table){
        case 'cate':
            $this->table = 'cate';
            $this->pkey = 'cate_id';
            $this->fields = array('cate_id'=>'','cate_name'=>'');
            break;
        case 'user_node_relation':
            $this->table = 'user_node_relation';
            $this->pkey = 'relation_id';
            $this->fields = array('relation_id'=>'','user_id'=>'','node_id'=>'','add_time'=>'');
            break;
        case 'nodes':
        default:
            $this->table = 'nodes';
            $this->pkey = 'node_id';
            $this->fields = array('node_id'=>'','cate_id'=>'','node_name'=>'','keywords'=>'','alias_id'=>'','type_id'=>'');
        }
    }
    
    function select_nodes($fields, $where = null, $user_id=null, $size=null){
        if($where['q']){
            $where['LIKE']['keywords%[~]']=strtolower($where['q']);
        }
        if($user_id){
            $join = array('[<]user_node_relation'=>'node_id');
            $where['AND']['user_id'] = $user_id;
        }
        
        return $this->select_cache($fields,$where,$order,$size,$join);
    }
    
    function select_indirect_nodes($node_id){
        if(!$node_id) return;
        
        $this->init('user_node_relation');
        $nodes = $this->select_cache(array('user_id'),array('node_id'=>$node_id,),'relation_id DESC',20);
        if(!$nodes) return;
        $user_ids = array();
        foreach($nodes as $v){
            $user_ids[] = $v['user_id'];
        }
        
        //$this->init();
        $fields = array('nodes.node_id','nodes.alias_id','node_name');
        $where = array('AND'=>array('user_node_relation.user_id'=>$user_ids,'user_node_relation.node_id[!]'=>$node_id));
        $join = array('[>]nodes'=>'node_id');
        return $this->select_cache($fields,$where,'nodes.node_id DESC',10,$join);
    }
    
    static function top_cate_list(){
        static $top_cate_list = array();
        if(!$top_cate_list){
            $node_mod = model::load('node');
            $node_mod->init('cate');
            $fields = array('cate_id','cate_name');
            $top_cate_list = $node_mod->select_cache($fields);
        }
        return $top_cate_list;
    }
    
    static function top_node_list(){
        $node_mod = model::load('node');
        $fields = array('node_id','node_name');
        $where = array('alias_id'=>0);
        return $node_mod->select_cache($fields,$where,'node_id DESC','20');
    }
    
    static function user_node_relation($user_id,$node_id=null){
        if(!$user_id) return;
        
        static $relation = array();
        if(empty($relation)){
            $node_mod = model::load('node');
            $node_mod->init('user_node_relation');
            $user_node_relation = $node_mod->select_cache(array('node_id'),array('user_id'=>$user_id),null,5000);
            if(!$user_node_relation){
                $relation = 1;
            }else{
                $relation = $node_mod->make_assoc($user_node_relation,null,'node_id');
            }
            $node_mod->init();
        }
        
        //已经搜索过，数据为空
        if($relation == 1) return;
        
        if($node_id)
            return array_key_exists($node_id,$relation);
        else
            return $relation;
    }
}