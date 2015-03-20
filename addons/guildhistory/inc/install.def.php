<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5" * @package    GuildHistory
 * @subpackage Installer
*/

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

/**
 * Installer Instance Keys Addon
 *
 * @package    InstanceKeys
 * @subpackage Installer
 */
class guildhistoryInstall
{
	var $active = true;
	var $icon = 'inv_misc_book_06';

	var $version = '1.9.9.1703';	// ALWAYS NOTE BELOW IN upgrade() WHY THE VERSION NUMBER HAS CHANGED, EVEN WHEN ONLY UPDATING KEY DEFINES
	var $wrnet_id = '124';

	var $fullname = 'guildhistory';
	var $description = 'guildhistory_desc';
	var $credits = array(
		array(	"name"=>	"Joerg Hufen, 2008",
				"info"=>	"Displays the GuildHistory tracking by GuildProfiler >= 2.3.1")
	);


	/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;

		// Master and menu entries
		$installer->add_config("'1','startpage','guildhistory_conf','display','master'");
		$installer->add_config("'110','guildhistory_conf',NULL,'blockframe','menu'");
		$installer->add_config("'200','guildhistory_cats','rostercp-addon-guildhistory-categories','makelink','menu'");
		$installer->add_config("'1010','guildhistory_access','0','access','guildhistory_conf'");
		$installer->add_config("'1020','guildhistory_format', '0', 'radio{long^1|short^0', 'guildhistory_conf'");
		$installer->add_config("'1030','guildhistory_line_format', '0', 'radio{block^1|single^0', 'guildhistory_conf'");
		$installer->add_menu_button('guildhistorybutton','guild');
 		$installer->create_table($installer->table('guildhistory'),"
  					`guild_id` int(11) NOT NULL,
					`id` tinyint(3) NOT NULL,
  					`player1` varchar(255) NOT NULL,
  					`type` varchar(100) NOT NULL,
  					`player2` varchar(255) NOT NULL,
  					`time` datetime default NULL,
  					`logtime` datetime default NULL,
  					`rank` varchar(100) NOT NULL,
  					KEY `player1` (`player1`),
  					KEY `guild_id` (`guild_id`)");
		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
		global $installer;
		if( version_compare('1.9.9.1702', $oldversion,'>') == true )
		{
			$installer->add_config("'1020','guildhistory_format', '0', 'radio{long^1|short^0', 'guildhistory_conf'");
			$installer->add_config("'1030','guildhistory_line_format', '0', 'radio{block^1|single^0', 'guildhistory_conf'");
		}
		
		if( version_compare('1.9.9.1703', $oldversion,'>') == true )
		{
			$installer->remove_all_config();
			$installer->add_config("'1','startpage','guildhistory_conf','display','master'");
			$installer->add_config("'110','guildhistory_conf',NULL,'blockframe','menu'");
			$installer->add_config("'200','guildhistory_cats','rostercp-addon-guildhistory-categories','makelink','menu'");
			$installer->add_config("'1010','guildhistory_access','0','access','guildhistory_conf'");
			$installer->add_config("'1020','guildhistory_format', '0', 'radio{long^1|short^0', 'guildhistory_conf'");
			$installer->add_config("'1030','guildhistory_line_format', '0', 'radio{block^1|single^0', 'guildhistory_conf'");
		}
		
		return true;
	}

	/**
	 * Un-Install Function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer;

		$installer->remove_all_config();

		$installer->drop_table($installer->table('guildhistory'));
		$installer->remove_all_menu_button();
		return true;
	}
}
