<?php

/**
*
* @author Rich McGirr
* @package - contact board admin
* @version $Id acp_contact.php 1.0.9 2009-12-25 RMcGirr83 $ 
* @copyright (c) RMcGirr83 http://rmcgirr83.org
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
class acp_contact
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/constants_contact.' . $phpEx);

		$this->tpl_name = 'acp_contact';
		$this->page_title = $user->lang['ACP_CAT_CONTACT'];
		$this->configuration();
		add_form_key('acp_contact');

	}
	/**
	 * make_user_select
	 * @return string User List html
	 * for drop down when selecting the contact bot
	 */
	function make_user_select($select_id = false)
	{
		global $db, $auth;
		
		// variables
		$user_list = '';
		
		// do the main sql query
		$sql = 'SELECT user_id, username
			FROM ' . USERS_TABLE . '
			ORDER BY username_clean';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{	
			$selected = ($row['user_id'] == $select_id) ? ' selected="selected"' : '';
			$user_list .= '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['username'] . '</option>';
		}
		$db->sql_freeresult($result);

		return $user_list;	
	}	
	/**
	 * Create the selection for the contact method
	 */
	function contact_method_select($value, $key = '')
	{
		$radio_ary = array(
			CONTACT_METHOD_EMAIL	=> 'CONTACT_METHOD_EMAIL',
			CONTACT_METHOD_POST		=> 'CONTACT_METHOD_POST',
			CONTACT_METHOD_PM		=> 'CONTACT_METHOD_PM',
		);
		return h_radio('contact_method', $radio_ary, $value, $key);
	}
	/**
	 * Create the selection for the post method
	 */
	function contact_poster_select($value, $key = '')
	{
		$radio_ary = array(
			CONTACT_POST_NEITHER	=> 'CONTACT_POST_NEITHER',
			CONTACT_POST_GUEST		=> 'CONTACT_POST_GUEST',
			CONTACT_POST_ALL		=> 'CONTACT_POST_ALL',
		);

		return h_radio('contact_bot_poster', $radio_ary, $value, $key);
	}
	/**
	 * Create the selection for the bot forum
	 */
	function contact_bot_forum_select($value, $key = '')
	{
		return '<select id="' . $key . '" name="contact_bot_forum">' . make_forum_select($value, false, true, true) . '</select>';
	}
	/**
	 * Create the selection for the bot
	 */
	function contact_bot_user_select($value, $key = '')
	{
		global $phpbb_root_path, $phpEx;
		return '<select id="' . $key . '" name="contact_bot_user">' . $this->make_user_select($value) . '</select>';
	}

	function configuration ()
	{
		global $cache, $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;
		
		// install file been run?
		if (!isset($config['contact_version']))
		{
			trigger_error('MCHAT_NOT_INSTALLED');
		}
		
		// something was submitted
		$submit = (isset($_POST['submit'])) ? true : false;
		
		$contact_row = array(
			'contact_confirm'			=> request_var('contact_confirm', 0),
			'contact_confirm_guests'	=> request_var('contact_confirm_guests', 0),
			'contact_max_attempts'		=> request_var('contact_max_attempts', 0),
			'contact_method'			=> request_var('contact_method', 0),
			'contact_bot_user'			=> request_var('contact_bot_user', 0),
			'contact_bot_forum'			=> request_var('contact_bot_forum', 0),
			'contact_reasons'			=> utf8_normalize_nfc(request_var('contact_reasons', '', true)),
			'contact_founder_only'		=> request_var('contact_founder_only', 0),
			'contact_bbcodes_allowed'	=> request_var('contact_bbcodes_allowed', 0),
			'contact_urls_allowed'		=> request_var('contact_urls_allowed', 0),
			'contact_smilies_allowed'	=> request_var('contact_smilies_allowed', 0),
			'contact_bot_poster'		=> request_var('contact_bot_poster', 0),
			'contact_attach_allowed'	=> request_var('contact_attach_allowed', 0),
			'contact_username_chk'		=> request_var('contact_username_chk', 0),
			'contact_email_chk'			=> request_var('contact_email_chk', 0),
		);		
				
		if ($submit)
		{
			$error = array();		
			
			if (!check_form_key('acp_contact'))
			{
				$error[] = 'FORM_INVALID';
			}

			if (!sizeof($error))
			{
				$sql = 'UPDATE ' . CONTACT_CONFIG_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $contact_row);
				$db->sql_query($sql);

				//update setting in config table for mod enabled or not
				set_config('contact_enable', request_var('contact_enable', 0));
				
				// and an entry into the log table
				add_log('admin', 'LOG_CONTACT_CONFIG_UPDATE');
				
				// purge the cache
				$cache->destroy('_contact_config');

				trigger_error($user->lang['CONTACT_CONFIG_SAVED'] . adm_back_link($this->u_action));
			}
					
			// Replace "error" strings with their real, localised form
			$error = preg_replace('#^([A-Z_]+)$#e', "(!empty(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '\\1'", $error);			
		}
		
		// let's get it on
		$sql = 'SELECT * FROM ' . CONTACT_CONFIG_TABLE;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);	
		$db->sql_freeresult($result);

		$contact_enable = isset($config['contact_enable']) ? $config['contact_enable'] : 0;
		$contact_version = isset($config['contact_version']) ? $config['contact_version'] : '';
		
		$template->assign_vars(array(
			'CONTACT_ERROR'					=> isset($error) ? ((sizeof($error)) ? implode('<br />', $error) : '') : '',
			'CONTACT_VERSION'				=> $contact_version,
			'CONTACT_ENABLE'				=> ($contact_enable) ? true : false,			
			'CONTACT_CONFIRM'				=> $row['contact_confirm'],
			'CONTACT_CONFIRM_GUESTS'		=> $row['contact_confirm_guests'],
			'CONTACT_BBCODES'				=> $row['contact_bbcodes_allowed'],
			'CONTACT_SMILIES'				=> $row['contact_smilies_allowed'],
			'CONTACT_URLS'					=> $row['contact_urls_allowed'],
			'CONTACT_ATTACHMENTS'			=> $row['contact_attach_allowed'],
			'CONTACT_MAX_ATTEMPTS'			=> $row['contact_max_attempts'],
			'CONTACT_FOUNDER'				=> $row['contact_founder_only'],
			'CONTACT_REASONS'				=> $row['contact_reasons'],
			'CONTACT_METHOD'				=> $this->contact_method_select($row['contact_method']),
			'CONTACT_BOT_POSTER'			=> $this->contact_poster_select($row['contact_bot_poster']),
			'CONTACT_BOT_FORUM'				=> $this->contact_bot_forum_select($row['contact_bot_forum']),
			'CONTACT_BOT_USER'				=> $this->contact_bot_user_select($row['contact_bot_user']),
			'CONTACT_USERNAME_CHK'			=> $row['contact_username_chk'],
			'CONTACT_EMAIL_CHK'				=> $row['contact_email_chk'],
			
			'U_ACTION'						=> $this->u_action)
		);
	}	
}

?>