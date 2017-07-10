<?php
/**
 * 创建房间接口
 * Date: 2016/11/17
 */
require_once dirname(__FILE__) . '/../../Path.php';

require_once SERVICE_PATH . '/TokenCmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once MODEL_PATH . '/AvRoom.php';
require_once MODEL_PATH . '/NewLiveRecord.php';
require_once MODEL_PATH . '/InteractAvRoom.php';
require_once MODEL_PATH . '/Account.php';

require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';

class CreateLiveRoomCmd extends TokenCmd
{

    private $avRoom;

    public function parseInput()
    {
        if (!isset($this->req['type']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of type');
        }
        if (!is_string($this->req['type']))
        {
             return new CmdResp(ERR_REQ_DATA, ' Invalid type');
        }
        
        $this->avRoom = new AvRoom($this->user);
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function handle()
    {
        /*$ret = $this->avRoom->load();
        // 加载房间出错
        if ($ret < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }

        //房间不存在，执行创建
        if($ret == 0)
        {
            $ret = $this->avRoom->create();
            if (!$ret)
            {
                return new CmdResp(ERR_SERVER, 'Server internal error: create av room fail');
            }
        }*/

        // 创建房间之前先清空数据
        $ret = $this->avRoom->load();
        // 存在旧房间
        if ($ret > 0)
        {
            //Log::info('uid '.$this->user.' has old room '.$this->avRoom->getId().' to delete');
            //删除直播记录
            NewLiveRecord::delete($this->user);
            //清空房间成员
            InteractAvRoom::ClearRoomByRoomNum($this->avRoom->getId());
        }

        //Log::info('uid '.$this->user.' create room now');

        // 每次请求都创建一个新的房间出来
        $ret = $this->avRoom->create();
        if (!$ret)
        {
            //Log::error('uid '.$this->user.' create room failed');
            return new CmdResp(ERR_SERVER, 'Server internal error: create av room fail');
        }

        //房间id
        $id = $this->avRoom->getId();
        //房间成员设置
        $interactAvRoom = new InteractAvRoom($this->user, $id, 'off', 1);
        //主播加入房间列表
        $ret = $interactAvRoom->enterRoom();    
        if(!$ret)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error:insert record into interactroom fail'); 
        }

        return new CmdResp(ERR_SUCCESS, '', array('roomnum' => (int)$id, 'groupid' => (string)$id));
    }    
}
