<?php
function is_even($num){
    return $num%2==0;
}

function is_post(){
    return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}

function is_ajax(){
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
        if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
            return true;
    }

    return false;
}

function is_ssl(){
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}

function generate_code($len = 6){
    $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for ($i = 0, $count = strlen($chars); $i < $count; $i++)
    {
        $arr[$i] = $chars[$i];
    }

    mt_srand((double) microtime() * 1000000);
    shuffle($arr);
    $code = substr(implode('', $arr), 5, $len);
    return $code;
}

function json_echo($data) {
	header('Content-type: text/html; charset=UTF-8');
	echo json_encode($data);
	exit;
}

//导出excel格式表
function exportExls($filename,$title,$data,$assoc=false){
    header("Content-type: application/vnd.ms-excel");
    header("Content-disposition: attachment; filename="  . $filename . ".xls");
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
      <head>
      <meta http-equiv="expires" content="Mon, 06 Jan 1999 00:00:01 GMT">
      <meta http-equiv=Content-Type content="text/html; charset=utf-8">
      <!--[if gte mso 9]><xml>
      <x:ExcelWorkbook>
      <x:ExcelWorksheets>
        <x:ExcelWorksheet>
        <x:Name>sheet1</x:Name>
        <x:WorksheetOptions>
          <x:DisplayGridlines/>
        </x:WorksheetOptions>
        </x:ExcelWorksheet>
      </x:ExcelWorksheets>
      </x:ExcelWorkbook>
      </xml><![endif]-->
     </head>
     <body><table><tr>';
    if (is_array($title)){
        foreach ($title as $key => $value){
            echo "<td>$value</td>\t";
        }
    }
    echo "\n</tr><tr>";
    if (is_array($data)){
        if($assoc){
            foreach ($data as $key => $value){
                foreach ($title as $_key => $_value){
                    $v = $value[$_key];
                    echo is_numeric($v)&&strlen($v)>11?"<td style='vnd.ms-excel.numberformat:@'>{$v}</td>\t":"<td>{$v}</td>\t";
                }
                echo "\n</tr>";
            }
        }else{
            foreach ($data as $key => $value){
                foreach ($value as $_key => $_value){
                    echo is_numeric($_value)&&strlen($_value)>11?"<td style='vnd.ms-excel.numberformat:@'>$_value</td>\t":"<td>$_value</td>\t";
                }
                echo "\n</tr>";
            }
        }
    }
    echo "</tr></table></body></html>";
}

function del_file($path){
	if(file_exists($path)) return unlink($path);
}

/* key 以default为准，value以argv为准
 */
function right_merge($argv,$default){
	$intersect = array_intersect_key($argv,$default);
	$result = array_merge($default,$intersect);
	return $result;
}

function auto_convert_code($str,$from=null,$to=null){
	$encode = mb_detect_encoding($str, array('ASCII','GB2312','GBK','UTF-8'));
	if($encode=="GBK"){
		$result = iconv("UTF-8","UTF-8",$str);
		$str=mb_convert_encoding($str,"GBK","utf-8");
	}
	return $result;
}

function set_cookie($key,$value,$expire=-1){
	if($expire == -1) $expire = time()+3600*24;
	setcookie($key,$value,$expire);
}

function delete_cookie($key){
	setcookie($key,'',time()-3600);
}

function json2xml($source,$charset='utf8') {
	$source = trim($source);
	if(empty($source) || strpos($source,'{') !==0 ){
		return false;
	}
	$array = json_decode($source);
	if(!$array) return false;
	
	$xml ="<?xml version='1.0' encoding='$charset' ?>";
	$xml .= assoc2xml($array);
	return $xml;
}

function assoc2xml($source) {
	$string=""; 
	foreach($source as $k=>$v){ 
		$string .="<".$k.">";
		if(is_array($v) || is_object($v)){ //判断是否是数组，或者，对像
			$string .= assoc2xml($v);//是数组或者对像就的递归调用
		}else{
			$string .=$v;//取得标签数据
		}
		$string .="</".$k.">";
	}
	return $string;
}

function mailto($email,$title,$body){
    $mail = model::load('SMail');
    
    $mail->hostname = Configuration::get('MIAIL_SMTP_HOST');
    $mail->username = Configuration::get('MAIL_SMTP_USER');
    $mail->password = Configuration::get('MAIL_SMTP_PASS');
    $mail->protocol = "smtp";
    $mail->port = Configuration::get('MAIL_SMTP_PORT');
            

    $mail->setTo($email);
    $mail->setSender(Configuration::get('MAIL_SMTP_USER'));
    $mail->setFrom(Configuration::get('MAIL_SMTP_USER'));
    $mail->setSubject($title);
    $mail->setText(strip_tags($body));
    $mail->setHtml($body);
    
    $mail->send();
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
        //$this->assign['message'] = $error;
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
            //$this->assign['message'] = "请选择文件。";
            return false;
        }
        //检查目录
        if (@is_dir($save_path) === false) {
            //$this->assign['message'] = "上传目录不存在。";
            return false;
        }
        //检查目录写权限
        if (@is_writable($save_path) === false) {
            //$this->assign['message'] = "上传目录没有写权限。";
            return false;
        }
        //检查是否已上传
        if (@is_uploaded_file($tmp_name) === false) {
            //$this->assign['message'] = "上传失败。";
            return false;
        }
        //检查文件大小
        if ($file_size > $max_size) {
            //$this->assign['message'] = "上传文件大小超过限制。";
            return false;
        }
        //检查目录名
        $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
        if ($dir_name != $aim_dir || empty($ext_arr[$dir_name])) {
            //$this->assign['message'] = "目录名不正确。";
            return false;
        }
        //获得文件扩展名
        $temp_arr = explode(".", $file_name);
        $file_ext = array_pop($temp_arr);
        $file_ext = trim($file_ext);
        $file_ext = strtolower($file_ext);
        //检查扩展名
        if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
            //$this->assign['message'] = "上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。";
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
            //$this->assign['message'] = "上传文件失败。";
            return false;
        }
        @chmod($file_path, 0644);
        $file_url = $save_url . $new_file_name;
        
        //$this->assign['message'] = $file_url;
        return $file_url;
    }
}

/* 
 * 创建正方形jpeg缩略图
 */
function image_resize($src_file,$dst_width){
    $dst_height = $dst_width;
    $dst_file = dirname($src_file).'/s_'.basename($src_file);
    $save_file = ROOT_PATH.'.'.$dst_file;
    $src_image=imagecreatefromstring(file_get_contents(ROOT_PATH.'.'.$src_file));
    $src_width=imagesx($src_image);
    $src_height=imagesy($src_image);
    if($src_width>$src_height){
        $x=($src_width-$src_height)/2;
        $y=0;
        $src_width = $src_height;
    }elseif($src_width<$src_height){
        $x=0;
        $y=($src_height-$src_width)/2;
        $src_height = $src_width;
    }else{
        $x=0;
        $y=0;
    }
    $dst_image = imagecreatetruecolor($dst_width,$dst_height);
    imagecopyresized($dst_image,$src_image,0,0,$x,$y,$dst_width,$dst_height,$src_width,$src_height);
    imagejpeg($dst_image,$save_file);
    imagedestroy($src_image);
    imagedestroy($dst_image);
    return $dst_file;
}