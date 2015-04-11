<?php

/**
 * 数据库方式Session驱动
 *    CREATE TABLE think_session (
 *      session_id varchar(255) NOT NULL,
 *      user_id int(10) unsigned NOT NULL,
 *      session_expire int(11) NOT NULL,
 *      session_data blob,
 *      UNIQUE KEY `session_id` (`session_id`)
 *    );
 * 
 */
class SessionStorageDb implements SessionHandlerInterface{

    /**
     * Session有效时间
     */
    protected $lifeTime = '1800';

    /**
     * session保存的数据库名
     */
    protected $sessionTable = 'session';

    /**
     * 数据库句柄
     */
    protected $hander = array();

    /**
     * 打开Session 
     * @access public 
     * @param string $savePath 
     * @param mixed $sessName  
     */
    public function open($save_path, $session_name) {
        $hander = mysql_connect($this->host, $this->username, $this->password);
        if (!$hander)
            return false;
        mysql_select_db($this->dbname, $hander);
        $this->hander = $hander;
        return true;
    }

    /**
     * 关闭Session 
     * @access public 
     */
    public function close() {
        $this->gc($this->lifeTime);
        return mysql_close($this->hander);
    }

    /**
     * 读取Session 
     * @access public 
     * @param string $sessID 
     */
    public function read($sessID) {
        $hander = $this->hander;
        $res = mysql_query("SELECT session_data AS data FROM " . $this->sessionTable . " WHERE session_id = '$sessID'   AND session_expire >" . time(), $hander);
        if ($res) {
            $row = mysql_fetch_assoc($res);
            return $row['data'];
        }
        return "";
    }

    /**
     * 写入Session 
     * @access public 
     * @param string $sessID 
     * @param String $sessData  
     */
    public function write($sessID, $sessData) {

        $hander = $this->hander;
        $expire = time() + $this->lifeTime;

        $userId = intval($_SESSION['user_id']);
        mysql_query("REPLACE INTO  " . $this->sessionTable . " (  session_id, user_id, session_expire, session_data)  VALUES( '$sessID','$userId' ,'$expire',  '$sessData')", $hander);
        if (mysql_affected_rows($hander))
            return true;

        return false;
    }

    /**
     * 删除Session 
     * @access public 
     * @param string $sessID 
     */
    public function destroy($sessID) {
        $hander = $this->hander;
        mysql_query("DELETE FROM " . $this->sessionTable . " WHERE session_id = '$sessID'", $hander);
        if (mysql_affected_rows($hander))
            return true;
        return false;
    }

    /**
     * Session 垃圾回收
     * @access public 
     * @param string $sessMaxLifeTime 
     */
    public function gc($sessMaxLifeTime) {
        $hander = $this->hander;
        mysql_query("DELETE FROM " . $this->sessionTable . " WHERE session_expire < " . time(), $hander);
        return mysql_affected_rows($hander);
    }

    /**
     * 删除其他user登录设置当前用户id
     */
    public function delete($user_id) {
        $hander = $this->hander;
        $sessID = session_id();
        mysql_query("DELETE FROM " . $this->sessionTable . " WHERE user_id = $user_id AND session_id != '$sessID'", $hander);
    }

    public function setConf($host, $username, $password, $dbname) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
    }

}
