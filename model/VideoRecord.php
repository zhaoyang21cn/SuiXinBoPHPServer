<?php
/**
 * 视频记录表
 * Date: 2016/11/18
 */
require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

class VideoRecord
{
    const FIELD_HOST_UID = 'uid';
    const FIELD_VIDEO_ID = 'video_id';
    const FIELD_PLAY_URL = 'play_url';
    const FIELD_CREATE_TIME = 'create_time';
    
    // 用户id => string
    private $uid = '';

    // 视频id => string
    private $videoId = '';

    // 视频url => string
    private $playUrl = '';

    // 创建时间(时间戳) => int
    private $createTime = 0;
    
    public function __construct($uid, $videoId, $playUrl)
    {
        $this->uid = $uid;
        $this->videoId = $videoId;
        $this->playUrl = $playUrl;
    }
    
    public function getUid()
    {
        return $this->uid;
    }
 
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
 
    public function getCreateTime()
    {
        return $this->createTime;
    }

    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    private function InitFromDBFields($fields)
    { 
        $this->uid = $fields[self::FIELD_HOST_UID];
        $this->videoId = $fields[self::FIELD_VIDEO_ID];
        $this->playUrl = $fields[self::FIELD_PLAY_URL];
        $this->createTime = $fields[self::FIELD_CREATE_TIME];
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
            self::FIELD_VIDEO_ID => $this->videoId,
            self::FIELD_PLAY_URL =>  $this->playUrl,
            self::FIELD_CREATE_TIME => date('U'),
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
    public static function getList($offset = 0, $limit = 50, $appid = 0)
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
            self::FIELD_HOST_UID,
            self::FIELD_VIDEO_ID,
            self::FIELD_PLAY_URL
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
                $record = new VideoRecord($row['uid'], $row['video_id'], $row['play_url']);
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
    public static function getCount($appid = 0)
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

    /* 功能：生成Json数组
     */
    public function toJsonArray()
    {
        return array(
            'uid' => $this->uid,
            'videoId' => $this->videoId,
            'playurl' => $this->playUrl,
        );
    }

	static	public function getVideoUrl($index, $size, &$http_info)
	{
		$domain = 'vod.api.qcloud.com';
		$Action = 'DescribeVodPlayInfo';
		$Nonce = rand(10000, 100000000);
		$Region = 'gz';
		$SecretId = 'AKIDlnkbPqucPuUgJmkMnaocUEBhZzBa5bpO';
		$Timestamp = date('U');
		$fileName = 'sxb';
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
		$secretKey = 'yw2nqIhlWkCmw7xZQaHUITMspCkatqsU';
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


