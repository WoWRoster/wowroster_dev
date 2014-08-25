<?php

$catss = array();
$cats = array();
$ach = array();		
		$query =	"SELECT * FROM `" . $roster->db->table('g_achie', $addon['basename']) . "` WHERE (`factionId` = '0' OR `factionId` = '2') ORDER BY `c_id` DESC ";
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
		$sqlquery2 = "SELECT * FROM `" . $roster->db->table('g_achievements', $addon['basename']) . "` WHERE `member_id` = '16' ORDER BY `achie_date` DESC";
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

	
		foreach($ach as $cat => $t)
		{
			$text = $t['comp'].'/'.$t['total'];
			$per = $t['comp']/$t['total']*100;
			echo '
			<div >'.$achcat[$cat].'</div>
			<div  style="cursor:default;">
				<div class="levelbarParent" style="width:150px;">
					<div class="levelbarChild">'.$text.'</div>
				</div>
				<table class="expOutline" border="0" cellpadding="0" cellspacing="0" width="150">
					<tr>
						<td style="background-image: url(\'img/honored.gif\');" width="'.$per.'%">
							<img src="img/pixel.gif" height="16" width="1" alt="" />
						</td>
						<td width="'.(100-$per).'%"></td>
					</tr>
				</table>
			</div>';
		}

