<?php
	
class rb_mInstall
{
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'rb_m';
	var $filename = 'rb_m.php';
	var $parent = 'main';
	var $scope = 'guild';
	var $icon = 'inv_misc_note_05';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Raid Box modual';
	var $description = 'displays a raid box on the main guild cms page';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"Feeds (Alpha Release)"),
	);
	
	
		/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
			  
		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
	     global $installer,$addon,$roster;
		return true;

	}

	/**
	 * Un-Install Function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer, $addon;

		return true;
	}
	
	
}

?>