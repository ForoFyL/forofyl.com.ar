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
* @package acp
*/
class acp_dap
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $dap;

		$dap_config = $dap->get_dap_config();
		$this->new_config = $dap_config;

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_dap';
		add_form_key($form_key);

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
			case 'settings':
				$display_vars = array(
					'title'	=> 'ACP_DAP_SETTINGS',
					'vars'	=> array(
						'legend1'						=> 'ACP_DAP_SETTINGS',
						'require_ip_check'				=> array('lang' => 'IP_CHECK_REGISTRATION',					'validate' => 'int', 'type' => 'custom', 'method' => 'select_ip_check_registration', 'explain' => true),
						'require_cookie_check'			=> array('lang' => 'COOKIE_CHECK_REGISTRATION',				'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'enable_email_notification'		=> array('lang' => 'DAP_EMAIL_NOTIFICATION',				'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),

						'legend2'						=> 'ACP_DAP_COOKIE_BAN_SETTINGS',
						'cookie_ban_enabled'			=> array('lang' => 'DAP_COOKIE_BAN_ENABLED',				'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'cookie_ban_message'			=> array('lang' => 'DAP_COOKIE_BAN_MESSAGE',				'validate' => 'string', 'type' => 'text:80:200', 'explain' => true),

						'legend3'						=> 'ACP_DAP_PROXY_SETTINGS',
						'proxy_check_enabled'			=> array('lang' => 'DAP_PROXY_CHECK_ENABLED',				'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'proxy_check_block'				=> array('lang' => 'DAP_PROXY_CHECK_BLOCK',					'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'proxy_block_message'			=> array('lang' => 'DAP_PROXY_BLOCK_MESSAGE',				'validate' => 'string', 'type' => 'text:80:200', 'explain' => true),
					)
				);
			break;

			case 'pm_notification':
				$display_vars = array(
					'title' => 'ACP_DAP_PM_NOTIFICATION',
					'vars'	=> array(
						'legend1'						=> 'ACP_DAP_PM_NOTIFICATION',
						'enable_pm_notification'		=> array('lang' => 'DAP_PM_NOTIFICATION',					'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'alert_user_id'					=> array('lang' => 'DAP_ALERT_USER_ID',						'validate' => 'string', 'type' => 'text:10:200', 'explain' => true),
						'pm_subject'					=> array('lang' => 'DAP_PM_NOTIFICATION_SUBJECT',			'validate' => 'string', 'type' => 'text:80:200', 'explain' => true),
						'pm_message'					=> array('lang' => 'DAP_PM_NOTIFICATION_MESSAGE',			'type' => 'textarea:10:6', 'explain' => true),
					)
				);
			break;

			case 'post_notification':
				$display_vars = array(
					'title' => 'ACP_DAP_POST_NOTIFICATION',
					'vars'	=> array(
						'legend1'						=> 'ACP_DAP_POST_NOTIFICATION',
						'enable_post_notification'		=> array('lang' => 'DAP_POST_NOTIFICATION',					'validate' => 'bool', 'type' => 'radio:yes_no', 'explain' => true),
						'post_forum_id'					=> array('lang' => 'DAP_POST_NOTIFICATION_FORUM',			'validate' => 'string', 'type' => 'text:10:200', 'explain' => true),
						'post_subject'					=> array('lang' => 'DAP_POST_NOTIFICATION_SUBJECT',			'validate' => 'string', 'type' => 'text:80:200', 'explain' => true),
						'post_message'					=> array('lang' => 'DAP_POST_NOTIFICATION_MESSAGE',			'type' => 'textarea:10:6', 'explain' => true),
					)
				);
			break;

			case 'dupe_user_list':
				$display_vars = array(
					'title' => 'ACP_DAP_DUPE_USER_LIST',
				);

				$this->list_dupe_users();

				$template->assign_vars(array(
					'L_TITLE'			=> $user->lang[$display_vars['title']],
					'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

					'DUPE_USER_LIST'					=> true,
				));

				if (isset($_GET['ban']))
				{
					//$user_id = $_GET['u'];
					//$ban = $_GET['ban'];
					$user_id = request_var('u', 0);
					$ban = request_var('ban', 0);

					$this->cookie_ban_user($user_id, $ban);
				}
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}

		if ($mode != 'dupe_user_list')
		{
			$this->page_output($display_vars, $form_key);
		}

		$this->tpl_name = 'acp_dap';
		$this->page_title = $user->lang[$display_vars['title']];
	}

	function select_ip_check_registration($value, $key = '')
	{
		global $user, $config;

		$radio_ary = array(0 => 'IP_CHECK_DISABLE', 1 => 'IP_CHECK_NONE', 2 => 'IP_CHECK_LIGHT', 3 => 'IP_CHECK_FULL');	

		return h_radio('config[require_ip_check]', $radio_ary, $value, $key);
	}

	function list_dupe_users()
	{
		global $auth, $db, $template, $phpbb_root_path, $phpEx;

		$sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE user_double = ' . (bool) true;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$ban = ($row['user_banned_cookie'] == 0) ? 1 : 0;

			$template->assign_block_vars('dupe_users', array(
				'USER_ID'			=> $row['user_id'],
				'USERNAME'			=> $row['username'],
				'COMMON_NAMES'		=> $row['common_names'],
				'DETECT_METHOD'		=> $row['user_detect_method'],

				'PROXY'				=> $row['proxy'],

				'U_USER_ADMIN'		=> ($auth->acl_get('a_user')) ? append_sid("{$phpbb_root_path}adm/index.$phpEx", 'i=users&amp;mode=overview&amp;u=' . $row['user_id'], true) : '',
				'U_USER_COOKIE_BAN'	=> append_sid("{$phpbb_root_path}adm/index.$phpEx", 'i=dap&amp;mode=dupe_user_list&amp;ban=' . $ban . '&amp;u=' . $row['user_id'], true),
			));
		}
		$db->sql_freeresult($result);

		return;
	}

	function cookie_ban_user($user_id, $ban)
	{
		global $db;

		$sql = 'UPDATE ' . USERS_TABLE . ' SET user_banned_cookie = ' . (int) $ban . ' WHERE user_id = ' . (int) $user_id;
		$db->sql_query($sql);
	}

	function page_output($display_vars, $form_key)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
		global $dap;

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$dap_config = $dap->get_dap_config();
		$this->new_config = $dap_config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				$dap->set_dap_config($config_name, $config_value);
			}
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}

		//return;
	}
}

?>