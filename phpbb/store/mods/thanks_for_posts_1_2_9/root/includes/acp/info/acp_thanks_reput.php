<?php
/**
*
* @package acp
* @version $Id: acp_thanks_reput.php,v 128 2010-04-11 10:02:51 Палыч$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @package module_install
*/
if (!defined('IN_PHPBB'))
{
   exit;
}

class acp_thanks_reput_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_thanks_reput',
			'title'		=> 'ACP_THANKS_REPUT_SETTINGS',
			'version'	=> '1.2.8',
			'modes'		=> array(
				'thanks'			=> array('title' => 'ACP_THANKS_REPUT_SETTINGS', 'auth' => 'acl_a_board', 'cat' => array('ACP_THANKS')),
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