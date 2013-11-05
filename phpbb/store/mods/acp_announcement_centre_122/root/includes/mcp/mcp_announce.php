<?php
/**
*
* @package mcp
* @version $Id: mcp_announce.php 192 2009-03-28 20:09:59Z lefty74 $
* @copyright (c) 2008, 2009 lefty74
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
* mcp announce
* lets the moderator change site announcements
* @package mcp
*/
class mcp_announce
{
	var $p_master;
	var $u_action;

	function mcp_announce(&$p_master)
	{
		$this->p_master = &$p_master;
	}

	function main($id, $mode)
	{
		global $user, $template;

		$action = request_var('action', array('' => ''));

		if (is_array($action))
		{
			list($action, ) = each($action);
		}

		$this->page_title = 'MCP_ANNOUNCEMENTS_CENTRE';

		switch ($mode)
		{
			case 'front':
				$user->add_lang(array('posting', 'mods/announcement_centre', 'mods/info_acp_announcement_centre', 'acp/common'));

				$this->mcp_announcement();
				$this->tpl_name = 'mcp_announcement_centre';

			break;
		}
	}

	/**
	* mcp announcements
	*/
	function mcp_announcement()
	{
		global $config, $db, $user, $auth, $template;
		global $phpbb_root_path, $phpEx;

		include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
		include($phpbb_root_path . 'includes/functions_announcements.' . $phpEx);

		add_form_key('announcement_centre');


		// Set some vars
		//$action	= request_var('action', '');
		$submit 	= (isset($_POST['submit'])) ? true : false;

		$announcement_row = array(
		'announcement_text' 			=> utf8_normalize_nfc( request_var('announcement_text', $user->lang['ANNOUNCEMENT_TEXT'], true)),
		);
		
		if ($submit)
		{
			if (!check_form_key('announcement_centre'))
			{
				trigger_error('FORM_INVALID');
			}
		}

		if ($submit)
		{
			$uid_text = $bitfield_text = $options_text = ''; // will be modified by generate_text_for_storage
			$allow_bbcode = $allow_urls = $allow_smilies = true;

			generate_text_for_storage($announcement_row['announcement_text'], $uid_text, $bitfield_text, $options_text, $allow_bbcode, $allow_urls, $allow_smilies);

			$sql_ary = array(
			'announcement_text' 						=> (string) $announcement_row['announcement_text'],
			'announcement_text_bbcode_uid'		 		=> (string) $uid_text,
			'announcement_text_bbcode_bitfield'			=> (string) $bitfield_text,
			'announcement_text_bbcode_options' 			=> (int) 	$options_text,
			);

			$sql = 'UPDATE ' . ANNOUNCEMENTS_CENTRE_TABLE . '
					SET ' . $db->sql_build_array('UPDATE', $sql_ary);
			$db->sql_query($sql);
			
			add_log('mod', '','','LOG_ANNOUNCEMENT_UPDATED');

			$redirect = $this->u_action;
			meta_refresh(3, $redirect);
			trigger_error($user->lang['ANNOUNCEMENT_UPDATED'] . '<br /><br />' . sprintf($user->lang['RETURN_PAGE'], '<a href="' . $redirect . '">', '</a>'));

		}
					
		$sql = 'SELECT * 
			FROM ' . ANNOUNCEMENTS_CENTRE_TABLE;
		$result = $db->sql_query($sql);
		$announcements = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
			
		generate_smilies('inline', '',1);
		
		decode_message($announcements['announcement_text'], $announcements['announcement_text_bbcode_uid']);
		
		$template->assign_vars(array(
			'U_ACTION'			=> $this->u_action,
			'S_ALLOW_EDITS'		=>	($announcements['announcement_forum_id'] || $announcements['announcement_topic_id'] || $announcements['announcement_post_id']) ? false : true,
			'MCP_ANNOUNCEMENT_TEXT'	=> $announcements['announcement_text'],
			)
		);
		// Assigning custom bbcodes
		display_custom_bbcodes();
	}

}

?>