# tencentyun-cos-php
php sdk for [腾讯云对象存储服务](http://wiki.qcloud.com/wiki/COS%E4%BA%A7%E5%93%81%E4%BB%8B%E7%BB%8D)

## 安装（直接下载源码集成）

### 直接下载源码集成
从github下载源码装入到您的程序中，并加载include.php
调用请参考示例1

## 修改配置
修改Qcloud_cos/Conf.php内的appid等信息为您的配置

## 上传、查询、删除程序示例1（使用tencentyun提供的include.php）
```php
<?php

require('./include.php');

use Qcloud_cos\Auth;
use Qcloud_cos\Cosapi;

$bucketName = 'your bucket name';
$srcPath = 'local file path';
$dstPath = 'remote file path';

// 上传文件
$uploadRet = Cosapi::upload('test.log', $bucketName, '/test.log');
var_dump($uploadRet);

//分片上传
$sliceUploadRet = Cosapi::upload_slice(
        $srcPath, $bucketName, $dstPath);
//用户指定分片大小来分片上传
//$sliceUploadRet = Cosapi::upload_slice(
//        $srcPath, $bucketName, $dstPath, null, 2000000);
//指定了session，可以实现断点续传
//$sliceUploadRet = Cosapi::upload_slice(
//        $srcPath, $bucketName, $dstPath, null, 2000000, '48d44422-3188-4c6c-b122-6f780742f125+CpzDLtEHAA==');
var_dump($sliceUploadRet);

//创建目录
$createFolderRet = Cosapi::createFolder($bucketName, "/test/");
var_dump($createFolderRet);

//listFolder
$listRet = Cosapi::listFolder($bucketName, "/");
var_dump($listRet);

//prefixSearch
$ret = Cosapi::prefixSearch($bucketName, "/test");
var_dump($ret);

//updateFolder
$updateRet = Cosapi::updateFolder($bucketName, '/test/', 'attribution');
var_dump($updateRet);

//update
$updateRet = Cosapi::update($bucketName, $dstPath, 'attribution');
var_dump($updateRet);

//statFolder
$statRet = Cosapi::statFolder($bucketName, "/test/");
var_dump($statRet);

//stat
$statRet = Cosapi::stat($bucketName, $dstPath);
var_dump($statRet);

//delFolder
$delRet = Cosapi::delFolder($bucketName, "/test/");
var_dump($delRet);

//del
$delRet = Cosapi::del($bucketName, $dstPath);
var_dump($delRet);


```
