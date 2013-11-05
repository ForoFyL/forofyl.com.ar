<?php
/**
*
* @package acp
* @version $Id: acp_xcache_info.php joebert $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
class acp_xcache_info_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_xcache_info',
			'title'		=> 'ACP_XCACHE_INFO',
			'version'	=> '1.0.0',
			'modes'		=> array(
				'status'	=> array('title' => 'ACP_XCACHE_STATUS', 'auth' => 'acl_a_phpinfo', 'cat' => array('ACP_GENERAL_TASKS')),
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
