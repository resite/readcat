<?php
class model{
    var $db,$cache,$table,$pkey,$fields = array(),$message,$last_query;
    
    function __construct(){
        global $_db,$_cache;
        $this->db = $_db;
        $this->cache = $_cache;
        
        if(!$this->table)
            $this->table = get_class($this);
        if(!$this->pkey)
            $this->pkey = $this->table.'_id';
    }
    
    function init($table=''){
    }
    
    function insert($data){
        if(!isset($data[0])){
			$data = array($data);
        }
        foreach($data as &$v){
            $v = array_intersect_key($v,$this->fields);
        }
        
        $res = $this->db->insert($this->table,$data);
        if(!$res) throw new Exception();
        return $res;
    }
    
    function update($data,$where = null){
        if(is_array($data['OPR'])){
            $opr = $data['OPR'];
        }
        $data = array_intersect_key($data,$this->fields);
        if($opr){
            $data = array_merge($data,$opr);
        }
        
        if($where !=null && !is_array($where)){
            $where = array($this->pkey=>$where);
        }elseif($data[$this->pkey]){
            $where = array($this->pkey=>$data[$this->pkey]);
            unset($data[$this->pkey]);
        }elseif(is_array($where)){
            $where = array_intersect_key($where,$this->fields);
        }
        
        if(empty($where)) return false;
        else $where = array('AND'=>$where);
        
        $res = $this->db->update($this->table,$data,$where);
        if($res === false) throw new Exception();
        return $res;
    }
    
    function edit($data,$where=null){
        if($data[$this->pkey] || $where){
            $this->update($data,$where);
        }else{
            $this->insert($data);
        }
    }
    
    function select($fields = '*', $_where = null, $order = null, $size = 0, $join = null) {
        $page = intval($_where['page']);
        
        $where = array();
        if(is_array($_where['AND'])) $where['AND'] = $_where['AND'];
        if(is_array($_where['OR'])) $where['OR'] = $_where['OR'];
        if(is_array($_where['LIKE'])) $where['LIKE'] = $_where['LIKE'];
        
        if(is_array($_where)){
            $_where = array_intersect_key($_where,$this->fields);
        }
        if(!empty($_where))
            $where['AND'] = $where['AND']?array_merge($where['AND'],$_where):array('AND'=>$_where);
            
        if(!$page) $page = 1;
        if(!$size) $size = SELECT_LIMIT;
            
        if ($page && $size)
            $where['LIMIT'] = array(($page - 1) * $size, $size);

        if ($order)
            $where['ORDER'] = $order;
         
        if($join){
            return $this->db->select($this->table, $join, $fields, $where);
        }else{
            return $this->db->select($this->table, $fields, $where);
        }
    }
    
    function select_cache($fields = '*', $_where = null, $order = null, $size = 0 ,$join = null) {
        if(!$this->cache){
            return $this->select($fields,$_where,$order,$size,$join);
        }
        $key = 'select'.$this->table.$this->pkey.$fields.serialize($where).$order.$size;
        $rows = $this->cache->get($key);
        if(!$rows){
            $rows = $this->select($fields,$_where,$order,$size,$join);
            if($rows) $this->cache->set($key,$rows,0,3600);
        }
        return $rows;
    }
    
    function delete_select_cache($fields = '*', $_where = null, $order = null, $size = 0, $join = null) {
        if($this->cache){
            $key = 'select'.$this->table.$this->pkey.$fields.serialize($where).$order.$size;
            $this->cache->delete($key);
        }
    }
    
    function get($where,$lock=false){
        if(is_array($where))
            $where = array_intersect_key($where,$this->fields);
        
        if(empty($where)) return false;
        
        if(!is_array($where))
            $where = array($this->pkey=>$where);
        
        $where = array('AND'=>$where,'ORDER'=>$this->pkey.' DESC');
        
        $option = $lock ? 'for update' : '';
        return $this->db->get($this->table,"*",$where,$option);
    }
    
    function get_cache($where){
        if(!$this->cache){
            return $this->get($where);
        }
        $key = 'get'.$this->table.$this->pkey.serialize($where);
        $row = $this->cache->get($key);
        if(!$row){
            $row = $this->get($where);
            if($row) $this->cache->set($key,$row,0,3600);
        }
        return $row;
    }
    
    function delete_get_cache($where){
        if($this->cache){
            $key = 'get'.$this->table.$this->pkey.serialize($where);
            $this->cache->delete($key);
        }
    }
    
    function delete($where){
        if(is_array($where))
            $where = array_intersect_key($where,$this->fields);
            
        if(empty($where)) return false;
        
        if(!is_array($where))
            $where = array($this->pkey=>$where);
        
        return $this->db->delete($this->table,$where);
    }
    
    function count($where) {
        if(is_array($where))
            $where = array_intersect_key($where,$this->fields);

        if(!empty($where))
            $where = array('AND'=>array_intersect_key($where,$this->fields));
        
        return $this->db->count($this->table, $where);
    }
    
    function count_cache($where) {
        if(!$this->cache){
            return $this->count($where);
        }
        $key = 'count'.$this->table.$this->pkey.serialize($where);
        $row = $this->cache->get($key);
        if(!$row){
            $row = $this->count($where);
            if($row) $this->cache->set($key,$row,0,3600);
        }
        return $row;
    }
    
    /**
     * 开始事物
     * @return type
     */
    function begin() {
        return $this->db->beginTransaction();
    }

    /**
     * 提交事物
     * @return type
     */
    function commit() {
        return $this->db->commit();
    }

    /**
     * 回滚
     * @return type
     */
    public function rollBack() {
        return $this->db->rollBack();
    }
    
    public function sql($sql, $one = true) {
        return $this->db->sql($sql, $one);
    }

    static function load($name) {
        static $loadClass = array();
        if (empty($loadClass[$name])) {
            $loadClass[$name] = new $name ();
        }
        $class = $loadClass[$name];
        $class->init();
        return $class;
    }
    
    function make_assoc($data,$val=null,$key=null){
        $res = array();
        if(!$key) $key = $this->pkey;
        foreach($data as $v){
            $res[$v[$key]] = $val?$v[$val]:$v;
        }
        return $res;
    }
    
    function get_var($id,$field){
        $res = $this->get(array($this->pkey=>$id));
        return $res[$field];
    }
    
    function get_var_cache($id,$field){
        $res = $this->get_cache(array($this->pkey=>$id));
        return $res[$field];
    }
}