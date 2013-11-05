<?php
/**
*
* @package acp
* @copyright (c) 2009 kmklr72
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

/**
* @package mod_version_check
*/
class dap_version
{
	function version()
	{
		return array(
			'author'	=> 'kmklr72',
			'title'		=> 'Double Account Preventer',
			'tag'		=> 'double_account_preventer',
			'version'	=> '1.0.4',
			'file'		=> array('demoninstall.co.cc', 'versioncheck', 'mods.xml'),
		);
	}
}

?>