<?php
/** 
*
* @package acp
* @version $Id: acp_announcements_centre.php 192 2009-03-28 20:09:59Z lefty74 $
* @copyright (c) 2007, 2008, 2009 lefty74 
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
class acp_announcements_centre
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_announcements.' . $phpEx);

		$user->add_lang(array('posting', 'mods/announcement_centre'));
		add_form_key('announcement_centre');

		
		switch ($mode)
		{
			case 'announcements':
				$title = 'ACP_ANNOUNCEMENTS_CENTRE';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_announcement_centre';
				$this->announcements();
			break;

			default:
				$title = 'ACP_ANNOUNCEMENTS_CENTRE_CONFIG';
				$this->page_title = $user->lang[$title];
				$this->tpl_name = 'acp_announcement_centre';
				$this->configuration();
			break;
		}
	}

	function announcements()
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		// Set some vars
		//$action	= request_var('action', '');
		$preview	= (isset($_POST['preview'])) ? true : false;
		$submit 	= (isset($_POST['submit'])) ? true : false;

		$announcement_row = array(
		'announcement_forum_id' 		=> request_var('announcement_forum_id', 0),
		'announcement_topic_id' 		=> request_var('announcement_topic_id', 0),
		'announcement_post_id' 			=> request_var('announcement_post_id', 0),
		'announcement_gopost' 			=> request_var('announcement_gopost', 0),
		'announcement_first_last_post' 	=> utf8_normalize_nfc(request_var('announcement_first_last_post', '')),
		'announcement_title' 			=> utf8_normalize_nfc(request_var('announcement_title', $user->lang['ANNOUNCEMENT_TITLE'], true)),
		'announcement_text' 			=> utf8_normalize_nfc( request_var('announcement_text', $user->lang['ANNOUNCEMENT_TEXT'], true)),
		'announcement_draft' 			=> utf8_normalize_nfc(request_var('announcement_draft', $user->lang['ANNOUNCEMENT_DRAFT'], true)),
		'announcement_title_guests' 	=> utf8_normalize_nfc(request_var('announcement_title_guests', $user->lang['ANNOUNCEMENT_TITLE_GUESTS'], true)),
		'announcement_text_guests' 		=> utf8_normalize_nfc(request_var('announcement_text_guests', $user->lang['ANNOUNCEMENT_TEXT_GUESTS'], true)),
		);
		
		if ($submit || $preview)
		{
			if (!check_form_key('announcement_centre'))
			{
				trigger_error('FORM_INVALID');
			}
		}

		if ($submit)
		{
			$uid_text = $bitfield_text = $options_text = ''; // will be modified by generate_text_for_storage
			$uid_text_guests = $bitfield_text_guests = $options_text_guests = ''; // will be modified by generate_text_for_storage
			$uid_draft = $bitfield_draft = $options_draft = ''; // will be modified by generate_text_for_storage
			$allow_bbcode = $allow_urls = $allow_smilies = true;

			generate_text_for_storage($announcement_row['announcement_text'], $uid_text, $bitfield_text, $options_text, $allow_bbcode, $allow_urls, $allow_smilies);
			generate_text_for_storage($announcement_row['announcement_text_guests'], $uid_text_guests, $bitfield_text_guests, $options_text_guests, $allow_bbcode, $allow_urls, $allow_smilies);
			generate_text_for_storage($announcement_row['announcement_draft'], $uid_draft, $bitfield_draft, $options_draft, $allow_bbcode, $allow_urls, $allow_smilies);

			$sql_ary = array(
			'announcement_forum_id' 					=> (int) $announcement_row['announcement_forum_id'],
			'announcement_topic_id' 					=> (int) $announcement_row['announcement_topic_id'],
			'announcement_post_id' 						=> (int) $announcement_row['announcement_post_id'],
			'announcement_gopost' 						=> (int) $announcement_row['announcement_gopost'],
			'announcement_first_last_post' 				=> (string) $announcement_row['announcement_first_last_post'],
			'announcement_title' 						=> (string) $announcement_row['announcement_title'],
			'announcement_text' 						=> (string) $announcement_row['announcement_text'],
			'announcement_text_bbcode_uid'		 		=> (string) $uid_text,
			'announcement_text_bbcode_bitfield'			=> (string) $bitfield_text,
			'announcement_text_bbcode_options' 			=> (int) 	$options_text,
			'announcement_draft' 						=> (string) $announcement_row['announcement_draft'],
			'announcement_draft_bbcode_uid' 			=> (string) $uid_draft,
			'announcement_draft_bbcode_bitfield' 		=> (string) $bitfield_draft,
			'announcement_draft_bbcode_options' 		=> (int) 	$options_draft,
			'announcement_title_guests' 				=> (string) $announcement_row['announcement_title_guests'],
			'announcement_text_guests' 					=> (string) $announcement_row['announcement_text_guests'],
			'announcement_text_guests_bbcode_uid' 		=> (string) $uid_text_guests,
			'announcement_text_guests_bbcode_bitfield' 	=> (string) $bitfield_text_guests,
			'announcement_text_guests_bbcode_options' 	=> (int) 	$options_text_guests,
			);

			$sql = 'UPDATE ' . ANNOUNCEMENTS_CENTRE_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary);
			$db->sql_query($sql);
		}
		
		
		if ($submit)
		{						
			add_log('admin', 'LOG_ANNOUNCEMENT_UPDATED');
			trigger_error($user->lang['LOG_ANNOUNCEMENT_UPDATED'] . adm_back_link($this->u_action));
		}
			
		$sql = 'SELECT * 
			FROM ' . ANNOUNCEMENTS_CENTRE_TABLE;
		$result = $db->sql_query($sql);
		$announcement = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$announcement_preview = '';
		
		if ($preview)
		{
			$announcement_preview = preview_announcement($announcement_row['announcement_draft']);
		}
		
		generate_smilies('inline', '',1);
		
		$announcement_draft = '';
		$announcement_draft = $announcement['announcement_draft'];
		$announcement_draft = generate_text_for_display($announcement_draft, $announcement['announcement_draft_bbcode_uid'], $announcement['announcement_draft_bbcode_bitfield'], $announcement['announcement_draft_bbcode_options']);
	
		decode_message($announcement['announcement_text'], $announcement['announcement_text_bbcode_uid']);
		decode_message($announcement['announcement_draft'], $announcement['announcement_draft_bbcode_uid']);
		decode_message($announcement['announcement_text_guests'], $announcement['announcement_text_guests_bbcode_uid']);
		
		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			
			'S_ANNOUNCEMENT_CONFIGURATION'		=> false,
			'ANNOUNCEMENT_TITLE'				=> $announcement['announcement_title'],
			'ANNOUNCEMENT_FORUM_ID'				=> $announcement['announcement_forum_id'],
			'ANNOUNCEMENT_TOPIC_ID'				=> $announcement['announcement_topic_id'],
			'ANNOUNCEMENT_POST_ID'				=> $announcement['announcement_post_id'],
			'ANNOUNCEMENT_GOPOST'				=> $announcement['announcement_gopost'],
			'ANNOUNCEMENT_FIRST_LAST_POST'		=> $announcement['announcement_first_last_post'],
			'ANNOUNCEMENT_TEXT'					=> $announcement['announcement_text'],
			'ANNOUNCEMENT_DRAFT'				=> ( $announcement_preview ) ? $announcement_row['announcement_draft'] : $announcement['announcement_draft'],
			'ANNOUNCEMENT_DRAFT_PREVIEW'		=> ( $announcement_preview ) ? $announcement_preview : $announcement_draft,
			'ANNOUNCEMENT_TITLE_GUESTS'			=> $announcement['announcement_title_guests'],
			'ANNOUNCEMENT_TEXT_GUESTS'			=> $announcement['announcement_text_guests'],
			'ANNOUNCEMENT_VERSION'				=> $config['acmod_version'],

			)
		);
		// Assigning custom bbcodes
		display_custom_bbcodes();
	}
	
	function configuration()
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		// Set some vars
		//$action	= request_var('action', '');
		$submit 	= (isset($_POST['submit'])) ? true : false;


		$announcement_row = array(
		'announcement_enable_guests' 	=> request_var('announcement_enable_guests', 0),
		'announcement_show' 			=> request_var('announcement_show', 0),
		'announcement_show_birthdays' 	=> request_var('announcement_show_birthdays', 0),
		'announcement_birthday_avatar' 	=> request_var('announcement_birthday_avatar', 0),
		'announcement_ava_max_size' 	=> request_var('announcement_ava_max_size', 0),
		'announcement_enable' 			=> request_var('announcement_enable', 0),
		'announcement_show_index' 		=> request_var('announcement_show_index', 0),

		'announcement_show_birthdays_always' 			=> request_var('announcement_show_birthdays_always', 0),
		'announcement_show_birthdays_and_announce' 		=> request_var('announcement_show_birthdays_and_announce', 0),
		);
		
		$announcement_show_group 	= request_var('announcement_show_group', array(0));
		$announcement_align 		= request_var('announcement_align', '');
		$announcement_guests_align 	= request_var('announcement_guests_align', '');
	
		if ($submit)
		{
			if (!check_form_key('announcement_centre'))
			{
				trigger_error('FORM_INVALID');
			}
		}

		if ($submit)
		{

			foreach ($announcement_row as $config_name => $config_value)
			{
				set_config($config_name, (int) $config_value);
			}

			set_config('announcement_show_group', (string) implode(",", $announcement_show_group));
			set_config('announcement_align', (string) $announcement_align);
			set_config('announcement_guests_align', (string) $announcement_guests_align);
		}
		
		
		if ($submit)
		{						
			add_log('admin', 'LOG_ANNOUNCEMENT_CONFIG_UPDATED');
			trigger_error($user->lang['LOG_ANNOUNCEMENT_CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}
			
		$exclude_groups = array(0);
		// dont show the guests group as we already have guests specified separately
		$sql = 'SELECT group_id 
			FROM ' . GROUPS_TABLE . "
			WHERE group_name = 'GUESTS'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$exclude_groups[] = (int) $row['group_id']; 

		$selected_groups = array();
		$selected_groups = 	explode(",", $config['announcement_show_group']);

		$align_ary = array(
			'left' => $user->lang['ANNOUNCEMENT_LEFT_ALIGNED'],
			'center' => $user->lang['ANNOUNCEMENT_CENTER_ALIGNED'],
			'right' => $user->lang['ANNOUNCEMENT_RIGHT_ALIGNED'],
		);
		
		$s_align_options = '';
		foreach ($align_ary as $value => $name)
		{
				
			$selected = ($config['announcement_align'] == $value) ? ' selected="selected"' : '';
			$s_align_options .= '<option value="' . $value . '"' . $selected . '>' . $name . '</option>';
		}

		$s_align_guests_options = '';
		foreach ($align_ary as $value => $name)
		{
				
			$selected = ($config['announcement_guests_align'] == $value) ? ' selected="selected"' : '';
			$s_align_guests_options .= '<option value="' . $value . '"' . $selected . '>' . $name . '</option>';
		}


		$template->assign_vars(array(
			'U_ACTION'		=> $this->u_action,
			
			'S_ANNOUNCEMENT_CONFIGURATION'		=> true,
			'ANNOUNCEMENT_ENABLE'				=> $config['announcement_enable'],
			'ANNOUNCEMENT_SHOW_INDEX'			=> $config['announcement_show_index'],
			'ANNOUNCEMENT_ENABLE_GUESTS'		=> $config['announcement_enable_guests'],
			'ANNOUNCEMENT_SHOW_BIRTHDAYS'		=> $config['announcement_show_birthdays'],
			'ANNOUNCEMENT_BIRTHDAY_AVATAR'		=> $config['announcement_birthday_avatar'],
			'ANNOUNCEMENT_AVA_MAX_SIZE'			=> $config['announcement_ava_max_size'],
			'ANNOUNCEMENT_SHOW'					=> $config['announcement_show'],
			'ANNOUNCEMENT_VERSION'				=> $config['acmod_version'],


			'ANNOUNCEMENT_SHOW_BIRTHDAYS_ALWAYS'		=> $config['announcement_show_birthdays_always'],
			'ANNOUNCEMENT_SHOW_BIRTHDAYS_AND_ANNOUNCE'	=> $config['announcement_show_birthdays_and_announce'],
			
			'S_ANNOUNCEMENT_SHOW_GROUPS'		=> ( $config['announcement_show'] == GROUPS_ONLY ) ? true:false,
			'S_ANNOUNCEMENT_SHOW_EVERYONE'		=> ( $config['announcement_show'] == EVERYONE ) ? true:false,
			'S_ANNOUNCEMENT_SHOW_GUESTS'		=> ( $config['announcement_show'] == GUESTS_ONLY ) ? true:false,
			'S_ANNOUNCEMENT_SELECT_GROUP'		=> group_select_options_selected($selected_groups, $exclude_groups, false),
			'S_ANNOUNCEMENT_SELECT_ALIGN'			=> $s_align_options,
			'S_ANNOUNCEMENT_GUESTS_SELECT_ALIGN'	=> $s_align_guests_options,
			)
		);
	}
}
?>