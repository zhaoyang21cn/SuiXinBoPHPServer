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
        if (!isset($this->req['videoid']) && !is_string($this->req['videoid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of videoid');
        }
        
        if (!isset($this->req['playurl']) && !is_string($this->req['playurl']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of playurl');
        }
        
        $this->videoRecord = new VideoRecord($this->user, $this->req['videoid'], $this->req['playurl']);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $ret = $this->videoRecord->save();
        if (!$ret)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error'); 
        }
 
        return new CmdResp(ERR_SUCCESS, '');
    }    
}
