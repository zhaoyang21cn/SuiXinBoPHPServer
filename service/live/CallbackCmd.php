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

require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';

class CallbackCmd extends Cmd
{
    private $channelIdi = '';
    private $streamId = '';
    private $groupId = '';
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
        if ($this->eventType == 100) {
            /*if (isset($this->req['channel_id']))
                $this->channelId = $this->req['channel_id'];*/
            if (isset($this->req['start_time']))
                $this->startTime = $this->req['start_time'];
            if (isset($this->req['end_time']))
                $this->endTime = $this->req['end_time'];
            if (isset($this->req['stream_id']))
                $this->streamId = $this->req['stream_id'];
            if (isset($this->req['stream_param']))
            {
                $stream_param = $this->req['stream_param'];
                parse_str($stream_param, $parr);
                $this->groupId = $parr['groupid'];
            }
            /*
            if (isset($this->req['file_id']))
                $this->fileId = $this->req['file_id'];
             */
            if (isset($this->req['file_size']))
                $this->fileSize = $this->req['file_size'];
            if (isset($this->req['duration']))
                $this->duration = $this->req['duration'];
            if (isset($this->req['video_id']))
                $this->videoId = $this->req['video_id'];
            if (isset($this->req['video_url']))
                $this->videoUrl = $this->req['video_url'];
        }
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        if ($this->eventType == 100) //100标示录制完成处理
        {
            //Log::info('callback handle,group id:'.$this->groupId);
            // 根据roomnum(group id)获取到房间然后进一步填充uid、cover、title
            $room = AvRoom::getRoomById($this->groupId);

            if (empty($room)) {
                //Log::error('room info empty');
                return new CmdResp(ERR_SERVER, 'search room by group id error');
            }
            //Log::info('room info, cover:'.$room['cover']);
            //Log::info('room info, title:'.$room['title']);
            //Log::info('room info, uid:'.$room['uid']);
            //插入记录
            $videoRecord = new VideoRecord();
            $videoRecord->setUid($room['uid']);
            $videoRecord->setCover($room['cover']);
            $videoRecord->setTitle($room['title']);
            $videoRecord->setRoomNum($this->groupId);
            $videoRecord->setVideoId($this->videoId);
            $videoRecord->setFileName($this->streamId);
            $videoRecord->setStartTime($this->startTime);
            $videoRecord->setEndTime($this->endTime);
            $videoRecord->setPlayUrl($this->videoUrl);
            $videoRecord->setFileSize($this->fileSize);
            $videoRecord->setDuration($this->duration);
            $result = $videoRecord->save();
            if ($result == false) {
                return new CmdResp(ERR_SERVER, 'server error');
            }
        }

        return new CmdResp(ERR_SUCCESS, '');
    }
}
