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
-- 版本1.0的直播记录表的结构 `t_live_record`
-- 说明：如果不使用版本1.0的接口，那么t_live_record表可以不创建

CREATE TABLE IF NOT EXISTS `t_live_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面URL',
  `host_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播UID',
  `host_avatar` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播头像',
  `host_username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播用户名',
  `longitude` double NOT NULL DEFAULT 0 COMMENT '经度',
  `latitude` double NOT NULL DEFAULT 0 COMMENT '纬度',
  `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '地址',
  `av_room_id` int(11) NOT NULL DEFAULT 0 COMMENT 'av房间ID',
  `chat_room_id` varchar(50) NOT NULL COMMENT '聊天室ID',
  `admire_count` int(11) NOT NULL DEFAULT 0 COMMENT '点赞人数',
  `watch_count` int(11) NOT NULL DEFAULT 0 COMMENT '观看人数',
  `time_span` int(11) NOT NULL DEFAULT 0 COMMENT '直播时长',
  `create_time` datetime COMMENT '创建日期',
  `modify_time` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `appid` int(11) NOT NULL DEFAULT 0 COMMENT 'appid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_host_uid` (`host_uid`),
  KEY `idx_modify_time` (`modify_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='直播记录表' AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- 版本1.0 表的结构 `t_user_av_room`
--

CREATE TABLE IF NOT EXISTS `t_user_av_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'av房间ID',
  `uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户uid',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=10001 ;

-- --------------------------------------------------------

--
-- 版本2.0的直播记录表的结构 `t_new_live_record`
-- 说明：相对版本1.0的有字段的删减

CREATE TABLE IF NOT EXISTS `t_new_live_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `create_time` datetime COMMENT '创建日期',
  `modify_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `appid` int(11) NOT NULL DEFAULT 0 COMMENT 'appid',
  `title` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',
  `cover` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '封面URL',
  `host_uid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主播UID',
  `av_room_id` int(11) NOT NULL DEFAULT 0 COMMENT 'av房间ID',
  `chat_room_id` varchar(50) NOT NULL COMMENT '聊天室ID',
  `room_type`    varchar(30) NOT NULL DEFAULT 'live',
  `admire_count` int(11) NOT NULL DEFAULT 0 COMMENT '点赞人数',
  `longitude` double NOT NULL DEFAULT 0 COMMENT '经度',
  `latitude` double NOT NULL DEFAULT 0 COMMENT '纬度',
  `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '地址',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_host_uid` (`host_uid`),
  KEY `idx_modify_time` (`modify_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='新版直播记录表' AUTO_INCREMENT=14 ;

--
-- 版本2.1 增加字段 video_type,device
--

alter table `t_new_live_record` add column `video_type` tinyint not null default 0 after `room_type`;
alter table `t_new_live_record` add column `device` tinyint not null default 0 after `video_type`;
alter table `t_new_live_record` add column `play_url1` varchar(128) after `admire_count`;
alter table `t_new_live_record` add column `play_url2` varchar(128) after `play_url1`;
alter table `t_new_live_record` add column `play_url3` varchar(128) after `play_url2`;
-- --------------------------------------------------------

--
-- 版本2.0 账号管理表的结构 `t_account`
--

CREATE TABLE IF NOT EXISTS `t_account` (
 `uid`                varchar(50)   NOT  NULL COMMENT '用户名',          
 `pwd`                varchar(50)   NOT  NULL  COMMENT '用户密码',         
 `token`              varchar(50)   DEFAULT NULL COMMENT '用户token',           
 `state`              tinyint(1)    NOT  NULL DEFAULT  0   COMMENT '登录状态',            
 `user_sig`           varchar(512)  DEFAULT NULL COMMENT 'sig',        
 `register_time`      int(11)       NOT  NULL DEFAULT  0   COMMENT '注册时间戳',             
 `login_time`         int(11)       NOT  NULL DEFAULT  0   COMMENT '登录时间戳',            
 `logout_time`        int(11)       NOT  NULL DEFAULT  0   COMMENT '退出时间戳',           
 `last_request_time`  int(11)       NOT  NULL DEFAULT  0   COMMENT '最新请求时间戳',          
  PRIMARY KEY (`uid`)
);

-- --------------------------------------------------------

--
-- 版本2.0 创建房间表的结构 `t_av_room`
--

CREATE TABLE IF NOT EXISTS `t_av_room` (
 `id`                int(11)      NOT  NULL  AUTO_INCREMENT  COMMENT '房间ID',
 `uid`               varchar(50)  NOT  NULL  COMMENT '房间主播名',                  
 `last_update_time`  int(11)      NOT  NULL DEFAULT  0  COMMENT '心跳时间戳',                       
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
)AUTO_INCREMENT=10001;

-- --------------------------------------------------------

--
-- 版本2.0 互动成员表的结构 `t_interact_av_room`
--

CREATE TABLE IF NOT EXISTS `t_interact_av_room` (
 `uid`          varchar(50)  NOT  NULL  COMMENT '成员id',          
 `av_room_id`   int(11)      NOT  NULL DEFAULT  0  COMMENT '成员所在房间ID',            
 `status`       varchar(20)  NOT  NULL DEFAULT  'off' COMMENT '成员在房间的麦状态',           
 `modify_time`  int(11)      NOT  NULL DEFAULT  0   COMMENT '成员心跳时间戳',           
 `role`         int(11)      NOT  NULL DEFAULT  0   COMMENT '成员角色',                              
  PRIMARY KEY (`uid`)
);

-- --------------------------------------------------------

--
-- 版本2.0 互动成员表的结构 `t_video_record`
--

CREATE TABLE IF NOT EXISTS `t_video_record` (
 `id`           int(11)       NOT  NULL   AUTO_INCREMENT  COMMENT '视频id',
 `uid`          varchar(50)   NOT  NULL   DEFAULT ''  COMMENT '视频的拥有者',                
 `video_id`     varchar(50)   NOT  NULL   DEFAULT ''  COMMENT '视频id',                            
 `play_url`     varchar(128)  NOT  NULL   DEFAULT ''  COMMENT '视频url',                            
 `create_time`  int(11)       NOT  NULL   DEFAULT  0  COMMENT '视频创建时间戳',                                     
  PRIMARY KEY (`id`)
)AUTO_INCREMENT=10001;

-- --------------------------------------------------------

-- 录制推流 新增字段
alter table `t_av_room` add column `aux_md5` varchar(128) comment 'groupid_userid_aux' after `uid`;
alter table `t_av_room` add column `main_md5` varchar(128) comment 'groupid_userid_main' after `aux_md5`;

alter table `t_video_record` add column cover varchar(100) after `uid`;
alter table `t_video_record` add column room_num int(11) comment '房间号' after `uid`;
alter table `t_video_record` add column file_name varchar(100) comment '视频名' after `cover`;
alter table `t_video_record` add column start_time int(11)  not null default 0  comment '录制时间' after `video_id`;
alter table `t_video_record` add column end_time int(11)  not null default 0  comment '录制时间' after `start_time`;
