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
if( isset($_POST['process']))
{
	if ($_POST['process'] == 'addgroup' )
	{
		$data = array(
			'group_name'	=> $_POST['name'],
			'group_desc'	=> $_POST['desc'],
		);
		$query = 'INSERT INTO `' . $roster->db->table('user_groups') . '` ' . $roster->db->build_query('INSERT', $data);
		$result = $roster->db->query($query);
	}
}
$type = (isset($_GET['type']) ? $_GET['type'] : '');

if ($type == 'delete')
{
	$adm_query = "DELETE FROM `" . $roster->db->table('user_groups') . "` WHERE `group_id` = '".$_GET['id']."'";
	$adm_result = $roster->db->query($adm_query);
	$roster->db->free_result($adm_result);
	$type = '';
}
if ($type == '')
{
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
		$queryb = "SELECT COUNT(`id`) AS `count` FROM `" . $roster->db->table('user_members') . "` WHERE `group_id` = '".$row['group_id']."' ";
		$resultsb = $roster->db->query($queryb);
		$c = $roster->db->fetch($resultsb);
		
		$roster->tpl->assign_block_vars('groups', array(
			'ID'		=> $row['group_id'],
			'NAME'		=> $row['group_name'],
			'MEM'		=> $c['count'],
			'DELURL'	=> makelink('&amp;type=delete&amp;id='.$row['group_id']),
			)
		);
	}
	$roster->db->free_result($dm_result);
}

$roster->tpl->assign_vars(array(
		'L_USER_MANAGER'	=> $roster->locale->act['admin']['user_groups_desc'],
		'ADDGROUP'			=>  makelink('&amp;type=add'),
		'ADDGROUPF'			=>  makelink('&amp;type='),
		'GPTYPE'			=>  $type,
	)
);

$roster->tpl->set_filenames(array('body' => 'admin/user_groups.html'));
$body = $roster->tpl->fetch('body');
