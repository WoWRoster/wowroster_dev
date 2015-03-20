<?php
/**
 * WoWRoster.net WoWRoster
 *
 * RosterCP (Control Panel)
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @package    WoWRoster
 * @subpackage RosterCP
*/

/******************************
 * Call parameters:
 *
 * page
 *		roster		Roster config
 *		character	Per-character preferences
 *		addon		Addon config
 *		install		Addon installation screen
 *
 * addon	If page is addon, this says which addon is being configured
 * profile	If page is addon, this says which addon profile is being configured.
 *
 ******************************/

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}
$roster->tpl->assign_vars(array(
	'PAGE_INFO' => $roster->locale->act['roster_ucp'],
	)
);
// ----[ Check log-in ]-------------------------------------
if( !$roster->auth->allow_login )
{
	print
	'<span class="title_text">' . $roster->locale->act['user_page']['settings'] . '</span><br />'.
	$roster->auth->getMessage().
	$roster->auth->getLoginForm();
	return;
}
// ----[ End Check log-in ]---------------------------------

define('IN_ROSTER_ADMIN',true);

include_once(ROSTER_UCP . 'pages.php');

$header = $menu = $footer = $body = '';

// Find out what subpage to include, and do so
$page = (isset($roster->pages[1]) && ($roster->pages[1]!='')) ? $roster->pages[1] : 'user';

if( isset($config_pages[$page]['file']) )
{
	if (file_exists(ROSTER_UCP . $config_pages[$page]['file']))
	{
		require_once(ROSTER_UCP . $config_pages[$page]['file']);
	}
	else
	{
		$body = messagebox(sprintf($roster->locale->act['roster_cp_not_exist'], $page), $roster->locale->act['roster_cp'], 'sred');
	}
}
else
{
	$body = messagebox($roster->locale->act['roster_cp_invalid'], $roster->locale->act['roster_cp'], 'sred');
}

// Build the pagebar from admin/pages.php
foreach( $config_pages as $pindex => $data )
{
	$pagename = $roster->pages[0] . ( $page != 'roster' ? '-' . $page : '' );

	if( !isset($data['special']) || $data['special'] != 'hidden' )
	{
		$roster->tpl->assign_block_vars('pagebar',array(
			'SPECIAL' => ( isset($data['special']) ? $data['special'] : '' ),
			'SELECTED' => ( isset($data['href']) ? ($pagename == $data['href'] ? true : false) : ''),
			'LINK' => ( isset($data['href']) ? makelink($data['href']) : '' ),
			'NAME' => ( isset($data['title']) ? ( isset($roster->locale->act[$data['title']]) ? $roster->locale->act[$data['title']] : $data['title'] ) : '' ),
			)
		);
	}
}

// Refresh the addon list because we may have installed/uninstalled something
$roster->get_addon_data();

$roster->tpl->assign_var('ADDON_PAGEBAR',(bool)count($roster->addon_data));

foreach( $roster->addon_data as $row )
{
	$addon = getaddon($row['basename']);

	updateCheck($addon);

	if( file_exists($addon['ucp_dir'] . 'index.php'))
	{
		// Save current locale array
		// Since we add all locales for localization, we save the current locale array
		// This is in case one addon has the same locale strings as another, and keeps them from overwritting one another
		$localetemp = $roster->locale->wordings;

		foreach( $roster->multilanguages as $lang )
		{
			$roster->locale->add_locale_file(ROSTER_ADDONS . $row['basename'] . DIR_SEP . 'locale' . DIR_SEP . $lang . '.php',$lang);
		}

		$roster->tpl->assign_block_vars('addon_pagebar',array(
			'SELECTED' => (isset($roster->pages[2]) && $roster->pages[2] == $row['basename'] ? true : false),
			'LINK' => makelink('ucp-addon-' . $row['basename']),
			'NAME' => ( isset($roster->locale->act[$row['fullname']]) ? $roster->locale->act[$row['fullname']] : $row['fullname'] ),
			)
		);

		// Restore our locale array
		$roster->locale->wordings = $localetemp;
		unset($localetemp);
	}
}


// ----[ Render the page ]----------------------------------

// Generate a title, so the user knows where they are at in RosterCP
$rostercp_title = $roster->locale->act['roster_ucp_ab'];
if( isset($roster->pages[1]) )
{
	if( $roster->pages[1] == 'addon' )
	{
		$fullname = $roster->addon_data[$roster->pages[2]]['fullname'];
		$rostercp_title = ( isset($roster->locale->act[$fullname]) ? $roster->locale->act[$fullname] : $fullname );
	}
	elseif( $roster->pages[1] != '' )
	{
		$rostercp_title = ( isset($config_pages[$roster->pages[1]]['title']) ?
		( isset($roster->locale->act[$config_pages[$roster->pages[1]]['title']]) ? $roster->locale->act[$config_pages[$roster->pages[1]]['title']] : $config_pages[$roster->pages[1]]['title'] ) : '' );
	}
}

$roster->tpl->assign_vars(array(
	'ROSTERCP_TITLE'  => (!empty($rostercp_title) ? $rostercp_title : $roster->locale->act['roster_ucp_ab']),
	'HEADER' => $header,
	'MENU' => $menu,
	'BODY' => $body,
	'PAGE_INFO' => $roster->locale->act['roster_ucp'],
	'FOOTER' => $footer,
	)
);

$roster->tpl->set_filenames(array('ucp' => 'ucp.html'));
$roster->tpl->display('ucp');
