# 随心播 Server QuickStart
该后台演示了在互动直播 [独立账号模式](https://www.qcloud.com/document/product/268/7654) 下如何为App提供用户管理接口，作为开发者后台，该后台与开发者App及互动直播server的关系如下图所示：
![](https://mc.qcloudimg.com/static/img/4094feaf383cf1e3c5714bd3f9dbfc8e/hudongzhibo.png)

## 1. 代码部署

### 1.1 搭建PHP和数据库环境

##### 服务器环境要求 

* Linux OS
* PHP >= 5.4
* MySQL >= 5.5.3

### 1.2 数据库建表建库

* 执行sxb_db.sql文件中的sql。
```bash
mysql -u root -p #此处可能需要输入mysql密码
> source sxb_db.sql #执行建库脚本
```

### 1.3 修改配置

* 下载代码，部署到php目录中
* 在lib/db/DBConfig.php填写mysql的数据库url、用户名和密码
* 在Config.php中填写默认的SDKAppID及对应用于跨房连麦的权限密钥：
```php
    define('DEFAULT_SDK_APP_ID', 'Your_SDK_APP_ID'); //默认APPID
    define('AUTHORIZATION_KEY', serialize([
        'Your_SDK_APP_ID' => 'Your_Authrization_Key'
    ])); //权限密钥表
```
* 上传SDKAppID对应的公私钥到deps/keys/[SDKAppID]目录下，使其具有可读权限（[SDKAppID]替换为你所使用的SDKAppID）
* 在Config.php中填写secretID和SecretKey用于拉取视频列表：
```php
    define('VIDEO_RECORD_SECRET_ID', 'Your_Video_Secret_ID'); //录像Secret ID
    define('VIDEO_RECORD_SECRET_KEY', 'Your_Video_Secret_Key'); //录像Secret Key
```
* 修改service/service/Server.php的这句代码为自己的日志路径
```php
    $handler = new FileLogHandler('/data/log/sxb/sxb_' . date('Y-m-d') . '.log');
```
* 修改deps/bin/tls_licence_tools具有可执行权限，用于生产userSig；32位OS请用tls_licence_tools_32替换tls_licence_tools（tls_licence_tools名字不变）
* 修改deps/bin/linksig具有可执行权限，用于生成跨房连麦sig
* 修改deps/sig目录权限(没有该目录请自行创建)，使得其他用户有可读写执行权限（chmod 757 deps/sig），用于生成sig临时文件的目录。
* 如果您在使用直播码进行旁路推流，调整service/live/ReportLiveRoomInfoCmd.php代码的BIZID。
```php
    const BIZID = '123456';
```
* 如果想使用图片上传功能，需要开通腾讯云COS服务，并在deps/cos-php-sdk/Conf.php填写对应APPID、SecretKey和SecretID。

### 1.4 确定服务器已正常运行

* 在浏览器中访问 `http://your.server.hostname/index.php`，若显示以下json数据说明服务器已正常运行：
```json
{"errorCode":10001,"errorInfo":"Invalid request."}
```

## 2. 请求格式说明

## 2.1 HTTP请求格式
* HTTP请求的类型为 `POST`
* HTTP请求的URL格式为 `http://your.server.hostname/index.php?svc=*&cmd=*`
* HTTP请求的 `Content-Type` 为 `application/json; charset=utf-8`
* HTTP请求的内容为json格式的参数描述
* HTTP请求的URL中的 `svc`、`cmd` 取值及内容中的参数格式详见[接口文档](https://github.com/zhaoyang21cn/SuiXinBoPHPServer/blob/StandaloneAuth/%E9%9A%8F%E5%BF%83%E6%92%AD%E6%8E%A5%E5%8F%A3.markdown)

## 2.2 HTTP请求实例

### 2.2.1 Qt发送登录请求
```cpp
QString text(QStringLiteral("{\"id\":\"username\",\"pwd\":\"password\"}"));
QByteArray const data = text.toUtf8();

QNetworkRequest request(QUrl(QStringLiteral("http://your.server.hostname/index.php?svc=account&cmd=login")));
request.setHeader(QNetworkRequest::ContentTypeHeader, QStringLiteral("application/json; charset=utf-8"));

QNetworkAccessManager *manager = new QNetworkAccessManager(this);
manager->post(request, data);
```

### 2.2.2 Android发送登录请求

* 参考随心播中的 [UserServerHelper](https://github.com/zhaoyang21cn/ILiveSDK_Android_Demos/blob/master/app/src/main/java/com/tencent/qcloud/suixinbo/presenters/UserServerHelper.java)类
* 将类中 `SERVER` 字段的域名替换为自己的服务器域名
* 调用类方法进行服务器接口访问
```java
UserServerHelper.getInstance().loginId("username", "password");
```

### 2.2.3 iOS发送登录请求

* 参考随心播中的 [WebService](https://github.com/zhaoyang21cn/ILiveSDK_iOS_Demos/blob/master/suixinbo/TILLiveSDKShow/WebService) 工具类
* 将类 `BaseRequest` 中 `- (NSString *)hostUrl` 接口返回值的域名替换为自己的服务器域名
* 调用工具类进行服务器接口访问
```ObjectiveC
LoginRequest *sigReq = [[LoginRequest alloc] initWithHandler:^(BaseRequest *request) {
    LoginResponceData *responseData = (LoginResponceData *)request.response.data;
    //登录成功
    //responseData.token 返回token
    //responseData.userSig 返回userSig
} failHandler:^(BaseRequest *request) {
    //登录失败    
}];
sigReq.identifier = @"username";
sigReq.pwd = @"password";
[[WebServiceEngine sharedEngine] asyncRequest:sigReq];
```

## 3. 代码目录结构

![](https://mc.qcloudimg.com/static/img/0413205b36b65645ef4a5ddd8135198c/2.png)

### 3.1 service 

服务层，也就是接口层，主要包括：账号管理，直播服务、AV房间服务、COS服务。每个服务（亦即模块）下是各个子接口。详细可参看协议文档。

#### 3.1.1 直播服务

- 开始直播：数据库Replace一条记录，注意一个用户同一时间最多只能有一场直播；
- 直播结束：从数据库中删除记录；
- 直播列表：从数据库分页获取直播列表；
- 直播心跳包：客户端10秒发一次心跳包更新数据。

#### 3.1.2 Cos服务

获取Cos签名。

#### 3.1.3 AvRoom服务

获取AV房间号。


### 3.2 model 

数据层。

### 3.3 client-data 

客户端数据对象层，主要用于接收和返回给客户端的数据对象。

### 3.4 lib 

包括数据库和日志等库。

### 3.5 deps 

依赖库和依赖程序和文件，主要是其他项目或者SDK，比如腾讯云COS SDK。

### 3.6 cron 
后台定时任务。清理90秒没有发心跳包的直播记录。可以crontab定时执行。

## 4. 再次强调
 
 * sig目录其他用户一定要有读写可执行权限
 * deps/bin/tls_licence_tools签名程序一定可执行权限
 * deps/bin/linksig程序一定可执行权限
 * 上传需要支持的SDKAppID对应的公私钥到deps/keys/[SDKAppID]目录