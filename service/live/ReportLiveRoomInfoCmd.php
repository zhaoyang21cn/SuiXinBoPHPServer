<?php
/**
 * 房间信息上报接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/NewLiveRecord.php';
require_once MODEL_PATH . '/AvRoom.php';

require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';


class ReportLiveRoomInfoCmd extends TokenCmd
{
    //NewLiveRecord
    private $record;
    //AvRoom
    private $av_room;
    const URL = 'liveplay.myqcloud.com/live/';
    const BIZID = '123456';

    public function parseInput()
    {
        $liveRecord = new NewLiveRecord();
        $req = $this->req;

        $room = $req['room'];
        // room-必填
        if (!isset($req['room'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of room.');
        }
        $room = $req['room'];

        // 检查title-选填
        if (isset($room['title']) && is_string($room['title'])) {
            if (strlen($room['title']) > 128) {
                return new CmdResp(ERR_REQ_DATA, 'Title length too long.');
            }
            $liveRecord->setTitle($room['title']);
        }

        // 检查封面-选填
        if (isset($room['cover']) && is_string($room['cover'])) {
            if (strlen($room['cover']) > 128) {
                return new CmdResp(ERR_REQ_DATA, 'Cover length too long.');
            }
            $liveRecord->setCover($room['cover']);
        }

        // 检查type-必填
        if (!isset($room['type'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type.');
        }
        if (!is_string($room['type'])) {
            return new CmdResp(ERR_REQ_DATA, 'invalid type.');
        }
        $liveRecord->setRoomType($room['type']);

        // 检查 av room id-必填
        if (!isset($room['roomnum'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of av room id.');
        }
        if (!is_int($room['roomnum'])) {
            if (is_string($room['roomnum'])) {
                $room['roomnum'] = intval($room['roomnum']);
            } else {
                return new CmdResp(ERR_REQ_DATA, 'AV room id should be integer or string.');
            }
        }
        $liveRecord->setAvRoomId($room['roomnum']);

        // 检查 chat room id-必填
        if (!isset($room['groupid'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of chat room id.');
        }
        if (!is_string($room['groupid'])) {
            return new CmdResp(ERR_REQ_DATA, 'Chat room id should be string.');
        }
        $liveRecord->setChatRoomId($room['groupid']);

        // 检查device-必填
        $device = $room['device'];
        if (!isset($device)) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of device.');
        }
        if (!is_int($device) || (is_int($device) && $device >= 3)) {
            return new CmdResp(ERR_REQ_DATA, 'device should be 0-IOS 1-Android  2-PC.');
        }
        $liveRecord->setDevice($device);

        // 检查video type-必填
        $videotype = $room['videotype'];
        if (!isset($videotype)) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of videotype.');
        }
        if (!is_int($videotype) || (is_int($videotype) && $videotype >= 2)) {
            return new CmdResp(ERR_REQ_DATA, 'videotype should be 0 or 1.');
        }
        $liveRecord->setVideoType($videotype);

        if (!isset($room['appid'])) {
            return new CmdResp(ERR_REQ_DATA, 'Lack of appid.');
        }
        if (!is_string($room['appid'])) {
            if (!is_int($room['appid'])) {
                return new CmdResp(ERR_REQ_DATA, 'appid should be integer or string.');
            }
        } else {
            $room['appid'] = intval($room['appid']);
        }
        $liveRecord->setAppid($room['appid']);

        // LBS-选填
        if (isset($req['lbs']) && is_array($req['lbs'])) {
            $lbsInReq = $req['lbs'];
            if (isset($lbsInReq['longitude']) && is_double($lbsInReq['longitude'])) {
                $liveRecord->setLongitude($lbsInReq['longitude']);
            }
            if (isset($lbsInReq['latitude']) && is_double($lbsInReq['latitude'])) {
                $liveRecord->setLatitude($lbsInReq['latitude']);
            }
            if (isset($lbsInReq['address']) && is_string($lbsInReq['address'])) {
                if (strlen($lbsInReq['address']) > 100) {
                    return new CmdResp(ERR_REQ_DATA, 'Address length too long.');
                }
                $liveRecord->setAddress($lbsInReq['address']);
            }
        }

        $liveRecord->setHostUid($this->user);
        $this->record = $liveRecord;

        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        $ret = $this->record->genPlayUrl(self::BIZID, self::URL);
        if ($ret != true) {
            return new CmdResp(ERR_SERVER, 'Server internal error: gen play url fail');
        }

        //Log::info('update room info by room id '.$this->record->getAvRoomId());
        //Log::info('room uid '.$this->user);
        //Log::info('room title '.$this->record->getTitle());
        //Log::info('room cover '.$this->record->getCover());
        $up_res = AvRoom::updateRoomInfoById(
            $this->user,
            $this->record->getAvRoomId(),
            $this->record->getTitle(),
            $this->record->getCover(),
            $this->record->getDevice()
        );
        if (!$up_res) {
            Log::error('update room info failed. room id '.$this->record->getAvRoomId());
            return new CmdResp(ERR_SERVER, 'av room info update error');
        }

        $id = $this->record->save();
        if ($id < 0) {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        return new CmdResp(ERR_SUCCESS, '');
    }
}
