<?php
/**
 * 录制回调接口
 * Date: 2017/01/18
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once LIB_PATH . '/db/DB.php';
require_once MODEL_PATH . '/AvRoom.php';
require_once MODEL_PATH . '/VideoRecord.php';

class CallbackCmd extends Cmd
{
    private $channelIdi = '';
    private $streamId = '';
    private $fileId = '';
    private $fileSize = 0;
    private $startTime = 0;
    private $endTime = 0;
    private $duration = 0;
    private $videoId = '';
    private $videoUrl = '';
    private $eventType = 0;

    public function parseInput()
    {
        if (isset($this->req['event_type']))
            $this->eventType = $this->req['event_type'];
        if($this->eventType == 100)
        {
            if (isset($this->req['channel_id']))
                $this->channelId = $this->req['channel_id'];
            if (isset($this->req['start_time']))
                $this->startTime = $this->req['start_time'];
            if (isset($this->req['end_time']))
                $this->endTime = $this->req['end_time'];
            if (isset($this->req['stream_id']))
                $this->streamId = $this->req['stream_id'];
            /*
            if (isset($this->req['file_id']))
                $this->fileId = $this->req['file_id'];
            if (isset($this->req['file_size']))
                $this->fileSize = $this->req['file_size'];
            if (isset($this->req['duration']))
                $this->duration = $this->req['duration'];
             */
            if (isset($this->req['video_id']))
                $this->videoId = $this->req['video_id'];
            if (isset($this->req['video_url']))
                $this->videoUrl = $this->req['video_url'];
        }
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        if($this->eventType == 100) //100标示录制完成处理
        {
            $streamMd5 = explode("_", $this->channelId)[1];
            $rsp = AvRoom::getUidByMd5($streamMd5);
            
            //MD5映射uid
            if(empty($rsp))
            {
                return new CmdResp(ERR_SERVER, 'search uid by md5 error');
            }

            //插入记录
            $videoRecord = new VideoRecord();
            $videoRecord->setUid($rsp['uid']);
            $videoRecord->setRoomNum($rsp['roomnum']);
            $videoRecord->setVideoId($this->videoId);
            $videoRecord->setFileName($this->streamId);
            $videoRecord->setStartTime($this->startTime);
            $videoRecord->setEndTime($this->endTime);
            $videoRecord->setPlayUrl($this->videoUrl);
            $result = $videoRecord->save();
            if($result == false)
            {
                return new CmdResp(ERR_SERVER, 'server error');
            }
        }
        
        return new CmdResp(ERR_SUCCESS, '');
    }
}
