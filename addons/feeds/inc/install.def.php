<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3. * @package    GuildInfo
 * @subpackage Installer
*/

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * Installer for GuildInfo Addon
 * @package    GuildInfo
 * @subpackage Installer
 */
class feedsInstall
{
	var $active = true;
	var $icon = 'inv_misc_note_05';

	var $version = '1.0';
	var $wrnet_id = '0';

	var $fullname = 'feeds';
	var $description = 'feeds_desc';
	var $credits = array(
		array(	"name"=>	"Ulminia",
				"info"=>	"Original Author")
	);


	/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
		
		$installer->add_config("7550,'startpage','display','display','master'");

		# Config menu entries
		$installer->add_config("7551,'display',NULL,'blockframe','menu'");
		$installer->add_config("7552,'page_size','0','text{4|30','display'");
		$installer->add_config("7553,'icon_size','24','select{12^12|14^14|18^18|24^24|30^30|36^36|50^50|56^56','display'");		
		
		$installer->create_table($installer->table('char_feed'),"
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`member_id` int(11) unsigned NOT NULL default '0',
			`type` varchar(96) NOT NULL default '',
			`Member` varchar(64) NOT NULL default '',
			`Achievement` mediumtext ,
			`achievement_icon` varchar(96) NOT NULL default '',
			`achievement_title` varchar(150) NOT NULL default '',
			`achievement_points` varchar(96) NOT NULL default '',
			`item_icon` varchar(96) NOT NULL default '',
			`item_id` varchar(10) NOT NULL default '',
			`achievement_id` varchar(10) NOT NULL default '',
			`criteria_description` varchar(150) NOT NULL default '',
			`Date` datetime default NULL,
			`timestamp` varchar(96) NOT NULL default '',
			`Typpe` varchar(32) NOT NULL default '',
			KEY `id` (`id`)");
			
		$installer->create_table($installer->table('guild_feed'),"
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`guild_id` int(11) unsigned NOT NULL default '0',
			`type` varchar(96) NOT NULL default '',
			`Member` varchar(64) NOT NULL default '',
			`Achievement` mediumtext ,
			`achievement_icon` varchar(96) NOT NULL default '',
			`achievement_title` varchar(150) NOT NULL default '',
			`achievement_points` varchar(96) NOT NULL default '',
			`item_icon` varchar(96) NOT NULL default '',
			`item_id` varchar(10) NOT NULL default '',
			`achievement_id` varchar(10) NOT NULL default '',
			`criteria_description` varchar(150) NOT NULL default '',
			`Date` datetime default NULL,
			`timestamp` varchar(96) NOT NULL default '',
			`Typpe` varchar(32) NOT NULL default '',
			KEY `id` (`id`)");
		
		//$installer->add_query("ALTER TABLE  `".$installer->table('guild_feed')."` DROP INDEX  `timestamp`");
		//$installer->add_query("ALTER TABLE  `".$installer->table('char_feed')."` DROP INDEX  `timestamp`");

		$installer->add_menu_button('cfeedbutton','char');
		$installer->add_menu_button('gfeedbutton','guild');
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

		$installer->drop_table($installer->table('char_feed'));
		$installer->drop_table($installer->table('guild_feed'));
		$installer->remove_all_config();
		$installer->remove_all_menu_button();
		return true;
	}
}
