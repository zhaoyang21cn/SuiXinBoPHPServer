<?php
/**
 * Date: 2016/4/29
 */

require_once  __DIR__ . '/../path.php';
require_once MODEL_PATH . '/LiveRecord.php';

function clear()
{
	// 删除90秒没有收到心跳包（LiveHostHearBeat）的直播记录。
	LiveRecord::deleteInactiveRecord(90);
}

ini_set('date.timezone','Asia/Shanghai');
clear();