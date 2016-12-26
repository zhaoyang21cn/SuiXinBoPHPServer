<?php
/**
 * Date: 2016/4/20
 * Update: 2016/11/18
 */

class Router
{
    private static $mapper = array(
		//独立账号系统
		'account' => array(
			'regist' => 'AccountRegisterCmd',
			'login' => 'AccountLoginCmd',
			'logout' => 'AccountLogoutCmd',
		),
		
        'live' => array(
			//房间
			'create' => 'CreateLiveRoomCmd',
			'reportroom' => 'ReportLiveRoomInfoCmd',
			'roomlist' => 'GetLiveRoomListCmd',
			'exitroom' => 'ExitLiveRoomCmd',

			//上/下麦
			'request' => 'RequestInteractLiveRoomCmd',
			'reportstatus' => 'JoinOrExitInteractLiveCmd',

			//心跳
			'heartbeat' => 'HeartBeatCmd',

			//点播
			'recordlist' => 'GetVideoRecordListCmd',		
			'reportrecord' => 'SaveVideoRecordCmd',

			//成员
			'reportmemid' => 'ReportRoomMemberCmd',
			'roomidlist' => 'GetRoomMemberListCmd',

			//old
            'start' => 'LiveStartCmd',
            'list' => 'LiveListCmd',
            'end' => 'LiveEndCmd',
            'host_heartbeat' => 'LiveHostHeartBeatCmd',
        ),
        'user_av_room' => array(
            'get' => 'UserAvRoomGetCmd',

        ),
        'cos' => array(
            'get_sign' => 'CosGetSignCmd',
        ),
        'test' => array(
            'test' => 'TestCmd',
        ),
    );

    public static function getCmdClassName($svc, $cmd)
    {
        if (!is_string($svc) || !is_string($cmd))
        {
            return '';
        }

        if (isset(self::$mapper[$svc]) && isset(self::$mapper[$svc][$cmd]))
        {
            return self::$mapper[$svc][$cmd];
        }
        return '';
    }


}
