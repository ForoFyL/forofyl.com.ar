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
* @package module_install
*/
class acp_dap_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_dap',
			'title'		=> 'ACP_DAP',
			'version'	=> '1.0.4',
			'modes'		=> array(
				'settings'				=> array('title' => 'ACP_DAP_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'pm_notification'		=> array('title' => 'ACP_DAP_PM_NOTIFICATION', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'post_notification'		=> array('title' => 'ACP_DAP_POST_NOTIFICATION', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
				'dupe_user_list'		=> array('title' => 'ACP_DAP_DUPE_USER_LIST', 'auth' => 'acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}

?>