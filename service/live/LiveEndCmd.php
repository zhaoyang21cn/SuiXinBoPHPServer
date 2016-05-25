<?php
/**
 * 直播结束接口
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

/**
 * Date: 2016/4/22
 */
class LiveEndCmd extends LiveModifyCmd
{
    public function handle()
    {
        $record = new LiveRecord();

        $ret = $record->loadByHostUid($this->uid);
        if ($ret < 0)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        if ($ret == 0)
        {
            return new CmdResp(ERR_USER_NO_LIVE, 'The user is not in live');
        }
        $cliRecord = new CliLiveData();
        $cliRecord->InitFromLiveRecord($record);
        $cliRecord->setWatchCount($this->watchCount);
        $cliRecord->setTimeSpan($this->timeSpan);
        $cliRecord->setAdmireCount($this->admireCount);
        $fields = array(
            LiveRecord::FIELD_WATCH_COUNT => $this->watchCount,
            LiveRecord::FIELD_TIME_SPAN => $this->timeSpan,
            LiveRecord::FIELD_ADMIRE_COUNT => $this->admireCount,
        );
        $ret = $record->delete();
        if (!$ret)
        {
            return new CmdResp(ERR_SERVER, 'Server internal error');
        }
        $resp = array('record' => $cliRecord->toJsonArray());
        return new CmdResp(ERR_SUCCESS, '', $resp);
    }
}