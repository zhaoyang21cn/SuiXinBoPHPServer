<?php

/**
 * Date: 2016/4/19
 * 开始直播接口
 */

require_once dirname(__FILE__) . '/../../Path.php';

require_once 'LiveModifyCmd.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/LiveRecord.php';
require_once MODEL_PATH . '/UserAvRoom.php';
require_once CLIENT_DATA_PATH . '/CliLiveData.php';
require_once CLIENT_DATA_PATH . '/CliUserInfo.php';
require_once CLIENT_DATA_PATH . '/CliLbs.php';

class LiveStartCmd extends Cmd
{

    /**
     * @var LiveRecord
     */
    private $record;

    public function parseInput()
    {
        $cliLiveData = new CliLiveData();
        $req = $this->req;
        // 检查host
        if (!isset($req['host']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of host.');
        }
        $hostInReq = $req['host'];
        $host = new CliUserInfo();
        if (empty($hostInReq['uid']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of host uid.');
        }
        $host->setUid($hostInReq['uid']);
        if (isset($hostInReq['avatar']) && is_string($hostInReq['avatar']))
        {
            if (strlen($hostInReq['avatar']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'Avatar length too long.');
            }
            $host->setAvatar($hostInReq['avatar']);
        }
        if (isset($hostInReq['username']) && is_string($hostInReq['username']))
        {
            if (strlen($hostInReq['username']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'User name length too long.');
            }
            $host->setUsername($hostInReq['username']);
        }
        $cliLiveData->setHost($host);

        // 检查封面
        if (isset($req['cover']) && is_string($req['cover']))
        {
            if (strlen($req['cover']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'Cover length too long.');
            }
            $cliLiveData->setCover($req['cover']);
        }

        // 检查title
        if (isset($req['title']) && is_string($req['title']))
        {
            if (strlen($req['title']) > 128)
            {
                return new CmdResp(ERR_REQ_DATA, 'Title length too long.');
            }
            $cliLiveData->setTitle($req['title']);
        }
        // 检查appid
        if (isset($req['appid']) && is_int($req['appid']))
        {
            $cliLiveData->setAppid($req['appid']);
        }
        else
        {
            $cliLiveData->setAppid(0);
        }

        $lbs = new CliLbs();//change by helloyang; move in there;
        // 检查LBS
        if (isset($req['lbs']) && is_array($req['lbs']))
        {
            $lbsInReq = $req['lbs'];
            if (is_double($lbsInReq['longitude']))
            {
                $lbs->setLongitude($lbsInReq['longitude']);
            }
            if (is_double($lbsInReq['latitude']))
            {
                $lbs->setLatitude($lbsInReq['latitude']);
            }
            if (is_string($lbsInReq['address']))
            {
                if (strlen($lbsInReq['address']) > 100)
                {
                    return new CmdResp(ERR_REQ_DATA, 'Address length too long.');
                }
                $lbs->setAddress($lbsInReq['address']);
            }
        }
        $cliLiveData->setLbs($lbs);//change by helloyang
        // 检查 av room id
        if (!isset($req['avRoomId']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of av room id.');
        }
        if ($req['avRoomId'] !== (int)$req['avRoomId'])
        {
            return new CmdResp(ERR_REQ_DATA, 'AV room id should be integer.');
        }
        $cliLiveData->setAvRoomId($req['avRoomId']);

        // 检查 chat room id
        if (!isset($req['chatRoomId']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of chat room id.');
        }
        if (!is_string($req['chatRoomId']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Chat room id should be string.');
        }
        $cliLiveData->setChatRoomId($req['chatRoomId']);
        $this->record = $cliLiveData->toLiveRecord();
//var_dump($this->record);
//exit(0);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        // $avRoom = new UserAvRoom($this->record->getHostUid());
        // $cnt = $avRoom->load();
        // if ($cnt < 0)
        // {
        //     return new CmdResp(ERR_SERVER, 'Server internal error');
        // }
        // if ($cnt === 0)
        // {
        //     return new CmdResp(ERR_LIVE_NO_AV_ROOM_ID, 'The user doesn\'t have av room id');
        // }
        $id = $this->record->save();
        if ($id < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        return new CmdResp(ERR_SUCCESS, '', array('id' => $id));
    }
}
