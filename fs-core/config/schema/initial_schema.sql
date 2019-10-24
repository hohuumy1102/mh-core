
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

-- --------------------------------------------------------

--
-- Table structure for table `admin_whitelist_ips`
--

CREATE TABLE IF NOT EXISTS `admin_whitelist_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(20) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_role_id` int(11) DEFAULT 0,
  `email` varchar(255) NOT NULL,
  `password` varchar(255),
  `status` tinyint(2) DEFAULT 0,
  `count_login` tinyint(4) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `admin_role_id` (`admin_role_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `target_id` int(11) NOT NULL,
  `target_type` tinyint(2) DEFAULT 0,
  `content` text NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `target` (`target_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `language_contents`
--

CREATE TABLE IF NOT EXISTS `language_contents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field` varchar(100) NOT NULL,
  `content` text NULL,
  `language` tinyint(3) DEFAULT 0,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field` (`field`),
  KEY `language` (`language`),
  KEY `target` (`target_id`, `target_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `configs`
--

CREATE TABLE IF NOT EXISTS `configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `field` int(6) NOT NULL DEFAULT 0,
  `content` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field` (`field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `sub_contents`
--

CREATE TABLE IF NOT EXISTS `sub_contents` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `position` int(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `seo`
--

CREATE TABLE IF NOT EXISTS `seo` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL DEFAULT '',
  `language` tinyint(3) DEFAULT 0,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language` (`language`),
  KEY `target` (`target_id`, `target_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `slugs`
--

CREATE TABLE IF NOT EXISTS `slugs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `language` tinyint(3) DEFAULT 0,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `language` (`language`),
  KEY `target` (`target_id`, `target_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `multi_photos`
--

CREATE TABLE IF NOT EXISTS `multi_photos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `target_id` int(11) NOT NULL,
  `target_type` varchar(30) NOT NULL,
  `field` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field` (`field`),
  KEY `target` (`target_id`, `target_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

