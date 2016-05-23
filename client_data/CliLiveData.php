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
    	$this->setCreateTime(strtotime($record->getCreateTime()));
    }


}

?>