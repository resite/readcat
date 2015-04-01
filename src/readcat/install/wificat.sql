/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50524
Source Host           : localhost:3306
Source Database       : wificat

Target Server Type    : MYSQL
Target Server Version : 50524
File Encoding         : 65001

Date: 2014-11-09 12:06:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for account
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `user_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `freeze_amount` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for account_log
-- ----------------------------
DROP TABLE IF EXISTS `account_log`;
CREATE TABLE `account_log` (
  `account_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `trade_no` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `account_amount` decimal(10,2) NOT NULL,
  `freeze_amount` decimal(10,2) unsigned NOT NULL,
  `type_id` tinyint(4) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for account_teller
-- ----------------------------
DROP TABLE IF EXISTS `account_teller`;
CREATE TABLE `account_teller` (
  `account_teller_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `gateway_id` int(10) unsigned NOT NULL,
  `trade_no` varchar(255) NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `type_id` tinyint(1) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `verify_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`account_teller_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ads
-- ----------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads` (
  `ads_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `ads_name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `media_url` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `ads_page` tinyint(1) unsigned NOT NULL,
  `start_date` int(10) unsigned NOT NULL,
  `start_time` tinyint(1) unsigned NOT NULL,
  `end_date` int(10) unsigned NOT NULL,
  `end_time` tinyint(1) unsigned NOT NULL,
  `region_ids` varchar(255) NOT NULL,
  `region_field` varchar(20) NOT NULL,
  `industry_ids` varchar(255) NOT NULL,
  `industry_field` varchar(20) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `verify_time` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL,
  `price` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY (`ads_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ads_account
-- ----------------------------
DROP TABLE IF EXISTS `ads_account`;
CREATE TABLE `ads_account` (
  `ads_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) unsigned NOT NULL,
  `cost` decimal(10,2) unsigned NOT NULL,
  `total_cost` decimal(10,2) unsigned NOT NULL,
  PRIMARY KEY (`ads_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for ads_log
-- ----------------------------
DROP TABLE IF EXISTS `ads_log`;
CREATE TABLE `ads_log` (
  `ads_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ads_user_id` int(10) unsigned NOT NULL,
  `shop_user_id` int(10) unsigned NOT NULL,
  `shop_id` int(10) unsigned NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `router_id` int(10) unsigned NOT NULL,
  `ads_id` int(10) unsigned NOT NULL,
  `ads_title` varchar(255) NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `region_name` varchar(255) NOT NULL,
  `industry_id` int(10) unsigned NOT NULL,
  `industry_name` varchar(255) NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ads_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for article
-- ----------------------------
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `article_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for article_cate
-- ----------------------------
DROP TABLE IF EXISTS `article_cate`;
CREATE TABLE `article_cate` (
  `article_cate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY (`article_cate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for bank
-- ----------------------------
DROP TABLE IF EXISTS `bank`;
CREATE TABLE `bank` (
  `bank_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `account` varchar(100) NOT NULL COMMENT '账号',
  `bank_name` varchar(50) NOT NULL COMMENT '所属银行',
  `branch` varchar(100) NOT NULL COMMENT '支行',
  `province` varchar(100) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(100) NOT NULL DEFAULT '' COMMENT '区',
  `add_time` int(10) unsigned NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`bank_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户的RMB提现账号，1个用户可以对应多个银行账号';

-- ----------------------------
-- Table structure for industry
-- ----------------------------
DROP TABLE IF EXISTS `industry`;
CREATE TABLE `industry` (
  `industry_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `industry_name` varchar(255) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`industry_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for notice
-- ----------------------------
DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `notice_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `add_time` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`notice_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for payment
-- ----------------------------
DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `payment_id` varchar(50) NOT NULL,
  `payment_name` varchar(255) NOT NULL,
  `payment_desc` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `is_online` tinyint(4) NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  `sort_order` tinyint(4) NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for region
-- ----------------------------
DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
  `region_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `region_name` varchar(255) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `sort_order` int(10) unsigned NOT NULL,
  PRIMARY KEY (`region_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for router
-- ----------------------------
DROP TABLE IF EXISTS `router`;
CREATE TABLE `router` (
  `router_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mac` char(12) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `shop_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`router_id`),
  UNIQUE KEY `mac` (`mac`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for router_log
-- ----------------------------
DROP TABLE IF EXISTS `router_log`;
CREATE TABLE `router_log` (
  `router_log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `router_ip` varchar(16) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `shop_id` int(10) unsigned NOT NULL,
  `mac` char(12) NOT NULL,
  `status` tinyint(1) NOT NULL,
  `verify_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`router_log_id`),
  KEY `router_ip_status` (`router_ip`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for session
-- ----------------------------
DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `session_id` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `session_expire` int(10) unsigned NOT NULL,
  `session_data` blob NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for shop
-- ----------------------------
DROP TABLE IF EXISTS `shop`;
CREATE TABLE `shop` (
  `shop_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `gps` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `industry_id0` int(10) unsigned NOT NULL,
  `industry_id1` int(10) unsigned NOT NULL,
  `region_id1` int(10) unsigned NOT NULL,
  `region_id2` int(10) unsigned NOT NULL,
  `region_id3` int(10) unsigned NOT NULL,
  `region_id4` int(10) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for sys_config
-- ----------------------------
DROP TABLE IF EXISTS `sys_config`;
CREATE TABLE `sys_config` (
  `sys_config_id` varchar(50) NOT NULL,
  `v` varchar(255) NOT NULL,
  `sys_config_desc` varchar(1000) NOT NULL,
  PRIMARY KEY (`sys_config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for userinfo
-- ----------------------------
DROP TABLE IF EXISTS `userinfo`;
CREATE TABLE `userinfo` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `realname` varchar(255) NOT NULL,
  `id_card` char(18) NOT NULL,
  `card_img1` varchar(255) NOT NULL,
  `card_img2` varchar(255) NOT NULL,
  `real_status` tinyint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `mob_phone` int(11) unsigned NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `type_id` tinyint(3) unsigned NOT NULL,
  `last_login_time` int(10) unsigned NOT NULL,
  `last_login_ip` varchar(16) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
