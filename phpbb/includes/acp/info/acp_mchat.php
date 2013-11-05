<?php
/**
*
* @author Stokerpiller
* @package - Mchat
* @version $Id acp_mchat.php 1.2.16 2009-10-21 10:35:46 GMT Stokerpiller $
* @copyright (c) Stokerpiller
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
class acp_mchat_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_mchat',
			'title'		=> 'ACP_CAT_MCHAT',
			'version'	=> '1.2.16',
			'modes'		=> array(
				'configuration'		=> array('title' => 'ACP_MCHAT_CONFIG', 'auth' => 'acl_a_mchat', 'cat' => array('ACP_CAT_DOT_MODS')),
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