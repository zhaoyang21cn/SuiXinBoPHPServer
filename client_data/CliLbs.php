<?php

/**
 * 客户端地理信息类
 * Date: 2016/4/18
 */
class CliLbs
{
	/**
	 * 经度
	 * @var float
	 */
	private $longitude = 0.0;
	/**
	 * 纬度
	 * @var float
	 */
    private $latitude = 0.0;
    /**
     * 地址
     * @var string
     */
    private $address = '';

    // Getters And Setters

    /**
     * Gets 经度.
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
    
    /**
     * Sets 经度.
     *
     * @param float $longitude the longitude
     *
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }
    
    /**
     * Gets 纬度.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
    
    /**
     * Sets 纬度.
     *
     * @param float $latitude the latitude
     *
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }
    
    /**
     * Gets 地址.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    /**
     * Sets 地址.
     *
     * @param string $address the address
     *
     * @return self
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    public function toJsonArray()
    {
    	return array(
    		'longitude' => $this->longitude,
    		'latitude' => $this->latitude,
    		'address' => $this->address,
    	);
    }

}

?>