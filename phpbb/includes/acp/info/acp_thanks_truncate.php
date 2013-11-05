<?php
/**
*
* @package acp
* @version $Id: acp_thanks_truncate.php,v 127 2010-04-17 10:02:51 Палыч$
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

class acp_thanks_truncate_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_thanks_truncate',
			'title'		=> 'ACP_THANKS_TRUNCATE',
			'version'	=> '1.2.7',
			'modes'		=> array(
				'thanks'			=> array('title' => 'ACP_THANKS_TRUNCATE', 'auth' => 'acl_a_board', 'cat' => array('ACP_THANKS')),
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