-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 2016-05-23 10:01:00
-- 服务器版本： 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `sxb_db`
--
CREATE DATABASE IF NOT EXISTS `sxb_db` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `sxb_db`;

-- --------------------------------------------------------

--
-- 表的结构 `t_live_record`
--

CREATE TABLE IF NOT EXISTS `t_live_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面URL',
  `host_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播UID',
  `host_avatar` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播头像',
  `host_username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播用户名',
  `longitude` double NOT NULL COMMENT '经度',
  `latitude` double NOT NULL COMMENT '纬度',
  `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '地址',
  `av_room_id` int(11) NOT NULL,
  `chat_room_id` int(11) NOT NULL COMMENT '聊天室ID',
  `admire_count` int(11) NOT NULL DEFAULT '0' COMMENT '点赞人数',
  `watch_count` int(11) NOT NULL DEFAULT '0' COMMENT '观看人数',
  `time_span` int(11) NOT NULL DEFAULT '0' COMMENT '直播时长',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建日期',
  `modify_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_host_uid` (`host_uid`),
  KEY `idx_modify_time` (`modify_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='直播记录表' AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- 表的结构 `t_user_av_room`
--

CREATE TABLE IF NOT EXISTS `t_user_av_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'av房间ID',
  `uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户uid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10001 ;

--
-- 转存表中的数据 `t_user_av_room`
--

INSERT INTO `t_user_av_room` (`id`, `uid`) VALUES
(10000, 'user1002');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
