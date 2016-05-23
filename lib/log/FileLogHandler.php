<?php

require_once 'LogHandlerInterface.php';

/**
 * 文件日志Handler
 * Date: 2016/4/15
 */
class FileLogHandler implements LogHandlerInterface
{
    private $handler;

    public function __construct($fileName = '')
    {
        $this->handler = fopen($fileName, 'a');
    }

    public function write($msg)
    {
        if (flock($this->handler, LOCK_EX))
        {
            // 最大写入长度4096字节
            fwrite($this->handler, $msg, 4096);
            fflush($this->handler);
            flock($this->handler, LOCK_UN);
        }
    }

    public function __destruct()
    {
        fclose($this->handler);
    }


}