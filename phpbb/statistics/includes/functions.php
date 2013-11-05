<?php

/**
*
* @package phpBB Statistics
* @version $Id: functions.php 170 2011-02-09 01:44:15Z marc1706 $
* @copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de, (c) TheUniqueTiger - Nayan Ghosh
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Forum Statistics by TheUniqueTiger - Nayan Ghosh
*/


if (!defined('IN_PHPBB'))
{
   exit;
}

if(!function_exists('display_forums'))
{
	include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
}

/* 	Get stats config
*   based on function obtain_portal_config (included in the Board3 Portal package - www.board3.de) 
*/
function obtain_stats_config()
{
	global $db, $cache;

	$stats_config = array();

	$sql = 'SELECT config_name, config_value
		FROM ' . STATS_CONFIG_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$stats_config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);


	return $stats_config;
}

/*	Set config value. Creates missing config entry.
* 	based on function set_portal_config (included in the Board3 Portal package - www.board3.de) 
*/
function set_stats_config($config_name, $config_value)
{
	global $db, $cache, $stats_config;

	$sql = 'UPDATE ' . STATS_CONFIG_TABLE . "
		SET config_value = '" . $db->sql_escape($config_value) . "'
		WHERE config_name = '" . $db->sql_escape($config_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows() && isset($stats_config[$config_name]) == false)
	{
		$sql = 'INSERT INTO ' . STATS_CONFIG_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'config_name'	=> $config_name,
			'config_value'	=> $config_value));
		$db->sql_query($sql);
	}

	$stats_config[$config_name] = $config_value;
}

/**	Change stats add-ons in database and install them if needed
*	this will install the add-on to the database
*	you can also use this function if you want to change the settings in the database
*/
function set_stats_addon($class_name, $enabled = 0)
{
	global $db;
	
	$sql = 'UPDATE ' . STATS_ADDONS_TABLE . "
		SET addon_enabled = '" . $db->sql_escape($enabled) . "'
		WHERE addon_classname = '" . $db->sql_escape($class_name) . "'";
	$db->sql_query($sql);

	if (!$db->sql_affectedrows())
	{
		$sql = 'INSERT INTO ' . STATS_ADDONS_TABLE . ' ' . $db->sql_build_array('INSERT', array(
			'addon_classname'	=> $class_name,
			'addon_enabled'	=> $enabled));
		$db->sql_query($sql);
	}
}

/**
* choose if we need to refresh the selected cache data
* @param <string> $name: the name of the cached data
*/
function select_cache_refresh($name)
{
	global $db, $cache, $stats_config;
	
	$cache_name = $name . '_time';
	
	if($cache->get($cache_name) === false)
	{
		// it doesn't even exist
		return false;
	}
	else
	{
		// it exists, so check if we need to refresh
		$last_refresh = $cache->get($cache_name);
		$refresh_span = $stats_config['resync_stats'] * 86400;
		
		if($last_refresh <= (time() - $refresh_span))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/**
* set refresh time of cache data
* @param <string> $name: the name of the cached data
*/
function set_cache_refresh($name)
{
	global $db, $cache;
	
	$cache_name = $name . '_time';
	
	$cache->put($cache_name, time());
}

/*function get_time_string --- returns the formatted time string like 3 months 20 days etc.
*@param $old_time : the old timestamp
*/
function get_time_string($old_time, $current = 0)
{
	global $user;
	$old_time_ary = getdate($old_time);	
	$current_time_ary = array();
	$diff_ary = array(
		'seconds' 			=> 0,
		'minutes'			=> 0,
		'hours'				=> 0,
		'days'				=> 0,
		'months'			=> 0,
		'years'				=> 0
	);
	$decrement_ary = array(
		'seconds' 			=> false,
		'minutes'			=> false,
		'hours'				=> false,
		'days'				=> false,
		'months'			=> false,
		'years'				=> false
	);
	if (!$current)
	{
		$current = time();
	}
	$temp_time_ary = $old_time_ary;
	if (isset($current)) //do subtraction and get the difference
	{
		$current_time_ary = getdate($current);
		
		//do seconds
		$diff_ary['seconds'] = $current_time_ary['seconds'] - $old_time_ary['seconds'];
		if ($diff_ary['seconds'] < 0)
		{
			$diff_ary['seconds'] = 60 + $diff_ary['seconds'];
			$decrement_ary['minutes'] = true;
		}
		
		//do minutes
		$diff_ary['minutes'] = $current_time_ary['minutes'] - $old_time_ary['minutes'];
		if (($decrement_ary['minutes']) == true)
		{
			--$diff_ary['minutes'];
		}
		if ($diff_ary['minutes'] < 0)
		{
			$diff_ary['minutes'] = 60 + $diff_ary['minutes'];
			$decrement_ary['hours'] = true;
		}
		
		//do hours
		$diff_ary['hours'] = $current_time_ary['hours'] - $old_time_ary['hours'];
		if (($decrement_ary['hours']) == true)
		{
			--$diff_ary['hours'];
		}
		if ($diff_ary['hours'] < 0)
		{
			$diff_ary['hours'] = 24 + $diff_ary['hours'];
			$decrement_ary['days'] = true;
		}
		
		//do days
		$diff_ary['days'] = $current_time_ary['mday'] - $old_time_ary['mday'];
		if (($decrement_ary['days'])  == true)
		{
			--$diff_ary['days'];
		}
		if ($diff_ary['days'] < 0)
		{
			$diff_ary['days'] = 30 + $diff_ary['days'];
			$decrement_ary['months'] = true;
		}
		
		//do months
		$diff_ary['months'] = $current_time_ary['mon'] - $old_time_ary['mon'];
		if (($decrement_ary['months']) == true)
		{
			--$diff_ary['months'];
		}
		if ($diff_ary['months'] < 0)
		{
			$diff_ary['months'] = 12 + $diff_ary['months'];
			$decrement_ary['years'] = true;
		}
		
		//do years
		$diff_ary['years'] = $current_time_ary['year'] - $old_time_ary['year'];
		if (($decrement_ary['years'])  == true)
		{
			--$diff_ary['years'];
		}
		
	}
	$result = '';	
	$result .= (isset($diff_ary['years'])) ? $diff_ary['years'] . ' ' . (($diff_ary['years'] > 1) ? $user->lang['YEARS'] . ' ' : $user->lang['YEAR'] . ' ') : '';
	$result .= (isset($diff_ary['months'])) ? $diff_ary['months'] . ' ' . (($diff_ary['months'] > 1) ? $user->lang['MONTHS'] . ' ' : $user->lang['MONTH'] . ' ') : '';
	$result .= (isset($diff_ary['days'])) ? $diff_ary['days'] . ' ' . (($diff_ary['days'] > 1) ? $user->lang['DAYS'] . ' ' : $user->lang['DAY'] . ' ') : '';
	$result .= (isset($diff_ary['hours'])) ? $diff_ary['hours'] . ' ' . (($diff_ary['hours'] > 1) ? $user->lang['HOURS'] . ' ' : $user->lang['HOUR'] . ' ') : '';
	$result .= (isset($diff_ary['minutes'])) ? $diff_ary['minutes'] . ' ' . (($diff_ary['minutes'] > 1) ? $user->lang['MINUTES'] . ' ' : $user->lang['MINUTE'] . ' ') : '';
	$result .= (isset($diff_ary['seconds'])) ? $diff_ary['seconds'] . ' ' . (($diff_ary['seconds'] > 1) ? $user->lang['SECONDS'] . ' ' : $user->lang['SECOND'] . ' ') : '';
	
	return $result;
}

/** 
* function get_forum_count
* return the counts of posting forums, categories, and link forums
* old function replace by Marc Alexander in order to reduce the queries used by phpBB Statistics
* now uses only 1 query instead of 3 queries
* by Marc Alexander (c) 2010
*/
function get_forum_count()
{
	global $db;
	$count = array(
		FORUM_POST => 0,
		FORUM_CAT => 0,
		FORUM_LINK => 0,
	);	

	$sql = 'SELECT DISTINCT(forum_id) AS id, forum_type AS type
				FROM ' . FORUMS_TABLE . '
				WHERE ' . $db->sql_in_set('forum_type', array(FORUM_POST, FORUM_CAT, FORUM_LINK));
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		switch($row['type'])
		{
			case FORUM_POST:
				++$count[FORUM_POST];
			break;
			
			case FORUM_CAT:
				++$count[FORUM_CAT];
			break;
			
			case FORUM_LINK:
				++$count[FORUM_LINK];
			break;
			
			default:
			
		}
	}
	$db->sql_freeresult($result);
	return $count;
}

/* function get_polls_count --- returns the count of polls in the give forum
*@param $forum_id : forum_id, for future use
*/
function get_polls_count($type = '', $forum_id = 0)
{
	global $db, $user;
	$count = 0;

	switch ($type)
	{
		case 'open':
			$sql = 'SELECT COUNT(topic_id) AS polls_count
						FROM ' . TOPICS_TABLE . '
						WHERE poll_start > 0
							AND poll_length = 0 OR poll_start + poll_length > ' . time();
		break;
		
		case 'votes':
			$sql = 'SELECT COUNT(*) AS polls_count
						FROM ' . POLL_VOTES_TABLE;						
		break;
		
		case 'voted':
			$sql = 'SELECT COUNT(DISTINCT topic_id) AS polls_count
						FROM ' . POLL_VOTES_TABLE . '
						WHERE vote_user_id = ' . (int) $user->data['user_id'];
		break;
		
		default:
			$sql = 'SELECT COUNT(DISTINCT topic_id) AS polls_count
				FROM ' . POLL_OPTIONS_TABLE;
	}			
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('polls_count');
	$db->sql_freeresult($result);
	
	return $count;
}

/*function get_topic_types_count ----- returns an array containing the number of topics of various types
*@param $forum_id : forum_id, for future use
*/
function get_topic_types_count($forum_id = 0)
{
	global $db, $config;
	
	$temp_counts = array(0,0,0,0);
	
	$where_forum_sql = (($forum_id)) ? ' WHERE forum_id = ' . (int) $forum_id : '';
	
	//first get counts by topic_type field
	$sql = 'SELECT topic_type, COUNT(topic_id) as topics_count
				FROM ' . TOPICS_TABLE . $where_forum_sql . '
				GROUP BY topic_type';
	$result = $db->sql_query($sql);
	while ($temp_ary = $db->sql_fetchrow($result))
	{
		$temp_counts[$temp_ary['topic_type']] = $temp_ary['topics_count'];
	}
	$db->sql_freeresult($result);
	
	//now get the count of unapproved topics
	$sql = 'SELECT COUNT(topic_id) as topics_count
				FROM ' . TOPICS_TABLE . $where_forum_sql;
	$sql .= (($where_forum_sql)) ? ' AND topic_approved = 0' : ' WHERE topic_approved = 0';
				 
	$result = $db->sql_query($sql);
	$unapproved_topic_count = $db->sql_fetchfield('topics_count');
	$db->sql_freeresult($result);
	
	//now get the count of unapproved posts
	$sql = 'SELECT COUNT(post_id) as posts_count
				FROM ' . POSTS_TABLE . $where_forum_sql;
	$sql .= (($where_forum_sql)) ? ' AND post_approved = 0' : ' WHERE post_approved = 0';
				 
	$result = $db->sql_query($sql);
	$unapproved_post_count = $db->sql_fetchfield('posts_count');
	$db->sql_freeresult($result);
	
	$counts = array(
		'global'				=> $temp_counts[POST_GLOBAL],
		'announce'			=> $temp_counts[POST_ANNOUNCE],
		'sticky'				=> $temp_counts[POST_STICKY],
		'normal'				=> $temp_counts[POST_NORMAL],
		'unapproved'			=> (int) $unapproved_topic_count,		
		'unapproved_posts'	=> (int) $unapproved_post_count,
	);
	return $counts;
}

/**	function get timezone and dst offset
*	returns the difference to the board timezone or dst
*	by Marc Alexander (c) 2009
*/
function get_timezone_offset($mode, $data1, $data2)
{
	$return = 0; // make sure there will be no errors after cache has been emptied
	
	switch($mode)
	{
		case 'timezone':
			if (($data1 < 0 && $data2 < 0) || ($data1 > 0 && $data2 > 0))
			{
				$return = ($data1 != $data2) ? ($data2 - $data1) : 0;
			}
			elseif (($data1 < 0 && $data2 > 0) || ($data2 != 0 && $data1 == 0))
			{
				$return = $data2 - $data1;
			}
			elseif ($data2 == 0 && $data1 != 0)
			{
				$return = - $data1;
			}
			else 
			{
				$return = 0;
			}
		break;
		
		case 'dst':
			if ($data1 == $data2)
			{
				$return = 0;
			}
			elseif ($data1 < $data2)
			{
				$return = 1;
			}
			
		break;
		
		default:
	
	}
	
	return $return;
}

// function get_user_count_data --- returns the active user count, inactive users, registered bots, visited bots
function get_user_count_data()
{
	global $db, $config, $user;
	//get active users
	$cutoff_days = 30;
	$timezone = get_timezone_offset('timezone', $config['board_timezone'], $user->data['user_timezone']);
	$dst = get_timezone_offset('dst', $config['board_dst'], $user->data['user_dst']);
	$current_time = (time() + ($timezone * 3600) + ($dst * 3600));  
	$cutoff_time = $current_time - ($cutoff_days * 86400);
	$sql = 'SELECT COUNT(user_id) as user_count
				FROM ' . USERS_TABLE . '
				WHERE user_lastvisit > ' . $cutoff_time . '
					AND user_type IN ( ' . USER_NORMAL . ', ' . USER_FOUNDER . ' )';
	$result = $db->sql_query($sql);
	$active_user_count = (int) $db->sql_fetchfield('user_count');
	$db->sql_freeresult($result);
	
	//get bots count
	$sql = 'SELECT COUNT(bot_id) as bot_count
				FROM ' . BOTS_TABLE;
	$result = $db->sql_query($sql);
	$bot_count = (int) $db->sql_fetchfield('bot_count');
	$db->sql_freeresult($result);
	
	//get visited bot count
	$sql = 'SELECT COUNT(bot_id) as bot_count
				FROM ' . BOTS_TABLE . ' b INNER JOIN ' . USERS_TABLE . ' u ON b.user_id = u.user_id 
				WHERE u.user_lastvisit > 0';
	$result = $db->sql_query($sql);
	$visited_bot_count = (int) $db->sql_fetchfield('bot_count');
	$db->sql_freeresult($result);
	
	$return_ary = array(
		'active'				=> $active_user_count,
		'inactive' 			=> $config['num_users'] - $active_user_count,
		'registered_bots'	=> $bot_count,
		'visited_bots'		=> $visited_bot_count,
	);
	return $return_ary;
}

/*function get_topic_type_count --- returns the count of the given topic type in the given forum
@param $topic_type : the type of topic
@param $forum_id : forum_id, for future use
*/
function get_topic_type_count($topic_type = POST_NORMAL, $forum_id = 0)
{
	global $db;
	$count = 0;
	$sql = 'SELECT COUNT(topic_id) AS count FROM ' . TOPICS_TABLE . '
				WHERE topic_type = ' . (int) $topic_type;
	$result = $db->sql_query($sql);
	$count = $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

//function get_topic_views_count --- returns the total topic views
function get_topic_views_count()
{
	global $db;
	$count = 0;
	$sql = 'SELECT SUM(topic_views) AS count FROM ' . TOPICS_TABLE . ' 
				WHERE topic_approved = 1';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_posters_count -- returns the number of users who have posted (post_count > 0)*/
function get_posters_count()
{
	global $db;
	$count = 0;
	$sql = 'SELECT COUNT(user_id) AS count FROM ' . USERS_TABLE . '
				WHERE user_posts > 0';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_formatted_filesize --- returns formatted filesize with B/KB/MB/GB
@param $size - the filesize in bytes
*/
function get_formatted_filesize_fs($size)
{
	global $user;
	return ($size >= 1073741824) ? sprintf('%.2f' . $user->lang['GB'], ($size / 1073741824)) : (($size >= 1048576) ? sprintf('%.2f ' . $user->lang['MB'], ($size / 1048576)) : (($size >= 1024) ? sprintf('%.2f ' . $user->lang['KB'], ($size / 1024)) : sprintf('%.2f ' . $user->lang['BYTES'], $size)));
}

/*function get_top_forums ---- returns the top $limit_count number of forums based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like topics, sticky, posts, polls)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_forums($limit_count = 10, $criteria = 'topics', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'topics':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, f.forum_topics_real AS count
						FROM ' . FORUMS_TABLE . ' f					
						WHERE f.forum_id <> 0 ' . $forum_sql . '
							AND f.forum_type = ' . FORUM_POST . '
						GROUP BY f.forum_id, f.forum_name, f.forum_topics_real
						ORDER BY count ' . $order;
			
		break;
		
		case 'posts':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, f.forum_posts AS count 
						FROM ' . FORUMS_TABLE . ' f  
						WHERE f.forum_id <> 0 ' . $forum_sql . '
							AND f.forum_type = ' . FORUM_POST . '
						GROUP BY f.forum_id, f.forum_name, f.forum_posts
						ORDER BY count ' . $order;
			
		break;
		
		case 'polls':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, COUNT(DISTINCT po.topic_id) AS count 
				FROM ' . POLL_OPTIONS_TABLE . ' po, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE po.topic_id = t.topic_id 
					AND t.forum_id = f.forum_id' . $forum_sql . '
					AND f.forum_type = ' . FORUM_POST . '
				GROUP BY f.forum_id, f.forum_name
				ORDER BY count ' . $order;
			
		break;
		
		case 'sticky':
			$sql = 'SELECT COUNT(t.topic_id) AS count, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE t.topic_type = ' . POST_STICKY . '
					AND t.forum_id = f.forum_id' . $forum_sql . '
					AND f.forum_type = ' . FORUM_POST . '
				GROUP BY f.forum_id, f.forum_name
				ORDER BY count ' . $order;
			
		break;
		
		case 'participation':
			$sql = 'SELECT COUNT(DISTINCT p.poster_id) AS count, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
				WHERE p.forum_id = f.forum_id 
					AND p.post_approved = 1' . $forum_sql . '
					AND f.forum_type = ' . FORUM_POST . '
				GROUP BY f.forum_id, f.forum_name
				ORDER BY count ' . $order;
			
		break;
		
		case 'subscriptions':
			// top forums by subscriptions; added by Marc Alexander
			$sql = 'SELECT COUNT(t.topic_id) AS count, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . TOPICS_WATCH_TABLE . ' tw, ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 
				WHERE tw.topic_id = t.topic_id
					AND t.forum_id = f.forum_id' . $forum_sql . '
					AND f.forum_type = ' . FORUM_POST . '
				GROUP BY f.forum_id, f.forum_name
				ORDER BY count ' . $order;
			
		break;
		
		default:
	}
	
	$result = $db->sql_query_limit($sql, $limit_count);
	while ($temp_row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $temp_row;
	}			
	$db->sql_freeresult($result);

	
	return $return_ary;
}

/*function make_select_box --- creates a select box
@param $options --- array containing options in ($key => $value) ~ ($option => $option_lang) 
@param $selected --- the selected option
@param $select_identifier --- the name for the <select>
@param $label_prompt --- the label to be shown for the select box
@param $submit_prompt --- the text shown for the submit button
@param $action_url --- the url for the action attribute of form
*/
function make_select_box($options, $selected, $select_identifier, $label_prompt, $submit_prompt = 'submit', $action_url = '')
{
	$return_str = $temp_str = '';
	
	foreach ($options as $option => $option_lang)
	{
		if ($option != $selected)
		{
			$temp_str .= '<option value="' . $option . "\">$option_lang</option>";
		}
		else {
			$temp_str .= '<option value="' . $option . '" selected="selected">' . $option_lang . '</option>';
		}
	}
	
	$submit_prompt = ucfirst($submit_prompt);
	
	if (isset($options))
	{
		$return_str = '<label for="' . $select_identifier . '">' . $label_prompt . ': </label><select name="' . $select_identifier . '" id="' . $select_identifier . '">' . $temp_str . '</select> <input class="button2" type="submit" value="' . $submit_prompt . '" />';
	}
	
	return $return_str;
}

/*function get_top_topics ---- returns the top $limit_count number of topics based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_topics($limit_count = 10, $criteria = 'posts', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary) > 0)
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}	
	
	switch ($criteria)
	{
		case 'posts':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, (t.topic_replies_real + 1) AS count, t.topic_id AS t_id, t.topic_title AS t_title 
						FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 						
						WHERE t.forum_id = f.forum_id
							AND t.topic_approved = 1' . $forum_sql . '
							AND t.topic_status <> ' . ITEM_MOVED . '
						GROUP BY t.topic_id, f.forum_name, f.forum_id, t.topic_title, (t.topic_replies_real + 1)
						ORDER BY count ' . $order;					
		break;
		
		case 'views':
			$sql = 'SELECT f.forum_id AS f_id, f.forum_name AS f_name, t.topic_views AS count, t.topic_id AS t_id, t.topic_title AS t_title 
						FROM ' . FORUMS_TABLE . ' f, ' . TOPICS_TABLE . ' t 						
						WHERE t.forum_id = f.forum_id
							AND t.topic_approved = 1' . $forum_sql . '	
							AND t.topic_status <> ' . ITEM_MOVED . '							
						GROUP BY t.topic_id, f.forum_name, f.forum_id, t.topic_title, t.topic_views					
						ORDER BY count ' . $order;		
		break;
		
		case 'participation':
			$sql = 'SELECT COUNT(DISTINCT p.poster_id) AS count, t.topic_id as t_id, t.topic_title as t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE p.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND p.post_approved = 1' . $forum_sql . '
					AND t.topic_status <> ' . ITEM_MOVED . '
				GROUP BY t.topic_id, f.forum_id, f.forum_name, t.topic_title
				ORDER BY count ' . $order;			
		break;
		
		case 'attachments':
			$sql = 'SELECT COUNT(at.attach_id) AS count, at.topic_id as t_id, t.topic_title as t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . ATTACHMENTS_TABLE . ' at, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE at.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND t.topic_approved = 1' . $forum_sql . '
					AND t.topic_status <> ' . ITEM_MOVED . '
				GROUP BY t.topic_id, at.topic_id, f.forum_id, f.forum_name, t.topic_title
				ORDER BY count ' . $order;		
		break;
		
		case 'bookmarks':
			//	top topics by bookmarks
			$sql = 'SELECT COUNT(b.topic_id) AS count, t.topic_id AS topic_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . BOOKMARKS_TABLE . ' b, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE b.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND t.topic_approved = 1' . $forum_sql . '
					AND t.topic_status <> ' . ITEM_MOVED . '
				GROUP BY t.topic_id, f.forum_id, f.forum_name, t.topic_title
				ORDER BY count ' . $order;			
		break;
		
		case 'subscriptions':
			//	top topics by subscriptions
			$sql = 'SELECT COUNT(tw.topic_id) AS count, tw.topic_id AS topic_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name 
				FROM ' . TOPICS_WATCH_TABLE . ' tw, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f 
				WHERE tw.topic_id = t.topic_id
					AND t.forum_id = f.forum_id
					AND t.topic_approved = 1' . $forum_sql . '
					AND t.topic_status <> ' . ITEM_MOVED . '
				GROUP BY tw.topic_id, f.forum_id, f.forum_name, t.topic_title
				ORDER BY count ' . $order;		
		break;
		
		default:
	}
	$result = $db->sql_query_limit($sql, $limit_count);
	while ($temp_row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $temp_row;
	}			
	$db->sql_freeresult($result);
	
	return $return_ary;
}

/*function get_top_users ---- returns the top $limit_count number of users based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_users($limit_count = 10, $criteria = 'posts', $order = 'DESC', $recent_posts_limit_days = 10)
{
	global $db, $stats_config;
	$return_ary = array();
	
	if(!isset($stats_config))
	{
		$stats_config = obtain_stats_config();
	}
	
	$hide = ($stats_config['activity_users_hide_anonymous'] == true) ? '' : 'AND user_id <> ' . ANONYMOUS;
	
	switch ($criteria)
	{
		case 'posts':
			$sql = 'SELECT user_id AS u_id, username, user_colour AS u_colour, user_posts AS count
						FROM ' . USERS_TABLE . '
						WHERE user_posts > 1 ' . $hide . '
						GROUP BY user_id, username, user_colour, user_posts
						ORDER BY user_posts ' . $order;
		break;
		
		case 'topics':
			$sql = 'SELECT u.user_id AS u_id, u.username AS username, u.user_colour AS u_colour, COUNT(t.topic_id) AS count
						FROM ' . TOPICS_TABLE. ' t, ' . USERS_TABLE . ' u 
						WHERE t.topic_approved = 1 
							AND t.topic_poster = u.user_id ' . $hide . '
						GROUP BY u.user_id, u.username, u.user_colour
						ORDER BY count ' . $order;
		break;
		
		case 'recent_posts':
			$sql = 'SELECT p.poster_id AS u_id, u.username AS username, u.user_colour AS u_colour, COUNT(p.post_id) AS count
						FROM ' . POSTS_TABLE. ' p, ' . USERS_TABLE . ' u 
						WHERE p.post_approved = 1 
							AND p.poster_id = u.user_id ' . $hide . '
							AND p.post_time > ' . (time() - $recent_posts_limit_days * 86400) . ' 
						GROUP BY p.poster_id, u.username, u.user_colour
						ORDER BY count ' . $order;
		break;
		
		case 'attachments':
			$sql = 'SELECT u.user_id AS u_id, u.username, u.user_colour AS u_colour, COUNT(at.attach_id) AS count
						FROM ' . ATTACHMENTS_TABLE . ' at, ' . USERS_TABLE . ' u 
						WHERE at.poster_id = u.user_id ' . $hide . '
						GROUP BY u.user_id, u.username, u.user_colour
						ORDER BY count ' . $order;
		break;
		
		default:
	}
	
	$result = $db->sql_query_limit($sql, $limit_count);
	while ($current_user = $db->sql_fetchrow($result))
	{
		$return_ary[] = $current_user;
	}
	$db->sql_freeresult($result);
	
	return $return_ary;
}

/*
* function get_total_members
* this function will retrieve the total number of members from the database
* for accuracy, please do not cache this function
* Copyright (c) 2010 Marc Alexander(marc1706) www.m-a-styles.de
*/
function get_total_members()
{
    global $db;

    $return = 0;

    $sql = 'SELECT (MAX(user_id) - (MAX(user_id) - COUNT(user_id))) AS total
                FROM phpbb_users
                WHERE ' .  $db->sql_in_set('user_type', USER_INACTIVE, true);
    $result = $db->sql_query($sql);
    $total= $db->sql_fetchfield('total') - 1; // subtract 1 for ANONYMOUS
    $db->sql_freeresult($result);
    return $total;
}


/*
* function get_group_members --- gets the total member count and the group membership counts.
* @param $groups_data --- contains data about the groups
*/
function get_group_members($groups_data)
{
	global $db;
        
	$member_counts = array();
	
	//populate the $return_ary with basic group_ids
	foreach ($groups_data as $group_row)
	{		
		$member_counts[$group_row['group_id']] = array();		
	}
	
	//get all the users and increment the group counter	
	$sql = 'SELECT COUNT(ug.user_id) AS count, ug.group_id AS g_id, g.group_name AS g_name 
				FROM ' . USER_GROUP_TABLE . ' ug, ' . GROUPS_TABLE . ' g, ' . USERS_TABLE . ' u
				WHERE ug.group_id = g.group_id
					AND ug.user_id <> ' . ANONYMOUS . '
                                        AND u.user_id = ug.user_id
                                        AND u.user_type <> ' . USER_INACTIVE . '
				GROUP BY ug.group_id, g.group_name';
	$result = $db->sql_query($sql);
	while ($current_group = $db->sql_fetchrow($result))
	{
		$member_counts[$current_group['g_id']] = $current_group['count'];		
	}
	$db->sql_freeresult($result);
		
	return $member_counts;
}

/*function get_online_data --- returns the groupwise count of users online
@param $userss_ary --- array containing the online users data
@param $total_count --- the total count of users online
@param $total_hidden --- the total count of hidden users
*/
function get_online_data($groups_data, &$total_online, &$total_hidden, &$total_guests)
{
	global $db, $config;
	$total_online = $total_hidden = 0;
	$online_data = array();
	
	//create the array structure for $online_data grouped by groups
	foreach ($groups_data as $current_group)
	{
		$online_data[$current_group['group_id']] = array();		
	}
	
	$sql = 'SELECT s.session_user_id AS user_id, u.username AS username, u.user_colour AS user_colour, g.group_id AS group_id, g.group_name AS group_name, g.group_colour AS group_colour, s.session_viewonline AS viewonline 
			FROM ' . SESSIONS_TABLE . ' s, ' . USERS_TABLE . ' u, ' . GROUPS_TABLE . ' g 
			WHERE s.session_user_id = u.user_id 
				AND s.session_time >= ' . (time() - $config['load_online_time'] * 60) . ' 
				AND u.group_id = g.group_id
			ORDER BY u.username_clean ASC';
			
	$result = $db->sql_query($sql);
	while ($user_data = $db->sql_fetchrow($result))
	{
		++$total_online;
		if ($user_data['user_id'] != ANONYMOUS) //we won't add users to the online data if ANONYMOUS... we'll simply show the total guest count retrieved earlier with another function
		{
			if (($user_data['viewonline']) == true)
			{
				
				$online_data[$user_data['group_id']][] = $user_data;
			}
			else
			{
				++$total_hidden;
			}
		}
		else
		{
			++$total_guests;
			$online_data[$user_data['group_id']][] = $total_guests;			
		}
	}
	return $online_data;
}

//function get_groups_data --- returns data about all the groups in the database
function get_groups_data()
{
	global $db, $auth;
	$return_ary = array();
	
	// Decide what we show the user
	if($auth->acl_gets('a_group', 'a_groupadd', 'a_groupdel'))
	{
		$sql_where = '';
	}
	else
	{
		$sql_where = 'WHERE group_legend = 1
						OR ' . $db->sql_in_set('group_name', array('BOTS', 'REGISTERED', 'GUESTS'));
	}
	
	$sql = 'SELECT * FROM ' . GROUPS_TABLE . '
				' . $sql_where . '
				ORDER BY group_name ASC';
	$result = $db->sql_query($sql);
	while ($group_row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $group_row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

//function get_num_recent_posts --- returns the number of recent posts from last $limit_days days
function get_num_recent_posts($limit_days = 10)
{
	global $db;	
	$sql = 'SELECT COUNT(post_id) AS count FROM ' . POSTS_TABLE . ' 
				WHERE post_time > ' . (time() - ((int) $limit_days * 86400));
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

//function get_orphan_attachments_count --- returns the number of orphan attachments
function get_orphan_attachments_count()
{
	global $db;	
	$sql = 'SELECT COUNT(attach_id) AS count
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE is_orphan = 1
					AND filetime < ' . (time() - 3*60*60);
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('count');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_top_attachments ---- returns the top $limit_count number of attachments based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_attachments($limit_count = 10, $criteria = 'posts', $order = 'DESC', $no_forum_ary = array())
{
	global $db, $cache;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'recent':
			$return_ary = $cache->get('top_attachments_by_recent_' . $limit_count);
			if(!isset($return_ary[0]['attach_id']) || select_cache_refresh('top_attachments_by_recent_' . $limit_count))
			{
				$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime
							FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
							WHERE at.is_orphan = 0							
								AND at.post_msg_id = p.post_id
								AND p.forum_id = f.forum_id' . $forum_sql . '
							ORDER BY filetime ' . $order;
				$result = $db->sql_query_limit($sql, $limit_count);
				while ($current_attach = $db->sql_fetchrow($result))
				{
					$return_ary[] = $current_attach;
				}
				$db->sql_freeresult($result);
				$cached_return_ary = $return_ary;
				$cache->put('top_attachments_by_recent_' . $limit_count, $cached_return_ary);
				set_cache_refresh('top_attachments_by_recent_' . $limit_count);
			}
		break;
		
		case 'filetype':
			$return_ary = $cache->get('top_attachments_by_filetype_' . $limit_count);
			if(!isset($return_ary[0]['count']) || select_cache_refresh('top_attachments_by_filetype_' . $limit_count))
			{
				$sql = 'SELECT COUNT(attach_id) AS count, extension, mimetype 
							FROM ' . ATTACHMENTS_TABLE . ' 
							WHERE is_orphan = 0
							GROUP BY extension, mimetype
							ORDER BY count ' . $order;
				$result = $db->sql_query_limit($sql, $limit_count);
				while ($current_attach = $db->sql_fetchrow($result))
				{
					$return_ary[] = $current_attach;
				}
				$db->sql_freeresult($result);
				$cached_return_ary = $return_ary;
				$cache->put('top_attachments_by_filetype_' . $limit_count, $cached_return_ary);
				set_cache_refresh('top_attachments_by_filetype_' . $limit_count);
			}
		break;
		
		case 'filesize':
			$return_ary = $cache->get('top_attachments_by_filesize_' . $limit_count);
			if(!isset($return_ary[0]['filesize']) || select_cache_refresh('top_attachments_by_filesize_' . $limit_count))
			{
				$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime
							FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
							WHERE at.is_orphan = 0							
								AND at.post_msg_id = p.post_id
								AND p.forum_id = f.forum_id' . $forum_sql . '
							ORDER BY filesize ' . $order;
				$result = $db->sql_query_limit($sql, $limit_count);
				while ($current_attach = $db->sql_fetchrow($result))
				{
					$return_ary[] = $current_attach;
				}
				$db->sql_freeresult($result);
				$cached_return_ary = $return_ary;
				$cache->put('top_attachments_by_filesize_' . $limit_count, $cached_return_ary);
				set_cache_refresh('top_attachments_by_filesize_' . $limit_count);
			}
		break;
		
		case 'download':
			$return_ary = $cache->get('top_attachments_by_download_' . $limit_count);
			if(!isset($return_ary[0]['count']) || select_cache_refresh('top_attachments_by_filetype_' . $limit_count))
			{
				$sql = 'SELECT at.attach_id AS attach_id, at.post_msg_id AS p_id, p.topic_id AS t_id, p.post_subject AS p_subject, f.forum_id AS f_id, f.forum_name AS f_name, at.filesize AS filesize, at.real_filename AS filename, at.filetime AS filetime, at.download_count AS count
							FROM ' . ATTACHMENTS_TABLE . ' at, ' . POSTS_TABLE . ' p, ' . FORUMS_TABLE . ' f 
							WHERE at.is_orphan = 0							
								AND at.post_msg_id = p.post_id
								AND p.forum_id = f.forum_id' . $forum_sql . '
							ORDER BY count ' . $order;
				$result = $db->sql_query_limit($sql, $limit_count);
				while ($current_attach = $db->sql_fetchrow($result))
				{
					$return_ary[] = $current_attach;
				}
				$db->sql_freeresult($result);
				$cached_return_ary = $return_ary;
				$cache->put('top_attachments_by_download_' . $limit_count, $cached_return_ary);
				set_cache_refresh('top_attachments_by_download_' . $limit_count);
			}
		break;
		
		default:
	}
	return $return_ary;
}

//function get_total_attach_downloads --- returns the total download count of all attachments
function get_total_attach_downloads($option = '')
{
	global $db;
	switch ($option)
	{
		case 'total_size':
			$sql = 'SELECT SUM(download_count * filesize) AS count FROM ' . ATTACHMENTS_TABLE;
			$result = $db->sql_query($sql);
			$count = $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
			return $count;
		break;
		
		case 'total_count':
		default:
			$sql = 'SELECT SUM(download_count) AS count FROM ' . ATTACHMENTS_TABLE;
			$result = $db->sql_query($sql);
			$count = $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
			return $count;
	}
}

//function get_orphan_attachments_size --- returns the total size of all orphan attachments
function get_orphan_attachments_size()
{
	global $db;
	$sql = 'SELECT SUM(filesize) AS total_size 
				FROM ' . ATTACHMENTS_TABLE . '
				WHERE is_orphan = 1';
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('total_size');
	$db->sql_freeresult($result);
	return $count;
}

/*function get_top_polls ---- returns the top $limit_count number of polls based on and sorted by the given criteria
@param $limit_count : the maximum top records to be retrieved
@param $criteria : the criteria to sort (like posts etc)
@param $order : the sort order ('ASC' or 'DESC')
*/
function get_top_polls($limit_count = 10, $criteria = 'votes', $order = 'DESC', $no_forum_ary = array())
{
	global $db;
	$return_ary = array();
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($criteria)
	{
		case 'votes':
			$sql = 'SELECT COUNT(po.poll_option_id) AS count, po.topic_id AS t_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name, t.poll_title AS poll_title
					FROM ' . POLL_VOTES_TABLE . ' po, ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
					WHERE po.topic_id = t.topic_id
						AND t.forum_id = f.forum_id
						AND t.topic_status <> ' . ITEM_MOVED . $forum_sql . '
					GROUP BY t_id, t_title, poll_title, f_id, f_name
					ORDER BY count ' . $order;
		break;
		
		case 'recent':
			$sql = 'SELECT t.topic_id AS t_id, t.topic_title AS t_title, f.forum_id AS f_id, f.forum_name AS f_name, t.poll_title AS poll_title, t.poll_start AS poll_start
					FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
					WHERE t.poll_start > 0
						AND t.forum_id = f.forum_id
						AND t.topic_status <> ' . ITEM_MOVED . $forum_sql . '
					GROUP BY t_id, t_title, poll_title, poll_start, f_id, f_name
					ORDER BY poll_start ' . $order;
		break;
		
		default:
	}
	
	$result = $db->sql_query_limit($sql, $limit_count);
	while ($current_poll = $db->sql_fetchrow($result))
	{
		$return_ary[] = $current_poll;
	}
	
	return $return_ary;
}

/* function get_poll_options --- returns the poll options for the given topics
@param topic_ids : array containing the topic ids
@return value : array([topic_id_1] => array(option_1_text, option_2_text...), ...)
*/
function get_poll_options($topic_ids)
{
	global $db;
	$return_ary = array();
	if (!$topic_ids)
	{
		return;
	}
	//setup the return array
	foreach ($topic_ids as $topic_id)
	{
		$return_ary[] = array($topic_id => array());
	}
	//get the poll option texts
	$sql = 'SELECT po.poll_option_text AS poll_option_text, po.poll_option_id AS poll_option_id, po.topic_id AS topic_id, p.bbcode_uid AS bbcode_uid, p.bbcode_bitfield AS bbcode_bitfield
				FROM ' . POLL_OPTIONS_TABLE . ' po, ' . POSTS_TABLE . ' p, ' . TOPICS_TABLE . ' t
				WHERE ' . $db->sql_in_set('po.topic_id', array_map('intval', $topic_ids)) . '
					AND po.topic_ID = t.topic_id
					AND t.topic_first_post_id = p.post_id';				
	$result = $db->sql_query($sql);
	while ($current_option = $db->sql_fetchrow($result))
	{
		$current_text = '';
		$current_text = generate_text_for_display($current_option['poll_option_text'], $current_option['bbcode_uid'], $current_option['bbcode_bitfield'], 7);
		$return_ary[$current_option['topic_id']][] = '(' . $current_option['poll_option_id'] . ') ' . $current_text;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}


/*	function get_users_ranks_count --- returns the count of users belonging to non-special ranks
*	has been shortened to only the parts that are needed
*/
function get_users_ranks_count()
{
	global $db;
	$return_ary = array();
	$sql = 'SELECT user_posts
				FROM ' . USERS_TABLE . '
				WHERE user_type = ' . USER_NORMAL;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

/*function get_custom_profile_fields --- gets the custom profile field of certain types, and which are active and viewable
returns an array containing each profile field
*/
function get_custom_profile_fields()
{
	global $db, $user;
	$return_ary = array();
	$select_field_types = array(FIELD_STRING, FIELD_BOOL, FIELD_DROPDOWN); //only these types will be selected
	//get all the fields and its language data from 2 custom profile fields tables comparing lang with the user lang
	$sql = 'SELECT pf.*, pl.*
		FROM ' . PROFILE_FIELDS_TABLE . ' pf, ' . PROFILE_LANG_TABLE . ' pl, ' . LANG_TABLE . ' l 
		WHERE ' . $db->sql_in_set('pf.field_type', $select_field_types) . "
			AND (pf.field_active = 1 AND pf.field_hide = 0 AND pf.field_no_view = 0 AND pf.field_stats_show = 1) 
			AND pf.field_id = pl.field_id
			AND pl.lang_id = l.lang_id
			AND l.lang_iso = '" . $user->data['user_lang'] . "'
			ORDER BY field_order ASC";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$return_ary[] = $row;
	}
	$db->sql_freeresult($result);
	return $return_ary;
}

/*function get_profile_field_data --- gets the data for a given custom profile field, The result varies depending on the field type. However the return value is always an array
@param : $profile_field : an array containing the data for the profile field whose data is to be retrieved
@param : $total_values_set : the total values that have been set
@param : $limit_count : the number of top entries to be returned
@param : $total_groups : the number of groups that have been retrieved
*/
function get_profile_field_data($profile_field, &$total_values_set, &$total_groups, $limit_count = 0)
{
	global $db, $user;
	$return_ary = array();
	$field_identifier = 'pf_' . $db->sql_escape($profile_field['field_ident']);
	
	switch ($profile_field['field_type'])
	{
		case FIELD_STRING:
			//if this is a single line string, we have to treat this like a city or country name... here we have to get the results in groups after trim and lower
			$sql = 'SELECT COUNT(user_id) AS count, TRIM(LOWER(' . $field_identifier . ')) AS value
				FROM ' . PROFILE_FIELDS_DATA_TABLE . '
				WHERE ' . $field_identifier . " <> '' 
				GROUP BY value
				ORDER BY count DESC";
			$result = $db->sql_query($sql); //don't limit here as we need the total count too!
			while ($row = $db->sql_fetchrow($result))
			{
				$return_ary[] = $row;
				$total_values_set += $row['count'];
				++$total_groups;
			}
			$db->sql_freeresult($result);
			
			if (isset($limit_count))
			{
				$return_ary = array_slice($return_ary, 0, $limit_count);
			}
		
		break;
		
		case FIELD_BOOL:
		case FIELD_DROPDOWN:	//we treat both as same as they have options
			//here we cannot use count directly as it doesn't return the zero values. So we have to get all the data and later count them
			$exclude_option_id = ($profile_field['field_type'] == FIELD_DROPDOWN) ? $profile_field['field_novalue'] - 1 : ''; //this value has to be excluded as it means an unselected value			
			//get the options first... we do this since we are not sure they would be retrieved by going through the data table in case no one selected this option
			
			
			$sql = 'SELECT pfl.lang_value, pfl.option_id 
				FROM ' . PROFILE_FIELDS_LANG_TABLE . ' pfl, ' . LANG_TABLE . ' l 
				WHERE pfl.field_id = ' . $profile_field['field_id'] . 
				(($exclude_option_id != '') ? ' AND pfl.option_id <> ' . (int) $exclude_option_id : '') . "
					AND pfl.lang_id = l.lang_id
					AND l.lang_iso = '" . $user->data['user_lang'] . "'					
				ORDER BY pfl.option_id ASC";
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{				
				$options[$row['option_id'] + 1]['lang_value'] = $row['lang_value'];
				$options[$row['option_id'] + 1]['count'] = 0;
				$options[$row['option_id'] + 1]['option_id'] = $row['option_id'] + 1;
			}
			$db->sql_freeresult($result);
			
			//now get the counts
			$sql = 'SELECT COUNT(user_id) AS count, ' . $field_identifier . '
				FROM ' . PROFILE_FIELDS_DATA_TABLE . '
				WHERE ' . $field_identifier . ' <> 0 ' . 
				(($exclude_option_id != '') ? ' AND ' . $field_identifier . ' <> ' . (int) $exclude_option_id : '') . '
				GROUP BY ' . $field_identifier . '
				ORDER BY count DESC';
			
			$result = $db->sql_query($sql); //don't limit count here as we need the total too!
			while ($row = $db->sql_fetchrow($result))
			{				
				$options[$row[$field_identifier]]['count'] = $row['count']; //here $field_identifier is actually (option_id + 1)
				$total_values_set += $row['count'];
				++$total_groups;
			}
			$db->sql_freeresult($result);		
			
			if ($profile_field['field_type'] == FIELD_DROPDOWN)
			{				
				//only for dropdown... if we have to get only the top x results, remove the unwanted results				
			
				//we have to use multisort... for this purpose we have to split our temp_return_ary into two arrays for lang_value and count
				$lang_value_ary = $counts_ary = $option_ids_ary = array();
				foreach ($options as $option_id => $data)
				{
					$lang_value_ary[] = $data['lang_value'];
					$counts_ary[] = $data['count'];
					$option_ids_ary[] = $option_id;
				}			
				//now use multisort
				array_multisort($counts_ary, SORT_DESC, $option_ids_ary, SORT_ASC, $lang_value_ary, SORT_ASC);
				//now populate the temp_return_ary with the new values, break when we find a zero count, or reaches the limit
				$temp_return_ary = array();				
				for ($i = 0; isset($lang_value_ary[$i]); ++$i)
				{
					if ($counts_ary[$i] == 0 || ($limit_count && $i == $limit_count))
					{
						break;
					}
					$temp_return_ary[$i] = array(
						'lang_value' 	=> $lang_value_ary[$i],
						'count'			=> $counts_ary[$i],
						'option_id'		=> $option_ids_ary[$i],
					);
				}
			}
			
			if ($profile_field['field_type'] == FIELD_BOOL)
			{
				$return_ary = $options;
			}
			else // for dropdown only
			{
				$return_ary = $temp_return_ary;
			}
			unset($options);			
		break;		
		
		default:
	}
	
	return $return_ary;
}

/* 	get the number of smilies installed on the board 
*	by Marc Alexander
*/
function get_smiley_count($type = '')
{
	global $db, $user;
	$count = 0;
	$dop = 0;		// display on posting
	
	$sql = 'SELECT DISTINCT(smiley_url) AS count, display_on_posting AS dop
			FROM ' . SMILIES_TABLE;
	$result = $db->sql_query($sql);
	while($row = $db->sql_fetchrow($result))
	{
		++$count;
		if($row['dop'])
		{
			++$dop;
		}
	}
	$db->sql_freeresult($result);
	$count = array('total' => $count, 'dop' => $dop);
	
	return $count;
}

/**	
* get top smilies
* now totally reworked
* type: total, default: top xx
* Copyright (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
*/
function get_top_smilies($limit_count = 10, $type = '')
{
	global $db;
	
	switch ($type)
	{
		case 'total':
			$count = 0;
			
			$sql = 'SELECT SUM(smiley_count) AS count
					FROM ' . STATS_SMILIES_TABLE;
			$result = $db->sql_query($sql);
			$count = (int) $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
		break;
		default:
			$count = array();
			
			$sql = 'SELECT ss.smiley_count AS count, ss.smiley_url AS url, s.emotion AS emotion
					FROM ' . STATS_SMILIES_TABLE . ' ss, ' . SMILIES_TABLE . ' s
					WHERE ss.smiley_url = s.smiley_url
					GROUP BY ss.smiley_url, s.emotion, ss.smiley_count
					ORDER BY count DESC';
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$count[] = $temp_row;
			}			
			$db->sql_freeresult($result);
	}
	
	return $count;

}

/*	get the number of warnings and distinct users warned
*	by Marc Alexander
*/
function get_warning_count($type = '')
{
	global $db, $user;
	$count = 0;
	
	switch ($type)
	{
		case 'own_warnings':
			$sql = 'SELECT COUNT(DISTINCT warning_id) AS warning_count 
					FROM ' . WARNINGS_TABLE . ' 
					WHERE user_id = ' . $user->data['user_id'];
		break;
		default:
			$sql = 'SELECT COUNT(DISTINCT warning_id) AS warning_count
					FROM ' . WARNINGS_TABLE;
	}
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('warning_count');
	$db->sql_freeresult($result);
	
	return $count;
}

/**	
* get top bbcodes
* now totally reworked
* type: total, default: top xx
* Copyright (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
*/
function get_top_bbcodes($limit_count = 10, $type = '')
{
	global $db;
	
	switch ($type)
	{
		case 'total':
			$count = 0;
			
			$sql = 'SELECT SUM(bbcode_count) AS count
					FROM ' . STATS_BBCODES_TABLE;
			$result = $db->sql_query($sql);
			$count = (int) $db->sql_fetchfield('count');
			$db->sql_freeresult($result);
		break;
		default:
			$count = array();
			
			$sql = 'SELECT bbcode, bbcode_count FROM ' . STATS_BBCODES_TABLE . '
					GROUP BY bbcode, bbcode_count
					ORDER BY bbcode_count DESC';
			$result = $db->sql_query_limit($sql, $limit_count);
			
			//	Make the BBCodes look nicely
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$test_result = preg_replace('/[^a-zA-Z0-9\s]/', '', $temp_row['bbcode']);
				$test_result2 = strpos($temp_row['bbcode'], '/');
				$test_result3 = strpos($temp_row['bbcode'], ':', (strlen($temp_row['bbcode']) - 1));
				if($test_result2 == 1)
				{
					$temp_row['bbcode'] = '[' . (($test_result3 > 0) ? substr($temp_row['bbcode'], 2, -1) : substr($temp_row['bbcode'], 2)) . ']' . substr($temp_row['bbcode'], 0, -1) . ']';
				}
				elseif(!empty($test_result))
				{
					$temp_row['bbcode'] = '[' . (($test_result3 > 0) ? substr($temp_row['bbcode'], 1, -1) : substr($temp_row['bbcode'], 1)) . '][/' . preg_replace('/[^a-zA-Z0-9\s]/', '', $temp_row['bbcode']) . ']';
				}
				else
				{
					$temp_row['bbcode'] = '[' . (substr($temp_row['bbcode'], 1)) . '][/' . (substr($temp_row['bbcode'], 1)) . ']';
				}
				$count[] = $temp_row;
			}			
			$db->sql_freeresult($result);
	}
	
	return $count;
}


/*	get bbcode count
*	by Marc Alexander	
*/
function get_bbcode_count()
{
	global $db, $cache;
	
	$bbcode_start_count = 13;	//	This needs to be changed if phpBB starts to ship with more BBCodes
	$custom_count = 0;
	$count = 0;
	
	$custom_count = $cache->get('bbcode_custom_count');
	if(!isset($custom_count) || $custom_count == 1 || select_cache_refresh('bbcode_custom_count'))
	{
		$sql = 'SELECT COUNT(bbcode_id) AS count 
				FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);
		$custom_count = (int) $db->sql_fetchfield('count');
		$db->sql_freeresult($result);
		$cached_custom_count = $custom_count;
		$cache->put('bbcode_custom_count', $cached_custom_count);
		set_cache_refresh('bbcode_custom_count');
	}
	$count = $custom_count + $bbcode_start_count;

	return array('custom' => $custom_count, 'total' => $count);
}




/*	get the page views
* 	either total count, for each forum or for each topic
*	by Marc Alexander
*	I dedicate this function to Andy. Hopefully you won't create one that looks exactly the same as this one ;)
*/
function get_page_views_count($limit_count = 10, $type = '', $order = 'DESC', $no_forum_ary = array())
{
	global $db, $cache;
	
	$forum_sql = '';
	if (sizeof($no_forum_ary))
	{
		$forum_sql = ' AND ' . $db->sql_in_set('f.forum_id', $no_forum_ary, true);
	}
	
	switch ($type)
	{
		case 'forums':
			$forum_views_ary = array();
			$sql = 'SELECT SUM(t.topic_views) AS count, t.forum_id AS forum_id, f.forum_name AS forum_name
					FROM ' . TOPICS_TABLE . ' t, ' . FORUMS_TABLE . ' f
					WHERE t.forum_id = f.forum_id
						AND t.topic_approved = 1' . $forum_sql . '
					GROUP BY t.forum_id, forum_name
					ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$forum_views_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			return $forum_views_ary;
		break;
	
		default:
			$total_views = $cache->get('total_views');
			if(!isset($total_views) || $total_views < 1 || select_cache_refresh('total_views'))
			{
				$cached_total_views = 0;
				$total_views = 0;
				$sql = 'SELECT SUM(topic_views) AS count
						FROM ' . TOPICS_TABLE . '
						WHERE topic_approved = 1';
				$result = $db->sql_query($sql);
				$total_views = (int) $db->sql_fetchfield('count');
				$cached_total_views = $total_views;
				$cache->put('total_views', $cached_total_views);
				set_cache_refresh('total_views');
			}
			return $total_views;
	}
}

/* 	get the top XX ignored users (foes) or friends
*	by Marc Alexander
*	I dedicate this function to Andy. Hopefully you won't create one that looks exactly the same as this one ;)
*/
function get_zebra_info($limit_count = 10, $type = 'foes', $order = 'DESC')
{
	global $db;
	$zebra_ary = array();
	$zebra_count = 0;
	
	switch ($type)
	{
		case 'foes':
			$sql = 'SELECT COUNT(z.zebra_id) AS count, u.user_id AS user_id, u.username AS username, u.user_colour AS user_colour
					FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
					WHERE z.zebra_id = u.user_id
						AND z.foe = 1
					GROUP BY u.user_id, username, user_colour
					ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$zebra_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			return $zebra_ary;
		break;
		
		case 'friends':
			$sql = 'SELECT COUNT(z.zebra_id) AS count, u.user_id AS user_id, u.username AS username, u.user_colour AS user_colour
					FROM ' . ZEBRA_TABLE . ' z, ' . USERS_TABLE . ' u
					WHERE z.zebra_id = u.user_id
						AND z.friend = 1
					GROUP BY u.user_id, username, user_colour
					ORDER BY count ' . $order;
			$result = $db->sql_query_limit($sql, $limit_count);
			while ($temp_row = $db->sql_fetchrow($result))
			{
				$zebra_ary[] = $temp_row;
			}			
			$db->sql_freeresult($result);
			return $zebra_ary;
		break;
	}
	
}

/* 	get the ignored users (foes) or friends count
*	by Marc Alexander
*	I dedicate this function to Andy. Hopefully you won't create one that looks exactly the same as this one ;)
*/
function get_zebra_count($type = '')
{
	global $db;
	$zebra_count = 0;
	
	switch ($type)
	{
		case 'foes_count':
			$sql = 'SELECT COUNT(zebra_id) AS count
				FROM ' . ZEBRA_TABLE . '
				WHERE foe = 1';
			$result = $db->sql_query($sql);
			$zebra_count = (int) $db->sql_fetchfield('count');
			return $zebra_count;
		break;
		
		default:
			$sql = 'SELECT COUNT(zebra_id) AS count
				FROM ' . ZEBRA_TABLE . '
				WHERE friend = 1';
			$result = $db->sql_query($sql);
			$zebra_count = (int) $db->sql_fetchfield('count');
			return $zebra_count;
	}
	
}

/*	find out how many users have been deleted
*	by Marc Alexander
*/
function get_deleted_users_count()
{
	global $db, $cache;
	$count = 0;
	
	$count = $cache->get('deleted_users_count');
	
	if(!isset($count) || $count < 1 || select_cache_refresh('deleted_users_count'))
	{
		$sql = 'SELECT MAX(user_id) - COUNT(user_id) AS count FROM ' . USERS_TABLE;
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('count');
		$db->sql_freeresult($result);
		$cached_count = $count;
		$cache->put('deleted_users_count', $cached_count);
		set_cache_refresh('deleted_users_count');
	}
	return $count;
	
	
}

/*	get the top used icons
*	by Marc Alexander
*/
function get_top_icons_count($limit_count = 10, $type = 'each', $order = 'DESC')
{
	global $db, $cache;
	
	switch ($type)
	{
		case 'each':
			$return_ary = $cache->get('topic_icons_ary_' . $limit_count);
			if(!isset($return_ary[0]['count']) || $return_ary[0]['count'] < 1 || select_cache_refresh('topic_icons_ary_' . $limit_count))
			{
				$cached_return = array();
				$return_ary = array();
				$sql = 'SELECT COUNT(t.topic_id) AS count, t.icon_id AS icon_id, i.icons_url AS icon_url
						FROM ' . TOPICS_TABLE . ' t, ' . ICONS_TABLE . ' i
						WHERE icon_id = i.icons_id
							AND i.display_on_posting = 1
							AND t.topic_approved = 1
						GROUP BY icon_id
						ORDER BY count ' . $order;
				$result = $db->sql_query_limit($sql, $limit_count);
				while ($temp_row = $db->sql_fetchrow($result))
				{
					$return_ary[] = $temp_row;
				}			
				$db->sql_freeresult($result);
				$cached_return = $return_ary;
				$cache->put('topic_icons_ary_' . $limit_count, $cached_return);
				set_cache_refresh('topic_icons_ary_' . $limit_count);
			}
				
			return $return_ary;
		break;
		case 'total':
			$return_ary = 0;
			$sql = 'SELECT COUNT(topic_id) AS count
					FROM ' . TOPICS_TABLE . '
					WHERE icon_id <> 0 
						AND topic_approved = 1';
			$result = $db->sql_query($sql);
			$return_ary = (int) $db->sql_fetchfield('count');	
			return $return_ary;			
		break;
		default:
	}	
		
	
}

/*	get the bookmarks count
*	by Marc Alexander
*/
function get_bookmarks_count()
{
	global $db, $cache;
	$count = 0;
	
	$count = $cache->get('bookmarks_count');
	
	if(!isset($count) || $count < 1 || select_cache_refresh('bookmarks_count'))
	{
		$sql = 'SELECT COUNT(topic_id) AS count FROM ' . BOOKMARKS_TABLE;
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('count');
		$db->sql_freeresult($result);
		$cached_count = $count;
		$cache->put('bookmarks_count', $cached_count);
		set_cache_refresh('bookmarks_count');
	}
	return $count;
}

/*	get the subscriptions count
*	by Marc Alexander
*/

function get_subscriptions_count()
{
	global $db, $cache;
	$count = 0;
	
	$count = $cache->get('subscriptions_count');
	
	if(!isset($count) || $count < 1 || select_cache_refresh('subscriptions_count'))
	{
		$sql = 'SELECT COUNT(topic_id) AS count FROM ' . TOPICS_WATCH_TABLE;
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('count');
		$db->sql_freeresult($result);
		$cached_count = $count;
		$cache->put('subscriptions_count', $cached_count);
		set_cache_refresh('subscriptions_count');
	}
	return $count;
}

/**	
* save bbcode and smiley count
* this has to be executed before the post is submitted to the database
* if the post has been edited, the function will also substract the initial bbcode and smiley counts
* possible modes: post(default), edit
* Copyright (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
*/
function save_bbcode_smiley_count($data, $mode)
{
	global $db;	
	
	$matches = $smilies = $smiley_ary = $bbcode_ary =  array();
	
	$message = $data['message'];
	
	//	get bbcode information
	$sql = 'SELECT bbcode FROM ' . STATS_BBCODES_TABLE;
	$result = $db->sql_query($sql);
	while ($bbcode_row = $db->sql_fetchrow($result))
	{
		$bbcode_ary[] = array('bbcode' => $bbcode_row['bbcode'], 'count' => 0);
	}	
	$db->sql_freeresult($result);
	
	//	get smiley information
	$sql = 'SELECT smiley_url FROM ' . STATS_SMILIES_TABLE;
	$result = $db->sql_query($sql);
	while ($smiley_row = $db->sql_fetchrow($result))
	{
		$smilies[$smiley_row['smiley_url']] = 0;
	}	
	$db->sql_freeresult($result);
   
	$text = $message;
	
	/**
	* strip the smilies
	* unfortunately, we can't use preg_match_all anymore, since that stupid function also gets inline attachments
	*/
	foreach($smilies as $key => $count)
	{
		$smilies[$key] = $smilies[$key] + ((strlen($text) - strlen(str_replace($key, '', $text))) / strlen($key));
	}
	
	//	count the bbcodes
	foreach($bbcode_ary as $key => $current_bbcode)
	{
		$bbcode_ary[$key]['count'] = $bbcode_ary[$key]['count'] + ((strlen($text) - strlen(str_replace($current_bbcode['bbcode'], '', $text))) / strlen($current_bbcode['bbcode']));
	}
	
	/**	Now let's save what we got here
	* 	I know this looks kind of fancy, and it really was hard to get
	*	@phpBB Dev Team: Please don't change the way smilies in posts are saved in the database. ;-)
	*/
	foreach($bbcode_ary as $current_bbcode)
	{
		if($current_bbcode['count'] > 0)
		{
			$sql = 'UPDATE ' . STATS_BBCODES_TABLE . ' SET bbcode_count = (bbcode_count + ' . (int) $current_bbcode['count'] . ")
					WHERE bbcode = '" . $db->sql_escape($current_bbcode['bbcode']) . "'"; 
			$db->sql_query($sql);
			if (!$db->sql_affectedrows())
			{
				$sql = 'INSERT INTO ' . STATS_BBCODES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'bbcode'	=> $current_bbcode['bbcode'],
					'bbcode_count'	=> $current_bbcode['count']));
				$db->sql_query($sql);
			}
		}
	}
	
	//	The counts of the old smilies and bbcodes need to be substracted
	if($mode == 'edit')
	{
		// 	get old post's information
		$post_ary = array();
		$sql = 'SELECT post_text FROM ' . POSTS_TABLE . ' WHERE post_id = ' . $db->sql_escape($data['post_id']);
		$result = $db->sql_query($sql);
		$post_text = $db->sql_fetchfield('post_text');
		$db->sql_freeresult($result);
		
		$text = $post_text;

		/**
		* strip the smilies
		* unfortunately, we can't use preg_match_all anymore, since that stupid function also gets inline attachments
		*/
		foreach($smilies as $key => $count)
		{
			$smilies[$key] = $smilies[$key] - ((strlen($text) - strlen(str_replace($key, '', $text))) / strlen($key));
		}
		
		//	Clean bbcode_ary
		foreach($bbcode_ary as $key => $current_bbcode)
		{
			$bbcode_ary[$key]['count'] = 0;
		}
		
		//	count the bbcodes
		foreach($bbcode_ary as $key => $current_bbcode)
		{
			$bbcode_ary[$key]['count'] = $bbcode_ary[$key]['count'] + ((strlen($text) - strlen(str_replace($current_bbcode['bbcode'], '', $text))) / strlen($current_bbcode['bbcode']));
		}
		
		foreach($bbcode_ary as $key => $current_bbcode)
		{
			if($current_bbcode['count'] > 0)
			{
				$sql = 'UPDATE ' . STATS_BBCODES_TABLE . ' SET bbcode_count = (bbcode_count - ' . (int) $current_bbcode['count'] . ") 
						WHERE bbcode = '" . $db->sql_escape($current_bbcode['bbcode']) . "'"; 
				$db->sql_query($sql);
			}
		}
	}
	
	/** 
	*	Update the smiley counts in the database
	*/
	foreach($smiley_ary as $key => $smiley_count)
	{
		if($smiley_count != 0)
		{
			$sql = 'UPDATE ' . STATS_SMILIES_TABLE . ' SET smiley_count = (smiley_count + ' . (int) $smiley_count . ")
							WHERE smiley_url = '" . $db->sql_escape($key) . "'";
			$db->sql_query($sql);
		}
	}
}

/**
* get overall bbcode and smiley count
* this has to be executed upon install, update, and if the counts table have been corrupted
* Copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
*/
function overall_bbcode_smiley_count($start = 0, $url, $get_vars)
{
	global $db, $phpbb_root_path, $phpEx;
	
	$post_ary = $bbcodes = $matches = $smilies = $bbcode_ary = array();
	
	if($start < 1)
	{
		//	We need some BBCode information
		$bbcodes = array();
		$bbcode_ary[0] = array('bbcode' => '[/b:', 'count' => 0);
		$bbcode_ary[1] = array('bbcode' => '[/attachment:', 'count' => 0);
		$bbcode_ary[2] = array('bbcode' => '[/code:', 'count' => 0);
		//$bbcode_ary[3] = array('bbcode' => '[*', 'count' => 0);
		$bbcode_ary[4] = array('bbcode' => '[/size:', 'count' => 0);
		$bbcode_ary[5] = array('bbcode' => '[/i:', 'count' => 0);
		$bbcode_ary[6] = array('bbcode' => '[list:', 'count' => 0);
		$bbcode_ary[7] = array('bbcode' => '[/img:', 'count' => 0);
		$bbcode_ary[8] = array('bbcode' => '[list=', 'count' => 0);
		$bbcode_ary[9] = array('bbcode' => '[/quote:', 'count' => 0);
		$bbcode_ary[10] = array('bbcode' => '[/color:', 'count' => 0);
		$bbcode_ary[11] = array('bbcode' => '[/u:', 'count' => 0);
		$bbcode_ary[12] = array('bbcode' => '[/url:', 'count' => 0);
		$bbcode_ary[13] = array('bbcode' => '[/flash:', 'count' => 0);
			
		// now get the custom BBCodes
		$sql = 'SELECT bbcode_tag AS tag
				FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);
		while ($bbcode_row = $db->sql_fetchrow($result))
		{
			if(preg_match ('/[^a-z]/i', $bbcode_row['tag']))
			{
				$bbcode_row['tag'] = preg_replace('/[^a-zA-Z0-9\s]/', '', $bbcode_row['tag']);
			}
			
			//Make sure we don't get any duplicates
			if(!in_array(array('bbcode' => '[/' . strtolower($bbcode_row['tag']) . ':', 'count' => 0), $bbcode_ary))
			{
				$bbcode_ary[] = array('bbcode' => '[/' . strtolower($bbcode_row['tag']) . ':', 'count' => 0);
			}
		}	
		$db->sql_freeresult($result);
		
		// Now we also need some Smiley information
		$sql = 'SELECT DISTINCT(smiley_url)
				FROM ' . SMILIES_TABLE;
		$result = $db->sql_query($sql);
		while ($smiley_row = $db->sql_fetchrow($result))
		{
			if(!isset($smilies[$smiley_row['smiley_url']]))
			{
				$smilies[$smiley_row['smiley_url']] = 0;
			}
		}
		$db->sql_freeresult($result);
	}
	else
	{
		//	get bbcode information
		$sql = 'SELECT bbcode FROM ' . STATS_BBCODES_TABLE;
		$result = $db->sql_query($sql);
		while ($bbcode_row = $db->sql_fetchrow($result))
		{
			$bbcode_ary[] = array('bbcode' => $bbcode_row['bbcode'], 'count' => 0);
		}	
		$db->sql_freeresult($result);
		
		//	get smiley information
		$sql = 'SELECT smiley_url FROM ' . STATS_SMILIES_TABLE;
		$result = $db->sql_query($sql);
		while ($smiley_row = $db->sql_fetchrow($result))
		{
			$smilies[$smiley_row['smiley_url']] = 0;
		}	
		$db->sql_freeresult($result);
	}
	
	//	first we have to get all posts from the database
	$sql = 'SELECT post_text 
			FROM ' . POSTS_TABLE . '
			ORDER BY post_id ASC';
	$result = $db->sql_query_limit($sql, 5000, $start);
	$affected_rows = $db->sql_affectedrows();
	while ($row = $db->sql_fetchrow($result))
	{	
		$text = $row['post_text'];
		
		/**
		* strip the smilies
		* unfortunately, we can't use preg_match_all anymore, since that stupid function also gets inline attachments
		*/
		foreach($smilies as $key => $count)
		{
			$smilies[$key] = $smilies[$key] + ((strlen($text) - strlen(str_replace($key, '', $text))) / strlen($key));
			
		}
		
		/**	strip the bbcodes
		*	we can't use preg_match_all here, since that will just parse everything that looks like a bbcode
		*/
		foreach($bbcode_ary as $key => $current_bbcode)
		{
			$bbcode_ary[$key]['count'] = $bbcode_ary[$key]['count'] + ((strlen($text) - strlen(str_replace($current_bbcode['bbcode'], '', $text))) / strlen($current_bbcode['bbcode']));
		}
		
	}			
	$db->sql_freeresult($result);
	
	if($start == 0)
	{
		//	Clean the database tables at the beginning of the loop
		$sql = 'DELETE FROM ' . STATS_SMILIES_TABLE;
		$db->sql_query($sql);
		
		$sql = 'DELETE FROM ' . STATS_BBCODES_TABLE;
		$db->sql_query($sql);
	}
	
	//	Now let's save what we got here
	foreach($smilies as $key => $current_smiley)
	{
		if($start > 0 && $current_smiley > 0)
		{
			$sql = 'UPDATE ' . STATS_SMILIES_TABLE . ' SET smiley_count = (smiley_count + ' . (int) $current_smiley . ")
							WHERE smiley_url = '" . $db->sql_escape($key) . "'";
			$result = $db->sql_query($sql);
			if (!$db->sql_affectedrows())
			{
				$sql = 'INSERT INTO ' . STATS_SMILIES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
						'smiley_url'    => $key,
						'smiley_count'  => $current_smiley));
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);
		}
		elseif($start == 0)
		{
			$sql = 'INSERT INTO ' . STATS_SMILIES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'smiley_url'    => $key,
					'smiley_count'  => $current_smiley));
			$db->sql_query($sql);
		}
	}
	
	foreach($bbcode_ary as $current_bbcode)
	{
		if($current_bbcode['count'] > 0)
		{
			$sql = 'UPDATE ' . STATS_BBCODES_TABLE . ' SET bbcode_count = (bbcode_count + ' . (int) $current_bbcode['count'] . ")
					WHERE bbcode = '" . $db->sql_escape($current_bbcode['bbcode']) . "'"; 
			$result = $db->sql_query($sql);
			if (!$db->sql_affectedrows())
			{
				$sql = 'INSERT INTO ' . STATS_BBCODES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'bbcode'	=> $current_bbcode['bbcode'],
					'bbcode_count'	=> $current_bbcode['count']));
				$db->sql_query($sql);
			}
			$db->sql_freeresult($result);
		}
		elseif($start == 0)
		{
			$sql = 'INSERT INTO ' . STATS_BBCODES_TABLE . ' ' . $db->sql_build_array('INSERT', array(
					'bbcode'	=> $current_bbcode['bbcode'],
					'bbcode_count'	=> $current_bbcode['count']));
			$db->sql_query($sql);
		}
	}
	
	if($affected_rows == 5000) // set this to the limit number of the post_text sql query
	{
		$url = (append_sid($url, $get_vars . '&amp;start_sql=' . ($start + 5000))); // add the limit number to $start
		meta_refresh(5, $url); // time is set to 5 seconds -- that should be enough for 5000 posts
		return true; // Tell the install script that we need a refresh
	}
	else
	{
		return false; // Tell the install script that no refresh is needed
	}
	
}

/**
* Move Add-Ons up or down
* $mode: up, down
* $size: pass the size of the addons-array
* $id: ID of the current-addon; IDs are just for sorting purposes
* $classname: the classname of the addon that is being moved
*
* Copyright (c) 2010 Marc Alexander(marc1706) www.m-a-styles.de
*/
function move_addon($mode = 'move_up', $size, $id, $classname)
{
	global $db;
	
	if(($id == 1 && $mode == 'move_up') || ($id == $size && $mode == 'move_down'))
	{
		return;
	}
	
	/**
	* select the new ID of the selected add-on
	*/
	if($mode == 'move_down')
	{
		$new_id = $id + 1;
	}
	elseif ($mode == 'move_up')
	{
		$new_id = $id - 1;
	}
	
	/**
	* select the add-on classname of the add-on that currently has the new ID
	*/
	$sql = 'SELECT addon_classname AS addon_classname
			FROM ' . STATS_ADDONS_TABLE . '
			WHERE addon_id = ' . (int) $new_id;
	$result = $db->sql_query($sql);
	$addon_name = $db->sql_fetchfield('addon_classname');
	$db->sql_freeresult($result);
	
	/**
	* update the database
	*/
	$sql = 'UPDATE ' . STATS_ADDONS_TABLE . '
			SET addon_id = ' . (int) $id . "
			WHERE addon_classname = '" . $db->sql_escape($addon_name) . "'";
	$result = $db->sql_query($sql);
	
	$sql = 'UPDATE ' . STATS_ADDONS_TABLE . '
			SET addon_id = ' . (int) $new_id . "
			WHERE addon_classname = '" . $db->sql_escape($classname) . "'";
	$result = $db->sql_query($sql);
	$db->sql_freeresult($result);
}

?>