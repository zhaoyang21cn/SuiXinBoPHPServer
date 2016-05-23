<?php

/**
 * 日志级别类
 * Date: 2016/4/15
 */
abstract class LogLevel
{
    const DEBUG = 1;
    const INFO = 2;
    const WARN = 4;
    const ERROR = 8;

    public static function toString($logLevel)
    {
        switch ($logLevel)
        {
            case self::DEBUG:
                return 'DEBUG';
                break;
            case self::INFO:
                return 'INFO';
                break;
            case self::WARN:
                return 'WARN';
                break;
            case self::ERROR:
                return 'ERROR';
            default:
                break;
        }
    }
}