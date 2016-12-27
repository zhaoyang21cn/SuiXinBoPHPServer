<?php
/**
 * Date: 2016/4/29
 * Update：2016/12/12
 * Tips：此清理程序由crontab定时执行
 */

require_once  __DIR__ . '/../Path.php';
require_once MODEL_PATH . '/InteractAvRoom.php';

function clear()
{
   // 删除10秒没有收到心跳包（HearBeat）的房间成员记录 0-观众
   InteractAvRoom::deleteDeathRoomMember(10, 0);
}

ini_set('date.timezone','Asia/Shanghai');
clear();
