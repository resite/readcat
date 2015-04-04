<?php
class html{
    const YES = 1;
    const NO = 0;
    
    static function boolen($id=null){
        $arr = array(self::YES => '是',self::NO=>'否');
        if($id === NULL){
            return $arr;
        }else{
            return $arr[$id];
        }
    }
    
    static function range($first,$second=null,$step=1){
        if(!$second){
            $second = $first;
            $first = 1;
        }
        $arr = range($first,$second,$step);
        return array_combine($arr,$arr);
    }
    
    static function url($url){
        if(defined('REWRITE')){
            return $url;
        }else{
            $url = str_replace('/?','&',$url);
            return '/index.php?view='.str_replace('/','&entry=',$url);
        }
    }
    
    static function date($date){
        return $date?date('Y-m-d',$date):date('Y-m-d',$_SERVER['REQUEST_TIME']);
    }
    
    static function option($data,$default=''){
        $html = '<option></option>';
        foreach($data as $k=>$v){
            $html .= "<option value='{$k}'";
            if($k == $default) $html .= ' selected';
            $html .= " >{$v}</option>";
        }
        return $html;
    }
    
    static function radio($data,$default='',$name=''){
        $html = '';
        foreach($data as $k=>$v){
            $html .= "<input type='radio' id='{$name}' name='{$name}' value='{$k}'";
            if($k == $default) $html .= ' checked';
            $html .= " />{$v}";
        }
        return $html;
    }
    
    static function checkbox($data,$default='',$name=''){
        $html = '';
        foreach($data as $k=>$v){
            $html .= "<input type='checkbox' id='{$name}' name='{$name}' value='{$k}'";
            if(is_array($default) && in_array($k, $default)) $html .= ' checked';
            elseif($k == $default) $html .= ' checked';
            $html .= " />{$v}";
        }
        return $html;
    }
    
    static function nickname($id){
        if(!$id) return 'unknow';
        $model = model::load('user');
        return $model->get_var_cache($id,'nickname');
    }
    
    static function comments_count($feed_id){
        $model = model::load('feed');
        $model->init('comments');
        return $model->count_cache(array('feed_id'=>$feed_id));
    }
    
    static function query_string($exclude=array()){
        $page_get = '?';
        $len = count($_GET);
        $_get_keys = array_diff(array_keys($_GET),$exclude);
        $key_len = count($_get_keys);
        foreach($_get_keys as $i=>$k){
            $page_get .= "$k={$_GET[$k]}";
            if($i+1<$key_len) $page_get .= '&';
        }
        return $page_get;
    }
    
    static function date_relative($unix_time){
        $today = date('Y-m-d',$_SERVER['REQUEST_TIME']);
        $day = date('Y-m-d',$unix_time);
        if($today == $day){
            return date('今天 H:i',$unix_time);
        }else{
            return date('n月j日 H:i',$unix_time);
        }
    }
    
    static function feed_vote_class($user_id, $feed_id, $class1='unvoted', $class2='likes', $class3='dislikes'){
        if(!$user_id) return $class1;
        
        $relation = feed::user_vote_feed($user_id, $feed_id);
        if($relation == feed::VOTE_TYPE_LIKE) return $class2;
        elseif($relation == feed::VOTE_TYPE_DISLIKE) return $class3;
        else return $class1;
    }
    
    static function comment_vote_class($user_id, $feed_id, $comment_id, $class1='unvoted', $class2='likes', $class3='dislikes'){
        if(!$user_id) return $class1;
        
        $relation = feed::user_vote_comment($user_id, $feed_id, $comment_id);
        if($relation == feed::VOTE_TYPE_LIKE) return $class2;
        elseif($relation == feed::VOTE_TYPE_DISLIKE) return $class3;
        else return $class1;
    }
    
    static function subscribe_button($user_id,$node_id){
        if(!$user_id)
            return '<button type="button" class="btn-whoaverse-paging btn-xs btn-default" onclick="mustLogin();">订阅</button>';
        
        $relation = node::user_node_relation($user_id,$node_id);
        if($relation)
            return '<button type="button" class="btn-whoaverse-paging btn-xs btn-default" onclick="unsubscribe(this,'.$node_id.');">取消订阅</button>';
        else
            return '<button type="button" class="btn-whoaverse-paging btn-xs btn-default" onclick="subscribe(this,'.$node_id.');">订阅</button>';
    }
    
    /* 子数组按 hot_score 排序
     * $arr 按 parent_id 排序过的评论数组 array('parent_id'=>array(),'parent_id'=>array()...)
     * $id_name
     * $pid 当前 parent_id
     */
    static function show_comments_building($user_id,$feed_id,&$arr,$pid){
        $child_arr = $arr[$pid];
        if($child_arr){
            //$blank .= '-';
            krsort($child_arr);
            foreach($child_arr as $comments){
                foreach($comments as $comment){
                    $hide = (!$comment['enabled'] || $comment['downs']>feed::COMMENT_DOWNS_CEILING) ? true : false;
                    include TEMPLATES_PATH.'comments_hot.html';
                    //echo $blank,$v['c'],'<br>';
                    self::show_comments_building($user_id,$feed_id,$arr,$comment['comment_id']);
                    echo '</div></div>';
                }
            }
        }
    }
}