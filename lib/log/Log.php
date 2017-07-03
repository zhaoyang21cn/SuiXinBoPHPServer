<?php

/**
 * 日志类
 * Date: 2016/4/15
 */

require_once 'LogLevel.php';
require_once 'FileLogHandler.php';

class Log
{

    private $level;  // 日志级别
    private $handler;  // 日志handler
    private static $instance = null;  // 日志实例

    /**
     * 初始化Log
     * @param $handler - 日志handler对象
     * @param $level - 日志级别, <0 表示所有级别
     */
    public static function init($handler, $level = -1)
    {
        if (!self::$instance instanceof self)
        {
            self::$instance = new self();
        }
        self::$instance->setHandler($handler);
        if ($level >= 0)
        {
            self::$instance->setLevel($level);
        }
        else
        {
            $level = (LogLevel::DEBUG | LogLevel::INFO | LogLevel::WARN | LogLevel::ERROR);
            self::$instance->setLevel($level);
        }
    }

    /**
     * @param $handler
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }
    


    public function writeLog($level, $content)
    {
        $levelStr = LogLevel::toString($level);
        $traceInfo = debug_backtrace();
        $stackSize = count($traceInfo);
        if ($stackSize < 3)
        {
            // NEVER REACH HERE.
            return;
        }
        $row = ($stackSize > 3) ? $traceInfo[3] : $traceInfo[2];
        $fields = array(
            'file',
            'line'.
            'class',
            'function',
        );
        $msg = '';
        foreach ($fields as $field)
        {
            if (isset($row[$field]))
            {
                $msg .= '|' . $field . ':' . $row[$field];
            }
        }
        $msg .= ">\n" . $content;
        if(($level & $this->level) == $level )
        {
            $msg = '<' . date('Y-m-d H:i:s') . '|'. LogLevel::toString($level) . str_replace("\n", "\n\t", $msg) . "\n";
            $this->handler->write($msg);
        }
    }

    public static function debug($msg)
    {
        self::$instance->writeLog(LogLevel::DEBUG, $msg);
    }

    public static function info($msg)
    {
        self::$instance->writeLog(LogLevel::INFO, $msg);
    }

    public static function warn($msg)
    {
        self::$instance->writeLog(LogLevel::WARN, $msg);
    }

    public static function error($msg)
    {
        self::$instance->writeLog(LogLevel::ERROR, $msg);
    }

}