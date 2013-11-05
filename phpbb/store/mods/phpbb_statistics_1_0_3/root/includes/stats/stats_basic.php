<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_basic.php 167 2011-02-09 01:07:15Z marc1706 $
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
* fs_basic
* Displays basic statistics - submodules : basic, advanced
*
* @package fs
*/
class stats_basic
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $cache, $stats_config;
		
		$do_redirect = false; // needed for misc stats
		
		// we should be able to remove this, since this php file is called by stats.php
		// @todo: check if we need this
		if(sizeof($stats_config) < 1)
		{
			$stats_config = obtain_stats_config();
		}
		
		switch ($mode)
		{
			case 'basic':
			if($stats_config['basic_basic_enable'])
			{
				//get and display the basic statistics
				$total_posts = $config['num_posts'];
				$total_topics = $config['num_topics'];
				$total_users = $config['num_users'];
				$total_attachments = $config['num_files'];
				$board_start_date = $user->format_date($config['board_startdate']);
				
				$topic_types_count = $cache->get('topic_types_count');
				if(!isset($topic_types_count['global']) || select_cache_refresh('topic_types_count'))
				{
					$topic_types_count = get_topic_types_count();
					$cached_topic_types_count = $topic_types_count;
					$cache->put('topic_types_count', $cached_topic_types_count);
					set_cache_refresh('topic_types_count');
				}
				/*
				* Users Account Data will be refreshed after one day
				* $user_count_data_refresh defaults to time() in order to prevent errors and unwanted behavior
				*/
				$users_count_data = $cache->get('users_count_data');
				if(!isset($users_count_data['active']) || select_cache_refresh('users_count_data'))
				{
					$users_count_data = get_user_count_data();
					$cached_users_count_data = $users_count_data;
					$cache->put('users_count_data', $cached_users_count_data);
					set_cache_refresh('users_count_data');
				}
				
				// get highest user_id for $users_per_day
				$sql = 'SELECT MAX(user_id) AS count FROM ' . USERS_TABLE;
				$result = $db->sql_query($sql);
				$max_user = (int) $db->sql_fetchfield('count');
				$db->sql_freeresult($result);
				
				//get board age in days
				$boarddays = (time() - $config['board_startdate']) / 86400;
				//averages
				$posts_per_day = ($boarddays < 1) ? ($total_posts) : (sprintf('%.2f', $total_posts / $boarddays));
				$topics_per_day = ($boarddays < 1) ? ($total_topics) : (sprintf('%.2f', $total_topics / $boarddays));
				$users_per_day = ($boarddays < 1) ? ($total_users) : (sprintf('%.2f', ($max_user - $users_count_data['registered_bots'] - 1) / $boarddays)); // don't count the bots and don't count the guest user
				$files_per_day = ($boarddays < 1) ? ($total_attachments) : (sprintf('%.2f', $total_attachments / $boarddays));
				
				$forum_count = get_forum_count();
				$total_forum_cat = $forum_count[FORUM_CAT];
				$total_forum_post = $forum_count[FORUM_POST];
				$total_forum_link = $forum_count[FORUM_LINK];
				$total_forums = $total_forum_cat + $total_forum_post + $total_forum_link;				
				$total_polls = get_polls_count();
				$total_views = get_page_views_count();
				
				$template->assign_vars(array(
					'TOTAL_POSTS'					=> $total_posts,
					'TOTAL_TOPICS'					=> $total_topics,
					'TOTAL_USERS'					=> $total_users,
					'TOTAL_FORUM_CAT'			=> $total_forum_cat,
					'TOTAL_FORUM_POST'		=> $total_forum_post,
					'TOTAL_FORUM_LINK'		=> $total_forum_link,
					'TOTAL_FORUMS'			=> $total_forums,
					'TOTAL_ATTACHMENTS'		=> $total_attachments,
					'TOTAL_POLLS'			=> $total_polls,
					'TOTAL_VIEWS'			=> $total_views,
					'TOPIC_TYPES_GLOBAL'	=> $topic_types_count['global'],
					'TOPIC_TYPES_ANNOUNCE'  => $topic_types_count['announce'],
					'TOPIC_TYPES_STICKY'		=> $topic_types_count['sticky'],
					'TOPIC_TYPES_NORMAL'		=> $topic_types_count['normal'],
					'TOPIC_TYPES_UNAPPROVED'	=> $topic_types_count['unapproved'],
					'UNAPPROVED_POSTS'		=> $topic_types_count['unapproved_posts'],
					'USERS_INACTIVE'		=> $users_count_data['inactive'],
					'USERS_ACTIVE'			=> $users_count_data['active'],
					'USERS_ACTIVE_EXPLAIN'	=> sprintf($user->lang['USERS_ACTIVE_EXPLAIN'], 30), //replace 30 by configurable number if you wish
					'USERS_INACTIVE_EXPLAIN'	=> sprintf($user->lang['USERS_INACTIVE_EXPLAIN'], 30), //same comment as above
					'TOTAL_BOTS'			=> $users_count_data['registered_bots'],
					'VISITED_BOTS'			=> $users_count_data['visited_bots'],
					'AVG_POSTS_PER_DAY'		=> $posts_per_day,
					'AVG_TOPICS_PER_DAY'	=> $topics_per_day,
					'AVG_USERS_PER_DAY'	=> $users_per_day,
					'AVG_FILES_PER_DAY'		=> $files_per_day,
					'MOST_ONLINE'			=> $config['record_online_users'],
					'MOST_ONLINE_DATE'		=> $user->format_date($config['record_online_date']),
				));
			}
			else
			{
				$template->assign_var('S_BASIC_BASIC_DISABLED', true);
			}
			break;
			
			case 'advanced':
			if($stats_config['basic_advanced_enable'])
			{
				//get and display advanced statistics
				if(!function_exists('recalc_nested_sets'))
				{
					include ("{$phpbb_root_path}includes/functions_admin.$phpEx"); //for database size
				}
				
				
				$sort_by = request_var('sort_by', 'name');
				
				//create an array containing the sort_by options as $option=>$option_lang
				$sort_by_options = array(
					'name'		=> $user->lang['COMPONENTS_NAME'],
					'id'		=> $user->lang['COMPONENTS_ID'],
					'copyright'	=> $user->lang['COMPONENTS_AUTHOR'],
				);
				$sort_by_prompt = '';
				
				switch($sort_by)
				{
					case 'name':
						$lang_sort_by = 'local_name';
					break;
					
					case 'id':
						$lang_sort_by = 'id';
					break;
					
					case 'author':
						$lang_sort_by = 'author';
					break;
					
					default:
						$lang_sort_by = 'local_name';
				}
				
				$board_start_date = $user->format_date($config['board_startdate']);
				
				//get attachments info
				$attachments = array(
					'total_files'			=> $config['num_files'],
					'total_size'			=> $config['upload_dir_size'],
				);
				//get avatars info
				$avatars = array(
					'total_files'			=> 0,
					'total_size'			=> 0,
				);
				$avatar_dir = @opendir($phpbb_root_path . $config['avatar_path']);
				if ($avatar_dir)
				{
					while (($file = readdir($avatar_dir)) !== false)
					{
						if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
						{
							$avatars['total_size'] += filesize($phpbb_root_path . $config['avatar_path'] . '/' . $file);
							++$avatars['total_files'];
						}
					}
					@closedir($avatar_dir);
				}
				//get cached files data
				$cached_files = array(
					'total_files'			=> 0,
					'total_size'			=> 0,
				);
				$cache_path = 'cache';
				$cache_dir = @opendir($phpbb_root_path . $cache_path);
				if ($cache_dir)
				{
					while (($file = readdir($cache_dir)) !== false)
					{
						if ($file[0] != '.' && $file != 'CVS' && strpos($file, 'index.') === false)
						{
							$cached_files['total_size'] += filesize($phpbb_root_path . $cache_path . '/' . $file);
							++$cached_files['total_files'];
						}
					}
					@closedir($cache_dir);
				}
				
				//get info about installed components
				//styles
				$styles = array();
				if($sort_by == 'id')
				{
					$sql = 'SELECT style_name, style_copyright FROM ' . STYLES_TABLE . ' ORDER BY ABS(style_' . $sort_by . ')';
				}
				elseif ($sort_by == 'name' || $sort_by == 'copyright') 
				{
					$sql = 'SELECT style_name, style_copyright FROM ' . STYLES_TABLE . ' ORDER BY LOWER(style_' . $sort_by . ')';
				}
				else
				{
					trigger_error($user->lang['NO_ACTION']); // This shouldn't happen, but we'll just make sure
				}
				$result = $db->sql_query($sql);
				while ($style_row = $db->sql_fetchrow($result))
				{
					$styles[] = $style_row;
				}
				$db->sql_freeresult($result);
				foreach ($styles as $current_style)
				{
					$template->assign_block_vars('stylerow', array(
						'STYLE_NAME'			=> $current_style['style_name'],
						'STYLE_COPYRIGHT'			=> $current_style['style_copyright'],
					));
				}
				//imagesets
				$imagesets = array();
				if($sort_by == 'id')
				{
					$sql = 'SELECT imageset_name, imageset_copyright FROM ' . STYLES_IMAGESET_TABLE . ' ORDER BY ABS(imageset_' . $sort_by . ')';
				}
				else
				{
					$sql = 'SELECT imageset_name, imageset_copyright FROM ' . STYLES_IMAGESET_TABLE . ' ORDER BY LOWER(imageset_' . $sort_by . ')';
				}
				$result = $db->sql_query($sql);
				while ($imageset_row = $db->sql_fetchrow($result))
				{
					$imagesets[] = $imageset_row;
				}
				$db->sql_freeresult($result);
				foreach ($imagesets as $current_imageset)
				{
					$template->assign_block_vars('imagesetrow', array(
						'IMAGESET_NAME'			=> $current_imageset['imageset_name'],
						'IMAGESET_COPYRIGHT'			=> $current_imageset['imageset_copyright'],
					));
				}
				//templates
				$templates = array();
				if($sort_by == 'id')
				{
					$sql = 'SELECT template_name, template_copyright FROM ' . STYLES_TEMPLATE_TABLE . ' ORDER BY ABS(template_' . $sort_by . ')';
				}
				else
				{
					$sql = 'SELECT template_name, template_copyright FROM ' . STYLES_TEMPLATE_TABLE . ' ORDER BY LOWER(template_' . $sort_by . ')';
				}
				$result = $db->sql_query($sql);
				while ($template_row = $db->sql_fetchrow($result))
				{
					$templates[] = $template_row;
				}
				$db->sql_freeresult($result);
				foreach ($templates as $current_template)
				{
					$template->assign_block_vars('templaterow', array(
						'TEMPLATE_NAME'			=> $current_template['template_name'],
						'TEMPLATE_COPYRIGHT'			=> $current_template['template_copyright'],
					));
				}
				//themes
				$themes = array();
				if($sort_by == 'id')
				{
					$sql = 'SELECT theme_name, theme_copyright FROM ' . STYLES_THEME_TABLE . ' ORDER BY ABS(theme_' . $sort_by . ')';
				}
				else
				{
					$sql = 'SELECT theme_name, theme_copyright FROM ' . STYLES_THEME_TABLE . ' ORDER BY LOWER(theme_' . $sort_by . ')';
				}
				$result = $db->sql_query($sql);
				while ($theme_row = $db->sql_fetchrow($result))
				{
					$themes[] = $theme_row;
				}
				$db->sql_freeresult($result);
				foreach ($themes as $current_theme)
				{
					$template->assign_block_vars('themerow', array(
						'THEME_NAME'			=> $current_theme['theme_name'],
						'THEME_COPYRIGHT'			=> $current_theme['theme_copyright'],
					));
				}
				
				//lang packs
				$lang_packs = array();
				if($lang_sort_by == 'id')
				{
					$sql = 'SELECT lang_local_name, lang_iso, lang_author FROM ' . LANG_TABLE . ' ORDER BY ABS(lang_' . $lang_sort_by . ')';
				}
				else
				{
					$sql = 'SELECT lang_local_name, lang_iso, lang_author FROM ' . LANG_TABLE . ' ORDER BY LOWER(lang_' . $lang_sort_by . ')';
				}
				$result = $db->sql_query($sql);
				while ($lang_row = $db->sql_fetchrow($result))
				{
					$lang_packs[] = $lang_row;
				}
				$db->sql_freeresult($result);
				foreach ($lang_packs as $current_lang)
				{
					$template->assign_block_vars('langrow', array(
						'LANG_NAME'			=> $current_lang['lang_local_name'],
						'LANG_ISO'			=> $current_lang['lang_iso'],
						'LANG_AUTHOR'		=> $current_lang['lang_author'],
					));
				}
				
				$template->assign_vars(array(
					'START_DATE'					=> $board_start_date,
					'BOARD_AGE'						=> get_time_string($config['board_startdate']),
					'GZIP_COMPRESSION'				=> ($config['gzip_compress']) ? $user->lang['ON'] : $user->lang['OFF'],
					'ATTACHMENTS_TOTAL'		=> $attachments['total_files'],
					'ATTACHMENTS_SIZE'			=> get_formatted_filesize($attachments['total_size']),
					'AVATARS_TOTAL'		=> $avatars['total_files'],
					'AVATARS_SIZE'			=> get_formatted_filesize($avatars['total_size']),
					'CACHED_FILES_TOTAL'	=> $cached_files['total_files'],
					'CACHED_FILES_SIZE'		=> get_formatted_filesize($cached_files['total_size']),
					'S_STYLES'				=> ($styles) ? true : false,
					'S_IMAGESETS'			=> ($imagesets) ? true : false,
					'S_TEMPLATES'			=> ($templates) ? true : false,
					'S_THEMES'				=> ($themes) ? true : false,
					'S_LANG_PACKS'			=> ($lang_packs) ? true : false,
				));
				
				
				if(!$stats_config['basic_advanced_security'] && $stats_config['basic_advanced_pretend_version'])
				{
					// pretend to have newest version installed
					$errstr = '';
					$errno = 0;

					$info = get_remote_file('www.phpbb.com', '/updatecheck', ((defined('PHPBB_QA')) ? '30x_qa.txt' : '30x.txt'), $errstr, $errno);
				
					// if there is an error, fall back to displaying 3.x.x
					if ($info != false)
					{
						$info = explode("\n", $info);
						$latest_version = trim($info[0]);
						$template->assign_vars(array(
							'BOARD_VERSION'					=> $latest_version,
							'DATABASE_INFO'					=> $db->sql_server_info(),
							'DATABASE_SIZE'					=> get_database_size(),
							));
					}
					else
					{
						$template->assign_vars(array(
							'BOARD_VERSION'					=> '3.x.x',
							'DATABASE_INFO'					=> $db->sql_server_info(),
							'DATABASE_SIZE'					=> get_database_size(),
							));
					}
				}
				elseif(!$stats_config['basic_advanced_security'] && !$stats_config['basic_advanced_pretend_version'])
				{
					// Displays the exact version number, database info and database size
					$template->assign_vars(array(
						'BOARD_VERSION'					=> $config['version'],
						'DATABASE_INFO'					=> $db->sql_server_info(),
						'DATABASE_SIZE'					=> get_database_size(),
					));
				}
				else
				{
					$template->assign_vars(array(
						'S_HIDE_BOARD_VERSION'			=> true,
						'S_HIDE_DATABASE_INFO'			=> true,
					));
				}
				
				$sort_by_prompt = sprintf($user->lang['SORT_BY_PROMPT']);
				$template->assign_var('SORT_BY_SELECT_BOX', make_select_box($sort_by_options, $sort_by, 'sort_by', $sort_by_prompt, $user->lang['GO'], $this->u_action));
				
			}
			else
			{
				$template->assign_var('S_BASIC_ADVANCED_DISABLED', true);
			}
			break;
			
			case 'miscellaneous':
			if($stats_config['basic_miscellaneous_enable'])
			{
				$template_count = 0;
				$max_count = 0;
				$limit_count = request_var('limit_count', 10); //replace 10 by the config option

				//create an array containing the limit_count options as $option=>$option_lang
				$limit_options = array(
					'1'		=> 1,
					'3'		=> 3,
					'5'		=> 5,
					'10'	=> 10,
					'15'	=> 15,
				);
				$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['SMILEY'] . ', ' . $user->lang['BBCODE'] . ', ' . $user->lang['ICONS']);
				
				/*
				* find out if we need to run the resync
				*/
				$next_resync = $stats_config['resync_stats_last_sync'] + ($stats_config['resync_stats'] * 86400);
				
				if($next_resync <= time())
				{
					$do_resync = true;
					set_stats_config('resync_stats_last_sync', time());
				}
				else
				{
					$do_resync = false;
				}
				
				/*	
				* This will take a while, please don't overdo it
				* only users with admin permissions will be able to do run this
				*/
				if(($stats_config['resync_stats_bbcodes'] && $auth->acl_get('a_')) || ($do_resync == true && $auth->acl_get('a_')))
				{
					$start = request_var('start_sql', 0); // find out where we need to start the sql queries
					// Do a loop in order to prevent timeouts
					$url = $phpbb_root_path . 'stats.' . $phpEx;
					$get_vars = 'i=basic&amp;mode=miscellaneous';
					$do_redirect = overall_bbcode_smiley_count($start, $url, $get_vars);
				}
				
				if($do_redirect == false) // we don't need a redirect so just go on
				{
					/*
					* if we finished counting the smilies and bbcodes, set it back to 0
					* we only do this if user has admin permissions, since users without admin permissions didn't run the resync
					*/
					if($stats_config['resync_stats_bbcodes']  && $auth->acl_get('a_'))
					{
						set_stats_config('resync_stats_bbcodes', 0);
					}
					// get BBCodes stats
					$bbcodes = get_bbcode_count();
					
					$template->assign_vars(array(
						'TOTAL_BBCODE_COUNT' 		=> $bbcodes['total'],
						'CUSTOM_BBCODE_COUNT'		=> $bbcodes['custom'],
					));
					
					// get top XX BBCodes used on board
					$bbcode_ary = get_top_bbcodes($limit_count, '');
					$total_count = get_top_bbcodes($limit_count, 'total');
					
					//	Now we check if maybe someone deleted the bbcodes from the database
					if(!isset($bbcode_ary))
					{
						$url = $phpbb_root_path . 'stats.' . $phpEx;
						$get_vars = 'i=basic&amp;mode=miscellaneous';
						set_stats_config('resync_stats_bbcodes', 1);
						overall_bbcode_smiley_count(0, $url, $get_vars);
					}
					
					if($total_count > 0 && $bbcode_ary[0]['bbcode_count'] > 0)
					{
						$max_count = $bbcode_ary[0]['bbcode_count'];
					
						$template->assign_var('S_TOP_BBCODES', true);
					
						foreach ($bbcode_ary as $current_bbcode)
						{
							$template->assign_block_vars('top_bbcodes_row', array(
							'BBCODE'					=> $current_bbcode['bbcode'],
							'COUNT'						=> $current_bbcode['bbcode_count'],
							'PCT'						=> number_format($current_bbcode['bbcode_count'] / $total_count * 100, 2),
							'BARWIDTH'					=> number_format($current_bbcode['bbcode_count'] / $max_count * 100, 1),
							));
						}	
					}
					$template->assign_var('TOP_BBCODES', sprintf($user->lang['TOP_BBCODES'], $limit_count));
					
					// get smiley statistics
					$smiley_count = get_smiley_count();
					
					$template->assign_vars(array(
						'TOTAL_SMILEY_COUNT' 		=> $smiley_count['total'],
						'DISPLAY_ON_POSTING_COUNT'	=> ($smiley_count['total']) ? $smiley_count['dop'] . ' / ' . $smiley_count['total'] .  ' (' . number_format($smiley_count['dop'] / $smiley_count['total'] * 100, 2) . '%)' : ' 0 / 0',
						'TOP_SMILIES_BY_URL'		=> sprintf($user->lang['TOP_SMILIES_BY_URL'], $limit_count),	
					));
					// get top xx smilies
					if($smiley_count['dop'])
					{
						$smiley_ary = get_top_smilies($limit_count, '');
						if(isset($smiley_ary[0]['count']))
						{					
							$max_count = $smiley_ary[0]['count'];
						}
						$total_count = get_top_smilies($limit_count, 'total');
						
						if($config['num_posts'] && ($max_count > 0))
						{
							$template->assign_var('S_TOP_SMILIES_BY_URL', true);
						
							if ($config['num_posts'])
							{
								foreach ($smiley_ary as $current_smiley)
								{
									$template->assign_block_vars('top_by_url_row', array(
									'SMILEY'					=> "<img src=\"{$phpbb_root_path}/images/smilies/" . $current_smiley['url'] . '" alt="' . $current_smiley['emotion'] . '" title="' . $current_smiley['emotion'] . '" />',
									'COUNT'						=> $current_smiley['count'],
									'PCT'						=> number_format($current_smiley['count'] / $total_count * 100, 2),
									'BARWIDTH'					=> number_format($current_smiley['count'] / $max_count * 100, 1),
									));
								}
							}
						}
						
					
					}
					
					// get top XX icons used on board
					$total_count = 0;
					$total_count = get_top_icons_count($limit_count, $type = 'total', $order = 'DESC');
					
					if($total_count > 0)
					{
						$icons_ary = get_top_icons_count($limit_count, $type = 'each', $order = 'DESC');
						$template->assign_var('S_TOP_ICONS', true);
						$max_count = $icons_ary[0]['count'];
						foreach ($icons_ary as $current_icon)
						{					
							$template->assign_block_vars('top_icons_row', array(	
							'ICON'					=> "<img src=\"{$phpbb_root_path}/images/icons/" . $current_icon['icon_url'] . '" alt="' . $current_icon['icon_url'] . '" />',
							'COUNT'						=> $current_icon['count'],
							'PCT'						=> number_format($current_icon['count'] / $total_count * 100, 2),
							'BARWIDTH'					=> number_format($current_icon['count'] / $max_count * 100, 1),
							));
						}
					}
					$template->assign_var('TOP_ICONS', sprintf($user->lang['TOP_ICONS'], $limit_count));
					
					// warnings statistics
					if (!$stats_config['basic_miscellaneous_hide_warnings'])
					{
						$warning_count = get_warning_count();
						$own_warnings_count = get_warning_count('own_warnings');
						$total_users = $config['num_users'];
						$boarddays = (time() - $config['board_startdate']) / 86400;
					
						$template->assign_vars(array(
							'TOTAL_WARNING_COUNT' 		=> $warning_count,
							'OWN_WARNINGS_COUNT'		=> ($warning_count) ? $own_warnings_count . ' / ' . $warning_count .  ' (' . number_format($own_warnings_count / $warning_count * 100, 2) . '%)' : ' 0 / 0',
							'WARNINGS_PER_USER'			=> ($total_users) ? $warning_count . ' / ' . $total_users .  ' (' . number_format($warning_count / $total_users, 3) . ')' : ' 0 / 0',
							'WARNINGS_PER_DAY'			=> ($boarddays) ? number_format($warning_count / $boarddays, 3) : '0',
						));
					}
					else
					{
						$template->assign_var('S_HIDE_WARNINGS_STATS', true);
					}
					$template->assign_var('LIMIT_SELECT_BOX', make_select_box($limit_options, $limit_count, 'limit_count', $limit_prompt, $user->lang['GO'], $this->u_action));
				}
				else
				{
					// Tell the user how far we have come
					$progress_info = sprintf($user->lang['RECOUNT_PROGRESS'], $start, $config['num_posts']);
					$template->assign_var('ADDON_INFO', $progress_info);
				}
			}
			else
			{
				$template->assign_var('S_BASIC_MISCELLANEOUS_DISABLED', true);
			}
			break;
			
			default:
		}
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['STATS_BASIC_' . strtoupper($mode)],			
			'S_STATS_ACTION'	=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = (!$do_redirect) ? 'stats/stats_basic_' . $mode : 'stats/addons/no_addon';
		$this->lang_name = 'stats_basic_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->lang_name)];
		
	}
}
?>