<?php

/**
*
* @package phpBB3
* @copyright (c) 2009 kmklr72 with IP check code by mtrs and mail codes by ameeck's notify admin registration mod
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

class dap
{
	function ban_cookie($banned)
	{
		global $config;

		setcookie('DAPBan', $banned, time() + 10368000 * 5, $config['cookie_path']);
	}

	// Create a board cookie for the cookie check
	function create_cookie($username)
	{
		global $config;

		setcookie('DAPCheck', $username, time() + 10368000 * 5, $config['cookie_path']);
	}

	// Check for the DAP cookie
	function cookie_check()
	{
		global $user;

		if (isset($_COOKIE['DAPCheck']))
		{
			$double_account = array(
				'user_double'			=> true,
				'user_detect_method'	=> $user->lang['COOKIE_CHECK'],
				'common_names'			=> request_var('DAPCheck', '', false, true),
			);
		}

		return $double_account;
	}

	// Check for proxies
	// Not programmed to find anonymous proxies
	function proxy_check()
	{
		global $user;

		$dap_config = $this->get_dap_config();
		$error = array();

		// Proxy header check
		if (!empty($_SERVER['HTTP_VIA']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_VIA'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		}
		elseif (!empty($_SERVER['HTTP_FORWARDED_FOR']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		}
		elseif (!empty($_SERVER['HTTP_FORWARDED']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_FORWARDED'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$using_proxy = true;
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		$ip = (isset($ip)) ? $ip : $user->ip;

		if ($config['check_dnsbl'])
		{
			if (($dnsbl = $user->check_dnsbl('register', $ip)) !== false)
			{
				$blacklisted = true;
			}
		}

		$bad_ip = (isset($using_proxy) || isset($blacklisted)) ? true : false;

		return $bad_ip;
	}

	// Replace shortcodes for the PMs and posts
	function replace_strings(&$string, $data_username, $data_email, $user_regdate, $user_detect_method, $common_names)
	{
		global $config, $user;

		$string = str_replace('[sitename]', $config['sitename'], $string);
		$string = str_replace('[username]', htmlspecialchars_decode($data_username), $string);
		$string = str_replace('[user_ip]', $user->ip, $string);
		$string = str_replace('[user_email]', $data_email, $string);
		$string = str_replace('[user_regdate]', date($config['default_dateformat'], $user_regdate), $string);
		$string = str_replace('[user_detect_method]', $user_detect_method, $string);
		$string = str_replace('[common_names]', $common_names, $string);

		return;
	}

	// Send a PM to all admins
	function send_dap_pm($data_username, $data_email, $user_regdate, $user_detect_method, $common_names)
	{
		global $auth, $db, $user, $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
		include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

		$message_parser = new parse_message();

		// Grab an array of user_id's with admin permissions to send a PM to
		$admin_ary = $auth->acl_get_list(false, 'a_', false);
		$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : array();

		if (sizeof($admin_ary))
		{
			$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
		}

		$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
			FROM ' . USERS_TABLE . ' ' .
			$where_sql;
		$result = $db->sql_query($sql);

		// Loop through our results
		while ($row = $db->sql_fetchrow($result))
		{
			$contact_users[] = $row;
		}
		$db->sql_freeresult($result);

		// Get the subject and message
		$dap_config = $this->get_dap_config();
		$message = $dap_config['pm_message'];
		$subject = $dap_config['pm_subject'];

		// Replace the shortcodes
		$this->replace_strings($message, $data_username, $data_email, $user_regdate, $user_detect_method, $common_names);
		$this->replace_strings($subject, $data_username, $data_email, $user_regdate, $user_detect_method, $common_names);

		$message_parser->message = utf8_normalize_nfc($message);
		$message_parser->parse(true, true, true);

		$sql = 'SELECT username, user_ip
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . $dap_config['alert_user_id'];
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$alert_user_ip = $row['user_ip'];
			$alert_username = $row['username'];
		}
		$db->sql_freeresult($result);

		// Set the PM data
		$pm_data = array(
			'from_user_id'		=> $dap_config['alert_user_id'],
			'from_user_ip'		=> $alert_user_ip,
			'from_username'		=> $alert_username,
			'enable_sig'		=> false,
			'enable_bbcode'		=> true,
			'enable_smilies'	=> true,
			'enable_urls'		=> false,
			'icon_id'			=> 0,
			'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
			'bbcode_uid'		=> $message_parser->bbcode_uid,
			'message'			=> $message_parser->message,
		);

		// Loop through our list of users
		for ($i = 0, $size = sizeof($contact_users); $i < $size; $i++)
		{
			$pm_data['address_list'] = array('u' => array($contact_users[$i]['user_id'] => 'to'));

			submit_pm('post', $subject, $pm_data, true);
		}
	}

	// Post in the specified forums
	function submit_dap_post($data_username, $data_email, $user_regdate, $user_detect_method, $common_names)
	{
		global $db, $phpbb_root_path, $phpEx;
		global $auth, $user;

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);

		// Get the subject and message
		// Also get the forums to post in
		$dap_config = $this->get_dap_config();
		$message = $dap_config['post_message'];
		$subject = $dap_config['post_subject'];
		$forums = $dap_config['post_forum_id'];
		$alert_user_id = $dap_config['alert_user_id'];

		// Replace the shortcodes
		$this->replace_strings($message, $data_username, $data_email, $user_regdate, $user_detect_method, $common_names);
		$this->replace_strings($subject, $data_username, $data_email, $user_regdate, $user_detect_method, $common_names);

		// Backup and replace the $user and $auth constants so an actual user can post, not just a guest with a username
		$backup = array(
			'user_data'	=> $user->data,
			'user_ip'	=> $user->ip,
			'auth'		=> $auth,
		);

		$sql = "SELECT *
			FROM " . USERS_TABLE . "
			WHERE user_id = '" . $dap_config['alert_user_id'] . "'";
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// We have to overwrite some $user values to make the script post correctly
			$user->data = $row;
			$user->ip = '0.0.0.0';
			$auth->acl($user->data);

			// We set these so we don't have to use the overwritten values. It's just safer this way.
			$alert_user = array(
				'user_id'		=> (int) $dap_config['alert_user_id'],
				'user_ip'		=> '0.0.0.0',
				'username'		=> $row['username'],
			);
		}
		$db->sql_freeresult($result);

		$forums = explode(',', $forums);

		// Loop through and post in the correct forums
		foreach ($forums as $forum_id)
		{
			// variables to hold the parameters for submit_post
			$poll = $uid = $bitfield = $options = ''; 

			generate_text_for_storage($subject, $uid, $bitfield, $options, false, false, false);
			generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);

			// Set the post data
			$data = array( 
				'forum_id'			=> $forum_id,
				'icon_id'			=> false,
				'enable_bbcode'		=> true,
				'enable_smilies'	=> true,
				'enable_urls'		=> true,
				'enable_sig'		=> true,
				'message'			=> $message,
				'message_md5'		=> md5($message),
				'bbcode_bitfield'	=> $bitfield,
				'bbcode_uid'		=> $uid,
				'post_edit_locked'	=> 0,
				'topic_title'		=> $subject,
				'notify_set'		=> false,
				'notify'			=> false,
				'post_time' 		=> 0,
				'forum_name'		=> '',
				'enable_indexing'	=> true,
				'topic_approved'	=> true,
				'post_approved'		=> true,
			);

			// Time to post
			submit_post('post', $subject, $alert_user['username'], POST_NORMAL, $poll, $data);
		}

		// Extract the backup to set the $user and $auth constants to what they were before
		//extract($backup);
		$auth = $backup['auth'];
		$user->data = $backup['user_data'];
		$user->ip = $backup['user_ip'];
		unset($backup);
	}

	// Get the MOD config for the ACP
	function get_dap_config()
	{
		global $db;

		$dap_config = array();
	
		$sql = "SELECT * FROM " . DAP_CONFIG_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$dap_config[$row['config_name']] = $row['config_value'];
		}
		$db->sql_freeresult($result);

		return $dap_config;
	}

	// Update settings in the config
	function set_dap_config($config_name, $config_value)
	{
		global $db, $dap_config;

		$sql = "UPDATE " . DAP_CONFIG_TABLE . "
			SET config_value = '" . $db->sql_escape($config_value) . "'
			WHERE config_name = '" . $db->sql_escape($config_name) . "'";
		$db->sql_query($sql);
	}

	/*
	* The following Duplicate IP check code was created by mtrs and ameeck
	* Huge thanks to both of them for allowing me to use their code for this mod
	*/

	//Begin: Duplicate IP Check
	function duplicate_ip_check($string)
	{
		global $db, $template, $user, $config;

		$dap_config = $this->get_dap_config();

		if ($dap_config['proxy_check_enabled'])
		{
			$bad_ip = $this->proxy_check();

			if ($bad_ip == true && $dap_config['proxy_check_block'])
			{
				trigger_error($dap_config['proxy_block_message']);
			}

			$proxy = ($bad_ip = true) ? $user->lang['DAP_PROXY_TRUE'] : $user->lang['DAP_PROXY_FALSE'];
		}

		if ($dap_config['require_ip_check'] >= IP_CHECK_LIGHT)
		{
			$sql = 'SELECT user_id
				FROM ' . USERS_TABLE . '
				WHERE user_ip = "' . $db->sql_escape($user->ip) . '"
					AND user_type <> ' . USER_IGNORE;
	
			$result = $db->sql_query($sql);
	
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['user_id'];
			}
			$db->sql_freeresult($result);

			$sql = 'SELECT session_user_id
				FROM ' . SESSIONS_TABLE . '
				WHERE session_ip = "' . $db->sql_escape($user->ip) . '"
					AND session_user_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);
	
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['session_user_id'];
			}
			$db->sql_freeresult($result);
	
			$sql = 'SELECT user_id
				FROM ' . SESSIONS_KEYS_TABLE . '
				WHERE last_ip = "' . $db->sql_escape($user->ip) . '"
					AND user_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);
		}

		if ($dap_config['require_ip_check'] == IP_CHECK_FULL)
		{
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['user_id'];
			}
			$db->sql_freeresult($result);
	
			$sql = 'SELECT user_id
				FROM ' . LOG_TABLE . '
				WHERE log_ip = "' . $db->sql_escape($user->ip) . '"
					AND user_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);	
	
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['user_id'];
			}
			$db->sql_freeresult($result);	
	
			$sql = 'SELECT poster_id
				FROM ' . POSTS_TABLE . '
				WHERE poster_ip = "' . $db->sql_escape($user->ip) . '"
					AND poster_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['poster_id'];
			}
			$db->sql_freeresult($result);	
	
			$sql = 'SELECT author_id
				FROM ' . PRIVMSGS_TABLE . '
				WHERE author_ip = "' . $db->sql_escape($user->ip) . '"
					AND author_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);
	
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['author_id'];
			}
			$db->sql_freeresult($result);	
	
			$sql = 'SELECT vote_user_id
				FROM ' . POLL_VOTES_TABLE . '
				WHERE vote_user_ip = "' . $db->sql_escape($user->ip) . '"
					AND vote_user_id <> ' . ANONYMOUS;
			$result = $db->sql_query($sql);
	
			while ($row = $db->sql_fetchrow($result))
			{
				$dupe_ips[] = $row['vote_user_id'];
			}
			$db->sql_freeresult($result);
		}	

		if (isset($dupe_ips))
		{
			// Get usernames list for notifications
			foreach ($dupe_ips as $dupe_name)
			{
				$sql = 'SELECT username
					FROM ' . USERS_TABLE . '
					WHERE user_id = ' . $dupe_name;
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					$common_names_array[] = $row['username'];
				}
				$db->sql_freeresult($result);
			}

			// Get only unique keys
			// (why not use array_unique?  it didn't work for me, this does)
			$common_names_array = array_keys(array_flip($common_names_array));

			// Generate list of usernames
			$common_names = (isset($common_names_array)) ? implode(', ', $common_names_array) : '';

			// Assign array values for insertion into the users table
			$double_account = array(
				'user_double'			=> true,
				'user_detect_method'	=> $user->lang['IP_CHECK'],
				'common_names'			=> $common_names,
				'proxy'					=> $proxy,
			);
		}
	
		return $double_account;
	}

	function notify_admin_dupe_ips($data_username, $data_email, $user_regdate, $user_detect_method, $common_names)
	{
		global $db, $auth, $template, $user, $phpbb_root_path, $phpEx, $config;

		include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);

		$messenger = new messenger(false);

		// Codes below take from "Notify admin on registration MOD" by ameeck
		// Grab an array of user_id's with a_user permissions ... these users can activate a user
		$admin_ary = $auth->acl_get_list(false, 'a_user', false);
		$admin_ary = (!empty($admin_ary[0]['a_user'])) ? $admin_ary[0]['a_user'] : array();

		if (sizeof($admin_ary))
		{
			$where_sql .= ' OR ' . $db->sql_in_set('user_id', $admin_ary);
		}

		$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
			FROM ' . USERS_TABLE . ' ' .
			$where_sql;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			$messenger->template('admin_notify_duplicates', $row['user_lang']);
			$messenger->to($row['user_email'], $row['username']);
			$messenger->im($row['user_jabber'], $row['username']);

			$messenger->assign_vars(array(
				'USERNAME'				=> htmlspecialchars_decode($data_username),
				'USER_MAIL'				=> $data_email,
				'USER_IP'				=> $user->ip,
				'USER_REGDATE'			=> date($config['default_dateformat'], $user_regdate),
				'USER_DETECT_METHOD'	=> $user_detect_method,
				'COMMON_NAMES'			=> $common_names,
			));

			$messenger->send($row['user_notify_type']);
		}
		$db->sql_freeresult($result);
	}
	//End: Duplicate IP Check
}

?>