<?php
/**
*
* @package mChat PHP Code
* @version 1.3.7 26.10.2009
* @copyright (c) djs596 ( http://djs596.com/ ), (c) RMcGirr83 ( http://www.rmcgirr83.org/ ), (c) Stokerpiller ( http://www.phpbb3bbcodes.com/ )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

/**
* DO NOT CHANGE (IN_PHPBB)!
*/
if(!defined('MCHAT_INCLUDE'))
{
  // Custom Page code from http://www.phpbb.com/kb/article/add-a-new-custom-page-to-phpbb/
  define('IN_PHPBB', true);
  $phpbb_root_path = './';
  $phpEx = substr(strrchr(__FILE__, '.'), 1);
  include($phpbb_root_path.'common.'.$phpEx);
  $mchat_include_index = false;

  // Start session management.
  $user->session_begin();
  $auth->acl($user->data);
  $user->setup();
}
// for who is chatting and whois popup
include($phpbb_root_path . 'includes/functions_mchat.' . $phpEx);

// Add lang file
$user->add_lang(array('mods/mchat_lang', 'viewtopic'));

// Grab the config entries in teh ACP...and cache em :P
if (($config_mchat = $cache->get('_mchat_config')) === false)
{
	$sql = 'SELECT * FROM ' . MCHAT_CONFIG_TABLE;
	$result = $db->sql_query($sql);
	$config_mchat = array();
	while ($row = $db->sql_fetchrow($result))	
	{
		$config_mchat[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	// cache this data forever (one year?), can only change in ACP
	// this improves performance
	$cache->put('_mchat_config', $config_mchat);
}

// Access rights 
$mchat_allow_bbcode	= ($config['allow_bbcode'] && $auth->acl_get('u_mchat_bbcode')) ? true : false;
$mchat_smilies = ($config['allow_smilies'] && $auth->acl_get('u_mchat_smilies')) ? true : false;
$mchat_urls = ($config['allow_post_links'] && $auth->acl_get('u_mchat_urls')) ? true : false;
$mchat_ip = ($auth->acl_get('u_mchat_ip')) ? true : false;
$mchat_add_mess	= ($auth->acl_get('u_mchat_use')) ? true : false;
$mchat_view	= ($auth->acl_get('u_mchat_view')) ? true : false;
$mchat_no_flood	= ($auth->acl_get('u_mchat_flood_ignore')) ? true : false;
$mchat_read_archive = ($auth->acl_get('u_mchat_archive')) ? true : false;
$mchat_founder = ($user->data['user_type'] == USER_FOUNDER) ? true : false;

// needed variables
// Request options.
$mchat_mode	= request_var('mode', '');
$mchat_read_mode = $mchat_archive_mode = $mchat_custom_page = false;
// set redirect if on index or custom page
$mchat_redirect = ($mchat_include_index && $mchat_mode != 'clean') ? append_sid("{$phpbb_root_path}index.$phpEx") : append_sid("{$phpbb_root_path}mchat.$phpEx");

// Request mode...
switch ($mchat_mode)
{
	// whois function..
	case 'whois';

		// Must have auths
		if ($mchat_mode == 'whois' && $mchat_ip)
		{	
			// function already exists..
			if (!function_exists('user_ipwhois'))
			{
				include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
			}
			
			$user_ip = request_var('ip', '');
			
			$template->assign_var('WHOIS', user_ipwhois($user_ip));

			// Output the page
			page_header($user->lang['WHO_IS_ONLINE']);

			$template->set_filenames(array(
				'body' => 'viewonline_whois.html')
			);

			page_footer();	
		}
		else
		{
				// Show not authorized
				trigger_error('NO_AUTH_OPERATION', E_USER_WARNING);
		}
		
	break;
	
	// Clean function...
	case 'clean';
			
		// User logged in?
		if(!$user->data['is_registered'] || !$mchat_founder)
		{
			if(!$user->data['is_registered'])
			{
				// Login box...
				login_box('', $user->lang['LOGIN']);
			}
			else if (!$mchat_founder)
			{
				// Show not authorized
				trigger_error('NO_AUTH_OPERATION', E_USER_WARNING);
			}			
		}
		// Founder only
		else if ($mchat_founder)
		{
			if(confirm_box(true))
			{
				// Run cleaner
				$sql = 'TRUNCATE TABLE ' . MCHAT_TABLE;
				$db->sql_query($sql);
				// Show OK box and redirect to correct page
				
				meta_refresh(3, $mchat_redirect);
				trigger_error($user->lang['MCHAT_CLEANED'].'<br /><br />'.sprintf($user->lang['RETURN_PAGE'], '<a href="'.$mchat_redirect.'">', '</a>'), E_USER_NOTICE);
			}
			else
			{
				// Display confirm box
				confirm_box(false, $user->lang['MCHAT_DELALLMESS']);
			}
			redirect($mchat_redirect);
		}
		else
		{
			// Show error box...
			trigger_error('MCHAT_NOACCESS', E_USER_NOTICE);
		}
		// Stop run code!
		exit;
		
	break;

	// Archive function...
	case 'archive';
	
		if (!$mchat_read_archive || !$mchat_view)
		{
			// Redirect to previous page
			meta_refresh(3, $mchat_redirect);
			trigger_error($user->lang['MCHAT_NOACCESS_ARCHIVE'].'<br /><br />'.sprintf($user->lang['RETURN_PAGE'], '<a href="'.$mchat_redirect.'">', '</a>'), E_USER_NOTICE);
			// Stop running code
			exit;
		}
		
		if ($config['mchat_enable'] && $mchat_read_archive && $mchat_view)
		{
			// check for copyright
			if (!isset($user->lang['MCHAT_COPYRIGHT']) || (trim($user->lang['MCHAT_COPYRIGHT']) == '') || $user->lang['MCHAT_COPYRIGHT'] != $user->lang['MCHAT_COPYRIGHT_CHECK'])
			{
				trigger_error($user->lang['MCHAT_REPLACE_COPYRIGHT'], E_USER_NOTICE);
				// Stop running code
				exit;
			}
			// prune the chats if nescessary and amount in ACP not empty
			if ($config_mchat['prune_enable'] && $config_mchat['prune_num'] > 0)
			{
				mchat_prune((int) $config_mchat['prune_num']);
			}
					
			// Reguest...
			$mchat_archive_start = request_var('start', 0);
			// Message row
			$sql = 'SELECT m.*, u.username, u.user_colour
				FROM ' . MCHAT_TABLE . ' m
					LEFT JOIN ' . USERS_TABLE . ' u ON (m.user_id = u.user_id)
					ORDER BY m.message_id DESC';
			$result = $db->sql_query_limit($sql, (int) $config_mchat['archive_limit'], $mchat_archive_start);
			$rows = $db->sql_fetchrowset($result);
			foreach($rows as $row)
			{
	
				// edit, delete and permission auths
				$mchat_ban = ($auth->acl_get('a_authusers') && $user->data['user_id'] != $row['user_id']) ? true : false;
				$mchat_edit = ($auth->acl_get('u_mchat_edit') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['user_id'])) ? true : false;
				$mchat_del = ($auth->acl_get('u_mchat_delete') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['user_id'])) ? true : false;
				
				$message_edit = $row['message'];
				decode_message($message_edit, $row['bbcode_uid']);
				$message_edit = str_replace('"', '&quot;', $message_edit); // Edit Fix ;)
				$template->assign_block_vars('mchatrow', array(
					'MCHAT_ALLOW_BAN'		=> $mchat_ban,
					'MCHAT_ALLOW_EDIT'		=> $mchat_edit,
					'MCHAT_ALLOW_DEL'		=> $mchat_del,
					'MCHAT_MESSAGE_EDIT'	=> $message_edit,
					'MCHAT_MESSAGE_ID'		=> $row['message_id'],
					'MCHAT_USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USERNAME_COLOR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USER_IP'			=> $row['user_ip'],
					'MCHAT_U_WHOIS'			=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=whois&amp;ip=' . $row['user_ip']),
					'MCHAT_U_BAN'			=> append_sid("{$phpbb_root_path}adm/index.$phpEx" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[0]=' . $row['user_id'], true, $user->session_id),
					'MCHAT_MESSAGE'			=> censor_text(generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options'])),
					'MCHAT_TIME'			=> $user->format_date($row['message_time'], $config_mchat['date']),
					'MCHAT_CLASS'			=> ($row['message_id'] % 2) ? 1 : 2
				));
			}
			$db->sql_freeresult($result);
			// Write no message
			if(empty($rows))
			{
				// Template function...
				$template->assign_vars(array('MCHAT_NOMESSAGE_MODE' => true));
			}
			else
			{
				// Run query again to get the total message rows...
				$sql = 'SELECT COUNT(message_id) AS mess_id FROM ' . MCHAT_TABLE;
				$result = $db->sql_query($sql);
				$mchat_total_message = $db->sql_fetchfield('mess_id');
				$db->sql_freeresult($result);
				// Page list function...
				$template->assign_vars(array(
					'MCHAT_PAGE_NUMBER'		=> on_page($mchat_total_message, (int) $config_mchat['archive_limit'], $mchat_archive_start),
					'MCHAT_TOTAL_MESSAGES'	=> sprintf($user->lang['MCHAT_TOTALMESSAGES'], $mchat_total_message),
					'MCHAT_PAGINATION'		=> generate_pagination(append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=archive'), $mchat_total_message, (int) $config_mchat['archive_limit'], $mchat_archive_start, true)
				));
				// If archive mode request set true
				$mchat_archive_mode = true;
			}
		}

		// If archive mode request set true
		$mchat_archive_mode = true;

	break;

	// Read function...
	case 'read':

		// If mChat disabled or user can't view the chat
		if (!$config['mchat_enable'] || !$mchat_view)
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		// Request
		$mchat_message_last_id = request_var('message_last_id', 0);
		
		$sql = 'SELECT m.*, u.username, u.user_colour, u.user_id as userid
					FROM (' . MCHAT_TABLE . ' m, ' . USERS_TABLE . ' u)
						WHERE m.user_id = u.user_id
							AND m.message_id > ' . $mchat_message_last_id . '
				ORDER BY m.message_id DESC';		
		$result = $db->sql_query_limit($sql, (int) $config_mchat['message_limit']);
		$rows = $db->sql_fetchrowset($result);
		foreach($rows as $row)
		{
	
			// edit, delete and permission auths
			$mchat_ban = ($auth->acl_get('a_authusers') && $user->data['user_id'] != $row['userid']) ? true : false;
			$mchat_edit = ($auth->acl_get('u_mchat_edit') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['userid'])) ? true : false;
			$mchat_del = ($auth->acl_get('u_mchat_delete') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['userid'])) ? true : false;
				
			$message_edit = $row['message'];
			decode_message($message_edit, $row['bbcode_uid']);
			$message_edit = str_replace('"', '&quot;', $message_edit);
			$template->assign_block_vars('mchatrow', array(
				'MCHAT_ALLOW_BAN'		=> $mchat_ban,
				'MCHAT_ALLOW_EDIT'		=> $mchat_edit,
				'MCHAT_ALLOW_DEL'		=> $mchat_del,			
				'MCHAT_MESSAGE_EDIT'	=> $message_edit,
				'MCHAT_MESSAGE_ID' 		=> $row['message_id'],
				'MCHAT_USERNAME_FULL'	=> get_username_string('full', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
				'MCHAT_USERNAME'		=> get_username_string('username', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
				'MCHAT_USERNAME_COLOR'	=> get_username_string('colour', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
				'MCHAT_USER_IP'			=> $row['user_ip'],
				'MCHAT_U_WHOIS'			=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=whois&amp;ip=' . $row['user_ip']),
				'MCHAT_U_BAN'			=> append_sid("{$phpbb_root_path}adm/index.$phpEx" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[0]=' . $row['user_id'], true, $user->session_id),
				'MCHAT_MESSAGE'			=> censor_text(generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options'])),
				'MCHAT_TIME'			=> $user->format_date($row['message_time'], $config_mchat['date']),
				'MCHAT_CLASS'			=> ($row['message_id'] % 2) ? 1 : 2
			));
		}
		$db->sql_freeresult($result);
		
		// If read mode request set true
		$mchat_read_mode = true;

	break;

	// Stats function...
	case 'stats':

		// If mChat disabled or user can't view the chat
		if (!$config['mchat_enable'] || !$mchat_view || !$config_mchat['whois'])
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		
		$reading_sql = ' AND s.session_page = "mchat.'.$phpEx.'"';

		$time = (time() - (intval($config['load_online_time']) * 60));

		// Get number of online guests
		if ($db->sql_layer === 'sqlite')
		{
			$sql = 'SELECT COUNT(session_ip) as num_guests
				FROM (
					SELECT DISTINCT s.session_ip
					FROM ' . SESSIONS_TABLE . ' s
					WHERE s.session_user_id = ' . ANONYMOUS . '
						AND s.session_time >= ' . ($time - ((int) ($time % 60))) . 
					$reading_sql . '
				)';
		}
		else
		{
			$sql = 'SELECT COUNT(DISTINCT s.session_ip) as num_guests
				FROM ' . SESSIONS_TABLE . ' s
				WHERE s.session_user_id = ' . ANONYMOUS . '
					AND s.session_time >= ' . ($time - ((int) ($time % 60))) .
				$reading_sql;
		}
		$result = $db->sql_query($sql);
		$mchat_guests_online = (int) $db->sql_fetchfield('num_guests');
		$db->sql_freeresult($result);
			
		$mchat_online_users = array(
			'online_users'			=> array(),
			'hidden_users'			=> array(),
			'total_online'			=> 0,
			'visible_online'		=> 0,
			'hidden_online'			=> 0,
			'guests_online'			=> 0,
		);

		$mchat_online_users['guests_online'] = $mchat_guests_online;

		$sql = 'SELECT s.session_user_id, s.session_ip, s.session_viewonline
			FROM ' . SESSIONS_TABLE . ' s
			WHERE s.session_time >= ' . ($time - ((int) ($time % 60))) . $reading_sql . '
			AND s.session_user_id <> ' . ANONYMOUS;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			// Skip multiple sessions for one user
			if (!isset($mchat_online_users['online_users'][$row['session_user_id']]))
			{
				$mchat_online_users['online_users'][$row['session_user_id']] = (int) $row['session_user_id'];
				if ($row['session_viewonline'])
				{
					$mchat_online_users['visible_online']++;
				}
				else
				{
					$mchat_online_users['hidden_users'][$row['session_user_id']] = (int) $row['session_user_id'];
					$mchat_online_users['hidden_online']++;
				}
			}
		}
		$mchat_online_users['total_online'] = $mchat_online_users['guests_online'] + $mchat_online_users['visible_online'] + $mchat_online_users['hidden_online'];
		$db->sql_freeresult($result);

		$mchat_user_online_link = $mchat_online_userlist = '';

		if (sizeof($mchat_online_users['online_users']))
		{
			$sql = 'SELECT username, username_clean, user_id, user_type, user_allow_viewonline, user_colour
					FROM ' . USERS_TABLE . '
					WHERE ' . $db->sql_in_set('user_id', $mchat_online_users['online_users']) . '
					ORDER BY username_clean ASC';
			$result = $db->sql_query($sql);

			while ($row = $db->sql_fetchrow($result))
			{
				// User is logged in and therefore not a guest
				if ($row['user_id'] != ANONYMOUS)
				{
					if (isset($mchat_online_users['hidden_users'][$row['user_id']]))
					{
						$row['username'] = '<em>' . $row['username'] . '</em>';
					}

					if (!isset($mchat_online_users['hidden_users'][$row['user_id']]) || $auth->acl_get('u_viewonline'))
					{
						$mchat_user_online_link = get_username_string(($row['user_type'] <> USER_IGNORE) ? 'full' : 'no_profile', $row['user_id'], $row['username'], $row['user_colour']);
						$mchat_online_userlist .= ($mchat_online_userlist != '') ? ', ' . $mchat_user_online_link : $mchat_user_online_link;
					}
				}
			}
			$db->sql_freeresult($result);
		}

		if (!$mchat_online_userlist)
		{
			$mchat_online_userlist = $user->lang['NO_ONLINE_USERS'];
		}

		$mchat_online_userlist = $user->lang['REGISTERED_USERS'] . ' ' . $mchat_online_userlist;

		// Build online listing
		$vars_online = array(
			'MCHAT_ONLINE'	=> array('total_online', 'l_t_user_s', 0),
			'MCHAT_REG'		=> array('visible_online', 'l_r_user_s', 0),
			'MCHAT_HIDDEN'	=> array('hidden_online', 'l_h_user_s', 1),
			'MCHAT_GUEST'		=> array('guests_online', 'l_g_user_s', 0)
		);

		foreach ($vars_online as $l_prefix => $var_ary)
		{
			if ($var_ary[2])
			{
				$l_suffix = '_AND';
			}
			else
			{
				$l_suffix = '';
			}
			switch ($mchat_online_users[$var_ary[0]])
			{
				case 0:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_ZERO_TOTAL' . $l_suffix];
				break;

				case 1:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USER_TOTAL' . $l_suffix];
				break;

				default:
					${$var_ary[1]} = $user->lang[$l_prefix . '_USERS_TOTAL' . $l_suffix];
				break;
			}
		}
		unset($vars_online);

		$l_online_users = sprintf($l_t_user_s, $mchat_online_users['total_online']);
		$l_online_users .= sprintf($l_r_user_s, $mchat_online_users['visible_online']);
		$l_online_users .= sprintf($l_h_user_s, $mchat_online_users['hidden_online']);

		$l_online_users .= sprintf($l_g_user_s, $mchat_online_users['guests_online']);
		
		$mchat_total_online_users = $mchat_online_users['total_online'];
		
		$l_online_time = ($config['load_online_time'] == 1) ? 'VIEW_ONLINE_TIME' : 'VIEW_ONLINE_TIMES';
		$l_online_time = sprintf($user->lang[$l_online_time], $config['load_online_time']);

		//++ Change this to whatever you need it to be, for the output
		// Thanks Obsidian!! :)
		$message = '<p>' . $l_online_users . ' (' . $l_online_time . ')<br />' . $mchat_online_userlist . '</p>';
		echo $message; 		
		exit;
	break;
	
	// Add function...
	case 'add':
	
		// If mChat disabled
		if (!$config['mchat_enable'] || !$mchat_add_mess)
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
				
		// Reguest...
		$message = utf8_normalize_nfc(request_var('message', '', true));
		
		// must have something other than bbcode in the message		
		if (empty($RegEx))
		{
			//let's strip all the bbcode
			$RegEx = '#\[/?[^\[\]]+\]#mi';
		}
		$message_chars = preg_replace($RegEx, '', $message);
		$message_chars = (utf8_strlen(trim($message_chars)) > 0) ? true : false;
			
		if (!$message || !$message_chars)
		{
			// Not Implemented (for jQ AJAX request)
			header('HTTP/1.0 501 Not Implemented');
			// Stop running code
			exit('HTTP/1.0 501 Not Implemented');
		}

		// Flood control
		if (!$mchat_no_flood)
		{
			$mchat_flood_current_time = time();		
			$sql = 'SELECT MAX(message_time) AS last_time FROM ' . MCHAT_TABLE . ' 
				WHERE user_id = ' . $user->data['user_id'];
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if($row['last_time'] > 0 && ($mchat_flood_current_time - $row['last_time']) < (int) $config_mchat['flood_time'])
			{
				// Locked (for jQ AJAX request)
				header('HTTP/1.0 400 Bad Request');
				// Stop running code
				exit('HTTP/1.0 400 Bad Request');
			}
			$db->sql_freeresult($result);
		}
		
		// Message limit
		$message = ($config_mchat['max_message_lngth'] != 0 && utf8_strlen($message) >= $config_mchat['max_message_lngth'] + 3) ? utf8_substr($message, 0, $config_mchat['max_message_lngth']).'...' : $message;
		
		// Add function part code from http://wiki.phpbb.com/Parsing_text
		$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
		generate_text_for_storage($message, $uid, $bitfield, $options, $mchat_allow_bbcode, $mchat_urls, $mchat_smilies);
		// Not allowed bbcodes
		if (!$mchat_allow_bbcode || $config_mchat['bbcode_disallowed'])
		{
			if (!$mchat_allow_bbcode)
			{
				$bbcode_remove = '#\[/?[^\[\]]+\]#mi';
				$message = preg_replace($bbcode_remove, '', $message);
			}
			// disallowed bbcodes
			else if ($config_mchat['bbcode_disallowed'])
			{
				if (empty($bbcode_replace))
				{
					$bbcode_replace = array('#\[(' . $config_mchat['bbcode_disallowed'] . ')[^\[\]]+\]#Usi',
										'#\[/(' . $config_mchat['bbcode_disallowed'] . ')[^\[\]]+\]#Usi',
									);
				}		
				$message = preg_replace($bbcode_replace, '', $message);
			}
		}
		
		$sql_ary = array(		
			'user_id'			=> $user->data['user_id'],
			'user_ip'			=> $user->data['session_ip'],
			'message' 			=> $message,
			'bbcode_bitfield'	=> $bitfield,
			'bbcode_uid'		=> $uid,
			'bbcode_options'	=> $options,
			'message_time'		=> time()
		);
		$sql = 'INSERT INTO ' . MCHAT_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		$db->sql_query($sql);
		
		// Stop run code!
		exit;
	break;

	// Edit function...
	case 'edit':
	
		// edit and delete auths
		// we only get here if the user has the auths as set in default
		// want to recheck the auths for the user as they may have changed
		// in the permissions
		$mchat_edit = $auth->acl_get('u_mchat_edit') ? true : false;
		$mchat_del = $auth->acl_get('u_mchat_delete') ? true : false;
		
		// If mChat disabled and not edit
		if (!$config['mchat_enable'] || !$mchat_edit)
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		// Reguest...
		$message_id = request_var('message_id', 0);
		$message = utf8_normalize_nfc(request_var('message', '', true));
		
		// stop run code
		if (!$message_id )
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		
		// must have something other than bbcode in the message
		if (empty($RegEx))
		{
			//let's strip all the bbcode
			$RegEx = '#\[/?[^\[\]]+\]#mi';
		}
		$message_chars = preg_replace($RegEx, '', $message);
		$message_chars = (utf8_strlen(trim($message_chars)) > 0) ? true : false;			
		if (!$message || !$message_chars)
		{
			// Not Implemented (for jQ AJAX request)
			header('HTTP/1.0 501 Not Implemented');
			// Stop running code
			exit('HTTP/1.0 501 Not Implemented');
		}

		// Message limit
		$message = ($config_mchat['max_message_lngth'] != 0 && utf8_strlen($message) >= $config_mchat['max_message_lngth'] + 3) ? utf8_substr($message, 0, $config_mchat['max_message_lngth']).'...' : $message;
		
		// Edit function part code from http://wiki.phpbb.com/Parsing_text
		$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
		generate_text_for_storage($message, $uid, $bitfield, $options, $mchat_allow_bbcode, $mchat_urls, $mchat_smilies);
		
		// Not allowed bbcodes
		if (!$mchat_allow_bbcode || $config_mchat['bbcode_disallowed'])
		{
			if (!$mchat_allow_bbcode)
			{
				$bbcode_remove = '#\[/?[^\[\]]+\]#mi';
				$message = preg_replace($bbcode_remove, '', $message);
			}
			// disallowed bbcodes
			else if ($config_mchat['bbcode_disallowed'])
			{
				if (empty($bbcode_replace))
				{
					$bbcode_replace = array('#\[(' . $config_mchat['bbcode_disallowed'] . ')[^\[\]]+\]#Usi',
										'#\[/(' . $config_mchat['bbcode_disallowed'] . ')[^\[\]]+\]#Usi',
									);
				}		
				$message = preg_replace($bbcode_replace, '', $message);
			}
		}
		
		$sql_ary = array(
			'message'			=> $message,
			'bbcode_bitfield'	=> $bitfield,
			'bbcode_uid'		=> $uid,
			'bbcode_options'	=> $options
		);
		
		$sql = 'UPDATE ' . MCHAT_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary).' 
			WHERE message_id = ' . $message_id;
		$db->sql_query($sql);
		
		// Message edited...now read it
		$sql = 'SELECT m.*, u.username, u.user_colour, u.user_id as userid
					FROM (' . MCHAT_TABLE . ' m, ' . USERS_TABLE . ' u)
						WHERE m.user_id = u.user_id
					AND m.message_id = ' . $message_id . '
				ORDER BY m.message_id DESC';		
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		
		$message_edit = $row['message'];
		
		decode_message($message_edit, $row['bbcode_uid']);
		$message_edit = str_replace('"', '&quot;', $message_edit); // Edit Fix ;)
		$mchat_ban = ($auth->acl_get('a_authusers') && $user->data['user_id'] != $row['userid']) ? true : false;
        
		$template->assign_block_vars('mchatrow', array(
			'MCHAT_ALLOW_BAN'		=> $mchat_ban,
			'MCHAT_ALLOW_EDIT'		=> $mchat_edit,
			'MCHAT_ALLOW_DEL'		=> $mchat_del,		
			'MCHAT_MESSAGE_EDIT'	=> $message_edit,
			'MCHAT_MESSAGE_ID'		=> $row['message_id'],
			'MCHAT_USERNAME_FULL'	=> get_username_string('full', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
			'MCHAT_USERNAME'		=> get_username_string('username', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
			'MCHAT_USERNAME_COLOR'	=> get_username_string('colour', $row['userid'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
			'MCHAT_USER_IP'			=> $row['user_ip'],
			'MCHAT_U_WHOIS'			=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=whois&amp;ip=' . $row['user_ip']),
			'MCHAT_U_BAN'			=> append_sid("{$phpbb_root_path}adm/index.$phpEx" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[0]=' . $row['user_id'], true, $user->session_id),
			'MCHAT_MESSAGE'			=> censor_text(generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options'])),
			'MCHAT_TIME'			=> $user->format_date($row['message_time'], $config_mchat['date']),
			'MCHAT_CLASS'			=> ($row['message_id'] % 2) ? 1 : 2
		));
		
		// If read mode request set true
		$mchat_read_mode = true;

	break;

	// Delete function...
	case 'delete':
		
		// must have auths to delete
		$mchat_del = ($auth->acl_get('u_mchat_delete')) ? true : false;
		
		// If mChat disabled
		if (!$config['mchat_enable'] || !$mchat_del)
		{ 
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		// Reguest...
		$message_id = request_var('message_id', 0);

		// Stop run delete!
		if (!$message_id)
		{
			// Forbidden (for jQ AJAX request)
			header('HTTP/1.0 403 Forbidden');
			// Stop running code
			exit('HTTP/1.0 403 Forbidden');
		}
		
		// Run delete!
		$sql = 'DELETE FROM ' . MCHAT_TABLE . ' 
			WHERE message_id = ' . $message_id;
		$db->sql_query($sql);
		
		// Stop running code
		exit;
	break;

	// Default function...
	default:
		
		//chat enabled
		if (!$config['mchat_enable'])
		{
			trigger_error($user->lang['MCHAT_ENABLE'], E_USER_NOTICE);
			// Stop running code
			exit;
		}
		
		// check for copyright
		if (!isset($user->lang['MCHAT_COPYRIGHT']) || (trim($user->lang['MCHAT_COPYRIGHT']) == '') || $user->lang['MCHAT_COPYRIGHT'] != $user->lang['MCHAT_COPYRIGHT_CHECK'])
		{
			trigger_error($user->lang['MCHAT_REPLACE_COPYRIGHT'], E_USER_NOTICE);
			
			// Stop running code
			exit;
		}
			
		// If not include in index.php set mchat.php page true
		if (!$mchat_include_index)
		{
			// Yes its custom page...
			$mchat_custom_page = true;

			// If custom page false mchat.php page redirect to index...
			if (!$config_mchat['custom_page'] && $mchat_custom_page)
			{
				// Redirect to previous page
				meta_refresh(3, $mchat_redirect);
				trigger_error($user->lang['MCHAT_NO_CUSTOM_PAGE'].'<br /><br />'.sprintf($user->lang['RETURN_PAGE'], '<a href="'.$mchat_redirect.'">', '</a>'), E_USER_NOTICE);
				
				// Stop running code
				exit;
			}
			
			// user has permissions to view the custom chat?
			if (!$mchat_view && $mchat_custom_page)
			{
				trigger_error($user->lang['NOT_AUTHORISED'], E_USER_WARNING);
				// Stop running code
				exit;				
			}						
			
			// prune the chats if nescessary and amount in ACP not empty
			if ($config_mchat['prune_enable'] && $config_mchat['prune_num'] > 0)
			{
				mchat_prune((int) $config_mchat['prune_num']);
			}

			// if whois true
			if ($config_mchat['whois'])
			{
				// Grab group details for legend display for who is online on the custom page.
				if ($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
				{
					$sql = 'SELECT group_id, group_name, group_colour, group_type FROM ' . GROUPS_TABLE . ' 
						WHERE group_legend = 1 
							ORDER BY group_name ASC';
				}
				else
				{
					$sql = 'SELECT g.group_id, g.group_name, g.group_colour, g.group_type FROM ' . GROUPS_TABLE . ' g 
						LEFT JOIN ' . USER_GROUP_TABLE . ' ug ON (g.group_id = ug.group_id AND ug.user_id = ' . $user->data['user_id'] . ' AND ug.user_pending = 0) 
							WHERE g.group_legend = 1 
								AND (g.group_type <> ' . GROUP_HIDDEN . ' 
									OR ug.user_id = ' . $user->data['user_id'] . ') 
							ORDER BY g.group_name ASC';
				}
				$result = $db->sql_query($sql);
				$legend = array();
				
				while ($row = $db->sql_fetchrow($result))
				{
					$colour_text = ($row['group_colour']) ? ' style="color:#'.$row['group_colour'].'"' : '';
					$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $user->lang['G_'.$row['group_name']] : $row['group_name'];
					if ($row['group_name'] == 'BOTS' || ($user->data['user_id'] != ANONYMOUS && !$auth->acl_get('u_viewprofile')))
					{
						$legend[] = '<span'.$colour_text.'>'.$group_name.'</span>';
					}
					else
					{
						$legend[] = '<a'.$colour_text.' href="'.append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g='.$row['group_id']).'">'.$group_name.'</a>';
					}
				}
				$db->sql_freeresult($result);
				$legend = implode(', ', $legend);
				
				// Assign index specific vars
				$template->assign_vars(array(
					'LEGEND'	=> $legend,
				));
				
				// a list of users using the custom chat page
				// functions_mchat.php
				mchat_users();
			}
		}
		
		// Run code...
		if ($mchat_view)
		{
			// Message row
			$sql = 'SELECT m.*, u.username, u.user_colour
				FROM ' . MCHAT_TABLE . ' m
					LEFT JOIN ' . USERS_TABLE . ' u ON (m.user_id = u.user_id)
				ORDER BY message_id DESC';
			$result = $db->sql_query_limit($sql, $config_mchat['message_limit']);
			$rows = $db->sql_fetchrowset($result);
			
			foreach($rows as $row)
			{
				// edit, delete and permission auths
				$mchat_ban = ($auth->acl_get('a_authusers') && $user->data['user_id'] != $row['user_id']) ? true : false;
				$mchat_edit = ($auth->acl_get('u_mchat_edit') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['user_id'])) ? true : false;
				$mchat_del = ($auth->acl_get('u_mchat_delete') && ($auth->acl_get('m_') || $user->data['user_id'] == $row['user_id'])) ? true : false;
		
				$message_edit = $row['message'];
				decode_message($message_edit, $row['bbcode_uid']);
				$message_edit = str_replace('"', '&quot;', $message_edit); // Edit Fix ;)
				
				$template->assign_block_vars('mchatrow', array(
					'MCHAT_ALLOW_BAN'		=> $mchat_ban,
					'MCHAT_ALLOW_EDIT'		=> $mchat_edit,
					'MCHAT_ALLOW_DEL'		=> $mchat_del,				
					'MCHAT_MESSAGE_EDIT'	=> $message_edit,
					'MCHAT_MESSAGE_ID'		=> $row['message_id'],
					'MCHAT_USERNAME_FULL'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USERNAME'		=> get_username_string('username', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USERNAME_COLOR'	=> get_username_string('colour', $row['user_id'], $row['username'], $row['user_colour'], $user->lang['GUEST']),
					'MCHAT_USER_IP'			=> $row['user_ip'],
					'MCHAT_U_WHOIS'			=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=whois&amp;ip=' . $row['user_ip']),
					'MCHAT_U_BAN'			=> append_sid("{$phpbb_root_path}adm/index.$phpEx" ,'i=permissions&amp;mode=setting_user_global&amp;user_id[0]=' . $row['user_id'], true, $user->session_id),
					'MCHAT_MESSAGE'			=> censor_text(generate_text_for_display($row['message'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options'])),
					'MCHAT_TIME'			=> $user->format_date($row['message_time'], $config_mchat['date']),
					'MCHAT_CLASS'			=> ($row['message_id'] % 2) ? 1 : 2
				));
			}
		
			$db->sql_freeresult($result);
			
			// Write no message
			if (empty($rows))
			{
				// Template function...
				$template->assign_vars(array('MCHAT_NOMESSAGE_MODE' => true));
			}
			
			// Smile row
			if ($mchat_smilies)
			{
				$sql = 'SELECT * FROM ' . SMILIES_TABLE . ' 
					WHERE display_on_posting = 1 
						ORDER BY smiley_order';
				$result = $db->sql_query($sql, 3600);
				$smilies = array();
				while ($row = $db->sql_fetchrow($result))
				{
					if(empty($smilies[$row['smiley_url']]))
					{
						$smilies[$row['smiley_url']] = $row;
					}
				}
				$db->sql_freeresult($result);
		 
				if(sizeof($smilies))
				{
					foreach($smilies as $row)
					{
						$template->assign_block_vars('mchatsmilerow', array(
							'MCHAT_SMILE_CODE'		=> $row['code'],
							'MCHAT_SMILE_EMOTION'	=> $row['emotion'],
							'MCHAT_SMILE_IMG'		=> $phpbb_root_path.$config['smilies_path'].'/'.$row['smiley_url'],
							'MCHAT_SMILE_WIDTH'		=> $row['smiley_width'],
							'MCHAT_SMILE_HEIGHT'	=> $row['smiley_height']
						));
					}
				}
				else
				{
					$template->assign_vars(array('MCHAT_NO_SMILE' => true));   
				}
			}
		}
	break;
}


// Template function...
$template->assign_vars(array(
	'MCHAT_FILE_NAME'		=> append_sid("{$phpbb_root_path}mchat.$phpEx"),
	'MCHAT_REFRESH_JS'		=> 1000 * $config_mchat['refresh'],
	'MCHAT_WHOIS_REFRESH'	=> 1000 * $config_mchat['whois_refresh'],
	'MCHAT_REFRESH_HTML'	=> sprintf($user->lang['MCHAT_AUTOUPDATE'], $config_mchat['refresh']),
	'MCHAT_ADD_MESSAGE'		=> $mchat_add_mess,
	'MCHAT_READ_MODE'		=> $mchat_read_mode,
	'MCHAT_ARCHIVE_MODE'	=> $mchat_archive_mode,
	'MCHAT_ALLOW_SMILES'	=> $mchat_smilies,
	'MCHAT_ALLOW_IP'		=> $mchat_ip,
	'MCHAT_ALLOW_BBCODES'	=> ($mchat_allow_bbcode && $config['allow_bbcode']) ? true : false,
	'MCHAT_ENABLE'			=> $config['mchat_enable'],
	'MCHAT_ARCHIVE_URL'		=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=archive'),
	'MCHAT_CUSTOM_PAGE'				=> $mchat_custom_page,
	'MCHAT_CUSTOM_PAGE_WHOIS'		=> $config_mchat['whois'],
	'MCHAT_WHOIS_REFRESH_EXPLAIN'	=> sprintf($user->lang['WHO_IS_REFRESH_EXPLAIN'], $config_mchat['whois_refresh']),
	'MCHAT_READ_ARCHIVE_BUTTON'		=> $mchat_read_archive,
	'MCHAT_FOUNDER'			=> $mchat_founder,
	'MCHAT_CLEAN_URL'		=> append_sid("{$phpbb_root_path}mchat.$phpEx", 'mode=clean'),

	'S_MCHAT_LOCATION'		=> $config_mchat['location']
));

// Template
if (!$mchat_include_index)
{
	page_header($user->lang['MCHAT_TITLE']);
		$template->set_filenames(array('body' => 'mchat_body.html'));
	page_footer();
}
?>