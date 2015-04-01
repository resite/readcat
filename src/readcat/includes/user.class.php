<?php
class user extends model{
    const TYPE_ADMIN = 0;//管理员
    const TYPE_TELLER = 1;
    const TYPE_SERVER = 2;
    const TYPE_VERIFIER = 3;
    
    const TYPE_USER = 5;//用户
    const TYPE_BLOCKED = 10;
    
    static function admin_type($id=null){
        $status = array( self::TYPE_ADMIN => '管理员',self::TYPE_TELLER=>'财务');
        if ($id == null) {
            return $status;
        } else {
            return $status[$id];
        }
    }
    
    static function user_type($id=null){
        $status = array( self::TYPE_USER => '用户');
        if ($id == null) {
            return $status;
        } else {
            return $status[$id];
        }
    }
    
    function __construct(){
        $this->init();
        parent::__construct();
    }
    
    function init($table=''){
        switch($table){
        case 'userinfo':
            $this->table = 'userinfo';
            $this->pkey = 'user_id';
            $this->fields = array('user_id'=>'','realname'=>'','real_reatus'=>'','id_card'=>'','email_status'=>'');
            break;
        case 'user':
        default:
            $this->table = 'users';
            $this->pkey = 'user_id';
            $this->fields = array('user_id'=>'','email'=>'','password'=>'','nickname'=>'','type_id'=>'','last_login_time'=>'','last_login_ip'=>'');
            break;
        }
    }
    
    function register($data){
        if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){
            $this->message = '邮箱格式错误';
            return false;
        }
        
        if(!$data['password'] || $data['password'] != $data['password2']){
            $this->message = '密码错误';
            return false;
        }
        
        $user = $this->get(array('email'=>$data['email']));
        if($user){
            $this->message = '邮箱已经注册';
            return false;
        }
        
        $user = $this->get(array('nickname'=>$data['nickname']));
        if($user){
            $this->message = '昵称已存在';
            return false;
        }
        
        $data['type_id'] = self::TYPE_USER;
        $data['password'] = md5($data['password']);
        $this->begin();
        try{
            $user_id = $this->insert($data);
            if(!$user_id) return false;
            
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = self::TYPE_USER;
            setcookie('nickname',$data['nickname']);
            
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->message = '失败';
            $this->rollBack();
            return false;
        }
    }
    
    function activate_email($data){
        $user = $this->get(array('email'=>$data['email']));
        $this->init('userinfo');
        $this->update(array('email_status'=>1),array('user_id'=>$user['user_id']));
    }
    
    function login($data,$type_arr=null){
        if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL)){
            $this->message = '邮箱格式错误';
            return false;
        }
        
        if(!$type_arr) $type_arr = self::user_type();
        
        $where = array('email'=>$data['email']);
        $user = $this->get($where);
        if($user['password'] == md5($data['password']) && $type_arr[$user['type_id']]){
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = $user['type_id'];
            setcookie('nickname',$user['nickname']);
            //$this->session->delete($user[$this->pkey]);多端口登录
            return true;
        }else{
            $this->message = '信息错误';
            return false;
        }
    }
    
    function admin_login($data){
        $res = $this->login($data,self::admin_type());
        if($res) $_SESSION['is_admin'] = 1;
        return $res;
    }
    
    function logout(){
        $_SESSION['user_id']=null;
        $_SESSION['user_type']=null;
        $_SESSION['is_admin']=null;
        return true;
    }
    
    function edit($data,$where=null){
        if($data['email'] && !filter_var($data['email'],FILTER_VALIDATE_EMAIL) && $this->get(array('email'=>$data['email']))){
            $this->message = '邮箱错误';
            return false;
        }
        parent::edit($data);
    }
    
    function edit_password($data){
        if($data['password'] != $data['password2']){
            $this->message = '密码不匹配';
            return false;
        }
        $row['password'] = md5($data['password']);
        $row['user_id'] = $data['user_id'];
        $this->edit($data);
    }
}