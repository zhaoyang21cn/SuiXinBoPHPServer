<?php
/**
 * 新的直播记录表
 * Date: 2016/11/17
 * Update: 2016/12/29
 */
require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

class NewLiveRecord
{
    const FIELD_TITLE = 'title';
    const FIELD_COVER = 'cover';
    const FIELD_TYPE = 'type';
    const FIELD_HOST_UID = 'host_uid';
    const FIELD_LONGITUDE = 'longitude';
    const FIELD_LATITUDE = 'latitude';
    const FIELD_ADDRESS = 'address';
    const FIELD_ADMIRE_COUNT = 'admire_count';
    const FIELD_CHAT_ROOM_ID = 'chat_room_id';
    const FIELD_AV_ROOM_ID = 'av_room_id';
    const FIELD_CREATE_TIME = 'create_time';
    const FIELD_MODIFY_TIME = 'modify_time';
    const FIELD_APPID = 'appid';
    const FIELD_ROOM_TYPE = 'room_type';
    const FIELD_DEVICE = 'device';
    const FIELD_VIDEO_TYPE = 'video_type';
    const FIELD_PLAY_URL1 = 'play_url1';
    const FIELD_PLAY_URL2 = 'play_url2';
    const FIELD_PLAY_URL3 = 'play_url3'; 

    // 直播标题 => sring
    private $title = '';

    // 直播appid => int
    private $appid = 0;

    // 封面 => string
    private $cover = '';

    // 聊天室ID => string
    private $chatRoomId = '';

    // 主播UID => string
    private $hostUid = '';

    // 经度 => float
    private $longitude = 0.0;

    // 纬度 => float
    private $latitude = 0.0;

    // 地址 => string
    private $address = '';

    // 点赞数 => int
    private $admireCount = 0;
   
    // 创建时间 => string
    private $createTime;

    // av房间ID => int
    private $avRoomId = 0;

    // 房间类型 => string
    private $roomType = '';

    // 设备类型 => int; 0-IOS  1-Android  2-PC
    private $device = 0;

    // 视频类型 => int; 0-摄像头 1-屏幕分享
    private $videoType = 0;

    // 播放地址1 => string
    private $playUrl1 = '';

    // 播放地址2 => string
    private $playUrl2 = '';

    // 播放地址3 => string
    private $playUrl3 = '';

    /* 功能：查询结果行初始化记录对象
     */
    private function InitFromDBFields($fields)
    {
        $this->title = $fields[self::FIELD_TITLE];
        $this->cover = $fields[self::FIELD_COVER];
        $this->longitude = (float)$fields[self::FIELD_LONGITUDE];
        $this->latitude = (float)$fields[self::FIELD_LATITUDE];
        $this->address = $fields[self::FIELD_ADDRESS];
        $this->hostUid = $fields[self::FIELD_HOST_UID];
        $this->admireCount = (int)$fields[self::FIELD_ADMIRE_COUNT];
        $this->chatRoomId = $fields[self::FIELD_CHAT_ROOM_ID];
        $this->avRoomId = (int)$fields[self::FIELD_AV_ROOM_ID];
        $this->roomType = $fields[self::FIELD_ROOM_TYPE];
        if(array_key_exists(self::FIELD_APPID, $fields))
            $this->appid = $fields[self::FIELD_APPID];
        if(array_key_exists(self::FIELD_CREATE_TIME, $fields))
            $this->createTime = strtotime($fields[self::FIELD_CREATE_TIME]);
        if(array_key_exists(self::FIELD_DEVICE, $fields))
            $this->device = $fields[self::FIELD_DEVICE];
        if(array_key_exists(self::FIELD_VIDEO_TYPE, $fields))
            $this->videoType = $fields[self::FIELD_VIDEO_TYPE];
        if(array_key_exists(self::FIELD_PLAY_URL1, $fields))
            $this->playUrl1 = $fields[self::FIELD_PLAY_URL1];
        if(array_key_exists(self::FIELD_PLAY_URL2, $fields))
            $this->playUrl2 = $fields[self::FIELD_PLAY_URL2];
        if(array_key_exists(self::FIELD_PLAY_URL3, $fields))
            $this->playUrl3 = $fields[self::FIELD_PLAY_URL3];
    }

    public function genPlayUrl($bizId, $url)
    {
        $livecode = $this->avRoomId . '_' . $this->hostUid; 
        if($this->videoType == 0) 
        { //摄像头
            $livecode = $livecode . '_main';
        } 
        else if ($this->videoType == 1) 
        { //屏幕分享
            $livecode =  $livecode . '_aux';
        } 
        else 
        {
            return false;
        }
/*
        $cmd = 'echo  -n ' . $livecode . '| md5sum - | awk -F\' \' \'{print $1}\'';
        exec($cmd, $output, $ret);
        if ($ret != 0) 
        {
            return false;
        }

        $livecode = $bizId . '_' . $output[0]; //直播码
*/
        $md5sum = md5($livecode);
        $livecode = $bizId . '_' . $md5sum; //直播码
        
        $this->playUrl1 = 'rtmp://' . $bizId . '.' . $url . $livecode;
        $this->playUrl2 = 'http://' . $bizId . '.' . $url . $livecode . '.flv';
        $this->playUrl3 = 'http://' . $bizId . '.' . $url . $livecode . '.m3u8';

        return true;
    }

    /* 功能：将直播记录存入数据库
     * 说明：成功返回插入的ID, 失败返回-1
     */
    public function save()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return -1;
        }
        $fields = array(
            self::FIELD_CREATE_TIME => date('Y-m-d H:i:s'),
            self::FIELD_MODIFY_TIME => date('U'),
            self::FIELD_APPID => $this->appid,
            self::FIELD_TITLE => $this->title,
            self::FIELD_COVER => $this->cover,
            self::FIELD_HOST_UID => $this->hostUid, 
            self::FIELD_AV_ROOM_ID => $this->avRoomId,
            self::FIELD_CHAT_ROOM_ID => $this->chatRoomId, 
            self::FIELD_ROOM_TYPE => $this->roomType,
            self::FIELD_VIDEO_TYPE => $this->videoType,
            self::FIELD_DEVICE => $this->device,
            self::FIELD_ADMIRE_COUNT => $this->admireCount,
            self::FIELD_PLAY_URL1 => $this->playUrl1,
            self::FIELD_PLAY_URL2 => $this->playUrl2,
            self::FIELD_PLAY_URL3 => $this->playUrl3,  
            self::FIELD_LONGITUDE => $this->longitude,
            self::FIELD_LATITUDE => $this->latitude,
            self::FIELD_ADDRESS => $this->address,  
        );
        try
        {
            $sql = 'REPLACE INTO t_new_live_record (';
            $sql .= implode(', ', array_keys($fields)) . ')';
            $params = array();
            foreach ($fields as $k => $v)
            {
                $params[] = ':' . $k;
            }
            $sql .= ' VALUES (' . implode(', ', $params) . ')';
            $stmt = $dbh->prepare($sql);
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

    /* 功能：删除直播记录
     * 说明：将用户hostUid的直播记录删除。一个用户同一时间只能开启一个直播；
     *       成功返回true 失败返回false
     */
    static public function delete($hostUid)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        try
        {
            $sql = 'DELETE FROM t_new_live_record WHERE ' .
                   self::FIELD_HOST_UID . ' = ?';
            $stmt = $dbh->prepare($sql);
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

    /* 功能：删除死亡直播记录
     * 说明：超过inactiveSeconds时间间隔未收到主播心跳，则视为直播死亡，由定时清理程序调用删除
     *       成功返回true 失败返回false
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
            $sql = 'DELETE FROM t_new_live_record WHERE ' .
                   self::FIELD_MODIFY_TIME .  ' < ?';
            $stmt = $dbh->prepare($sql);
            $lastModifyTime = date('U') - $inactiveSeconds;
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

    /* 功能：获取直播记录总数
     * 说明：超过inactiveSeconds时间间隔未收到主播心跳，则视为直播死亡，由定时清理程序调用删除
     *       成功返回直播总数，出错返回-1
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
            $sql = "SELECT COUNT(*) as total FROM t_new_live_record $whereSql";
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

    /* 功能：根据主播Uid加载直播数据
     * 说明：成功：1，不存在记录: 0, 出错：-1
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
            self::FIELD_APPID,
            self::FIELD_TITLE,          
            self::FIELD_COVER,          
            self::FIELD_HOST_UID,
            self::FIELD_CHAT_ROOM_ID,
            self::FIELD_AV_ROOM_ID,
            self::FIELD_ADMIRE_COUNT,           
            self::FIELD_LONGITUDE,
            self::FIELD_LATITUDE,
            self::FIELD_ADDRESS,
        );
        try
        {
            $sql = 'SELECT ' . implode(',', $fields) .
                   ' FROM t_new_live_record WHERE ' .
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

    /* 功能：查询直播记录
     * 说明：查询指定的记录字段
     */     
    public static function getList($appid, $roomType, $offset, $limit, $fields)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = $appid ";
        }
        if ($roomType == 'live') {
            if(empty($whereSql))
            {
                $whereSql .= " WHERE room_type = '$roomType' ";
            }
            else
            {
                $whereSql .= " AND room_type = '$roomType' ";
            }
        }
       
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return null;
        }

        try
        {
            $sql = 'SELECT ' . implode(',', $fields) .
                   ' FROM t_new_live_record ' . $whereSql . ' ORDER BY id DESC LIMIT ' .
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
            return $rows;
        }
        catch (PDOException $e)
        {
            return null;
        }
        return array();
    }

    /* 功能：查询直播记录列表
     * 说明：成功返回直播记录列表，失败返回null
     */
    public static function getLiveRoomList($appid, $roomType, $offset = 0, $limit = 50)
    {
        $fields = array(
            self::FIELD_TITLE,
            self::FIELD_COVER,
            self::FIELD_HOST_UID,
            self::FIELD_AV_ROOM_ID,
            self::FIELD_CHAT_ROOM_ID,
            self::FIELD_ROOM_TYPE,
            self::FIELD_ADMIRE_COUNT,
            self::FIELD_LONGITUDE,
            self::FIELD_LATITUDE,
            self::FIELD_ADDRESS,  
        );

        $rows = NewLiveRecord::getList($appid, $roomType, $offset, $limit, $fields);
        if(is_null($rows)) 
        {
            return null;
        }
        $data = array();
        foreach ($rows as $row)
        {
            $data[] = array(
                'uid' => $row[self::FIELD_HOST_UID],
                'info' => array(
                    'title' => $row[self::FIELD_TITLE],
                    'roomnum' => $row[self::FIELD_AV_ROOM_ID],
                    'type' => $row[self::FIELD_ROOM_TYPE],
                    'groupid' => $row[self::FIELD_CHAT_ROOM_ID],
                    'cover' => $row[self::FIELD_COVER],
                    'thumbup' => $row[self::FIELD_ADMIRE_COUNT],
                ),
            );
        }
        return $data;
    }


    /* 功能：拉取房间的推流地址
     * 说明：成功返回播放url json数据，失败返回null
     */
    public static function getLiveStreamList($appid, $roomType, $offset = 0, $limit = 50)
    {
        $fields = array(
            self::FIELD_COVER,
            self::FIELD_HOST_UID,
            self::FIELD_PLAY_URL1,
            self::FIELD_PLAY_URL2,
            self::FIELD_PLAY_URL3,
        );

        $rows = NewLiveRecord::getList($appid, $roomType, $offset, $limit, $fields);
        if(is_null($rows)) 
        {
            return null;
        }
        $data = array();
        foreach ($rows as $row)
        {
            $data[] = array(
                'uid' => $row[self::FIELD_HOST_UID],
                'cover' => $row[self::FIELD_COVER],
                'address' => $row[self::FIELD_PLAY_URL1],
                'address2' => $row[self::FIELD_PLAY_URL2],
                'address3' => $row[self::FIELD_PLAY_URL3],
            );
        }
        return $data;
    }

    /* 功能：依据房间ID拉取本房间的推流地址
     * 说明：成功返回播放url json数据，失败返回null
     */
    public static function getLiveStreamByRoomID($appid, $avRoomId)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = $appid ";
        }
       
        if(empty($whereSql))
        {
            $whereSql .= " WHERE av_room_id = '$avRoomId' ";
        }
        else
        {
            $whereSql .= " AND av_room_id = '$avRoomId' ";
        }
      
       
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return null;
        }

        $fields = array( 
            self::FIELD_COVER,
            self::FIELD_PLAY_URL1,
            self::FIELD_PLAY_URL2,
            self::FIELD_PLAY_URL3,
        );

        try
        {
            $sql = 'SELECT ' . implode(',', $fields) . ' FROM t_new_live_record ' . $whereSql ;
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
            $data = array();
            foreach ($rows as $row)
            {  
                $data['cover'] = $row[self::FIELD_COVER];
                $data['address'] = $row[self::FIELD_PLAY_URL1];
                $data['address2'] = $row[self::FIELD_PLAY_URL2];
                $data['address3'] = $row[self::FIELD_PLAY_URL3];
            }
            return $data;
        }
        catch (PDOException $e)
        {
            return null;
        }
        return array(); 
    }


    /* 功能：获取字段类型
     */
    private static function getType($field)
    {
        switch ($field)
        {
            case self::FIELD_TITLE:
            case self::FIELD_APPID:
            case self::FIELD_COVER:
            case self::FIELD_CHAT_ROOM_ID:
            case self::FIELD_HOST_UID:
                return PDO::PARAM_STR;
            case self::FIELD_LONGITUDE:
            case self::FIELD_LATITUDE:
                // PDO PARAM常量无浮点数，默认得用字符串。
                return pdo::PARAM_STR;
            case self::FIELD_ADDRESS:
                return PDO::PARAM_STR;
            case self::FIELD_ADMIRE_COUNT:
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

    /* 功能：根据主播Uid更新直播数据
     * 说明：data   直播动态数据，目前主要是点赞数，和更新时间。成功：更新记录数;出错：-1
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
    //        $data[self::FIELD_MODIFY_TIME] = date('U');
            $sql = 'UPDATE t_new_live_record SET ';
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
    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getAppid()
    {
        return $this->appid;
    }

    public function setAppid($appid)
    {
        $this->appid = $appid;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    public function getChatRoomId()
    {
        return $this->chatRoomId;
    }

    public function setChatRoomId($chatRoomId)
    {
        $this->chatRoomId = $chatRoomId;
    }

    public function getHostUid()
    {
        return $this->hostUid;
    }

    public function setHostUid($hostUid)
    {
        $this->hostUid = $hostUid;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function getAdmireCount()
    {
        return $this->admireCount;
    }

    public function setAdmireCount($admireCount)
    {
        $this->admireCount = $admireCount;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    public function getAvRoomId()
    {
        return $this->avRoomId;
    }

    public function setAvRoomId($avRoomId)
    {
        $this->avRoomId = $avRoomId;
    }
    
    public function getRoomType()
    {
        return $this->roomType;
    }

    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;
    }
    
    public function setMemberSize($memberSize)
    {
        $this->memberSize = $memberSize;
    }
    
    public function getMemberSize()
    {
        return $this->memberSize;
    }

    public function setDevice($device)
    {
        $this->device = $device;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function setVideoType($videoType)
    {
        $this->videoType = $videoType;
    }

    public function getVideoType()
    {
        return $this->videoType;
    }
}


