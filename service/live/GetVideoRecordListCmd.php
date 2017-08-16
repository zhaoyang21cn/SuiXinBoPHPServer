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
    // 用于search的user id
    private $s_uid = null;

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
        if ($pageSize !== (int)$pageSize || $pageSize < 0 || $pageSize > 100)
        {
            return new CmdResp(ERR_REQ_DATA, 'Page size should be a positive integer(not larger than 100)');
        }
        
        if (!isset($this->req['type']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type');
        }
        if(!is_int($this->req['type']))
        {
            return new CmdResp(ERR_REQ_DATA, 'invalid of type');
        }

        if (isset($this->req['s_uid']))
        {
            $this->s_uid = $this->req['s_uid'];
        }

        $this->pageIndex = $pageIndex > 0 ? $pageIndex : 1;
        $this->pageSize = $pageSize;
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        /* //test
        $http_info = '';
        $rsp = VideoRecord::getVideoInfo('200000291_9bf79ccfb5a6418badf35d07615de5b8', $http_info);
        $rsp = VideoRecord::getVideoUrl('sxb_', 0, 100, $http_info);
        $rsp = VideoRecord::getFileInfo('9031868222844415350', $http_info);
        return new CmdResp(ERR_SUCCESS, '', $rsp);
        */
        
        $data = array();    

        //获取后台DB中自动录制时回调生成的记录
        if($this->req['type'] == 0) 
        {
            $offset = ($this->pageIndex - 1) * $this->pageSize;
            $limit = $this->pageSize;
            $recordList = VideoRecord::getList($offset, $limit, 0, $this->s_uid);
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
            $totalCount = VideoRecord::getCount(0, $this->s_uid);
            if (!$totalCount)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error');
            }
            $data = array(
                    'total' => $totalCount,
                    'videos' => $rspRecordList,
                    );
        }
        else //频道模式，通过http请求以“sxb_”前缀搜索上报的视频记录
        {
            $http_info = '';
            $fileName = 'sxb_';
            $rsp = VideoRecord::getVideoUrl($fileName, $this->pageIndex, $this->pageSize, $http_info);
            if($rsp === false)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error: curl_exec fail-' . $http_info);
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
                $fileName = $set['fileName'];
                $words = explode("_", $fileName);
                $videos[] = array (
                        'cover' => $set['image_url'],
                        'uid' => $words[1],
                        'name' => $fileName,
                        'videoId' => $set['fileId'],
                        'playurl' => $playUrl,
                        );
            }

            $data = array(
                    'total' => $rsp['totalCount'],
                    'videos' => $videos,
                    );
        }
        return new CmdResp(ERR_SUCCESS, '', $data);
    }
}
