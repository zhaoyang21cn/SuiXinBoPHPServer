<?php

require_once dirname(__FILE__) . '/../Path.php';
require_once SERVICE_PATH . '/Router.php';
require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';
require_once LIB_PATH . '/log/FileLogHandler.php';
require_once LIB_PATH . '/log/Log.php';

/**
 * Date: 2016/4/19
 */
class Server
{
    private function sendResp($reply, $svc = "Unknown", $cmd = "Unknown", $start = 0, $end = 0)
    {
        header('Content-Type: application/json');
        $str = json_encode($reply);
        Log::info('response svc = ' . $svc . ', cmd = ' . $cmd . ', time = ' . ($end - $start) . " secs, data:\n" . $str);
        echo $str;
    }

    public function handle()
    {
        $handler = new FileLogHandler(LOG_PATH . '/sxb_' . date('Y-m-d') . '.log');
        Log::init($handler);
        if (!isset($_REQUEST['svc']) || !isset($_REQUEST['cmd']))
        {
            $this->sendResp(
                array('errorCode' => ERR_INVALID_REQ, 
                      'errorInfo' => 'Invalid request.'
                )
            );
            return;
        }
        $svc = $_GET['svc'];
        $cmd = $_GET['cmd'];
        $className = Router::getCmdClassName($svc, $cmd);
        if (empty($className))
        {
            $this->sendResp(
                array(
                    'errorCode' => ERR_INVALID_REQ, 
                    'errorInfo' => 'Invalid request.'
                )
                , $svc, $cmd
            );
            return;
        }

        $str = file_get_contents('php://input');
        Log::info('request svc = ' . $svc . ', cmd = ' . $cmd . ", data:\n" . $str);
        $start = time();
        require_once SERVICE_PATH . '/' . $svc . '/' . $className . '.php';
        $handler = new $className();
        $resp = $handler->execute();
        $reply = $resp->toArray();
        $this->sendResp($reply, $svc, $cmd, $start, time());
    }
}
