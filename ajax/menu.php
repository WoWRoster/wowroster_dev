<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Roster ajax function for Roster menu
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @package    WoWRoster
 * @subpackage Ajax
*/

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

switch ($method)
{
	case 'menu_button_add':
		if( ! $roster->auth->getAuthorized( 'roster_cp' ) )
		{
			$status = 103;
			$errmsg = 'Not authorized';
			return;
		}

		if( isset($_POST['title']) )
		{
			$title = $_POST['title'];
		}
		else
		{
			$status = 104;
			$errmsg = 'Failed to insert button: Not enough data (no title given)';
			return;
		}

		if( isset($_POST['url']) )
		{
			$url = $_POST['url'];
		}
		else
		{
			$status = 104;
			$errmsg = 'Failed to insert button: Not enough data (no url given)';
			return;
		}

		if( isset($_POST['icon']) )
		{
			$icon = $_POST['icon'];
		}
		else
		{
			$status = 104;
			$errmsg = 'Failed to insert button: Not enough data (no icon given)';
			return;
		}

		if( isset($_POST['scope']) )
		{
			$scope = $_POST['scope'];
		}
		else
		{
			$status = 104;
			$errmsg = 'Failed to insert button: Not enough data (no scope given)';
			return;
		}

		$query = "INSERT INTO `" . $roster->db->table('menu_button') . "` VALUES (NULL,-1,'" . $title . "','" . $scope . "','" . $url . "','" . $icon . "');";

		$DBres = $roster->db->query($query);

		if (!$DBres)
		{
			$status = 101;
			$errmsg = 'Failed to insert button. MySQL said: ' . $roster->db->error();
			return;
		}

		if( !empty($icon) )
		{
			$icon = $roster->config['interface_url'] . 'Interface/Icons/' . $icon . '.' . $roster->config['img_suffix'];
		}
		else
		{
			$icon = $roster->config['interface_url'] . 'Interface/Icons/inv_misc_questionmark.' . $roster->config['img_suffix'];
		}

		$status=0;
		$result  = '<id>b' . $roster->db->insert_id() . "</id>\n";
		$result .= '<title>' . $title . '</title>';
		$result .= '<icon>' . $icon . '</icon>';

		break;

	case 'menu_button_del':
		if( ! $roster->auth->getAuthorized( 'roster_cp' ) )
		{
			$status = 103;
			$errmsg = 'Not authorized';
			return;
		}

		$button = $_POST['button'];
		$button_id = (int)substr($button,1);

		$query = "SELECT * FROM `" . $roster->db->table('menu_button') . "` WHERE `button_id` = '" . $button_id . "';";
		$DBres = $roster->db->query($query);

		if( !$DBres )
		{
			$status = 101;
			$errmsg = 'Failed to fetch button properties. MySQL said: ' . "\n" . $roster->db->error() . "\n" . $query;
			return;
		}

		if( $roster->db->num_rows($DBres) == 0 )
		{
			$status = 102;
			$errmsg = 'The specified button does not exist: ' . $button;
			return;
		}

		$row = $roster->db->fetch($DBres);

		$roster->db->free_result($DBres);

		if( $row['addon_id'] != '-1' )
		{
			$status = 105;
			$errmsg = 'You cannot delete non-user made buttons: ' . $button;
			return;
		}

		$query = "DELETE FROM `" . $roster->db->table('menu_button') . "` WHERE `button_id` = '" . $button_id . "';";

		$DBres = $roster->db->query($query);

		if (!$DBres)
		{
			$status = 101;
			$errmsg = 'Failed to delete button. MySQL said: ' . "\n" . $roster->db->error() . "\n" . $query;
			return;
		}

		$status = 0;
		$result = $button;
		break;
}
