<?php
/**
*
* @package phpBB Statistics
* @version $Id: stats_settings.php 168 2011-02-09 01:12:55Z marc1706 $
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
* fs_settings
* Displays statistics about various board & profile settings
* 
* @package fs
*/
class stats_settings
{
	var $u_action;

	function main($id, $mode)
	{
		global $config, $db, $user, $auth, $template, $phpbb_root_path, $phpEx, $stats_config;		

		// $user->add_lang('mods/stats'); // not needed
		
		if(sizeof($stats_config) < 1)
		{
			$stats_config = obtain_stats_config();
		}

		switch ($mode)
		{
			case 'board':
			if($stats_config['settings_board_enable'])
			{
				//stats about styles used by users
				if ($config['override_user_style']) //simply display the default style and the explain message.
				{
					//get the default style
					$sql = 'SELECT style_name, style_copyright FROM ' . STYLES_TABLE . '
								WHERE style_id = ' . $config['default_style'];
					$result = $db->sql_query($sql);
					$default_style_data = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);
					$template->assign_vars(array(
						'S_OVERRIDE_STYLE'		=> true,
						'DEFAULT_STYLE_EXPLAIN'		=> sprintf($user->lang['DEFAULT_STYLE_EXPLAIN'], $default_style_data['style_name'], $default_style_data['style_copyright']),
					));
				}
				else //get each style and the number of users using it
				{
					//first get all the active styles
					$styles_data = array();
					$sql = 'SELECT style_id, style_name, style_copyright FROM ' . STYLES_TABLE . '
								WHERE style_active = 1';
					$result = $db->sql_query($sql);
					while ($temp_row = $db->sql_fetchrow($result))
					{
						$styles_data[] = $temp_row;
					}
					$db->sql_freeresult($result);
					//now create a count array where we will store the count data
					$style_users_count = array();
					foreach ($styles_data as $current_style)
					{
						$style_users_count[$current_style['style_id']] = 0;
					}
					//now get the counts
					$sql = 'SELECT user_style FROM ' . USERS_TABLE . '
								WHERE user_type <> ' . USER_INACTIVE;
					$result = $db->sql_query($sql);
					while ($temp_user = $db->sql_fetchrow($result))
					{
						/** 
						* Make sure we don't get an undefined index error
						* this might happen if a user's default style is set to a style that doesn't exist anymore	
						*/
						if(isset($style_users_count[$temp_user['user_style']]))
						{
							++$style_users_count[$temp_user['user_style']];
						}
					}
					$db->sql_freeresult($result);
					//send vars to template
					foreach ($styles_data as $current_style)
					{
						$template->assign_block_vars('style_users_count', array(
							'STYLE_NAME'			=> $current_style['style_name'],
							'STYLE_COPYRIGHT'		=> $current_style['style_copyright'],
							'STYLE_USERS_COUNT'		=> $style_users_count[$current_style['style_id']],
						));
					}					
				}
				
				//now languages
				//first get the languages count, to know if there are more than 1 languages installed, otherwise its no use getting the list
				$all_langs = array();				
				$sql = 'SELECT lang_id, lang_iso, lang_local_name FROM ' . LANG_TABLE;
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$all_langs[] = $row;
				}
				$db->sql_freeresult($result);
				
				$template->assign_var('LANG_PACKS_COUNT', sizeof($all_langs));
				
				if (sizeof($all_langs) > 1)
				{
				
					$lang_data = array();
					$sql = 'SELECT COUNT(u.user_id) AS count, l.lang_iso, l.lang_local_name
								FROM ' . USERS_TABLE . ' u, ' . LANG_TABLE . ' l 
								WHERE (u.user_type = ' . USER_NORMAL . ' OR u.user_type = ' . USER_FOUNDER . ') 
									AND u.user_lang = l.lang_iso
								GROUP BY l.lang_iso
								ORDER BY count DESC';
					$result = $db->sql_query($sql);
					while ($row = $db->sql_fetchrow($result))
					{
						$lang_data[] = $row;
					}
					$db->sql_freeresult($sql);
					$max_count = $lang_data[0]['count'];
					foreach ($lang_data as $row)
					{
						$template->assign_block_vars('lang_row', array(
							'LANG_NAME'			=> $row['lang_local_name'],
							'LANG_ISO'			=> $row['lang_iso'],
							'COUNT'				=> $row['count'],
							'PCT'				=> number_format($row['count'] / $config['num_users'] * 100, 2),
							'BARWIDTH'			=> number_format($row['count'] / $max_count * 100, 1),
							'IS_MAX'			=> ($row['count'] == $max_count),
							'IS_MINE'			=> ($row['lang_iso'] == $user->data['user_lang']),
						));
					}
				}
				elseif (sizeof($all_langs) == 1) //theres only one lang pack so show the message and the name of this lang pack
				{
					$template->assign_var('DEFAULT_LANG_EXPLAIN', sprintf($user->lang['DEFAULT_LANG_EXPLAIN'], $all_langs[0]['lang_local_name'], $all_langs[0]['lang_iso']));
				}
				
				//timezones
				$timezones = array();
				$sql = 'SELECT COUNT(user_id) AS count, user_timezone FROM ' . USERS_TABLE . '
							WHERE user_type = ' . USER_NORMAL . ' OR user_type = ' . USER_FOUNDER . '
							GROUP BY user_timezone
							ORDER BY user_timezone ASC';
				$result = $db->sql_query($sql);
				while ($row = $db->sql_fetchrow($result))
				{
					$timezones[] = $row;
				}
				$db->sql_freeresult($result);
				
				if ($timezones)
				{
					$template->assign_var('S_TIMEZONES', true);
					$max_count = $timezones[0]['count'];
					//get max value
					foreach ($timezones as $row)
					{
						if ($row['count'] > $max_count)
						{
							$max_count = $row['count'];
						}
					}
				
					foreach ($timezones as $row)
					{
						$template->assign_block_vars('tz_row', array(						
							'TZ_TEXT'				=> $user->lang['tz_zones'][(string) ($row['user_timezone'] * 1)],
							'COUNT'				=> $row['count'],
							'PCT'					=> number_format($row['count'] / $config['num_users'] * 100, 2),
							'BARWIDTH'					=> number_format($row['count'] / $max_count * 100, 1),
							'IS_MINE'					=> ($row['user_timezone'] == $user->data['user_timezone']) ? true : false,
							'IS_MAX'					=> ($row['count'] == $max_count) ? true : false,
						));
					}
				}
			}
			else
			{
				$template->assign_var('S_SETTINGS_BOARD_DISABLED', true);
			}
			break;
			
			case 'profile':
			if($stats_config['settings_profile_enable'])
			{
				//get the age of users
				$range_interval = request_var('range_interval', 10); //this calculates the interval in the age groups ... if 10 => 10-19, 20-29, if 5 => 10-14, 15-19... considering $range_start = 10
				$range_start = 0;
				$upper_limit = 100; //will show results upto 99
				
				$birthdays = array();
				$age_ranges = array();
				$users_with_birthday = 0;
				$current_time = getdate(time());
				//initialize the array
				for ($i = $range_start; $i < $upper_limit; $i += $range_interval)
				{
					$age_ranges[$i] = 0;
				}
								
				$sql = 'SELECT user_birthday FROM ' . USERS_TABLE . "
							WHERE user_birthday <> ''
							AND user_type IN (" . USER_NORMAL . ', ' . USER_FOUNDER . ')';
				$result = $db->sql_query($sql);				
				while ($row = $db->sql_fetchrow($result))
				{
					$birthdays[] = explode('-', $row['user_birthday'], 3); //[0] => day, [1] => month, [2] => year					
				}				
				$db->sql_freeresult($result);
				
				foreach ($birthdays as $row)
				{
					if($row[0] < 1 || $row[1] < 1 || $row[2] < 1)
					{
						continue;
					}
					
					if (mktime($current_time['hours'], $current_time['minutes'], $current_time['seconds'], $row[1], $row[0],  $current_time['year']) > $current_time[0])
					{
						$age = $current_time['year'] - $row[2] - 1;
					}
					else
					{
						$age = $current_time['year'] - $row[2];
					}
					//now increment the appropriate counter
					if ($age < $range_start || $age >= $upper_limit)
					{
						continue;
					}
					++$age_ranges[$age - (($age - $range_start) % $range_interval)];
					++$users_with_birthday;
					
				}
				
				$total_age_counts = array_sum($age_ranges);
				if ($user->data['user_birthday'] && $user->data['user_birthday'] != '0- 0-   0')
				{
					$my_age_data = explode('-', $user->data['user_birthday'], 3);				

					if (mktime($current_time['hours'], $current_time['minutes'], $current_time['seconds'], $my_age_data[1], $my_age_data[0], $current_time['year']) > $current_time[0])
					{
						$my_age = $current_time['year'] - $my_age_data[2] - 1;
					}
					else
					{
						$my_age = $current_time['year'] - $my_age_data[2];
					}
				}
				
				if ($total_age_counts)
				{
					//make the age interval options
					$age_interval_options = array(
						'4' 	=> 4,
						'5'		=> 5,
						'8'		=> 8,
						'10'	=> 10,
						'15'	=> 15,
						'20'	=> 20,
					);
					$template->assign_vars(array(
						'S_AGE'				=> true,
						'AGE_INTERVAL_SELECT_BOX'		=> make_select_box($age_interval_options, $range_interval, 'range_interval', $user->lang['SEL_AGE_INTERVAL_PROMPT'], $user->lang['GO'], $this->u_action),
					));
					//find max
					$max_count = 0;
					foreach ($age_ranges as $row)
					{
						if ($row > $max_count)
						{
							$max_count = $row;
						}
					}
					//assign vars to template
					if(isset($my_age))
					{
						foreach ($age_ranges as $age_range_start => $count)
						{
							$template->assign_block_vars('age_row', array(
								'AGE_RANGE'			=> $age_range_start . ' - ' . (($age_range_start + $range_interval > $upper_limit) ? $upper_limit - 1 : $age_range_start + $range_interval - 1),
								'COUNT'				=> $count,
								'PCT'				=> number_format($count / $total_age_counts * 100, 2),
								'BARWIDTH'			=> number_format($count / $max_count * 100, 1),
								'IS_MAX'			=> ($count == $max_count),
								'IS_MINE'			=> ($my_age && $my_age >= $age_range_start && $my_age < $age_range_start + $range_interval),
							));
						}
					}
					else
					{
						foreach ($age_ranges as $age_range_start => $count)
						{
							$template->assign_block_vars('age_row', array(
								'AGE_RANGE'			=> $age_range_start . ' - ' . (($age_range_start + $range_interval > $upper_limit) ? $upper_limit - 1 : $age_range_start + $range_interval - 1),
								'COUNT'				=> $count,
								'PCT'				=> number_format($count / $total_age_counts * 100, 2),
								'BARWIDTH'			=> number_format($count / $max_count * 100, 1),
								'IS_MAX'			=> ($count == $max_count),
								'IS_MINE'			=> false,
							));
						}
					}
				}
				
				//locations
				$limit_count = request_var('limit_count', 10);
				$locations = array(); 
				$locations_count = array();
				if($db->sql_layer != 'mssql' && $db->sql_layer != 'mssql_odbc')
				{
				$sql = 'SELECT TRIM(LOWER(user_from)) AS location, COUNT(user_id) AS count FROM ' . USERS_TABLE . "
							WHERE user_from <> ''
								GROUP BY TRIM(LOWER(user_from))
							ORDER BY count DESC, location ASC";
				}
				else
				{
					$sql = 'SELECT LTRIM(RTRIM(LOWER(user_from))) AS location, COUNT(user_id) AS count FROM ' . USERS_TABLE . "
								WHERE user_from <> ''
								GROUP BY LTRIM(RTRIM(LOWER(user_from)))
								ORDER BY count DESC, location ASC";
				}
				$result = $db->sql_query_limit($sql, $limit_count); //we can't use limit here since we cant order in sql (gosh the location string!)
				while ($row = $db->sql_fetchrow($result))
				{
					$locations[] = $row;
				}
				$db->sql_freeresult($result);
				
				//get total users with locations
				$total_users_set_location = 0;
				$sql = 'SELECT COUNT(user_id) AS count FROM ' . USERS_TABLE . "
							WHERE user_from <> ''";
				$result = $db->sql_query($sql);
				$total_users_set_location = (int) $db->sql_fetchfield('count');
				$db->sql_freeresult($result);
				
				if ($total_users_set_location)
				{
					$limit_options = array(
						'1'		=> 1,
						'3'		=> 3,
						'5'		=> 5,
						'10'	=> 10,
						'15'	=> 15,
					);
					$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $user->lang['USER_LOCATIONS']);
					$template->assign_vars(array(						
						'S_LOCATIONS'			=> true,
						'LIMIT_SELECT_BOX'		=> make_select_box($limit_options, $limit_count, 'limit_count', $limit_prompt, $user->lang['GO'], $this->u_action),
					));
					
					$max_count = $locations[0]['count']; //get the max count
					
					foreach ($locations as $row)
					{
						$left_character = substr($row['location'], 0, 1);
						$left_character = strtoupper($left_character);
						$template->assign_block_vars('location_row', array(
							'LOCATION'			=> $left_character . substr($row['location'], 1),
							'COUNT'				=> $row['count'],
							'PCT'						=> number_format($row['count'] / $total_users_set_location * 100, 2),
							'BARWIDTH'					=> number_format($row['count'] / $max_count * 100, 1),
							'IS_MAX'				=> ($row['count'] == $max_count),
							'IS_MINE'				=> ($row['location'] == trim(strtolower($user->data['user_from']))),
						));
					}
				}
				
				$template->assign_vars(array(
				'USERS_WITH_BIRTHDAY'		=> $users_with_birthday,
				'USERS_WITH_LOCATION'		=> $total_users_set_location,
				'TOTAL_USERS'				=> $config['num_users'],
				'USERS_WITH_BIRTHDAY_PCT'	=> number_format($users_with_birthday / $config['num_users'] * 100, 2),
				'USERS_WITH_LOCATION_PCT'	=> number_format($total_users_set_location / $config['num_users'] * 100, 2),
				'TOP_USER_LOCATIONS'	=> sprintf($user->lang['TOP_USER_LOCATIONS'], $limit_count),
				));
				
				
				//now the tricky custom profile fields section
				$custom_profile_fields = get_custom_profile_fields(); //the results are already in the order as set to be shown				
				$show_custom_profile_fields = false; //this will be enabled if even one custom profile field has some data
				$user_cpf_data = array();				
				if (sizeof($custom_profile_fields))
				{
					//get user's cpf info first
					if ($user->data['user_id'] != ANONYMOUS && $user->data['is_registered'])
					{
						$sql = 'SELECT * FROM ' . PROFILE_FIELDS_DATA_TABLE . '
					WHERE user_id = ' . $user->data['user_id'];
						$result = $db->sql_query($sql);
						$user_cpf_data = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);
					}
					
					//get the limit first
					/*$limit_counts = request_var('cpf_limit_counts', array()); //please note this is an array ([field_id1] => limit1, [field_id2] => limit2...)		*/			
					$total_values_set = array(); //this array holds the total values set for each field with key as the field id
					foreach ($custom_profile_fields as $current_field)
					{
						$field_identifier = 'pf_' . $current_field['field_ident'];
						//here we have to show as groups just like the top x topics etc...
						
						$limit_count = request_var('cpf_limit_counts_' . $current_field['field_id'], 10);
						
						$total_data_groups = 0; //this holds the number of data groups returned
						//get the data for this field
						$field_data = get_profile_field_data($current_field, $total_values_set[$current_field['field_id']], $total_data_groups, $limit_count);
						
						//assign the total first
						$template->assign_block_vars('cpf_total', array(
							'TOTAL_VALUES_SET'		=> $total_values_set[$current_field['field_id']],
							'TOTAL_VALUES_SET_PROMPT'	=> sprintf($user->lang['TOTAL_VALUES_SET_PROMPT'], $current_field['lang_name']),
							'PCT'					=> number_format($total_values_set[$current_field['field_id']] / $config['num_users'] * 100, 2),
						));
						
						switch ($current_field['field_type'])
						{
							case FIELD_STRING:
							
								if (sizeof($field_data))
								{
									//proceed to display the data
									$current_cpf_limit_options = array(
										'1'		=> 1,
										'3'		=> 3,
										'5'		=> 5,
										'10'	=> 10,
										'15'	=> 15,
									);
									$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $current_field['lang_name']);
									
									$template->assign_block_vars('cpf_block', array(
										'FIELD_TITLE'			=> $current_field['lang_name'],
										'FIELD_TYPE'			=> $current_field['field_type'],
										'TOP_X_PROMPT'			=> sprintf($user->lang['CPF_TOP_X'], $limit_count, $current_field['lang_name']),
										'LIMIT_SELECT_BOX'		=> make_select_box($current_cpf_limit_options, $limit_count, 'cpf_limit_counts_' . $current_field['field_id'], $limit_prompt, $user->lang['GO'], $this->u_action),
									));
									
									//get max value
									$max_count = $field_data[0]['count'];
									
									if(isset($user_cpf_data[$field_identifier]))
									{
										foreach ($field_data as $row)
										{
											$template->assign_block_vars('cpf_block.cpf_row', array(
											'VALUE'					=> ucwords($row['value']),									
											'COUNT'					=> $row['count'],
											'PCT'					=> number_format($row['count'] / $total_values_set[$current_field['field_id']] * 100, 2),
											'BARWIDTH'				=> number_format($row['count'] / $max_count * 100, 1),
											'IS_MINE'				=> ($row['value'] == trim(strtolower($user_cpf_data[$field_identifier]))),
											));
										}
									}
									else
									{
										foreach ($field_data as $row)
										{
											$template->assign_block_vars('cpf_block.cpf_row', array(
											'VALUE'					=> ucwords($row['value']),									
											'COUNT'					=> $row['count'],
											'PCT'					=> number_format($row['count'] / $total_values_set[$current_field['field_id']] * 100, 2),
											'BARWIDTH'				=> number_format($row['count'] / $max_count * 100, 1),
											'IS_MINE'				=> false,
											));
										}
									}
								
								}
							break;
							
							case FIELD_BOOL:
								if ($total_values_set[$current_field['field_id']] && sizeof($field_data)) //here we have to check for total values count as we will get two elements in $field_data by default
								{									
									//proceed to display the data
									$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $current_field['lang_name']);
									
									$template->assign_block_vars('cpf_block', array(
										'FIELD_TITLE'			=> $current_field['lang_name'],
										'FIELD_TYPE'			=> $current_field['field_type'],
										'TOP_X_PROMPT'			=> $current_field['lang_name'],										
									));
									
									foreach ($field_data as $row)
									{
										$template->assign_block_vars('cpf_block.cpf_row', array(
										'VALUE'					=> ucwords($row['lang_value']),									
										'COUNT'					=> $row['count'],
										'TOTAL_VALUES'			=> $total_values_set[$current_field['field_id']],
										'IS_MINE'				=> ($row['option_id'] == trim(strtolower($user_cpf_data[$field_identifier]))),
										'PCT'					=> number_format($row['count'] / $total_values_set[$current_field['field_id']] * 100, 2),
										'IS_DEFAULT'			=> ($current_field['field_default_value'] == $row['option_id']),
										));
									}								
								}
							break;
							
							case FIELD_DROPDOWN:
								if (sizeof($field_data))
								{									
									//proceed to display the data
									$current_cpf_limit_options = array();
									for ($i = 1; $i <= $total_data_groups; ++$i)
									{
										$current_cpf_limit_options[$i] = $i;
									}
									if ($limit_count > $total_data_groups)
									{
										$limit_count = $total_data_groups;
									}
									$limit_prompt = sprintf($user->lang['LIMIT_PROMPT'], $current_field['lang_name']);
									
									$template->assign_block_vars('cpf_block', array(
										'FIELD_TITLE'			=> $current_field['lang_name'],
										'FIELD_TYPE'			=> $current_field['field_type'],
										'TOP_X_PROMPT'			=> ($limit_count < $total_data_groups) ? sprintf($user->lang['CPF_TOP_X'], $limit_count, $current_field['lang_name']) : '<i>' . $user->lang['ALL'] . '</i> ' . $current_field['lang_name'],
										'LIMIT_SELECT_BOX'		=> make_select_box($current_cpf_limit_options, $limit_count, 'cpf_limit_counts_' . $current_field['field_id'], $limit_prompt, $user->lang['GO'], $this->u_action),
									));
									
									//get max value

									$max_count = $field_data[0]['count'];

									foreach ($field_data as $row)
									{
										$template->assign_block_vars('cpf_block.cpf_row', array(
										'VALUE'					=> ucwords($row['lang_value']),
										'COUNT'					=> $row['count'],
										'PCT'					=> number_format($row['count'] / $total_values_set[$current_field['field_id']] * 100, 2),
										'BARWIDTH'				=> number_format($row['count'] / $max_count * 100, 1),
										'IS_MINE'				=> ($row['option_id'] == trim(strtolower($user_cpf_data[$field_identifier]))),
										'IS_DEFAULT'			=> ($current_field['field_default_value'] == $row['option_id']),
										));
									}
								
								}
							break;
							
							default:
						}
						
					}
				}
			}
			else
			{
				$template->assign_var('S_SETTINGS_PROFILE_DISABLED', true);
			}
			break;
			
			default:		
		}
		
		$template->assign_vars(array(
			'L_TITLE'	=> $user->lang['STATS_SETTINGS_' . strtoupper($mode)],
			'S_FS_ACTION'		=> $this->u_action,
			'AS_ON'				=> sprintf($user->lang['AS_ON'], $user->format_date(time())),			
		));
		
		
		$this->tpl_name = 'stats/stats_settings_' . $mode;
		$this->lang_name = 'stats_settings_' . $mode;
		$this->page_title = $user->lang['STATISTICS'] . ' &bull; ' . $user->lang[strtoupper($this->lang_name)];
	}
}
?>
