<?php
/**
 * 视频记录表
 * Date: 2016/11/18
 */
require_once dirname(__FILE__) . '/../Config.php';
require_once LIB_PATH . '/db/DB.php';

require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';

class VideoRecord
{
    const FIELD_HOST_UID = 'uid';
    const FIELD_ROOM_NUM = 'room_num';
    const FIELD_COVER = 'cover';
    const FIELD_FILE_NAME = 'file_name';
    const FIELD_VIDEO_ID = 'video_id';
    const FIELD_START_TIME = 'start_time';
    const FIELD_END_TIME = 'end_time';
    const FIELD_PLAY_URL = 'play_url';
    const FIELD_CREATE_TIME = 'create_time';
    const FIELD_TITLE = 'title';
    const FIELD_FILE_SIZE = 'file_size';
    const FIELD_DURATION = 'duration';
    
    // 用户id => string
    private $uid = '';
    
    // 录制房间号 => int
    private $roomNum = 0;

    // 封面 => string
    private $cover = '';

    // 视频名 => string
    private $fileName = '';

    // 视频id => string
    private $videoId = '';

    // 录制时间 => int
    private $startTime = 0;

    // 录制时间 => int
    private $endTime= 0;

    // 视频url => string
    private $playUrl = '';

    // 创建时间(时间戳) => int
    private $createTime = 0;

    // 直播名称 => string
    private $title = '';

    // 文件大小
    private $fileSize = '';

    // 时长
    private $duration = '';
   
    public function getUid()
    {
        return $this->uid;
    }
 
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
 
    public function setRoomNum($roomNum)
    {
        $this->roomNum = $roomNum;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    public function setPlayUrl($playUrl)
    {
        $this->playUrl = $playUrl;
    }

    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getFileSize()
    {
        return $this->fileSize;
    }

    public function setFileSize($fileSize)
    {
        $this->fileSize = $fileSize;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /* 功能：存储视频记录
     * 说明: 成功返回 true, 失败返回false
     */
    public function save()
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            return false;
        }
        $fields = array(
            self::FIELD_HOST_UID => $this->uid,
            self::FIELD_ROOM_NUM => $this->roomNum,
            self::FIELD_COVER => $this->cover,
            self::FIELD_FILE_NAME => $this->fileName,
            self::FIELD_VIDEO_ID => $this->videoId,
            self::FIELD_START_TIME => $this->startTime,
            self::FIELD_END_TIME => $this->endTime,
            self::FIELD_PLAY_URL =>  $this->playUrl,
            self::FIELD_CREATE_TIME => date('U'),
            self::FIELD_TITLE => $this->title,
            self::FIELD_FILE_SIZE => $this->fileSize,
            self::FIELD_DURATION => $this->duration,
        );
        try
        {
            $sql = 'REPLACE INTO t_video_record (';
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
                return false;
            }
            return true;
        }
        catch (PDOException $e)
        {
            return false;
        }
    }

    /* 功能：获取视频列表
     * 说明: 从偏移（offset）处获取N（limit）条APP（appid）的视频信息；
     *      成功返回视频列表，失败返回空
     */
    public static function getList($offset = 0, $limit = 100, $appid = 0, $s_uid = null)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = " . $appid . " ";
        }
        if ($s_uid !== null) {
            if ($whereSql === "") {
                $whereSql = " WHERE uid = '" . $s_uid . "' ";
            } else {
                $whereSql = $whereSql." and uid = '" . $s_uid . "' ";
            }
            Log::info('search uid:'.$whereSql);
        }
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            //Log::error('video record getList. dbh null');
            return null;
        }
        $fields = array(
            self::FIELD_HOST_UID,
            self::FIELD_COVER,
            self::FIELD_FILE_NAME,
            self::FIELD_VIDEO_ID,
            self::FIELD_PLAY_URL,
            self::FIELD_TITLE,
            self::FIELD_CREATE_TIME,
            self::FIELD_FILE_SIZE,
            self::FIELD_DURATION,
        );
        try
        {
            $sql = 'SELECT ' . implode(',', $fields) .
                   ' FROM t_video_record ' . $whereSql . ' ORDER BY id DESC LIMIT ' .
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
            foreach ($rows as $row)
            {
                $record = new VideoRecord();
                $record->setUid($row['uid']);
                $record->setFileName($row['file_name']);
                $record->setVideoId($row['video_id']);
                $record->setPlayUrl($row['play_url']);
                $record->setCover($row['cover']);
                $record->setTitle($row['title']);
                $record->setCreateTime($row['create_time']);
                $record->setFileSize($row['file_size']);
                $record->setDuration($row['duration']);
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
    
    /* 功能：删除视频记录
     * 说明: 成功返回 true, 失败返回false
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
            $sql = 'DELETE FROM t_video_record WHERE ' .
                   self::FIELD_HOST_UID . ' = ?';
            $stmt = $dbh->prepare($sql);
            $uid = $this->uid;
            $stmt->bindParam(1, $uid, PDO::PARAM_STR);
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

    /* 功能：获取视频记录总数
     * 说明: 成功返回视频记录总数, 失败返回-1。
     */
    public static function getCount($appid = 0, $s_uid = null)
    {
        if ($appid == 0) {
            $whereSql = "";
        }else{
            $whereSql = " WHERE appid = $appid ";
        }
        if ($s_uid !== null) {
            if ($whereSql === "") {
                $whereSql = " WHERE uid = '$s_uid' ";
            } else {
                $whereSql = $whereSql." and uid = '$s_uid' ";
            }
        }
        $dbh = DB::getPDOHandler();
        $list = array();
        if (is_null($dbh))
        {
            return -1; 
        }
        try
        {
            $sql = "SELECT COUNT(*) as total FROM t_video_record $whereSql";
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

    /* 功能：生成Json数组,返回前端
     */
    public function toJsonArray()
    {
        return array(
            'uid' => $this->uid,
            'cover' => $this->cover?$this->cover:'',
            'name' => $this->title?$this->title:'',
            'videoId' => $this->videoId?$this->videoId:'',
            'playurl' => array(0 => $this->playUrl),//兼容已有版本
            'createTime' => $this->createTime?$this->createTime:0,
            'fileSize' => $this->fileSize?$this->fileSize:0,
            'duration' => $this->duration?$this->duration:0,
        );
    }

    /* 功能：http方式获取点播url地址
     * 说明：filename指定搜索前缀，index，size是http请求参数。成功则返回记录信息，失败则错误信息通过http_info返回
     * 参考：https://www.qcloud.com/document/product/266/1373
     */
    static    public function getVideoUrl($fileName, $index, $size, &$http_info)
    {
        $domain = 'vod.api.qcloud.com';
        $Action = 'DescribeVodPlayInfo';
        $Nonce = rand(10000, 100000000);
        $Region = 'gz';
        $SecretId = VIDEO_RECORD_SECRET_ID;
        $Timestamp = date('U');
        //$fileName = 'sxb';
        $pageNo = $index;
        $pageSize = $size;

        $Signature = '';
        $https = 'https://';
        $url = $domain . '/v2/index.php?'
            . 'Action=' . $Action . '&'
            . 'Nonce=' . $Nonce . '&'
            . 'Region=' . $Region . '&'
            . 'SecretId=' . $SecretId . '&'
            . 'Timestamp=' . $Timestamp . '&'
            . 'fileName=' . $fileName . '&'
            . 'pageNo=' . $pageNo . '&'
            . 'pageSize=' . $pageSize;
        $srcStr = 'GET' . $url;
        $secretKey = VIDEO_RECORD_SECRET_KEY;
        $Signature = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
        $Signature = urlencode($Signature);

        $url = $https . $url . '&Signature=' . $Signature;
        $timeout = 3000;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        $ret = curl_exec($ch);

        if ($ret === false) 
        {
            return false;
        }
        $http_info = curl_getinfo($ch);
        curl_close($ch);
        $input = iconv('UTF-8', 'UTF-8//IGNORE', $ret);
        $rsp = json_decode($input, 12);
        return $rsp;
    }

    /* 功能：http方式获取video信息
     * 参考：https://www.qcloud.com/document/product/266/1302
     */
    static    public function getVideoInfo($videoId, &$http_info)
    {
        $domain = 'vod.api.qcloud.com';
        $Action = 'DescribeRecordPlayInfo';
        $Nonce = rand(10000, 100000000);
        $Region = 'gz';
        $SecretId = VIDEO_RECORD_SECRET_ID;
        $Timestamp = date('U');

        $Signature = '';
        $https = 'https://';
        $url = $domain . '/v2/index.php?'
            . 'Action=' . $Action . '&'
            . 'Nonce=' . $Nonce . '&'
            . 'Region=' . $Region . '&'
            . 'SecretId=' . $SecretId . '&'
            . 'Timestamp=' . $Timestamp . '&'
            . 'vid=' . $videoId;
        $srcStr = 'GET' . $url;
        $secretKey = VIDEO_RECORD_SECRET_KEY;
        $Signature = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
        $Signature = urlencode($Signature);

        $url = $https . $url . '&Signature=' . $Signature;
        $timeout = 3000;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        $ret = curl_exec($ch);

        if ($ret === false) 
        {
            return false;
        }
        $http_info = curl_getinfo($ch);
        curl_close($ch);
        $input = iconv('UTF-8', 'UTF-8//IGNORE', $ret);
        $rsp = json_decode($input, 12);
        return $rsp;
    }

    /* 功能：http方式获取文件信息
     * 参考：https://www.qcloud.com/document/product/266/1302
     */
    static    public function getFileInfo($fileId, &$http_info)
    {
        $domain = 'vod.api.qcloud.com';
        $Action = 'DescribeVodInfo';
        $Nonce = rand(10000, 100000000);
        $Region = 'gz';
        $SecretId = VIDEO_RECORD_SECRET_ID;
        $Timestamp = date('U');

        $Signature = '';
        $https = 'https://';
        $url = $domain . '/v2/index.php?'
            . 'Action=' . $Action . '&'
            . 'Nonce=' . $Nonce . '&'
            . 'Region=' . $Region . '&'
            . 'SecretId=' . $SecretId . '&'
            . 'Timestamp=' . $Timestamp . '&'
            . 'fileId.1=' . $fileId;
        $srcStr = 'GET' . $url;
        $secretKey = VIDEO_RECORD_SECRET_KEY;
        $Signature = base64_encode(hash_hmac('sha1', $srcStr, $secretKey, true));
        $Signature = urlencode($Signature);

        $url = $https . $url . '&Signature=' . $Signature;
        $timeout = 3000;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
        $ret = curl_exec($ch);

        if ($ret === false) 
        {
            return false;
        }
        $http_info = curl_getinfo($ch);
        curl_close($ch);
        $input = iconv('UTF-8', 'UTF-8//IGNORE', $ret);
        $rsp = json_decode($input, 12);
        return $rsp;
    }
}


