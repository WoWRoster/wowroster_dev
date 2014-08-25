<?php
	
class feedInstall
{
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'feed';
	var $filename = 'feed.php';
	var $parent = 'main';
	var $scope = 'guild';
	var $icon = 'inv_misc_note_05';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'Latest Activity Feeds';
	var $description = 'displays top X feeds from the database.';
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
		/*
		$installer->add_config("'1060','startpage','events_conf','display','master'");
		$installer->add_config("'1061','events_conf',NULL,'blockframe','menu'");
		$installer->add_config("'1062','events','1','radio{enabled^1|disabled^0', 'events_conf'");
		$installer->add_config("'1063','news_add','11','access','events_conf'");
		$installer->add_config("'1064','news_edit','11','access','events_conf'");
		*/
			  
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
		// Nothing to upgrade from yet
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
		//$installer->remove_all_config();
		//$installer->drop_table( $installer->table('xxxxxx') );
		return true;
	}
	
	
}

?>