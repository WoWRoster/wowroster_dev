<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Available pages for RosterCP
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: pages.php 2555 2012-06-24 13:47:17Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
 * @subpackage RosterCP
*/

if( !defined('IN_ROSTER') || !defined('IN_ROSTER_ADMIN') )
{
	exit('Detected invalid access to this file!');
}

// The key in the $config_pages array is the pagename for the admincp file.
// The value is an array whose keys have these meanings:
//	"href"		The link this should refer to.
//	"title"		The localization key for the button title.
//	"file"		The file to include if this page is called. Missing means
//			invalid page.
//	"special"	Ignored unless it's one of the following:
//			'divider'	Prints a horizontal line and no button.
//			'hidden'	Hides the link, but allows access to the page

$config_pages['user'] = array(
	'href'=>	$roster->pages[0],
	'title'=>	'pagebar_user_settings',
	'file'=>	'user_settings.php',
	);
$config_pages['charconf'] = array(
	'href'=>	$roster->pages[0].'-charconf',
	'title'=>	'pagebar_user_chars',
	'file'=>	'user_char_conf.php',
	);
	
$config_pages['change_pass'] = array(
	'href'=>	$roster->pages[0].'-change_pass',
	'title'=>	'pagebar_changepass',
	'file'=>	'change_pass.php',
	);
$config_pages['addon'] = array(
	'special'=>	'hidden',
	'file'=>	'addon_conf.php',
	);