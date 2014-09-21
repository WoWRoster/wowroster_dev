<?php
/**
 * WoWRoster.net WoWRoster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license	http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version	SVN: $Id: url.php 2631 2014-08-21 17:54:35Z ulminia@gmail.com $
 * @link	   http://www.wowroster.net
 * @since	  File available since Release 2.2.0
 * @package	WoWRoster
 */

class url {

	/**********************************************
	*	This function builds the urls for the function calls
	* @params
	* $ui - the url for the api ie us.battle.net/api/wow/
	* $class - the type of data being collected
	* $server - the name of the server the info it comming from
	* $name - iter character guild or team name
	* $fields - extra data used for additional info multiple realm names
	*			character event calls and team size
	**********************************************/
	public function BuildUrl($ui,$class,$server,$name,$fields)
	{
		global $roster;
		$name = str_replace('+' , '%20' , urlencode($name));
		$server = str_replace('+' , '%20' , urlencode($server));
		$local = 'locale='.$roster->config['api_url_locale'];

		switch ($class)
		{
			case 'character':
				$q = $ui.'api/wow/character/'.$server.'/'.$name.$fields['data'].'&'.$local;
			break;
			case 'status':
				$q = $ui.'api/wow/realm/status?'.$fields['data'].'';
			break;
			case 'guild':
				$q = $ui.'api/wow/guild/'.$server.'/'.$name . $fields['data'].'&'.$local;
			break;
			case 'team':
				$q = $ui.'api/wow/arena/'.$fields['server'].'/'.$fields['size'].'/'.$fields['name'].'?'.$local;
			break;
			
			case 'item':
				$q = $ui.'api/wow/item/'.$name.'?'.$local;
			break;
			
			case 'itemClass':
				$q = $ui.'api/wow/data/item/classes?'.$local;
			break;
			
			case 'itemSet':
				$q = $ui.'api/wow/item/set/'.$name.'?'.$local;
			break;
			
			case 'recipe':
				$q = $ui.'api/wow/recipe/'.$name.'?'.$local;
			break;
			
			case 'achievement':
				$q = $ui.'api/wow/achievement/'.$name.'?'.$local;
			break;
			
			case 'gperks':
				$q = $ui.'api/wow/data/guild/perks?'.$local;
			break;
			
			case 'gachievements':
				$q = $ui.'api/wow/data/guild/achievements?'.$local;
			break;
			case 'grewards':
				$q = $ui.'api/wow/data/guild/rewards?'.$local;
			
			case 'races':
				$q = $ui.'api/wow/data/character/races?'.$local;
			break;

			case 'achievements':
				$q = $ui.'api/wow/data/character/achievements?'.$local;
			break;
			
			case 'quests':
				$q = $ui.'/api/wow/quest/'.$name.'?'.$local;
			break;
			
			case 'ladder':
				$q = $ui.'/api/wow/leaderboard/'.$fields['size'].'?'.$local;
			break;
			
			case 'talent':
				$q = $ui.'/api/wow/data/talents?'.$local;
			break;

			case 'abilities':
				$q = $ui.'/api/wow/battlePet/ability/'.$name.'?'.$local;
			break;

			case 'species':
				$q = $ui.'/api/wow/battlePet/species/'.$name.'?'.$local;
			break;

			case 'stats':
				$q = $ui.'/api/wow/battlePet/stats/'.$name.'?'.$local;
			break;
			
			case 'spell':
				$q = $ui.'/api/wow/spell/'.$name.'?'.$local;
			break;
			
			case 'challenge':
				$q = $ui.'/api/wow/challenge/'.$name.'?'.$local;
			break;
			
			case 'auction':
				$q = $ui.'api/wow/auction/data/'.$fields['server'];
			break;
			
			case 'repo':
				$q = 'https://api.github.com/repos/ulminia/wowroster/git/refs';
			break;
			
			case 'files':
				$q = 'https://api.github.com/repos/ulminia/wowroster/git/trees/'.$fields['name'].'?recursive=1';
			break;
			
			case 'file':
				$q = $fields['name'];
			break;
			
			default:
			break;
		}

		return $q;
	}




}