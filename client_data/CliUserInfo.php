<?php

/**
 * 客户端用户数据类
 * Date: 2016/4/18
 */
class CliUserInfo
{
	/**
	 * uid
	 * @var string
	 */
	private $uid = '';
	/**
	 * 头像
	 * @var string
	 */
    private $avatar = '';
    /**
     * 用户名
     * @var string
     */
    private $username = '';

    public function toJsonArray()
    {
    	return array(
    		'uid' => $this->uid,
    		'avatar' => $this->avatar,
    		'username' => $this->username,
    	);
    }

    // Getters and Setters
    
    /**
     * Gets uid.
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }
    
    /**
     * Sets uid.
     *
     * @param string $uid the uid
     *
     * @return self
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }
    
    /**
     * Gets 头像.
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }
    
    /**
     * Sets 头像.
     *
     * @param string $avatar the avatar
     *
     * @return self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }
    
    /**
     * Gets 用户名.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }
    
    /**
     * Sets 用户名.
     *
     * @param string $username the username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}

?>