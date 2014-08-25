<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Contants and defines file for ApiSync
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2007 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: constants.php 550 2012-06-02 05:27:04Z ulminia@gmail.com $
 * @link       http://www.wowroster.net
 * @since      File available since Release 2.6.0
 * @package    ApiSync
*/

if( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

define('RSYNC_STARTTIME', isset($_POST['RSYNC_STARTTIME']) ? $_POST['RSYNC_STARTTIME']: format_microtime());
define('RSYNC_CACHE', ROSTER_CACHEDIR );
define('RSYNC_VERSION','1.0');

define('RSYNC_REQUIRED_ROSTER_VERSION','2.1.9.2340');
