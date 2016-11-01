<?php

require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

/**
 * Date: 2016/4/18
 */
class LiveRecord
{
    const FIELD_TITLE = 'title';
    const FIELD_APPID = 'appid';
    const FIELD_COVER = 'cover';
    const FIELD_CHAT_ROOM_ID = 'chat_room_id';
    const FIELD_HOST_UID = 'host_uid';
    const FIELD_HOST_AVATAR = 'host_avatar';
    const FIELD_HOST_USERNAME = 'host_username';
    const FIELD_LONGITUDE = 'longitude';
    const FIELD_LATITUDE = 'latitude';
    const FIELD_ADDRESS = 'address';
    const FIELD_ADMIRE_COUNT = 'admire_count';
    const FIELD_WATCH_COUNT = 'watch_count';
    const FIELD_TIME_SPAN = 'time_span';
    const FIELD_AV_ROOM_ID = 'av_room_id';
    const FIELD_CREATE_TIME = 'create_time';
    const FIELD_MODIFY_TIME = 'modify_time';

    /**
     * 直播标题
     * @var string
     */
    private $title = '';
    /**
     * 直播appid
     * @var int
     */
    private $appid = 0;
    /**
     * 封面
     * @var string
     */
    private $cover = '';
    /**
     * 聊天室ID
     * @var string
     */
    private $chatRoomId = '';
    /**
     * 主播UID
     * @var string
     */
    private $hostUid = '';
    /**
     * 主播头像
     * @var string
     */
    private $hostAvatar = '';
    /**
     * 主播用户名
     * @var string
     */
    private $hostUserName = '';
    /**
     * 经度
     * @var float
     */
    private $longitude = 0.0;
    /**
     * 纬度
     * @var float
     */
    private $latitude = 0.0;
    /**
     * 地址
     * @var string
     */
    private $address = '';

    /**
     * 点赞人数
     * @var int
     */
    private $admireCount = 0;
    /**
     * 直播时长
     * @var int
     */
    private $timeSpan = 0;
    /**
     * 观看人数
     * @var int
     */
    private $watchCount = 0;
    /**
     * 创建时间
     * @var string
     */
    private $createTime;
    /**
     * av房间ID
     * @var int
     */
    private $avRoomId = 0;

    private function InitFromDBFields($fields)
    {
        $this->createTime = strtotime($fields[self::FIELD_CREATE_TIME]);
        $this->title = $fields[self::FIELD_TITLE];
        $this->appid = $fields[self::FIELD_APPID];
        $this->cover = $fields[self::FIELD_COVER];
        $this->longitude = (float)$fields[self::FIELD_LONGITUDE];
        $this->latitude = (float)$fields[self::FIELD_LATITUDE];
        $this->address = $fields[self::FIELD_ADDRESS];
        $this->hostUid = $fields[self::FIELD_HOST_UID];
        $this->hostAvatar = $fields[self::FIELD_HOST_AVATAR];
        $this->hostUserName = $fields[self::FIELD_HOST_USERNAME];
        $this->admireCount = (int)$fields[self::FIELD_ADMIRE_COUNT];
        $this->chatRoomId = $fields[self::FIELD_CHAT_ROOM_ID];
        $this->avRoomId = (int)$fields[self::FIELD_AV_ROOM_ID];
        $this->timeSpan = (int)$fields[self::FIELD_TIME_SPAN];
        $this->watchCount = (int)$fields[self::FIELD_WATCH_COUNT];
    }


    /**
     * 将直播记录存入数据库
     * @return int 成功: 插入ID, 失败：-1
     */
    public function save()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return -1;
        }
        $fields = array(
            self::FIELD_TITLE => $this->title,
            self::FIELD_APPID => $this->appid,
            self::FIELD_COVER => $this->cover,
            self::FIELD_CHAT_ROOM_ID => $this->chatRoomId,
            self::FIELD_AV_ROOM_ID => $this->avRoomId,
            self::FIELD_HOST_UID => $this->hostUid,
            self::FIELD_HOST_USERNAME => $this->hostUserName,
            self::FIELD_HOST_AVATAR => $this->hostAvatar,
            self::FIELD_LONGITUDE => $this->longitude,
            self::FIELD_LATITUDE => $this->latitude,
            self::FIELD_ADDRESS => $this->address,
            self::FIELD_ADMIRE_COUNT => $this->admireCount,
            self::FIELD_WATCH_COUNT => $this->watchCount,
            self::FIELD_TIME_SPAN => $this->timeSpan,
            self::FIELD_CREATE_TIME => date('Y-m-d H:i:s'),
        );
        try
        {
            $sql = 'REPLACE INTO t_live_record (';
            $sql .= implode(', ', array_keys($fields)) . ')';
            $params = array();
            foreach ($fields as $k => $v)
            {
                $params[] = ':' . $k;
            }
            $sql .= ' VALUES (' . implode(', ', $params) . ')';
            $stmt = $dbh->prepare($sql);
            // foreach ($fields as $k => $v)
            // {
            //     $type = self::getType($k);
            //     // echo ':' . $k . '= ' . $v . "\r\n";
            //     $stmt->bindParam(':' . $k, $v, $type);
            // }
            // die($sql);
            $result = $stmt->execute($fields);
            if (!$result)
            {
                return -1;
            }
            return $dbh->lastInsertId();
        }
        catch (PDOException $e)
        {
            return -1;
        }
    }

    /**
     * 从数据库删除直播记录
     * @return bool 成功：true, 失败：false
     */
    public function delete()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'DELETE FROM t_live_record WHERE ' .
                   self::FIELD_HOST_UID . ' = ?';
            $stmt = $dbh->prepare($sql);
            $hostUid = $this->hostUid;
            $stmt->bindParam(1, $hostUid, PDO::PARAM_STR);
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

    /**
     * 删除不活跃的记录
     * @param  int $inactiveSeconds 多久没更新
     * @return bool 成功：true; 失败：false.
     */
    public static function deleteInactiveRecord($inactiveSeconds)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'DELETE FROM t_live_record WHERE ' .
                   self::FIELD_MODIFY_TIME .  ' < ?';
            $stmt = $dbh->prepare($sql);
            $lastModifyTime = date('Y-m-d H:i:s', time() - $inactiveSeconds);
            $stmt->bindParam(1, $lastModifyTime, PDO::PARAM_STR);
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

    /**
     * 获取直播总数
     * @return int 直播总数，出错返回负数
     */
    public static function getCount($appid)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = $appid ";
        }
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = "SELECT COUNT(*) as total FROM t_live_record $whereSql";
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

    /**
     * 根据主播Uid从数据库加载数据
     * @return int 成功：1，不存在记录: 0, 出错：-1
     */
    public function loadByHostUid($hostUid)
    {
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return null;
        }
        $fields = array(
            self::FIELD_CREATE_TIME,
            self::FIELD_TITLE,
            self::FIELD_APPID,
            self::FIELD_COVER,
            self::FIELD_LONGITUDE,
            self::FIELD_LATITUDE,
            self::FIELD_ADDRESS,
            self::FIELD_HOST_UID,
            self::FIELD_HOST_AVATAR,
            self::FIELD_HOST_USERNAME,
            self::FIELD_ADMIRE_COUNT,
            self::FIELD_WATCH_COUNT,
            self::FIELD_TIME_SPAN,
            self::FIELD_CHAT_ROOM_ID,
            self::FIELD_AV_ROOM_ID,
        );
        try
        {
            $sql = 'SELECT ' . implode(',', $fields) .
                   ' FROM t_live_record WHERE ' .
                   self::FIELD_HOST_UID . ' = :host_uid ';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':host_uid', $hostUid, PDO::PARAM_STR);
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
            $this->InitFromDBFields($row);
            return 1;
        }
        catch (PDOException $e)
        {
            return -1;
        }
        return -1;
    }


    /**
     * 获取直播列表
     * @param  integer $offset
     * @param  integer $limit
     * @return array  LiveRecord对象数组,出错返回null
     */
    public static function getList($appid, $offset = 0, $limit = 50)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = $appid ";
        }
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return null;
        }
        $fields = array(
            self::FIELD_CREATE_TIME,
            self::FIELD_TITLE,
            self::FIELD_APPID,
            self::FIELD_COVER,
            self::FIELD_LONGITUDE,
            self::FIELD_LATITUDE,
            self::FIELD_ADDRESS,
            self::FIELD_HOST_UID,
            self::FIELD_HOST_AVATAR,
            self::FIELD_HOST_USERNAME,
            self::FIELD_ADMIRE_COUNT,
            self::FIELD_WATCH_COUNT,
            self::FIELD_TIME_SPAN,
            self::FIELD_CHAT_ROOM_ID,
            self::FIELD_AV_ROOM_ID,
        );
        try
        {
            $sql = 'SELECT ' . implode(',', $fields) .
                   ' FROM t_live_record ' . $whereSql . ' ORDER BY id DESC LIMIT ' .
                   (int)$offset . ',' . (int)$limit;
//var_dump($sql);
//exit(0);
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
            $list = array();
            foreach ($rows as $row)
            {
                $record = new LiveRecord();
                $record->InitFromDBFields($row);
                $list[] = $record;
            }
            return $list;
        }
        catch (PDOException $e)
        {
            return null;
        }
        return array();
    }

    private static function getType($field)
    {
        switch ($field)
        {
            case self::FIELD_TITLE:
            case self::FIELD_APPID:
            case self::FIELD_COVER:
            case self::FIELD_CHAT_ROOM_ID:
            case self::FIELD_HOST_UID:
            case self::FIELD_HOST_AVATAR:
            case self::FIELD_HOST_USERNAME:
                return PDO::PARAM_STR;
            case self::FIELD_LONGITUDE:
            case self::FIELD_LATITUDE:
                // PDO PARAM常量无浮点数，默认得用字符串。
                return pdo::PARAM_STR;
            case self::FIELD_ADDRESS:
                return PDO::PARAM_STR;
            case self::FIELD_WATCH_COUNT:
            case self::FIELD_ADMIRE_COUNT:
            case self::FIELD_TIME_SPAN:
            case self::FIELD_AV_ROOM_ID:
                return PDO::PARAM_INT;
            case self::FIELD_CREATE_TIME:
            case self::FIELD_MODIFY_TIME:
                return PDO::PARAM_STR;
            default:
                break;
        }
        return '';
    }

    /**
     * 根据主播Uid更新数据
     * @param  string $hostUid 主播Uid
     * @param  LiveDynamicData $data   直播动态数据
     * @return int  成功：更新记录数;出错：-1
     */
    public static function updateByHostUid($hostUid, $data)
    {
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $data[self::FIELD_MODIFY_TIME] = date('Y-m-d H:i:s');
            $sql = 'UPDATE t_live_record SET ';
            $fields = array();
            foreach ($data as $k => $v)
            {
                $fields[] = $k . '=' . ':' . $k;
            }
            $sql .= implode(', ', $fields);
            $sql .= ' WHERE ' . self::FIELD_HOST_UID . ' = :host_uid';
            $stmt = $dbh->prepare($sql);
            foreach ($data as $k => $v)
            {
                $type = self::getType($k);
                // Should use $data[$k]
                $stmt->bindParam(':' . $k, $data[$k], $type);
            }
            $hostUidType = self::getType(self::FIELD_HOST_UID);
            $stmt->bindParam(
                ':' . self::FIELD_HOST_UID, $hostUid, $hostUidType);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            $count = $stmt->rowCount();
            return $count;
        }
        catch (PDOException $e)
        {
            return -1;
        }
        return 0;
    }


    // Getters and Setters

    /**
     * Gets 直播标题.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets 直播标题.
     *
     * @param string $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Gets 直播appid.
     *
     * @return string
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * Sets 直播标题.
     *
     * @param string $appid the appid
     *
     * @return self
     */
    public function setAppid($appid)
    {
        $this->appid = $appid;
    }

    /**
     * Gets 封面.
     *
     * @return string
     */
    public function getCover()
    {
        return $this->cover;
    }

    /**
     * Sets 封面.
     *
     * @param string $cover the cover
     *
     * @return self
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    /**
     * Gets 聊天室ID.
     *
     * @return string
     */
    public function getChatRoomId()
    {
        return $this->chatRoomId;
    }

    /**
     * Sets 聊天室ID.
     *
     * @param string $chatRoomId the chat room id
     *
     * @return self
     */
    public function setChatRoomId($chatRoomId)
    {
        $this->chatRoomId = $chatRoomId;
    }

    /**
     * Gets 主播UID.
     *
     * @return string
     */
    public function getHostUid()
    {
        return $this->hostUid;
    }

    /**
     * Sets 主播UID.
     *
     * @param string $hostUid the host uid
     *
     * @return self
     */
    public function setHostUid($hostUid)
    {
        $this->hostUid = $hostUid;
    }

    /**
     * Gets 主播头像.
     *
     * @return string
     */
    public function getHostAvatar()
    {
        return $this->hostAvatar;
    }

    /**
     * Sets 主播头像.
     *
     * @param string $hostAvatar the host avatar
     *
     * @return self
     */
    public function setHostAvatar($hostAvatar)
    {
        $this->hostAvatar = $hostAvatar;
    }

    /**
     * Gets 主播用户名.
     *
     * @return string
     */
    public function getHostUserName()
    {
        return $this->hostUserName;
    }

    /**
     * Sets 主播用户名.
     *
     * @param string $hostUserName the host user name
     *
     * @return self
     */
    public function setHostUserName($hostUserName)
    {
        $this->hostUserName = $hostUserName;
    }

    /**
     * Gets 经度.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Sets 经度.
     *
     * @param float $longitude the longitude
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Gets 纬度.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Sets 纬度.
     *
     * @param float $latitude the latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Gets 地址.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets 地址.
     *
     * @param string $address the address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Gets 点赞人数.
     *
     * @return int
     */
    public function getAdmireCount()
    {
        return $this->admireCount;
    }

    /**
     * Sets 点赞人数.
     *
     * @param int $admireCount the admire count
     *
     * @return self
     */
    public function setAdmireCount($admireCount)
    {
        $this->admireCount = $admireCount;
    }

    /**
     * Gets 直播时长.
     *
     * @return int
     */
    public function getTimeSpan()
    {
        return $this->timeSpan;
    }

    /**
     * Sets 直播时长.
     *
     * @param int $timeSpan the time span
     *
     * @return self
     */
    public function setTimeSpan($timeSpan)
    {
        $this->timeSpan = $timeSpan;
    }

    /**
     * Gets 观看人数.
     *
     * @return int
     */
    public function getWatchCount()
    {
        return $this->watchCount;
    }

    /**
     * Sets 观看人数.
     *
     * @param int $watchCount the watch count
     *
     * @return self
     */
    public function setWatchCount($watchCount)
    {
        $this->watchCount = $watchCount;
    }

    /**
     * Gets 创建时间.
     *
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Sets 创建时间.
     *
     * @param string $createTime the create time
     *
     * @return self
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * Gets av房间ID.
     *
     * @return int
     */
    public function getAvRoomId()
    {
        return $this->avRoomId;
    }

    /**
     * Sets av房间ID.
     *
     * @param int $avRoomId the av room id
     *
     * @return self
     */
    public function setAvRoomId($avRoomId)
    {
        $this->avRoomId = $avRoomId;
    }
}


