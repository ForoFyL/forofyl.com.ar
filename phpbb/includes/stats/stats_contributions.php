<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_contributions.php 163 2010-12-11 14:03:40Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Forum Statistics by TheUniqueTiger - Nayan Ghosh
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* fs_contributions
* Displays forum contributions classified into attachments, polls
*
* @package fs
*/
class stats_contributions
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $cache, $stats_config;		

		// $user->add_lang('mods/stats'); // not needed
		
		if(sizeof($stats_config) < 1)
		{
			$stats_config = obtain_stats_config();
		}
		
		$limit_count = request_var('limit_count', 10); //replace 10 by the config option
		//create an array containing the limit_count options as $option=>$option_lang
		$limit_options = array(
			'1'		=> 1,
			'3'		=> 3,
			'5'		=> 5,
			'10'	=> 10,
			'15'	=> 15,
		);
		$limit_prompt = '';
		
		if ($mode == 'attachments' || $mode == 'polls')
		{
			//label unreadable forums/topics as hidden		
			//get in an array all the readable forums
			$no_forum_ary = array(); //contains readable forums as array([forum_id1] => 1, [forum_id2] => 1)

			// include those forums the user is having read access to...
			$no_forum_read_ary = $auth->acl_getf('!f_read', true);

			foreach ($no_forum_read_ary as $forum_id => $is_not_allowed)
			{
				if ($is_not_allowed['f_read'])
				{
					$no_forum_ary[] = (int) $forum_id;
				}
			}
			unset($forum_read_ary); //free some memory
		}
		
		switch ($mode)
		{
			case 'attachments':
			if($stats_config['contributions_attachments_enable'])
			{
				//get and display statistics about attachments
				$total_orphan_attachments = get_orphan_attachments_count();
				$total_orphan_attachments_size = get_orphan_attachments_size();
				$total_downloads_size = get_total_attach_downloads('total_size');
				
				//get recent attachments				
				$recent_attachments = get_top_attachments($limit_count, 'recent', 'DESC', $no_forum_ary);
				if ($recent_attachments)
				{
					$template->assign_var('S_RECENT_ATTACHMENTS', true);
					foreach ($recent_attachments as $current_attach)
					{
						$template->assign_block_vars('recent_attach_row', array(
							'L_FILE'				=> $current_attach['filename'],
							'L_POST'				=> $current_attach['p_subject'],
							'L_FORUM'				=> $current_attach['f_name'],
							'U_FILE'					=> append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $current_attach['attach_id']),
							'FILESIZE'				=> get_formatted_filesize($current_attach['filesize']),
							'U_POST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_attach['f_id'] . '&amp;t=' . $current_attach['t_id'] . '&amp;p=' . $current_attach['p_id'] . '#p' . $current_attach['p_id']),
							'U_FORUM'				=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_attach['f_id']),
							'FILETIME'				=> $user->format_date($current_attach['filetime']),
						));
					}
				}
				
				//get top attachments by download
				if ($config['num_files'])
				{
					$top_attachments_by_download = get_top_attachments($limit_count, 'download', 'DESC', $no_forum_ary);
				}
				else
				{
					$top_attachments_by_download = array();
				}
				$total_downloads = get_total_attach_downloads();
	
				if ($top_attachments_by_download && $total_downloads > 0)
				{
					$max_count = $top_attachments_by_download[0]['count'];
					$template->assign_var('S_TOP_BY_DOWNLOAD', true);
					foreach ($top_attachments_by_download as $current_attach)
					{
						$template->assign_block_vars('top_by_download_row', array(
							'L_FILE'				=> $current_attach['filename'],
							'L_POST'				=> $current_attach['p_subject'],
							'L_FORUM'				=> $current_attach['f_name'],
							'U_FILE'					=> append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $current_attach['attach_id']),			
							'U_POST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_attach['f_id'] . '&amp;t=' . $current_attach['t_id'] . '&amp;p=' . $current_attach['p_id'] . '#p' . $current_attach['p_id']),
							'U_FORUM'				=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_attach['f_id']),
							'FILETIME'				=> $user->format_date($current_attach['filetime']),
							'COUNT'				=> $current_attach['count'],
							'PCT'						=> number_format($current_attach['count'] / $total_downloads * 100, 2),
							'BARWIDTH'			=> number_format($current_attach['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top attachments count by filetype
				if ($config['num_files'])
				{
					$top_attachments_by_filetype = get_top_attachments($limit_count, 'filetype', 'DESC');
				}
				else
				{
					$top_attachments_by_filetype = array();
				}
				
				if ($top_attachments_by_filetype)
				{
					$max_count = $top_attachments_by_filetype[0]['count'];
					$template->assign_var('S_TOP_BY_FILETYPE', true);
					foreach ($top_attachments_by_filetype as $current_type)
					{
						$template->assign_block_vars('top_by_filetype_row', array(
							'COUNT'				=> $current_type['count'],
							'FILE_EXTENSION'	=> $current_type['extension'],
							'FILE_MIMETYPE'		=> $current_type['mimetype'],
							'PCT'				=> number_format($current_type['count'] / $config['num_files'] * 100, 2),
							'BARWIDTH'			=> number_format($current_type['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top attachments b filesize
				if ($config['num_files'])
				{
					$top_attachments_by_filesize = get_top_attachments($limit_count, 'filesize', 'DESC', $no_forum_ary);
				}
				else
				{
					$top_attachments_by_filesize = array();
				}				
				
				if ($top_attachments_by_filesize)
				{
					$max_count = $top_attachments_by_filesize[0]['filesize'];
					$template->assign_var('S_TOP_BY_FILESIZE', true);					
					foreach ($top_attachments_by_filesize as $current_attach)
					{
						$template->assign_block_vars('top_by_filesize_row', array(
							'L_FILE'				=> $current_attach['filename'],
							'L_POST'				=> $current_attach['p_subject'],
							'L_FORUM'				=> $current_attach['f_name'],
							'U_FILE'					=> append_sid("{$phpbb_root_path}download/file.$phpEx", 'id=' . $current_attach['attach_id']),			
							'U_POST'				=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_attach['f_id'] . '&amp;t=' . $current_attach['t_id'] . '&amp;p=' . $current_attach['p_id'] . '#p' . $current_attach['p_id']),
							'U_FORUM'				=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_attach['f_id']),
							'FILETIME'				=> $user->format_date($current_attach['filetime']),
							'COUNT'				=> get_formatted_filesize($current_attach['filesize']),
							'PCT'						=> number_format($current_attach['filesize'] / $config['upload_dir_size'] * 100, 2),
							'BARWIDTH'			=> number_format($current_attach['filesize'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top users by attachments
				if ($config['num_files'])
				{
					$top_users_by_attachments = get_top_users($limit_count, 'attachments', 'DESC');
				}
				else
				{
					$top_users_by_attachments = array();
				}
				
				if ($top_users_by_attachments)
				{
					$max_count = $top_users_by_attachments[0]['count'];
					$template->assign_var('S_TOP_BY_ATTACHMENTS', true);					
					foreach ($top_users_by_attachments as $current_user)
					{
						$template->assign_block_vars('top_by_attachments_row', array(
							'U_USER'				=> get_username_string('full', $current_user['u_id'], $current_user['username'], $current_user['u_colour']),							
							'COUNT'				=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $config['num_files'] * 100, 2),
							'BARWIDTH'			=> number_format($current_user['count'] / $max_count * 100, 1),
						));
					}
				}
				
				
				$template->assign_vars(array(
					'ATTACHMENTS_TOTAL'					=> $config['num_files'],
					'ATTACHMENTS_SIZE'					=> get_formatted_filesize($config['upload_dir_size']),
					'ATTACHMENTS_ORPHAN'				=> $total_orphan_attachments,
					'ATTACHMENTS_ORPHAN_SIZE'			=> get_formatted_filesize($total_orphan_attachments_size),
					'TOTAL_DOWNLOADS'					=> ($total_downloads > 0) ? $total_downloads : 0,
					'TOTAL_DOWNLOADS_SIZE'				=> ($total_downloads_size > 0) ? get_formatted_filesize($total_downloads_size) : 0 . ' ' . $user->lang['BYTES'],
					'RECENT_ATTACHMENTS'				=> sprintf($user->lang['RECENT_ATTACHMENTS'], $limit_count),
					'TOP_ATTACHMENTS_BY_FILETYPE'		=> sprintf($user->lang['TOP_ATTACHMENTS_BY_FILETYPE'], $limit_count),
					'TOP_ATTACHMENTS_BY_FILESIZE'		=> sprintf($user->lang['TOP_ATTACHMENTS_BY_FILESIZE'], $limit_count),
					'TOP_ATTACHMENTS_BY_DOWNLOAD'		=> sprintf($user->lang['TOP_ATTACHMENTS_BY_DOWNLOAD'], $limit_count),
					'TOP_USERS_BY_ATTACHMENTS'			=> sprintf($user->lang['TOP_USERS_BY_ATTACHMENTS'], $limit_count),
				));
				
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['ATTACHMENTS_OR_USERS']);
			}
			else
			{
				$template->assign_var('S_CONTRIBUTIONS_ATTACHMENTS_DISABLED', true);
			}
			break;

			case 'polls':
			if($stats_config['contributions_polls_enable'])
			{
				//get and display polls statistics
				$total_polls = get_polls_count();
				$total_open_polls = get_polls_count('open');
				$total_poll_votes = get_polls_count('votes');

				if ($user->data['is_registered'])
				{					
					$total_polls_voted = get_polls_count('voted');
					$template->assign_vars(array(
						'S_TOTAL_POLLS_VOTED'		=> true,
						'TOTAL_POLLS_VOTED'			=> ($total_polls) ? $total_polls_voted . ' / ' . $total_polls .  ' (' . number_format($total_polls_voted / $total_polls * 100, 2) . '%)' : ' 0 / 0',
					));
				}
				
				//get accessible polls count
				$count = 0;
					
				$sql = 'SELECT DISTINCT po.topic_id AS t_id, t.forum_id AS f_id FROM ' . POLL_OPTIONS_TABLE . ' po, ' . TOPICS_TABLE . ' t
						WHERE po.topic_id = t.topic_id';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					if ($auth->acl_get('f_poll', $row['f_id']))
					{
						++$count;
					}
				}
				$db->sql_freeresult($result);
				$total_accessible_polls = $count;

				
				//get recent polls
				if ($total_polls)
				{
					$recent_polls = get_top_polls($limit_count, 'recent', 'DESC', $no_forum_ary);
					if ($recent_polls)
					{
						$template->assign_var('S_RECENT_POLLS', true);
					}
					//first get all poll options for the recent topics, for which first get all the topic_ids
					$recent_polls_topic_ids = array();
					foreach ($recent_polls as $current_poll)
					{
						$recent_polls_topic_ids[] = $current_poll['t_id'];
					}
					//now get all the poll option texts
					$poll_option_texts = get_poll_options($recent_polls_topic_ids);
				}
				else
				{
					$recent_polls = array();
				}				
				
				foreach ($recent_polls as $current_poll)
				{
					//get a string of all the poll options
					$current_poll_options = '';					
					foreach ($poll_option_texts[$current_poll['t_id']] as $poll_option_text)
					{
						$current_poll_options .= "<i>$poll_option_text</i> ";					
					}
					
					$template->assign_block_vars('recent_polls_row', array(
						'POLL_TITLE'					=> $current_poll['poll_title'],
						'L_TOPIC'						=> $current_poll['t_title'],
						'L_FORUM'						=> $current_poll['f_name'],
						'U_TOPIC'						=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $current_poll['t_id'] . '&amp;f=' . $current_poll['f_id']),
						'U_FORUM'						=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_poll['f_id']),
						'POLL_START'					=> $user->format_date($current_poll['poll_start']),
						'POLL_OPTIONS'			=> $current_poll_options,
					));
				}
				
				//get top polls by votes
				if ($total_polls)
				{
					$top_polls_by_votes = get_top_polls($limit_count, 'votes', 'DESC', $no_forum_ary);
					if ($top_polls_by_votes)
					{
						$template->assign_var('S_TOP_BY_VOTES', true);
					}
					if (isset($top_polls_by_votes[0]['count']))
					{
						$max_count = $top_polls_by_votes[0]['count'];
					}
					//first get all poll options for the recent topics, for which first get all the topic_ids
					$top_polls_by_votes_topic_ids = array();
					foreach ($top_polls_by_votes as $current_poll)
					{
						$top_polls_by_votes_topic_ids[] = $current_poll['t_id'];
					}
					//now get all the poll option texts
					$poll_option_texts = get_poll_options($top_polls_by_votes_topic_ids);
				}
				else
				{
					$top_polls_by_votes = array();
				}
				foreach ($top_polls_by_votes as $current_poll)
				{
					//get a string of all the poll options
					$current_poll_options = '';					
					foreach ($poll_option_texts[$current_poll['t_id']] as $poll_option_text)
					{
						$current_poll_options .= "<i>$poll_option_text</i> ";					
					}
					
					$template->assign_block_vars('top_by_votes_row', array(
						'POLL_TITLE'					=> $current_poll['poll_title'],
						'L_TOPIC'						=> $current_poll['t_title'],
						'L_FORUM'						=> $current_poll['f_name'],
						'U_TOPIC'						=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 't=' . $current_poll['t_id'] . '&amp;f=' . $current_poll['f_id']),
						'U_FORUM'						=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_poll['f_id']),
						'COUNT'							=> $current_poll['count'],
						'PCT'						=> number_format($current_poll['count'] / $total_poll_votes * 100, 2),
						'BARWIDTH'			=> number_format($current_poll['count'] / $max_count * 100, 1),
						'POLL_OPTIONS'					=> $current_poll_options,
					));
				}
				
				$template->assign_vars(array(
					'TOTAL_POLLS'			=> $total_polls,
					'TOTAL_OPEN_POLLS'		=> $total_open_polls . ' / ' . $total_polls,
					'TOTAL_POLL_VOTES'		=> $total_poll_votes,
					'TOTAL_ACCESSIBLE_POLLS'=> ($total_polls) ? $total_accessible_polls . ' / ' . $total_polls .  ' (' . number_format($total_accessible_polls / $total_polls * 100, 2) . '%)' : ' 0 / 0',
					'RECENT_POLLS'			=> sprintf($user->lang['RECENT_POLLS'], $limit_count),
					'TOP_POLLS_BY_VOTES'		=> sprintf($user->lang['TOP_POLLS_BY_VOTES'], $limit_count),
				));
				
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['POLLS']);
			}
			else
			{
				$template->assign_var('S_CONTRIBUTIONS_POLLS_DISABLED', true);
			}
			break;
			
			default:
		}
		
		$template->assign_var('LIMIT_SELECT_BOX', make_select_box($limit_options, $limit_count, 'limit_count', $limit_prompt, $user->lang['GO'], $this->u_action));
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['STATS_CONTRIBUTIONS_' . strtoupper($mode)],			
			'S_FS_ACTION'		=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = 'stats/stats_contributions_' . $mode;
		$this->lang_name = 'stats_contributions_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->lang_name)];
	}
}
?>