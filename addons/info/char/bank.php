<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Displays character information
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3. * @package    CharacterInfo
 */

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

include( $addon['inc_dir'] . 'header.php' );

if( $roster->auth->getAuthorized($addon['config']['show_bank']) )
{
	$bag0 = bag_get( $char->get('member_id'), 'Bank Bag0' );
	if( !is_null( $bag0 ) )
	{
		$bag0->out();
	}

	$bag1 = bag_get( $char->get('member_id'), 'Bank Bag1' );
	if( !is_null( $bag1 ) )
	{
		$bag1->out();
	}

	$bag2 = bag_get( $char->get('member_id'), 'Bank Bag2' );
	if( !is_null( $bag2 ) )
	{
		$bag2->out();
	}

	$bag3 = bag_get( $char->get('member_id'), 'Bank Bag3' );
	if( !is_null( $bag3 ) )
	{
		$bag3->out();
	}

	$bag4 = bag_get( $char->get('member_id'), 'Bank Bag4' );
	if( !is_null( $bag4 ) )
	{
		$bag4->out();
	}

	$bag5 = bag_get( $char->get('member_id'), 'Bank Bag5' );
	if( !is_null( $bag5 ) )
	{
		$bag5->out();
	}

	$bag6 = bag_get( $char->get('member_id'), 'Bank Bag6' );
	if( !is_null( $bag6 ) )
	{
		$bag6->out();
	}

	$bag7 = bag_get( $char->get('member_id'), 'Bank Bag7' );
	if( !is_null( $bag7 ) )
	{
		$bag7->out();
	}
}

$roster->tpl->assign_var('L_BAG_TITLE', $roster->locale->act['bank']);

$roster->tpl->set_filenames(array('bag' => $addon['basename'] . '/bag.html'));

$roster->tpl->display('bag');
