<?php
/**
 * 视频上报接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/VideoRecord.php';

class SaveVideoRecordCmd extends TokenCmd
{

    private $videoRecord;

    public function parseInput()
    {
        $this->videoRecord = new VideoRecord();

        if (!isset($this->req['roomnum'])) {
            return new CmdResp(ERR_REQ_DATA, 'lack of roomnum');
        }
        if (!is_int($this->req['roomnum'])) {
            if (is_string($this->req['roomnum'])) {
                $this->req['roomnum'] = intval($this->req['roomnum']);
            } else {
                return new CmdResp(ERR_REQ_DATA, 'Invalid roomnum');
            }
        }
        $this->videoRecord->setRoomNum($this->req['roomnum']);

        if (!isset($this->req['uid']) || empty($this->req['uid'])) {
            return new CmdResp(ERR_REQ_DATA, 'lack of uid');
        }
        if (!is_string($this->req['uid'])) {
            return new CmdResp(ERR_REQ_DATA, 'invalid uid');
        }
        $this->videoRecord->setUid($this->req['uid']);

        if (!isset($this->req['name'])) {
            return new CmdResp(ERR_REQ_DATA, 'lack of name');
        }
        if (!is_string($this->req['name'])) {
            return new CmdResp(ERR_REQ_DATA, 'invalid video name');
        }
        $this->videoRecord->setFileName($this->req['name']);

        if (!isset($this->req['type'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type');
        }
        if (!is_int($this->req['type'])) {
            return new CmdResp(ERR_REQ_DATA, 'invalid of type');
        }

        if (isset($this->req['cover'])) {
            if (!is_string($this->req['cover'])) {
                return new CmdResp(ERR_REQ_DATA, 'invalid cover');
            }
            $this->videoRecord->setCover($this->req['cover']);
        }

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        /* //暂时忽略上报请求
        if($this->req['type'] == 0) 
        {
            //只取第一页即可
            $http_info = '';
            $fileName = 'sxb_' . $this->req['uid'] . '_' . $this->req['name'];
            $rsp = VideoRecord::getVideoUrl($fileName, 0, 1, $http_info);
            if($rsp === false)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error: curl_exec fail');
            }

            if($rsp['code'] != 0) 
            {
                return new CmdResp(ERR_SUCCESS, $rsp['message']);
            }

            $fileSet = array();
            $fileSet = $rsp['fileSet'];
            $videos = array();
            $set = $fileSet[0]; //只存储一个
            $cover = $this->videoRecord->getCover(); 
            if(empty($cover))
                $this->videoRecord->setCover($set['image_url']);
            $this->videoRecord->setVideoId($set['fileId']);
            //$fileName = $set['fileName'];
            //$words = explode("_", $fileName);
            $this->videoRecord->setFileName($set['fileName']);
            $playUrl = $set['playSet'][0]['url'];
            $this->videoRecord->setPlayUrl($playUrl);

            $ret = $this->videoRecord->save();
            if (!$ret)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error'); 
            }
        }     
        */
        return new CmdResp(ERR_SUCCESS, '');
    }
}
