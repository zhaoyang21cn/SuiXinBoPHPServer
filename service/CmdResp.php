<?php

/**
 * Date: 2016/4/19
 */
class CmdResp
{
    private $errorCode;
    private $errorInfo;
    private $data;

    public function __construct($errorCode, $errorInfo, $data = null)
    {
        $this->errorCode = $errorCode;
        $this->errorInfo = $errorInfo;
        $this->data = $data;
    }

    public function isSuccess()
    {
        return $this->errorCode === ERR_SUCCESS;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorInfo()
    {
        return $this->errorInfo;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = $this->data;
        $result = array();
        $result['errorCode'] = $this->getErrorCode();
        $result['errorInfo'] = $this->getErrorInfo();
        if (is_array($data))
        {
            $result['data'] = $data;
        }
        return $result;
    }


}
