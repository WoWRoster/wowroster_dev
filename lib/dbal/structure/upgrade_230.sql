#
# MySQL WoWRoster Upgrade File
#
# * $Id: upgrade_230.sql 2632 2014-08-21 20:28:28Z ulminia@gmail.com $
#
# --------------------------------------------------------
### New Tables

DROP TABLE IF EXISTS `renprefix_user_groups`;
CREATE TABLE `renprefix_user_groups` (
  `group_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_type` tinyint(4) NOT NULL DEFAULT '1',
  `group_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `group_desc` text COLLATE utf8_bin NOT NULL,
  `group_rank` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `renprefix_permissions` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL DEFAULT '',
  `type_id` int(5) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `info` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `cfg_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `renprefix_guild_rank`;
CREATE TABLE `renprefix_guild_rank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `control` varchar(255) NOT NULL DEFAULT '0',
  `guild_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `renprefix_api_enchant`;
CREATE TABLE `renprefix_api_enchant` (
 `id` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `name` varchar(200) NOT NULL,
 `bonus` mediumtext DEFAULT NULL,
 `slot` varchar(30) NOT NULL,
 `icon` varchar(64) NOT NULL,
 `description` mediumtext NOT NULL,
 `castTime` varchar(100) DEFAULT NULL,
 KEY `name` ( `name` ),
 PRIMARY KEY  ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `renprefix_all_gems`;
CREATE TABLE `renprefix_all_gems` (
 `gem_id` int(11) NOT NULL,
 `gem_name` varchar(96) NOT NULL,
 `gem_color` varchar(16) NOT NULL,
 `gem_tooltip` mediumtext NOT NULL,
 `gem_texture` varchar(64) NOT NULL,
 `gem_bonus` varchar(255) NOT NULL,
 `gem_bonus_stat1` varchar(255) NOT NULL,
 `gem_bonus_stat2` varchar(255) NOT NULL,
 `locale` varchar(16) NOT NULL,
 `timestamp` int(10) NOT NULL,
 `json` longtext DEFAULT NULL,
 PRIMARY KEY  ( `gem_id`, `locale` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `renprefix_sessions_keys`;
CREATE TABLE `renprefix_sessions_keys` (
 `key_id` char(32) NOT NULL,
 `user_id` mediumint(8) UNSIGNED NOT NULL DEFAULT '0',
 `last_ip` varchar(40) NOT NULL,
 `last_login` int(11) UNSIGNED NOT NULL DEFAULT '0',
 KEY `last_login` ( `last_login` ),
 PRIMARY KEY  ( `key_id`, `user_id` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `api_data_glyphs`;
CREATE TABLE `api_data_glyphs` (
 `name` varchar(96) NOT NULL,
 `id` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `class` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `type` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `description` mediumtext NOT NULL,
 `icon` varchar(96) NOT NULL,
 `itemId` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `spellKey` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `spellId` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `htmlDescription` mediumtext NOT NULL,
 `subtext` varchar(96) NOT NULL,
 `prettyName` varchar(96) NOT NULL,
 `typeOrder` varchar(96) NOT NULL,
 KEY `class` ( `class` ),
 KEY `name` ( `name` ),
 PRIMARY KEY  ( `id` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `api_class_spells`;
CREATE TABLE `api_class_spells` (
 `spellId` int(11) UNSIGNED NOT NULL DEFAULT '0',
 `class_id` varchar(2) NOT NULL,
 `name` varchar(96) NOT NULL,
 `icon` varchar(96) NOT NULL,
 `castTime` varchar(96) NOT NULL,
 `description` mediumtext NOT NULL,
 `id` varchar(96) NOT NULL,
 `powerType` varchar(96) NOT NULL,
 `classMask` varchar(96) NOT NULL,
 `raceMask` varchar(96) NOT NULL,
 `htmlDescription` mediumtext NOT NULL,
 `classAbility` varchar(96) NOT NULL,
 `rawDescription` mediumtext NOT NULL,
 `serverOnly` varchar(96) NOT NULL,
 `keyAbility` varchar(96) NOT NULL,
 `spec` varchar(96) NOT NULL,
 `minLevel` varchar(96) NOT NULL,
 `mastery` varchar(96) NOT NULL,
 `passive` varchar(96) NOT NULL,
 KEY `class` ( `class_id` ),
 KEY `name` ( `name` ),
 PRIMARY KEY  ( `spellId` )
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
# --------------------------------------------------------
### Altered Tables
ALTER TABLE  `renprefix_items` ADD `json` longtext DEFAULT NULL;
ALTER TABLE  `renprefix_user_members` ADD `hash` varchar(32) NOT NULL DEFAULT '0';
ALTER TABLE  `renprefix_sessions ADD `guestid` varchar(10) DEFAULT NULL;
ALTER TABLE  `renprefix_talents_data` ADD INDEX (  `tree_order` ) ;
# --------------------------------------------------------
### Add to Tables

# --------------------------------------------------------
### Update Tables
# --------------------------------------------------------
### Config Table Updates

# javascript/css aggregation

### api key settings
# session settings
# --------------------------------------------------------
### Menu Updates
INSERT INTO `renprefix_menu_button` VALUES (3, 0, 'menu_roster_ucp', 'util', 'ucp', 'inv_misc_gear_07');

# --------------------------------------------------------
### Permissions settings
INSERT INTO `renprefix_permissions` VALUES ('', 'roster', '00', 'core', 'roster_cp', 'roster_cp_desc' , 'roster_cp');
INSERT INTO `renprefix_permissions` VALUES ('', 'roster', '00', 'core', 'gp_update', 'gp_update_desc' , 'gp_update');
INSERT INTO `renprefix_permissions` VALUES ('', 'roster', '00', 'core', 'cp_update', 'cp_update_desc' , 'cp_update');
INSERT INTO `renprefix_permissions` VALUES ('', 'roster', '00', 'core', 'lua_update', 'lua_update_desc' , 'lua_update');

# --------------------------------------------------------
### User groups
INSERT INTO `renprefix_user_groups` VALUES ('0', '1', 'Public', 'general public access group auth use only', NULL, '0');
INSERT INTO `renprefix_user_groups` VALUES ('1', '1', 'Admin', 'Admin user group', NULL, '0');