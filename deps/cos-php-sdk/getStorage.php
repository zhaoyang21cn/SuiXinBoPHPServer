<?php

require('./include.php');

use Qcloud_cos\Auth;
use Qcloud_cos\Cosapi;

Cosapi::setTimeout(100);

function recursiveGetStorage($bucketName, $path, $context = null) {
    global $storage, $fileNum, $dirNum;
    $listRet = Cosapi::listFolder($bucketName, $path, 199, null, null, $context);
    //var_dump($listRet);
    while ($listRet && $listRet['code'] === 0 && $listRet['data']['infos']) {
        foreach($listRet['data']['infos'] as $item) {
            if (isset($item['filelen'])) {
                $fileNum++;
                $storage += $item['filesize'];
                if ($item['filelen'] != $item['filesize']) {
                    echo "$path" . $item['name'] . 
                         " filesize:" . $item['filesize'] . 
                         " filelen:" . $item['filelen'] . "\n";
                }
            } else {
                $dirNum++;
                recursiveGetStorage($bucketName, $path . $item['name'] . '/');
            }
        }

        $context = $listRet['data']['context'];
        $listRet = Cosapi::listFolder($bucketName, $path, 10, null, null, $context);
        //var_dump($listRet);
    }

    if ($listRet['code'] !== 0) {
        echo "$path has error, code:" . $listRet['code'] . "\n";
    }
    return;
}

$bucketName = 'userdata';
$rootPath = '/';

$storage = 0;
$fileNum = 0;
$dirNum = 0;

recursiveGetStorage($bucketName, $rootPath);
echo "storage:" . $storage . "\n";
echo "fileNum:" . $fileNum . "\n";
echo "dirNum:" . $dirNum . "\n";


