<?php
class user extends model{
    const TYPE_BLOCKED = 0;
    
    const TYPE_ADMIN = 1;//管理员
    const TYPE_TELLER = 2;
    const TYPE_SERVER = 3;
    const TYPE_VERIFIER = 4;
    
    const TYPE_USER = 5;//用户
    
    const AUTH_SALT = 'READCAT';
    
    static function admin_type($id=null){
        $status = array( self::TYPE_ADMIN => '管理员',self::TYPE_TELLER=>'财务');
        if ($id == null) {
            return $status;
        } else {
            return $status[$id];
        }
    }
    
    static function user_type($id=null){
        $status = array(self::TYPE_BLOCKED=>'锁定用户', self::TYPE_USER => '用户');
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
            $this->fields = array('user_id'=>'','email'=>'','password'=>'','nickname'=>'','type_id'=>'','mob_phone'=>'','add_time'=>'','identifier'=>'','token'=>'','timeout'=>'');
            break;
        }
    }
    
    function register($data){
        if(strtolower($data['captcha']) != $_SESSION['captcha']){
            $this->message = '验证码错误';
            return false;
        }
        
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
        if(baddet::detect($data['nickname']) || $user){
            $this->message = '昵称错误';
            return false;
        }
        
        $data['type_id'] = self::TYPE_USER;
        $data['password'] = md5($data['password']);
        $data['add_time'] = $_SERVER['REQUEST_TIME'];
        $this->begin();
        try{
            $user_id = $this->insert($data);
            
            $_SESSION['user_id'] = $user_id;
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
    
    function check_login(){
        list($identifier, $token) = explode(':',$_COOKIE['auth']);
        
        if(!ctype_alnum($identifier) || !ctype_alnum($token)){
            return false;
        }
        $now = $_SERVER['REQUEST_TIME'];
        $user = $this->get(array('identifier'=>$identifier));
        if(!$user) return false;
        
        if($token == $user['token'] && $now <= $user['timeout']){
            $_SESSION['user_id'] = $user['user_id'];
        }
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
            if(isset($data['RememberMe'])){
                $identifier = md5(self::AUTH_SALT.md5($data['nickname'].self::AUTH_SALT));
                $token = md5(uniqid(rand(),true));
                $timeout = $_SERVER['REQUEST_TIME'] + 259200;
                setcookie('auth',"$identifier:$token",$timeout);
                
                $this->update(compact('identifier','token','timeout'),array('user_id'=>$user['user_id']));
            }
            $_SESSION['user_id'] = $user['user_id'];
            setcookie('nickname',$user['nickname'],$_SERVER['REQUEST_TIME']+2592000);
            //259200 = 3600*24*30
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
        unset($_SESSION['user_id']);
        unset($_SESSION['is_admin']);
        setcookie('auth','',$_SERVER['REQUEST_TIME']);
        return true;
    }
    
    function edit($data,$where=null){
        if($data['email'] && !filter_var($data['email'],FILTER_VALIDATE_EMAIL) && $this->get(array('email'=>$data['email']))){
            $this->message = '邮箱错误';
            return false;
        }
        parent::edit($data,$where);
        $this->delete_get_cache($data['user_id']);
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