<?php
class Vindex extends View{    
    function entry(){
        $this->entry_index();
    }
    
    function entry_index(){
        $feed_mod = model::load('feed');
        $fields = array('feed_id','url','title','top_image','ups','downs','add_time','domain','user_id');
        
        if($_GET['all']){
            $this->assign['feed_list'] = $feed_mod->select_feeds($fields,$_GET);
        }else{
            $this->assign['feed_list'] = $feed_mod->select_feeds($fields,$_GET,$this->user_id);
        }
        $this->show_page(SELECT_LIMIT*100);
        
        $fields = array('feed_id','title','ups');
        $where = $_GET;
        $where['order'] = 'ups';
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        $this->display('index');
    }
    
    function entry_login(){
        $model = model::load('user');
        if(is_post() && $model->login($_POST)){
            header('Location: /');
        }else{
            $this->assign['message']=$model->message;
        }
        
        $this->display('login');
    }
    
    function entry_register(){
        $model = model::load('user');
        if(is_post() && $model->register($_POST)){
            header('Location: /');
        }else{
            $this->assign['message']=$model->message;
        }
        
        $this->display('register');
    }
    
    function entry_captcha(){
        //session_start();
        //生成验证码图片
        Header("Content-type: image/PNG");
        $im = imagecreate(44,18); // 画一张指定宽高的图片
        $back = ImageColorAllocate($im, 245,245,245); // 定义背景颜色
        imagefill($im,0,0,$back); //把背景颜色填充到刚刚画出来的图片中
        $vcodes = "";
        //srand((double)microtime()*1000000);
        //生成4位数字
        $vcodes = generate_code(4);
        for($i=0;$i<4;$i++){
            $font = ImageColorAllocate($im, rand(100,255),rand(0,100),rand(100,255)); // 生成随机颜色
            $authnum = $vcodes{$i};
            imagestring($im, 5, 2+$i*10, 1, $authnum, $font);
        }
        $_SESSION['captcha'] = strtolower($vcodes);

        for($i=0;$i<100;$i++) //加入干扰象素
        {
        $randcolor = ImageColorallocate($im,rand(0,255),rand(0,255),rand(0,255));
        imagesetpixel($im, rand()%70 , rand()%30 , $randcolor); // 画像素点函数
        }
        ImagePNG($im);
        ImageDestroy($im);
    }
}