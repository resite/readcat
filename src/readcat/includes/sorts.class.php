<?php
class sorts{
    static function epoch_seconds($_date){
        //"""Returns the number of seconds from the epoch to date."""
        $epoch = new DateTime('1970-1-1');
        $td = $_date->diff($epoch);
        return $td->days * 86400 + $td->h*3600+$td->i*60+$td->s;// + (float(td.microseconds) / 1000000);微秒都是0
    }

    static function score($ups, $downs){
        return $ups - $downs;
    }
    
    //帖子的得分
    static function _hot($ups, $downs, $_date){
        //"""The hot formula. Should match the equivalent function in postgres."""
        
        $s = self::score($ups, $downs);
        $order = log(max(abs($s), 1), 10);
        if($s>0) $sign =1;
        elseif($s<0) $sign =-1;
        else $sign =0;
        $seconds = $_date - 1134028003;
        return round($order * $sign + $seconds / 45000, 7);
    }
    
    /*
     * ljj 简化 日期输入，转为9位int
     */
    static function hot_score($ups, $downs, $_date){
        if(is_numeric($_date))
            $_date = new DateTime(date('Y-m-d H:i:s',$_date));
        
        $_date = self::epoch_seconds($_date);
        return (int)(self::_hot($ups, $downs, $_date)*100000);
    }
    
    //争议性得分
    static function controversy($ups, $downs){
        if($downs <= 0 || $ups <= 0)
            return 0;
            
        $magnitude = $ups + $downs;
        $balance = $ups > $downs ? $downs/$ups : $ups/$downs;
        return pow($magnitude,$balance);
    }
    
    static function _confidence($ups, $downs){
        $n = $ups + $downs;
        
        if($n == 0)
            return 0;
        
        $z = 1.281551565545; //1.0 = 85%, 1.6 = 95%
        $p = (float)$ups/$n;
        
        $left = $p + 1/(2*$n)*$z*$z;
        $right = $z*sqrt($p*(1-$p)/$n + $z*$z/(4*$n*$n));
        $under = 1+1/$n*$z*$z;
        
        return ($left - $right)/$under;
    }
    
    //评论的得分 ，转为9位int
    static function comment_hot_score($ups, $downs){
        if($ups + $downs == 0)
            return 0;
        else
            //return (int)(self::_confidence($ups, $downs)*1000000000);
            return self::_confidence($ups, $downs);
    }
}