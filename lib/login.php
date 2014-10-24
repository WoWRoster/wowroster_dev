<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Login and authorization
 *
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: login.php 2631 2014-08-21 17:54:35Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.7.1
 * @package    WoWRoster
 * @subpackage User
*/

if( !defined('IN_ROSTER') )
{
	exit('Detected invalid access to this file!');
}

define('ROSTERLOGIN_ADMIN',11);

/**
 * Login and authorization
 *
 * @package    WoWRoster
 * @subpackage User
 */
class RosterLogin
{
	var $allow_login;
	var $message;
	var $action;
	var $logout;
	var $uid;
	var $levels = array();
	var $valid = 0;
	var $radid = 55;
	var $approved;
	var $access = 0;
	var $user = array();
	var $groups = array();

	/**
	 * Constructor for Roster Login class
	 * Accepts an action for the form
	 * And an array of additional fields
	 *
	 * @param $script_filename
	 * @return RosterLogin
	 */
	function RosterLogin( $script_filename='' )
	{
		global $roster;
		
		$this->getgroups();
		
		$this->setAction($script_filename);
		if( isset( $_POST['logout'] ) && $_POST['logout'] == '1' )
		{
			$this->endSession($this->getUID($_COOKIE['roster_user'], $_COOKIE['roster_pass']));
			setcookie('roster_user',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_u',        '0',  time() + (60*60*24*30),     ROSTER_PATH);
			setcookie('roster_pass',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_remember', NULL, time() - (60*60*24*30*100), ROSTER_PATH);

			$this->allow_login = false;
			$this->valid = 0;
			$this->uid = 0;
			//
			$this->message = $roster->locale->act['logged_out'] . $this->getLoginForm();
			$this->getLoginForm();
		}
		elseif( isset($_POST['password']) && $_POST['password'] != '' && isset($_POST['username']) && $_POST['username'] != '')
		{
			$this->checkPass(md5($_POST['password']), $_POST['username'],'1');
			return;
		}
		elseif( isset($_COOKIE['roster_pass']) && isset($_COOKIE['roster_user']) )
		{
			$this->checkPass($_COOKIE['roster_pass'], $_COOKIE['roster_user'],'0');
			return;
		}
		else
		{
			$this->allow_login = false;
			$this->message = '';
			setcookie('roster_user',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_u',        '0',  time() + (60*60*24*30),     ROSTER_PATH);
			setcookie('roster_pass',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_remember', NULL, time() - (60*60*24*30*100), ROSTER_PATH);
		}
	}
	function endSession($uid=null)
	{
		global $roster;
		if ($uid == '')
		{
			$uid = $this->getuserid();
		}
		$query = "DELETE FROM `".$roster->db->table('sessions')."` WHERE `session_user_id`='". $uid ."'";
		$roster->db->query($query);
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params['path'], $params['domain'],
			$params['secure'], $params['httponly']
		);

		// Finally, destroy the session.
		session_destroy();

	}

	function checkPass( $pass, $user, $createsession )
	{
		global $roster;

		$query = "SELECT * FROM `" . $roster->db->table('user_members') . "` WHERE `usr`='" . $user . "' AND `pass`='" . $pass . "' LIMIT 1;";
		$result = $roster->db->query($query);
		//echo (bool)$result;
		$row = $roster->db->fetch($result);
		$count = $roster->db->num_rows($result);
		//echo $count;

		if( $count == 0 )
		{
			setcookie('roster_user',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_u',        '0',  time() + (60*60*24*30),     ROSTER_PATH);
			setcookie('roster_pass',     NULL, time() - (60*60*24*30*100), ROSTER_PATH);
			setcookie('roster_remember', NULL, time() - (60*60*24*30*100), ROSTER_PATH);

			$this->allow_login = false;
			$this->valid = 0;
			$this->message = $roster->locale->act['login_fail'];
			return false;
		}

		if( $count == 1 )
		{
			$remember = (isset($_POST['rememberme']) ? (int)$_POST['rememberme'] : (int)$_COOKIE['roster_remember'] );
			setcookie('roster_user',     $user,      time() + (60*60*24*30), ROSTER_PATH);
			setcookie('roster_u',        $row['id'], time() + (60*60*24*30), ROSTER_PATH);
			setcookie('roster_pass',     $pass,      time() + (60*60*24*30), ROSTER_PATH);
			setcookie('roster_remember', $remember,  time() + (60*60*24*30), ROSTER_PATH);

			$this->valid = 1;
			$this->uid = $row['id'];
			$this->allow_login = true;
			$this->user = $row;
			if ($row['active'] != 1)
			{
				//roster_die('Your account is nto active or has not been approved by the admin only "Public" areas can be accessed');
				$roster->set_message($roster->locale->act['login_inactive'], 'Roster Auth', 'error');
				$this->access = '0:';
			}
			else
			{
				$this->access = $row['access'];
			}

			$this->message = sprintf($roster->locale->act['welcome_user'], $user);
			$roster->db->free_result($result);

			return true;
		}

		$roster->db->free_result($result);

		setcookie('roster_u', '0', time() + 60*60*24*30, ROSTER_PATH);
		$this->allow_login = false;
		$this->message = $roster->locale->act['login_invalid'];
		return;
	}

	function _getAuthorized( $access )
	{
		global $roster;

		$this->approved = false;
		$addon = array();
		$addon = explode(":",$access);
		$user = array();
		$user = explode(":",$this->access);
		foreach ($user as $x => $ac)
		{
			foreach ($addon as $a => $as)
			{
				if ( (int)$as ==  (int)$ac)
				{
					return true;
				}
			}
		}
		//$roster->set_message( sprintf($roster->locale->act['addon_no_access'], $roster->pages[0]), 'Roster Auth', 'warning');
		return false;
	}

	function getAuthorized( $access )
	{
		$a = explode(":",$access);
		if($access == '0' OR count($a) > 1 )
		{
			return $this->_getAuthorized( $access );
		}
		$pass = false;
		$up = json_decode($this->user['user_permissions'],true);
		$groups = explode(':',$this->access);
		// check user groups if opne matches it passes fails are not held
		// retuens on true
		foreach ($groups as $id)
		{
			if(isset($this->groups[$access]) && $this->groups[$access] == 1)
			{
				return true;
			}
		}
		if(isset($up[$access]) && $up[$access] == 1)
		{
			return true;
		}
		
		return false;
	}
	
	function getgroups()
	{
		global $roster;
		
		$dm_query = "SELECT * FROM `" . $roster->db->table('user_groups') . "` ORDER BY `group_id` ASC";

		$dm_result = $roster->db->query($dm_query);
		$g = array();
		while( $row = $roster->db->fetch($dm_result) )
		{
			$g[$row['group_id']] = $row;
		}
		$this->groups = $g;
	}
	
	function getMessage()
	{
		return $this->message;
	}

	function GetMemberLogin()
	{
		$this->getLoginForm();
	}
	function getLoginForm(  )
	{
		global $roster;

		if( !$this->allow_login && !isset($_POST['logout']) )
		{
			$roster->tpl->assign_vars(array(
				'U_LOGIN_ACTION'  => $this->action,
				'L_LOGIN_WORD'    => '',
				'S_LOGIN_MESSAGE' => (bool)$this->message,
				'L_LOGIN_MESSAGE' => $this->message,
				'L_REGISTER'      => '<a href="'.makelink('register').'" class="btn btn-block">'.$roster->locale->act['register'].'</a>',
				'U_LOGIN'         => 0
			));

			$roster->tpl->set_handle('roster_login', 'login.html');
			return $roster->tpl->fetch('roster_login');
		}
		else
		{
			return $this->getMessage();
		}
	}

	function getMenuLoginForm()
	{
		global $roster;

		$roster->tpl->assign_vars(array(
			'U_LOGIN_ACTION'  => $this->action,
			'S_LOGIN_MESSAGE' => (bool)$this->message,
			'L_LOGIN_MESSAGE' => $this->message,
			'L_REGISTER'      => '<a href="'.makelink('register').'" class="btn btn-block">'.$roster->locale->act['register'].'</a>',
			'U_LOGIN'         => $this->valid
		));

		$roster->tpl->set_handle('roster_menu_login', 'menu_login.html');
		return $roster->tpl->fetch('roster_menu_login');
	}

	function setAction( $action )
	{
		$this->action = makelink($action);
	}

	function makeAccess( $values )
	{
		trigger_error('$roster->auth->makeAccess is deprecated. Please update to use $roster->auth->rosterAccess.');
		$this->rosterAccess($values);
	}

	function rosterAccess( $values )
	{
		global $roster;

		// Only add levels if we have none stored
		if( count($this->levels) == 0 )
		{
			// Add built-in levels

			$query = "SELECT * FROM `" . $roster->db->table('user_groups') . "` ORDER BY `group_id` ASC";
			$result = $roster->db->query($query);

			if( !$result )
			{
				die_quietly($roster->db->error, 'Roster Auth', __FILE__,__LINE__,$query);
			}

			while( $row = $roster->db->fetch($result) )
			{
				$this->levels[$row['group_id']] = $row['group_name'];
			}
		}

		$name = $values['name'];
		$title = isset($values['title']) ? $values['title'] : $roster->locale->act['access_level'] .':';
		$this->radid++;

		$output = $title .' <select id="rad_config_'. $this->radid .'" name="config_'. $name .'[]" class="multiselect" multiple="multiple">';
		$lvl = explode(':', $values['value']);

		foreach ($this->levels as $acc => $a)
		{
			$output .= '<option value="'. $acc .'" '. (in_array($acc, $lvl) ? 'selected' : '') .'>'. ucfirst ($a) ."</option>\n";
		}
		$output .= '</select>';

		return $output;
	}

	function GetUserInfo($uid)
	{
		global $roster;
		
		$query = "SELECT * FROM `" . $roster->db->table('user_members') . "` WHERE `id`='" . $uid . "';";
		$result = $roster->db->query($query);
		//echo (bool)$result;
		$row = $roster->db->fetch($result);
		return $row;
	}
	
	function _ingroup( $groups, $user_group )
	{
		
		$this->approved = false;
		$addon = array();
		$addon = explode(":",$groups);
		$user = array();
		$user = explode(":",$user_group);
		foreach ($user as $x => $ac)
		{
			foreach ($addon as $a => $as)
			{
				if ( (int)$as ==  (int)$ac)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	function getUID($user, $pass)
	{
		global $roster;

		$query = "SELECT `id` FROM `" . $roster->db->table('user_members') . "` WHERE `usr`='".$user."' AND `pass`='".$pass."';";
		$result = $roster->db->query($query);
		$rows = $roster->db->num_rows($result);
		$row = $roster->db->fetch($result);

		if ($rows == 1)
		{
			return $row['id'];
		}
		else
		{
			return '0';
		}
	}

	function checkEMail($mail_address)
	{
		global $roster, $addon, $user;
		
		if (preg_match("/^[0-9a-z]+(([\.\-_])[0-9a-z]+)*@[0-9a-z]+(([\.\-])[0-9a-z-]+)*\.[a-z]{2,4}$/i", $mail_address))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getUUID( $d )
	{
		global $roster;

		return hash('ripemd128', $d);
	}

}
