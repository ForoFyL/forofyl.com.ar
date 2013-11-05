<?php
/** 
*
* @package phpBB3
* @version 0.1.4
* @copyright (c) 2007 eviL3
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

// database table
if (!defined('CONTACT_CONFIG_TABLE'))
{
	global $table_prefix;
	if (empty($table_prefix))
	{
		global $phpbb_root_path, $phpEx;
		include($phpbb_root_path . 'config.' . $phpEx);
		unset($dbpasswd);
	}
	// database table
	define('CONTACT_CONFIG_TABLE',		$table_prefix . 'contact_config');
}

// visual confirmation type constant
define('CONFIRM_CONTACT', 4);

// contact methods
define('CONTACT_METHOD_EMAIL', 0);
define('CONTACT_METHOD_POST', 1);
define('CONTACT_METHOD_PM', 2);

// contact type
define('CONTACT_POST_NEITHER', 0);
define('CONTACT_POST_GUEST', 1);
define('CONTACT_POST_ALL', 2);


?>