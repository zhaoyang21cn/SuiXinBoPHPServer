<?php
/**
 * Date: 2016/4/29
 * Update：2016/12/12
 * Tips：此清理程序由crontab定时执行
 */

require_once  __DIR__ . '/../Path.php';
require_once MODEL_PATH . '/LiveRecord.php';
require_once MODEL_PATH . '/NewLiveRecord.php';
require_once MODEL_PATH . '/InteractAvRoom.php';
require_once MODEL_PATH . '/AvRoom.php';

function clear()
{
    // 删除90秒没有收到心跳包（LiveHostHearBeat）的直播记录
    LiveRecord::deleteInactiveRecord(90);
    // 删除90秒没有收到心跳包（HearBeat）的新版直播记录
    NewLiveRecord::deleteInactiveRecord(90);
    // 删除90秒没有收到心跳包（HearBeat）的房间中的主播记录  1-主播
    InteractAvRoom::deleteDeathRoomMember(90, 1);

    AvRoom::finishInactiveRecord(90);
}

ini_set('date.timezone','Asia/Shanghai');
clear();
