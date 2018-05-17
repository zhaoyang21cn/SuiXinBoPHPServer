<?php

/**
 * User: neallin
 * Date: 2018/3/26
 * Time: 11:50
 * 视频签名
 */
require_once dirname(__FILE__) . '/../../Config.php';

require_once SERVICE_PATH . '/Cmd.php';
require_once SERVICE_PATH . '/CmdResp.php';
require_once ROOT_PATH . '/ErrorNo.php';

require_once MODEL_PATH . '/Account.php';
require_once LIB_PATH . '/tools/TLSSig.php';

class AccountAuthPrivMapCmd extends Cmd
{
    // 用户账号对象
    private $account;
    private $appid;
    private $privatekey;
    private $publickey;

    public function __construct()
    {
        $this->account = new Account();
    }

    public function parseInput()
    {

        if (empty($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of pwd');
        }
        if (!is_string($this->req['pwd']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Invalid pwd');
        }
        if (!isset($this->req['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of roomnum');
        }
        if (!is_int($this->req['roomnum']))
        {
            return new CmdResp(ERR_REQ_DATA, ' Invalid roomnum');
        }

        if (!isset($this->req['identifier']))
        {
            return new CmdResp(ERR_REQ_DATA, 'Lack of identifier');
        }

        $this->account->setUser($this->req['identifier']);
        if (isset($this->req['appid']) && is_int($this->req['appid']))
        {
            $this->appid = strval($this->req['appid']);
        }
        else
        {
            $this->appid = DEFAULT_SDK_APP_ID;
        }

        $this->privatekey = KEYS_PATH . '/' . $this->appid . '/private_key';
        $this->publickey = KEYS_PATH . '/' . $this->appid . '/public_key';

        if(!file_exists($this->privatekey) || !file_exists($this->publickey)){
            return new CmdResp(ERR_REQ_DATA, 'Invalid appid');
        }
        return new CmdResp(ERR_SUCCESS, '');
    }

    public function getSign(){
        try{
            $sdkappid = $this->appid;
            $roomnum = $this->req['roomnum'];
            $expire_time = isset($this->req['$expire_time']) ? $this->req['$expire_time'] : 600;
            $identifier = $this->req['identifier'];
            $accounttype = intval( isset($this->req['accounttype']) ? $this->req['accounttype'] : 0 );

            if( isset($this->req['privMap']) ){
              $privMap = intval($this->req['privMap']);
            }else{
              $privMap_list = array_reverse(array(
                'UPB_CREATE',
                'UPB_ENTER',
                'UPB_SEND_AUDIO',
                'UPB_RECV_AUDIO',
                'UPB_SEND_VIDEO',
                'UPB_RECV_VIDEO',
                'UPB_SEND_ASSIST',
                'UPB_RECV_ASSIST',
              ));
              $privMapBit = [];
              foreach( $privMap_list as $item){
                  $val = isset($this->req[$item]) ? $this->req[$item] : '0';
                  array_push($privMapBit, $val);
              }
              $privMap = intval( base_convert( implode('',$privMapBit), 2,10) );
            }
            $api = new TLSSigAPI();
            $api->SetAppid($sdkappid);//设置在腾讯云申请的appid
            $private = file_get_contents( $this->privatekey );
            $api->SetPrivateKey($private);//生成usersig需要先设置私钥
            $public = file_get_contents( $this->publickey );
            $api->SetPublicKey($public);//校验usersig需要先设置公钥

            $userbuf = pack('C1','0'); //cVer	unsigned char/1	版本号，填0
            $userbuf .= pack('n',strlen($identifier)); //wAccountLen	unsigned short /2	第三方自己的帐号长度
            $userbuf .= pack('a'.strlen($identifier),$identifier); //buffAccount	wAccountLen	第三方自己的帐号字符
            $userbuf .= pack('N',$sdkappid); //dwSdkAppid	unsigned int/4	sdkappid
            $userbuf .= pack('N',$roomnum); //dwAuthId	unsigned int/4	群组号码
            $userbuf .= pack('N', time() + $expire_time); //dwExpTime	unsigned int/4	过期时间 （当前时间 + 有效期（单位：秒，建议300秒））
            // $userbuf .= pack('N', 1522034973); //dwExpTime	unsigned int/4	过期时间 （当前时间 + 有效期（单位：秒，建议300秒））
            // $userbuf .= pack('n',0); //dwPrivilegeMap	unsigned int/4	权限位
            $userbuf .= pack('N',$privMap); //dwPrivilegeMap	unsigned int/4	权限位
            $userbuf .= pack('N',$accounttype); //dwAccountType	unsigned int/4	第三方帐号类型

            $sig = $api->genSigWithUserbuf($identifier ,$userbuf);//生成usersig

            // $result = $api->verifySigWithUserbuf($sig, $identifier, $init_time, $expire_time, $userbuf, $error_msg);
            // var_dump($result);
            return [
              'privMap'=>$privMap,
              'privMapEncrypt'=> $sig
            ];
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    public function handle()
    {
        $account = $this->account;
        $errorMsg = '';

        // 获取用户账号信息
        // $ret = $account->getAccountRecordByUserID($errorMsg);
        // if($ret != ERR_SUCCESS)
        // {
        //     return new CmdResp($ret, $errorMsg);
        // }

        // // 密码验证
        // $ret = $account->authentication($this->req['pwd'], $errorMsg);
        // if($ret != ERR_SUCCESS)
        // {
        //     return new CmdResp($ret, $errorMsg);
        // }

        // 获取sig
        $userSig = $account->getUserSig();
        if(empty($userSig))
        {
            $userSig = $account->genUserSig($this->appid, $this->privatekey);
            // 更新对象account的成员userSig
            $account->setUserSig($userSig);
        }
        else
        {
            $ret = $account->verifyUserSig($this->appid, $this->publickey);
            if($ret == 1) //过期重新生成
            {
                $userSig = $account->genUserSig($this->appid, $this->privatekey);
                // 更新对象account的成员userSig
                $account->setUserSig($userSig);
            }
            else if($ret == -1)
            {
                return new CmdResp(ERR_SERVER, 'Server error:gen sig fail');
            }
        }
        if(empty($userSig))
            return new CmdResp(ERR_SERVER, 'Server error: gen sig fail');


        //获取token
        $token = $account->genToken();
        if(empty($token))
        {
            return new CmdResp(ERR_SERVER, 'Server error');
        }
        $account->setToken($token);

        $account->setLoginTime(date('U'));

        //登录，更新DB
        $ret = $account->login($errorMsg);

        if($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }

        //更新 app id
        $ret = $account->updateCurrentAppid($errorMsg, $this->appid);

        if ($ret != ERR_SUCCESS)
        {
            return new CmdResp($ret, $errorMsg);
        }
        else
        {
            $data['userSig'] = $userSig;
            $data['token'] = $token;
            $data = array_merge($data , $this->getSign());
            return new CmdResp(ERR_SUCCESS, '', $data);
        }
    }
}
