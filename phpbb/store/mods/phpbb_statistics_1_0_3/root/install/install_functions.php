<?php
/**
*
* @package phpBB Statistics
* @version $Id: install_functions.php 146 2010-05-18 22:33:54Z marc1706 $
* @copyright (c) 2009 Marc Alexander(marc1706) www.m-a-styles.de
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @based on: Board3 Portal Installer (www.board3.de)
*/

/**
* @ignore
*/

if (!defined('IN_PHPBB'))
{
	exit;
}
if (!defined('IN_INSTALL'))
{
	exit;
}

function get_dbms_infos()
{
	global $db;

	switch ($db->sql_layer)
	{
		case 'mysql':
			$return['db_schema'] = 'mysql_40';
			$return['delimiter'] = ';';
		break;

		case 'mysql4':
			if (version_compare($db->sql_server_info(true), '4.1.3', '>='))
			{
				$return['db_schema'] = 'mysql_41';
			}
			else
			{
				$return['db_schema'] = 'mysql_40';
			}
			$return['delimiter'] = ';';
		break;

		case 'mysqli':
			$return['db_schema'] = 'mysql_41';
			$return['delimiter'] = ';';
		break;

		case 'mssql':
		case 'mssql_odbc':
			$return['db_schema'] = 'mssql';
			$return['delimiter'] = 'GO';
		break;

		case 'postgres':
			$return['db_schema'] = 'postgres';
			$return['delimiter'] = ';';
		break;

		case 'sqlite':
			$return['db_schema'] = 'sqlite';
			$return['delimiter'] = ';';
		break;

		case 'firebird':
			$return['db_schema'] = 'firebird';
			$return['delimiter'] = ';;';
		break;

		case 'oracle':
			$return['db_schema'] = 'oracle';
			$return['delimiter'] = '/';
		break;

		default:
			trigger_error($user->lang['UNSUPPORTED_DB']);
		break;
	}

	return $return;
}

/*
* Needed to handle the creating of the db-tables out of the schema-files
*/
function split_sql_file($sql, $delimiter)
{
	$sql = str_replace("\r" , '', $sql);
	$data = preg_split('/' . preg_quote($delimiter, '/') . '$/m', $sql);

	$data = array_map('trim', $data);

	// The empty case
	$end_data = end($data);

	if (empty($end_data))
	{
		unset($data[key($data)]);
	}

	return $data;
}

/*
* Creates a new db-table
*	Note: we don't check for it on anyother way, so it might return a SQL-Error,
*	if you create the same table twice without this!
* @param	string	$table	table-name
* @param	bool	$drop	drops the table if it exist.
*/
function stats_create_table($table, $dbms_data, $drop = true)
{
	global $db, $table_prefix, $db_schema, $delimiter;

	$table_name = substr($table . '#', 6, -1);
	$db_schema = $dbms_data['db_schema'];
	$delimiter = $dbms_data['delimiter'];

	if ($drop == true)
	{
		if(is_array($table))
		{
			foreach($table as $current_table)
			{
				stats_drop_table($current_table);
			}
		}
		else
		{
		stats_drop_table($table);
		}
	}

	// locate the schema files
	$dbms_schema = 'schemas/_' . $db_schema . '_schema.sql';
	$sql_query = @file_get_contents($dbms_schema);
	$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);
	$sql_query = preg_replace('/\n{2,}/', "\n", preg_replace('/^#.*$/m', "\n", $sql_query));
	$sql_query = split_sql_file($sql_query, $delimiter);
	// make the new one's
	foreach ($sql_query as $sql)
	{
		if (!$db->sql_query($sql))
		{
			$error = $db->sql_error();
			$this->p_master->db_error($error['message'], $sql, __LINE__, __FILE__);
		}
	}
	unset($sql_query);
}

/*
* Drops a db-table
* Note: you will loose all data!
* @param	string	$table	table-name
*/
function stats_drop_table($table)
{
	global $db, $table_prefix, $db_schema;

	$table_name = substr($table . '#', 6, -1);

	if ($db->sql_layer != 'mssql' && $db->sql_layer != 'mssql_odbc')
	{
		$sql = 'DROP TABLE IF EXISTS ' . $table_prefix . $table_name;
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
	else
	{
		$sql = 'if exists (select * from sysobjects where name = ' . $table_prefix . $table_name . ')
			drop table ' . $table_prefix . $table_name;
		$sql = "IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '{$table_prefix}{$table_name}')
			DROP TABLE {$table_prefix}{$table_name}";
		$result = $db->sql_query($sql);
		$db->sql_freeresult($result);
	}
}

/*
* Advanced: Add/update a stats-config value
*/
function set_stats_config($column, $value, $update = false)
{
	global $db;

	$sql = 'SELECT * FROM ' . STATS_CONFIG_TABLE . " WHERE config_name = '$column'";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if (!$row)
	{
		$sql_ary = array(
			'config_name'				=> $column,
			'config_value'				=> $value,
		);
		$db->sql_query('INSERT INTO ' . STATS_CONFIG_TABLE . $db->sql_build_array('INSERT', $sql_ary));
	}
	else
	{
		$sql_ary = array(
			'config_name'				=> $column,
			'config_value'				=> $value,
		);
		$db->sql_query('UPDATE ' . STATS_CONFIG_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . " WHERE config_name = '$column'");
	}
}

/*
* Changes a column to a table
*	Note: it's not allowed to change the name of the column!
* @param	string	$table	table-name
* @param	string	$column	column-name
* @param	array	$values	column-type
*							array({column_type}, {default}, {auto_increment})
*							for explanation see: create_schema_files.php "*	Column Types:"
*/
function stats_change_column($table, $column_name, $column_data)
{
	global $db;

	$phpbb_db_tools = new phpbb_db_tools($db);
	if ($phpbb_db_tools->sql_column_exists($table, $column_name) == true)
	{
		$phpbb_db_tools->sql_column_change($table, $column_name, $column_data);
	}
}

/*
* Creates a dropdown box with all modules to choose a parent-module for a new module to avoid "PARENT_NO_EXIST"
* Note: you will loose all data of this column!
* @param	string	$module_class	'acp' or 'mcp' or 'ucp'
* @param	int		$default_id		the "standard" id of the module: enter 0 if not available, Exp: 31
* @param	string	$default_langname	language-less name Exp for 31 (.MODs): ACP_CAT_DOT_MODS
*/
function module_select($module_class, $default_id, $default_langname)
{
	global $db, $user;

	$module_options = '<option value="0">' . $user->lang['MODULES_SELECT_NONE'] . '</option>';
	$found_selected = false;

	$sql = 'SELECT module_id, module_langname, module_class
		FROM ' . MODULES_TABLE . "
		WHERE module_class = '$module_class'";
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$selected = '';
		if (($row['module_id'] == $default_id) || ($row['module_langname'] == $default_langname))
		{
			$selected = ' selected="selected"';
			$found_selected = true;
		}
		$module_options .= '<option value="' . $row['module_id'] . '"' . $selected .'>' . ((isset($user->lang[$row['module_langname']])) ? $user->lang[$row['module_langname']] : $row['module_langname']) . '</option>';
	}
	if (!$found_selected && $default_id)
	{
		$module_options = '<option value="-1">' . $user->lang['MODULES_CREATE_PARENT'] . '</option>' . $module_options;
	}

	return $module_options;
}

/*
* Adds a module to the phpbb_modules-table
* @param	array	$array	Exp:	array('module_basename' => '',	'module_enabled' => 1,	'module_display' => 1,	'parent_id' => $choosen_acp_module,	'module_class' => 'acp',	'module_langname'=> 'PHPBB_GALLERY',	'module_mode' => '',	'module_auth' => '')
*/
function add_module($array)
{
	global $user;
	$modules = new acp_modules();
	$failed = $modules->update_module_data($array, true);
}

/*
* Removes a module of the phpbb_modules-table
*	Note: Be sure that the module exists, otherwise it may give an error message
*/
function remove_module($module_id, $module_class)
{
	global $user;
	$modules = new acp_modules();
	$modules->module_class = $module_class;
	$failed = $modules->delete_module($module_id);
}

/*
* Advanced: Load stats-config values
*/
function load_stats_config()
{
	global $db;

	$portal_config = array();

	$sql = 'SELECT * FROM ' . STATS_CONFIG_TABLE;
	$result = $db->sql_query($sql);
	while( $row = $db->sql_fetchrow($result) )
	{
		$stats_config[$row['config_name']] = $row['config_value'];
	}
	$db->sql_freeresult($result);

	return $stats_config;
}

/*
* Create a back-link
*	Note: just like phpbb3's adm_back_link
* @param	string	$u_action	back-link-url
*/
function adm_back_link($u_action)
{
	global $user;
	return '<br /><br /><a href="' . $u_action . '">&laquo; ' . $user->lang['BACK_TO_PREV'] . '</a>';
}

/**	get overall bbcode and smiley count
*	this has to be executed upon install, update, and if the counts table have been corrupted
*	Copyright (c) 2009 - 2010 Marc Alexander(marc1706) www.m-a-styles.de
*	DO NOT USE without written permission by Marc Alexander or one of his associates
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
			$smilies[$smiley_row['smiley_url']] = 0;
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
		elseif($current_smiley > 0)
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

?>