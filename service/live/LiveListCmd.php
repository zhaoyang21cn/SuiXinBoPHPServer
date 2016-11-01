<?php

/**
 * 直播列表接口
 * Date: 2016/4/22
 */

require_once dirname(__FILE__) . '/../../Path.php';

require_once 'LiveModifyCmd.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/LiveRecord.php';
require_once CLIENT_DATA_PATH . '/CliLiveData.php';
require_once LIB_PATH . '/db/DB.php';


class LiveListCmd extends Cmd
{
    private $appid;
    private $pageIndex;
    private $pageSize;

    public function parseInput()
    {
        //var_dump($this->req['appid']);
        if (isset($this->req['appid']) && is_int($this->req['appid']))
        {
            $this->appid = $this->req['appid'];
        }
        else
        {
            $this->appid = 0;
        }
        if (!isset($this->req['pageIndex']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of page index');
        }
        $pageIndex = $this->req['pageIndex'];
        if ($pageIndex !== (int)$pageIndex || $pageIndex < 0)
        {
            return new CmdResp(ERR_REQ_DATA, 'Page index should be a non-negative integer');
        }
        if (!isset($this->req['pageSize']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of page size');
        }
        $pageSize = $this->req['pageSize'];
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

        $offset = $this->pageIndex * $this->pageSize;
        $limit = $this->pageSize;
        $recordList = LiveRecord::getList($this->appid, $offset, $limit);
        if (is_null($recordList))
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        $totalCount = LiveRecord::getCount($this->appid);
        if ($totalCount < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        $cliRecordList = array();
        foreach ($recordList as $record)
        {
            $cliRecord = new CliLiveData();
            $cliRecord->InitFromLiveRecord($record);
            $cliRecordList[] = $cliRecord->toJsonArray();
        }
        $resp = array(
            'totalItem' => $totalCount,
            'recordList' => $cliRecordList,
        );
        return new CmdResp(ERR_SUCCESS, '', $resp);
    }
}
