<?php
/**
 * WoWRoster.net WoWRoster
 *
 * @copyright  2002-2011 WoWRoster.net
 * @license    http://www.gnu.org/licenses/gpl.html   Licensed under the GNU General Public License v3.
 * @version    SVN: $Id: Data.php 2631 2014-08-21 17:54:35Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 2.2.0
 * @package    WoWRoster
 */
require_once 'Resource.php';

/**
 * Realm resource.
 *
 * @throws ResourceException If no methods are defined.
 */
class Git extends Resource {

	protected $region;
	
	protected $methods_allowed = array(
		'repo',
		'files'
	);

	public function GetRepo()
	{
		$data = $this->consume('repo', array(
			'data' => '',
			'type' => 'GET',
			'server' => '',
			'name' => ''
			));
		return $data;
	}
	
	public function GetFiles($sha)
	{
		$data = $this->consume('files', array(
			'data' => '',
			'dataa' => 'talent',
			'server' => '',
			'name' => $sha
			));
		return $data;
	}
	
}
