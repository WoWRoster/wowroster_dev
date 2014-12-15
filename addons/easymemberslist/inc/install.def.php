<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @link       http://www.wowroster.net
 * @package    EasyMembersList
 * @subpackage Installer
 */

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * Installer for EasyMembersList Addon
 * @package    easymemberslist
 * @subpackage Installer
 */
class easymemberslistInstall
{
	var $active = true;
	var $icon = 'inv_letter_06';

	var $version = '2.1.0';
	var $wrnet_id = '0';

	var $fullname = 'easymemberslist';
	var $description = 'easymemberslist_desc';
	var $credits = array(
		array(	"name"=>	"Nefuh",
				"info"=>	"Original Author"),
		array(	"name"=>	"WoWRoster Dev Team",
				"info"=>	"Contributor")
	);


	/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
		$installer->add_menu_button('easymemberslist_button','guild','','spell_holy_prayerofspirit');
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
		$installer->remove_all_menu_button();
		return true;
	}
}
