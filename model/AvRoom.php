<?php

require_once dirname(__FILE__) . '/../Config.php';
require_once LIB_PATH . '/db/DB.php';

class AvRoom
{
    const FIELD_MODIFY_TIME = 'last_update_time';
    /**
     * Uid
     * @var string
     */
    private $uid;
    /**
     * Av房间ID
     * @var int
     */
    private $id = -1;

    public function __construct($uid)
    {
        $this->uid = $uid;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    /*
     * 生成旁路推流直播的MD5码
     */
    private function createStreamIdMd5($dbh)
    {
        $aux_md5 = md5($this->id . '_' . $this->uid . '_aux');
        $main_md5 = md5($this->id . '_' . $this->uid . '_main');

        $sql = 'UPDATE t_av_room SET aux_md5 = :aux_md5, main_md5 = :main_md5 where id = :id';
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':aux_md5', $aux_md5, PDO::PARAM_STR);
        $stmt->bindParam(':main_md5', $main_md5, PDO::PARAM_STR);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $result = $stmt->execute();
        if (!$result) 
        {
            return false;
        }
        return true;
    }

    /**
     * 创建 AvRoomId
     * @param  string $uid 
     * @return int      成功：true, 出错：false
     */
    public function create()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'INSERT INTO t_av_room (uid, create_time) VALUES (:uid, :create_time)';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $stmt->bindParam(':create_time', date('U'), PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }

            $this->id = $dbh->lastInsertId();
            
            $result = $this->createStreamIdMd5($dbh);
            if (!$result) //如果失败执行到这里，返回失败；再次创建时执行load进行弥补
            {
                return false;
            }

            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
        return false;
    }

    /**
     * 从数据库加载
     * @return int 记录存在：1，不存在：0，出错：-1
     */
    public function load()
    {
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = 'SELECT id, aux_md5, main_md5 FROM t_av_room WHERE uid = :uid ORDER BY id DESC LIMIT 1';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            $row = $stmt->fetch();
            if (empty($row))
            {
                return 0;
            }
            $this->id = $row['id'];

            //当create()执行insert成功，但是设置MD5失败时，在这里执行第二次设置
            if(empty($row['aux_md5']) || empty($row['main_md5']))
            {
                $result = $this->createStreamIdMd5($dbh);
                if (!$result) //如果此次执行也失败，则用户重试即可
                {
                    return -1;
                }
            }
            return 1;
        }
        catch (PDOException $e)
        {
            return -1;
        }
        return -1;
    }

    // Getter and Setters

    /**
     * Gets Av房间ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function exitAvRoom()
    {
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = 'DELETE FROM t_av_room WHERE uid = :uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
        }
        catch (PDOException $e)
        {
            return -1;
        }
        return 0;
    }

    /**
     * 生成跨房连麦密钥
     * @param $key          权限密钥
     * @param $uid          要连接的用户ID
     * @param $roomnum      要连接的用户房间号
     * @param $link_sig     用于接收生成的sig
     * @param $error_msg    接收错误码
     * @return int          成功返回ERR_SUCCESS
     */
    public function getLinkSig($key, $uid, $roomnum, &$link_sig, &$error_msg)
    {
        $sig = '';
        // 生成sig
        $cmd = DEPS_PATH . '/bin/linkSig'
            . ' ' . escapeshellarg($this->uid)
            . ' ' . escapeshellarg($this->id)
            . ' ' . escapeshellarg($uid)
            . ' ' . escapeshellarg($roomnum)
            . ' ' . escapeshellarg($key);
        $ret = exec($cmd, $sig, $status);
        if($status != 0)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }

        $link_sig = $sig[0];
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /**
     * @param $uid 用户id
     * @param $id roomnum房价号
     * @param $title 直播名称
     * @param $cover 封面
     * @return bool 是否更新成功
     */
    static public function updateRoomInfoById($uid, $id, $title, $cover, $device=0)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'update t_av_room set title=:title,cover=:cover,device=:device where uid=:uid and id=:id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':cover', $cover, PDO::PARAM_STR);
            $stmt->bindParam(':device', $device, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
        return false;
    }

    static public function finishRoomByUidAndRoomNum($uid, $id)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'update t_av_room set finish_time=:finish_time where uid=:uid and id=:id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':finish_time', date('U'), PDO::PARAM_INT);
            $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
        return false;
    }

    static public function updateLastUpdateTimeByUid($uid, $time)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'update t_av_room set last_update_time=:time where uid=:uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':time', $time, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
        return false;
    }

    static public function updateLastUpdateTimeByUidAndRoomNum($uid, $roomnum, $time)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'update t_av_room set last_update_time=:time where uid=:uid and id=:id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':time', $time, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
            $stmt->bindParam(':id', $roomnum, PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
        return false;
    }

    static public function getRoomById($id)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return '';
        }
        try
        {
            $sql = 'select id, uid, title, cover from t_av_room where id = :id';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return '';
            }
            $row = $stmt->fetch();
            if (empty($row))
            {
                return '';
            }
            return array(
                'roomnum' => $row['id'],
                'uid' => $row['uid'],
                'title' => $row['title'],
                'cover' => $row['cover']
            );
        }
        catch (PDOException $e)
        {
            return '';
        }
        return '';
    }

    /* 功能：通过视频的md5码获取用户名和房间号
     * 说明：后期修订返回uid同时，附带其所在房间号。后台DB的t_av_room维护一份分别由roomnum_uid_aux和roomnum_uid_main
     *        生成的aux_md5和main_md5字段。当录制完成自动执行回调时，依据传递的channel_id中的md5码查询t_av_room，实现
     *        反解析roomnum和uid。(channel_id参考CallbackCmd.php)
     */
    static public function getUidByMd5($md5)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return '';
        }
        try
        {
            $sql = 'select id, uid from t_av_room where aux_md5 = :aux_md5 or main_md5 = :main_md5';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':aux_md5', $md5, PDO::PARAM_STR);
            $stmt->bindParam(':main_md5', $md5, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return '';
            }
            $row = $stmt->fetch();
            if (empty($row))
            {
                return '';
            }
            return array('roomnum' => $row['id'], 'uid' => $row['uid']);
        }
        catch (PDOException $e)
        {
            return '';
        }
        return '';
    }

    static public function finishInactiveRecord($inactiveSeconds)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'UPDATE t_av_room SET finish_time = ? WHERE ' .
                self::FIELD_MODIFY_TIME .  ' < ? '.
                'AND finish_time = 0';
            $stmt = $dbh->prepare($sql);
            $now = date('U');
            $lastModifyTime = $now - $inactiveSeconds;
            $stmt->bindParam(1, $now, PDO::PARAM_INT);
            $stmt->bindParam(2, $lastModifyTime, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
        }
        catch (PDOException $e)
        {
            return false;
        }
        return true;
    }
}

?>
