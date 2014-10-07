<?php

class gachievements
{

	var $output;
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'gachievements';
	var $filename = 'main-guild-gachievements.php';
	var $parent = 'main';
	var $icon = 'achievement_general';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Guild Achievements Summary';
	var $description = 'shows the guild achievements.';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"Feeds (Alpha Release)"),
	);
	
	public function __construct()
	{
		global $roster;
		
		$catss = array();
		$cats = array();
		$ach = array();		
		$query =	"SELECT * FROM `" . $roster->db->table('g_achie', 'achievements') . "` WHERE (`factionId` = '0' OR `factionId` = '2') ORDER BY `c_id` DESC ";
		$ret = $roster->db->query($query);
		
		while( $row = $roster->db->fetch($ret) )
		{
			if ($row['p_id'] =='-1')
			{
				$catss[$row['c_id']][$row['achie_id']] = array(
															'name' => $row['achie_name'],
															'id' => $row['c_id'],
															'comp' => 0,
															);
				$cats[$row['achie_id']] = array(
													'name' => $row['achie_name'],
													'cid' => $row['c_id'],
													'comp' => 0,
													);
				$ach[$row['c_id']]['total'] = isset($ach[$row['c_id']]['total']) ? ($ach[$row['c_id']]['total']+1) : 1; 
			}
			else
			{
				$ach[$row['p_id']]['total'] = isset($ach[$row['p_id']]['total']) ? ($ach[$row['p_id']]['total']+1) : 1; 
				
				$catss[$row['p_id']][$row['achie_id']] = array(
															'name' => $row['achie_name'],
															'id' => $row['c_id'],
															'comp' => 0,
															);
				$cats[$row['achie_id']] = array(
													'name' => $row['achie_name'],
													'cid' => $row['p_id'],
													'comp' => 0,
													);
			}

		}
		$sqlquery2 = "SELECT * FROM `" . $roster->db->table('g_achievements', 'achievements') . "` WHERE `member_id` = '".$roster->data['guild_id']."' ORDER BY `achie_date` DESC";
		$result2 = $roster->db->query($sqlquery2);
		$e = 0;
		while($rowx = $roster->db->fetch($result2))
		{
			$cats[$rowx['achie_id']]['comp'] = 1;
		}


		foreach ($cats as $achID => $data)
		{
			$ach[$data['cid']]['comp'] = isset($ach[$data['cid']]['comp']) ? ($ach[$data['cid']]['comp']+$data['comp']) : $data['comp']; 
		}
	

		$achcat = array(
			'15088' => 'General',
			'15077' => 'Quests',
			'15078' => 'Player vs. Player',
			'15079' => 'Dungeons &amp; Raids',
			'15080' => 'Professions',
			'15089' => 'Reputation',
			'15093' => 'Guild Feats of Strength',
		);

		$output = '';
		$et = 0;
		$ct = 0;
		foreach($ach as $cat => $t)
		{
			$text = $t['comp'].' / '.$t['total'];
			$per = $t['comp']/$t['total']*100;
			$et = ($et+$t['total']);
			$ct = ($ct+$t['comp']);
			$output .= '
			<div >'.$achcat[$cat].'</div>
			<div  style="cursor:default;">
				<div class="levelbarParent" style="width:200px;">
					<div class="levelbarChild">'.$text.'</div>
				</div>
				<table class="expOutline" border="0" cellpadding="0" cellspacing="0" width="200">
					<tr>
						<td style="background-image: url(\'img/honored.gif\');" width="'.$per.'%">
							<img src="img/pixel.gif" height="16" width="1" alt="" />
						</td>
						<td width="'.(100-$per).'%"></td>
					</tr>
				</table>
			</div>';
		}
		$xper = $ct/$et*100;
		$this->output = '
			<div >Total Completed</div>
			<div  style="cursor:default;">
				<div class="levelbarParent" style="width:200px;">
					<div class="levelbarChild">'.$ct.' / '.$et.'</div>
				</div>
				<table class="expOutline" border="0" cellpadding="0" cellspacing="0" width="200">
					<tr>
						<td style="background-image: url(\'img/honored.gif\');" width="'.$xper.'%">
							<img src="img/pixel.gif" height="16" width="1" alt="" />
						</td>
						<td width="'.(100-$xper).'%"></td>
					</tr>
				</table>
			</div>';
		$this->output .= $output;
	}
}