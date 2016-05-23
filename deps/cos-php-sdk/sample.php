<?php

require('./include.php');

use Qcloud_cos\Auth;
use Qcloud_cos\Cosapi;

$bucketName = 'test_mikenwang_20150623';

//$srcPath = '/home/ubuntu/cos/sdk/63MB_test.exe';
//$dstPath = '/63MB_test.exe';
//$srcPath = '/home/ubuntu/cos/sdk/php-sdk/cos-php-sdk/test.mp4';
//$dstPath = '/test.mp4';
$srcPath = './test.log';
$dstPath = "/test.log";

Cosapi::setTimeout(10);

// 上传文件
//$uploadRet = Cosapi::upload($srcPath, $bucketName, 
//        $dstPath);
//var_dump($uploadRet);

//分片上传
//$sliceUploadRet = Cosapi::upload_slice(
//        $srcPath, $bucketName, $dstPath);
//用户指定分片大小来分片上传
//$sliceUploadRet = Cosapi::upload_slice(
//        $srcPath, $bucketName, $dstPath, null, 3*1024*1024);
//指定了session，可以实现断点续传
//$sliceUploadRet = Cosapi::upload_slice(
//        $srcPath, $bucketName, $dstPath, null, 2000000, '48d44422-3188-4c6c-b122-6f780742f125+CpzDLtEHAA==');
//var_dump($sliceUploadRet);

//创建目录
//$createFolderRet = Cosapi::createFolder($bucketName, "/test/");
//var_dump($createFolderRet);

//listFolder
$listRet = Cosapi::listFolder($bucketName, "/");
var_dump($listRet);

//prefixSearch
//$ret = Cosapi::prefixSearch($bucketName, "/test");
//var_dump($ret);

//updateFolder
//$updateRet = Cosapi::updateFolder($bucketName, '/test/', '{json:0}');
//var_dump($updateRet);

//update
//$updateRet = Cosapi::update($bucketName, $dstPath, '{json:1}');
//var_dump($updateRet);

//statFolder
//$statRet = Cosapi::statFolder($bucketName, "/test/");
//var_dump($statRet);

//stat
//$statRet = Cosapi::stat($bucketName, $dstPath);
//var_dump($statRet);

//delFolder
//$delRet = Cosapi::delFolder($bucketName, "/test/");
//var_dump($delRet);

//del
//$delRet = Cosapi::del($bucketName, $dstPath);
//var_dump($delRet);

//end of script


