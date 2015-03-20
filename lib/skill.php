<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Skill class and functions
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @package    WoWRoster
 * @subpackage Skill
*/

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * Skill class and functions
 *
 * @package    WoWRoster
 * @subpackage Skill
 */
class skill
{
	var $data;

	function skill( $data )
	{
		$this->data = $data;
	}

	function get( $field )
	{
		return $this->data[$field];
	}
}

function skill_get_many_by_type( $member_id, $type )
{
	global $roster;

	$type = $roster->db->escape( $type );

	return skill_get_many( $member_id, "`skill_type` = '$type'" );
}

function skill_get_many_by_order( $member_id, $order )
{
	global $roster;

	$order = $roster->db->escape( $order );

	return skill_get_many( $member_id, "`skill_order` = '$order'" );
}

function skill_get_many( $member_id )
{
	global $roster;

	if (isset($char))
	{
		$char = $roster->db->escape( $char );
	}
	if (isset($server))
	{
		$server = $roster->db->escape( $server );
	}
	$query= "SELECT * FROM `" . $roster->db->table('skills') . "` WHERE `member_id` = '$member_id';";

	$result = $roster->db->query( $query );

	$skills = array();
	while( $data = $roster->db->fetch( $result ) )
	{
		$skill = new skill( $data );
		$skills[$skill->data['skill_order']][] = $skill;
	}
	return $skills;
}
