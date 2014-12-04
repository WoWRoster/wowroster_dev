<?php
/**
 * WoWRoster.net WoWRoster
 *
 * rostersync install definition
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: install.def.php 563 2013-02-13 23:09:46Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @package    rostersync
*/

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

/**
 * rostersync Addon Installer
 * @package rostersync
 * @subpackage Installer
 */
class rostersyncInstall
{
	var $active = true;
	var $icon = 'spell_deathknight_bladedarmor';

	var $version = '0.0.2';
	var $wrnet_id  = '';

	var $fullname = 'Roster Sync';
	var $description = 'Syncronizes WoWRoster with Blizzard\'s API';
	var $credits = array(
            array(	"name"=>	"Ulminia",
			"info"=>	"Author API Dev"),
	);

	/**
	 * Install function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
		
		if( !function_exists('curl_init') )
		{
			$installer->seterrors('Curl not detected rostersync may not work!!!!!');
			return;
		}

		$installer->add_menu_button('rsync_button1','char', 'profile', 'rs_player.png');
		$installer->add_menu_button('rsync_button2','guild', 'gprofile', 'rs_members.png');
		$installer->add_menu_button('rsync_button3','util', 'addguild', 'rs_add_guild.png');
		$installer->add_menu_button('rsync_button4','guild', 'memberlist', 'rs_memberlist.png');
		
		if( !function_exists('curl_init') )
		{
			$installer->seterrors('Curl not detected rsync may not work!!!!!');
			return;
		}

		// Master and menu entries
		$installer->add_config("'1','startpage','rsync_conf','display','master'");
		$installer->add_config("'10','rsync_conf',NULL,'blockframe','menu'");
		$installer->add_config("'20','rsync_ranks',NULL,'blockframe','menu'");
		$installer->add_config("'30','rsync_scaning',NULL,'blockframe','menu'");
		$installer->add_config("'40','rsync_scan_guild',NULL,'blockframe','menu'");
		$installer->add_config("'50','rsync_scan_char',NULL,'blockframe','menu'");
		$installer->add_config("'60','rsync_access',NULL,'blockframe','menu'");
		$installer->add_config("'70','rsync_debug',NULL,'blockframe','menu'");
		
		/*
			rsync_conf
		*/
		$installer->add_config("'100', 'rsync_minlevel', '10', 'text{3|3', 'rsync_conf'");
		$installer->add_config("'101', 'rsync_synchcutofftime', '1', 'text{4|4', 'rsync_conf'");
		//$installer->add_config("'102', 'rsync_use_ajax', '0', 'radio{off^0|Off^0', 'rsync_conf'");
		$installer->add_config("'103', 'rsync_reloadwaittime', '24', 'text{4|4', 'rsync_conf'");
		$installer->add_config("'104', 'rsync_fetch_timeout', '8', 'text{2|2', 'rsync_conf'");
		$installer->add_config("'105', 'rsync_skip_start', '0', 'radio{On^1|Off^0', 'rsync_conf'");
		$installer->add_config("'106', 'rsync_status_hide', '0', 'radio{On^1|Off^0', 'rsync_conf'");
		$installer->add_config("'107', 'rsync_protectedtitle', 'Banker', 'text{64|20', 'rsync_conf'");
		
		/*
			rsync_ranks
		*/
		$installer->add_config("'201', 'rsync_rank_set_order', '3', 'select{Roster/rsync/Armory^3|rsync/Roster/Armory^2|Roster/Armory^1|Armory^0', 'rsync_ranks'");
		$installer->add_config("'202', 'rsync_rank_0', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'203', 'rsync_rank_1', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'204', 'rsync_rank_2', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'205', 'rsync_rank_3', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'206', 'rsync_rank_4', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'207', 'rsync_rank_5', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'208', 'rsync_rank_6', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'209', 'rsync_rank_7', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'210', 'rsync_rank_8', '', 'text{64|20', 'rsync_ranks'");
		$installer->add_config("'211', 'rsync_rank_9', '', 'text{64|20', 'rsync_ranks'");
		/*
			rsync_scanning
		*/
		$installer->add_config("'301','rsync_MinLvl','10', 'text{3|3', 'rsync_scaning'");
		$installer->add_config("'302','rsync_MaxLvl','100', 'text{3|3', 'rsync_scaning'");
		$installer->add_config("'303','rsync_Rank','','select{----None----^|GuildMaster^0|Rank 1^1|Rank 2^2|Rank 3^3|Rank 4^4|Rank 5^5|Rank 6^6|Rank 7^7|Rank 8^8|Rank 9^9|Rank 10^10|Rank 11^11|Rank 12^12', 'rsync_scaning'");
		$installer->add_config("'304','rsync_Class','','select{----None----^|Warrior^1|Paladin^2|Hunter^3|Rogue^4|Priest^5|Death Knight^6|Shaman^7|Mage^8|Warlock^9|Monk^10|Druid^11', 'rsync_scaning'");

		/*
			rsync_scan_guild
		*/
		$installer->add_config("'401', 'rsync_guild_members', '0', 'radio{yes^1|no^0', 'rsync_scan_guild'");
		$installer->add_config("'402', 'rsync_guild_achievements', '0', 'radio{yes^1|no^0', 'rsync_scan_guild'");
		$installer->add_config("'403', 'rsync_guild_news', '0', 'radio{yes^1|no^0', 'rsync_scan_guild'");
		$installer->add_config("'404', 'rsync_guild_challenge', '0', 'radio{yes^1|no^0', 'rsync_scan_guild'");
		/*
			rsync_scan_char
		*/
		$installer->add_config("'501', 'rsync_char_achievements', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'502', 'rsync_char_appearance', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'503', 'rsync_char_feed', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'504', 'rsync_char_guild', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'505', 'rsync_char_hunterPets', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'506', 'rsync_char_items', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'507', 'rsync_char_mounts', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'508', 'rsync_char_pets', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'509', 'rsync_char_petSlots', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'510', 'rsync_char_professions', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'511', 'rsync_char_progression', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'512', 'rsync_char_pvp', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'513', 'rsync_char_quests', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'514', 'rsync_char_reputation', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'515', 'rsync_char_stats', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'516', 'rsync_char_talents', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'517', 'rsync_char_titles', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'518', 'rsync_char_audit', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'519', 'rsync_char_companions', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'520', 'rsync_char_statistics', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		$installer->add_config("'521', 'rsync_char_hunterPets', '0', 'radio{yes^1|no^0', 'rsync_scan_char'");
		/*
			rsync_access
		*/
		$installer->add_config("'601', 'rsync_char_update_access', '1', 'access', 'rsync_access'");
		$installer->add_config("'602', 'rsync_guild_update_access', '2', 'access', 'rsync_access'");
		$installer->add_config("'603', 'rsync_guild_memberlist_update_access', '2', 'access', 'rsync_access'");
		$installer->add_config("'604', 'rsync_realm_update_access', '3', 'access', 'rsync_access'");
		$installer->add_config("'605', 'rsync_guild_add_access', '3', 'access', 'rsync_access'");
		/*
			rsync_debug
		*/
		$installer->add_config("'701', 'rsync_debuglevel', '1', 'select{All Methods Data Info^3|Armory & Job Data Info^2|Base Info^1|Quiet^0', 'rsync_debug'");
		$installer->add_config("'702', 'rsync_debugdata', '0', 'radio{yes^1|no^0', 'rsync_debug'");
		$installer->add_config("'703', 'rsync_javadebug', '0', 'radio{yes^1|no^0', 'rsync_debug'");
		$installer->add_config("'704', 'rsync_xdebug_php', '0', 'radio{yes^1|no^0', 'rsync_debug'");
		$installer->add_config("'705', 'rsync_xdebug_ajax', '0', 'radio{yes^1|no^0', 'rsync_debug'");
		$installer->add_config("'706', 'rsync_xdebug_idekey', 'test', 'text{64|10', 'rsync_debug'");
		$installer->add_config("'707', 'rsync_sqldebug', '0', 'radio{yes^1|no^0', 'rsync_debug'");
		$installer->add_config("'708', 'rsync_updateroster', '1', 'radio{yes^1|no^0', 'rsync_debug'");

		$installer->create_table(
			$installer->table('jobs'),
				"
				`job_id` int(11) unsigned NOT NULL auto_increment,
				`starttimeutc` datetime NOT NULL,
				PRIMARY KEY  (`job_id`)
				" );
		$installer->create_table(
			$installer->table('jobqueue'),
				"
				`job_id` int(11) unsigned NOT NULL,
				`member_id` int(11) unsigned NOT NULL,
				`name` varchar(64) NOT NULL,
				`guild_id` int(11) NOT NULL,
				`guild_name` varchar(64) NOT NULL,
				`server` varchar(32) NOT NULL,
				`region` char(2) NOT NULL,
				`guild_info` int(11) unsigned default NULL,
				`character_info` tinyint(1) default NULL,
				`skill_info` int(11) default NULL,
				`reputation_info` int(11) default NULL,
				`equipment_info` int(11) default NULL,
				`talent_info` int(11) default NULL,
				`starttimeutc` datetime default NULL,
				`stoptimeutc` datetime default NULL,
				`log` text,
				PRIMARY KEY  (`job_id`,`member_id`)
				" );
		$installer->create_table(
			$installer->table('updates'),
				"
				`member_id` int(11) NOT NULL,
				`dateupdatedutc` datetime default NULL,
				PRIMARY KEY  (`member_id`)
				" );
		return true;
	}

	/**
	 * Upgrade functoin
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
		global $installer;

		if( version_compare('0.0.2', $oldversion, '>') == true )
		{
		
			$installer->remove_config('102');
			$installer->remove_config('100');
			$installer->remove_config('301');
			$installer->remove_config('302');
			
			$installer->add_config("'100', 'rsync_minlevel', '10', 'text{3|3', 'rsync_conf'");
			$installer->add_config("'301','rsync_MinLvl','10', 'text{2|2', 'rsync_scaning'");
			$installer->add_config("'302','rsync_MaxLvl','100', 'text{3|3', 'rsync_scaning'");
			
			
		}

		return true;
	}

	/**
	 * Un-Install function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer;

		$installer->remove_all_config();
		$installer->remove_all_menu_button();
		$installer->drop_table( $installer->table('jobs') );
		$installer->drop_table( $installer->table('jobqueue') );
		$installer->drop_table( $installer->table('updates') );
		return true;
	}
}
