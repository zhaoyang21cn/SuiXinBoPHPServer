<?php

require_once dirname(__FILE__) . '/../path.php';
require_once LIB_PATH . '/db/DB.php';

/**
 * Date: 2016/4/18
 */
class LiveRecord
{
    public static FIELD_TITLE = 'title';
    public static FIELD_COVER = 'cover';
    public static FIELD_CHAT_ROOM_ID = 'chat_room_id';
    public static FIELD_HOST_UID = 'host_uid';
    public static FIELD_HOST_AVATAR = 'host_avatar';
    public static FIELD_HOST_USERNAME = 'host_username';
    public static FIELD_LONGITUDE = 'longitude';
    public static FIELD_LATITUDE = 'latitude';
    public static FIELD_ADDRESS = 'address';
    public static FIELD_ADMIRE_COUNT = 'admire_count';
    public static FIELD_WATCH_COUNT = 'watch_count';
    public static FIELD_TIME_SPAN = 'time_span';
    public static FIELD_AV_ROOM_ID = 'av_room_id';
    private static FIELD_CREATE_TIME = 'create_time';
    private static FIELD_MODIFY_TIME = 'modify_time';

    /**
     * 直播标题
     * @var string
     */
    private $title = '';
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
     * @var INT
     */
    private $avRoomId = 0;

    private function InitFromDBFields($fields)
    {
        $this->createTime = strtotime($fields['create_time']);
        $this->title = $fields['title'];
        $this->cover = $fields['cover'];
        $this->longitude = (float)$fields['longitude'];
        $this->latitude = (float)$fields['latitude'];
        $this->address = $fields['address'];
        $this->hostUid = $fields['host_uid'];
        $this->hostAvatar = $fields['host_avatar'];
        $this->hostUserName = $fields['host_username'];
        $this->admireCount = (int)$fields['admire_count'];
        $this->chatRoomId = $fields['chat_room_id'];
        $this->avRoomId = (int)$fields['av_room_id'];
        $this->timeSpan = (int)$fields['time_span'];
        $this->watchCount = (int)$fields['watch_count'];
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
            return false;
        }
        $fields = array(
            self::FIELD_TITLE => $this->title,
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
            $sql .= ' VALUES (' . implode(',', $params) . ')';
            $stmt = $dbh->prepare($sql);
            foreach ($fields as $k => $v)
            {
                $type = self::getType($k);
                $stmt->bindParam(':' . $k, $v, $type);
            }
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            return $dbh->getLastInsertId();
        }
        catch (PDOException $e)
        {
            return -1;
        }
    }

    /**
     * 删除不活跃的直播
     * @param  int $inactiveSeconds 不活跃的秒数
     * @return                   
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
            $sql = 'DELETE FROM t_live_record WHERE modify_time < ?';
            $stmt = $dbh->prepare($sql);
            $lastModifyTime = date('Y-m-d H:i:s', time() - inactiveSeconds);
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
    public static function getCount()
    {
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1;
        }
        try
        {
            $sql = 'SELECT COUNT(*) as total FROM t_live_record';
            $stmt = $dbh->prepare($sql);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            return $stmt->fetchOne()['total'];
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
                   ' FROM t_live_record WHERE host_uid = :host_uid ';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':host_uid', $hostUid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                return -1;
            }
            $row = $stmt->fetchOne();
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
    public static function getList($offset = 0, $limit = 50)
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
                   ' FROM t_live_record ORDER BY id DESC LIMIT ' . 
                   (int)$offset . ',' . (int)$limit;
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
            foreach ($rows => $row)
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
            return null;
        }
        try
        {
            $sql = 'UPDATE t_live_record SET ';
            $fields = array();
            foreach ($data as $k => $v)
            {
                $type = self::getType($k);
                $placeHolder = ':' . $k;
                $fields[] = $k . ' = ' . $placeHolder;
                $params[$placeHolder] = $v;
            }
            $sql .= implode(',', $fields);
            $sql .= ' WHERE host_uid = :host_uid';
            $stmt = $dbh->prepare($sql);
            foreach ($params as $k => $v)
            {
                $type = self::getType($k);
                $stmt->bindParam(':' . $k, $v, $type);
            }
            $hostUidType = self::getType(self::FIELD_HOST_UID);
            $stmt->bindParam(':host_uid', $hostUid, $hostUidType);
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
     * @return INT
     */
    public function getAvRoomId()
    {
        return $this->avRoomId;
    }
    
    /**
     * Sets av房间ID.
     *
     * @param INT $avRoomId the av room id
     *
     * @return self
     */
    public function setAvRoomId(INT $avRoomId)
    {
        $this->avRoomId = $avRoomId;
    }
}


