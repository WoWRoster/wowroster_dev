<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Roster upload rule config
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: user_manager.php 2603 2012-09-01 23:24:03Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
 * @subpackage RosterCP
*/

if( !defined('IN_ROSTER') || !defined('IN_ROSTER_ADMIN') )
{
	exit('Detected invalid access to this file!');
}

$start = (isset($_GET['start']) ? $_GET['start'] : 0);

$roster->output['title'] .= $roster->locale->act['pagebar_userman'];


//user_desc
if( isset($_POST['process']) && $_POST['process'] == 'process' )
{
	//echo '<pre>';print_r($_POST);echo '</pre>';
		if (isset($_POST['delete']))
		{
			foreach ($_POST['delete'] as $user => $id)
			{
				$dl_query = "DELETE FROM `" . $roster->db->table('user_members') . "` WHERE `id` = '".$id."';";
				$dl_result = $roster->db->query($dl_query);
				$dla_query = "DELETE FROM `" . $roster->db->table('user_link', 'user') . "` WHERE `uid` = '".$id."';";
				$dla_result = $roster->db->query($dla_query);
			}
		}
	foreach ($_POST as $name => $value)
	{
		$query = $access = '';
		$ad = array();

		if ($name != 'action' && $name != 'process')
		{

			$name = substr($name, 7);
			$a=$b='';
			if (isset($value['access']))
			{
				$access = implode(":",$value['access']);
				$a = "`access` = '".$access."' ";
			}
			if (isset($value['active']))
			{
				$b = ", `active` = '".$value['active']."' ";
			}
			if ($name != '')
			{
				$up_query = "UPDATE `" . $roster->db->table('user_members') . "` SET $a $b WHERE `id` = '".$name."';";
				$up_result = $roster->db->query($up_query);
			}
		}
	}
}

// Change scope to guild, and rerun detection to load default
//print_r($roster->auth->rosterAccess());
// Get the scope select data
$dm_query = "SELECT * FROM `" . $roster->db->table('user_groups') . "` ORDER BY `group_id` ASC";

$dm_result = $roster->db->query($dm_query);
$x = '';
if( !$dm_result )
{
	die_quietly($roster->db->error(), 'Database error', __FILE__, __LINE__, $dm_query);
}

$c = 1;
while( $row = $roster->db->fetch($dm_result) )
{
	$roster->tpl->assign_block_vars('group', array(
		'ID'		=> $row['group_id'],
		'NAME'		=> $row['group_name'],
		'MEM'		=> '',
		'SETURL'	=> makelink('&amp;action=settings&amp;id='.$row['group_id']),
		'DELURL'	=> makelink('&amp;action=delete&amp;id='.$row['group_id']),
		)
	);
}

$roster->db->free_result($dm_result);
$roster->tpl->assign_vars(array(
  'L_USER_MANAGER' => $roster->locale->act['admin']['user_groups_desc'],
	)
);

$roster->tpl->set_filenames(array('body' => 'admin/user_groups.html'));
$body = $roster->tpl->fetch('body');
