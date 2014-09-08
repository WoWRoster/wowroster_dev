<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Roster Installer
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: upgrade.php 2612 2012-09-08 16:58:13Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.7.0
 * @package    WoWRoster
 * @subpackage Upgrader
 */

if (!defined('IN_ROSTER')) {
	exit('Detected invalid access to this file!');
}

/**
 * WoWRoster Upgrader
 *
 * @package    WoWRoster
 * @subpackage Upgrader
 */
class Upgrade {
	var $versions = array(
		'2.0.0',
		'2.0.1',
		'2.0.2',
		'2.1.0',
		'2.2.0',
		'2.9.9.1111',
		'2.9.9.1119',
		'2.9.9.1120'
	);
	var $index = null;

	function Upgrade() {
		global $roster;

		//$roster->db->error_die(false);

		$roster->tpl->assign_var('MESSAGE', false);
		$roster->cache->cleanCache();
		// First check the current version compared to upgrade version
		if (version_compare($roster->config['version'], ROSTER_VERSION, '>=')) {
			$roster->tpl->assign_var('MESSAGE', sprintf($roster->locale->act['no_upgrade'], ROSTER_PATH));
			$this->display_page();
		}

		if (isset($_POST['upgrade'])) {
			// Find out what version we're upgrading from
			$version_from = $_POST['version'];
			foreach ($this->versions as $index => $version) {
				$this->index = $index;
				if (ROSTER_BETA)
				{
					if (str_replace('.', '', $version) == $version_from) {
						$method = 'beta_' . $version_from;
						$this->$method();
					}
				}
				else if (str_replace('.', '', $version) == $version_from) {
					$method = 'upgrade_' . $version_from;
					$this->$method();
				}
			}
		}
		else {
			$this->display_form();
		}
	}

	function finalize() {
		global $roster;

		$this->index++;

		if (isset($this->versions[$this->index])) {
			$method = 'upgrade_' . str_replace('.', '', $this->versions[$this->index]);
			$this->$method();
		}
		else {
			$roster->tpl->assign_var('MESSAGE', sprintf($roster->locale->act['upgrade_complete'], ROSTER_PATH));
			$this->display_page();
		}
	}

	//--------------------------------------------------------------
	// Upgrade methods
	//--------------------------------------------------------------
	/**
	 * Upgrades the 2.2.9.x beta versions into the 2.3.0 release
	 */
	function beta_230()
	{
		global $roster, $installer;

		if (version_compare($roster->config['version'], '2.9.9.1111', '<'))
		{
			$roster->set_message('test for 2.9.9.1111 upgrade ');
			$roster->db->query("INSERT INTO `" . $roster->db->table('menu_button') . "` VALUES (3, 0, 'menu_roster_ucp', 'util', 'ucp', 'inv_misc_gear_07')");
			$this->beta_upgrade();
			$this->finalize();
		}
		return;
	}
	function beta_2991111()
	{
		global $roster, $installer;
		if (version_compare($roster->config['version'], '2.9.9.1119'))
		{
			$roster->set_message('2.9.9.1119 Upgrade');
			$roster->db->query("INSERT INTO `" . $roster->db->table('menu_button') . "` VALUES (3, 0, 'menu_roster_ucp', 'util', 'ucp', 'inv_misc_gear_07')");
			$roster->db->query("ALTER TABLE  `" . $roster->db->table('api_usage') . "` ADD  `url` MEDIUMTEXT NULL DEFAULT NULL AFTER  `type` ;
			ALTER TABLE  `" . $roster->db->table('api_usage') . "` ADD  `responce_code` VARCHAR( 20 ) NULL DEFAULT NULL AFTER  `url` ;
			ALTER TABLE  `" . $roster->db->table('api_usage') . "` ADD  `content_type` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `responce_code` ;

			DROP TABLE IF EXISTS `" . $roster->db->table('api_cache') . "`;
			CREATE TABLE `" . $roster->db->table('api_cache') . "` (
				`cid` int(11) NOT NULL AUTO_INCREMENT,
				`id`				int(11) NOT NULL,
				`type`			varchar(96) NOT NULL,
				`timestamp`		varchar(16) NOT NULL,
				`name`			varchar(64) NOT NULL,
				`locale`         varchar(16) DEFAULT NULL,
				`json`           longtext DEFAULT NULL,
				PRIMARY KEY (`cid`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

			$this->beta_upgrade();
			$this->finalize();
		}
		return;
	}
	function beta_2991119()
	{
		global $roster, $installer;
		if (version_compare($roster->config['version'], '2.9.9.1120'))
		{
			$roster->db->query("
			CREATE TABLE IF NOT EXISTS `" . $roster->db->table('guild_rank') . "` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `rank` tinyint(4) NOT NULL DEFAULT '0',
			  `title` varchar(255) NOT NULL DEFAULT '',
			  `control` varchar(255) NOT NULL DEFAULT '0',
			  `guild_id` int(11) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$this->beta_upgrade();
		$this->finalize();
		}
		return;
	}
	
	function beta_2991120()
	{
		global $roster, $installer;
		if (version_compare($roster->config['version'], '2.9.9.1121'))
		{
			$roster->db->query("DROP TABLE IF EXISTS `" . $roster->db->table('api_usage') . "`;");
			$roster->db->query("CREATE TABLE IF NOT EXISTS `" . $roster->db->table('api_usage') . "` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `url` mediumtext,
  `responce_code` varchar(20) DEFAULT NULL,
  `content_type` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `total` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;");

		$this->beta_upgrade();
		$this->finalize();
		}
		return;
	}

	/*
	*	this ends the beta upgrader
	*/

	/**
	 * Upgrades 2.2.0 to 2.3
	 */
	function upgrade_230() {
		global $roster;

		$this->standard_upgrader();
		$this->finalize();
		
	}
	/**
	 * Upgrades 2.1.0 to 2.2
	 */
	function upgrade_220() {
		global $roster;

		// make admin password roster password
		$this->standard_upgrader();
		$this->finalize();
		
	}
	//*/
	/**
	 * Upgrades 2.1.0 to 2.1.1
	 */
	function upgrade_211() {
		global $roster;

		$this->upgrade_220();
	}
	/**
	 * Upgrades 2.1.0 to 2.1.1
	 */
	function upgrade_210() {
		global $roster;

		$this->upgrade_220();
	}

	/**
	 * Upgrades 2.0.2 to 2.1.0
	 */
	function upgrade_202() {
		global $roster;

		$this->standard_upgrader();
		$this->finalize();
	}

	/**
	 * Upgrades 2.0.1 to 2.0.2
	 */
	function upgrade_201() {
		$this->standard_upgrader();
		$this->finalize();
	}

	/**
	 * Upgrades 2.0.0 to 2.0.1
	 */
	function upgrade_200() {
		$this->standard_upgrader();
		$this->finalize();
	}

	function beta_upgrade() {
		global $roster;
		
		$ver = str_replace('.', '', $this->versions[$this->index]);

		/*
		$db_structure_file = ROSTER_LIB . 'dbal' . DIR_SEP . 'structure' . DIR_SEP . 'beta_' . $ver . '.sql';

		if (file_exists($db_structure_file)) {
			// Parse structure file and create database tables
			$sql = @fread(@fopen($db_structure_file, 'r'), @filesize($db_structure_file));
			$sql = preg_replace('#renprefix\_(\S+?)([\s\.,]|$)#', $roster->db->prefix . '\\1\\2', $sql);

			$sql = remove_remarks($sql);
			$sql = parse_sql($sql, ';');

			$sql_count = count($sql);
			for ($i = 0; $i < $sql_count; $i++) {
				$roster->db->query($sql[$i]);
			}
			unset($sql);
		}
		else {
			roster_die('Could not obtain SQL structure/data "'.$db_structure_file.'"', $roster->locale->act['upgrade_wowroster']);
		}
		*/
		
		
		$roster->db->query("UPDATE `" . $roster->db->table('config') . "` SET `config_value` = '" . ROSTER_VERSION . "' WHERE `id` = '4' LIMIT 1;");
		$roster->db->query("ALTER TABLE `" . $roster->db->table('config') . "` ORDER BY `id`;");
		return;
	}

	/**
	 * The standard upgrader
	 * This parses the requested sql file for database upgrade
	 * Most upgrades will use this function
	 */
	function standard_upgrader() {
		global $roster;

		$ver = str_replace('.', '', $this->versions[$this->index]);

		$db_structure_file = ROSTER_LIB . 'dbal' . DIR_SEP . 'structure' . DIR_SEP . 'upgrade_' . $ver . '.sql';

		if (file_exists($db_structure_file)) {
			// Parse structure file and create database tables
			$sql = @fread(@fopen($db_structure_file, 'r'), @filesize($db_structure_file));
			$sql = preg_replace('#renprefix\_(\S+?)([\s\.,]|$)#', $roster->db->prefix . '\\1\\2', $sql);

			$sql = remove_remarks($sql);
			$sql = parse_sql($sql, ';');

			$sql_count = count($sql);
			for ($i = 0; $i < $sql_count; $i++) {
				$roster->db->query($sql[$i]);
			}
			unset($sql);
		}
		else {
			roster_die('Could not obtain SQL structure/data', $roster->locale->act['upgrade_wowroster']);
		}

		$roster->db->query("UPDATE `" . $roster->db->table('config') . "` SET `config_value` = '" . ROSTER_VERSION . "' WHERE `id` = '4' LIMIT 1;");
		$roster->db->query("ALTER TABLE `" . $roster->db->table('config') . "` ORDER BY `id`;");

		return;
	}

	function display_form() {
		global $roster;

		$this->versions = array_reverse($this->versions);

		foreach ($this->versions as $version) {
			$selected = ($version == $roster->config['version']) ? ' selected="selected"' : '';

			$roster->tpl->assign_block_vars('version_row', array(
				'VALUE' => str_replace('.', '', $version),
				'SELECTED' => $selected,
				'OPTION' => 'WoWRoster ' . $version
			));
		}
		$this->display_page();
	}

	function display_page() {
		global $roster;

		$roster->tpl->assign_var('U_UPGRADE', makelink('upgrade'));

		$roster->output['title'] = $roster->locale->act['upgrade_wowroster'];

		include (ROSTER_BASE . 'header.php');

		$roster->tpl->set_handle('body', 'upgrade.html');
		$roster->tpl->display('body');

		include (ROSTER_BASE . 'footer.php');
		die();
	}
}

/**
 * Removes comments from a SQL data file
 *
 * @param    string  $sql    SQL file contents
 * @return   string
 */
function remove_remarks($sql) {
	global $roster;

	if($sql == '') {
		roster_die('Could not obtain SQL structure/data', $roster->locale->act['upgrade_wowroster']);
	}

	$retval = '';
	$lines = explode("\n", $sql);
	unset($sql);

	foreach ($lines as $line) {
		// Only parse this line if there's something on it, and we're not on the last line
		if (strlen($line) > 0) {
			// If '#' is the first character, strip the line
			$retval .= (substr($line, 0, 1) != '#') ? $line . "\n" : "\n";
		}
	}
	unset($lines, $line);

	return $retval;
}

/**
 * Parse multi-line SQL statements into a single line
 *
 * @param    string  $sql    SQL file contents
 * @param    char    $delim  End-of-statement SQL delimiter
 * @return   array
 */
function parse_sql($sql, $delim) {
	global $roster;

	if ($sql == '') {
		roster_die('Could not obtain SQL structure/data', $roster->locale->act['upgrade_wowroster']);
	}

	$retval = array();
	$statements = explode($delim, $sql);
	unset($sql);

	$linecount = count($statements);
	for ($i = 0; $i < $linecount; $i++) {
		if (($i != $linecount - 1) || (strlen($statements[$i]) > 0)) {
			$statements[$i] = trim($statements[$i]);
			$statements[$i] = str_replace("\r\n", '', $statements[$i]) . "\n";

			// Remove 2 or more spaces
			$statements[$i] = preg_replace('#\s{2,}#', ' ', $statements[$i]);

			$retval[] = trim($statements[$i]);
		}
	}
	unset($statements);

	return $retval;
}

$upgrade = new Upgrade();

// And the upgrade-o-matic 5000 takes care of the rest.
