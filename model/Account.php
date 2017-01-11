<?php
/**
 * 独立账号模块
 * Date: 2016/11/20
 * Update：2016/12/23
 */

require_once dirname(__FILE__) . '/../Path.php';
require_once LIB_PATH . '/db/DB.php';

class Account
{
    // 用户名 => string
    private $uid;

    // 用户密码 => string
    private $pwd;

    // 用户token => string
    private $token;

    // 用户登录状态 => int
    private $state;
    
    // 签名 => string
    private $userSig;

    // 注册时间 => int 
    private $registerTime;
    
    // 登录时间 => int
    private $loginTime;

    // 注销时间 => int
    private $logoutTime;

    // 最近一次请求时间 => int
    private $lastRequestTime;
    
    public function __Construct()
    {
        $this->uid = '';
        $this->pwd='';
        $this->token = '';
        $this->userSig = '';
        $this->state = 0;
        $this->registerTime = 0;
        $this->loginTime = 0;
        $this->logoutTime = 0;
        $this->lastRequestTime = 0;
        
    }
    
    public function getUser()
    {
        return $this->uid;
    }
    
    public function setUser($uid)
    {
        $this->uid = $uid;
    }
    
    public function getPwd()
    {
        return $this->pwd;
    }
    
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;
    }
    
    public function getState()
    {
        return $this->state;
    }
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    public function getUserSig()
    {
        return $this->userSig;
    }
    
    public function setUserSig($userSig)
    {
        $this->userSig = $userSig;
    }
    
    public function getRegisterTime()
    {
        return $this->registerTime;
    }
    
    public function setRegisterTime($registerTime)
    {
        $this->registerTime = $registerTime;
    }
    
    public function getLoginTime()
    {
        return $this->loginTime;
    }
    
    public function setLoginTime($loginTime)
    {
        $this->loginTime = $loginTime;
    }
    
    public function getLogoutTime()
    {
        return $this->logoutTime;
    }
    
    public function setLogoutTime($logoutTime)
    {
        $this->logoutTime = $logoutTime;
    }
    
    public function getLastRequestTime()
    {
        return $this->lastRequestTime;
    }
    
    public function setLastRequestTime($lastRequestTime)
    {
        $this->lastRequestTime = $lastRequestTime;
    }

    /* 功能：通过用户名获取用户的个人账号信息
     * 说明：用户名通过Account对象成员获取，查询到的个人账号信息直接存储在
     *        Account对象成员中；成功返回0，error_msg为空；失败则返回错误码，
     *        设置错误信息error_msg。    
     */
    public function getAccountRecordByUserID(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {
            $sql = 'SELECT * from t_account WHERE uid=:uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'Server inner error';
                return ERR_SERVER;
            }
            
            if ($stmt->rowCount() == 0)
            {
                $error_msg = 'User not exist';
                return ERR_USER_NOT_EXIST;
            }
            $row = $stmt->fetch();
            $this->pwd= $row['pwd'];
            $this->state = $row['state'];
            $this->token = $row['token'];
            $this->userSig = $row['user_sig'];
            $this->loginTime = $row['login_time'];
            $this->logoutTime = $row['logout_time'];
            $this->registerTime = $row['register_time'];
            $this->lastRequestTime = $row['last_request_time'];
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：通过用户Token获取用户的个人账号信息
     * 说明：用户Token通过Account对象成员获取，查询到的个人账号信息直接存储在
     *        Account对象成员中；成功返回0，error_msg为空；失败则返回错误码，
     *        设置错误信息error_msg。    
     */    
    public function getAccountRecordByToken(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {
            $sql = 'SELECT * from t_account WHERE token=:token';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {                
                $error_msg = 'Server inner error';
                return ERR_SERVER;
            }
            
            $result = $stmt->rowCount();
            if($result == 0)
            {
                $error_msg = 'Token expired';
                return ERR_TOKEN_EXPIRE;
            }

            if ($stmt->rowCount() == 0)
            {
                $error_msg = 'User not exist';
                return ERR_USER_NOT_EXIST;
            }

            $row = $stmt->fetch();
            $this->uid= $row['uid'];
            $this->state = $row['state'];
            $this->userSig = $row['user_sig'];
            $this->loginTime = $row['login_time'];
            $this->logoutTime = $row['logout_time'];
            $this->registerTime = $row['register_time'];
            $this->lastRequestTime = $row['last_request_time'];
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：用户Token转换用户名
     * 说明：用户Token通过Account对象成员token获取，查询到的用户名直接存储在
     *        Account对象成员uid中；成功返回0，error_msg为空；失败则返回错误码，
     *        设置错误信息error_msg。    
     */
    public function getAccountUidByToken(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {
            $sql = 'SELECT uid from t_account WHERE token=:token';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'User id not existed';
                return ERR_USER_NOT_EXIST;
            }
            
            if ($stmt->rowCount() == 0)
            {
                $error_msg = 'User not exist';
                return ERR_USER_NOT_EXIST;
            }
            $row = $stmt->fetch();
            $this->uid = $row['uid'];
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        $error_msg = '';
        return ERR_SUCCESS;
    }
    
    /* 功能：用户密码正确性验证
     * 说明：用户输入密码pwd和DB中的pwd解密比对；DB中的密码使用base64加密存储
     */
    public function authentication($pwd, &$error_msg)
    {
        $pwd_decode = '';
        $cmd = 'echo "' . $this->pwd . '" | base64 -d ';
        $ret = exec($cmd, $pwd_decode, $status);
        if($status != 0)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        
        if($pwd_decode[0] != $pwd)
        {            
            $error_msg = 'User password error';
            return ERR_PASSWORD;
        }
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：创建临时sig文件
     * 说明：使用工具mktemp在deps/sig目录下创建临时文件sxb_sig.XXXXXXXXXX
     *        成功返回临时文件的绝对路径，失败返回null
     */
    private function mktemp()
    {
        $cmd = 'mktemp -t -p ' . DEPS_PATH . '/sig sxb_sig.XXXXXXXXXX';
        $ret = exec($cmd, $out, $status);
        if ($status != 0)
        {
            return null;
        }
        return $out[0];
    }
    
    /* 功能：删除临时sig文件
     * 说明：使用工具rm强制删除在deps/sig目录下指定的临时文件        
     */
    private function rmtemp($tmp)
    {
        $cmd = 'rm -f ' . $tmp;
        $ret = exec($cmd, $out, $status);
        if ($status != 0)
        {
            return false;
        }
        return true;
    }

    /* 功能：生成sig
     * 说明：对当前用户使用指定的sdkappid, 和指定的秘钥文件路径（private_key_path）使用
     *         tls_licence_tools工具生成sig，并sig存储在临时文件中。成功，sig返回给用户后，
     *        失败返回空；同时删除此临时sig文件。此sig用于前端SDK的登录。    
     * 备注：这里使用临时文件原因一是受工具tls_licence_tools只能读写文件方式所限；二是
     *        如果使用单个文件过渡将导致并发读写错误的问题。
     *        php另外一种集成sig的方式参考这里：
     *                https://www.qcloud.com/document/product/269/1510
     *                http://bbs.qcloud.com/thread-22519-1-1.html
     */
    public function genUserSig($sdkappid, $private_key_path)
    {
        // 创建临时sig文件
        $sig = $this->mktemp();
        if(!$sig) {
            return null;
        }

        // 生成sig
        $cmd = DEPS_PATH . '/bin/tls_licence_tools'
            . ' ' . 'gen'
            . ' ' . escapeshellarg($private_key_path)
            . ' ' . escapeshellarg($sig)
            . ' ' . escapeshellarg($sdkappid)
            . ' ' . escapeshellarg($this->uid);
        $ret = exec($cmd, $out, $status);
        if ($status != 0)
        {
            return null;
        }

        // 读取sig
        $out=array();
        $cmd = 'cat ' . ' ' . escapeshellarg($sig);
        $ret = exec($cmd, $out, $status);

        $this->rmtemp($sig);

        if ($status != 0)
        {
            return null;
        }

        return $out[0];
    }

    /* 功能：校验sig
     * 说明：对当前用户使用指定的sdkappid, 和指定的公钥文件路径（public_key_path）使用
     *         tls_licence_tools工具校验sig。成功，返回0；sig失败返回1；失败返回-1
     */    
    public function verifyUserSig($sdkappid, $public_key_path)
    {
        $sig = $this->mktemp();
        if(!$sig) {
            return -1;
        }

        $cmd = 'echo ' . $this->userSig . '>'. escapeshellarg($sig);
        $ret = exec($cmd, $out, $status);
        if ($status != 0)
        {
            $this->rmtemp($sig);
            return -1;
        }

        $out=array();
        $cmd = DEPS_PATH . '/bin/tls_licence_tools'
            . ' ' . 'verify'
            . ' ' . escapeshellarg($public_key_path)
            . ' ' . escapeshellarg($sig)
            . ' ' . escapeshellarg($sdkappid)
            . ' ' . escapeshellarg($this->uid);
        $ret = exec($cmd, $out, $status);

        $this->rmtemp($sig);

        if ($status != 0)
        {
            return -1;
        }
        //return $out[1]; //校验信息
        if($out[0] != 'verify sig ok')
        {
            return 1;
        }

        return 0;
    }

    /* 功能：生成用户token
     * 说明：生成方式：用户名+登录时间再base64加密。用户名是唯一，因此用户token一定唯一
     *        成功，返回token；失败返回空。开发人员可以自定义，只要保证token唯一即可
     */
    public function genToken()
    {
        $cmd = 'echo \'' . $this->uid . $this->loginTime . '\'| base64';
        $ret = exec($cmd, $out, $status);
        if ($status != 0)
        {
            return null;
        }
        return $out[0];
    }
    
    /* 功能：注册用户
     * 说明：成功返回ERR_SUCCESS，error_msg为空；失败则返回错误码，设置错误信息error_msg。    
     */  
    public function register(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {
            // 查重
            $sql = 'SELECT COUNT(*) as num from t_account WHERE uid=:uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            $result = $stmt->execute();
            if ($stmt->fetch()['num'] == 1)
            {
                $error_msg = 'Register user id existed';
                return ERR_REGISTER_USER_EXIST;
            }
            
            // 添加用户
            $sql = 'INSERT INTO t_account set uid=:uid, pwd=:pwd, register_time=:registerTime';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);

            // 加密密码
            $cmd = "echo $this->pwd | base64";
            $pwd = '';
            exec($cmd, $pwd, $ret);

            if($ret != 0)
            {
                $error_msg = 'Server inner error';
                return ERR_SERVER;
            }
            $stmt->bindParam(':registerTime', $this->registerTime, PDO::PARAM_INT);
            $stmt->bindParam(':pwd', $pwd[0], PDO::PARAM_STR);
                        
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'Server inner error, Regist fail!';
                return ERR_SERVER;
            }         
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }

        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：用户登录
     * 说明：成功返回ERR_SUCCESS，error_msg为空；失败则返回错误码，设置错误信息error_msg。    
     *          存储token，有效期内免登陆；更新时间
     */
    public function login(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {
            $sql = 'UPDATE t_account SET `state` = 1, token=:token, user_sig=:userSig,  login_time=:loginTime, last_request_time=:lastRequestTime WHERE uid=:uid';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':lastRequestTime', $this->loginTime, PDO::PARAM_INT);
            $stmt->bindParam(':loginTime', $this->loginTime, PDO::PARAM_INT);
            $stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
            $stmt->bindParam(':userSig', $this->userSig, PDO::PARAM_STR);
            $stmt->bindParam(':uid', $this->uid, PDO::PARAM_STR);
            
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'Server inner error';
                return ERR_SERVER;
            }          
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：注销登录
     * 说明：成功返回ERR_SUCCESS，error_msg为空；失败则返回错误码，设置错误信息error_msg。
     *        删除token    
     */
    public function logout(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {    
            $sql = 'UPDATE t_account SET `state` = 0, token=null, logout_time=:logoutTime WHERE token=:token';
            $stmt = $dbh->prepare($sql);         
            $stmt->bindParam(':logoutTime', $this->logoutTime, PDO::PARAM_INT);
            $stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'Server inner error, Logout fail!';
                return ERR_SERVER;
            }
            $result = $stmt->rowCount();
            if($result == 0)
            {
                $error_msg = 'Repeat logout';
                return ERR_REPEATE_LOGOUT;
            }
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        $error_msg = '';
        return ERR_SUCCESS;
    }

    /* 功能：更新用户请求时间
     * 说明：成功返回ERR_SUCCESS，error_msg为空；失败则返回错误码，设置错误信息error_msg。
     *        
     */
    public function updateLastRequestTime(&$error_msg)
    {
        $dbh = DB::getPDOHandler();
        if (is_null($dbh))
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
        try
        {    
            $sql = 'UPDATE t_account SET last_request_time=:lastRequestTime WHERE token=:token';
            $stmt = $dbh->prepare($sql);         
            $stmt->bindParam(':lastRequestTime', $this->lastRequestTime, PDO::PARAM_INT);
            $stmt->bindParam(':token', $this->token, PDO::PARAM_STR);
            $result = $stmt->execute();
            if (!$result)
            {
                $error_msg = 'Server inner error';
                return ERR_SERVER;
            }            
        }
        catch (PDOException $e)
        {
            $error_msg = 'Server inner error';
            return ERR_SERVER;
        }
         $error_msg = '';
        return ERR_SUCCESS;
    }
}

?>
