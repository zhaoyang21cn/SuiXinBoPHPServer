<?php
/**
 * DB配置
 */
 class DBConfig
{
    const HOST = '{{.Your_Mysql_Host}}';//数据库主机
    const PORT = '{{.Your_Mysql_Port}}';//数据库端口
    const USER = '{{.Your_Mysql_User}}';//数据库用户
    const PASSWORD = '{{.Your_Mysql_Password}}';//数据库密码
    const DATABASE = '{{.Your_Mysql_Database}}';//数据库DB名
    const CHARSET = 'utf8mb4';
}
?>
