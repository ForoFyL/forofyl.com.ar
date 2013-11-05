<?php
/**
 * @package phpBB3
 * @version 1.0.8 2009-12-22
 * @copyright (c) 2009 RMcGirr83
 * @copyright (c) 2007 eviL3
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License(
 */
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
/**
* contact_change_auth
* thanks to poppertom69 for the idea..and some of the code
* 
*/
function contact_change_auth($bot_id, $mode = 'replace', $bkup_data = false)
{
	global $auth, $db, $config, $user;
	
	switch($mode)
	{
		// we want auths for the user making the contact 
		// and a post count high enough in case queing is enabled
		// in 3.0.6 auths are no longer a concern thanks to "force_approved_state"
		case 'replace':

			$bkup_data = array(
				'user_backup'	=> $user->data,
			);
						
			// sql to get the bots info
			$sql = 'SELECT *
				FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $bot_id;
			$result	= $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			// reset the current users info to that of the bot	
			$user->data = array_merge($user->data, $row);
			
			unset($row);
			
			return $bkup_data;					
			
		break;
		
		// now we restore the users stuff
		case 'restore':

			$user->data = $bkup_data['user_backup'];

			unset($bkup_data);
		break;
	}
}
/**
* contact_check
* @param $forum_id, the forum id selected in ACP
* @param $forum_name returned from contact_check_forum
* ensures postable forum and correct "bot"
*/
function contact_check($forum_id = false, $bot_id = false, $mode, $method = false)
{
	global $phpbb_root_path, $phpEx, $cache, $config;
	global $auth, $db, $user;
	
	$user->add_lang('mods/info_acp_contact');
	// the servers url 
	$server_url = generate_board_url();	
	
	switch($mode)
	{
		// check for a valid forum
		case 'contact_check_forum':				

			$sql = 'SELECT forum_name
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id . '
			AND forum_type = ' . FORUM_POST;
			$result = $db->sql_query($sql);	
	
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
					
			// we didn't get a result
			// send an email if board enabled					
			if (!$row && $config['email_enable'])
			{
				// send an email to the board default
				$email_template = 'contact_error_forum';
				$email_message = sprintf($user->lang['CONTACT_BOT_FORUM_MESSAGE'], $user->data['username'], $config['sitename'], $server_url);
				contact_send_email($email_template, $email_message);
				
				// disable the contact mod
				set_config('contact_enable', 0);
						
				// add an entry into the error log
				add_log('critical', 'LOG_CONTACT_FORUM_INVALID', $forum_id);		

				$meta_info = append_sid("{$phpbb_root_path}index.$phpEx");
				meta_refresh(5, $meta_info);
				
				// show a message to the user
				$message = $user->lang['CONTACT_BOT_ERROR'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);		
			}
			elseif (!$row)
			{
				// disable the contact mod
				set_config('contact_enable', 0);
						
				// add an entry into the error log
				add_log('critical', 'LOG_CONTACT_FORUM_INVALID', $forum_id);
					
				$message = sprintf($user->lang['CONTACT_DISABLED'], '<a href="mailto:' . $config['board_contact'] . '">', '</a>');
				// show message that contact form is disabled
				trigger_error($message);				
			}

		break;
	
		// check for a valid bot..we need correct auths
		// auths not needed in 3.0.6..but we still need the bot
		case 'contact_check_bot':
			
			$sql = 'SELECT username
				FROM ' . USERS_TABLE . '
					WHERE user_id = ' . (int) $bot_id;
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			
			// we didn't get a result
			// send an email if board enabled
			if (!$row && $config['email_enable'])
			{
				// send an email to the board default
				$email_template = 'contact_error_user';
				$email_message = sprintf($user->lang['CONTACT_BOT_USER_MESSAGE'], $user->data['username'], $config['sitename'], $server_url);
				contact_send_email($email_template, $email_message);
				
				// disable the contact mod
				set_config('contact_enable', 0);
				
				// add an entry into the log error table
				add_log('critical', 'LOG_CONTACT_BOT_INVALID', $bot_id);
	
				$meta_info = append_sid("{$phpbb_root_path}index.$phpEx");
				meta_refresh(5, $meta_info);

				// show a message to the user
				$message = $user->lang['CONTACT_BOT_ERROR'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);		
			}
			elseif (!$row)
			{
				// disable the contact mod
				set_config('contact_enable', 0);
				
				// add an entry into the log error table
				add_log('critical', 'LOG_CONTACT_BOT_INVALID', $bot_id);
				
				// show a message to the user
				$message = sprintf($user->lang['CONTACT_DISABLED'], '<a href="mailto:' . $config['board_contact'] . '">', '</a>');
				trigger_error($message);
			}
		break;
					
		case 'contact_nobody':
		
			//this is only called if there are no contact admins available
			// for pm'ing or for emailing to per the preferences set by the admin user in their profiles

			if ($method == CONTACT_METHOD_EMAIL)
			{
				$error = $user->lang['EMAIL'];
			}
			else
			{
				$error = $user->lang['PRIVATE_MESSAGE'];
			}

			// only send an email if the board allows it
			if ($config['email_enable'])
			{				
				// send an email to the board default
				$email_template = 'contact_error_user';
				$email_message = sprintf($user->lang['CONTACT_BOT_NONE'], $user->data['username'], $config['sitename'], $error, $server_url);
				contact_send_email($email_template, $email_message);
								
				// disable the contact mod
				set_config('contact_enable', 0);	
				
				// add an entry into the log error table
				add_log('critical', 'LOG_CONTACT_NONE', $error);

				$meta_info = append_sid("{$phpbb_root_path}index.$phpEx");
				meta_refresh(5, $meta_info);			
				// show a message to the user
				$message = $user->lang['CONTACT_BOT_ERROR'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
				trigger_error($message);
			}
			else
			{
				// disable the contact mod
				set_config('contact_enable', 0);
						
				// add an entry into the log error table
				add_log('critical', 'LOG_CONTACT_NONE', $error);
					
				$message = sprintf($user->lang['CONTACT_DISABLED'], '<a href="mailto:' . $config['board_contact'] . '">', '</a>');
				// show message that contact form is disabled
				trigger_error($message);
			}			
		break;
	}
	return;
}
/**
 * contact_send_email
 * @param $email_template, the email template to use
 * @param $email_message, the message we are sending
 * sends an email to the board default if an error occurs
 */
function contact_send_email($email_template, $email_message)
{
	global $phpbb_root_path, $phpEx;
	global $user, $config;
	
	if (!function_exists('send'))
	{
		include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
	}
	
	// don't use the queue send the email immediately if not sooner
	$messenger = new messenger(false);
			
	// Email headers
	$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
	$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
	$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
	$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
				
	$messenger->template($email_template, $config['default_lang']);
	$messenger->to($config['board_email']);
	$messenger->from($config['server_name']);

	$messenger->assign_vars(array(
		'SUBJECT'		=> $user->lang['CONTACT_BOT_SUBJECT'],
		'EMAIL_SIG'  	=> $config['board_email_sig'],
		'MESSAGE'		=> $email_message,
	));
	
	$messenger->send(NOTIFY_EMAIL);
	
	return;
}

/**
 * contact_make_select
 * 
 * @param array $input_ary
 * @return string Select html
 * for drop down reasons
 */
function contact_make_select($input_ary, $selected)
{
	// only accept arrays, no empty ones
	if (!is_array($input_ary) || !sizeof($input_ary))
	{
		return;
	}
	
	// If selected isn't in the array, use first entry
	if (!in_array($selected, $input_ary))
	{
		$selected = $input_ary[0];
	}
	
	$select = '';
	foreach ($input_ary as $item)
	{
		//$item = htmlspecialchars($item);
		$item_selected = ($item == $selected) ? ' selected="selected"' : '';
		$select .= '<option value="' . $item . '"' . $item_selected . '>' . $item . '</option>';
	}
	return $select;
}

/**
 * contact_url
 * 
 * @param array $auth_url
 * @return string $auth_msg
 * for checking if contact is allowing urls
 */
function contact_url($auth_url)
{
	global $user, $config, $img_status;
	
	// initialize a variable or two
	$auth_msg = '';
	
	// our TLD array..add to or subtract from to suit your needs
	$tld_list = array(
		'ac', 'ad', 'ae', 'aero', 'af', 'ag', 'ai', 'al',
		'am', 'an', 'ao', 'aq', 'ar', 'arpa', 'arts', 'as',
		'at', 'au', 'aw', 'az', 'ba', 'bb', 'bd', 'be',
		'bf', 'bg', 'bh', 'bi', 'biz', 'bj', 'bm', 'bn',
		'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz',
		'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck',
		'cl', 'cm', 'cn', 'co', 'com', 'coop', 'cr', 'cu',
		'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm',
		'do', 'dz', 'ec', 'edu', 'ee', 'eg', 'eh', 'er',
		'es', 'et', 'fi', 'firm', 'fj', 'fk', 'fm', 'fo',
		'fr', 'fx', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh',
		'gi', 'gl', 'gm', 'gn', 'gov', 'gp', 'gq', 'gr',
		'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn',
		'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in',
		'info', 'int', 'io', 'iq', 'ir', 'is', 'it', 'je',
		'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km',
		'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb',
		'lc', 'li', 'lk', 'lr', 'ls', 'lt', 'lu', 'lv',
		'ly', 'ma', 'mc', 'md', 'mg', 'mh', 'mil', 'mk',
		'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms',
		'mt', 'mu', 'museum', 'mv', 'mw', 'mx', 'my', 'mz',
		'na', 'name', 'nato', 'nc', 'ne', 'net', 'nf', 'ng',
		'ni', 'nl', 'no', 'np', 'nom', 'np', 'nr', 'nu',
		'nz', 'om', 'org', 'pa', 'pe', 'pf', 'pg', 'ph',
		'pk', 'pl', 'pn', 'pr', 'pro', 'pt', 'pw', 'py',
		'qa', 're', 'rec', 'ro', 'ru', 'rw', 'sa', 'sb',
		'sc', 'sd', 'se', 'sg', 'sh', 'shop', 'si', 'sj',
		'sk', 'sl', 'sm', 'sn', 'so', 'sr', 'st', 'su',
		'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th',
		'tj', 'tk', 'tm', 'tn', 'to', 'tp', 'tr', 'tt',
		'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us',
		'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn',
		'vu', 'web', 'wf', 'ws', 'ye', 'yt', 'yu', 'za',
		'zm', 'zr', 'zw'
	);
							
	$disallowed_tld = implode('|',$tld_list);

	// check the whole darn thang now for any TLD's
	
	preg_match("#(([a-z0-9\-_.+]+)@)?([a-z]{3,6}://)?(((?:www.)?\b[a-z0-9\-_]+)\.($disallowed_tld)(\.($disallowed_tld))?\b)#i", $auth_url, $match);

	//free up a tad of memory
	unset($auth_url);

	// we have a match..uhoh, someone's being naughty
	// time to slap 'em up side the head
	if (sizeof($match))
	{
		$auth_msg = array(sprintf($user->lang['URL_UNAUTHED'], $match[0]));
	}

return ($auth_msg);
}
?>