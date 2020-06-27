/*
 Navicat Premium Data Transfer

 Source Server         : docker_mysql_127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 80018
 Source Host           : 127.0.0.1
 Source Database       : project-swoft-swoftFramework2

 Target Server Type    : MySQL
 Target Server Version : 80018
 File Encoding         : utf-8

 Date: 06/28/2020 00:01:53 AM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `count`
-- ----------------------------
DROP TABLE IF EXISTS `count`;
CREATE TABLE `count` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'primary',
  `user_id` int(11) NOT NULL DEFAULT '0' COMMENT 'user table primary',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT 'create time',
  `update_time` timestamp NOT NULL COMMENT 'update timestamp',
  PRIMARY KEY (`id`),
  KEY `count_user_id_create_time_index` (`user_id`,`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='user count comment ...';

-- ----------------------------
--  Table structure for `desc`
-- ----------------------------
DROP TABLE IF EXISTS `desc`;
CREATE TABLE `desc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `desc` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='user desc';

-- ----------------------------
--  Table structure for `migration`
-- ----------------------------
DROP TABLE IF EXISTS `migration`;
CREATE TABLE `migration` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `time` bigint(20) NOT NULL,
  `is_rollback` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ----------------------------
--  Records of `migration`
-- ----------------------------
BEGIN;
INSERT INTO `migration` VALUES ('1', 'App\\Migration\\User', '20190630164222', '2'), ('2', 'Database\\Migration\\Count', '20190913232743', '2'), ('3', 'Database\\Migration\\Desc', '20190913234407', '2');
COMMIT;

-- ----------------------------
--  Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `age` int(11) NOT NULL DEFAULT '0',
  `password` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `user_desc` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `test_json` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
