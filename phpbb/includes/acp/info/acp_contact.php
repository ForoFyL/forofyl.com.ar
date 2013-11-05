<?php
/**
*
* @author RMcGirr83
* @package - Contact Board Admin
* @version $Id acp_mchat.php 1.0.1 2009-12-02 RMcGirr83 $
* @copyright (c) RMcGirr83
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* @package module_install
*/
class acp_contact_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_contact',
			'title'		=> 'ACP_CAT_CONTACT',
			'version'	=> '1.7.0',
			'modes'		=> array(
				'configuration'		=> array('title' => 'ACP_CONTACT_CONFIG', 'auth' => 'acl_a_contact', 'cat' => array('ACP_CAT_DOT_MODS')),
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