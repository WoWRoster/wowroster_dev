<?php
/**
 * WoWRoster.net WoWRoster
 *
 *
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @link       http://www.wowroster.net
 * @package    EasyMembersList
 * @subpackage easymemberList Class
 */

if ( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

/**
 * new_memberList generation class
 *
 * @package    easymemberslist
 * @subpackage easymembersList Class
 */
class easymemberslist
{
    var $roster = null;
    var $addon = null;
    
    function __construct()
    {
        global $roster, $addon;
        $this->roster = &$roster;
        $this->addon = &$addon;    
    }
    
    function get_guildranks()
    {
        $sql = "SELECT `guild_rank`, `guild_title` FROM `".$this->roster->db->table('members')."` GROUP BY `guild_rank`";
        $result = $this->roster->db->query($sql);
        $data = $this->roster->db->fetch_all($result);
        return $data;        
    }
    

    function get_members($order_by = 'm.name ASC', $where = null)
    {
        if (isset($where) && !empty($where)) 
            $where = 'WHERE '.$where;
        else
            $where = null;
        $sql = "SELECT * FROM `".$this->roster->db->table('members')."` as m LEFT JOIN `".$this->roster->db->table('players')."` as p ON p.member_id = m.member_id ".$where." ORDER BY ".$order_by;
        $result = $this->roster->db->query($sql);
		$data = $this->roster->db->fetch_all($result);
        return $data;
    }
    
    function get_character_skills($member_id)
    {
        $sql = "SELECT * FROM `".$this->roster->db->table('skills')."` WHERE `member_id` = ".intval($member_id)." ORDER BY `skill_order`;";
        $result = $this->roster->db->query($sql);
        $data = $this->roster->db->fetch_all($result);
        return $data;        
    }
    
    function get_param($param_name, $param_type = 'string', $method = 'request', $default_value = null)
    {
        if ($param_type == 'integer') $param_type = 'int';
        $method = strtolower($method);
        $param_type = strtolower($param_type);
        switch ($method)
        {
            default:
                /* REQUEST */
                switch ($param_type)
                {
                    default:
                        /* STRING */
                        $return = (isset($_REQUEST[$param_name]) && !empty($_REQUEST[$param_name])) ? (string) $_REQUEST[$param_name] : (string) $default_value;
                        break;
                    
                    case 'int':
                        /* INTEGER */
                        $return = (isset($_REQUEST[$param_name]) && !empty($_REQUEST[$param_name])) ? intval($_REQUEST[$param_name]) : intval($default_value);
                        break;
                    
                    case 'bool':
                        $return = (isset($_REQUEST[$param_name]) && !empty($_REQUEST[$param_name])) ? (bool) $_REQUEST[$param_name] : (bool) $default_value;
                        break;                    
                }
                break;
        
            case 'post':
                /* POST */
                switch ($param_type)
                {
                    default:
                        /* STRING */
                        $return = (isset($_POST[$param_name]) && !empty($_POST[$param_name])) ? (string) $_POST[$param_name] : (string) $default_value;
                        break;
                    
                    case 'int':
                        /* INTEGER */
                        $return = (isset($_POST[$param_name]) && !empty($_POST[$param_name])) ? intval($_POST[$param_name]) : intval($default_value);
                        break;
                    
                    case 'bool':
                        $return = (isset($_POST[$param_name]) && !empty($_POST[$param_name])) ? (bool) $_POST[$param_name] : (bool) $default_value;
                        break;                    
                }
                break;

            case 'get':
                /* GET */
                switch ($param_type)
                {
                    default:
                        /* STRING */
                        $return = (isset($_GET[$param_name]) && !empty($_GET[$param_name])) ? (string) $_GET[$param_name] : (string) $default_value;
                        break;
                    
                    case 'int':
                        /* INTEGER */
                        $return = (isset($_GET[$param_name]) && !empty($_GET[$param_name])) ? intval($_GET[$param_name]) : intval($default_value);
                        break;
                    
                    case 'bool':
                        $return = (isset($_GET[$param_name]) && !empty($_GET[$param_name])) ? (bool) $_GET[$param_name] : (bool) $default_value;
                        break;                    
                }
                break;
        }
        return $return;
    }
    
    function get_profession_icon($profession_name, $region)
    {
        $profession_to_en['EU'] = array(
                                    'Schmiedekunst' => 'Blacksmithing',
                                    'Bergbau' => 'Mining',
                                    'Kräuterkunde' => 'Herbalism',
                                    'Alchemie' => 'Alchemy',
                                    'Lederverarbeitung' => 'Leatherworking',
                                    'Juwelierskunst' => 'Jewelcrafting',
                                    'Kürschnerei' => 'Skinning',
                                    'Archäologie' => 'Archaeology',
                                    'Schneiderei' => 'Tailoring',
                                    'Verzauberkunst' => 'Enchanting',
                                    'Ingenieurskunst' => 'Engineering',
                                    'Inschriftenkunde' => 'Inscription',
                                    'Runen schmieden' => 'Runeforging',
                                    'Kochkunst' => 'Cooking',
                                    'Angeln' => 'Fishing',
                                    'Erste Hilfe' => 'First Aid',
                                    'Gifte' => 'Poisons');
        $profession_to_en['US'] = array(
                                    'Blacksmithing' => 'Blacksmithing',
                                    'Mining' => 'Mining',
                                    'Herbalism' => 'Herbalism',
                                    'Alchemy' => 'Alchemy',
                                    'Leatherworking' => 'Leatherworking',
                                    'Jewelcrafting' => 'Jewelcrafting',
                                    'Skinning' => 'Skinning',
                                    'Archaeology' => 'Archaeology',
                                    'Tailoring' => 'Tailoring',
                                    'Enchanting' => 'Enchanting',
                                    'Engineering' => 'Engineering',
                                    'Inscription' => 'Inscription',
                                    'Runeforging' => 'Runeforging',
                                    'Cooking' => 'Cooking',
                                    'Fishing' => 'Fishing',
                                    'First Aid' => 'First Aid',
                                    'Poisons' => 'Poisons');        
        if (isset($profession_to_en[$region][$profession_name]) && !empty($profession_to_en[$region][$profession_name]))
            return $this->roster->locale->act['ts_iconArray'][$this->roster->locale->act[$profession_to_en[$region][$profession_name]]];
        else
        {
            $prof_id = $this->roster->locale->act['prof_to_id'][$profession_name];
            if (isset($this->roster->locale->act['ts_id_icon'][$prof_id]) && !empty($this->roster->locale->act['ts_id_icon'][$prof_id])) return $this->roster->locale->act['ts_id_icon'][$prof_id];
            else return null;
        }
    }
}