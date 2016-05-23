<?php

namespace Qcloud_cos;

class Cosapi
{
    // 60 seconds
    const EXPIRED_SECONDS = 60;

    //3M
    const DEFAULT_SLICE_SIZE = 3145728;

    //10M
    const MIN_SLICE_FILE_SIZE = 10485760;

    const MAX_RETRY_TIMES = 3;

    const COSAPI_FILE_NOT_EXISTS = -1;
    const COSAPI_NETWORK_ERROR = -2;
    const COSAPI_PARAMS_ERROR = -3;
    const COSAPI_ILLEGAL_SLICE_SIZE_ERROR = -4;

    private static $timeout = 30;
    
    public static function setTimeout($t) {
        if (!is_int($t) || $t < 0) {
            return false;
        }

        self::$timeout = $t;
        return true;
    }

    public static function cosUrlEncode($path) {
        return str_replace('%2F', '/',  rawurlencode($path));
    }
    
    public static function generateResUrl($bucketName, $dstPath) {
        return Conf::API_COSAPI_END_POINT . Conf::APPID . '/' . $bucketName . $dstPath;
    }
        
    public static function sendRequest($req) {
        $rsp = Http::send($req);
        $info = Http::info();
        $ret = json_decode($rsp, true);

        if ($ret) {
            if (0 === $ret['code']) {
                $ret['httpcode'] = $info['http_code'];
                return $ret;
            } else {
                return array(
                    'httpcode' => $info['http_code'], 
                    'code' => $ret['code'], 
                    'message' => $ret['message'], 
                    'data' => array()
                );
            }
        } else {
            return array(
                    'httpcode' => $info['http_code'], 
                    'code' => self::COSAPI_NETWORK_ERROR, 
                    'message' => $rsp, 
                    'data' => array()
                );
        }
    }

    /**
     * 上传文件
     * @param  string  $srcPath     本地文件路径
     * @param  string  $bucketName  上传的bcuket名称
     * @param  string  $dstPath     上传的文件路径
     * @return [type]                [description]
     */
    public static function upload($srcPath, $bucketName, $dstPath, $bizAttr = null) {

        $srcPath = realpath($srcPath);
        $dstPath = self::cosUrlEncode($dstPath);

        if (!file_exists($srcPath)) {
            return array(
                    'httpcode' => 0, 
                    'code' => self::COSAPI_FILE_NOT_EXISTS, 
                    'message' => 'file '.$srcPath.' not exists', 
                    'data' => array());
        }

        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $dstPath);
        $sign = Auth::appSign($expired, $bucketName);
        $sha1 = hash_file('sha1', $srcPath);

        $data = array(
            'op' => 'upload',
            'sha' => $sha1,
            'biz_attr' => (isset($bizAttr) ? $bizAttr : ''),
        );
        if (function_exists('curl_file_create')) {
            $data['filecontent'] = curl_file_create($srcPath);
        } else {
            $data['filecontent'] = '@'.$srcPath;
        }

        $req = array(
            'url' => $url,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $data,
            'header' => array(
                'Authorization:'.$sign,
            ),
        );

        return self::sendRequest($req);
    }

    /**
     * 上传文件
     * @param  string  $srcPath     本地文件路径
     * @param  string  $bucketName  上传的bcuket名称
     * @param  string  $dstPath     上传的文件路径
     * @return [type]                [description]
     */
    public static function upload_slice(
            $srcPath, $bucketName, $dstPath, 
            $bizAttr = null, 
            $sliceSize = 0, $session = null) {

        $srcPath = realpath($srcPath);

        $fileSize = filesize($srcPath);
        if ($fileSize < self::MIN_SLICE_FILE_SIZE) {
            return self::upload(
                    $srcPath, $bucketName, $dstPath,
                    $bizAttr);
        }

        $dstPath = self::cosUrlEncode($dstPath);

        if (!file_exists($srcPath)) {
            return array(
                    'httpcode' => 0, 
                    'code' => self::COSAPI_FILE_NOT_EXISTS, 
                    'message' => 'file '.$srcPath.' not exists', 
                    'data' => array());
        }

        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $dstPath);
        $sign = Auth::appSign($expired, $bucketName);
        $sha1 = hash_file('sha1', $srcPath);

        $ret = self::upload_prepare(
                $fileSize, $sha1, $sliceSize, 
                $sign, $url, $bizAttr, $session);

        if($ret['httpcode'] != 200
                || $ret['code'] != 0) {
            return $ret;
        }

        if(isset($ret['data']) 
                && isset($ret['data']['url'])) {
        //秒传命中，直接返回了url
            return $ret;
        }

        $sliceSize = $ret['data']['slice_size'];
        if ($sliceSize > self::DEFAULT_SLICE_SIZE ||
            $sliceSize <= 0) {
            $ret['code'] = self::COSAPI_ILLEGAL_SLICE_SIZE_ERROR;
            $ret['message'] = 'illegal slice size';
            return $ret;
        }

        $session = $ret['data']['session'];
        $offset = $ret['data']['offset'];

        $sliceCnt = ceil($fileSize / $sliceSize);
        // expired seconds for one slice mutiply by slice count 
        // will be the expired seconds for whole file
        $expired = time() + (self::EXPIRED_SECONDS * $sliceCnt);
        $sign = Auth::appSign($expired, $bucketName);

        $ret = self::upload_data(
                $fileSize, $sha1, $sliceSize,
                $sign, $url, $srcPath,
                $offset, $session);
        return $ret;
    }

    private static function upload_prepare(
            $fileSize, $sha1, $sliceSize,
            $sign, $url, $bizAttr, $session = null) {

        $data = array(
            'op' => 'upload_slice',
            'filesize' => $fileSize,
            'sha' => $sha1,
        );
        isset($bizAttr) && 
            $data['biz_attr'] = $bizAttr;
        isset($session) &&
            $data['session'] = $session;

        if ($sliceSize > 0) {
            if ($sliceSize <= self::DEFAULT_SLICE_SIZE) {
                $data['slice_size'] = $sliceSize;
            } else {
                $data['slice_size'] = self::DEFAULT_SLICE_SIZE;
            }
        }

        $req = array(
            'url' => $url,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $data,
            'header' => array(
                'Authorization:'.$sign,
            ),
        );

        $ret = self::sendRequest($req);
        return $ret;
    
    }

    /**
     * [upload_data description]
     * @param  int $fileSize  filesize
     * @param  string $sha1      filesha
     * @param  int $sliceSize file slice size
     * @param  string $sign      sign, it will be replaced.
     * @param  string $url       url
     * @param  string $srcPath   src path
     * @param  int $offset    upload offset
     * @param  string $session   session string
     * @return array            result array
     */
    private static function upload_data(
            $fileSize, $sha1, $sliceSize,
            $sign, $url, $srcPath, 
            $offset, $session) {
    
        while ($fileSize > $offset) {
            $filecontent = file_get_contents(
                    $srcPath, false, null,
                    $offset, $sliceSize);

            if ($filecontent === false) {
                return array(
                    'httpcode' => 0, 
                    'code' => self::COSAPI_FILE_NOT_EXISTS,
                    'message' => 'read file '.$srcPath.' error', 
                    'data' => array(),
                );
            }

            $boundary = '---------------------------' . substr(md5(mt_rand()), 0, 10); 
            $data = self::generateSliceBody(
                    $filecontent, $offset, $sha1,
                    $session, basename($srcPath), $boundary);

            $req = array(
                'url' => $url,
                'method' => 'post',
                'timeout' => self::$timeout,
                'data' => $data,
                'header' => array(
                    'Authorization:'.$sign,
                    'Content-Type: multipart/form-data; boundary=' . $boundary,
                ),
            );

            $retry_times = 0;
            do {
                $ret = self::sendRequest($req);
                if ($ret['httpcode'] == 200
                    && $ret['code'] == 0) {
                    break;
                }
                $retry_times++;
            } while($retry_times < self::MAX_RETRY_TIMES);

            if($ret['httpcode'] != 200 
                    || $ret['code'] != 0) {
                return $ret;
            }

            if ($ret['data']['session']) {
                $session = 
                    $ret['data']['session'];
            }
            $offset += $sliceSize;
        }

        return $ret;
    }


    private static function generateSliceBody(
            $fileContent, $offset, $sha, 
            $session, $fileName, $boundary) {
        $formdata = '';

        $formdata .= '--' . $boundary . "\r\n";
        $formdata .= "content-disposition: form-data; name=\"op\"\r\n\r\nupload_slice\r\n";

        $formdata .= '--' . $boundary . "\r\n";
        $formdata .= "content-disposition: form-data; name=\"offset\"\r\n\r\n" . $offset. "\r\n";

        $formdata .= '--' . $boundary . "\r\n";
        $formdata .= "content-disposition: form-data; name=\"session\"\r\n\r\n" . $session . "\r\n";

        $formdata .= '--' . $boundary . "\r\n";
        $formdata .= "content-disposition: form-data; name=\"fileContent\"; filename=\"" . $fileName . "\"\r\n"; 
        $formdata .= "content-type: application/octet-stream\r\n\r\n";

        $data = $formdata . $fileContent . "\r\n--" . $boundary . "--\r\n";

        return $data;
    }

    /*
     * 创建目录
     * @param  string  $bucketName
     * @param  string  $path 目录路径，sdk会补齐末尾的 '/'
     *
     */
    public static function createFolder($bucketName, $path,
                  $bizAttr = null) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }
        if (preg_match('/\/$/', $path) == 0) {
            $path = $path . '/';
        }
        $path = self::cosUrlEncode($path);

        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $path);
        $sign = Auth::appSign($expired, $bucketName);

        $data = array(
            'op' => 'create',
            'biz_attr' => (isset($bizAttr) ? $bizAttr : ''),
        );
        
        $data = json_encode($data);

        $req = array(
            'url' => $url,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $data,
            'header' => array(
                'Authorization:'.$sign,
                'Content-Type: application/json',
            ),
        );

        return self::sendRequest($req);
    }

    /*
     * 目录列表
     * @param  string  $bucketName
     * @param  string  $path     目录路径，sdk会补齐末尾的 '/'
     * @param  int     $num      拉取的总数
     * @param  string  $pattern  eListBoth,ListDirOnly,eListFileOnly  默认both
     * @param  int     $order    默认正序(=0), 填1为反序,
     * @param  string  $offset   透传字段,用于翻页,前端不需理解,需要往前/往后翻页则透传回来
     *  
     */
    public static function listFolder(
                    $bucketName, $path, $num = 20, 
                    $pattern = 'eListBoth', $order = 0, 
                    $context = null) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }
        if (preg_match('/\/$/', $path) == 0) {
            $path = $path . '/';
        }

        return self::listBase($bucketName, $path, $num,
                $pattern, $order, $context);
    }

    /*
     * 前缀搜索
     * @param  string  $bucketName
     * @param  string  $prefix   列出含此前缀的所有文件
     * @param  int     $num      拉取的总数
     * @param  string  $pattern  eListBoth,ListDirOnly,eListFileOnly  默认both
     * @param  int     $order    默认正序(=0), 填1为反序,
     * @param  string  $offset   透传字段,用于翻页,前端不需理解,需要往前/往后翻页则透传回来
     *  
     */
    public static function prefixSearch(
                    $bucketName, $prefix, $num = 20, 
                    $pattern = 'eListBoth', $order = 0, 
                    $context = null) {

        if (preg_match('/^\//', $prefix) == 0) {
            $prefix = '/' . $prefix;
        }

        return self::listBase($bucketName, $prefix, $num,
                $pattern, $order, $context);
    }

    private static function listBase(
                    $bucketName, $path, $num = 20, 
                    $pattern = 'eListBoth', $order = 0, $context = null) {

        $path = self::cosUrlEncode($path);
        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $path);
        $sign = Auth::appSign($expired, $bucketName);

        $data = array(
            'op' => 'list',
            'num' => $num,
            'pattern' => $pattern,
            'order' => $order,
            'context' => $context,
        );
        
        $url = $url . '?' . http_build_query($data);

        $req = array(
            'url' => $url,
            'method' => 'get',
            'timeout' => self::$timeout,
            'header' => array(
                'Authorization:'.$sign,
            ),
        );

        return self::sendRequest($req);
    } 


    /*
     * 目录信息 update
     * @param  string  $bucketName
     * @param  string  $path 路径， sdk会补齐末尾的 '/'
     *
     */
    public static function updateFolder($bucketName, $path, 
                  $bizAttr = null) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }
        if (preg_match('/\/$/', $path) == 0) {
            $path = $path . '/';
        }

        return self::updateBase($bucketName, $path, $bizAttr);
    }

    /*
     * 文件信息 update
     * @param  string  $bucketName
     * @param  string  $path 路径
     *
     */
    public static function update($bucketName, $path, 
                  $bizAttr = null) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }

        return self::updateBase($bucketName, $path, $bizAttr);
    }

    private static function updateBase($bucketName, $path, 
                  $bizAttr = null) {

        $path = self::cosUrlEncode($path);
        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $path);
        $sign = Auth::appSign_once(
                $path, $bucketName);

        $data = array(
            'op' => 'update',
            'biz_attr' => $bizAttr,
        );
        
        $data = json_encode($data);

        $req = array(
            'url' => $url,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $data,
            'header' => array(
                'Authorization:'.$sign,
                'Content-Type: application/json',
            ),
        );

        return self::sendRequest($req);
    }

    /*
     * 目录信息 查询
     * @param  string  $bucketName
     * @param  string  $path 路径，sdk会补齐末尾的 '/'
     *  
     */
    public static function statFolder(
                    $bucketName, $path) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }
        if (preg_match('/\/$/', $path) == 0) {
            $path = $path . '/';
        }

        return self::statBase($bucketName, $path);
    }

    /*
     * 文件信息 查询
     * @param  string  $bucketName
     * @param  string  $path 路径
     *  
     */
    public static function stat(
                    $bucketName, $path) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }

        return self::statBase($bucketName, $path);
    }

    private static function statBase(
                    $bucketName, $path) {

        $path = self::cosUrlEncode($path);
        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $path);
        $sign = Auth::appSign($expired, $bucketName);

        $data = array(
            'op' => 'stat',
        );

        $url = $url . '?' . http_build_query($data);

        $req = array(
            'url' => $url,
            'method' => 'get',
            'timeout' => self::$timeout,
            'header' => array(
                'Authorization:'.$sign,
            ),
        );

        return self::sendRequest($req);
    } 

    /*
     * 删除目录
     * @param  string  $bucketName
     * @param  string  $path 路径，sdk会补齐末尾的 '/'
     *                       注意不能删除bucket下根目录/
     *
     */
    public static function delFolder($bucketName, $path) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }
        if (preg_match('/\/$/', $path) == 0) {
            $path = $path . '/';
        }

        return self::delBase($bucketName, $path);
    }

    /*
     * 删除文件
     * @param  string  $bucketName
     * @param  string  $path 路径
     *
     */
    public static function del($bucketName, $path) {
        if (preg_match('/^\//', $path) == 0) {
            $path = '/' . $path;
        }

        return self::delBase($bucketName, $path);
    }

    private static function delBase($bucketName, $path) {
        if ($path == "/") {
            return array(
                    'code' => self::COSAPI_PARAMS_ERROR,
                    'message' => 'can not delete bucket using api! go to http://console.qcloud.com/cos to operate bucket',
                    );
        }

        $path = self::cosUrlEncode($path);
        $expired = time() + self::EXPIRED_SECONDS;
        $url = self::generateResUrl($bucketName, $path);
        $sign = Auth::appSign_once(
                $path, $bucketName);

        $data = array(
            'op' => 'delete',
        );
        
        $data = json_encode($data);

        $req = array(
            'url' => $url,
            'method' => 'post',
            'timeout' => self::$timeout,
            'data' => $data,
            'header' => array(
                'Authorization:'.$sign,
                'Content-Type: application/json',
            ),
        );

        return self::sendRequest($req);
    }
    
//end of script
}

