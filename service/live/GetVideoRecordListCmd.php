<?php
/**
 * 视频列表接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/VideoRecord.php';

class GetVideoRecordListCmd extends TokenCmd
{
    private $pageIndex;
    private $pageSize;

    public function parseInput()
    {
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
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
		/*
        //获取视频列表
        $offset = $this->pageIndex;
        $limit = $this->pageSize;
        $recordList = VideoRecord::getList($offset, $limit);
        if (is_null($recordList))
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        $rspRecordList = array();
        foreach ($recordList as $record)
        {
            $rspRecordList[] = $record->toJsonArray();
        }
        
        //获取视频总数
        $totalCount = VideoRecord::getCount();
        if (!$totalCount)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        $data = array(
            'total' => $totalCount,
            'videos' => $rspRecordList,
        );
        return new CmdResp(ERR_SUCCESS, '', $data);
		*/

		$http_info = '';
		$rsp = VideoRecord::getVideoUrl($this->pageIndex, $this->pageSize, $http_info);
		if($rsp === false)
		{
            return new CmdResp(ERR_SERVER, 'Server internal error: curl_exec fail');
		}
		
		$fileSet = array();
		$fileSet = $rsp['fileSet'];
		$videos = array();
		foreach($fileSet as $set)
		{
			$playSet = array();
			$playSet = $set['playSet'];
			$playUrl = array();
			foreach($playSet as $play)
			{
				$playUrl[] = $play['url'];
			}

			$videos[] = array (
					'cover' => $set['image_url'],
					'uid' => $set['fileName'],
					'videoId' => $set['fileId'],
					'playurl' => $playUrl,
					);
		}

        $data = array(
            'total' => $rsp['totalCount'],
            'videos' => $videos,
        );

        return new CmdResp(ERR_SUCCESS, '', $data);
    }
}
