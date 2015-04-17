<?php
class feed extends model{
    const CATE_TYPE_KEYWORDS = 0;
    const CATE_TYPE_SELECTED = 1;
    const CATE_TYPE_NOMAL = 2;
    
    const AIM_TYPE_FEED = 0;
    const AIM_TYPE_COMMENT = 1;
    
    const VOTE_TYPE_UNVOTE = 0;
    const VOTE_TYPE_LIKE = 1;
    const VOTE_TYPE_DISLIKE = 2;
    
    const SELECT_COMMENTS_LIMIT = 200;
    const COMMENT_DOWNS_CEILING = 10;
    
    const FEED_STATUS_BLOCK = 0;
    const FEED_STATUS_ENABLE = 1;
    const FEED_STATUS_WAITING = 2;
    const FEED_STATUS_STICKY = 5;
    
    const REPORT_TYPE_COMMENT = 0;
    
    function __construct(){
        $this->init();
        parent::__construct();
    }
    
    function init($table=''){
        if($this->table === $table) return;
        switch($table){
        case 'reports':
            $this->table = 'reports';
            $this->pkey = 'report_id';
            $this->fields = array('report_id'=>'','aim_id'=>'','report_type'=>'','user_id'=>'','add_time'=>'');
            break;
        case 'comments':
            $this->table = 'comments';
            $this->pkey = 'comment_id';
            $this->fields = array('comment_id'=>'','user_id'=>'','feed_id'=>'','content'=>'','add_time'=>'','parent_id'=>'','ups'=>'','downs'=>'','hot_score'=>'','enabled'=>'');
            break;
        case 'votes':
            $this->table = 'votes';
            $this->pkey = 'vote_id';
            $this->fields = array('vote_id'=>'','user_id'=>'','aim_id'=>'','aim_type'=>'','vote_type'=>'','add_time'=>'');
            break;
        case 'collections':
            $this->table = 'collections';
            $this->pkey = 'collection_id';
            $this->fields = array('collection_id'=>'','user_id'=>'','feed_id'=>'','add_time'=>'');
            break;
        case 'node_feed_relation':
            $this->table = 'node_feed_relation';
            $this->pkey = 'relation_id';
            $this->fields = array('relation_id'=>'','feed_id'=>'','node_id'=>'');
            break;
        case 'feeds':
        default:
            $this->table = 'feeds';
            $this->pkey = 'feed_id';
            $this->fields = array('feed_id'=>'','user_id'=>'','cate_id'=>'','url'=>'','title'=>'','content'=>'','add_time'=>'','top_image'=>'','ups'=>'','downs'=>'','hot_score'=>'','controversy_score'=>'','status'=>'');
            break;
        }
    }
    
    function add_comment($data,$user_id){
        $this->init('comments');
        $content = trim($data['content']);
        $content_len = mb_strlen($content);
        if($content_len < 2 || $content_len > 255 || baddet::detect($content)){
            $this->message = '内容错误';
            return false;
        }
        
        $last_comment1 = $this->select_cache(array('content'),array('user_id'=>$user_id),'comment_id DESC','2');
        $last_comment2 = $this->select_cache(array('content'),array('comment_id'=>$data['parent_id']),null,'1');
        if($content == $last_comment1[0]['content'] || $content == $last_comment1[1]['content'] || $content == $last_comment2[0]['content']){
            $this->message = '内容错误';
            return false;
        }
        
        $data['content'] = $content;
        $data['user_id'] = $user_id;
        $data['ups'] = 0;
        $data['downs'] = 0;
        $data['hot_score'] = sorts::comment_hot_score(0,0);
        $data['add_time'] = $_SERVER['REQUEST_TIME'];
        $data['enabled'] = 1;
        
        $comment_id = $this->insert($data);
        $data['comment_id'] = $comment_id;
        $comment_arr = array($comment['parent_id']=>array($comment['hot_score']=>array($data)));
        return $comment_arr;
    }
    
    /*
     * ljj 盖楼，对应html::show_building
     */
    function select_comments($fields,$feed_id){
        $this->init('comments');
        $_comments = $this->select($fields,array('feed_id'=>$feed_id),'hot_score DESC',self::SELECT_COMMENTS_LIMIT);
        
        $this->init();
        
        $comments = array();
        foreach($_comments as $v){
            $comments[$v['parent_id']][$v['hot_score']][] = $v;
        }
        $this->init();
        return $comments;
    }
    
    //暂时只允许插入，不允许修改
    function add_feed($data,$user_id){
        if(strtolower($data['captcha']) != $_SESSION['captcha']){
            $this->message = '验证码错误';
            return false;
        }
        $data['content'] = trim($content);
        
        if($data['url']){
            if(!filter_var($data['url'],FILTER_SANITIZE_URL)){
                $this->message = '网址错误';
                return false;
            }else{
                $path = parse_url($data['url']);
                $data['domain'] = $path['host'];
            }
        }
            
        if(!$data['node_keyword']){
            $this->message = '节点错误';
            return false;
        }
        $node_mod = model::load('node');
        $node = $node_mod->get_cache(array('keywords'=>$data['node_keyword']));
        if(!$node){
            $this->message = '节点错误';
            return false;
        }else{
            $data['domain'] = $node['keywords'];
        }
        
        $add_time = $_SERVER['REQUEST_TIME'];
        $data['add_time'] = $add_time;
        $data['cate_id'] = $node['cate_id'];
        $data['ups'] = 0;
        $data['downs'] = 0;
        $data['hot_score'] = sorts::hot_score(0,0,$add_time);
        $data['controversy_score'] = 0;
        $data['status'] = self::FEED_STATUS_WAITING;
        $data['user_id'] = $user_id;
        
        $this->begin();
        try{
            $feed_id = $this->insert($data);
            if(!$feed_id) return;
            
            $this->init('node_feed_relation');
            $this->insert(array('node_id'=>$node['node_id'],'feed_id'=>$feed_id));
        
            $this->init();
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->message = '失败';
            $this->rollBack();
            return false;
        }
    }
    
    function select_feeds($fields,$where=null,$user_id=null,$relation_type=null){
        $where['OR']['status'] = array(self::FEED_STATUS_ENABLE,self::FEED_STATUS_STICKY);
        $pos = array_search('add_time',$fields);
        if($pos !== false){
            $fields[$pos] = 'feeds.add_time';
        }
        $pos = array_search('user_id',$fields);
        if($pos !== false){
            $fields[$pos] = 'feeds.user_id';
        }
        
        if($where['order'] == 'new'){
            $order = 'feed_id DESC';
        }elseif($where['order'] == 'ups'){
            $where['AND']['add_time[>]'] = strtotime('1 day ago');
            $order = 'ups DESC';
            unset($where['page']);
            $size = 10;
        }elseif($where['order'] == 'controversy'){
            $where['AND']['add_time[>]'] = strtotime('1 week ago');
            $order = 'controversy_score DESC';
        }else{
            $order = 'hot_score DESC';
        }
        
        if($relation_type){
            //赞过 || 私藏
            $pos = array_search('feed_id',$fields);
            if($pos !== false){
                $fields[$pos] = 'feeds.feed_id';
            }
            
            if($relation_type == 'like'){
                $join = array('[<]votes'=>array('feed_id'=>'aim_id'));
                $where['AND']['votes.vote_type'] = self::VOTE_TYPE_LIKE;
                $where['AND']['votes.user_id'] = $user_id;
                $where['AND']['votes.aim_type'] = self::AIM_TYPE_FEED;
            }elseif($relation_type == 'collect'){
                $join = array('[<]collections'=>'feed_id');
                $where['AND']['collections.user_id'] = $user_id;
            }
        }elseif(!$where['cate_id'] && ($where['node_id'] || $user_id)){
            //节点页 || 首页
            if($user_id){
                $node_mod = model::load('node');
                $node_mod->init('user_node_relation');
                $node_list = $node_mod->select_cache(array('node_id'),array('user_id'=>$user_id),null,999);

                if($node_list){
                    foreach($node_list as &$v){
                        $v = $v['node_id'];
                    }
                    $where['AND']['node_id'] = $node_list;
                }
            }else{
                $where['AND']['node_id'] = $where['node_id'];
            }
            $join = array('[<]node_feed_relation'=>'feed_id');
        }
        
        $feed_list = $this->select_cache($fields,$where,$order,$size,$join);
        $this->init();
        return $feed_list;
    }
    
    function select_indirect_feeds($feed_id){
        if(!$feed_id) return;
        
        $this->init('votes');
        $votes = $this->select_cache(array('user_id'),array('feed_id'=>$feed_id,'vote_type'=>self::VOTE_TYPE_LIKE),'vote_id DESC',20);
        if(!$votes) return;
        
        $user_ids = array();
        foreach($votes as $v){
            $user_ids[] = $v['user_id'];
        }
        
        //$this->init();
        $fields = array('feeds.feed_id','title','ups');
        $where = array('AND'=>array('votes.user_id'=>$user_ids,'votes.feed_id[!]'=>$feed_id,'enable'=>1));
        $join = array('[>]feeds'=>'feed_id');
        return $this->select_cache($fields,$where,'feeds.feed_id DESC',10,$join);
    }
    
    function vote($aim_type, $aim_id, $user_id, $score){
        if(!is_numeric($aim_id) || !$aim_id || !$user_id)
            return;
        
        //$aim_id;
        //$aim_type = self::AIM_TYPE_FEED;
        $score = $score>0?1:-1;
        
        $this->init('votes');
        $votes = $this->get(compact('aim_id','aim_type','user_id'));
        $last_vote_type = $votes['vote_type'];
        $vote_id = $votes['vote_id'];
        $OPR = array();
        
        $this->begin();
        try{
            if($aim_type == self::AIM_TYPE_FEED){
                $this->init();
                //锁行
                $feed = $this->get($aim_id,true);
                $ups = $feed['ups'];
                $downs = $feed['downs'];
            }elseif($aim_type == self::AIM_TYPE_COMMENT){
                $this->init('comments');
                //锁行
                $comment = $this->get($aim_id,true);
                $ups = $comment['ups'];
                $downs = $comment['downs'];
            }
            
            if($score>0 && $last_vote_type != self::VOTE_TYPE_LIKE){
                //改成喜欢
                $ups += 1;
                $OPR['ups[+]'] = 1;
                if($last_vote_type == self::VOTE_TYPE_DISLIKE){
                    $downs -= 1;
                    $OPR['downs[-]'] = 1;
                }
                $vote_type = self::VOTE_TYPE_LIKE;
            }elseif($score<0 && $last_vote_type != self::VOTE_TYPE_DISLIKE){
                //改成不喜欢
                $downs += 1;
                $OPR['downs[+]'] = 1;
                if($last_vote_type == self::VOTE_TYPE_LIKE){
                    $ups -= 1;
                    $OPR['ups[+]'] = 1;
                }
                $vote_type = self::VOTE_TYPE_DISLIKE;
            }else{
                //改成未打过
                if($last_vote_type == self::VOTE_TYPE_DISLIKE){
                    $downs -= 1;
                    $OPR['downs[-]'] = 1;
                }
                if($last_vote_type == self::VOTE_TYPE_LIKE){
                    $ups -= 1;
                    $OPR['ups[-]'] = 1;
                }
                $vote_type = self::VOTE_TYPE_UNVOTE;
            }
        
            if($aim_type == self::AIM_TYPE_FEED){
                //为了方便计算和存储，省略两位小数，转为整形
                $hot_score = sorts::hot_score($ups, $downs, $feed['add_time']);
                $controversy_score = sorts::controversy($ups, $downs);
                $this->init();
                $this->update(compact('OPR','hot_score','controversy_score'),array('feed_id'=>$aim_id));
                $this->user_vote_feed($user_id,'refresh');
            }elseif($aim_type == self::AIM_TYPE_COMMENT){
                //为了方便计算和存储，省略两位小数，转为整形
                $hot_score = sorts::comment_hot_score($ups, $downs);
                $this->init('comments');
                $this->update(compact('OPR','hot_score'),array('comment_id'=>$aim_id));
            }
            
            $this->init('votes');
            
            if($last_vote_type != self::VOTE_TYPE_UNVOTE && $vote_type == self::VOTE_TYPE_UNVOTE){
                $this->delete($vote_id);
            }else{
                $add_time = $_SERVER['REQUEST_TIME'];
                $this->edit(compact('vote_id','aim_id','aim_type','user_id','vote_type','add_time'));
            }
            $this->init();
            $this->commit();
            return true;
        }catch(Exception $e){
            $this->message = '失败';
            $this->rollBack();
            return false;
        }
    }
    
    function collect_feed($feed_id,$user_id){
        if(!is_numeric($feed_id) || !$feed_id || !$user_id)
            return;
            
        $collect_id = self::user_collect_feed($user_id,$feed_id);
        $this->init('collections');
        if($collect_id){
            $this->delete($collect_id);
        }else{
            $this->insert(compact('collect_id','user_id','feed_id'));
        }
        return true;
    }
    
    static function user_vote_comment($user_id,$feed_id, $comment_id=null){
        if(!$user_id) return;
        
        static $relation = array();
        if(empty($relation)){
            $feed_mod = model::load('feed');
            $feed_mod->init('votes');
            $fields = array('vote_id','aim_id(comment_id)','vote_type');
            $where = array('user_id'=>$user_id,'feed_id'=>$feed_id,'aim_type'=>self::AIM_TYPE_COMMENT);
            $join = array('[<]comments'=>'comment_id');
            $votes = $feed_mod->select($fields,$where,null,500,$join);
            if(!$votes){
                $relation = 1;
            }else{
                $relation = $feed_mod->make_assoc($votes,null,'comment_id');
            }
            $feed_mod->init();
        }
        
        //已经搜索过，数据为空
        if($relation == 1) return;
        return $comment_id?$relation[$comment_id]['vote_type']:$relation;
    }
    
    static function user_vote_feed($user_id,$feed_id='refresh'){
        if(!$user_id) return;
        
        static $relation = array();
        if($feed_id == 'refresh'){
            $feed_mod = model::load('feed');
            $feed_mod->init('votes');
            $feed_mod->delete_select_cache(array('vote_id','aim_id(feed_id)','vote_type'),array('user_id'=>$user_id,'aim_type'=>self::AIM_TYPE_FEED),null,500);
            return;
        }
        
        if(empty($relation)){
            $feed_mod = model::load('feed');
            $feed_mod->init('votes');
            $votes = $feed_mod->select_cache(array('vote_id','aim_id(feed_id)','vote_type'),array('user_id'=>$user_id,'aim_type'=>self::AIM_TYPE_FEED),null,500);
            if(!$votes){
                $relation = 1;
            }else{
                $relation = $feed_mod->make_assoc($votes,null,'feed_id');
            }
            $feed_mod->init();
        }
        
        //已经搜索过，数据为空
        if($relation == 1) return;
        return $feed_id?$relation[$feed_id]['vote_type']:$relation;
    }
    
    static function user_collect_feed($user_id,$feed_id=null){
        if(!$user_id) return;
        
        static $relation = array();
        if(empty($relation)){
            $feed_mod = model::load('feed');
            $feed_mod->init('collections');
            $collections = $feed_mod->select_cache(array('collection_id','feed_id'),array('user_id'=>$user_id),null,500);
            if(!$collections){
                $relation = 1;
            }else{
                $relation = $feed_mod->make_assoc($collections,null,'feed_id');
            }
            $feed_mod->init();
        }
        
        //已经搜索过，数据为空
        if($relation == 1) return;
        return $feed_id?$relation[$feed_id]['collection_id']:$relation;
    }
    
}