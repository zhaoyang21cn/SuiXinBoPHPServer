<?php
/**
 * 互动+直播房间成员列表
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

class InteractAvRoom
{
    // 用户名 => string
    private $uid;

    //房间ID => int
    private $avRoomId = -1;

    //上下麦状态 => string; 状态:on-上麦，off-下麦
    private $status = '';

    //心跳时间 => int
    private $modifyTime = 0;

    //成员角色 => int；0-观众；1-主播；2-上麦成员
    private $role = 0;
    
    public function __construct($uid, $avRoomId, $status = 'off', $role = 0)
    {
        $this->uid = $uid;
        $this->avRoomId = $avRoomId;
        $this->status = $status;
        $this->role = $role;
        $this->modifyTime = date('U'); 
    }

    /* 功能：检查房间ID是否存在
     * 说明：房间存在返回1，房间不存在返回0；查询失败返回-1；主要用于房间成员加入
     */
    public function getAvRoomId()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = 'SELECT * from t_new_live_record where av_room_id=:avRoomId';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':avRoomId', $this->avRoomId, PDO::PARAM_INT);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            $result = $stmt->rowCount();
            if($result >= 1)
            {
                return 1;
            }
        }
        catch (PDOException $e)
        {
            return -1;
        }
            
        return 0;
    }

    /* 功能：成员进入房间
     * 说明：如果成员已经存在，覆盖。成功：true, 出错：false
     */
    public function enterRoom()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'REPLACE INTO t_interact_av_room (uid, av_room_id, status, modify_time, role) '
                    . ' VALUES (:uid, :avRoomId, :status, :modifyTime, :role)';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $stmt->bindParam(':avRoomId', $this->avRoomId, PDO::PARAM_INT);
            $stmt->bindParam(':status', $this->status, PDO::PARAM_STR);
            $stmt->bindParam(':modifyTime', $this->modifyTime, PDO::PARAM_INT);
            $stmt->bindParam(':role', $this->role, PDO::PARAM_INT);
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

    /* 功能：成员退出房间
     * 说明：成功：true, 出错：false
     */
    public function exitRoom()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'delete from t_interact_av_room  where uid=:uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
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

    /* 功能：清空房间成员
     * 说明：用于直播结束清空房间成员；成功：true, 出错：false
     */
    static public function ClearRoomByRoomNum($avRoomId)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'delete from t_interact_av_room  where av_room_id=:avRoomId';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':avRoomId', $avRoomId, PDO::PARAM_INT);
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

    /* 功能：获取房间成员
     * 说明：从偏移（offset）处获取N（limit）条APP（appid）的房间（roomnum）的成员信息；
     *      成功返回房间成员信息，失败返回空
     */
    public static function getList($roomnum, $offset = 0, $limit = 50, $appid = 0)
    {
        if ($appid == 0) 
        {
            $whereSql = " WHERE av_room_id = $roomnum ";
        }
        else
        {
            $whereSql = " WHERE appid = $appid AND av_room_id = $roomnum ";
        }

        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return null;
        }
        try
        {
            $sql = 'select uid, role from  t_interact_av_room ' . $whereSql . ' LIMIT ' . (int)$offset . ',' . (int)$limit;
            $stmt = $dbh->prepare($sql);
            $result = $stmt->execute();
            if (!$result)
            {
                return null;
            }
            $rows = $stmt->fetchAll();
            if (empty($rows))
            {
                return array();
            }
            return $rows;
        }
        catch (PDOException $e)
        {
            return null;
        }
        return array();
    }

    /* 功能：获取房间成员总数
     * 说明：APP（appid）的房间（roomnum）的成员总数；
     *      成功返回房间成员总数，失败返回-1
     */
    public static function getCount($roomnum, $appid = 0)
    {
        if ($appid == 0) {
            $whereSql = " WHERE av_room_id=$roomnum";
        }else{
            $whereSql = " WHERE appid = $appid AND av_room_id=$roomnum";
        }

        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = "SELECT COUNT(*) as total FROM t_interact_av_room $whereSql";
            $stmt = $dbh->prepare($sql);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            return $stmt->fetch()['total'];
        }
        catch (PDOException $e)
        {
            return -1;
        }
        return 0;
    }

    /* 功能：更新上下麦状态
     */
    public function updateStatus()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'UPDATE t_interact_av_room SET status=:status, modify_time=:modifyTime WHERE uid=:uid AND av_room_id=:avRoomId';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':status', $this->status, PDO::PARAM_STR);
            $stmt->bindParam(':modifyTime', $this->modifyTime, PDO::PARAM_INT);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $stmt->bindParam(':avRoomId', $this->avRoomId, PDO::PARAM_INT);
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
    
    /* 功能：更新成员心跳时间
     * 说明：更新用户（uid）的心跳时间（time）；role角色
     *      成功返回true，失败返回false
     */
    static public function updateLastUpdateTimeByUid($uid, $role, $time, $video_type)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'UPDATE t_interact_av_room SET modify_time=:time, role=:role, video_type=:video_type WHERE uid = :uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $uid, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_INT);
            $stmt->bindParam(':time', $time, PDO::PARAM_INT);
            $stmt->bindParam(':video_type', $video_type, PDO::PARAM_INT);
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

    /* 功能：删除僵尸成员
     * 说明：由定时清理程序调用。删除心跳超过定时（inactiveSeconds）时间的成员
     *      成功返回true，失败返回false
     */
    public static function deleteDeathRoomMember($inactiveSeconds, $role = 0)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'DELETE FROM t_interact_av_room WHERE role = :role and modify_time < :lastModifyTime';
            $stmt = $dbh->prepare($sql);
            $lastModifyTime = date('U') - $inactiveSeconds;
            $stmt->bindParam(":role", $role, PDO::PARAM_INT);
            $stmt->bindParam(":lastModifyTime", $lastModifyTime, PDO::PARAM_INT);
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
