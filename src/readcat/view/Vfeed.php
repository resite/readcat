<?php
class Vfeed extends View{
    function __construct(){
        parent::__construct();
        
        global $_entry;
        
        $login_entry = array('entry_post','entry_post_comment','entry_vote_feed','entry_vote_comment','entry_collect_feed','entry_report_comment');
        if(in_array($_entry,$login_entry)){
            if(!$this->user_id){
                header('Location: /index.php?view=index&entry=login');
                exit;
            }
            $user_mod = model::load('user');
            $user = $user_mod->get_cache($this->user_id);

            if($user['type_id'] == user::TYPE_BLOCKED){
                $this->assign['message'] ='禁止操作';
                $this->display('message');
                exit;
            }
        }
    }
    
    function entry(){
        $this->entry_feed_list();
    }
    
    function entry_feed_list(){
        $feed_mod = model::load('feed');
        $fields = array('feed_id','url','title','top_image','ups','downs','add_time','domain','user_id');
        $this->assign['feed_list'] = $feed_mod->select_feeds($fields,$_GET);
        
        if(count($this->assign['feed_list']) == SELECT_LIMIT){
            $this->show_page(SELECT_LIMIT*100);
        }
        
        $node_mod = model::load('node');
        $node = $node_mod->get($_GET['node_id']);
        $this->assign['node'] = $node;
        
        $fields = array('feed_id','title','ups');
        $where = $_GET;
        $where['cate_id'] = $node['cate_id'];
        $where['order'] = 'ups';
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        
        if($_GET['node_id']){
            $this->assign['indirect_node_list'] = $node_mod->select_indirect_nodes($_GET['node_id']);
        }
        
        $this->assign['sys_config']['website_title'] = $node['node_name'].' - '.$this->assign['sys_config']['website_name'];
        $this->display('feed_list');
    }
    
    function entry_feed_view(){
        $feed_mod = model::load('feed');
        $feed = $feed_mod->get($_GET);
        $this->assign['feed'] = $feed;
        
        $feed_mod->init('node_feed_relation');
        $relation = $feed_mod->get(array('feed_id'=>$feed['feed_id']));
        $node_mod = model::load('node');
        $node = $node_mod->get($relation['node_id']);
        $this->assign['node'] = $node;
        
        $feed_mod->init();
        $fields = array('feed_id','title','ups');
        $where = array('cate_id'=>$node['cate_id'],'order' => 'ups');
        $this->assign['top_feed_list'] = $feed_mod->select_feeds($fields,$where);
        
        $fields = array('comment_id','user_id','content','add_time','ups','downs','hot_score','parent_id','enabled');
        $this->assign['comments'] = $feed_mod->select_comments($fields,$feed['feed_id']);
        
        if($_GET['feed_id']){
            $this->assign['indirect_feed_list'] = $feed_mod->select_indirect_feeds($_GET['feed_id']);
        }
        
        $this->assign['sys_config']['website_title'] = $feed['title'].' - '.$this->assign['sys_config']['website_name'];
        $this->display('feed_view');
    }
    
    function entry_post(){
        if(is_post()){
            $feed_mod = model::load('feed');
            if($feed_mod->add_feed($_POST,$this->user_id)){
                $this->assign['message'] = '内容已经提交，正在等待审核，在此期间您可以到处看看';
                $this->display('message');
            }else{
                $this->assign['message'] = $feed_mod->message;
                $this->display('message');
            }
        }elseif($_GET['linkpost']){
            $this->display('link_post');
        }else{
            $this->display('discuss_post');
        }
    }
    
    //ajax
    function entry_post_comment(){
        $feed_mod = model::load('feed');
        $feed_mod->init('comments');
        
        if(is_post()){
            $comment_arr = $feed_mod->add_comment($_POST,$this->user_id);
            html::show_comments_building($this->user_id,$_POST['feed_id'],$comment_arr,$_POST['patent_id']);
        }
    }
    
    //ajax
    function entry_vote_feed(){
        $feed_mod = model::load('feed');
        $feed_id = $_REQUEST['feed_id'];
        $feed_mod->vote(feed::AIM_TYPE_FEED,$feed_id,$this->user_id,$_REQUEST['score']);
    }
    
    //ajax
    function entry_vote_comment(){
        $feed_mod = model::load('feed');
        $comment_id = $_REQUEST['comment_id'];
        $feed_mod->vote(feed::AIM_TYPE_COMMENT,$comment_id,$this->user_id,$_REQUEST['score']);
    }
    
    //ajax
    function entry_collect_feed(){
        $feed_mod = model::load('feed');
        $feed_id = $_REQUEST['feed_id'];
        $feed_mod->collect_feed($feed_id,$this->user_id);
    }
    
    //ajax
    function entry_report_comment(){
        if($_REQUEST['comment_id']){
            $sys_config = model::load('feed');
            $sys_config->init('reports');
            $sys_config->insert(array('aim_id'=>$_REQUEST['comment_id'],'user_id'=>$this->user_id,'report_type'=>feed::REPORT_TYPE_COMMENT));
        }
    }
}