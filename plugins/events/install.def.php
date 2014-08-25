<?php
	
class eventsInstall
{
	
	/*
	*	These Vars are used with the new Plugin installer 
	*	@var name - unique name for the plugin
	*	@var parent - the intended addon to use this plugin
	*
	*/
	var $active = true;
	var $name = 'events';
	var $filename = 'events.php';
	var $parent = 'main';
	var $scope = 'guild';
	var $icon = 'achievement_worldevent_childrensweek';
	var $version = '1.0';
	var $oldversion = '';
	var $wrnet_id = '';

	var $fullname = 'WoW Events';
	var $description = 'Creates a display of the current ingame event.';
	var $credits = array(
		array(	"name"=>	"Ulminia <Ulminia@gmail.com>",
				"info"=>	"WoW Events (Alpha Release)"),
	);
	
	
		/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;
		$installer->add_config("'1060','startpage','events_conf','display','master'");
		$installer->add_config("'1061','events_conf',NULL,'blockframe','menu'");
		$installer->add_config("'1062','events','1','radio{enabled^1|disabled^0', 'events_conf'");
		$installer->add_config("'1063','news_add','11','access','events_conf'");
		$installer->add_config("'1064','news_edit','11','access','events_conf'");
		
		$installer->create_table($installer->table('xxxxxx'),"
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `crit_achie_id` int(10) NOT NULL,
			  `crit_id` int(10) NOT NULL,
			  `crit_desc` text,
			  PRIMARY KEY (`id`)");
			  
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
		$installer->remove_all_config();
		$installer->drop_table( $installer->table('xxxxxx') );
		return true;
	}
	
	
}

?>