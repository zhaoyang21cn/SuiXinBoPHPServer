<?php

require_once dirname(__FILE__) . '/../path.php';
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
    
    private function sendResp($reply)
    {
        header('Content-Type: application/json');
        $str = json_encode($reply);
        Log::info('response data: ' . $str);
        echo $str;
    }

    public function handle()
    {
        $handler = new FileLogHandler(basename(__DIR__) . '/sxb_' . date('Y-m-d') . '.log');
        // $handler = new FileLogHandler('/data/log/sxb/sxb_' . date('Y-m-d') . '.log');
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
            );
            return;
        }


        $str = file_get_contents('php://input');
        Log::info('request data: '. $str);
        $start = time();
        require_once SERVICE_PATH . '/' . $svc . '/' . $className . '.php';
        $handler = new $className();
        $resp = $handler->execute();
        $reply = $resp->toArray();
        $end = time();
        Log::info('response time: ' . ($end - $start) . ' secs, svc = ' . $svc . ', cmd = ' . $cmd);
        $this->sendResp($reply);
    }
}
