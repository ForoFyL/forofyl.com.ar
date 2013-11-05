<?php
/**
*
* @package acp
* @version $Id: acp_thanks.php,v 127 2010-04-11 10:02:51 Палыч$
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_thanks
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$config_vars = array(
			'thanks_postlist_view'		=> 'THANKS_POSTLIST_VIEW',
			'thanks_profilelist_view'	=> 'THANKS_PROFILELIST_VIEW',
			'thanks_counters_view'		=> 'THANKS_COUNTERS_VIEW',
			'thanks_info_page'			=> 'THANKS_INFO_PAGE',
			'thanks_number'				=> 'THANKS_NUMBER',
			'thanks_number_post'		=> 'THANKS_NUMBER_POST',
			'remove_thanks'				=> 'REMOVE_THANKS',
			'thanks_only_first_post'	=> 'THANKS_ONLY_FIRST_POST',
			'thanks_time_view'			=> 'THANKS_TIME_VIEW',
			'thanks_top_number'			=> 'THANKS_TOP_NUMBER',
			'thanks_mod_version'		=> 'THANKS_MOD_VERSION'
		);

		$this->tpl_name = 'acp_thanks';
		$this->page_title = 'ACP_THANKS';
		$form_key = 'acp_thanks';
		add_form_key($form_key);

		$submit = request_var('submit', false);
		
		if ($submit && check_form_key($form_key))
		{
			$config_vars = array_keys($config_vars);
			foreach ($config_vars as $config_var)
			{
				set_config($config_var, request_var($config_var, ''));
			}
			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
		else if($submit)
		{
				trigger_error($user->lang['FORM_INVALID'] . adm_back_link($this->u_action));
		}
		else
		{	
			foreach ($config_vars as $config_var => $template_var)
			{
				$template->assign_var($template_var, (isset($_REQUEST[$config_var])) ? request_var($config_var, '') : $config[$config_var]);
			}
		}
	}
}	
?>