<?php
/** 
 *
 *
 * @package phpBB3
 * @version	1.0.10 2010-01-22
 * @copyright (c) 2009 RMcGirr83
 * @copyright (c) 2007 eviL<3
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 *
 */

/**
 * @ignore
 */
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_contact.' . $phpEx);
include($phpbb_root_path . 'includes/constants_contact.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

// redirect bots
if ($user->data['is_bot'])
{
	redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
}

// check to make sure mod is enabled
if (!$config['contact_enable'])
{
	trigger_error ('CONTACT_DISABLED');
}

// language(s) we need...why re-invent the wheel?
$user->setup(array('ucp','mods/contact'));
$user->add_lang('posting');

// Grab the config entries in teh ACP
// write them to the cache
if (($config_contact = $cache->get('_contact_config')) === false)
{
	$sql = 'SELECT * 
		FROM ' . CONTACT_CONFIG_TABLE;
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);	
	$db->sql_freeresult($result);

	$config_contact = array(
		'contact_confirm'			=> $row['contact_confirm'],
		'contact_confirm_guests'	=> $row['contact_confirm_guests'],
		'contact_max_attempts'		=> $row['contact_max_attempts'],
		'contact_method'			=> $row['contact_method'],
		'contact_bot_user'			=> $row['contact_bot_user'],
		'contact_bot_forum'			=> $row['contact_bot_forum'],
		'contact_reasons'			=> $row['contact_reasons'],
		'contact_founder_only'		=> $row['contact_founder_only'],
		'contact_bbcodes_allowed'	=> $row['contact_bbcodes_allowed'],
		'contact_urls_allowed'		=> $row['contact_urls_allowed'],
		'contact_smilies_allowed'	=> $row['contact_smilies_allowed'],
		'contact_bot_poster'		=> $row['contact_bot_poster'],
		'contact_attach_allowed'	=> $row['contact_attach_allowed'],
		'contact_username_chk'		=> $row['contact_username_chk'],
		'contact_email_chk'			=> $row['contact_email_chk'],
	);
	// cache this data forever, can only change in ACP
	// this improves performance
	$cache->put('_contact_config', $config_contact);
}

// Trigger error if board email is disabled but email set in config for contact
if (!$config['email_enable'] && $config_contact['contact_method'] == CONTACT_METHOD_EMAIL)
{
	set_config('contact_enable', 0);
	$message = sprintf($user->lang['CONTACT_MAIL_DISABLED'], '<a href="mailto:' . $config['board_contact'] . '">', '</a>');

	trigger_error($message);
}

// check to make sure the contact bot forum is legit for posting
// has to be able to accept posts
if ($config_contact['contact_method'] == CONTACT_METHOD_POST)
{
	// from includes/functions_contact.php
	// check to make sure forum is, ermmm, forum 
	// not link and not cat
	contact_check($config_contact['contact_bot_forum'], '', 'contact_check_forum');
}
else if (in_array($config_contact['contact_method'], array(CONTACT_METHOD_EMAIL, CONTACT_METHOD_PM)))
{
	// quick check to ensure our "bot" is good
	contact_check('', $config_contact['contact_bot_user'], 'contact_check_bot');
}

// Only have contact CAPTCHA confirmation for guests, if the option is enabled
if ($user->data['user_id'] != ANONYMOUS && $config_contact['contact_confirm_guests'])
{
	$config_contact['contact_confirm'] = false;
}

// Our request variables
$submit		= (isset($_POST['submit'])) ? true : false;
$preview	= (isset($_POST['preview'])) ? true : false;
$refresh	= (isset($_POST['add_file']) || isset($_POST['delete_file'])) ? true : false;

//a variable
$date	= gmstrftime("%A %d-%b-%y %T %Z", time());

// bbcode and smilies allowed?
// not for emails...at least don't show the buttons/icons
// removal of bbcodes occurs later in contact_email
$bbcodes_allowed	= ($config_contact['contact_bbcodes_allowed'] && $config_contact['contact_method'] != CONTACT_METHOD_EMAIL) ? true : false;

// allow attachments?
$attachments_allowed = ($config_contact['contact_attach_allowed'] && $config_contact['contact_method'] != CONTACT_METHOD_EMAIL) ? true : false;

// in 3.0.6 smilies no longer care about bbcode being allowed
// changed in version 1.0.6 of mod to reflect this
$smilies_allowed	= ($config_contact['contact_smilies_allowed'] && $config_contact['contact_method'] != CONTACT_METHOD_EMAIL) ? true : false;
$urls_allowed		= ($bbcodes_allowed && $config_contact['contact_urls_allowed'] && $config_contact['contact_method'] != CONTACT_METHOD_EMAIL) ? true : false;

// we need the following pre-set for the dropdown of the contact reasons
// in the template vars
$contact_data = array(
	'contact_reason'	=> utf8_normalize_nfc(request_var('contact_reason', '', true)),			
);			

// add the form key
add_form_key('contact');

// lol @ obSCENEdian..thanks for the hints ;)
// &*^@#ING CAPTCHA
// Visual Confirmation - The CAPTCHA kicks in here
if ($config_contact['contact_confirm'])
{
   include($phpbb_root_path . 'includes/captcha/captcha_factory.' . $phpEx);
   $captcha =& phpbb_captcha_factory::get_instance($config['captcha_plugin']);
   $captcha->init(CONFIRM_CONTACT);
}
// only get attachments/use the message parser 
// for methods other than email
if($config_contact['contact_method'] != CONTACT_METHOD_EMAIL)
{
	$message_parser = new parse_message();
}

if ($submit || $preview || $refresh)
{
	// our data array
	$contact_data = array(
		'username'			=> ($user->data['user_id'] != ANONYMOUS) ?  $user->data['username'] : utf8_normalize_nfc(request_var('username', '', true)),
		'email'				=> ($user->data['user_id'] != ANONYMOUS) ? $user->data['user_email'] : strtolower(request_var('email', '')),
		'email_confirm'		=> ($user->data['user_id'] != ANONYMOUS) ? $user->data['user_email'] : strtolower(request_var('email_confirm', '')),
		'contact_reason'	=> utf8_normalize_nfc(request_var('contact_reason', '', true)),	
		'contact_subject'	=> utf8_normalize_nfc(request_var('contact_subject', '', true)),
		'contact_message'	=> utf8_normalize_nfc(request_var('message', '', true)),		
	);			

	$error = array();
	
	// check form
	if (($submit || $preview) && !check_form_key('contact'))
	{
		$error[] = $user->lang['FORM_INVALID'];
	}
	
	// let's check our inputs against the database..but only for unregistered user and only if so set in ACP
	if (!$user->data['is_registered'] && !$refresh)
	{
		// if set in ACP check against already registered email/usernames
		if ($config_contact['contact_username_chk'] && !$config_contact['contact_email_chk'])
		{
			$error = validate_data($contact_data, array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username', '')),
				));
		}
		else if ($config_contact['contact_email_chk'] && !$config_contact['contact_username_chk'])
		{
			$error = validate_data($contact_data, array(
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
			));
		}
		else if ($config_contact['contact_email_chk'] && $config_contact['contact_username_chk'])
		{
			$error = validate_data($contact_data, array(
				'username'			=> array(
					array('string', false, $config['min_name_chars'], $config['max_name_chars']),
					array('username', '')),
				'email'				=> array(
					array('string', false, 6, 60),
					array('email')),
				'email_confirm'		=> array('string', false, 6, 60),
			));
		}
	}

	if (!$refresh)
	{	
		// Validate message and subject
		// and reason and username and email..oh my
		
		if (utf8_clean_string($contact_data['username']) === '')
		{
			$error[] = $user->lang['CONTACT_NO_NAME'];
		}
		
		if (!preg_match('/^' . get_preg_expression('email') . '$/i', $contact_data['email']))
		{
			$error[] = $user->lang['EMAIL_INVALID_EMAIL'];
		}
		
		if ($contact_data['email'] != $contact_data['email_confirm'])
		{
			$error[] = $user->lang['NEW_EMAIL_ERROR'];
		}		
		
		if (!empty($config_contact['contact_reasons']) && !in_array($contact_data['contact_reason'], explode("\n", $config_contact['contact_reasons'])))
		{
			$error[] = $user->lang['CONTACT_NO_REASON'];
		}		
		
		if (utf8_clean_string($contact_data['contact_subject']) === '')
		{
			$error[] = $user->lang['CONTACT_NO_SUBJ'];
		}
		
		if (utf8_clean_string($contact_data['contact_message']) === '')
		{
			$error[] = $user->lang['CONTACT_NO_MSG'];
		}		
	}
	
	// CAPTCHA check
	if ($config_contact['contact_confirm'] && !$captcha->is_solved())
	{
		$vc_response = $captcha->validate();
		// don't show an error message on refresh
		if ($vc_response !== false && !$refresh)
		{
			$error[] = $vc_response;
		}
		// Make sure we've not submitted it too many times
		if ($config_contact['contact_max_attempts'] && ($captcha->get_attempt_count() > $config_contact['contact_max_attempts']))
		{
			$message = $user->lang['CONTACT_TOO_MANY'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');      
			trigger_error($message);
		}      
	}
	
	// pretty up the user name..but only for non emails
	if (in_array($config_contact['contact_method'], array(CONTACT_METHOD_PM, CONTACT_METHOD_POST)))
	{
		$user_name = $user->data['is_registered'] ? get_username_string('full', $user->data['user_id'], $user->data['username'], $user->data['user_colour']) : htmlspecialchars_decode($contact_data['username']);
	}
	else
	{
		$user_name = htmlspecialchars_decode($contact_data['username']);
	}		
	
	if ($config_contact['contact_method'] != CONTACT_METHOD_EMAIL)
	{
		// change the users stuff
		if ($config_contact['contact_bot_poster'] == CONTACT_POST_ALL || ($config_contact['contact_bot_poster'] == CONTACT_POST_GUEST && !$user->data['is_registered']))
		{		
			$contact_perms = contact_change_auth($config_contact['contact_bot_user']);
		}
		// only get attachments/use the message parser 
		// for methods other than email
		$message_parser = new parse_message();
		
		// check to see if urls are allowed
		if(!$config_contact['contact_urls_allowed'])
		{
			$auth_msg = contact_url($contact_data['contact_message']);
			if(!empty($auth_msg))
			{
				$message_parser->warn_msg = $auth_msg;
			}
		}						
		
		// pretty up the message for posts and pms
		$contact_message = censor_text(trim('[quote] ' . $contact_data['contact_message'] . '[/quote]'));
		
		// bbcodes allowed?  If not get rid of them
		if (!$config_contact['contact_bbcodes_allowed'])
		{
			$contact_regex = '#\[/?[^\[\]]+\]#';
			$contact_message = preg_replace($contact_regex, '', $contact_message);
		}
		// there may not be a reason entered in the ACP...so change the template to reflect this
		if(!empty($config_contact['contact_reasons']))
		{
			$contact_message = sprintf($user->lang['CONTACT_TEMPLATE'], $user_name, $contact_data['email'], $user->ip, $date, $contact_data['contact_reason'], $contact_data['contact_subject'], $contact_message);
		}
		else
		{
			$contact_message = sprintf($user->lang['CONTACT_TEMPLATE_NO_REASON'], $user_name, $contact_data['email'], $user->ip, $date, $contact_data['contact_subject'], $contact_message);
		}
		
		$message_parser->get_submitted_attachment_data();
		$message_parser->message = $contact_message;
		
		// Parse Attachments - before checksum is calculated
		if($config_contact['contact_method'] != CONTACT_METHOD_PM)
		{
			$message_parser->parse_attachments('fileupload', 'post', $config_contact['contact_bot_forum'], $submit, $preview, $refresh);
		}
		else
		{
			$message_parser->parse_attachments('fileupload', 'post', $config_contact['contact_bot_forum'], $submit, $preview, $refresh, true);
		}
		
		// Grab md5 'checksum' of new message
		$message_md5 = md5($message_parser->message);		

		if (sizeof($message_parser->warn_msg))
		{
			$error[] = implode('<br />', $message_parser->warn_msg);
			$message_parser->warn_msg = array();
		}
		
		$message_parser->parse($bbcodes_allowed, $urls_allowed, $smilies_allowed, true, false, true, $urls_allowed);

		// On a refresh we do not care about message parsing errors
		if (sizeof($message_parser->warn_msg) && $refresh)
		{
			$message_parser->warn_msg = array();
		}
		//Restore user
		if ($config_contact['contact_bot_poster'] == CONTACT_POST_ALL || ($config_contact['contact_bot_poster'] == CONTACT_POST_GUEST && isset($contact_perms)))
		{		
			contact_change_auth('', 'restore', $contact_perms);
		}
	}
		
	// Replace "error" strings with their real, localised form
	$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);

	// here we go
	if ($submit && !sizeof($error))
	{
		$subject = censor_text($contact_data['contact_subject']);
		
		// change who the message is to come from
		if ($config_contact['contact_method'] != CONTACT_METHOD_EMAIL && $config_contact['contact_bot_poster'] == CONTACT_POST_ALL || ($config_contact['contact_bot_poster'] == CONTACT_POST_GUEST && !$user->data['is_registered']))
		{
			// change user
			$contact_perms = contact_change_auth($config_contact['contact_bot_user']);
		}

		if ($config_contact['contact_method'] != CONTACT_METHOD_POST)
		{
			$sql_where = '';
			// Only founders...maybe
			if ($config_contact['contact_founder_only'])
			{
				$sql_where .= ' WHERE user_type = ' . USER_FOUNDER;
			}
			else
			{
				// Grab an array of user_id's with admin permissions
				$admin_ary = $auth->acl_get_list(false, 'a_', false);
				$admin_ary = (!empty($admin_ary[0]['a_'])) ? $admin_ary[0]['a_'] : array();

				if ($config_contact['contact_method'] == CONTACT_METHOD_EMAIL && sizeof($admin_ary))
				{
					$sql_where .= ' WHERE ' . $db->sql_in_set('user_id', $admin_ary) . ' AND user_allow_viewemail = 1';
				}
				else if ($config_contact['contact_method'] == CONTACT_METHOD_PM && sizeof($admin_ary))
				{
					$sql_where .= ' WHERE ' . $db->sql_in_set('user_id', $admin_ary) . ' AND user_allow_pm = 1';
				}
			}
	
			$sql = 'SELECT user_id, username, user_email, user_lang, user_jabber, user_notify_type
				FROM ' . USERS_TABLE . ' ' .
				$sql_where;
			$result = $db->sql_query($sql);
			$contact_users = $db->sql_fetchrowset($result);
			$db->sql_freeresult($result);
			
			// we didn't get a soul
			if (!sizeof($contact_users))
			{
				// we have no one to send anything to
				// notify the board default
				contact_check('', '', 'contact_nobody', (int) $config_contact['contact_method']);
			}
		}
				
		switch ($config_contact['contact_method'])
		{
			case CONTACT_METHOD_PM:
				// Send using PMs
				// Thanks to Handymans handy tutorial :D
				if (!function_exists('submit_pm'))
				{
					include($phpbb_root_path . 'includes/functions_privmsgs.' . $phpEx);
				}
						
				$pm_data = array(
					'from_user_id'		=> (int) $user->data['user_id'],
					'icon_id'			=> 0,
					'from_user_ip'		=> $user->data['user_ip'],
					'from_username'		=> (string) $user->data['username'],
					'enable_sig'		=> false,
					'enable_bbcode'		=> (bool) $bbcodes_allowed,
					'enable_smilies'	=> (bool) $smilies_allowed,
					'enable_urls'		=> (bool) $urls_allowed,
					'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
					'bbcode_uid'		=> $message_parser->bbcode_uid,
					'message'			=> $message_parser->message,
					'attachment_data'	=> $message_parser->attachment_data,
					'filename_data'		=> $message_parser->filename_data,
				);
				
				// Loop through our list of users
				for ($i = 0, $size = sizeof($contact_users); $i < $size; $i++)
				{
						$pm_data['address_list'] = array('u' => array($contact_users[$i]['user_id'] => 'to'));
						submit_pm('post', $subject, $pm_data, false);					
				}

			break;

			case CONTACT_METHOD_POST:
						
				// Create a new post
				// Many thanks to paul999 for helping me with this! evil<3
				// updated to 3.0.4 - RMcGirr83
				// ..and again to 3.0.6
				if(!function_exists('submit_post'))
				{
					include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
				}

				$sql = 'SELECT forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . (int) $config_contact['contact_bot_forum'];
				$result = $db->sql_query($sql);
				$forum_name = $db->sql_fetchfield('forum_name');
				$db->sql_freeresult($result);
				
				$post_data = array(
					'forum_id'			=> (int) $config_contact['contact_bot_forum'],
					'icon_id'			=> false,

					'enable_sig'		=> true,
					'enable_bbcode'		=> (bool) $bbcodes_allowed,
					'enable_smilies'	=> (bool) $smilies_allowed,
					'enable_urls'		=> (bool) $urls_allowed,

					'message_md5'		=> (string) $message_md5,
								
					'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
					'bbcode_uid'		=> $message_parser->bbcode_uid,
					'message'			=> $message_parser->message,
					'attachment_data'	=> $message_parser->attachment_data,
					'filename_data'		=> $message_parser->filename_data,
					'poster_ip'			=> $user->ip,
					
					'post_edit_locked'	=> 0,
					'topic_title'		=> $subject,
					'notify_set'		=> false,
					'notify'			=> false,
					'post_time'			=> time(),
					'forum_name'		=> $forum_name,
					'enable_indexing'	=> true,

					// 3.0.6
					'force_approved_state'	=> true,
				);
				
				$poll = array();

				// Submit the post!
				submit_post('post', $subject, $user->data['username'], POST_NORMAL, $poll, $post_data);
				
			break;
				
			case CONTACT_METHOD_EMAIL:
			default:
				
				// Send using email (default)..first remove all bbcodes
				$bbcode_remove = '#\[/?[^\[\]]+\]#';
				$message = censor_text($contact_data['contact_message']);
				$message = preg_replace($bbcode_remove, '', $message);
				$message = htmlspecialchars_decode($message);

				// Some of the code borrowed from includes/ucp/ucp_register.php
				// The first argument of messenger::messenger() decides if it uses the message queue (which we will)
				if (!function_exists('send'))
				{
					include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
				}
				$messenger = new messenger(true);

				// Email headers
				$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
				$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
				$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
				$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
				
				// Loop through our list of users
				for ($i = 0, $size = sizeof($contact_users); $i < $size; $i++)
				{
						if (!empty($contact_data['contact_reason']))
						{
							$messenger->template('contact', $contact_users[$i]['user_lang']);
						}
						else
						{
							$messenger->template('contact_no_reason', $contact_users[$i]['user_lang']);
						}
						$messenger->to($contact_users[$i]['user_email'], $contact_users[$i]['username']);
						$messenger->im($contact_users[$i]['user_jabber'], $contact_users[$i]['username']);
						$messenger->from($contact_data['email']);
						$messenger->replyto($contact_data['email']);
						
						$messenger->assign_vars(array(
							'ADM_USERNAME'	=> htmlspecialchars_decode($contact_users[$i]['username']),
							'SITENAME'		=> htmlspecialchars_decode($config['sitename']),
							'USER_IP'		=> $user->ip,
							'USERNAME'		=> $user_name,
							'USER_EMAIL'	=> htmlspecialchars_decode($contact_data['email']),
							'DATE'			=> $date,
							'REASON'		=> htmlspecialchars_decode($contact_data['contact_reason']),
							
							'SUBJECT'		=> htmlspecialchars_decode($subject),
							'MESSAGE'		=> $message,
						));
			
						$messenger->send($contact_users[$i]['user_notify_type']);
				}
				
				// Save emails in the queue to prevent timeouts
				$messenger->save_queue();
				
			break;
		}
		//reset captcha
		if ($config_contact['contact_confirm'] && (isset($captcha) && $captcha->is_solved() === true))
		{
			$captcha->reset();
		}
		
		// restore permissions
		if ($config_contact['contact_method'] != CONTACT_METHOD_EMAIL && ($config_contact['contact_bot_poster'] == CONTACT_POST_ALL || ($config_contact['contact_bot_poster'] == CONTACT_POST_GUEST && isset($contact_perms))))
		{
			//Restore user
			contact_change_auth('', 'restore', $contact_perms);	
		}		
	
		// Everything went fine, output a confirmation page
		$meta_info = append_sid("{$phpbb_root_path}index.$phpEx");
		meta_refresh(5, $meta_info);
		$message = $user->lang['CONTACT_MSG_SENT'] . '<br /><br />' . sprintf($user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$phpbb_root_path}index.$phpEx") . '">', '</a>');
		trigger_error($message);
	}
}

// Preview
if ($preview && !sizeof($error))
{

	$preview_message = $message_parser->format_display($bbcodes_allowed, $urls_allowed, $smilies_allowed, false);

	$preview_subject = censor_text($contact_data['contact_subject']);
	
	// Attachment Preview
	if (sizeof($message_parser->attachment_data))
	{
		$template->assign_var('S_HAS_ATTACHMENTS', true);

		$update_count = array();
		$attachment_data = $message_parser->attachment_data;

		parse_attachments($config_contact['contact_bot_forum'], $preview_message, $attachment_data, $update_count, true);

		foreach ($attachment_data as $i => $attachment)
		{
			$template->assign_block_vars('attachment', array(
				'DISPLAY_ATTACHMENT'	=> $attachment)
			);
		}
		unset($attachment_data);
	}

	if (!sizeof($error))
	{
		$template->assign_vars(array(
			'PREVIEW_SUBJECT'		=> $preview_subject,
			'PREVIEW_MESSAGE'		=> $preview_message,

			'S_DISPLAY_PREVIEW'		=> true)
		);
	}
}

// only get attachments/use the message parser 
// for methods other than email
if ($config_contact['contact_method'] != CONTACT_METHOD_EMAIL)
{
	$attachment_data = $message_parser->attachment_data;
	$filename_data = $message_parser->filename_data;
	
	posting_gen_attachment_entry($attachment_data, $filename_data, $attachments_allowed);
}

// Visual Confirmation - Show images
$s_hidden_fields = array();

if ($config_contact['contact_confirm'])
{
	$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
}
$s_hidden_fields = build_hidden_fields($s_hidden_fields);


if ($config_contact['contact_confirm'] && !$captcha->is_solved())
{
	$template->assign_vars(array(
		'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
	));
}

// attachments and uploads
$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off' || !$attachments_allowed) ? '' : ' enctype="multipart/form-data"';

// output the display
$template->assign_vars(array(
	'USERNAME'			=> isset($contact_data['username']) ? $contact_data['username'] : '',
	'EMAIL'				=> isset($contact_data['email']) ? $contact_data['email'] : '',
	'EMAIL_CONFIRM' 	=> isset($contact_data['email_confirm']) ? $contact_data['email_confirm'] : '',
	'CONTACT_REASONS'	=> (!empty($config_contact['contact_reasons'])) ? contact_make_select(explode("\n", $config_contact['contact_reasons']), $contact_data['contact_reason']) : '',
	'CONTACT_SUBJECT'	=> isset($contact_data['contact_subject']) ? $contact_data['contact_subject'] : '',
	'CONTACT_MESSAGE'	=> isset($contact_data['contact_message']) ? $contact_data['contact_message'] : '',

	'BBCODE_STATUS'			=> ($bbcodes_allowed) ? sprintf($user->lang['BBCODE_IS_ON'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>') : sprintf($user->lang['BBCODE_IS_OFF'], '<a href="' . append_sid("{$phpbb_root_path}faq.$phpEx", 'mode=bbcode') . '">', '</a>'),
	'SMILIES_STATUS'		=> ($smilies_allowed) ? $user->lang['SMILIES_ARE_ON'] : $user->lang['SMILIES_ARE_OFF'],
	'URL_STATUS'			=> ($urls_allowed) ? $user->lang['URL_IS_ON'] : $user->lang['URL_IS_OFF'],
	
	'L_CONTACT_YOUR_NAME_EXPLAIN'	=> $config_contact['contact_username_chk'] ? sprintf($user->lang[$config['allow_name_chars'] . '_EXPLAIN'], $config['min_name_chars'], $config['max_name_chars']) : $user->lang['CONTACT_YOUR_NAME_EXPLAIN'],
	
	'S_ATTACH_BOX'			=> ($config_contact['contact_method'] == CONTACT_METHOD_EMAIL) ? false : $attachments_allowed,
	'S_FORM_ENCTYPE'		=> $form_enctype,
	'S_CONFIRM_REFRESH'		=> ($config_contact['contact_confirm']) ? true : false,
	'S_BBCODE_ALLOWED'		=> $bbcodes_allowed,
	'S_EMAIL'				=> ($config_contact['contact_method'] == CONTACT_METHOD_EMAIL) ? true : false,
	'S_SMILIES_ALLOWED'		=> $smilies_allowed,
	'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
	'S_ERROR'				=> (isset($error) && sizeof($error)) ? implode('<br />', $error) : '',
	'S_CONTACT_USERNAME_CHK'	=> $config_contact['contact_username_chk'] ? true : false,
	'S_CONTACT_EMAIL_CHK'	=> $config_contact['contact_email_chk'] ? true : false,
	'S_CONTACT_ACTION'		=> append_sid("{$phpbb_root_path}contact.$phpEx",'', true, $user->session_id),
	// GAH...subsilver2
	'S_CLOSE_PROGRESS_WINDOW'	=> (isset($_POST['add_file'])) ? true : false,	
	'UA_PROGRESS_BAR'			=> addslashes(append_sid("{$phpbb_root_path}posting.$phpEx", "mode=popup")),	
));


// we allow bbcodes...show them
if ($bbcodes_allowed)
{
	if (!function_exists('display_custom_bbcodes'))
	{
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
	}
	// Build custom bbcodes array
	display_custom_bbcodes();
}

// we allow smilies too
if ($smilies_allowed)
{
	// Generate smiley listing
	generate_smilies('inline', 0);	
}

// Output the page
page_header($user->lang['CONTACT_BOARD_ADMIN']);

$template->set_filenames(array(
	'body' => 'contact_body.html')
);

page_footer();

?>