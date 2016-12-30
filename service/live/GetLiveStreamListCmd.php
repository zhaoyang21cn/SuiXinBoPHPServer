<?php
/**
 * 拉旁路直播列表接口
 * Date: 2016/12/29
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/NewLiveRecord.php';

class GetLiveStreamListCmd extends TokenCmd
{

    private $appid;
    private $roomType = '';
    private $pageIndex = 0;
    private $pageSize;

    public function parseInput()
    {
        if (isset($this->req['appid']) && is_int($this->req['appid']))
        {
            $this->appid = $this->req['appid'];
        }
        else
        {
            $this->appid = 0;
        }

        if (!isset($this->req['index']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of page index');
        }
        $pageIndex = $this->req['index'];
        if ($pageIndex !== (int)$pageIndex || $pageIndex < 0)
        {
            return new CmdResp(ERR_REQ_DATA, 'Page index should be a non-negative integer');
        }
        if (!isset($this->req['size']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of page size');
        }
        $pageSize = $this->req['size'];
        if ($pageSize !== (int)$pageSize || $pageSize < 0 || $pageSize > 50)
        {
            return new CmdResp(ERR_REQ_DATA, 'Page size should be a positive integer(not larger than 50)');
        }
        
        $this->pageIndex = $pageIndex;
        $this->pageSize = $pageSize;
        if(isset($this->req['type']))
        {
            $this->roomType = $this->req['type'];
        }
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        //获取房间播放url记录
        $offset = $this->pageIndex;
        $limit = $this->pageSize;

        $rspData = NewLiveRecord::getLiveStreamList($this->appid, $this->roomType, $offset, $limit);
        if (is_null($rspData))
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }

        //获取总数
        $totalCount = NewLiveRecord::getCount($this->appid);
        if ($totalCount < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
       
        $data = array(
            'total' => $totalCount,
            'videos' => $rspData,
        );
        return new CmdResp(ERR_SUCCESS, '', $data);
    }    
}
