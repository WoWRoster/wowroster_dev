<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3. * @package    MembersList
 * @subpackage MemberList Plugins
 */

class events
{

	var $output;
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'events';
	var $filename = 'main-guild-events.php';
	var $parent = 'main';
	var $icon = 'achievement_worldevent_childrensweek';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'WoW Events';
	var $description = 'Displays imgame wow events on your page.';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"Guild Rep (Alpha Release)"),
	);
	var $events = array(
		'Harvest Festival' => array( 'start' => '2011/09/06', 'end' => '2011/09/13'),
		'Pirates\' Day' => array( 'start' => '2011/09/19', 'end' => '2011/09/20'),
		'Brewfest' => array( 'start' => '2011/09/20', 'end' => '2011/10/05'),
		'Hallow\'s End' => array( 'start' => '2011/10/18', 'end' => '2011/10/31'),
		'Day of the Dead' => array( 'start' => '2011/11/01', 'end' => '2011/11/02'),
		'Pilgrim\'s Bounty' => array( 'start' => '2011/11/20', 'end' => '2011/11/26'),
		'Feast of Winter Veil' => array( 'start' => '2011/12/15', 'end' => '2012/01/02'),
		'Lunar Festival' => array( 'start' => '2012/01/22', 'end' => '2012/02/11'),
		'Love is in the Air' => array( 'start' => '2012/02/05', 'end' => '2012/02/20'),
		'Noblegarden' => array( 'start' => '2012/04/08', 'end' => '2012/04/15'),
		'Children\'s Week' => array( 'start' => '2012/04/29', 'end' => '2012/05/06'),
		'Midsummer Fire Festival' => array( 'start' => '2012/06/21', 'end' => '2012/07/05'),
		'Darkmoon Faire' => array( 'start' => '2012/05/06', 'end' => '2012/05/12'),
		'Fireworks Spectacular' => array( 'start' => '2012/07/04', 'end' => '2012/07/04')
	);
	var $config = array();
	/*
	*	__construct
	*	this is there the veriables for the addons are 
	*	set in the plugin these are unique to each addon 
	*
	*	contact the addon author is you have a sugestion 
	*	as to where plugin code should occure or use there descression
	*/
	
	public function __construct($pdata)
	{
		global $roster;
		
		$this->config = $pdata['config'];
		$this->display();
	}
	
	function getNEXTEVENT()
	{
		foreach ($this->events as $event_name => $event)
		{
			$dateParts = explode('/', $this->events[$event_name]['start']);
										//m					d				y
			$currentTime = time();
			$Time = mktime(0, 0, 0, $dateParts[1], $dateParts[2], $dateParts[0]);
			if($Time > $currentTime)
			{
				$image = str_replace(" ", "", $event_name);
				$image = str_replace("'", "", $image);
				
				return '<center>'.$event_name.'<br><img src="plugins\events'.DIR_SEP.$image.'.png"></a><br> Starts '.gmdate("M d Y", $Time).'</center>';
				break;
			}
		}


    return false;
	}
	function createDateRangeArray($start, $end) 
	{
		// Modified by JJ Geewax 

		$range = array();

		if (is_string($start) === true) $start = strtotime($start);
		if (is_string($end) === true ) $end = strtotime($end);

		if ($start > $end) return $this->createDateRangeArray($end, $start);

		do
		{
			$range[] = date('Y/m/d', $start);
			$start = strtotime("+ 1 day", $start);
		}
		while($start < $end);

		return $range;
	}
	
	function date_change($date)
	{
		$date = new DateTime($date);
		return $date->format('M j');
	}

	function display ()
	{
		global $roster;
		$output='';
		$even = false;
		foreach ($this->events as $event_name => $event)
		{
			//now see if its the current event
			$e = $this->createDateRangeArray($this->events[$event_name]['start'], $this->events[$event_name]['end']);
			if (in_array(date('Y/m/d'), $e)) 
			{
				$even = true;
				$image = str_replace(" ", "", $event_name);
				$image = str_replace("'", "", $image);

				$dateParts1 = explode('/', $this->events[$event_name]['start']);
				$dateParts2 = explode('/', $this->events[$event_name]['end']);
										//m					d				y
				$Time1 = mktime(0, 0, 0, $dateParts1[1], $dateParts1[2], $dateParts1[0]);
				$Time2 = mktime(0, 0, 0, $dateParts2[1], $dateParts2[2], $dateParts2[0]);
				$output .= '<center>'.$event_name.'<br><img src="plugins\events'.DIR_SEP.$image.'.png"></a><br> From: '.gmdate("M d Y", $Time1).'<br />To: '.gmdate("M d Y", $Time2).'</center>';

			}
		
		}
		$sql = "SELECT * FROM `".$roster->db->table('xxxxxx','events','plugins')."`";
		$resultx = $roster->db->query( $sql );
		if ($even)
		{
			$this->output = $output;
		}
		else
		{
			$this->output = $this->getNEXTEVENT();
		}
	}
	
}

	
?>