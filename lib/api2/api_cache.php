<?php
//namespace OAuth2;
class apicache {

	public function character()
	{
		//echo 'i exist';
	}
	
	public function item($parameters,$usage)
	{
		return $this->CacheCheck($parameters['id'],$parameters);
	}
	
	public function insertitem($data,$vars,$parameters)
	{
		if ($data['itemClass'] == '3')
		{
			$this->InsertGCache($data);
		}
		else
		{
			$this->InsertICache($data,$parameters);
		}
	}
	
	public function api_track($method, $url, $responce_code, $content_type)
	{
		global $roster;
		
		$q = "SELECT * FROM `" . $roster->db->table('api_usage') . "` WHERE `date`='".date("Y-m-d")."' AND `type` = '".$method."'";
		$y = $roster->db->query($q);
		$row = $roster->db->fetch($y);
		if (!isset($row['total']))
		{
			$data = array(
				'type'				=> $method,
				'url'				=> $url,
				'responce_code'		=> $responce_code,
				'content_type'		=> $content_type,
				'date'				=> date("Y-m-d"),
				'total'				=> '+1',
			);
			$query = 'INSERT INTO `' . $roster->db->table('api_usage') . '` ' . $roster->db->build_query('INSERT', $data);
		}
		else
		{
			$data = array(
				'type'				=> $method,
				'url'				=> $url,
				'responce_code'		=> $responce_code,
				'content_type'		=> $content_type,
				'date'				=> date("Y-m-d"),
				'total'				=> ($row['total']+1),
			);
			$query = "Update `" . $roster->db->table('api_usage') . "` SET " . $roster->db->build_query('UPDATE', $data) . " WHERE `type` = '".$method."' AND `url` = '".$url."'";
		}
		$ret = $roster->db->query($query);
	}

	public function InsertICache($data,$par)
	{
		global $roster, $update;
		$tooltip = $roster->api->Item->item($data,null,null);
		require_once (ROSTER_LIB . 'update.lib.php');
		$update = new update();
		$update->reset_values();
		$update->add_value('item_name' , $data['name']);
		$update->add_value('item_color' , $this->_setQualityc( $data['quality'] ));
		$update->add_value('item_id' , ''.$data['id'].'');
		$update->add_value('item_texture' , $data['icon']);
		$update->add_value('item_rarity' , $data['quality']);
		$update->add_value('item_tooltip' , $tooltip);
		$update->add_value('item_type' , $roster->api->Item->itemclass[$data['itemClass']]);
		$update->add_value('item_subtype' , $roster->api->Item->itemSubClass[$data['itemClass']][$data['itemSubClass']]);
		$update->add_value('level' , $data['requiredLevel']);
		$update->add_value('item_level' , $data['itemLevel']);
		$update->add_value('locale' , $roster->config['api_url_locale']);
		$update->add_value('timestamp' , time() );
		$update->add_value('context' , $par['context'] );
		$update->add_value('bonus' , $par['bl'] );
		$update->add_value('json' ,json_encode($data, true));
		$querystr = "INSERT INTO `" .$roster->db->table('api_items') . "` SET " . $update->assignstr;
		$result = $roster->db->query($querystr);
	}

	public function InsertGCache($data)
	{
		global $roster, $update;
		$tooltip = $roster->api->Item->item($data,null,null);
		$tooltip = str_replace('<br /><br />', "<br />", $tooltip);

		require_once (ROSTER_LIB . 'update.lib.php');
		$update = new update();
		$update->reset_values();
		$update->add_value('gem_id' , $data['id'] );
		$update->add_value('gem_name' , $data['name'] );
		$update->add_value('gem_color' , strtolower($data['gemInfo']['type']['type']) );
		$update->add_value('gem_tooltip' , $tooltip );
		$update->add_value('gem_texture' , $data['icon'] );
		$update->add_value('gem_bonus' , $data['gemInfo']['bonus']['name'] );
		$update->add_value('locale' , $roster->config['api_url_locale']);
		$update->add_value('timestamp' , time() );
		$update->add_value('json' ,json_encode($data, true));
		$querystr = "INSERT INTO `" .$roster->db->table('api_gems') . "` SET " . $update->assignstr;
		$result = $roster->db->query($querystr);
	}
	public function CacheCheck($id,$parameters)
	{
		global $roster;
		
		$p = '';
		if (isset($parameters['context']))
		{
			$p .= ' AND `context` = "'.$parameters['context'].'" ';
		}
		if (isset($parameters['bl']))
		{
			$p .= ' AND `bonus` = "'.$parameters['bl'].'" ';
		}
				
		$sql = "SELECT * FROM `" .$roster->db->table('api_items') . "` WHERE `item_id` = '".$id."' ".$p."";
		$result = $roster->db->query($sql);
		if ($roster->db->num_rows($result) == 0)
		{
			$sqlg = "SELECT * FROM `" .$roster->db->table('api_gems') . "` WHERE `gem_id` = '".$id."' ";
			$resultg = $roster->db->query($sqlg);
			if ($roster->db->num_rows($resultg) == 0)
			{
				return false;
			}
			else
			{
				$rowg = $roster->db->fetch($resultg);
				if (!$this->cachetime($rowg['timestamp']))
				{
					return json_decode($rowg['json'], true);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			$row = $roster->db->fetch($result);
			if ($this->cachetime($row['timestamp']) && isset($row['name']))
			{
				return json_decode($row['json'], true);
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	public function cachetime($cache)
	{
		$now = time();
		$t = (30*24*60*60);//1 month for store time
		$x = $now-$cache;
		if ($x >= $t)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	public function _setQualityc( $color )
	{
		$ret = '';
		switch ($color) {
			case 5: $ret = "ff8000"; //Orange
				break;
			case 4: $ret = "a335ee"; //Purple
				break;
			case 3: $ret = "0070dd"; //Blue
				break;
			case 2: $ret = "1eff00"; //Green
				break;
			case 1: $ret = "ffffff"; //White
				break;
			default: $ret = "9d9d9d"; //Grey
				break;
		}
		return $ret;
	}
	
	public function InsertSpellCache($data)
	{
		global $roster, $update;
		require_once (ROSTER_LIB . 'update.lib.php');
		$update = new update();
		//Enchant Chest - Glorious Stats
		$a = explode(" - ", $data['name']);
		$b = explode(" ",$a[0]);
		print_r($b);echo '<br>';
		if ($b[1] == '2H')
		{
			$slot = $b[2];
		}
		else
		{
			$slot = $b[1];
		}
		
		$update->reset_values();
		$update->add_value('name' , $data['name']);
		$update->add_value('id' , ''.$data['id'].'');
		$update->add_value('icon' , $data['icon']);
		$update->add_value('slot' , $slot);
		$update->add_value('description' , $data['description']);
		$update->add_value('castTime' , $data['castTime']);
		$querystr = "REPLACE INTO `" .$roster->db->table('api_enchant') . "` SET " . $update->assignstr;
		$result = $roster->db->query($querystr);
	}
	
	public function _insertcache($data, $vars, $parameters)
	{
		global $roster, $update;
		require_once (ROSTER_LIB . 'update.lib.php');
		$update = new update();
		
		$update->reset_values();
		
		$update->add_value('id',		$parameters['id']);
		$update->add_value('type',		$vars['type']);
		$update->add_value('timestamp',	time());
		$update->add_value('name',		'');
		$update->add_value('locale',	$vars['locale']);
		$update->add_value('json',		json_encode($data, true));
		
		$querystr = "REPLACE INTO `" .$roster->db->table('api_cache') . "` SET " . $update->assignstr;
		$result = $roster->db->query($querystr);
	}
	public function _cachecheck($id)
	{
		global $roster;
		
		$sql = "SELECT * FROM `" .$roster->db->table('api_cache') . "` WHERE `id` = '".$id."' ";
		$result = $roster->db->query($sql);
		if ($roster->db->num_rows($result) == 0)
		{
			return false;
		}
		else
		{
			$row = $roster->db->fetch($result);
			if ($this->cachetime($row['timestamp']))
			{
				return json_decode($row['json'], true);
			}
			else
			{
				return false;
			}
		}
		return false;
	}
	
	
	
	public function achievement($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertachievement($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}

	public function abilities($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertabilities($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}

	public function species($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertspecies($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}

	public function stats($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertstats($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}

	public function item_set($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertitem_set($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}


	public function quest($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertquest($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}


	public function recipe($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertrecipe($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}

	public function spell($parameters,$usage)
	{
		return $this->_cachecheck($parameters['id']);
	}
	public function insertspell($data,$vars,$parameters)
	{
		$this->_insertcache($data,$vars,$parameters);
	}


}