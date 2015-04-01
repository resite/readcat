<?php
class Vrest{
    var $assign=array();
    function entry(){
        exit;
    }
    
    function user_info($field,$value){
        if(!$value){
            echo 'false';
            exit;
        }
        $model = model::load('user');
        $user = $model->get(array($field=>$value));
        if($user){
            echo 'true';
            exit;
        }else{
            echo 'false';
            exit;
        }
    }
    
    function entry_nickname(){
        $this->user_info('nickname',$_GET['nickname']);
    }
    
    function entry_email(){
        $this->user_info('email',$_GET['email']);
    }
    
    function entry_mob_phone(){
        $this->user_info('mob_phone',$_GET['mob_phone']);
    }
    
    function entry_upfile(){
        if($this->upfile('image')){
            json_echo(array('url'=>$this->assign['message'],'error'=>0));
        }else{
            json_echo(array('message'=>$this->assign['message'],'error'=>1));
        }
    }
    
    function entry_titlefromuri(){
        $url = $_REQUEST['uri'];
        if(!filter_var($url, FILTER_SANITIZE_URL)){
            return;
        }
        $html = file_get_contents($url);
        $res = preg_match('/<(title|TITLE)>(.*?)<\/(title|TITLE)>/',$html,$match);
        die($match[2]);
    }
    
    function entry_autocompletenodename(){
        $node_keyword = $_REQUEST['term'];
        if(!$node_keyword) echo '';
        
        $node_mod = model::load('node');
        $where = array('q'=>$node_keyword,'AND'=>array('type_id[!]'=>node::NODE_TYPE_SELECT));
        $node_list = $node_mod->select_nodes(array('keywords'),$where,null,5);
        $res = array();
        foreach($node_list as $v){
            $res[] = $v['keywords'];
        }
        
        json_echo($res);
    }
    
    /*
     * 上传图片
     */
    function upfile($aim_dir = 'image'){
        //定义允许上传的文件扩展名
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        //最大文件大小
        $max_size = 1000000;

        //文件保存目录URL
        $save_path = CONTENTS_PATH;
        //文件保存目录路径
        $save_url = '/contents/';

        //PHP上传失败
        if (!empty($_FILES['imgFile']['error'])) {
            switch($_FILES['imgFile']['error']){
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->assign['message'] = $error;
            return false;
        }

        //有上传文件时
        if (empty($_FILES) === false) {
            //原文件名
            $file_name = $_FILES['imgFile']['name'];
            //服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
            //文件大小
            $file_size = $_FILES['imgFile']['size'];
            //检查文件名
            if (!$file_name) {
                $this->assign['message'] = "请选择文件。";
                return false;
            }
            //检查目录
            if (@is_dir($save_path) === false) {
                $this->assign['message'] = "上传目录不存在。";
                return false;
            }
            //检查目录写权限
            if (@is_writable($save_path) === false) {
                $this->assign['message'] = "上传目录没有写权限。";
                return false;
            }
            //检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->assign['message'] = "上传失败。";
                return false;
            }
            //检查文件大小
            if ($file_size > $max_size) {
                $this->assign['message'] = "上传文件大小超过限制。";
                return false;
            }
            //检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if ($dir_name != $aim_dir || empty($ext_arr[$dir_name])) {
                $this->assign['message'] = "目录名不正确。";
                return false;
            }
            //获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            //检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->assign['message'] = "上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";
                return false;
            }
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                $save_url .= $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            //移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->assign['message'] = "上传文件失败。";
                return false;
            }
            @chmod($file_path, 0644);
            $file_url = $save_url . $new_file_name;
            
            $this->assign['message'] = $file_url;
            return true;
        }
    }
}