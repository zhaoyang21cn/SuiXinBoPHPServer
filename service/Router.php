<?php
/**
 * Date: 2016/4/20
 */

class Router
{
    private static $mapper = array(
        'live' => array(
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