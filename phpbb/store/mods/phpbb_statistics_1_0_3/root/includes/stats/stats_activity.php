<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_activity.php 163 2010-12-11 14:03:40Z marc1706 $
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
* stats_activity
* Displays forum activity classified into forums, topics, users
*/
class stats_activity
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $stats_config;		

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
		
		if ($mode == 'forums' || $mode == 'topics')
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
			case 'forums':
			if($stats_config['activity_forums_enable'])
			{
				//get and display forum statistics
				$forum_count = get_forum_count();
				$total_forum_cat = $forum_count[FORUM_CAT];
				$total_forum_post = $forum_count[FORUM_POST];
				$total_forum_link = $forum_count[FORUM_LINK];
				$total_forums = $total_forum_cat + $total_forum_post + $total_forum_link;
								
				//get top forums by topics
				$top_forums_by_topics = get_top_forums($limit_count, 'topics', 'DESC', $no_forum_ary);
				
				if ($config['num_topics'])
				{
					$max_count = $top_forums_by_topics[0]['count'];
					foreach ($top_forums_by_topics as $current_forum)
					{					
						$template->assign_block_vars('top_by_topics_row', array(	
						'FORUM_NAME'				=> $current_forum['f_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $config['num_topics'] * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}

				//get top forums by posts
				$top_forums_by_posts = get_top_forums($limit_count, 'posts', 'DESC', $no_forum_ary);
				
				if ($config['num_posts'])
				{
					$max_count = $top_forums_by_posts[0]['count'];
					foreach ($top_forums_by_posts as $current_forum)
					{
						$template->assign_block_vars('top_by_posts_row', array(
						'FORUM_NAME'				=> $current_forum['f_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $config['num_posts'] * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}

				//get top forums by polls
				$top_forums_by_polls = get_top_forums($limit_count, 'polls', 'DESC', $no_forum_ary);
				$total_polls = get_polls_count();
				if ($total_polls > 0 && isset($top_forums_by_polls[0]['count']))
				{
					$max_count = $top_forums_by_polls[0]['count'];
					foreach ($top_forums_by_polls as $current_forum)
					{
						$template->assign_block_vars('top_by_polls_row', array(
						'FORUM_NAME'				=> $current_forum['f_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $total_polls * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top forums by sticky topics
				$top_forums_by_sticky = get_top_forums($limit_count, 'sticky', 'DESC', $no_forum_ary);
				if ($top_forums_by_sticky) //get total sticky count only if there are any forums with sticky topics returned, hence save 1 query otherwise
				{
					$total_sticky = get_topic_type_count(POST_STICKY);
					
					$max_count = $top_forums_by_sticky[0]['count'];
					
					foreach ($top_forums_by_sticky as $current_forum)
					{
						$template->assign_block_vars('top_by_sticky_row', array(
								'FORUM_NAME'				=> $current_forum['f_name'],
								'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
								'COUNT'				=> $current_forum['count'],
								'PCT'						=> number_format($current_forum['count'] / $total_sticky * 100, 2),
								'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
								));
					}
				}
				
				//get top forums by user participation
				$top_forums_by_participation = get_top_forums($limit_count, 'participation', 'DESC', $no_forum_ary);
				//$total_participation = get_posters_count(); //for only posters
				$total_participation = $config['num_users']; //for all users
				
				if(isset($top_forums_by_participation[0]['count']))
				{
					$max_count = $top_forums_by_participation[0]['count'];
				}
				
				if ($total_participation)
				{
					foreach ($top_forums_by_participation as $current_forum)
					{
						if ($current_forum['count'] > $total_participation) //check for > count considering inactive users
						{
							$current_forum['count'] = $total_participation;
						}
						$template->assign_block_vars('top_by_participation_row', array(
						'FORUM_NAME'				=> $current_forum['f_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $total_participation * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top forums by views
				$total_views = 0;
				$max_count = 0;
				$top_forums_by_views = get_page_views_count($limit_count, 'forums', 'DESC', $no_forum_ary);
				$total_views = get_page_views_count();
				if ($total_views > 0 && isset($top_forums_by_views[0]['count']))
				{
					$max_count = $top_forums_by_views[0]['count'];
				}
				if ($total_views && (isset($max_count) && $max_count > 0))
				{
					$template->assign_var('S_TOP_FORUMS_BY_VIEWS',  true);
					foreach ($top_forums_by_views as $current_forum)
					{
						$template->assign_block_vars('top_by_views_row', array(
						'FORUM_NAME'				=> $current_forum['forum_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['forum_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $total_views * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top forums by subscriptions
				$top_forums_by_subscriptions = get_top_forums($limit_count, 'subscriptions', 'DESC', $no_forum_ary);
				$total_subscriptions = get_subscriptions_count();
			
				if (isset($top_forums_by_subscriptions) && $total_subscriptions > 0)
				{
					$max_count = $top_forums_by_subscriptions[0]['count'];
					
					$template->assign_var('S_TOP_FORUMS_BY_SUBSCRIPTIONS',  true);
					foreach ($top_forums_by_subscriptions as $current_forum)
					{
						$template->assign_block_vars('top_by_subscriptions_row', array(
						'FORUM_NAME'				=> $current_forum['f_name'],
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_forum['f_id']),
						'COUNT'				=> $current_forum['count'],
						'PCT'						=> number_format($current_forum['count'] / $total_subscriptions * 100, 2),
						'BARWIDTH'					=> number_format($current_forum['count'] / $max_count * 100, 1),
						));
					}
				}
				
				$template->assign_vars(array(
					'TOTAL_FORUMS'			=> $total_forums,
					'TOTAL_FORUM_CAT'			=> $total_forum_cat,
					'TOTAL_FORUM_POST'		=> $total_forum_post,
					'TOTAL_FORUM_LINK'		=> $total_forum_link,
					'TOP_FORUMS_BY_TOPICS'	=> sprintf($user->lang['TOP_FORUMS_BY_TOPICS'], $limit_count),	
					'TOP_FORUMS_BY_POSTS'	=> sprintf($user->lang['TOP_FORUMS_BY_POSTS'], $limit_count),	
					'TOP_FORUMS_BY_POLLS'	=> sprintf($user->lang['TOP_FORUMS_BY_POLLS'], $limit_count),
					'TOP_FORUMS_BY_STICKY'	=> sprintf($user->lang['TOP_FORUMS_BY_STICKY'], $limit_count),
					'TOP_FORUMS_BY_PARTICIPATION'	=> sprintf($user->lang['TOP_FORUMS_BY_PARTICIPATION'], $limit_count),
					'TOP_FORUMS_BY_VIEWS'	=> sprintf($user->lang['TOP_FORUMS_BY_VIEWS'], $limit_count),
					'TOP_FORUMS_BY_SUBSCRIPTIONS'	=> sprintf($user->lang['TOP_FORUMS_BY_SUBSCRIPTIONS'], $limit_count),
					'S_TOP_FORUMS_BY_TOPICS'	=> ($top_forums_by_topics) ? true : false,
					'S_TOP_FORUMS_BY_POSTS'	=> ($top_forums_by_posts) ? true : false,
					'S_TOP_FORUMS_BY_POLLS'	=> ($top_forums_by_polls) ? true : false,
					'S_TOP_FORUMS_BY_STICKY'	=> ($top_forums_by_sticky) ? true : false,
					'S_TOP_FORUMS_BY_PARTICIPATION'	=> ($top_forums_by_participation) ? true : false,	
					'S_TOP_FORUMS_BY_VIEWS'	=> ($total_views != 0) ? true : false,
				));
				
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['FORUMS']);
			}
			else
			{
				$template->assign_var('S_ACTIVITY_FORUMS_DISABLED', true);
			}
			break;
			
			case 'topics':
			if($stats_config['activity_topics_enable'])
			{
				//get and display topics statistics
				$topic_types_count = get_topic_types_count();
				
				//top topics by posts
				$top_topics_by_posts = get_top_topics($limit_count, 'posts', 'DESC', $no_forum_ary);
				$total_posts = $config['num_posts'];		
				
				if ($total_posts)
				{
					//get the max count (for comparing bar width) from the retrieved topics, its the first one as we have already sorted DESC
					$max_count = $top_topics_by_posts[0]['count'];		
					
					foreach ($top_topics_by_posts as $current_topic)
					{
						$template->assign_block_vars('top_by_posts_row', array(
						'L_TOPIC'					=> $current_topic['t_title'],
						'L_FORUM'					=> $current_topic['f_name'],
						'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['t_id']),						
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
						'COUNT'				=> $current_topic['count'],
						'PCT'						=> number_format($current_topic['count'] / $total_posts * 100, 2),
						'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));					
					}
				}
				
				//top topics by views
				$top_topics_by_views = get_top_topics($limit_count, 'views', 'DESC', $no_forum_ary);
				$total_views = get_topic_views_count();
				
				//edit to prevent div by zero
				if ($total_views)
				{
					$max_count = $top_topics_by_views[0]['count'];
					foreach ($top_topics_by_views as $current_topic)
					{
						$template->assign_block_vars('top_by_views_row', array(
							'L_TOPIC'					=> $current_topic['t_title'],
							'L_FORUM'					=> $current_topic['f_name'],
							'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['t_id']),						
							'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
							'COUNT'				=> $current_topic['count'],
							'PCT'						=> number_format($current_topic['count'] / $total_views * 100, 2),
							'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));
					}
				}

				//get top topics by user participation
				$top_topics_by_participation = get_top_topics($limit_count, 'participation', 'DESC', $no_forum_ary);
				//$total_participation = get_posters_count(); //for only posters
				$total_participation = $config['num_users']; //for all users
				
				if(isset($top_topics_by_participation[0]['count']))
				{
					$max_count = $top_topics_by_participation[0]['count'];
				}
				
				if ($total_participation)
				{
					foreach ($top_topics_by_participation as $current_topic)
					{
						if ($current_topic['count'] > $total_participation) //check for > count considering inactive users
						{
							$current_topic['count'] = $total_participation;
						}
						$template->assign_block_vars('top_by_participation_row', array(
						'L_TOPIC'					=> $current_topic['t_title'],
						'L_FORUM'					=> $current_topic['f_name'],
						'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['t_id']),						
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
						'COUNT'				=> $current_topic['count'],
						'PCT'						=> number_format($current_topic['count'] / $total_participation * 100, 2),
						'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top topics by user attachments
				$top_topics_by_attachments = get_top_topics($limit_count, 'attachments', 'DESC', $no_forum_ary);
				//$total_attachments = get_posters_count(); //for only posters
				$total_attachments = $config['num_files']; //for all users
				if ($total_attachments != 0)
				{
					$max_count = $top_topics_by_attachments[0]['count'];
				}
				
				if ($total_attachments)
				{
					foreach ($top_topics_by_attachments as $current_topic)
					{					
						$template->assign_block_vars('top_by_attachments_row', array(
						'L_TOPIC'					=> $current_topic['t_title'],
						'L_FORUM'					=> $current_topic['f_name'],
						'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['t_id']),						
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
						'COUNT'				=> $current_topic['count'],
						'PCT'						=> number_format($current_topic['count'] / $total_attachments * 100, 2),
						'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top topics by bookmarks
				$top_topics_by_bookmarks = get_top_topics($limit_count, 'bookmarks', 'DESC', $no_forum_ary);
				$total_bookmarks = get_bookmarks_count();
				
				if ($total_bookmarks != 0 && isset($top_topics_by_bookmarks))
				{
					$max_count = $top_topics_by_bookmarks[0]['count'];
					
					foreach ($top_topics_by_bookmarks as $current_topic)
					{					
						$template->assign_block_vars('top_by_bookmarks_row', array(
						'L_TOPIC'					=> $current_topic['t_title'],
						'L_FORUM'					=> $current_topic['f_name'],
						'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['topic_id']),						
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
						'COUNT'				=> $current_topic['count'],
						'PCT'						=> number_format($current_topic['count'] / $total_bookmarks * 100, 2),
						'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top topics by subscriptions
				$top_topics_by_subscriptions = get_top_topics($limit_count, 'subscriptions', 'DESC', $no_forum_ary);
				$total_subscriptions = get_subscriptions_count();
				
				if ($total_subscriptions != 0 && isset($top_topics_by_subscriptions))
				{
					$max_count = $top_topics_by_subscriptions[0]['count'];
					
					foreach ($top_topics_by_subscriptions as $current_topic)
					{					
						$template->assign_block_vars('top_by_subscriptions_row', array(
						'L_TOPIC'					=> $current_topic['t_title'],
						'L_FORUM'					=> $current_topic['f_name'],
						'U_TOPIC'					=> append_sid("{$phpbb_root_path}viewtopic.$phpEx", 'f=' . $current_topic['f_id'] . '&amp;t=' . $current_topic['topic_id']),						
						'U_FORUM'					=> append_sid("{$phpbb_root_path}viewforum.$phpEx", 'f=' . $current_topic['f_id']),
						'COUNT'				=> $current_topic['count'],
						'PCT'						=> number_format($current_topic['count'] / $total_subscriptions * 100, 2),
						'BARWIDTH'					=> number_format($current_topic['count'] / $max_count * 100, 1),
						));
					}
				}
				
				
				
				$template->assign_vars(array(
					'TOPIC_TYPES_GLOBAL'	=> $topic_types_count['global'],
					'TOPIC_TYPES_ANNOUNCE'  => $topic_types_count['announce'],
					'TOPIC_TYPES_STICKY'		=> $topic_types_count['sticky'],
					'TOPIC_TYPES_NORMAL'		=> $topic_types_count['normal'],
					'TOPIC_TYPES_UNAPPROVED'	=> $topic_types_count['unapproved'],
					'TOTAL_TOPICS'				=> $config['num_topics'] + $topic_types_count['unapproved'],
					'TOP_TOPICS_BY_POSTS'		=> sprintf($user->lang['TOP_TOPICS_BY_POSTS'], $limit_count),
					'TOP_TOPICS_BY_VIEWS'		=> sprintf($user->lang['TOP_TOPICS_BY_VIEWS'], $limit_count),
					'TOP_TOPICS_BY_PARTICIPATION'		=> sprintf($user->lang['TOP_TOPICS_BY_PARTICIPATION'], $limit_count),
					'TOP_TOPICS_BY_ATTACHMENTS'		=> sprintf($user->lang['TOP_TOPICS_BY_ATTACHMENTS'], $limit_count),
					'TOP_TOPICS_BY_VIEWS'		=> sprintf($user->lang['TOP_TOPICS_BY_VIEWS'], $limit_count),
					'TOP_TOPICS_BY_BOOKMARKS'	=> sprintf($user->lang['TOP_TOPICS_BY_BOOKMARKS'], $limit_count),
					'TOP_TOPICS_BY_SUBSCRIPTIONS'	=> sprintf($user->lang['TOP_TOPICS_BY_SUBSCRIPTIONS'], $limit_count),
					'S_TOP_TOPICS_BY_POSTS'		=> ($top_topics_by_posts) ? true : false,
					'S_TOP_TOPICS_BY_VIEWS'		=> ($total_views) ? true : false,
					'S_TOP_TOPICS_BY_PARTICIPATION'		=> ($top_topics_by_participation) ? true : false,
					'S_TOP_TOPICS_BY_ATTACHMENTS'		=> ($top_topics_by_attachments) ? true : false,
					'S_TOP_TOPICS_BY_BOOKMARKS'		=> ($top_topics_by_bookmarks) ? true : false,
					'S_TOP_TOPICS_BY_SUBSCRIPTIONS'		=> ($top_topics_by_subscriptions) ? true : false,
				));
				
				
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], strtolower($user->lang['TOPICS']));
			}
			else
			{
				$template->assign_var('S_ACTIVITY_TOPICS_DISABLED', true);
			}
			break;
			
			case 'users':
			if($stats_config['activity_users_enable'])
			{
				//get and display user statistics
				
				$total_members = get_total_members();
				$groups_data = get_groups_data();				
				$member_counts = get_group_members($groups_data);
				$total_users = $config['num_users'];
				$deleted_users = get_deleted_users_count();
				
				$total_bots_visited = 0;
				$total_users_online = 0;
				$total_members_online = 0;
				$total_bots_online = 0;
				$total_active_users = 0;
				$total_inactive_users = 0;
				$most_online = 0;
				$most_online_time = 0;
				$ranks_data = array();
				
				//output the groups count
				if ($groups_data)
				{					
					$template->assign_var('S_GROUPS', true);
					foreach ($groups_data as $current_group)
					{
						if ($current_group['group_name'] == 'GUESTS')
						{
							continue;
						}						
						$colour_text = ($current_group['group_colour']) ? ' style="color:#' . $current_group['group_colour'] . '"' : '';
						$template->assign_block_vars('grouprow', array(
							'U_GROUP'		=> ($current_group['group_name'] == 'BOTS') ? '<span' . $colour_text . '>' . $user->lang['G_BOTS'] . '</span>' : '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $current_group['group_id']) . '">' . (($current_group['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $current_group['group_name']] : $current_group['group_name']) . '</a>',
							'COUNT'			=> (int) $member_counts[$current_group['group_id']],						
						));
					}
				}
				
				//get the currently online users grouped by their user group				
				$total_online = $total_hidden = $total_guests = $total_members_online = 0;				
				$online_data = get_online_data($groups_data, $total_online, $total_hidden, $total_guests);
				
				if ($online_data)
				{					
					$template->assign_var('S_ONLINE', true);
					foreach ($groups_data as $current_group)
					{
						$colour_text = ($current_group['group_colour']) ? ' style="color:#' . $current_group['group_colour'] . '"' : '';
						if ($current_group['group_name'])
						{
							$users_list = '';
							/*if (!isset($online_data[$current_group['group_id']]))
							{
								$online_data[$current_group['group_id']] = array();
							}*/
							//now display the users...
							//if (isset($online_data[$current_group['group_id']]))
							//{
								if ($current_group['group_name'] != 'GUESTS')
								{
									foreach ($online_data[$current_group['group_id']] as $current_user)
									{
										$users_list .= (($users_list != '') ? ', ' : '') . get_username_string('full', $current_user['user_id'], $current_user['username'], $current_user['user_colour']);
									}
									$total_members_online += count($online_data[$current_group['group_id']]);
								}
								$template->assign_block_vars('online_grouprow', array(
									'U_GROUP'		=> ($current_group['group_name'] == 'BOTS') ? '<span' . $colour_text . '>' . $user->lang['G_BOTS'] . '</span>' : '<a' . $colour_text . ' href="' . append_sid("{$phpbb_root_path}memberlist.$phpEx", 'mode=group&amp;g=' . $current_group['group_id']) . '">' . (($current_group['group_type'] == GROUP_SPECIAL) ? $user->lang['G_' . $current_group['group_name']] : $current_group['group_name']) . '</a>',
									'COUNT'			=> count($online_data[$current_group['group_id']]),
									'USERS_LIST'			=> $users_list,
								));
							//}
						}
					}
				}
				
				//get and display ranks
				//we are not using get_user_rank since we know its only the non-special post count based ones.
				//$ranks_data = get_ranks('non-special');
				global $ranks;
				if (empty($ranks))
				{
					global $cache;
					$ranks = $cache->obtain_ranks();
				}
				if (isset($ranks['normal']))
				{
					$ranks_data = $ranks['normal'];
				}
				if (sizeof($ranks_data))
				{
					$ranks_counts = array();
					foreach ($ranks_data as $rank)
					{
						$ranks_counts[] = 0;
					}					
					
					$user_ranks_count = get_users_ranks_count(); //this returns all the post counts of the users
					//now we have to increment the appropriate counters depending on the post count
					foreach ($user_ranks_count as $row)
					{
						$temp_counter = 0;
						foreach ($ranks_data as $rank)
						{
							if ($row['user_posts'] >= $rank['rank_min'])
							{
								++$ranks_counts[$temp_counter];
								break;
							}
							++$temp_counter;
						}
					}
					//get my non-special rank
					if ($user->data['is_registered'])
					{
						$temp_counter = $my_rank = 0;
						foreach ($ranks_data as $rank)
						{
							if ($user->data['user_posts'] >= $rank['rank_min'])
							{
								$my_rank = $temp_counter;
								continue;
							}
							++$temp_counter;
						}
					}
					
					//now display the ranks and member counts
					if (array_sum($ranks_counts))
					{
						$template->assign_var('S_RANKS', true);
						//reverse $ranks_data so that it shows newbies first
						$ranks_data = array_reverse($ranks_data, true);
						$temp_counter = sizeof($ranks_data) - 1;
						foreach ($ranks_data as $row)
						{
							$template->assign_block_vars('ranks_row', array(
							'RANK_TITLE'			=> $row['rank_title'],
							'RANK_MIN_POSTS'	=> $row['rank_min'],
							'COUNT'					=> $ranks_counts[$temp_counter],
							'IS_MINE'				=> ($user->data['is_registered'] && $my_rank == $temp_counter),
							));
							$temp_counter--;
						}
					}
				}
				
				//get top users by posts
				$top_users_by_posts = get_top_users($limit_count, 'posts', 'DESC');
				if ($top_users_by_posts)
				{
					$max_count = $top_users_by_posts[0]['count'];
					$template->assign_var('S_TOP_USERS_BY_POSTS', true);
					foreach ($top_users_by_posts as $current_user)
					{						
						$template->assign_block_vars('top_by_posts_row', array(
							'U_USER'					=> get_username_string('full', $current_user['u_id'], $current_user['username'], $current_user['u_colour']),							
							'COUNT'				=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $config['num_posts'] * 100, 2),
							'BARWIDTH'					=> number_format($current_user['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top users by topics
				$top_users_by_topics = get_top_users($limit_count, 'topics', 'DESC');
				if ($top_users_by_topics)
				{
					$max_count = $top_users_by_topics[0]['count'];
					$template->assign_var('S_TOP_USERS_BY_TOPICS', true);
					foreach ($top_users_by_topics as $current_user)
					{						
						$template->assign_block_vars('top_by_topics_row', array(
							'U_USER'					=> get_username_string('full', $current_user['u_id'], $current_user['username'], $current_user['u_colour']),							
							'COUNT'				=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $config['num_topics'] * 100, 2),
							'BARWIDTH'					=> number_format($current_user['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top users by recent_posts
				$recent_posts_limit_days = request_var('recent_posts_limit_days', 10);	
				$num_recent_posts = get_num_recent_posts($recent_posts_limit_days);
				if ($num_recent_posts)
				{
					$top_users_by_recent_posts = get_top_users($limit_count, 'recent_posts', 'DESC', $recent_posts_limit_days);
								
					if ($top_users_by_recent_posts)
					{
						$max_count = $top_users_by_recent_posts[0]['count'];
						$template->assign_var('S_TOP_USERS_BY_RECENT_POSTS', true);
						foreach ($top_users_by_recent_posts as $current_user)
						{						
							$template->assign_block_vars('top_by_recent_posts_row', array(
							'U_USER'					=> get_username_string('full', $current_user['u_id'], $current_user['username'], $current_user['u_colour']),							
							'COUNT'				=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $num_recent_posts * 100, 2),
							'BARWIDTH'					=> number_format($current_user['count'] / $max_count * 100, 1),
							));
						}
					}
				}
				$recent_posts_days_limit_options = array(
					'1'		=> 1,
					'3'		=> 3,
					'5'		=> 5,
					'10'	=> 10,
					'15'	=> 15,
					'30'	=> 30,
				);
				
				//get top xx friends
				$zebra_count = get_zebra_count();
				$zebra_ary = get_zebra_info($limit_count, 'friends', 'DESC');
				if ($zebra_ary)
				{
					$max_count = $zebra_ary[0]['count'];
					$template->assign_var('S_TOP_FRIENDS', true);
					foreach ($zebra_ary as $current_user)
					{						
						$template->assign_block_vars('top_friends_row', array(
							'U_USER'					=> get_username_string('full', $current_user['user_id'], $current_user['username'], $current_user['user_colour']),							
							'COUNT'						=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $zebra_count * 100, 2),
							'BARWIDTH'					=> number_format($current_user['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//get top xx ignored users
				$zebra_count = get_zebra_count($limit_count, 'foes_count', 'DESC');
				$zebra_ary = get_zebra_info('foes');
				if ($zebra_ary)
				{
					$max_count = $zebra_ary[0]['count'];
					$template->assign_var('S_TOP_FOES', true);
					foreach ($zebra_ary as $current_user)
					{						
						$template->assign_block_vars('top_foes_row', array(
							'U_USER'					=> get_username_string('full', $current_user['user_id'], $current_user['username'], $current_user['user_colour']),							
							'COUNT'						=> $current_user['count'],
							'PCT'						=> number_format($current_user['count'] / $zebra_count * 100, 2),
							'BARWIDTH'					=> number_format($current_user['count'] / $max_count * 100, 1),
						));
					}
				}
				
				//show the limit_days box anyway
				$recent_posts_days_limit_prompt = $user->lang['RECENT_POSTS_DAYS_LIMIT_PROMPT'];
				$template->assign_var('RECENT_POSTS_DAYS_LIMIT_SELECT_BOX', make_select_box($recent_posts_days_limit_options, $recent_posts_limit_days, 'recent_posts_limit_days', $recent_posts_days_limit_prompt, $user->lang['GO'], $this->u_action));
				
				$template->assign_vars(array(
					'TOTAL_MEMBERS'					=> $total_members,
					'TOTAL_REG_USERS'				=> $total_users,
					'DELETED_USERS'					=> $deleted_users,
					'MOST_ONLINE'					=> $config['record_online_users'],
					'MOST_ONLINE_DATE'				=> $user->format_date($config['record_online_date']),
					'TOTAL_ONLINE'					=> $total_online,	
					'U_WHO_IS_ONLINE'				=> append_sid("{$phpbb_root_path}viewonline.$phpEx"),
					'WHO_IS_ONLINE_EXPLAIN'			=> sprintf($user->lang['WHO_IS_ONLINE_EXPLAIN'], $config['load_online_time']),
					'TOTAL_HIDDEN'					=> $total_hidden,
					'TOTAL_MEMBERS_ONLINE'			=> $total_members_online,
					'TOP_USERS_BY_POSTS'		=> sprintf($user->lang['TOP_USERS_BY_POSTS'], $limit_count),					
					'TOP_USERS_BY_TOPICS'		=> sprintf($user->lang['TOP_USERS_BY_TOPICS'], $limit_count),
					'TOP_USERS_BY_RECENT_POSTS'		=> sprintf($user->lang['TOP_USERS_BY_RECENT_POSTS'], $limit_count, $recent_posts_limit_days),			
					'TOP_FRIENDS'					=> sprintf($user->lang['TOP_FRIENDS'], $limit_count),
					'TOP_FOES'					=> sprintf($user->lang['TOP_FOES'], $limit_count),
				));
				
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['USERS']);
			}
			else
			{
				$template->assign_var('S_ACTIVITY_USERS_DISABLED', true);
			}
			break;
			
			default:		
		}
		


		$template->assign_var('LIMIT_SELECT_BOX', make_select_box($limit_options, $limit_count, 'limit_count', $limit_prompt, $user->lang['GO'], $this->u_action));
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['STATS_ACTIVITY_' . strtoupper($mode)],			
			'S_FS_ACTION'		=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = 'stats/stats_activity_' . $mode;
		$this->lang_name = 'stats_activity_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->lang_name)];
	}
}
?>