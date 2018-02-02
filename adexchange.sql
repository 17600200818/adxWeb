-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 年 01 月 30 日 06:34
-- 服务器版本: 5.6.20
-- PHP 版本: 5.6.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `adexchange`
--

-- --------------------------------------------------------

--
-- 表的结构 `advertiser`
--

CREATE TABLE IF NOT EXISTS `advertiser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `idBuyer` int(11) NOT NULL,
  `idBuyerAdvertiser` varchar(20) NOT NULL COMMENT '买方的广告主id',
  `category1` int(11) NOT NULL DEFAULT '0',
  `category2` int(11) NOT NULL DEFAULT '0',
  `siteName` varchar(45) DEFAULT NULL,
  `domain` varchar(45) DEFAULT NULL,
  `address` varchar(100) DEFAULT NULL,
  `zip` varchar(10) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `linkman` varchar(12) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：待审核；2：审核通过；3：审核不通过；4：停用',
  `remark` varchar(200) DEFAULT NULL COMMENT '备注',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idAdvertiser` (`idBuyer`,`idBuyerAdvertiser`),
  KEY `idBuyer_idx` (`idBuyer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告主信息' AUTO_INCREMENT=1111135 ;

--
-- 触发器 `advertiser`
--
DROP TRIGGER IF EXISTS `advertiser_INSERT`;
DELIMITER //
CREATE TRIGGER `advertiser_INSERT` AFTER INSERT ON `advertiser`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser select * from adexchange.advertiser where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `advertiser_UPDATE`;
DELIMITER //
CREATE TRIGGER `advertiser_UPDATE` AFTER UPDATE ON `advertiser`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser select * from adexchange.advertiser where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `advertiser_audit`
--

CREATE TABLE IF NOT EXISTS `advertiser_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idBuyer` int(11) NOT NULL,
  `idAdvertiser` int(11) NOT NULL,
  `idSeller` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：待上传；2：待审核；3：审核通过；4：审核不通过；5：停用;6：待重新上传',
  `allow` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：允许；2：不允许',
  `remark` varchar(200) DEFAULT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL,
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idSeller_idx` (`idSeller`),
  KEY `idAdvertiser_idx` (`idAdvertiser`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告主资质在媒体方的审核状态信息表' AUTO_INCREMENT=674439 ;

--
-- 触发器 `advertiser_audit`
--
DROP TRIGGER IF EXISTS `advertiser_audit_INSERT`;
DELIMITER //
CREATE TRIGGER `advertiser_audit_INSERT` AFTER INSERT ON `advertiser_audit`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser_audit select * from adexchange.advertiser_audit where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `advertiser_audit_UPDATE`;
DELIMITER //
CREATE TRIGGER `advertiser_audit_UPDATE` AFTER UPDATE ON `advertiser_audit`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser_audit select * from adexchange.advertiser_audit where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `advertiser_file`
--

CREATE TABLE IF NOT EXISTS `advertiser_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idBuyer` int(11) NOT NULL,
  `idAdvertiser` int(11) NOT NULL,
  `code` varchar(45) NOT NULL COMMENT '资质文件编号/登记号',
  `name` varchar(45) DEFAULT NULL COMMENT '资质主体名称',
  `filePath` varchar(200) DEFAULT NULL COMMENT '资质文件的相对保存路径',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：待审核；2：审核通过；3：审核不通过；4：停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idAdvertiser_idx` (`idAdvertiser`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告主资质文件信息' AUTO_INCREMENT=3 ;

--
-- 触发器 `advertiser_file`
--
DROP TRIGGER IF EXISTS `advertiser_file_INSERT`;
DELIMITER //
CREATE TRIGGER `advertiser_file_INSERT` AFTER INSERT ON `advertiser_file`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser_file select * from adexchange.advertiser_file where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `advertiser_file_UPDATE`;
DELIMITER //
CREATE TRIGGER `advertiser_file_UPDATE` AFTER UPDATE ON `advertiser_file`
 FOR EACH ROW BEGIN
insert into cnr_log.advertiser_file select * from adexchange.advertiser_file where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `buyer`
--

CREATE TABLE IF NOT EXISTS `buyer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL COMMENT '登陆的用户名',
  `password` varchar(32) NOT NULL,
  `idRole` int(11) NOT NULL COMMENT '角色id',
  `linkman` varchar(20) DEFAULT NULL COMMENT '联系人',
  `mobileTel` varchar(20) NOT NULL,
  `buyType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：rtb；2：adn',
  `creativeAuditType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:先审后投；2：先投后审',
  `allowAdm` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1:支持动态素材;2:不支持',
  `company` varchar(100) DEFAULT NULL COMMENT '公司名称',
  `address` varchar(100) DEFAULT NULL COMMENT '公司地址',
  `zip` varchar(10) DEFAULT NULL COMMENT '邮编',
  `gainType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '盈利模式：1：成交价上浮',
  `gainRate` tinyint(4) NOT NULL DEFAULT '0' COMMENT '盈利比例',
  `lastLoginIpAddr` char(20) DEFAULT NULL COMMENT '最后登录ip地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:待审核;2:正常;3:审核不通过,4:停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='买方账户信息' AUTO_INCREMENT=100186 ;

--
-- 触发器 `buyer`
--
DROP TRIGGER IF EXISTS `buyer_INSERT`;
DELIMITER //
CREATE TRIGGER `buyer_INSERT` AFTER INSERT ON `buyer`
 FOR EACH ROW BEGIN
insert into cnr_log.buyer select * from adexchange.buyer where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `buyer_UPDATE`;
DELIMITER //
CREATE TRIGGER `buyer_UPDATE` AFTER UPDATE ON `buyer`
 FOR EACH ROW BEGIN
insert into cnr_log.buyer select * from adexchange.buyer where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `buyer_param`
--

CREATE TABLE IF NOT EXISTS `buyer_param` (
  `id` int(11) NOT NULL,
  `token` varchar(200) DEFAULT NULL COMMENT '上传素材的token',
  `ipList` text COMMENT '买方上传素材的服务器ip列表',
  `priceKey` varchar(200) DEFAULT NULL COMMENT '价格解密的秘钥',
  `adxQps` int(11) NOT NULL DEFAULT '100' COMMENT 'adx设置的客户的qps',
  `buyerQps` int(11) NOT NULL DEFAULT '100',
  `bidUrl` varchar(200) NOT NULL COMMENT '请求地址',
  `cookieMappingUrl` varchar(200) DEFAULT NULL,
  `winNoticeUrl` varchar(200) DEFAULT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='买方账户信息';

--
-- 触发器 `buyer_param`
--
DROP TRIGGER IF EXISTS `buyer_param_INSERT`;
DELIMITER //
CREATE TRIGGER `buyer_param_INSERT` AFTER INSERT ON `buyer_param`
 FOR EACH ROW BEGIN
insert into cnr_log.buyer_param select * from adexchange.buyer_param where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `buyer_param_UPDATE`;
DELIMITER //
CREATE TRIGGER `buyer_param_UPDATE` AFTER UPDATE ON `buyer_param`
 FOR EACH ROW BEGIN
insert into cnr_log.buyer_param select * from adexchange.buyer_param where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `creative`
--

CREATE TABLE IF NOT EXISTS `creative` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idBuyer` int(11) NOT NULL,
  `buyerAdvertiserId` int(11) NOT NULL DEFAULT '0' COMMENT '买方的广告主id',
  `advertiserId` int(11) NOT NULL COMMENT '广告主id',
  `url` varchar(200) NOT NULL COMMENT '买方的文件url路径',
  `filePath` varchar(200) DEFAULT NULL COMMENT '缓存到本地的路径',
  `fileExt` tinyint(4) NOT NULL DEFAULT '0' COMMENT '文件扩展名id',
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `category1` int(11) NOT NULL DEFAULT '0' COMMENT '所属一级行业分类',
  `category2` int(11) NOT NULL DEFAULT '0' COMMENT '所属二级行业分类',
  `fileSize` int(11) NOT NULL COMMENT '文件大小(K)',
  `duration` int(11) NOT NULL DEFAULT '0' COMMENT '视频文件的播放时长',
  `buyerCrid` varchar(50) NOT NULL COMMENT '买方的素材id',
  `actionType` tinyint(4) NOT NULL DEFAULT '0',
  `clickUrl` text NOT NULL COMMENT '点击跳转地址',
  `loadingPage` text COMMENT '点击后的落地页',
  `imptrackers` text COMMENT '曝光监测地址',
  `clktrackers` text COMMENT '点击监测地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：待审核；2：审核通过；3：审核不通过',
  `expirationDate` date DEFAULT NULL,
  `adCode` text,
  `md5Id` varchar(32) DEFAULT NULL COMMENT '旧库的md5 id',
  `remark` varchar(200) DEFAULT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `buyerCrid` (`idBuyer`,`advertiserId`,`buyerCrid`),
  KEY `idAdvertiser_idx` (`advertiserId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='素材信息表' AUTO_INCREMENT=300965 ;

--
-- 触发器 `creative`
--
DROP TRIGGER IF EXISTS `creative_INSERT`;
DELIMITER //
CREATE TRIGGER `creative_INSERT` AFTER INSERT ON `creative`
 FOR EACH ROW BEGIN
insert into cnr_log.creative select * from adexchange.creative where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `creative_UPDATE`;
DELIMITER //
CREATE TRIGGER `creative_UPDATE` AFTER UPDATE ON `creative`
 FOR EACH ROW BEGIN
insert into cnr_log.creative select * from adexchange.creative where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `creative_audit`
--

CREATE TABLE IF NOT EXISTS `creative_audit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idBuyer` int(11) NOT NULL,
  `buyerAdvertiserId` int(11) NOT NULL DEFAULT '0',
  `advertiserId` int(11) NOT NULL DEFAULT '0',
  `crid` int(11) NOT NULL COMMENT '对应素材id',
  `idSeller` int(11) NOT NULL,
  `mediaCrid` varchar(50) DEFAULT NULL COMMENT '媒体的素材id',
  `url` varchar(200) DEFAULT NULL COMMENT '媒体的素材url地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：待上传；2：待审核；3：审核通过；4：审核不通过；5：停用；6：待重新上传',
  `errorId` int(11) NOT NULL DEFAULT '0' COMMENT '媒体审核返回的错误id',
  `allow` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：允许；2：不允许',
  `remark` varchar(200) DEFAULT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='素材在媒体方的审核状态信息表' AUTO_INCREMENT=366299 ;

--
-- 触发器 `creative_audit`
--
DROP TRIGGER IF EXISTS `creative_audit_INSERT`;
DELIMITER //
CREATE TRIGGER `creative_audit_INSERT` AFTER INSERT ON `creative_audit`
 FOR EACH ROW BEGIN
insert into cnr_log.creative_audit select * from adexchange.creative_audit where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `creative_audit_UPDATE`;
DELIMITER //
CREATE TRIGGER `creative_audit_UPDATE` AFTER UPDATE ON `creative_audit`
 FOR EACH ROW BEGIN
insert into cnr_log.creative_audit select * from adexchange.creative_audit where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `media`
--

CREATE TABLE IF NOT EXISTS `media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '网站名称或者app名称',
  `sellerId` int(11) NOT NULL DEFAULT '0' COMMENT '媒体账户id',
  `sellerSonId` int(11) NOT NULL DEFAULT '0' COMMENT '媒体子账户id',
  `mediaType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '媒体类型（1：web；2：app）',
  `category1` int(11) NOT NULL DEFAULT '0' COMMENT '媒体一级分类id',
  `category2` int(11) NOT NULL DEFAULT '0' COMMENT '媒体二级分类id',
  `domain` varchar(100) DEFAULT NULL COMMENT 'web端域名',
  `exclude_ad_url` text,
  `exclude_ad_category` text,
  `sellerAppId` varchar(200) DEFAULT NULL COMMENT '卖方app id',
  `storeurl` text COMMENT 'app store 地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常；2：停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idSeller_idx` (`sellerSonId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='媒体信息表' AUTO_INCREMENT=8910 ;

--
-- 触发器 `media`
--
DROP TRIGGER IF EXISTS `media_INSERT`;
DELIMITER //
CREATE TRIGGER `media_INSERT` AFTER INSERT ON `media`
 FOR EACH ROW BEGIN
insert into cnr_log.media select * from adexchange.media where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `media_UPDATE`;
DELIMITER //
CREATE TRIGGER `media_UPDATE` AFTER UPDATE ON `media`
 FOR EACH ROW BEGIN
insert into cnr_log.media select * from adexchange.media where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `monitor_log22`
--

CREATE TABLE IF NOT EXISTS `monitor_log22` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dspPrice` int(11) NOT NULL DEFAULT '0',
  `dataType` int(11) DEFAULT NULL,
  `bidid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `impid` tinyint(4) DEFAULT NULL,
  `buyerId` int(11) DEFAULT NULL,
  `crid` int(11) DEFAULT NULL,
  `firstPrice` int(11) DEFAULT NULL,
  `secondPrice` int(11) DEFAULT NULL,
  `bidPrice` int(11) DEFAULT NULL,
  `sellerBidPrice` int(11) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL,
  `placeId` int(11) DEFAULT NULL,
  `w` int(11) DEFAULT NULL,
  `h` int(11) DEFAULT NULL,
  `ip` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ts` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `nurl` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ld` text CHARACTER SET utf8,
  `clientIp` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ctime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `bidid` (`bidid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6564 ;

-- --------------------------------------------------------

--
-- 表的结构 `monitor_log_2017_08_09`
--

CREATE TABLE IF NOT EXISTS `monitor_log_2017_08_09` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dspPrice` int(11) NOT NULL DEFAULT '0',
  `dataType` int(11) DEFAULT NULL,
  `bidid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `impid` tinyint(4) DEFAULT NULL,
  `buyerId` int(11) DEFAULT NULL,
  `crid` int(11) DEFAULT NULL,
  `firstPrice` int(11) DEFAULT NULL,
  `secondPrice` int(11) DEFAULT NULL,
  `bidPrice` int(11) DEFAULT NULL,
  `sellerBidPrice` int(11) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL,
  `placeId` int(11) DEFAULT NULL,
  `w` int(11) DEFAULT NULL,
  `h` int(11) DEFAULT NULL,
  `ip` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ts` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `nurl` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ld` text CHARACTER SET utf8,
  `clientIp` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ctime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `bidid` (`bidid`),
  KEY `placeId` (`placeId`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=387123 ;

-- --------------------------------------------------------

--
-- 表的结构 `monitor_log_2017_08_20`
--

CREATE TABLE IF NOT EXISTS `monitor_log_2017_08_20` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dspPrice` int(11) NOT NULL DEFAULT '0',
  `dataType` int(11) DEFAULT NULL,
  `bidid` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `impid` tinyint(4) DEFAULT NULL,
  `buyerId` int(11) DEFAULT NULL,
  `crid` int(11) DEFAULT NULL,
  `firstPrice` int(11) DEFAULT NULL,
  `secondPrice` int(11) DEFAULT NULL,
  `bidPrice` int(11) DEFAULT NULL,
  `sellerBidPrice` int(11) DEFAULT NULL,
  `sellerId` int(11) DEFAULT NULL,
  `placeId` int(11) DEFAULT NULL,
  `w` int(11) DEFAULT NULL,
  `h` int(11) DEFAULT NULL,
  `ip` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ts` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  `nurl` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `ld` text CHARACTER SET utf8,
  `clientIp` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `ctime` timestamp NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `bidid` (`bidid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=127847 ;

-- --------------------------------------------------------

--
-- 表的结构 `place`
--

CREATE TABLE IF NOT EXISTS `place` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sellerId` int(11) NOT NULL DEFAULT '0' COMMENT '媒体账户id',
  `sellerSonId` int(11) NOT NULL DEFAULT '0' COMMENT '媒体子账户id',
  `mediaId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '广告位名称',
  `md5IdType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '广告位md5 id的类型',
  `md5Id` varchar(32) DEFAULT NULL COMMENT '广告位md5 id',
  `auditType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '素材审核类型（1：先审后投；2：先投后审）',
  `bidfloor` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT '底价',
  `deviceType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '设备类型（1：pc；2：手机；3：平板电脑；4：电视）',
  `osType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:未知；1：iOs；2：android',
  `screenLocation` tinyint(4) NOT NULL DEFAULT '0' COMMENT '屏幕位置，第n屏',
  `mediaPlaceId` varchar(100) DEFAULT NULL COMMENT '对应媒体的广告位id',
  `placeType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '广告位类型（1：banner；2：video；3：native）',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '宽度',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '高度',
  `fileExt` varchar(200) DEFAULT NULL COMMENT '''允许的素材文件类型''',
  `mimes` varchar(200) DEFAULT NULL COMMENT '允许的素材类型（banner的创意类型, 1 图片, 2 Flash, 3 HTML；video：1 flv, 2 mp4）',
  `instl` tinyint(4) NOT NULL COMMENT '展现形式（1:banner;2:video;3:背投;4:视频暂停;5:弹窗;6:视频悬浮;7:开屏;8:插屏;9:应用墙;10:信息流）',
  `code` text COMMENT '打底代码',
  `linearity` tinyint(4) NOT NULL DEFAULT '0' COMMENT '视频：1：线性；2：非线性',
  `minduration` int(11) NOT NULL DEFAULT '0' COMMENT '视频，允许的最小播放时长',
  `maxduration` int(11) NOT NULL DEFAULT '0' COMMENT '视频，允许的最大播放时长',
  `pos` tinyint(4) NOT NULL DEFAULT '0' COMMENT '贴片位置，1 前贴，2 中贴，3 暂停，4 后贴 , 0未知',
  `nativeAssets` text COMMENT '原生广告的元素',
  `areabidfloor` text COMMENT '区域底价',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常；2：停用',
  `gainType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '盈利模式',
  `gainRate` int(11) NOT NULL DEFAULT '0' COMMENT '盈利比例',
  `remark` text COMMENT '备注',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idMedia_idx` (`mediaId`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告位信息表' AUTO_INCREMENT=118375 ;

--
-- 触发器 `place`
--
DROP TRIGGER IF EXISTS `place_INSERT`;
DELIMITER //
CREATE TRIGGER `place_INSERT` AFTER INSERT ON `place`
 FOR EACH ROW BEGIN
insert into cnr_log.place select * from adexchange.place where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `place_UPDATE`;
DELIMITER //
CREATE TRIGGER `place_UPDATE` AFTER UPDATE ON `place`
 FOR EACH ROW BEGIN
insert into cnr_log.place select * from adexchange.place where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `place_day_2017_08`
--

CREATE TABLE IF NOT EXISTS `place_day_2017_08` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `sellerId` int(11) NOT NULL COMMENT '卖方账户id',
  `mediaId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `reportDate` date NOT NULL COMMENT '日期',
  `view` int(11) NOT NULL DEFAULT '0' COMMENT '媒体请求数',
  `request` int(11) NOT NULL DEFAULT '0' COMMENT '转发请求数',
  `requestOk` int(11) NOT NULL DEFAULT '0' COMMENT '转发请求成功数',
  `response` int(11) NOT NULL DEFAULT '0' COMMENT '回复数',
  `bid` int(11) NOT NULL DEFAULT '0' COMMENT '参与竞价数',
  `bidOk` int(11) NOT NULL DEFAULT '0',
  `play` int(11) NOT NULL DEFAULT '0' COMMENT '曝光数',
  `playIp` int(11) NOT NULL DEFAULT '0' COMMENT '曝光ip数',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '点击数',
  `clickIp` int(11) NOT NULL DEFAULT '0' COMMENT '点击ip数',
  `spend` bigint(20) NOT NULL DEFAULT '0' COMMENT '花费',
  `sellerPlay` int(11) NOT NULL DEFAULT '0' COMMENT '卖方曝光数',
  `sellerClick` int(11) NOT NULL DEFAULT '0' COMMENT '卖方点击数',
  `sellerSpend` bigint(20) NOT NULL DEFAULT '0' COMMENT '卖方花费',
  `buyerSpend` bigint(20) NOT NULL DEFAULT '0' COMMENT '买方花费',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sellerId` (`sellerId`,`mediaId`,`placeId`,`reportDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='卖方交易总表' AUTO_INCREMENT=22149606 ;

-- --------------------------------------------------------

--
-- 表的结构 `place_day_temp_08`
--

CREATE TABLE IF NOT EXISTS `place_day_temp_08` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `sellerId` int(11) NOT NULL COMMENT '卖方账户id',
  `mediaId` int(11) NOT NULL,
  `placeId` int(11) NOT NULL,
  `play` int(11) NOT NULL DEFAULT '0' COMMENT '曝光数',
  `spend` bigint(20) NOT NULL DEFAULT '0' COMMENT '花费',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sellerId` (`sellerId`,`mediaId`,`placeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='卖方交易总表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `platform`
--

CREATE TABLE IF NOT EXISTS `platform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `remark` varchar(100) DEFAULT NULL,
  `cuid` int(11) NOT NULL COMMENT '创建人id',
  `ctime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `muid` int(11) NOT NULL DEFAULT '0' COMMENT '修改人id',
  `mtime` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='平台的信息表' AUTO_INCREMENT=4 ;

--
-- 触发器 `platform`
--
DROP TRIGGER IF EXISTS `platform_INSERT`;
DELIMITER //
CREATE TRIGGER `platform_INSERT` AFTER INSERT ON `platform`
 FOR EACH ROW BEGIN
insert into cnr_log.platform select * from adexchange.platform where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `platform_UPDATE`;
DELIMITER //
CREATE TRIGGER `platform_UPDATE` AFTER UPDATE ON `platform`
 FOR EACH ROW BEGIN
insert into cnr_log.platform select * from adexchange.platform where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `pmp`
--

CREATE TABLE IF NOT EXISTS `pmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `pmpType` tinyint(4) NOT NULL COMMENT '1：保价保量；2：保价不保量；3：不保价保量；4：不保价不保量',
  `price` decimal(6,2) NOT NULL DEFAULT '0.00',
  `level` tinyint(4) NOT NULL DEFAULT '5' COMMENT '级别（1-10）',
  `saleType` tinyint(4) NOT NULL DEFAULT '3' COMMENT '1：最高价；2：第二出价；3：固定价格',
  `startDate` date NOT NULL COMMENT '开始日期',
  `endDate` date NOT NULL COMMENT '结束日期',
  `hourDirect` text COMMENT '时段定向',
  `deviceDirect` text COMMENT '设备类型定向',
  `areaDirect` text COMMENT '地域定向',
  `instlDirect` text COMMENT '展现形式定向',
  `buyerDirect` text COMMENT '买方定向',
  `sizeDirect` text COMMENT '尺寸定向',
  `sellerDealDirect` text COMMENT '卖方deal定向',
  `placeDirect` text COMMENT '广告位定向',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常；2：停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='私有交易信息表' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `power_item`
--

CREATE TABLE IF NOT EXISTS `power_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlatform` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '上级id',
  `controller` varchar(100) DEFAULT NULL COMMENT '功能模块',
  `action` varchar(100) DEFAULT NULL COMMENT '操作',
  `displayFlag` tinyint(4) NOT NULL DEFAULT '1' COMMENT '菜单显示：1:显示;2:不显示',
  `itemOrder` decimal(10,4) NOT NULL DEFAULT '1000.0000' COMMENT '排序',
  `level` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:正常;2:停用',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idProject` (`idPlatform`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='权限项的信息表' AUTO_INCREMENT=367 ;

--
-- 触发器 `power_item`
--
DROP TRIGGER IF EXISTS `power_item_INSERT`;
DELIMITER //
CREATE TRIGGER `power_item_INSERT` AFTER INSERT ON `power_item`
 FOR EACH ROW BEGIN
insert into cnr_log.power_item select * from adexchange.power_item where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `power_item_UPDATE`;
DELIMITER //
CREATE TRIGGER `power_item_UPDATE` AFTER UPDATE ON `power_item`
 FOR EACH ROW BEGIN
insert into cnr_log.power_item select * from adexchange.power_item where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlatform` int(11) NOT NULL,
  `name` varchar(30) NOT NULL COMMENT '角色名称',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:正常;2:停用',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`idPlatform`,`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='角色的信息表' AUTO_INCREMENT=18 ;

--
-- 触发器 `role`
--
DROP TRIGGER IF EXISTS `role_INSERT`;
DELIMITER //
CREATE TRIGGER `role_INSERT` AFTER INSERT ON `role`
 FOR EACH ROW BEGIN
insert into cnr_log.role select * from adexchange.role where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `role_UPDATE`;
DELIMITER //
CREATE TRIGGER `role_UPDATE` AFTER UPDATE ON `role`
 FOR EACH ROW BEGIN
insert into cnr_log.role select * from adexchange.role where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `role_power_items`
--

CREATE TABLE IF NOT EXISTS `role_power_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idPlatform` int(11) NOT NULL,
  `idRole` int(11) NOT NULL,
  `idPowerItem` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:正常;2:停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idRole` (`idRole`),
  KEY `idPowerItem` (`idRole`),
  KEY `idProject` (`idPlatform`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='角色包含的权限项' AUTO_INCREMENT=1122 ;

--
-- 触发器 `role_power_items`
--
DROP TRIGGER IF EXISTS `role_power_items_INSERT`;
DELIMITER //
CREATE TRIGGER `role_power_items_INSERT` AFTER INSERT ON `role_power_items`
 FOR EACH ROW BEGIN
insert into cnr_log.role_power_items select * from adexchange.role_power_items where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `role_power_items_UPDATE`;
DELIMITER //
CREATE TRIGGER `role_power_items_UPDATE` AFTER UPDATE ON `role_power_items`
 FOR EACH ROW BEGIN
insert into cnr_log.role_power_items select * from adexchange.role_power_items where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `route`
--

CREATE TABLE IF NOT EXISTS `route` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL COMMENT '路由名称',
  `idSeller` int(11) NOT NULL COMMENT '卖方账户id',
  `idMedia` int(11) NOT NULL COMMENT '媒体id',
  `idPlace` int(11) NOT NULL COMMENT '广告位id',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '级别',
  `dspIds` text COMMENT '需要分发的dsp id',
  `adnIds` text COMMENT '需要分发的adn id',
  `gainType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '盈利模式 1：底价上浮；2：成交量扣量',
  `gainRate` int(11) NOT NULL DEFAULT '0' COMMENT '盈利比例',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：正常；2：停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idSeller` (`idSeller`,`idMedia`,`idPlace`,`level`),
  KEY `idSeller_idx` (`idSeller`),
  KEY `idMedia_idx` (`idMedia`),
  KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='路由信息表' AUTO_INCREMENT=15 ;

--
-- 触发器 `route`
--
DROP TRIGGER IF EXISTS `route_INSERT`;
DELIMITER //
CREATE TRIGGER `route_INSERT` AFTER INSERT ON `route`
 FOR EACH ROW BEGIN
insert into cnr_log.route select * from adexchange.route where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `route_UPDATE`;
DELIMITER //
CREATE TRIGGER `route_UPDATE` AFTER UPDATE ON `route`
 FOR EACH ROW BEGIN
insert into cnr_log.route select * from adexchange.route where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `seller`
--

CREATE TABLE IF NOT EXISTS `seller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL COMMENT '登陆的用户名',
  `password` varchar(32) NOT NULL,
  `parentId` int(11) NOT NULL DEFAULT '0' COMMENT '上级代理商id',
  `idRole` int(11) NOT NULL COMMENT '角色id',
  `linkman` varchar(20) DEFAULT NULL COMMENT '联系人',
  `mobileTel` varchar(20) NOT NULL,
  `company` varchar(100) DEFAULT NULL COMMENT '公司名称',
  `isSsp` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:是ssp；2：不是ssp',
  `isAuditCreative` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:审核素材；2：不审核素材',
  `creativeAuditType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:先审后投；2：先投后审',
  `lastLoginIpAddr` char(20) DEFAULT NULL COMMENT '最后登录ip地址',
  `exclude_ad_url` text,
  `exclude_ad_category` text,
  `buyerBlacklist` text COMMENT '买方黑名单列表',
  `buyerWhitelist` text COMMENT '买方白名单列表',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:待审核;2:正常;3:审核不通过,4:停用',
  `gainType` tinyint(4) NOT NULL DEFAULT '0' COMMENT '盈利模式 1：底价上浮；2：成交量扣量',
  `gainRate` int(11) NOT NULL DEFAULT '0' COMMENT '盈利比例（0-100）',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='卖方账户信息' AUTO_INCREMENT=100194 ;

--
-- 触发器 `seller`
--
DROP TRIGGER IF EXISTS `seller_INSERT`;
DELIMITER //
CREATE TRIGGER `seller_INSERT` AFTER INSERT ON `seller`
 FOR EACH ROW BEGIN
insert into cnr_log.seller select * from adexchange.seller where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `seller_UPDATE`;
DELIMITER //
CREATE TRIGGER `seller_UPDATE` AFTER UPDATE ON `seller`
 FOR EACH ROW BEGIN
insert into cnr_log.seller select * from adexchange.seller where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `sys_city`
--

CREATE TABLE IF NOT EXISTS `sys_city` (
  `city_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '城市id',
  `name` varchar(30) DEFAULT NULL COMMENT '城市名称',
  `enName` varchar(100) DEFAULT NULL COMMENT '英文名称',
  `country_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '所属国家id',
  `province_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '所属省份id',
  `provincename` varchar(30) NOT NULL COMMENT '所属省份名称',
  `area_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '所属行政区域id',
  `areaname` varchar(30) NOT NULL,
  `mailcode` varchar(50) DEFAULT NULL COMMENT '邮编',
  `totalnum` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ip数量',
  PRIMARY KEY (`city_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='城市对照表';

-- --------------------------------------------------------

--
-- 表的结构 `sys_industry_category`
--

CREATE TABLE IF NOT EXISTS `sys_industry_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c1` int(11) NOT NULL,
  `n1` varchar(20) NOT NULL,
  `c2` int(11) NOT NULL,
  `n2` varchar(20) NOT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汇选的行业分类' AUTO_INCREMENT=389 ;

--
-- 触发器 `sys_industry_category`
--
DROP TRIGGER IF EXISTS `sys_industry_category_INSERT`;
DELIMITER //
CREATE TRIGGER `sys_industry_category_INSERT` AFTER INSERT ON `sys_industry_category`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_industry_category select * from adexchange.sys_industry_category where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `sys_industry_category_UPDATE`;
DELIMITER //
CREATE TRIGGER `sys_industry_category_UPDATE` AFTER UPDATE ON `sys_industry_category`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_industry_category select * from adexchange.sys_industry_category where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `sys_ipaddress`
--

CREATE TABLE IF NOT EXISTS `sys_ipaddress` (
  `id` varchar(255) DEFAULT NULL,
  `seq_id` varchar(255) DEFAULT NULL,
  `country_id` varchar(255) DEFAULT NULL,
  `area_id` varchar(255) DEFAULT NULL,
  `province_id` varchar(255) DEFAULT NULL,
  `city_id` varchar(255) DEFAULT NULL,
  `start_ip` bigint(12) DEFAULT NULL,
  `end_ip` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `iprecord` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `sys_media_category`
--

CREATE TABLE IF NOT EXISTS `sys_media_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `c1` int(11) NOT NULL,
  `n1` varchar(20) NOT NULL,
  `c2` int(11) NOT NULL,
  `n2` varchar(20) NOT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='汇选的媒体分类' AUTO_INCREMENT=275 ;

--
-- 触发器 `sys_media_category`
--
DROP TRIGGER IF EXISTS `sys_media_category_INSERT`;
DELIMITER //
CREATE TRIGGER `sys_media_category_INSERT` AFTER INSERT ON `sys_media_category`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_media_category select * from adexchange.sys_media_category where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `sys_media_category_UPDATE`;
DELIMITER //
CREATE TRIGGER `sys_media_category_UPDATE` AFTER UPDATE ON `sys_media_category`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_media_category select * from adexchange.sys_media_category where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `sys_place_size`
--

CREATE TABLE IF NOT EXISTS `sys_place_size` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deviceType` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：web端；2：移动端',
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wh` (`deviceType`,`height`,`width`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='广告位尺寸信息表' AUTO_INCREMENT=6 ;

--
-- 触发器 `sys_place_size`
--
DROP TRIGGER IF EXISTS `sys_place_size_INSERT`;
DELIMITER //
CREATE TRIGGER `sys_place_size_INSERT` AFTER INSERT ON `sys_place_size`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_place_size select * from adexchange.sys_place_size where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `sys_place_size_UPDATE`;
DELIMITER //
CREATE TRIGGER `sys_place_size_UPDATE` AFTER UPDATE ON `sys_place_size`
 FOR EACH ROW BEGIN
insert into cnr_log.sys_place_size select * from adexchange.sys_place_size where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `sys_province`
--

CREATE TABLE IF NOT EXISTS `sys_province` (
  `province_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '省份id',
  `name` varchar(30) DEFAULT NULL COMMENT '名称',
  `enName` varchar(100) DEFAULT NULL COMMENT '英文名称',
  `country_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '所属国家',
  `area_id` bigint(15) NOT NULL DEFAULT '0' COMMENT '所属行政区',
  `totalnum` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ip数',
  PRIMARY KEY (`province_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='省份';

-- --------------------------------------------------------

--
-- 表的结构 `temp`
--

CREATE TABLE IF NOT EXISTS `temp` (
  `bidid` varchar(80) DEFAULT NULL,
  `price` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `temp2`
--

CREATE TABLE IF NOT EXISTS `temp2` (
  `dspprice` int(11) DEFAULT NULL,
  `mediaid` varchar(100) DEFAULT NULL,
  `domain` varchar(100) DEFAULT NULL,
  `appid` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `bidid` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL COMMENT '名字',
  `email` varchar(30) NOT NULL COMMENT '登陆的用户名',
  `password` varchar(32) NOT NULL,
  `mobileTel` varchar(20) NOT NULL,
  `lastLoginIpAddr` char(20) DEFAULT NULL COMMENT '最后登录ip地址',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:待审核;2:正常;3:审核不通过,4:停用',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='账户信息' AUTO_INCREMENT=60013 ;

--
-- 触发器 `user`
--
DROP TRIGGER IF EXISTS `user_INSERT`;
DELIMITER //
CREATE TRIGGER `user_INSERT` AFTER INSERT ON `user`
 FOR EACH ROW BEGIN
insert into cnr_log.user select * from adexchange.user where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `user_UPDATE`;
DELIMITER //
CREATE TRIGGER `user_UPDATE` AFTER UPDATE ON `user`
 FOR EACH ROW BEGIN
insert into cnr_log.user select * from adexchange.user where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `user_buyer`
--

CREATE TABLE IF NOT EXISTS `user_buyer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL COMMENT '后台账户id',
  `idBuyer` int(11) NOT NULL COMMENT '买方账户id',
  `allow` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：允许；2：不允许',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idUserBuyer` (`idUser`,`idBuyer`),
  KEY `idUser` (`idUser`),
  KEY `idBuyer_idx` (`idBuyer`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户可以查看的买方数据' AUTO_INCREMENT=1 ;

--
-- 触发器 `user_buyer`
--
DROP TRIGGER IF EXISTS `user_buyer_INSERT`;
DELIMITER //
CREATE TRIGGER `user_buyer_INSERT` AFTER INSERT ON `user_buyer`
 FOR EACH ROW BEGIN
insert into cnr_log.user_buyer select * from adexchange.user_buyer where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `user_buyer_UPDATE`;
DELIMITER //
CREATE TRIGGER `user_buyer_UPDATE` AFTER UPDATE ON `user_buyer`
 FOR EACH ROW BEGIN
insert into cnr_log.user_buyer select * from adexchange.user_buyer where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idPlatform` int(11) NOT NULL,
  `idRole` int(11) NOT NULL,
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idUserProject` (`idUser`,`idPlatform`,`idRole`),
  KEY `idUser` (`idUser`),
  KEY `idPowerGroup` (`idRole`),
  KEY `idProject` (`idPlatform`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户所属的role' AUTO_INCREMENT=12 ;

--
-- 触发器 `user_role`
--
DROP TRIGGER IF EXISTS `user_role_INSERT`;
DELIMITER //
CREATE TRIGGER `user_role_INSERT` AFTER INSERT ON `user_role`
 FOR EACH ROW BEGIN
insert into cnr_log.user_role select * from adexchange.user_role where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `user_role_UPDATE`;
DELIMITER //
CREATE TRIGGER `user_role_UPDATE` AFTER UPDATE ON `user_role`
 FOR EACH ROW BEGIN
insert into cnr_log.user_role select * from adexchange.user_role where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `user_seller`
--

CREATE TABLE IF NOT EXISTS `user_seller` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL COMMENT '后台账户id',
  `idSeller` int(11) NOT NULL COMMENT '卖方账户id',
  `allow` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1：允许；2：不允许',
  `cuid` int(11) NOT NULL,
  `ctime` datetime NOT NULL,
  `muid` int(11) NOT NULL DEFAULT '0',
  `mtime` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idUserSeller` (`idUser`,`idSeller`),
  KEY `idUser` (`idUser`),
  KEY `idSeller_idx` (`idSeller`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户可以查看的卖方数据' AUTO_INCREMENT=5 ;

--
-- 触发器 `user_seller`
--
DROP TRIGGER IF EXISTS `user_seller_INSERT`;
DELIMITER //
CREATE TRIGGER `user_seller_INSERT` AFTER INSERT ON `user_seller`
 FOR EACH ROW BEGIN
insert into cnr_log.user_seller select * from adexchange.user_seller where id = NEW.id;
END
//
DELIMITER ;
DROP TRIGGER IF EXISTS `user_seller_UPDATE`;
DELIMITER //
CREATE TRIGGER `user_seller_UPDATE` AFTER UPDATE ON `user_seller`
 FOR EACH ROW BEGIN
insert into cnr_log.user_seller select * from adexchange.user_seller where id = NEW.id;
END
//
DELIMITER ;

-- --------------------------------------------------------

--
-- 替换视图以便查看 `v_buyer`
--
CREATE TABLE IF NOT EXISTS `v_buyer` (
`id` int(11)
,`buyType` tinyint(4)
,`creativeAuditType` tinyint(4)
,`gainType` tinyint(4)
,`gainRate` tinyint(4)
,`status` tinyint(4)
,`priceKey` varchar(200)
,`adxQps` int(11)
,`buyerQps` int(11)
,`bidUrl` varchar(200)
,`cookieMappingUrl` varchar(200)
,`winNoticeUrl` varchar(200)
);
-- --------------------------------------------------------

--
-- 替换视图以便查看 `v_role_power_items`
--
CREATE TABLE IF NOT EXISTS `v_role_power_items` (
`idPlatform` int(11)
,`idRole` int(11)
,`idPowerItem` int(11)
,`roleStatus` tinyint(4)
,`itemStatus` tinyint(4)
,`powerItemName` varchar(30)
,`parentId` int(11)
,`controller` varchar(100)
,`action` varchar(100)
,`displayFlag` tinyint(4)
,`itemOrder` decimal(10,4)
,`level` tinyint(4)
);
-- --------------------------------------------------------

--
-- 替换视图以便查看 `v_user`
--
CREATE TABLE IF NOT EXISTS `v_user` (
`id` int(11)
,`name` varchar(20)
,`email` varchar(30)
,`password` varchar(32)
,`mobileTel` varchar(20)
,`lastLoginIpAddr` char(20)
,`status` tinyint(4)
,`cuid` int(11)
,`ctime` datetime
,`muid` int(11)
,`mtime` timestamp
,`idRole` int(11)
);
-- --------------------------------------------------------

--
-- 视图结构 `v_buyer`
--
DROP TABLE IF EXISTS `v_buyer`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_buyer` AS select `b`.`id` AS `id`,`b`.`buyType` AS `buyType`,`b`.`creativeAuditType` AS `creativeAuditType`,`b`.`gainType` AS `gainType`,`b`.`gainRate` AS `gainRate`,`b`.`status` AS `status`,`bp`.`priceKey` AS `priceKey`,`bp`.`adxQps` AS `adxQps`,`bp`.`buyerQps` AS `buyerQps`,`bp`.`bidUrl` AS `bidUrl`,`bp`.`cookieMappingUrl` AS `cookieMappingUrl`,`bp`.`winNoticeUrl` AS `winNoticeUrl` from (`buyer` `b` left join `buyer_param` `bp` on((`b`.`id` = `bp`.`id`)));

-- --------------------------------------------------------

--
-- 视图结构 `v_role_power_items`
--
DROP TABLE IF EXISTS `v_role_power_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_role_power_items` AS select `r`.`idPlatform` AS `idPlatform`,`r`.`idRole` AS `idRole`,`r`.`idPowerItem` AS `idPowerItem`,`r`.`status` AS `roleStatus`,`p`.`status` AS `itemStatus`,`p`.`name` AS `powerItemName`,`p`.`parentId` AS `parentId`,`p`.`controller` AS `controller`,`p`.`action` AS `action`,`p`.`displayFlag` AS `displayFlag`,`p`.`itemOrder` AS `itemOrder`,`p`.`level` AS `level` from (`role_power_items` `r` join `power_item` `p`) where ((`r`.`idPlatform` = `p`.`idPlatform`) and (`r`.`idPowerItem` = `p`.`id`)) order by `r`.`idPlatform`,`r`.`idRole`,`p`.`itemOrder` desc,`p`.`parentId`,`p`.`level`,`p`.`itemOrder`;

-- --------------------------------------------------------

--
-- 视图结构 `v_user`
--
DROP TABLE IF EXISTS `v_user`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user` AS select `u`.`id` AS `id`,`u`.`name` AS `name`,`u`.`email` AS `email`,`u`.`password` AS `password`,`u`.`mobileTel` AS `mobileTel`,`u`.`lastLoginIpAddr` AS `lastLoginIpAddr`,`u`.`status` AS `status`,`u`.`cuid` AS `cuid`,`u`.`ctime` AS `ctime`,`u`.`muid` AS `muid`,`u`.`mtime` AS `mtime`,`ur`.`idRole` AS `idRole` from (`user` `u` left join `user_role` `ur` on(((`ur`.`idPlatform` = 1) and (`u`.`id` = `ur`.`idUser`))));

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
