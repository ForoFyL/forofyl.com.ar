<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_periodic.php 166 2011-01-06 23:19:06Z marc1706 $
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
* fs_periodic
* Displays periodic (monthly,  daily statistics)
* Currently this queries the db to get the data.... once this is successful, I'll try to make it cached data, delayed by upto 1 hour, so that the db is not overloaded
* @package fs
*/
class stats_periodic
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $stats_config;		

		$user->add_lang('common'); // needed for correct language dates
		
		if(sizeof($stats_config) < 1)
		{
			$stats_config = obtain_stats_config();
		}

		switch ($mode)
		{
			case 'daily':
			if($stats_config['periodic_daily_enable'])
			{
				//get the month for which to show stats, by default set it to the current month
				$selected_month = request_var('selected_month', '');
				$current_time = getdate(time() + ((get_timezone_offset('timezone', $config['board_timezone'], $user->data['user_timezone'])) * 3600) + ((get_timezone_offset('dst', $config['board_dst'], $user->data['user_dst'])) * 3600)); //calculate the time here which will be used henceforth to prevent any mismatch if date changes at the tick of midnight!!!
				$board_starttime = getdate($config['board_startdate']);
				
				$start_time = $end_time = $counted_days = 0;
				if (!$selected_month)
				{
					$selected_month = date('n-Y', $current_time[0]);
				}
				$selected_month = explode('-', $selected_month, 2); //[0] => month, [1] => year
				
				if ($selected_month[0] == $current_time['mon'] && $selected_month[1] == $current_time['year']) //if its the current month
				{
					//check if this is the first month of the board
					if ($board_starttime['mon'] == $current_time['mon'] && $board_starttime['year'] == $current_time['year'])
					{
						$start_time = $board_starttime[0];						
					}
					else
					{
						$start_time = mktime(0, 0, 0, $current_time['mon'], 1, $current_time['year']);
					}
					$end_time = $current_time[0];
				}
				else //some different month
				{
					//check if the month is the board startdate month, if so only start days from the board start day
					if ($selected_month[0] == $board_starttime['mon'] && $selected_month[1] == $board_starttime['year'])
					{
						$start_time = $board_starttime[0];
					}
					else
					{
						$start_time = mktime(0, 0, 0, $selected_month[0], 1, $selected_month[1]);
					}
					$end_time = mktime(0, 0, 0, $selected_month[0] + 1, 1, $selected_month[1]);
				}
				
				$start_time = getdate($start_time);
				$end_time = getdate($end_time);
								
				$totals = $max = array('topics' => 0, 'posts' => 0, 'user_reg' => 0);
				$daily_data = array();
				$first_day = $start_time['mday'];
				//$last_day is tricky, i do this this way
				$last_day = date('j', $end_time[0] - 1);
				$counted_days = $last_day - $first_day + 1;
				for ($i = $first_day; $i <= $last_day; ++$i)
				{					
					$daily_data[$i] = array('topics' => 0, 'posts' => 0, 'user_reg' => 0);
				}
				//free some memory
				unset($first_day);
				unset($last_day);
				
				//ok get the data now
				//topics				
				$sql = 'SELECT topic_time AS time FROM ' . TOPICS_TABLE . '
							WHERE topic_approved = 1
								AND topic_time >= ' . $start_time[0] . ' AND topic_time < ' . $end_time[0];				
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$daily_data[date('j', $row['time'])]['topics'];
				}
				$db->sql_freeresult($result);
				
				//posts
				$sql = 'SELECT post_time AS time FROM ' . POSTS_TABLE . '
							WHERE post_approved = 1
								AND post_time >= ' . $start_time[0] . ' AND post_time < ' . $end_time[0];				
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$daily_data[date('j', $row['time'])]['posts'];
				}
				$db->sql_freeresult($result);
				
				//user regs
				$sql = 'SELECT user_regdate AS time FROM ' . USERS_TABLE . '
							WHERE ' . $db->sql_in_set('user_type', array(USER_NORMAL, USER_FOUNDER)) . '
								AND user_regdate >= ' . $start_time[0] . ' AND user_regdate < ' . $end_time[0];				
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$daily_data[date('j', $row['time'])]['user_reg'];
				}
				$db->sql_freeresult($result);
				
				//now calculate totals and max
				foreach ($daily_data as $day => $data)
				{
					$totals['topics'] += $data['topics'];
					$totals['posts'] += $data['posts'];
					$totals['user_reg'] += $data['user_reg'];
					if ($data['topics'] > $max['topics'])
					{
						$max['topics'] = $data['topics'];
					}
					if ($data['posts'] > $max['posts'])
					{
						$max['posts'] = $data['posts'];
					}
					if ($data['user_reg'] > $max['user_reg'])
					{
						$max['user_reg'] = $data['user_reg'];
					}
				}
				$search = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
				$replace= array($user->lang['datetime']['January'], $user->lang['datetime']['February'], $user->lang['datetime']['March'], $user->lang['datetime']['April'], $user->lang['datetime']['May'], $user->lang['datetime']['June'], $user->lang['datetime']['July'], $user->lang['datetime']['August'], $user->lang['datetime']['September'], $user->lang['datetime']['October'], $user->lang['datetime']['November'], $user->lang['datetime']['December']);
				//now send data to template
				if ($totals['topics'])
				{
					$template->assign_var('S_DAILY_TOPICS', true);
					foreach ($daily_data as $day => $data)
					{
						$time = 0;
						$time = date('d F Y', mktime(0, 0, 0, $selected_month[0], $day, $selected_month[1]));
						$template->assign_block_vars('periodic_topics_row', array(
							'TIME_ELEMENT'				=> str_replace($search, $replace, $time),
							'COUNT'						=> $data['topics'],
							'PCT'						=> number_format($data['topics'] / $totals['topics'] * 100, 2),
							'BARWIDTH'					=> number_format($data['topics'] / $max['topics'] * 100, 1),
							'IS_MAX'				=> ($data['topics'] == $max['topics']),
						));
					}
				}
				//posts
				if ($totals['posts'])
				{
					$template->assign_var('S_DAILY_POSTS', true);
					foreach ($daily_data as $day => $data)
					{
						$time = 0;
						$time = date('d F Y', mktime(0, 0, 0, $selected_month[0], $day, $selected_month[1]));
						$template->assign_block_vars('periodic_posts_row', array(
							'TIME_ELEMENT'				=> str_replace($search, $replace, $time),
							'COUNT'						=> $data['posts'],
							'PCT'						=> number_format($data['posts'] / $totals['posts'] * 100, 2),
							'BARWIDTH'					=> number_format($data['posts'] / $max['posts'] * 100, 1),
							'IS_MAX'				=> ($data['posts'] == $max['posts']),
						));
					}
				}
				//user regs
				if ($totals['user_reg'])
				{
					$template->assign_var('S_DAILY_USER_REGS', true);
					foreach ($daily_data as $day => $data)
					{
						$time = 0;
						$time = date('d F Y', mktime(0, 0, 0, $selected_month[0], $day, $selected_month[1]));
						$template->assign_block_vars('periodic_user_regs_row', array(
							'TIME_ELEMENT'				=> str_replace($search, $replace, $time),
							'COUNT'						=> $data['user_reg'],
							'PCT'						=> number_format($data['user_reg'] / $totals['user_reg'] * 100, 2),
							'BARWIDTH'					=> number_format($data['user_reg'] / $max['user_reg'] * 100, 1),
							'IS_MAX'				=> ($data['user_reg'] == $max['user_reg']),
						));
					}
				}
				
				//we have to show the month-select box, so get all the months and their display from the board start date
				//calculate the first month and year
				$temp_month = $board_starttime['mon'];
				$temp_year = $board_starttime['year'];
				$month_options = array();
				
				while (($temp_epoch = mktime(0, 0, 0, $temp_month, 1, $board_starttime['year'])) <= $current_time[0])
				{
					$month_options = array_merge($month_options, array(
						date('n-Y', $temp_epoch) => date('F Y', $temp_epoch)
					));
					++$temp_month;
				}
				
				$month_select_box = make_select_box(str_replace($search, $replace, $month_options), str_replace($search, $replace, $selected_month[0]) . '-' . str_replace($search, $replace, $selected_month[1]), 'selected_month', $user->lang['SHOW_STATS_FOR_MONTH'], $user->lang['GO'], $this->u_action);
				
				$template->assign_vars(array(
					'TOTAL_TOPICS'				=> $totals['topics'],
					'TOTAL_POSTS'				=> $totals['posts'],
					'TOTAL_USER_REGS'				=> $totals['user_reg'],
					'AVG_TOPICS'				=> ($counted_days < 1) ? $totals['topics'] : number_format($totals['topics'] / $counted_days, 2),
					'AVG_POSTS'					=> ($counted_days < 1) ? $totals['posts'] : number_format($totals['posts'] / $counted_days, 2),
					'AVG_USER_REGS'				=> ($counted_days < 1) ? $totals['user_reg'] : number_format($totals['user_reg'] / $counted_days, 2),
					'STATS_MONTH_EXPLAIN'		=> sprintf($user->lang['STATS_MONTH_EXPLAIN'], str_replace($search, $replace, $start_time['month']) . ' ' . $start_time['year']),
					'MONTH_SELECT_BOX'			=> $month_select_box,
				));
			}
			else
			{
				$template->assign_var('S_PERIODIC_DAILY_DISABLED', true);
			}
			break;

			case 'monthly':
			if($stats_config['periodic_monthly_enable'])
			{
				$search = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
				$replace= array($user->lang['datetime']['January'], $user->lang['datetime']['February'], $user->lang['datetime']['March'], $user->lang['datetime']['April'], $user->lang['datetime']['May'], $user->lang['datetime']['June'], $user->lang['datetime']['July'], $user->lang['datetime']['August'], $user->lang['datetime']['September'], $user->lang['datetime']['October'], $user->lang['datetime']['November'], $user->lang['datetime']['December']);
				$show_all = false; //whether to show from the board start date
				//get the year for which to show stats, by default set it to the current month
				$selected_year = trim(request_var('selected_year', ''));
				$current_time = getdate(time() + ((get_timezone_offset('timezone', $config['board_timezone'], $user->data['user_timezone'])) * 3600) + ((get_timezone_offset('dst', $config['board_dst'], $user->data['user_dst'])) * 3600)); //calculate the time here which will be used henceforth to prevent any mismatch if date changes at the tick of midnight!!!
				$board_starttime = getdate($config['board_startdate']);				
				
				if (!$selected_year)
				{
					$selected_year = $current_time['year'];
				}
				elseif ($selected_year == 'all')
				{
					$show_all = true;
				}
				
				//now get the first and last time limit for the search
				$start_time = $end_time = $counted_months = 0;
				if ($show_all)
				{
					$start_time = $board_starttime[0];
					$end_time = $current_time[0];
				}
				else 
				{
					if ($selected_year == $board_starttime['year']) //if the board started in the selected year, start from the board start month
					{
						$start_time = $board_starttime[0];
					}
					else
					{
						$start_time = mktime(0, 0, 0, 1, 1, $selected_year);				
					}
					$end_time = mktime(0, 0, 0, 1, 1, $selected_year + 1);
				}
				$start_time = getdate($start_time);
				$end_time = getdate($end_time);
				
				$monthly_data = array();				
				$offset_start_time = $start_time[0];
				while ($offset_start_time < $end_time[0])
				{				
					$monthly_data[date('F Y', $offset_start_time)] = array('topics' => 0, 'posts' => 0, 'user_reg' => 0);
					++$counted_months;					
					$offset_start_time = mktime(0, 0, 0, $start_time['mon'] + $counted_months, 1, $start_time['year']);
				}			
				
				//now get the queries
				//topics				
				$sql = 'SELECT topic_time AS time FROM ' . TOPICS_TABLE . '
							WHERE topic_approved = 1';
				if (!$show_all)
				{
					$sql .= ' AND topic_time >= ' . $start_time[0] . ' AND topic_time < ' . $end_time[0];
				}
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$monthly_data[date('F Y', $row['time'])]['topics'];
				}
				$db->sql_freeresult($result);
				
				//posts
				$sql = 'SELECT post_time AS time FROM ' . POSTS_TABLE . '
							WHERE post_approved = 1';
				if (!$show_all)
				{
					$sql .= ' AND post_time >= ' . $start_time[0] . ' AND post_time < ' . $end_time[0];
				}
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$monthly_data[date('F Y', $row['time'])]['posts'];
				}
				$db->sql_freeresult($result);
				
				//user regs
				$sql = 'SELECT user_regdate AS time FROM ' . USERS_TABLE . '
							WHERE ' . $db->sql_in_set('user_type', array(USER_NORMAL, USER_FOUNDER));
				if (!$show_all)
				{
					$sql .= ' AND user_regdate >= ' . $start_time[0] . ' AND user_regdate < ' . $end_time[0];
				}
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					++$monthly_data[date('F Y', $row['time'])]['user_reg'];
				}
				$db->sql_freeresult($result);
				
				//all data retrieved now get the max and totals
				$totals = $max = array('topics' => 0, 'posts' => 0, 'user_reg' => 0);
				foreach ($monthly_data as $month => $data)
				{
					$totals['topics'] += $data['topics'];
					$totals['posts'] += $data['posts'];
					$totals['user_reg'] += $data['user_reg'];
					if ($data['topics'] > $max['topics'])
					{
						$max['topics'] = $data['topics'];
					}
					if ($data['posts'] > $max['posts'])
					{
						$max['posts'] = $data['posts'];
					}
					if ($data['user_reg'] > $max['user_reg'])
					{
						$max['user_reg'] = $data['user_reg'];
					}
				}
				
				//show stats for topics
				if ($totals['topics'])
				{
					$template->assign_var('S_MONTHLY_TOPICS', true);
					foreach ($monthly_data as $month => $data)
					{	
						$template->assign_block_vars('periodic_topics_row', array(
							'TIME_ELEMENT'			=> str_replace($search, $replace, $month),
							'COUNT'					=> $data['topics'],
							'PCT'					=> number_format($data['topics'] / $totals['topics'] * 100, 2),
							'BARWIDTH'				=> number_format($data['topics'] / $max['topics'] * 100, 1),
							'IS_MAX'				=> ($data['topics'] == $max['topics']),
						));
					}
				}
				//show stats for posts
				if ($totals['posts'])
				{
					$template->assign_var('S_MONTHLY_POSTS', true);
					foreach ($monthly_data as $month => $data)
					{	
						$template->assign_block_vars('periodic_posts_row', array(
							'TIME_ELEMENT'			=> str_replace($search, $replace, $month),
							'COUNT'					=> $data['posts'],
							'PCT'					=> number_format($data['posts'] / $totals['posts'] * 100, 2),
							'BARWIDTH'				=> number_format($data['posts'] / $max['posts'] * 100, 1),
							'IS_MAX'				=> ($data['posts'] == $max['posts']),
						));
					}
				}
				
				//show stats for user_reg
				if ($totals['user_reg'])
				{
					$template->assign_var('S_MONTHLY_USER_REGS', true);
					foreach ($monthly_data as $month => $data)
					{	
						$template->assign_block_vars('periodic_user_regs_row', array(
							'TIME_ELEMENT'			=> str_replace($search, $replace, $month),
							'COUNT'					=> $data['user_reg'],
							'PCT'					=> number_format($data['user_reg'] / $totals['user_reg'] * 100, 2),
							'BARWIDTH'				=> number_format($data['user_reg'] / $max['user_reg'] * 100, 1),
							'IS_MAX'				=> ($data['user_reg'] == $max['user_reg']),
						));
					}
				}
				
				//we have to show the month-select box, so get all the months and their display from the board start date				
				$temp_year = $board_starttime['year'];
				$year_options = array();				
				while ((int) $temp_year <= (int) $current_time['year'])
				{
					$year_options = array_merge($year_options, array(
						$temp_year . ' ' => $temp_year //we have to give the space so thats its taken as a string, while receiving the argument at the start of this function, we trim it. I tried converting the number to string but still didn't work!
					));
					++$temp_year;
				}
				//add the extra 'all' option also
				$year_options = array_merge($year_options, array(
					'all' => $user->lang['ALL']
				));
				
				$year_select_box = make_select_box($year_options, ($show_all) ? 'all' : $selected_year . ' ', 'selected_year', $user->lang['SHOW_STATS_FOR_YEAR'], $user->lang['GO'], $this->u_action);
				
				$template->assign_vars(array(
					'TOTAL_TOPICS'				=> $totals['topics'],
					'TOTAL_POSTS'				=> $totals['posts'],
					'TOTAL_USER_REGS'				=> $totals['user_reg'],
					'AVG_TOPICS'				=> ($counted_months < 1) ? $totals['topics'] : number_format($totals['topics'] / $counted_months, 2),
					'AVG_POSTS'					=> ($counted_months < 1) ? $totals['posts'] : number_format($totals['posts'] / $counted_months, 2),
					'AVG_USER_REGS'				=> ($counted_months < 1) ? $totals['user_reg'] : number_format($totals['user_reg'] / $counted_months, 2),
					'STATS_YEAR_EXPLAIN'		=> sprintf($user->lang['STATS_YEAR_EXPLAIN'], (($start_time['year'] != $current_time['year']) ? (($show_all) ? $start_time['year'] . ' - ' . $current_time['year'] : $start_time['year']) : $current_time['year'])),
					'MONTH_SELECT_BOX'			=> $year_select_box,
				));
				
			}
			else
			{
				$template->assign_var('S_PERIODIC_MONTHLY_DISABLED', true);
			}
			break;
			
			case 'hourly':
			if($stats_config['periodic_hourly_enable'])
			{
				$search = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
				$replace= array($user->lang['datetime']['January'], $user->lang['datetime']['February'], $user->lang['datetime']['March'], $user->lang['datetime']['April'], $user->lang['datetime']['May'], $user->lang['datetime']['June'], $user->lang['datetime']['July'], $user->lang['datetime']['August'], $user->lang['datetime']['September'], $user->lang['datetime']['October'], $user->lang['datetime']['November'], $user->lang['datetime']['December']);
				$current_time = getdate(time() + ((get_timezone_offset('timezone', $config['board_timezone'], $user->data['user_timezone'])) * 3600) + ((get_timezone_offset('dst', $config['board_dst'], $user->data['user_dst'])) * 3600)); //store the time, which will be used uniformly all throughout this function
				//set the req vars to current time as default
				$board_starttime = getdate($config['board_startdate']);
				$request_day = request_var('sel_day', $current_time['mday']);
				$request_month = request_var('sel_month', $current_time['mon'] . '-' . $current_time['year']);
				$show_all_day = $show_all_month = false;
				
				if ($request_month != 'all')
				{
					
					$request_month = explode('-', $request_month, 2); // [0] => month, [1] => year
					$start_time = mktime(0, 0, 0, $request_month[0], 1, $request_month[1]); //the start time set to 1st day of req month
									
					if ($request_day != 'all')
					{
					
						if ($request_month[0] == $current_time['mon'] && $request_month[1] == $current_time['year'] && $request_day > $current_time['mday']) //if its the current month then if the day is ahead of today, set it to today
						{
							$request_day = $current_time['mday'];
						}
						elseif ($request_month[0] == $board_starttime['mon'] && $request_month[1] == $board_starttime['year'] && $request_day < $board_starttime['mday']) //if its the board start month then if its before the start day, set it to start date
						{
							$request_day = $board_starttime['mday'];
						}
						else
						{
							$temp_day = date('t', $start_time);
							if ($request_day > $temp_day) //check if the days is > than the max days in that month
							{
								$request_day = $temp_day;
							}
						}
						$start_time += ($request_day - 1) * 86400; //update the $start_time with the day requested
						$end_time = $start_time + 86400;
						unset($temp_day);						
					}
					else
					{
						$show_all_day = true;						
						$end_time = mktime(0, 0, 0, $request_month[0] + 1, 1, $request_month[1]);						
					}
				}
				else
				{
					$show_all_month = true;
					//set the date to the 1st of the month of board startdate					
					//$start_time = mktime(0, 0, 0, $temp_date[0], 1, $temp_date[1]);
					$start_time = $board_starttime[0];
					$end_time = $current_time[0];
				}
				
				$all_stats = array(
					'topics'			=> array(),
					'posts'			=> array(),
					'user_reg'			=> array()
				); //the parent array that will hold all data			
				
				//initialize the structure
				for ($i = 0; $i < 24; ++$i)
				{
					$all_stats['topics'][$i] = 0;
					$all_stats['posts'][$i] = 0;
					$all_stats['user_reg'][$i] = 0;
				}
				
				$max_hourly = array('topics' => 0, 'posts' => 0, 'user_reg' => 0); //for storing max values
				
				//get topics first
				$sql = 'SELECT topic_time AS time
							FROM ' . TOPICS_TABLE . '
							WHERE topic_approved = 1';
				if (!$show_all_month) //dont burden the sql if all records are to be retrieved
				{
					$sql .= " AND topic_time >= $start_time
								AND topic_time < $end_time";
				}
				$result = $db->sql_query($sql);
				while ($temp_row = $db->sql_fetchrow($result))
				{
					++$all_stats['topics'][date('G', $temp_row['time'])];
				}
				$db->sql_freeresult($result);
				
				//now posts
				$sql = 'SELECT post_time AS time
							FROM ' . POSTS_TABLE . '
							WHERE post_approved = 1';
				if (!$show_all_month) //dont burden the sql if all records are to be retrieved
				{
					$sql .= " AND post_time >= $start_time
								AND post_time < $end_time";
				}
				$result = $db->sql_query($sql);
				while ($temp_row = $db->sql_fetchrow($result))
				{
					++$all_stats['posts'][date('G', $temp_row['time'])];
				}
				$db->sql_freeresult($result);
				
				//and user regs
				$sql = 'SELECT user_regdate AS time
							FROM ' . USERS_TABLE . '
							WHERE (user_type = ' . USER_NORMAL . ' OR user_type = ' . USER_FOUNDER . ')';
				if (!$show_all_month) //dont burden the sql if all records are to be retrieved
				{
					$sql .= " AND user_regdate >= $start_time
								AND user_regdate < $end_time";
				}
				$result = $db->sql_query($sql);
				while ($temp_row = $db->sql_fetchrow($result))
				{
					$all_stats['user_reg'][date('G', $temp_row['time'])]++;
				}
				$db->sql_freeresult($result);
				
				//get the totals for current duration
				$total_topics = array_sum($all_stats['topics']);
				$total_posts = array_sum($all_stats['posts']);
				$total_user_regs = array_sum($all_stats['user_reg']);
				
				//calculate max values
				for ($i = 0; $i < 24; ++$i)
				{
					if ($all_stats['topics'][$i] > $max_hourly['topics'])
					{
						$max_hourly['topics'] = $all_stats['topics'][$i];
					}
					if ($all_stats['posts'][$i] > $max_hourly['posts'])
					{
						$max_hourly['posts'] = $all_stats['posts'][$i];
					}
					if ($all_stats['user_reg'][$i] > $max_hourly['user_reg'])
					{
						$max_hourly['user_reg'] = $all_stats['user_reg'][$i];
					}
				}
				
				//display the results
				//since we have to compulsorily show the 24 hours we use for loops here instead of foreach, plus we have to show the hour too (like 0000 - 0059) which is easier this way
				//first topics
				if ($total_topics)
				{
					$template->assign_var('S_HOURLY_TOPICS', true);
					for ($i = 0; $i < 24; ++$i)
					{
						$template->assign_block_vars('periodic_topics_row', array(
						'TIME_ELEMENT'		=> str_pad($i, 2, '0', STR_PAD_LEFT) . '00 - ' . str_pad($i, 2, '0', STR_PAD_LEFT) . '59',
						'COUNT'				=> $all_stats['topics'][$i],
						'PCT'				=> number_format($all_stats['topics'][$i] / $total_topics * 100, 2),
						'BARWIDTH'			=> number_format($all_stats['topics'][$i] / $max_hourly['topics'] * 100, 1),
						'IS_MAX'			=> ($all_stats['topics'][$i] == $max_hourly['topics']) ? true : false,
						));
					}
				}
				//posts
				if ($total_posts)
				{
					$template->assign_var('S_HOURLY_POSTS', true);
					for ($i = 0; $i < 24; ++$i)
					{
						$template->assign_block_vars('periodic_posts_row', array(
						'TIME_ELEMENT'		=> str_pad($i, 2, '0', STR_PAD_LEFT) . '00 - ' . str_pad($i, 2, '0', STR_PAD_LEFT) . '59',
						'COUNT'				=> $all_stats['posts'][$i],
						'PCT'				=> number_format($all_stats['posts'][$i] / $total_posts * 100, 2),
						'BARWIDTH'			=> number_format($all_stats['posts'][$i] / $max_hourly['posts'] * 100, 1),
						'IS_MAX'			=> ($all_stats['posts'][$i] == $max_hourly['posts']) ? true : false,
						));
					}
				}
				//user_regs
				if ($total_user_regs)
				{
					$template->assign_var('S_HOURLY_USER_REGS', true);
					for ($i = 0; $i < 24; ++$i)
					{
						$template->assign_block_vars('periodic_user_regs_row', array(
						'TIME_ELEMENT'		=> str_pad($i, 2, '0', STR_PAD_LEFT) . '00 - ' . str_pad($i, 2, '0', STR_PAD_LEFT) . '59',
						'COUNT'				=> $all_stats['user_reg'][$i],
						'PCT'				=> number_format($all_stats['user_reg'][$i] / $total_user_regs * 100, 2),
						'BARWIDTH'			=> number_format($all_stats['user_reg'][$i] / $max_hourly['user_reg'] * 100, 1),
						'IS_MAX'			=> ($all_stats['user_reg'][$i] == $max_hourly['user_reg']) ? true : false,
						));		
					}
				}
			

				//create the day select box
				$day_select_box = '<select id="sel_day" name="sel_day">';
				//first use 'all'
				$day_select_box .= '<option value="all"' . (($show_all_day) ? ' selected="selected"' : '') . '>' . $user->lang['ALL'] . '</option>';
				for ($i = 1; $i <= 9; ++$i) //for 1 to 9 use 0 pading to the left
				{
					$day_select_box .= '<option value="' . $i . '"' . (($request_day == $i) ? ' selected="selected"' : '') . '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';					
				}
				for (; $i <= 31; ++$i)
				{
					$day_select_box .= '<option value="' . $i . '"' . (($request_day == $i) ? ' selected="selected"' : '') . '>' . $i . '</option>';
				}
				$day_select_box .= '</select>';
				
				//now create the month box
				$month_select_box = '<select id="sel_month" name="sel_month">';
				//first use 'all'
				$month_select_box .= '<option value="all"' . (($show_all_month) ? ' selected="selected"' : '') . '>' . $user->lang['ALL'] . '</option>';
				
				$count_months = 0;
				$selected_month = ($request_month != 'all') ? $request_month[0] . '-' . $request_month[1] : '';
				while (($temp_time = mktime(0, 0, 0, $board_starttime['mon'] + $count_months, 1, $board_starttime['year'])) < $current_time[0])
				{
					$month_select_box .= '<option value="' . str_replace($search, $replace, (date('n-Y', $temp_time))) . '"' . ((str_replace($search, $replace, (date('n-Y', $temp_time) == $selected_month))) ? ' selected="selected"' : '') . '>' . str_replace($search, $replace, (date('F Y', $temp_time))) . '</option>';
					++$count_months;
				}
				$month_select_box .= '</select>';
				
				$template->assign_vars(array(
					'TOTAL_TOPICS' 			=> $total_topics,
					'TOTAL_POSTS'				=> $total_posts,
					'TOTAL_USER_REGS'			=> $total_user_regs,
					'SELECT_BOX'				=> '<label for="sel_day">' . $user->lang['SELECT_TIME_PERIOD'] . ': </label>' . $day_select_box . ' ' . $month_select_box . ' <input type="submit" class="button2" value="' . ucfirst($user->lang['GO']) . '" />',
					'PERCENT_OF_TOTAL_TOPICS'	=> sprintf($user->lang['PERCENT_OF_TOTAL'], $user->lang['TOPICS']),
					'PERCENT_OF_TOTAL_POSTS'	=> sprintf($user->lang['PERCENT_OF_TOTAL'], $user->lang['POSTS']),
					'PERCENT_OF_TOTAL_USER_REGS'	=> sprintf($user->lang['PERCENT_OF_TOTAL'], $user->lang['USER_REGS']),
					'PCT_USER_REGS'				=> number_format($total_user_regs / $config['num_users'] * 100, 5),
					'HOURLY_STATS_EXPLAIN'		=> sprintf($user->lang['HOURLY_STATS_EXPLAIN'], str_replace($search, $replace, ((($show_all_month) ? $board_starttime['month'] . ' ' . $board_starttime['year'] . ' - ' . $current_time['month'] . ' ' . $current_time['year'] : ((!$show_all_day) ? str_pad($request_day, 2, '0', STR_PAD_LEFT) . ' ' : '') . date('F Y', mktime(0, 0, 0, $request_month[0], 1, $request_month[1])))))),
				));
				
				if($config['num_topics'])
				{	
					$template->assign_var('PCT_TOPICS', number_format($total_topics / $config['num_topics'] * 100, 5));
				}
				else
				{
					$template->assign_var('PCT_TOPICS', number_format(0 * 100, 5));
				}
				
				if($config['num_posts'])
				{	
					$template->assign_var('PCT_POSTS', number_format($total_posts / $config['num_posts'] * 100, 5));
				}
				else
				{
					$template->assign_var('PCT_POSTS', number_format(0 * 100, 5));
				}
				
			}
			else
			{
				$template->assign_var('S_PERIODIC_HOURLY_DISABLED', true);
			}
			break;
			
			default:
		}
		
		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['STATS_PERIODIC_' . strtoupper($mode)],			
			'S_STATS_ACTION'	=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),
		));
		
		$this->tpl_name = 'stats/stats_periodic_' . $mode;
		$this->lang_name = 'stats_periodic_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->lang_name)];
		
	}
}
?>