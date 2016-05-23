<?php

/**
 * 直播记录修改接口
 * Date: 2016/4/22
 */

require_once dirname(__FILE__) . '/../../Path.php';


require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';

class LiveModifyCmd extends Cmd
{
    protected $uid;
    protected $admireCount;
    protected $watchCount;
    protected $timeSpan;

    public function parseInput()
    {
        if (empty($this->req['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of uid');
        }
        if (!is_string($this->req['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid uid');
        }
        $this->uid = $this->req['uid'];
        $fields = array(
            'admireCount',
            'watchCount',
            'timeSpan',
        );
        $data = array();
        foreach ($fields as $field)
        {
            if (!isset($this->req[$field]))
            {
                return new CmdResp(ERR_REQ_DATA, 'Lack of ' . $field);
            }
            $val = $this->req[$field];
            if ($val !== (int)$val || $val < 0)
            {
                return new CmdResp(ERR_REQ_DATA, $field . ' should be a non-negative number');
            }
            $data[$field] = $val;
        }
        $this->watchCount = $data['watchCount'];
        $this->timeSpan = $data['timeSpan'];
        $this->admireCount = $data['admireCount'];
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
		return new CmdResp(ERR_SERVER, 'Server internal error');
    }

}