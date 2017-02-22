<?php
/**
 * DB辅助类
 * Date: 2016/4/20
 */

require 'DBConfig.php';

class DB
{
    public static function getPDOHandler()
    {
        $host = DBConfig::HOST;
        $port = DBConfig::PORT;
        $dbName = DBConfig::DATABASE;
        $user = DBConfig::USER;
        $password = DBConfig::PASSWORD;
        $charset = DBConfig::CHARSET;
        $dbh = null;
        try
        {
            $dbh = new PDO("mysql:host={$host};port={$port};dbname={$dbName};charset={$charset};", $user, $password);
            $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbh->exec("set names " . $charset);
        }
        catch (PDOException $e)
        {
            // die($e->getMessage());
            return null;
        }
        return $dbh;
    }
}


