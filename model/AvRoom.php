<?php

require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

class AvRoom
{

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
            $sql = 'INSERT INTO t_av_room (uid) VALUES (:uid)';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return false;
            }
            $this->id = $dbh->lastInsertId();
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
            $sql = 'SELECT id FROM t_av_room WHERE uid = :uid';
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
                return new CmdResp(ERR_SERVER, 'Server error'.$uid.$time.$stmt);
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
}

?>
