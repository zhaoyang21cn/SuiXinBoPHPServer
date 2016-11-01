<?php

/**
 * 客户端直播数据类
 * Date: 2016/4/18
 */

require 'CliUserInfo.php';
require 'CliLbs.php';

class CliLiveData
{
    /**
     * 直播标题
     * @var string
     */
    private $title = '';
    /**
     * 直播的appid
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
     * 主播信息
     * @var CliUserInfo
     */
    private $host;
    /**
     * 地理信息
     * @var CliLbs
     */
    private $lbs;
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
     * @var int
     */
    private $createTime = 0;
    /**
     * av房间ID
     * @var int
     */
    private $avRoomId = 0;

    /**
     * 转化成Json数组
     * @return array
     */
    public function toJsonArray()
    {
    	return array(
    		'title' => $this->title,
            //'appid' => $this->appid,
    		'cover' => $this->cover,
    		'chatRoomId' => $this->chatRoomId,
    		'host' => $this->host->toJsonArray(),
    		'lbs' => $this->lbs->toJsonArray(),
    		'admireCount' => $this->admireCount,
    		'timeSpan' => $this->timeSpan,
    		'watchCount' => $this->watchCount,
    		'createTime' => $this->createTime,
    		'avRoomId' => $this->avRoomId,
    	);
    }

    /**
     * 转化成服务端Model层 LiveRecord 数据类
     * @return LiveRecord
     */
    public function toLiveRecord()
    {
    	$record = new LiveRecord();
        $record->setTitle($this->title);
        $record->setAppid($this->appid);
    	$record->setCover($this->cover);
    	$record->setChatRoomId($this->chatRoomId);
    	$record->setHostUid($this->host->getUid());
    	$record->setHostAvatar($this->host->getAvatar());
    	$record->setHostUsername($this->host->getUsername());
    	$record->setLongitude($this->lbs->getLongitude());
    	$record->setLatitude($this->lbs->getLatitude());
    	$record->setAddress($this->lbs->getAddress());
    	$record->setAdmireCount($this->admireCount);
    	$record->setTimeSpan($this->timeSpan);
    	$record->setWatchCount($this->watchCount);
    	$record->setAvRoomId($this->avRoomId);
    	if ($this->createTime === 0)
    	{
    		$record->setCreateTime(date('Y-m-d H:i:s'));
    	}
    	else
    	{
    		$record->setCreateTime(strtotime($this->createTime));
    	}

    	return $record;
    }


    /**
     * 根据LiveRecord Model对象初始化
     * @param LiveRecord $record
     */
    public function InitFromLiveRecord($record)
    {
        $this->setTitle($record->getTitle());
        $this->setAppid($record->getAppid());
    	$this->setCover($record->getCover());
    	$this->setChatRoomId($record->getChatRoomId());
    	$this->host = new CliUserInfo();
    	$this->host->setUid($record->getHostUid());
    	$this->host->setAvatar($record->getHostAvatar());
    	$this->host->setUsername($record->getHostUsername());
    	$this->lbs = new CliLbs();
    	$this->lbs->setLongitude($record->getLongitude());
    	$this->lbs->setLatitude($record->getLatitude());
    	$this->lbs->setAddress($record->getAddress());
    	$this->setAdmireCount($record->getAdmireCount());
    	$this->setTimeSpan($record->getTimeSpan());
    	$this->setWatchCount($record->getWatchCount());
    	$this->setAvRoomId($record->getAvRoomId());
    	//$this->setCreateTime(strtotime($record->getCreateTime()));
    	$this->setCreateTime($record->getCreateTime());
    }



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
     * @return int
     */
    public function getAppid()
    {
        return $this->appid;
    }

    /**
     * Sets 直播appid.
     *
     * @param int $appid the appid
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
     * Gets 主播信息.
     *
     * @return CliUserInfo
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets 主播信息.
     *
     * @param CliUserInfo $host the host
     *
     * @return self
     */
    public function setHost(CliUserInfo $host)
    {
        $this->host = $host;
    }

    /**
     * Gets 地理信息.
     *
     * @return CliLbs
     */
    public function getLbs()
    {
        return $this->lbs;
    }

    /**
     * Sets 地理信息.
     *
     * @param CliLbs $lbs the lbs
     *
     * @return self
     */
    public function setLbs(CliLbs $lbs)
    {
        $this->lbs = $lbs;
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
     * @return int
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Sets 创建时间.
     *
     * @param int $createTime the create time
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

?>
